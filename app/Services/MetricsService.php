<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use App\Models\Vertical;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MetricsService
{
    public function getPipelineValue(User $user, ?int $verticalId = null): float
    {
        return Deal::forUser($user)
            ->when($verticalId, fn ($q) => $q->where('vertical_id', $verticalId))
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->sum('amount');
    }

    public function getConversionRate(User $user, Carbon $from, Carbon $to): float
    {
        $total = Deal::forUser($user)
            ->whereBetween('actual_close_date', [$from, $to])
            ->whereHas('stage', fn ($q) => $q->where('is_won', true)->orWhere('is_lost', true))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $won = Deal::forUser($user)
            ->whereBetween('actual_close_date', [$from, $to])
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->count();

        return round(($won / $total) * 100, 1);
    }

    public function getAverageTicket(User $user): float
    {
        return Deal::forUser($user)
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->avg('amount') ?? 0;
    }

    public function getAverageCloseTime(User $user): ?float
    {
        $deals = Deal::forUser($user)
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->whereNotNull('actual_close_date')
            ->select(['created_at', 'actual_close_date'])
            ->get();

        if ($deals->isEmpty()) {
            return null;
        }

        $totalDays = $deals->sum(fn ($deal) =>
            $deal->created_at->diffInDays($deal->actual_close_date)
        );

        return round($totalDays / $deals->count(), 1);
    }

    public function getFunnelData(User $user, ?int $verticalId = null): array
    {
        if (!$verticalId) {
            $vertical = Vertical::active()->with(['stages' => fn ($q) => $q->ordered()])->first();
            if (!$vertical) return ['labels' => [], 'values' => [], 'colors' => []];
        } else {
            $vertical = Vertical::with(['stages' => fn ($q) => $q->ordered()])->findOrFail($verticalId);
        }

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($vertical->stages as $stage) {
            $labels[] = $stage->name;
            $values[] = $stage->deals()->whereNull('deleted_at')->count();
            $colors[] = $stage->color;
        }

        return compact('labels', 'values', 'colors');
    }

    public function getTimelineData(User $user, Carbon $from, Carbon $to): array
    {
        $deals = Deal::forUser($user)
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->whereBetween('actual_close_date', [$from, $to])
            ->orderBy('actual_close_date')
            ->get(['actual_close_date', 'amount']);

        $grouped = $deals->groupBy(fn ($d) => $d->actual_close_date->format('Y-W'));
        $labels = [];
        $amounts = [];

        foreach ($grouped as $week => $weekDeals) {
            $labels[] = 'Sem. ' . $weekDeals->first()->actual_close_date->format('d/m');
            $amounts[] = $weekDeals->sum('amount');
        }

        return ['labels' => $labels, 'amounts' => $amounts];
    }

    public function getWeightedPipeline(User $user): float
    {
        return Deal::forUser($user)
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->get(['amount', 'probability'])
            ->sum(fn ($d) => $d->amount * ($d->probability / 100));
    }

    public function getUpcomingDeals(User $user, int $days = 7): Collection
    {
        return Deal::with(['client', 'stage', 'vertical'])
            ->forUser($user)
            ->whereNotNull('expected_close_date')
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->whereBetween('expected_close_date', [Carbon::today(), Carbon::today()->addDays($days)])
            ->orderBy('expected_close_date')
            ->get();
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\Models\Deal;
use Carbon\Carbon;
use Livewire\Component;

class UpcomingDeals extends Component
{
    public int $days = 7;

    public function getDealsProperty()
    {
        $deadline = Carbon::today()->addDays($this->days);

        return Deal::forUser(auth()->user())
            ->with(['client', 'stage'])
            ->whereNotNull('expected_close_date')
            ->whereBetween('expected_close_date', [Carbon::today(), $deadline])
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->orderBy('expected_close_date')
            ->get()
            ->map(function (Deal $deal) {
                $deal->days_remaining = (int) Carbon::today()->diffInDays($deal->expected_close_date, false);
                return $deal;
            });
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.upcoming-deals', [
            'deals' => $this->deals,
        ]);
    }
}

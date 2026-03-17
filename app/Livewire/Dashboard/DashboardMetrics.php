<?php

namespace App\Livewire\Dashboard;

use App\Models\Vertical;
use App\Services\MetricsService;
use Carbon\Carbon;
use Livewire\Component;

class DashboardMetrics extends Component
{
    public ?int $verticalId = null;
    public string $dateRange = '30d';
    public array $metrics = [];

    public function mount(): void
    {
        $this->loadMetrics();
    }

    public function updatedVerticalId(): void
    {
        $this->loadMetrics();
    }

    public function updatedDateRange(): void
    {
        $this->loadMetrics();
    }

    protected function loadMetrics(): void
    {
        $service = app(MetricsService::class);
        $user = auth()->user();

        [$from, $to] = $this->parseDateRange();

        $this->metrics = [
            'pipelineValue'  => $service->getPipelineValue($user, $this->verticalId),
            'conversionRate' => $service->getConversionRate($user, $from, $to),
            'averageTicket'  => $service->getAverageTicket($user),
            'avgCloseTime'   => $service->getAverageCloseTime($user),
            'funnelData'     => $service->getFunnelData($user, $this->verticalId),
        ];
    }

    protected function parseDateRange(): array
    {
        $to   = Carbon::today();
        $from = match ($this->dateRange) {
            '7d'   => $to->copy()->subDays(7),
            '90d'  => $to->copy()->subDays(90),
            '365d' => $to->copy()->subDays(365),
            default => $to->copy()->subDays(30),
        };

        return [$from, $to];
    }

    public function render(): \Illuminate\View\View
    {
        $verticals = Vertical::active()->orderBy('name')->get();

        return view('livewire.dashboard.dashboard-metrics', [
            'verticals'      => $verticals,
            'pipelineValue'  => $this->metrics['pipelineValue']  ?? 0,
            'conversionRate' => $this->metrics['conversionRate'] ?? 0,
            'averageTicket'  => $this->metrics['averageTicket']  ?? 0,
            'avgCloseTime'   => $this->metrics['avgCloseTime']   ?? null,
            'funnelData'     => $this->metrics['funnelData']     ?? [],
        ]);
    }
}

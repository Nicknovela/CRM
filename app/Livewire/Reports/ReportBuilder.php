<?php

namespace App\Livewire\Reports;

use App\Models\Vertical;
use Livewire\Component;

class ReportBuilder extends Component
{
    public string $reportType = 'pipeline';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $verticalId = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
    }

    public function exportExcel(): mixed
    {
        return $this->redirect($this->buildExportUrl('excel'), navigate: false);
    }

    public function exportPdf(): mixed
    {
        return $this->redirect($this->buildExportUrl('pdf'), navigate: false);
    }

    protected function buildExportUrl(string $format): string
    {
        return route('reports.export', array_filter([
            'type'       => $this->reportType,
            'date_from'  => $this->dateFrom,
            'date_to'    => $this->dateTo,
            'vertical_id'=> $this->verticalId ?: null,
            'format'     => $format,
        ]));
    }

    public function render(): \Illuminate\View\View
    {
        $verticals = Vertical::active()->orderBy('name')->get();

        return view('livewire.reports.report-builder', [
            'verticals' => $verticals,
        ]);
    }
}

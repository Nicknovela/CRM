<?php

namespace App\Exports;

use App\Models\Deal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DealsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return Deal::with(['client', 'vertical', 'stage', 'assignedTo'])
            ->when(isset($this->filters['vertical_id']), fn ($q) => $q->where('vertical_id', $this->filters['vertical_id']))
            ->when(isset($this->filters['date_from']), fn ($q) => $q->where('created_at', '>=', $this->filters['date_from']))
            ->when(isset($this->filters['date_to']), fn ($q) => $q->where('created_at', '<=', $this->filters['date_to']))
            ->forUser(auth()->user())
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Cliente',
            'Empresa',
            'Vertical',
            'Etapa',
            'Responsable',
            'Monto',
            'Moneda',
            'Probabilidad (%)',
            'Comisión (%)',
            'F. Cierre Esperado',
            'F. Cierre Real',
            'Estado',
            'Creado',
        ];
    }

    public function map($deal): array
    {
        $status = 'Activo';
        if ($deal->stage?->is_won) $status = 'Ganado';
        if ($deal->stage?->is_lost) $status = 'Perdido';

        return [
            $deal->id,
            $deal->title,
            $deal->client?->name,
            $deal->client?->company_name,
            $deal->vertical?->name,
            $deal->stage?->name,
            $deal->assignedTo?->name,
            $deal->amount,
            $deal->currency,
            $deal->probability,
            $deal->commission_rate,
            $deal->expected_close_date?->format('d/m/Y'),
            $deal->actual_close_date?->format('d/m/Y'),
            $status,
            $deal->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

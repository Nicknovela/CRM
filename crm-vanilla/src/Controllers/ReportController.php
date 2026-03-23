<?php

namespace Controllers;

use Core\Auth;
use Core\View;
use Models\Deal;
use Models\Vertical;

class ReportController
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRole('admin', 'manager');

        $filters = [
            'vertical_id' => query('vertical_id'),
            'date_from'   => query('date_from'),
            'date_to'     => query('date_to'),
        ];

        $deals     = Deal::forReport($filters, Auth::id(), Auth::role());
        $verticals = Vertical::active();

        View::render('reports/index', [
            'title'     => 'Reportes',
            'deals'     => $deals,
            'verticals' => $verticals,
            'filters'   => $filters,
        ]);
    }

    public function exportCsv(): void
    {
        Auth::requireAuth();
        Auth::requireRole('admin', 'manager');

        $filters = [
            'vertical_id' => query('vertical_id'),
            'date_from'   => query('date_from'),
            'date_to'     => query('date_to'),
        ];

        $deals = Deal::forReport($filters, Auth::id(), Auth::role());

        $filename = 'negocios_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        // BOM for Excel UTF-8 compatibility
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Título', 'Cliente', 'Empresa', 'Vertical', 'Etapa', 'Responsable', 'Monto', 'Moneda', 'Probabilidad', 'Comisión %', 'Fecha cierre esperada', 'Fecha cierre real', 'Estado', 'Creado'], ';');

        foreach ($deals as $d) {
            $status = $d['is_won'] ? 'Ganado' : ($d['is_lost'] ? 'Perdido' : 'En progreso');
            fputcsv($out, [
                $d['id'],
                $d['title'],
                $d['client_name'],
                $d['company_name'] ?? '',
                $d['vertical_name'],
                $d['stage_name'],
                $d['assigned_name'] ?? '',
                $d['amount'],
                $d['currency'],
                $d['probability'] . '%',
                $d['commission_rate'] ? $d['commission_rate'] . '%' : '',
                $d['expected_close_date'] ? format_date($d['expected_close_date']) : '',
                $d['actual_close_date']   ? format_date($d['actual_close_date'])   : '',
                $status,
                format_date($d['created_at']),
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function exportPdf(): void
    {
        Auth::requireAuth();
        Auth::requireRole('admin', 'manager');

        $filters = [
            'vertical_id' => query('vertical_id'),
            'date_from'   => query('date_from'),
            'date_to'     => query('date_to'),
        ];

        $deals     = Deal::forReport($filters, Auth::id(), Auth::role());
        $verticals = Vertical::active();

        header('Content-Type: text/html; charset=UTF-8');
        echo View::partial('reports/pdf', [
            'deals'     => $deals,
            'verticals' => $verticals,
            'filters'   => $filters,
            'date'      => date('d/m/Y H:i'),
        ]);
        exit;
    }
}

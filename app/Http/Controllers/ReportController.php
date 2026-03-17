<?php

namespace App\Http\Controllers;

use App\Exports\DealsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __invoke()
    {
        return view('reports.index');
    }

    public function exportExcel(Request $request)
    {
        $type = $request->query('type', 'deals');
        return Excel::download(new DealsExport($request->all()), 'reporte-negocios.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $type = $request->query('type', 'deals');
        $view = match ($type) {
            'pipeline' => 'exports.pdf.pipeline-summary',
            default => 'exports.pdf.deals-report',
        };

        $pdf = app(\Barryvdh\DomPDF\PDF::class)->loadView($view, [
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);
        return $pdf->download('reporte.pdf');
    }
}

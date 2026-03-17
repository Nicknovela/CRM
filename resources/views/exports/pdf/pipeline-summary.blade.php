<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #64748b; margin-bottom: 20px; }
        h2 { font-size: 14px; color: #1e293b; margin: 20px 0 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #6366f1; color: white; padding: 8px 10px; text-align: left; font-size: 10px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .total-row td { font-weight: bold; background: #f1f5f9 !important; border-top: 2px solid #cbd5e1; }
        .footer { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <h1>Resumen de Pipeline</h1>
    <p class="subtitle">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</p>

    @foreach(\App\Models\Vertical::active()->with(['stages' => fn($q) => $q->ordered()])->get() as $vertical)
    <h2>{{ $vertical->name }}</h2>
    <table>
        <thead>
            <tr>
                <th>Etapa</th>
                <th>Negocios</th>
                <th>Monto Total (BOB)</th>
                <th>Monto Ponderado</th>
            </tr>
        </thead>
        <tbody>
        @php $verticalTotal = 0; $verticalWeighted = 0; @endphp
        @foreach($vertical->stages as $stage)
        @php
            $deals = $stage->deals()->whereNull('deleted_at')->get();
            $total = $deals->sum('amount');
            $weighted = $deals->sum(fn($d) => $d->amount * $d->probability / 100);
            $verticalTotal += $total;
            $verticalWeighted += $weighted;
        @endphp
        <tr>
            <td>{{ $stage->name }}</td>
            <td>{{ $deals->count() }}</td>
            <td>{{ format_currency($total) }}</td>
            <td>{{ format_currency($weighted) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td>Total {{ $vertical->name }}</td>
            <td></td>
            <td>{{ format_currency($verticalTotal) }}</td>
            <td>{{ format_currency($verticalWeighted) }}</td>
        </tr>
        </tbody>
    </table>
    @endforeach

    <div class="footer">CRM Pipeline &mdash; Reporte confidencial</div>
</body>
</html>

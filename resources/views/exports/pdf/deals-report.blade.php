<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #4f46e5; color: white; padding: 8px 10px; text-align: left; font-size: 10px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
        .badge-won { background: #dcfce7; color: #166534; }
        .badge-lost { background: #fee2e2; color: #991b1b; }
        .badge-active { background: #e0e7ff; color: #3730a3; }
        .footer { margin-top: 30px; font-size: 9px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <h1>Reporte de Negocios</h1>
    <p class="subtitle">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Cliente</th>
                <th>Vertical</th>
                <th>Etapa</th>
                <th>Responsable</th>
                <th>Monto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Deal::with(['client','vertical','stage','assignedTo'])->forUser(auth()->user())->get() as $deal)
            @php
                $status = 'Activo';
                $badgeClass = 'badge-active';
                if ($deal->stage?->is_won) { $status = 'Ganado'; $badgeClass = 'badge-won'; }
                if ($deal->stage?->is_lost) { $status = 'Perdido'; $badgeClass = 'badge-lost'; }
            @endphp
            <tr>
                <td>{{ $deal->title }}</td>
                <td>{{ $deal->client?->name }}</td>
                <td>{{ $deal->vertical?->name }}</td>
                <td>{{ $deal->stage?->name }}</td>
                <td>{{ $deal->assignedTo?->name }}</td>
                <td>{{ format_currency($deal->amount, $deal->currency) }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">CRM Pipeline &mdash; Reporte confidencial</div>
</body>
</html>

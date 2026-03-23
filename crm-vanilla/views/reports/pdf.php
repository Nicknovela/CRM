<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Negocios</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
  h1 { font-size: 18px; margin-bottom: 4px; }
  .sub { color: #666; font-size: 11px; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th { background: #1e40af; color: #fff; text-align: left; padding: 6px 8px; font-size: 11px; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
  tr:nth-child(even) td { background: #f9fafb; }
  .badge-won  { background:#dcfce7; color:#166534; padding:1px 6px; border-radius:9999px; font-size:10px; font-weight:bold; }
  .badge-lost { background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:9999px; font-size:10px; font-weight:bold; }
  .badge-act  { background:#dbeafe; color:#1e40af; padding:1px 6px; border-radius:9999px; font-size:10px; font-weight:bold; }
  @media print { button { display:none; } }
</style>
</head>
<body>
<h1>Reporte de Negocios</h1>
<p class="sub">Generado el <?= e($date) ?></p>

<button onclick="window.print()" style="margin-bottom:16px;padding:6px 14px;background:#1e40af;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;">
    🖨 Imprimir / Guardar PDF
</button>

<table>
    <thead>
        <tr>
            <th>Negocio</th><th>Cliente</th><th>Vertical</th><th>Etapa</th>
            <th>Responsable</th><th>Monto</th><th>Moneda</th><th>Estado</th><th>Creado</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($deals as $d): ?>
    <tr>
        <td><?= e($d['title']) ?></td>
        <td><?= e($d['client_name']) ?><?= $d['company_name'] ? '<br><small style="color:#666">'.e($d['company_name']).'</small>' : '' ?></td>
        <td><?= e($d['vertical_name']) ?></td>
        <td><?= e($d['stage_name']) ?></td>
        <td><?= e($d['assigned_name'] ?? '—') ?></td>
        <td style="font-weight:bold"><?= number_format((float)$d['amount'],2,'.',',' )?></td>
        <td><?= e($d['currency']) ?></td>
        <td>
            <?php if ($d['is_won']): ?>
                <span class="badge-won">Ganado</span>
            <?php elseif ($d['is_lost']): ?>
                <span class="badge-lost">Perdido</span>
            <?php else: ?>
                <span class="badge-act">En progreso</span>
            <?php endif; ?>
        </td>
        <td><?= format_date($d['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p style="margin-top:16px;color:#666;font-size:10px;">Total: <?= count($deals) ?> negocios</p>
</body>
</html>

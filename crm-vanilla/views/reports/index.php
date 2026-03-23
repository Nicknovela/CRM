<!-- Filters + Export bar -->
<div class="flex flex-wrap items-end gap-4 mb-6">
    <form method="GET" action="<?= url('/reports') ?>" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Vertical</label>
            <select name="vertical_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todas</option>
                <?php foreach ($verticals as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= $filters['vertical_id'] == $v['id'] ? 'selected' : '' ?>><?= e($v['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
            <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
            <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800">Aplicar</button>
    </form>

    <div class="flex gap-2 ml-auto">
        <a href="<?= url('/reports/export/csv?'.http_build_query($filters)) ?>"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            📥 Exportar CSV
        </a>
        <a href="<?= url('/reports/export/pdf?'.http_build_query($filters)) ?>" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
            📄 Ver PDF
        </a>
    </div>
</div>

<!-- Summary cards -->
<?php
$wonDeals   = array_filter($deals, fn($d) => $d['is_won']);
$lostDeals  = array_filter($deals, fn($d) => $d['is_lost']);
$activeDeals= array_filter($deals, fn($d) => !$d['is_won'] && !$d['is_lost']);
$totalValue = array_sum(array_column($deals, 'amount'));
?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php $summaries = [
        ['label'=>'Total negocios', 'value'=>count($deals), 'color'=>'bg-gray-50 border-gray-200'],
        ['label'=>'Ganados',        'value'=>count($wonDeals),   'color'=>'bg-green-50 border-green-200'],
        ['label'=>'Perdidos',       'value'=>count($lostDeals),  'color'=>'bg-red-50 border-red-200'],
        ['label'=>'Valor total',    'value'=>format_money($totalValue), 'color'=>'bg-indigo-50 border-indigo-200'],
    ];
    foreach ($summaries as $s): ?>
    <div class="rounded-xl border p-4 <?= $s['color'] ?>">
        <p class="text-xs text-gray-500 uppercase tracking-wide"><?= $s['label'] ?></p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= $s['value'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Deals table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($deals)): ?>
        <p class="text-center py-12 text-gray-400">Sin datos para el período seleccionado.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 font-medium">Negocio</th>
                    <th class="px-4 py-3 font-medium">Cliente</th>
                    <th class="px-4 py-3 font-medium">Vertical</th>
                    <th class="px-4 py-3 font-medium">Etapa</th>
                    <th class="px-4 py-3 font-medium">Responsable</th>
                    <th class="px-4 py-3 font-medium">Monto</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 font-medium">Creado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($deals as $d): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="<?= url('/deals/'.$d['id']) ?>" class="font-medium text-indigo-600 hover:underline"><?= e($d['title']) ?></a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        <?= e($d['client_name']) ?>
                        <?php if ($d['company_name']): ?><br><span class="text-xs text-gray-400"><?= e($d['company_name']) ?></span><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?= e($d['vertical_name']) ?></td>
                    <td class="px-4 py-3"><?= stage_badge($d['stage_name'], '#6B7280') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= e($d['assigned_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 font-bold text-gray-900"><?= format_money((float)$d['amount'], $d['currency']) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($d['is_won']): ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700">Ganado</span>
                        <?php elseif ($d['is_lost']): ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">Perdido</span>
                        <?php else: ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700">En progreso</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= format_date($d['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

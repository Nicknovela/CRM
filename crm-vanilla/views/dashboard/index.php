<?php
$extraJs = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="APP_ASSET_JS_CHARTS"></script>
<script>
window.funnelData = JSON.parse(atob('FUNNEL_BASE64'));
</script>
HTML;
$extraJs = str_replace('APP_ASSET_JS_CHARTS', asset('js/charts.js'), $extraJs);
$extraJs = str_replace('FUNNEL_BASE64', base64_encode(json_encode($funnel, JSON_UNESCAPED_UNICODE)), $extraJs);
?>
<!-- Filters bar -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <form method="GET" action="<?= url('/dashboard') ?>" class="flex items-center gap-3 flex-wrap">
        <select name="vertical_id" onchange="this.form.submit()"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todas las verticales</option>
            <?php foreach ($verticals as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $verticalId == $v['id'] ? 'selected' : '' ?>>
                    <?= e($v['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="period" onchange="this.form.submit()"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <?php foreach ([7=>'Últimos 7 días',30=>'Últimos 30 días',90=>'Últimos 90 días',365=>'Último año'] as $val=>$lbl): ?>
                <option value="<?= $val ?>" <?= $period == $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Metrics cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['label'=>'Pipeline activo','value'=>format_money($metrics['pipeline_value']),'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'indigo'],
        ['label'=>'Tasa de conversión','value'=>$metrics['conversion_rate'].'%','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'green'],
        ['label'=>'Ticket promedio','value'=>format_money($metrics['average_ticket']),'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z','color'=>'yellow'],
        ['label'=>'Días prom. de cierre','value'=>$metrics['avg_close_days'].' días','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','color'=>'purple'],
    ];
    $colorMap = ['indigo'=>'bg-indigo-50 text-indigo-600','green'=>'bg-green-50 text-green-600','yellow'=>'bg-yellow-50 text-yellow-600','purple'=>'bg-purple-50 text-purple-600'];
    foreach ($cards as $card):
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide"><?= e($card['label']) ?></p>
                <p class="mt-1 text-2xl font-bold text-gray-900"><?= e($card['value']) ?></p>
            </div>
            <div class="w-10 h-10 rounded-lg <?= $colorMap[$card['color']] ?> flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $card['icon'] ?>"/>
                </svg>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts + Activity row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Funnel chart -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Embudo de ventas</h3>
        <canvas id="funnelChart" height="200"></canvas>
    </div>

    <!-- Activity feed -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Actividad reciente</h3>
        <div class="space-y-3 max-h-72 overflow-y-auto">
            <?php if (empty($activities)): ?>
                <p class="text-sm text-gray-400 text-center py-4">Sin actividad reciente</p>
            <?php else: ?>
                <?php foreach ($activities as $act): ?>
                <div class="flex gap-3 text-sm">
                    <span class="text-lg leading-none mt-0.5"><?= activity_icon($act['type']) ?></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800 truncate"><?= e($act['description']) ?></p>
                        <p class="text-xs text-gray-400">
                            <a href="<?= url('/deals/'.$act['deal_id']) ?>" class="hover:underline text-indigo-600"><?= e($act['deal_title']) ?></a>
                            · <?= time_ago($act['created_at']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upcoming deals -->
<?php if (!empty($upcoming)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Negocios a cerrar pronto (7 días)</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b border-gray-100">
                    <th class="pb-2 font-medium">Negocio</th>
                    <th class="pb-2 font-medium">Etapa</th>
                    <th class="pb-2 font-medium">Monto</th>
                    <th class="pb-2 font-medium">Cierre</th>
                    <th class="pb-2 font-medium">Días</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($upcoming as $d):
                    $days = (int) ceil((strtotime($d['expected_close_date']) - time()) / 86400);
                    $urgency = $days <= 1 ? 'text-red-600 font-semibold' : ($days <= 3 ? 'text-yellow-600 font-semibold' : 'text-gray-600');
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-2">
                        <a href="<?= url('/deals/'.$d['id']) ?>" class="font-medium text-indigo-600 hover:underline"><?= e($d['title']) ?></a>
                        <p class="text-xs text-gray-400"><?= e($d['client_name']) ?></p>
                    </td>
                    <td class="py-2"><?= stage_badge($d['stage_name'], $d['stage_color']) ?></td>
                    <td class="py-2 font-medium"><?= format_money((float)$d['amount'], $d['currency']) ?></td>
                    <td class="py-2 text-gray-600"><?= format_date($d['expected_close_date']) ?></td>
                    <td class="py-2 <?= $urgency ?>"><?= $days ?> d</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

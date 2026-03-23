<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <form method="GET" action="<?= url('/deals') ?>" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar negocios..."
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        <select name="vertical_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todas las verticales</option>
            <?php foreach ($verticals as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $filters['vertical_id'] == $v['id'] ? 'selected' : '' ?>><?= e($v['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($users)): ?>
        <select name="assigned_to" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todos los responsables</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $filters['assigned_to'] == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button type="submit" class="px-3 py-2 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800">Filtrar</button>
        <?php if ($filters['search'] || $filters['vertical_id'] || $filters['assigned_to']): ?>
            <a href="<?= url('/deals') ?>" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900">✕ Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (!\Core\Auth::is('viewer')): ?>
    <a href="<?= url('/deals/new') ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo negocio
    </a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($deals)): ?>
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="font-medium">No se encontraron negocios</p>
        </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                    <?php
                    $cols = ['title'=>'Negocio','client_name'=>'Cliente','vertical_name'=>'Vertical','stage_name'=>'Etapa','amount'=>'Monto','assigned_name'=>'Responsable','expected_close_date'=>'Cierre'];
                    foreach ($cols as $col=>$lbl):
                        $active = $filters['sort'] === $col;
                        $nextDir = ($active && $filters['dir']==='asc') ? 'desc' : 'asc';
                        $arrow = $active ? ($filters['dir']==='asc'?'↑':'↓') : '';
                        $href = url('/deals?'.http_build_query(array_merge($filters,['sort'=>$col,'dir'=>$nextDir,'page'=>1])));
                    ?>
                    <th class="px-4 py-3 font-medium">
                        <a href="<?= $href ?>" class="hover:text-gray-700"><?= $lbl ?> <?= $arrow ?></a>
                    </th>
                    <?php endforeach; ?>
                    <th class="px-4 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($deals as $d): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="<?= url('/deals/'.$d['id']) ?>" class="font-medium text-indigo-600 hover:underline"><?= e($d['title']) ?></a>
                        <p class="text-xs text-gray-400"><?= e($d['company_name'] ?: '') ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-700"><?= e($d['client_name']) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium text-white"
                              style="background-color:<?= e($d['vertical_color']) ?>">
                            <?= e($d['vertical_name']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3"><?= stage_badge($d['stage_name'], $d['stage_color']) ?></td>
                    <td class="px-4 py-3 font-medium text-gray-900"><?= format_money((float)$d['amount'], $d['currency']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= e($d['assigned_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= format_date($d['expected_close_date']) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?= url('/deals/'.$d['id'].'/edit') ?>"
                               class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Editar</a>
                            <?php if (\Core\Auth::is('admin','manager')): ?>
                            <form method="POST" action="<?= url('/deals/'.$d['id'].'/delete') ?>"
                                  onsubmit="return confirm('¿Eliminar este negocio?')">
                                <?= csrf_field() ?>
                                <button class="text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600">Eliminar</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pager['total_pages'] > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-600">
        <span><?= $pager['total'] ?> negocios — página <?= $pager['current'] ?> de <?= $pager['total_pages'] ?></span>
        <div class="flex gap-1">
            <?php if ($pager['has_prev']): ?>
                <a href="<?= url('/deals?'.http_build_query(array_merge($filters,['page'=>$pager['current']-1]))) ?>"
                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50">← Ant.</a>
            <?php endif; ?>
            <?php if ($pager['has_next']): ?>
                <a href="<?= url('/deals?'.http_build_query(array_merge($filters,['page'=>$pager['current']+1]))) ?>"
                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50">Sig. →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

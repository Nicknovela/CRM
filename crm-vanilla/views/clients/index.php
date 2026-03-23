<!-- Toolbar -->
<div class="flex items-center justify-between mb-6">
    <form method="GET" action="<?= url('/clients') ?>" class="flex items-center gap-2">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar clientes..."
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        <button type="submit" class="px-3 py-2 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800">Buscar</button>
        <?php if ($filters['search']): ?>
            <a href="<?= url('/clients') ?>" class="text-sm text-gray-500 hover:text-gray-700">✕</a>
        <?php endif; ?>
    </form>

    <?php if (!\Core\Auth::is('viewer')): ?>
    <a href="<?= url('/clients/new') ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo cliente
    </a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($clients)): ?>
        <div class="text-center py-16 text-gray-400">
            <p class="font-medium">No se encontraron clientes</p>
        </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 font-medium">Nombre</th>
                    <th class="px-4 py-3 font-medium">Empresa</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Teléfono</th>
                    <th class="px-4 py-3 font-medium">Negocios</th>
                    <th class="px-4 py-3 font-medium">Pipeline</th>
                    <th class="px-4 py-3 font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($clients as $c): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="<?= url('/clients/'.$c['id']) ?>" class="font-medium text-indigo-600 hover:underline"><?= e($c['name']) ?></a>
                    </td>
                    <td class="px-4 py-3 text-gray-700"><?= e($c['company_name'] ?: '—') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= e($c['email'] ?: '—') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= e($c['phone'] ?: '—') ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                            <?= $c['deal_count'] ?> negocio<?= $c['deal_count'] != 1 ? 's' : '' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900"><?= format_money((float)$c['pipeline_value']) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <?php if (!\Core\Auth::is('viewer')): ?>
                            <a href="<?= url('/clients/'.$c['id'].'/edit') ?>"
                               class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Editar</a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::is('admin','manager')): ?>
                            <form method="POST" action="<?= url('/clients/'.$c['id'].'/delete') ?>"
                                  onsubmit="return confirm('¿Eliminar este cliente?')">
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

    <?php if ($pager['total_pages'] > 1): ?>
    <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-600">
        <span><?= $pager['total'] ?> clientes</span>
        <div class="flex gap-1">
            <?php if ($pager['has_prev']): ?>
                <a href="<?= url('/clients?'.http_build_query(array_merge($filters,['page'=>$pager['current']-1]))) ?>"
                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50">← Ant.</a>
            <?php endif; ?>
            <?php if ($pager['has_next']): ?>
                <a href="<?= url('/clients?'.http_build_query(array_merge($filters,['page'=>$pager['current']+1]))) ?>"
                   class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-50">Sig. →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

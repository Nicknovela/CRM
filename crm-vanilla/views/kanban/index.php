<?php
$extraJs = '<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>'
         . '<script src="' . asset('js/kanban.js') . '"></script>';
?>
<!-- Vertical selector -->
<div class="flex items-center justify-between mb-6">
    <form method="GET" action="<?= url('/kanban') ?>">
        <select name="vertical_id" onchange="this.form.submit()"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <?php foreach ($verticals as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $verticalId == $v['id'] ? 'selected' : '' ?>>
                    <?= e($v['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if (!\Core\Auth::is('viewer')): ?>
    <a href="<?= url('/deals/new') ?>"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
        + Nuevo negocio
    </a>
    <?php endif; ?>
</div>

<!-- Kanban board -->
<div class="flex gap-4 overflow-x-auto pb-4" id="kanban-board">
    <?php foreach ($stages as $stage): ?>
    <div class="kanban-column flex-shrink-0 w-72"
         data-stage-id="<?= $stage['id'] ?>"
         data-stage-name="<?= e($stage['name']) ?>">

        <!-- Column header -->
        <div class="flex items-center justify-between px-3 py-2 rounded-t-lg"
             style="background-color:<?= e($stage['color']) ?>20; border-bottom: 2px solid <?= e($stage['color']) ?>">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                      style="background-color:<?= e($stage['color']) ?>"></span>
                <span class="font-medium text-sm text-gray-800"><?= e($stage['name']) ?></span>
            </div>
            <span class="text-xs bg-white rounded-full px-2 py-0.5 text-gray-600 font-medium">
                <?= count($stage['deals']) ?>
            </span>
        </div>

        <!-- Drop zone -->
        <div class="deal-list bg-gray-100 rounded-b-lg p-2 min-h-40 space-y-2"
             data-stage-id="<?= $stage['id'] ?>">
            <?php foreach ($stage['deals'] as $d): ?>
            <div class="deal-card bg-white rounded-lg border border-gray-200 p-3 cursor-grab hover:shadow-md transition-shadow"
                 data-deal-id="<?= $d['id'] ?>">
                <a href="<?= url('/deals/'.$d['id']) ?>"
                   class="font-medium text-sm text-gray-900 hover:text-indigo-600 block mb-1"
                   onclick="event.stopPropagation()">
                    <?= e($d['title']) ?>
                </a>
                <p class="text-xs text-gray-500 mb-2"><?= e($d['client_name']) ?><?= $d['company_name'] ? ' · '.e($d['company_name']) : '' ?></p>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700"><?= format_money((float)$d['amount'], $d['currency'] ?? 'BOB') ?></span>
                    <?php if ($d['assigned_name']): ?>
                    <div class="w-5 h-5 rounded-full bg-indigo-200 flex items-center justify-center"
                         title="<?= e($d['assigned_name']) ?>">
                        <span class="text-indigo-700 font-bold" style="font-size:9px"><?= initials($d['assigned_name']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($stages)): ?>
    <div class="text-center py-16 text-gray-400 w-full">
        <p>No hay etapas configuradas para esta vertical.</p>
        <?php if (\Core\Auth::is('admin','manager')): ?>
        <a href="<?= url('/admin/verticals') ?>" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">
            Configurar verticales →
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

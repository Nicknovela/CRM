<!-- Back + actions bar -->
<div class="flex items-center justify-between mb-6">
    <a href="<?= url('/deals') ?>" class="text-sm text-indigo-600 hover:underline">← Volver a negocios</a>
    <?php if (!\Core\Auth::is('viewer')): ?>
    <div class="flex gap-2">
        <a href="<?= url('/deals/'.$deal['id'].'/edit') ?>"
           class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Editar</a>
        <?php if (\Core\Auth::is('admin','manager')): ?>
        <form method="POST" action="<?= url('/deals/'.$deal['id'].'/delete') ?>"
              onsubmit="return confirm('¿Eliminar este negocio?')">
            <?= csrf_field() ?>
            <button class="px-3 py-2 border border-red-200 rounded-lg text-sm text-red-600 hover:bg-red-50">Eliminar</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Deal details -->
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900"><?= e($deal['title']) ?></h2>
                    <div class="flex items-center gap-3 mt-2 flex-wrap">
                        <?= stage_badge($deal['stage_name'], $deal['stage_color']) ?>
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium text-white"
                              style="background-color:<?= e($deal['vertical_color']) ?>">
                            <?= e($deal['vertical_name']) ?>
                        </span>
                        <?php if ($deal['is_won']): ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700">✓ Ganado</span>
                        <?php elseif ($deal['is_lost']): ?>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">✗ Perdido</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-gray-900"><?= format_money((float)$deal['amount'], $deal['currency']) ?></p>
                    <p class="text-sm text-gray-500"><?= $deal['probability'] ?>% probabilidad</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm border-t border-gray-100 pt-4">
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Cliente</span>
                    <p class="font-medium text-gray-800 mt-0.5">
                        <a href="<?= url('/clients/'.$deal['client_id']) ?>" class="hover:underline text-indigo-600"><?= e($deal['client_name']) ?></a>
                    </p>
                    <?php if ($deal['company_name']): ?>
                        <p class="text-gray-500 text-xs"><?= e($deal['company_name']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Responsable</span>
                    <p class="font-medium text-gray-800 mt-0.5"><?= e($deal['assigned_name'] ?? 'Sin asignar') ?></p>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Cierre esperado</span>
                    <p class="font-medium text-gray-800 mt-0.5"><?= format_date($deal['expected_close_date']) ?></p>
                </div>
                <?php if ($deal['actual_close_date']): ?>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Cierre real</span>
                    <p class="font-medium text-gray-800 mt-0.5"><?= format_date($deal['actual_close_date']) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($deal['commission_rate']): ?>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Comisión</span>
                    <p class="font-medium text-gray-800 mt-0.5"><?= $deal['commission_rate'] ?>%</p>
                </div>
                <?php endif; ?>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wide">Creado</span>
                    <p class="font-medium text-gray-800 mt-0.5"><?= format_date($deal['created_at']) ?></p>
                </div>
            </div>

            <?php if ($deal['notes']): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500 uppercase tracking-wide">Notas</span>
                <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap"><?= e($deal['notes']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($deal['lost_reason']): ?>
            <div class="mt-3 p-3 bg-red-50 rounded-lg">
                <span class="text-xs font-medium text-red-600">Motivo de pérdida:</span>
                <p class="text-sm text-red-700 mt-0.5"><?= e($deal['lost_reason']) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Add activity -->
        <?php if (!\Core\Auth::is('viewer')): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Agregar actividad</h3>
            <form id="activity-form" data-deal-id="<?= $deal['id'] ?>">
                <div class="flex gap-2 mb-3 flex-wrap">
                    <?php foreach (['note'=>'📝 Nota','call'=>'📞 Llamada','email'=>'📧 Email','meeting'=>'🤝 Reunión'] as $type=>$lbl): ?>
                    <label class="flex items-center gap-1 cursor-pointer">
                        <input type="radio" name="type" value="<?= $type ?>" <?= $type==='note'?'checked':'' ?> class="text-indigo-600">
                        <span class="text-sm text-gray-700"><?= $lbl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="flex gap-2">
                    <textarea name="description" placeholder="Escribe el detalle de la actividad..." rows="2"
                              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 self-end">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Activity timeline -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Historial</h3>
        <div id="activity-list" class="space-y-4">
            <?php if (empty($activities)): ?>
                <p class="text-sm text-gray-400">Sin actividad registrada.</p>
            <?php else: ?>
                <?php foreach ($activities as $act): ?>
                <div class="flex gap-3 text-sm activity-item">
                    <span class="text-xl leading-none mt-0.5"><?= activity_icon($act['type']) ?></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800"><?= e($act['description']) ?></p>
                        <?php if ($act['old_stage_name'] && $act['new_stage_name']): ?>
                            <p class="text-xs text-gray-400 mt-0.5">
                                <?= stage_badge($act['old_stage_name'], $act['old_stage_color']) ?>
                                → <?= stage_badge($act['new_stage_name'], $act['new_stage_color']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400 mt-1"><?= e($act['user_name']) ?> · <?= time_ago($act['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= asset('js/deals.js') ?>"></script>

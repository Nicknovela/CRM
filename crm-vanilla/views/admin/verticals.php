<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500"><?= count($verticals) ?> vertical(es)</p>
    <?php if (\Core\Auth::is('admin')): ?>
    <button onclick="document.getElementById('modal-new-vertical').classList.remove('hidden')"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
        + Nueva vertical
    </button>
    <?php endif; ?>
</div>

<!-- Verticals grid -->
<div class="space-y-6">
    <?php foreach ($verticals as $v): ?>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <!-- Vertical header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100"
             style="border-left: 4px solid <?= e($v['color']) ?>">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full" style="background:<?= e($v['color']) ?>"></span>
                <div>
                    <h3 class="font-semibold text-gray-900"><?= e($v['name']) ?></h3>
                    <?php if ($v['description']): ?>
                        <p class="text-xs text-gray-500"><?= e($v['description']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($v['is_active']): ?>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Activa</span>
                <?php else: ?>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactiva</span>
                <?php endif; ?>
                <?php if ($v['track_commission']): ?>
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Con comisión</span>
                <?php endif; ?>
            </div>
            <?php if (\Core\Auth::is('admin')): ?>
            <div class="flex gap-2">
                <button onclick="openEditVertical(<?= htmlspecialchars(json_encode($v), ENT_QUOTES) ?>)"
                        class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Editar</button>
                <form method="POST" action="<?= url('/admin/verticals/'.$v['id'].'/delete') ?>"
                      onsubmit="return confirm('¿Eliminar vertical <?= e(addslashes($v['name'])) ?>?')">
                    <?= csrf_field() ?>
                    <button class="text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600">Eliminar</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stages -->
        <div class="px-5 py-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Etapas</span>
                <button onclick="document.getElementById('modal-new-stage-<?= $v['id'] ?>').classList.remove('hidden')"
                        class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 hover:bg-indigo-100">+ Agregar</button>
            </div>

            <div class="flex flex-wrap gap-2" id="stages-<?= $v['id'] ?>">
                <?php foreach ($v['stages'] as $s): ?>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm"
                     style="border-color: <?= e($s['color']) ?>; background: <?= e($s['color']) ?>15">
                    <span class="w-2 h-2 rounded-full" style="background:<?= e($s['color']) ?>"></span>
                    <span class="text-gray-800 font-medium"><?= e($s['name']) ?></span>
                    <?php if ($s['is_won']): ?><span class="text-xs text-green-600">✓</span><?php endif; ?>
                    <?php if ($s['is_lost']): ?><span class="text-xs text-red-500">✗</span><?php endif; ?>
                    <button onclick="openEditStage(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)"
                            class="text-gray-400 hover:text-gray-600 ml-1 text-xs">✎</button>
                    <form method="POST" action="<?= url('/admin/stages/'.$s['id'].'/delete') ?>"
                          onsubmit="return confirm('¿Eliminar etapa?')" class="inline">
                        <?= csrf_field() ?>
                        <button class="text-red-400 hover:text-red-600 text-xs">✕</button>
                    </form>
                </div>
                <?php endforeach; ?>
                <?php if (empty($v['stages'])): ?>
                    <p class="text-xs text-gray-400">Sin etapas. Agrega la primera.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- New Stage Modal -->
    <div id="modal-new-stage-<?= $v['id'] ?>" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Nueva etapa en "<?= e($v['name']) ?>"</h3>
            <form method="POST" action="<?= url('/admin/verticals/'.$v['id'].'/stages/create') ?>">
                <?= csrf_field() ?>
                <?php include __DIR__ . '/partials/stage-fields.php'; ?>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="this.closest('[id^=modal-new-stage]').classList.add('hidden')"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">Cancelar</button>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Crear</button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- New Vertical Modal -->
<div id="modal-new-vertical" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Nueva vertical</h3>
        <form method="POST" action="<?= url('/admin/verticals/create') ?>">
            <?= csrf_field() ?>
            <?php include __DIR__ . '/partials/vertical-fields.php'; ?>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal-new-vertical').classList.add('hidden')"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">Cancelar</button>
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Crear</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Vertical Modal -->
<div id="modal-edit-vertical" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Editar vertical</h3>
        <form method="POST" id="form-edit-vertical" action="">
            <?= csrf_field() ?>
            <?php include __DIR__ . '/partials/vertical-fields.php'; ?>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal-edit-vertical').classList.add('hidden')"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">Cancelar</button>
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Stage Modal -->
<div id="modal-edit-stage" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Editar etapa</h3>
        <form method="POST" id="form-edit-stage" action="">
            <?= csrf_field() ?>
            <?php include __DIR__ . '/partials/stage-fields.php'; ?>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal-edit-stage').classList.add('hidden')"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">Cancelar</button>
                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditVertical(v) {
    const modal = document.getElementById('modal-edit-vertical');
    const form  = document.getElementById('form-edit-vertical');
    form.action = APP_URL + '/admin/verticals/' + v.id + '/edit';
    modal.querySelector('[name="name"]').value        = v.name;
    modal.querySelector('[name="description"]').value = v.description || '';
    modal.querySelector('[name="color"]').value       = v.color;
    modal.querySelector('[name="is_active"]').checked        = v.is_active == 1;
    modal.querySelector('[name="track_commission"]').checked = v.track_commission == 1;
    modal.classList.remove('hidden');
}
function openEditStage(s) {
    const modal = document.getElementById('modal-edit-stage');
    const form  = document.getElementById('form-edit-stage');
    form.action = APP_URL + '/admin/stages/' + s.id + '/edit';
    modal.querySelector('[name="name"]').value    = s.name;
    modal.querySelector('[name="color"]').value   = s.color;
    modal.querySelector('[name="is_won"]').checked = s.is_won == 1;
    modal.querySelector('[name="is_lost"]').checked= s.is_lost == 1;
    modal.classList.remove('hidden');
}
</script>

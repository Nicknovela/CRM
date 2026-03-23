<?php
$isEdit = $deal !== null;
$action = $isEdit ? url('/deals/'.$deal['id'].'/edit') : url('/deals/new');
$extraJs = '<script src="' . asset('js/deals.js') . '"></script>';
?>
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="<?= url('/deals') ?>" class="text-sm text-indigo-600 hover:underline">← Volver a negocios</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="space-y-5">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título del negocio *</label>
                    <input type="text" name="title" value="<?= e(old('title', $deal['title'] ?? '')) ?>" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Ej: Proyecto de implementación ERP">
                </div>

                <!-- Client -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select name="client_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar cliente...</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (old('client_id', $deal['client_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?><?= $c['company_name'] ? ' — '.e($c['company_name']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?= url('/clients/new') ?>" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">+ Crear nuevo cliente</a>
                </div>

                <!-- Vertical + Stage -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vertical *</label>
                        <select name="vertical_id" id="vertical_select" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($verticals as $v): ?>
                                <option value="<?= $v['id'] ?>"
                                        data-commission="<?= $v['track_commission'] ?>"
                                        <?= (old('vertical_id', $deal['vertical_id'] ?? '')) == $v['id'] ? 'selected' : '' ?>>
                                    <?= e($v['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Etapa *</label>
                        <select name="stage_id" id="stage_select" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Primero elige vertical</option>
                            <?php if (!empty($stages)): ?>
                                <?php foreach ($stages as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (old('stage_id', $deal['stage_id'] ?? '')) == $s['id'] ? 'selected' : '' ?>>
                                        <?= e($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Amount + Currency -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto</label>
                        <input type="number" name="amount" step="0.01" min="0"
                               value="<?= e(old('amount', $deal['amount'] ?? '0')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Moneda</label>
                        <select name="currency"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <?php foreach (['BOB','USD','COP'] as $cur): ?>
                                <option value="<?= $cur ?>" <?= (old('currency', $deal['currency'] ?? 'BOB')) === $cur ? 'selected' : '' ?>><?= $cur ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Probability + Commission -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Probabilidad (%)</label>
                        <input type="number" name="probability" min="0" max="100"
                               value="<?= e(old('probability', $deal['probability'] ?? '50')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div id="commission_field" style="display:none">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comisión (%)</label>
                        <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                               value="<?= e(old('commission_rate', $deal['commission_rate'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Assigned + Close date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                        <select name="assigned_to"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sin asignar</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (old('assigned_to', $deal['assigned_to'] ?? \Core\Auth::id())) == $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de cierre esperada</label>
                        <input type="date" name="expected_close_date"
                               value="<?= e(old('expected_close_date', $deal['expected_close_date'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= e(old('notes', $deal['notes'] ?? '')) ?></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-2">
                    <a href="<?= url('/deals') ?>"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <?= $isEdit ? 'Guardar cambios' : 'Crear negocio' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
window.DEAL_VERTICAL_ID = <?= json_encode($deal['vertical_id'] ?? null) ?>;
window.DEAL_STAGE_ID = <?= json_encode($deal['stage_id'] ?? null) ?>;
</script>

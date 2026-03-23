<?php
$isEdit = $client !== null;
$action = $isEdit ? url('/clients/'.$client['id'].'/edit') : url('/clients/new');
?>
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="<?= url('/clients') ?>" class="text-sm text-indigo-600 hover:underline">← Volver a clientes</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="<?= $action ?>">
            <?= csrf_field() ?>
            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" value="<?= e(old('name', $client['name'] ?? '')) ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                        <input type="text" name="company_name" value="<?= e(old('company_name', $client['company_name'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= e(old('email', $client['email'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="phone" value="<?= e(old('phone', $client['phone'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Industria</label>
                        <input type="text" name="industry" value="<?= e(old('industry', $client['industry'] ?? '')) ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sitio web</label>
                        <input type="url" name="website" value="<?= e(old('website', $client['website'] ?? '')) ?>"
                               placeholder="https://"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address" value="<?= e(old('address', $client['address'] ?? '')) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= e(old('notes', $client['notes'] ?? '')) ?></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="<?= url('/clients') ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <?= $isEdit ? 'Guardar cambios' : 'Crear cliente' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
        <input type="text" name="name" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Contraseña <?= isset($editMode) ? '(dejar vacío para no cambiar)' : '*' ?>
        </label>
        <input type="password" name="password" <?= isset($editMode) ? '' : 'required' ?> minlength="8"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select name="role"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="vendedor">Vendedor</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="viewer">Viewer</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Zona horaria</label>
            <select name="timezone"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php foreach (['America/La_Paz','America/Bogota','America/Lima','America/Santiago','America/Mexico_City','America/Buenos_Aires','America/Caracas','America/New_York','Europe/Madrid','UTC'] as $tz): ?>
                    <option value="<?= $tz ?>"><?= $tz ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" checked class="rounded text-indigo-600">
        <span class="text-sm text-gray-700">Usuario activo</span>
    </label>
</div>

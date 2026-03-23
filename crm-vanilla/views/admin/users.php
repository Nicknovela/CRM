<!-- Header actions -->
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500"><?= count($users) ?> usuario(s) registrado(s)</p>
    <button onclick="document.getElementById('modal-create-user').classList.remove('hidden')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
        + Nuevo usuario
    </button>
</div>

<!-- Users table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-4 py-3 font-medium">Usuario</th>
                <th class="px-4 py-3 font-medium">Rol</th>
                <th class="px-4 py-3 font-medium">Estado</th>
                <th class="px-4 py-3 font-medium">Zona horaria</th>
                <th class="px-4 py-3 font-medium">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-indigo-700 font-bold text-xs"><?= initials($u['name']) ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?= e($u['name']) ?></p>
                            <p class="text-xs text-gray-400"><?= e($u['email']) ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="capitalize inline-flex px-2 py-0.5 rounded text-xs font-medium
                        <?= match($u['role']) {
                            'admin'   => 'bg-purple-100 text-purple-700',
                            'manager' => 'bg-blue-100 text-blue-700',
                            'vendedor'=> 'bg-green-100 text-green-700',
                            default   => 'bg-gray-100 text-gray-600',
                        } ?>">
                        <?= e($u['role']) ?>
                    </span>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="<?= url('/admin/users/'.$u['id'].'/toggle') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" title="Toggle estado"
                                class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                <?= $u['is_active'] ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                            <?= $u['is_active'] ? 'Activo' : 'Inactivo' ?>
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs"><?= e($u['timezone']) ?></td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        <button onclick="openEditUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)"
                                class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Editar</button>
                        <?php if ($u['id'] !== \Core\Auth::id()): ?>
                        <form method="POST" action="<?= url('/admin/users/'.$u['id'].'/delete') ?>"
                              onsubmit="return confirm('¿Eliminar usuario <?= e(addslashes($u['name'])) ?>?')">
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

<!-- Create User Modal -->
<div id="modal-create-user" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Nuevo usuario</h3>
            <button onclick="document.getElementById('modal-create-user').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
        </div>
        <form method="POST" action="<?= url('/admin/users/create') ?>">
            <?= csrf_field() ?>
            <?php include __DIR__ . '/partials/user-fields.php'; ?>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal-create-user').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Crear</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="modal-edit-user" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Editar usuario</h3>
            <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
        </div>
        <form method="POST" id="form-edit-user" action="">
            <?= csrf_field() ?>
            <?php $editMode = true; include __DIR__ . '/partials/user-fields.php'; ?>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUser(user) {
    const modal = document.getElementById('modal-edit-user');
    const form  = document.getElementById('form-edit-user');
    form.action = APP_URL + '/admin/users/' + user.id + '/edit';
    modal.querySelector('[name="name"]').value     = user.name;
    modal.querySelector('[name="email"]').value    = user.email;
    modal.querySelector('[name="role"]').value     = user.role;
    modal.querySelector('[name="timezone"]').value = user.timezone;
    modal.querySelector('[name="is_active"]').checked = user.is_active == 1;
    modal.querySelector('[name="password"]').value = '';
    modal.classList.remove('hidden');
}
</script>

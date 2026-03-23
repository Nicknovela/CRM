<?php $layout = 'auth'; ?>
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    <h2 class="text-xl font-semibold text-gray-900 mb-6">Iniciar sesión</h2>
    <form method="POST" action="<?= url('/login') ?>">
        <?= csrf_field() ?>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="tu@correo.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="••••••••">
            </div>
            <button type="submit"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm transition-colors">
                Ingresar
            </button>
        </div>
    </form>
    <p class="mt-6 text-xs text-center text-gray-500">
        Sistema CRM — Acceso solo para usuarios registrados
    </p>
</div>

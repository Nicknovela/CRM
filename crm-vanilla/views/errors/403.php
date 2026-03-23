<?php $layout = 'auth'; ?>
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
    <div class="text-5xl mb-4">🔒</div>
    <h2 class="text-xl font-bold text-gray-900 mb-2">Acceso denegado</h2>
    <p class="text-gray-500 text-sm mb-6">No tienes permisos para acceder a esta sección.</p>
    <a href="<?= url('/dashboard') ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">← Ir al dashboard</a>
</div>

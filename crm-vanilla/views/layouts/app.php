<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'CRM') ?> — <?= e($appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const APP_URL = '<?= rtrim(getenv('APP_URL') ?: '', '/') ?>';
        const CSRF_TOKEN = '<?= csrf_token() ?>';
    </script>
</head>
<body class="h-full flex">

<!-- Sidebar -->
<aside class="w-64 flex-shrink-0 bg-gray-900 flex flex-col" id="sidebar">
    <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700">
        <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <span class="text-white font-bold text-lg"><?= e($appName) ?></span>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <?php
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $navItems = [
            ['href' => '/dashboard',  'label' => 'Dashboard',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['href' => '/deals',      'label' => 'Negocios',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['href' => '/clients',    'label' => 'Clientes',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['href' => '/kanban',     'label' => 'Kanban',     'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
        ];
        if (\Core\Auth::is('admin', 'manager')) {
            $navItems[] = ['href' => '/reports', 'label' => 'Reportes', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'];
        }
        if (\Core\Auth::is('admin')) {
            $navItems[] = ['href' => '/admin/users',     'label' => 'Usuarios',   'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'];
            $navItems[] = ['href' => '/admin/verticals', 'label' => 'Verticales', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'];
        }

        foreach ($navItems as $item):
            $active = str_starts_with($currentPath, $item['href']) && $item['href'] !== '/dashboard'
                || ($item['href'] === '/dashboard' && in_array($currentPath, ['/', '/dashboard']));
            $cls = $active
                ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white'
                : 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors';
        ?>
            <a href="<?= url($item['href']) ?>" class="<?= $cls ?>">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
                </svg>
                <?= e($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- User info -->
    <div class="px-3 py-4 border-t border-gray-700">
        <?php $u = \Core\Auth::user(); ?>
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-bold"><?= initials($u['name'] ?? 'U') ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= e($u['name'] ?? '') ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= e($u['role'] ?? '') ?></p>
            </div>
        </div>
        <a href="<?= url('/logout') ?>"
           class="flex items-center gap-3 mt-1 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Cerrar sesión
        </a>
    </div>
</aside>

<!-- Main content -->
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
        <h1 class="text-xl font-semibold text-gray-900"><?= e($title ?? '') ?></h1>
        <div id="flash-area">
            <?php if ($msg = \Core\Session::getFlash('success')): ?>
                <div class="flash-msg px-4 py-2 rounded-lg bg-green-100 text-green-800 text-sm"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \Core\Session::getFlash('error')): ?>
                <div class="flash-msg px-4 py-2 rounded-lg bg-red-100 text-red-800 text-sm"><?= $msg ?></div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-6">
        <?= $content ?>
    </main>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>
</body>
</html>

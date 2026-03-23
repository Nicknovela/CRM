<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Load .env file if it exists
if (file_exists(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
        $_ENV[trim($k)] = trim($v);
    }
}

// Timezone
$appCfg = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appCfg['timezone']);

// Session
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => $appCfg['session_lifetime'],
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Autoloader
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once BASE_PATH . '/src/Core/helpers.php';

use Core\Router;
use Core\Auth;

$router = new Router();

// ─── Authentication ────────────────────────────────────────────────
$router->get('/login',  'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// ─── Dashboard ─────────────────────────────────────────────────────
$router->get('/',          'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// ─── Deals ─────────────────────────────────────────────────────────
$router->get('/deals',              'DealController@index');
$router->get('/deals/new',          'DealController@create');
$router->post('/deals/new',         'DealController@store');
$router->get('/deals/{id}',         'DealController@show');
$router->get('/deals/{id}/edit',    'DealController@edit');
$router->post('/deals/{id}/edit',   'DealController@update');
$router->post('/deals/{id}/delete', 'DealController@destroy');

// ─── Clients ───────────────────────────────────────────────────────
$router->get('/clients',              'ClientController@index');
$router->get('/clients/new',          'ClientController@create');
$router->post('/clients/new',         'ClientController@store');
$router->get('/clients/{id}',         'ClientController@show');
$router->get('/clients/{id}/edit',    'ClientController@edit');
$router->post('/clients/{id}/edit',   'ClientController@update');
$router->post('/clients/{id}/delete', 'ClientController@destroy');

// ─── Kanban ────────────────────────────────────────────────────────
$router->get('/kanban', 'KanbanController@index');

// ─── Reports ───────────────────────────────────────────────────────
$router->get('/reports',            'ReportController@index');
$router->get('/reports/export/csv', 'ReportController@exportCsv');
$router->get('/reports/export/pdf', 'ReportController@exportPdf');

// ─── Admin ─────────────────────────────────────────────────────────
$router->get('/admin/users',                'Admin/UserController@index');
$router->post('/admin/users/create',        'Admin/UserController@store');
$router->post('/admin/users/{id}/edit',     'Admin/UserController@update');
$router->post('/admin/users/{id}/delete',   'Admin/UserController@destroy');
$router->post('/admin/users/{id}/toggle',   'Admin/UserController@toggle');

$router->get('/admin/verticals',                     'Admin/VerticalController@index');
$router->post('/admin/verticals/create',             'Admin/VerticalController@store');
$router->post('/admin/verticals/{id}/edit',          'Admin/VerticalController@update');
$router->post('/admin/verticals/{id}/delete',        'Admin/VerticalController@destroy');
$router->post('/admin/verticals/{id}/stages/create', 'Admin/VerticalController@storeStage');
$router->post('/admin/stages/{id}/edit',             'Admin/VerticalController@updateStage');
$router->post('/admin/stages/{id}/delete',           'Admin/VerticalController@destroyStage');
$router->post('/admin/stages/reorder',               'Admin/VerticalController@reorderStages');

// ─── Internal API (JSON) ───────────────────────────────────────────
$router->get('/api/stages',           'Api/StageController@byVertical');
$router->post('/api/deals/{id}/move', 'Api/DealController@move');
$router->get('/api/metrics',          'Api/DealController@metrics');
$router->post('/api/activities',      'Api/DealController@addActivity');

// ─── Dispatch ─────────────────────────────────────────────────────
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);

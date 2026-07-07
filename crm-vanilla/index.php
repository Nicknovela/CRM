<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Autoloader
spl_autoload_register(function (string $class): void {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once BASE_PATH . '/src/Core/helpers.php';

// .env — se carga en $_ENV (putenv suele estar deshabilitado en hosting compartido)
load_env(BASE_PATH . '/.env');

// Errores: nunca mostrarlos al visitante en producción; registrarlos en storage/logs
$debug = env('APP_DEBUG', 'false') === 'true';
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
$logDir = BASE_PATH . '/storage/logs';
if (is_dir($logDir) || @mkdir($logDir, 0755, true)) {
    ini_set('error_log', $logDir . '/php_errors.log');
}

// Config + zona horaria
$appCfg = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appCfg['timezone']);

// Cabeceras de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; font-src 'self' data:; img-src 'self' data:; connect-src 'self'");

// Sesión — la cookie se limita a la ruta de la app si vive en un subdirectorio
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => $appCfg['session_lifetime'],
    'path'     => app_base_path() ?: '/',
    'secure'   => is_https(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

use Core\Router;

$router = new Router();

// ─── Authentication ────────────────────────────────────────────────
$router->get('/login',   'AuthController@showLogin');
$router->post('/login',  'AuthController@login');
$router->post('/logout', 'AuthController@logout');

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
// Se quita el prefijo del subdirectorio para que las rutas coincidan
// tanto en https://dominio.com como en https://dominio.com/crm
$uri      = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = app_base_path();
if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}

$router->dispatch($uri, $_SERVER['REQUEST_METHOD'] ?? 'GET');

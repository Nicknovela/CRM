<?php

use Core\Auth;
use Core\Session;

// ─── Entorno ───────────────────────────────────────────────────────
// En hosting compartido (Hostinger) putenv() suele estar deshabilitado,
// así que las variables se guardan en $_ENV y se leen con env().

function load_env(string $file): void
{
    if (!is_file($file) || !is_readable($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (strlen($v) > 1 && ($v[0] === '"' || $v[0] === "'") && str_ends_with($v, $v[0])) {
            $v = substr($v, 1, -1);
        }
        $_ENV[$k] = $v;
    }
}

function env(string $key, mixed $default = null): mixed
{
    if (array_key_exists($key, $_ENV)) return $_ENV[$key];
    $v = getenv($key);
    return $v !== false ? $v : $default;
}

// ─── URLs ──────────────────────────────────────────────────────────
// base_url() se autodetecta si APP_URL no está definida, y funciona
// tanto en la raíz del dominio como en un subdirectorio de public_html.

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function app_base_path(): string
{
    static $dir = null;
    if ($dir === null) {
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }
    return $dir; // '' en la raíz, '/subcarpeta' si está en un subdirectorio
}

function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $base = rtrim((string) env('APP_URL', ''), '/');
        if ($base === '') {
            $scheme = is_https() ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base   = $scheme . '://' . $host . app_base_path();
        }
    }
    return $base;
}

function redirect(string $path): never
{
    header('Location: ' . base_url() . $path);
    exit;
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    static $version = null;
    if ($version === null) {
        $version = (string) env('ASSET_VERSION', '1');
    }
    return url('assets/' . ltrim($path, '/')) . '?v=' . rawurlencode($version);
}

// ─── Escapado / CSRF ───────────────────────────────────────────────

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
        http_response_code(419);
        if (is_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            exit(json_encode(['error' => 'CSRF token mismatch']));
        }
        exit('CSRF token mismatch.');
    }
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

// ─── Sesión / autenticación ────────────────────────────────────────

function flash(string $key): ?string
{
    return Session::getFlash($key);
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::old($key, $default);
}

function auth(): ?array
{
    return Auth::check() ? (Auth::user() ?: null) : null;
}

function is_role(string ...$roles): bool
{
    return Auth::is(...$roles);
}

function can(string $role): bool
{
    return Auth::is($role, 'admin');
}

// ─── Formato ───────────────────────────────────────────────────────

function format_money(float $amount, string $currency = 'BOB'): string
{
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

function format_date(?string $date): string
{
    if (!$date) return '—';
    return date('d/m/Y', strtotime($date));
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'hace ' . $diff . 's';
    if ($diff < 3600)   return 'hace ' . floor($diff / 60) . 'm';
    if ($diff < 86400)  return 'hace ' . floor($diff / 3600) . 'h';
    if ($diff < 604800) return 'hace ' . floor($diff / 86400) . 'd';
    return date('d/m/Y', strtotime($datetime));
}

function initials(string $name): string
{
    $words = explode(' ', trim($name));
    $ini = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $ini .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $ini;
}

function stage_badge(string $name, string $color): string
{
    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white" style="background-color:' . e($color) . '">' . e($name) . '</span>';
}

function activity_icon(string $type): string
{
    return match($type) {
        'note'         => '📝',
        'stage_change' => '🔄',
        'call'         => '📞',
        'email'        => '📧',
        'meeting'      => '🤝',
        default        => '📌',
    };
}

function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): array
{
    $totalPages = (int) ceil($total / $perPage);
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => ($currentPage - 1) * $perPage,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
        'base_url'    => $baseUrl,
    ];
}

// ─── Request ───────────────────────────────────────────────────────

function request(string $key, mixed $default = null): mixed
{
    return $_REQUEST[$key] ?? $default;
}

function input(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function query(string $key, mixed $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

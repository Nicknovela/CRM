<?php

use Core\Auth;
use Core\Session;

function redirect(string $path): never
{
    $base = rtrim(getenv('APP_URL') ?: '', '/');
    header('Location: ' . $base . $path);
    exit;
}

function url(string $path = ''): string
{
    $base = rtrim(getenv('APP_URL') ?: '', '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

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
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token mismatch.');
    }
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
}

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
    return Auth::check() ? Auth::user() : null;
}

function is_role(string ...$roles): bool
{
    return Auth::is(...$roles);
}

function can(string $role): bool
{
    return Auth::is($role, 'admin');
}

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

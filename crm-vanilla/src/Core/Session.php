<?php

namespace Core;

class Session
{
    public static function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }

    public static function putOld(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['old_input']);
    }
}

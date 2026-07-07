<?php

namespace Core;

class Auth
{
    // Cache por petición: evita repetir el SELECT del usuario en cada llamada
    private static array|false|null $cachedUser = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1",
            [strtolower(trim($email))]
        );
        if ($user && password_verify($password, $user['password'])) {
            self::login($user);
            return true;
        }
        return false;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        self::$cachedUser = $user;
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): array|false
    {
        if (!self::check()) return false;
        if (self::$cachedUser === null) {
            self::$cachedUser = Database::fetchOne(
                "SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1",
                [$_SESSION['user_id']]
            );
        }
        return self::$cachedUser;
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Debes iniciar sesión.');
            redirect('/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!self::is(...$roles)) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Acceso denegado']);
            exit;
        }
    }
}

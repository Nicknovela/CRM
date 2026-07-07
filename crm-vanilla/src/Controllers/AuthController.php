<?php

namespace Controllers;

use Core\Auth;
use Core\Session;
use Core\View;

class AuthController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutos

    public function showLogin(): void
    {
        if (Auth::check()) redirect('/dashboard');
        View::render('auth/login', ['title' => 'Iniciar sesión', 'layout' => 'auth']);
    }

    public function login(): void
    {
        verify_csrf();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (($wait = $this->lockoutRemaining($ip)) > 0) {
            Session::flash('error', 'Demasiados intentos fallidos. Intenta de nuevo en ' . ceil($wait / 60) . ' minuto(s).');
            redirect('/login');
        }

        $email    = trim(input('email', ''));
        $password = input('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Por favor ingresa tu correo y contraseña.');
            Session::putOld(['email' => $email]);
            redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $this->clearAttempts($ip);
            Session::clearOld();
            Session::flash('success', '¡Bienvenido!');
            redirect('/dashboard');
        }

        $this->recordFailure($ip);
        Session::flash('error', 'Credenciales incorrectas. Verifica tu correo y contraseña.');
        Session::putOld(['email' => $email]);
        redirect('/login');
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        redirect('/login');
    }

    // ─── Rate limiting por IP en archivos ──────────────────────────
    // No usa la sesión (se evade borrando la cookie) ni APCu/Redis
    // (no disponibles en hosting compartido).

    private function throttleFile(string $ip): string
    {
        $dir = BASE_PATH . '/storage/cache';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir . '/login_' . md5($ip) . '.json';
    }

    private function readAttempts(string $ip): array
    {
        $file = $this->throttleFile($ip);
        if (!is_file($file)) return ['count' => 0, 'first' => time()];
        $data = json_decode((string) @file_get_contents($file), true) ?: [];
        $data += ['count' => 0, 'first' => time()];
        // La ventana expiró: empezar de cero
        if (time() - $data['first'] > self::LOCKOUT_SECONDS) {
            return ['count' => 0, 'first' => time()];
        }
        return $data;
    }

    private function lockoutRemaining(string $ip): int
    {
        $data = $this->readAttempts($ip);
        if ($data['count'] < self::MAX_ATTEMPTS) return 0;
        return max(0, self::LOCKOUT_SECONDS - (time() - $data['first']));
    }

    private function recordFailure(string $ip): void
    {
        $data = $this->readAttempts($ip);
        $data['count']++;
        @file_put_contents($this->throttleFile($ip), json_encode($data), LOCK_EX);
    }

    private function clearAttempts(string $ip): void
    {
        @unlink($this->throttleFile($ip));
    }
}

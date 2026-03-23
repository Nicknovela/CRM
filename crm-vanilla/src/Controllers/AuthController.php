<?php

namespace Controllers;

use Core\Auth;
use Core\Session;
use Core\View;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) redirect('/dashboard');
        View::render('auth/login', ['title' => 'Iniciar sesión', 'layout' => 'auth']);
    }

    public function login(): void
    {
        verify_csrf();
        $email    = trim(input('email', ''));
        $password = input('password', '');

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Por favor ingresa tu correo y contraseña.');
            Session::putOld(['email' => $email]);
            redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            Session::clearOld();
            Session::flash('success', '¡Bienvenido!');
            redirect('/dashboard');
        }

        Session::flash('error', 'Credenciales incorrectas. Verifica tu correo y contraseña.');
        Session::putOld(['email' => $email]);
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }
}

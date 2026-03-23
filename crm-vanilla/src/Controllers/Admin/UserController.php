<?php

namespace Controllers\Admin;

use Core\Auth;
use Core\Session;
use Core\View;
use Models\User;

class UserController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        $users = User::all(['search' => query('search')]);

        View::render('admin/users', [
            'title' => 'Gestión de usuarios',
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');
        verify_csrf();

        $data   = $this->inputData();
        $errors = $this->validate($data);
        if (empty($data['password'])) $errors[] = 'La contraseña es requerida al crear.';

        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            redirect('/admin/users');
        }

        User::create($data);
        Session::flash('success', 'Usuario creado.');
        redirect('/admin/users');
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();

        $data   = $this->inputData();
        $errors = $this->validate($data, (int) $id);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            redirect('/admin/users');
        }

        User::update((int) $id, $data);
        Session::flash('success', 'Usuario actualizado.');
        redirect('/admin/users');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();
        if ((int) $id === Auth::id()) {
            Session::flash('error', 'No puedes eliminar tu propia cuenta.');
            redirect('/admin/users');
        }
        User::delete((int) $id);
        Session::flash('success', 'Usuario eliminado.');
        redirect('/admin/users');
    }

    public function toggle(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();
        $user = User::find((int) $id);
        if ($user) {
            User::update((int) $id, array_merge($user, ['is_active' => $user['is_active'] ? 0 : 1]));
        }
        Session::flash('success', 'Estado actualizado.');
        redirect('/admin/users');
    }

    private function inputData(): array
    {
        return [
            'name'      => trim(input('name')),
            'email'     => trim(input('email')),
            'password'  => input('password'),
            'role'      => input('role', 'vendedor'),
            'timezone'  => input('timezone', 'America/La_Paz'),
            'is_active' => input('is_active', 1),
        ];
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if (empty($data['name']))  $errors[] = 'El nombre es requerido.';
        if (empty($data['email'])) $errors[] = 'El correo es requerido.';
        if (!in_array($data['role'], ['admin','manager','vendedor','viewer'])) {
            $errors[] = 'Rol inválido.';
        }
        return $errors;
    }
}

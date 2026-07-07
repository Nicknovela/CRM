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
            Session::flash('error', implode(' ', $errors));
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
            Session::flash('error', implode(' ', $errors));
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
        if (!User::delete((int) $id)) {
            Session::flash('error', 'No se puede eliminar: el usuario tiene negocios asignados.');
        } else {
            Session::flash('success', 'Usuario eliminado.');
        }
        redirect('/admin/users');
    }

    public function toggle(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();
        if ((int) $id === Auth::id()) {
            Session::flash('error', 'No puedes desactivar tu propia cuenta.');
            redirect('/admin/users');
        }
        $user = User::find((int) $id);
        if ($user) {
            // Solo se toca is_active: pasar el hash por update() lo re-hashearía
            // y corrompería la contraseña del usuario.
            User::setActive((int) $id, !$user['is_active']);
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
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo no es válido.';
        }
        if (!in_array($data['role'], ['admin', 'manager', 'vendedor', 'viewer'], true)) {
            $errors[] = 'Rol inválido.';
        }
        if (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (!empty($data['email'])) {
            $existing = User::findByEmail($data['email']);
            if ($existing && (int) $existing['id'] !== $excludeId) {
                $errors[] = 'El correo ya está en uso.';
            }
        }
        return $errors;
    }
}

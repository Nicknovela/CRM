<?php

namespace Controllers;

use Core\Auth;
use Core\Session;
use Core\View;
use Models\Client;

class ClientController
{
    public function index(): void
    {
        Auth::requireAuth();
        $filters = ['search' => query('search')];
        $page    = max(1, (int) query('page', 1));
        $perPage = 20;
        $total   = Client::count($filters);
        $pager   = paginate($total, $perPage, $page, '/clients');
        $clients = Client::all($filters, $perPage, $pager['offset']);

        View::render('clients/index', [
            'title'   => 'Clientes',
            'clients' => $clients,
            'filters' => $filters,
            'pager'   => $pager,
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) { View::render('errors/403', ['title' => '403']); return; }

        View::render('clients/form', [
            'title'  => 'Nuevo cliente',
            'client' => null,
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) redirect('/clients');
        verify_csrf();

        $data   = $this->inputData();
        $errors = $this->validate($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::putOld($data);
            redirect('/clients/new');
        }

        $id = Client::create($data);
        Session::flash('success', 'Cliente creado.');
        redirect('/clients/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireAuth();
        $client = $this->findOrFail((int) $id);

        View::render('clients/show', [
            'title'  => $client['name'],
            'client' => $client,
            'deals'  => Client::deals((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) { View::render('errors/403', ['title' => '403']); return; }
        $client = $this->findOrFail((int) $id);

        View::render('clients/form', [
            'title'  => 'Editar cliente',
            'client' => $client,
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) redirect('/clients');
        verify_csrf();
        $this->findOrFail((int) $id);

        $data   = $this->inputData();
        $errors = $this->validate($data, (int) $id);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::putOld($data);
            redirect("/clients/{$id}/edit");
        }

        Client::update((int) $id, $data);
        Session::flash('success', 'Cliente actualizado.');
        redirect('/clients/' . $id);
    }

    public function destroy(string $id): void
    {
        Auth::requireAuth();
        Auth::requireRole('admin', 'manager');
        verify_csrf();
        Client::delete((int) $id);
        Session::flash('success', 'Cliente eliminado.');
        redirect('/clients');
    }

    private function inputData(): array
    {
        return [
            'name'         => trim(input('name')),
            'company_name' => trim(input('company_name')),
            'email'        => trim(input('email')),
            'phone'        => trim(input('phone')),
            'industry'     => trim(input('industry')),
            'website'      => trim(input('website')),
            'address'      => trim(input('address')),
            'notes'        => trim(input('notes')),
        ];
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'El nombre del cliente es requerido.';
        }
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo no es válido.';
            }
        }
        return $errors;
    }

    private function findOrFail(int $id): array
    {
        $client = Client::find($id);
        if (!$client) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Cliente no encontrado']);
            exit;
        }
        return $client;
    }
}

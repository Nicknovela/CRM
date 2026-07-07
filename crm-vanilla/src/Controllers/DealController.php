<?php

namespace Controllers;

use Core\Auth;
use Core\Session;
use Core\View;
use Models\Deal;
use Models\Client;
use Models\Vertical;
use Models\Stage;
use Models\Activity;
use Models\User;

class DealController
{
    public function index(): void
    {
        Auth::requireAuth();
        $userId = Auth::id();
        $role   = Auth::role();

        $filters = [
            'search'      => query('search'),
            'vertical_id' => query('vertical_id'),
            'stage_id'    => query('stage_id'),
            'assigned_to' => query('assigned_to'),
            'sort'        => query('sort', 'created_at'),
            'dir'         => query('dir', 'desc'),
        ];

        $page    = max(1, (int) query('page', 1));
        $perPage = 20;
        $total   = Deal::count($filters, $userId, $role);
        $pager   = paginate($total, $perPage, $page, '/deals');
        $deals   = Deal::query($filters, $userId, $role, $perPage, $pager['offset']);

        View::render('deals/index', [
            'title'     => 'Negocios',
            'deals'     => $deals,
            'filters'   => $filters,
            'pager'     => $pager,
            'verticals' => Vertical::active(),
            'users'     => Auth::is('admin', 'manager') ? User::vendedores() : [],
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) { View::render('errors/403', ['title' => '403']); return; }

        View::render('deals/form', [
            'title'     => 'Nuevo negocio',
            'deal'      => null,
            'clients'   => Client::forSelect(),
            'verticals' => Vertical::active(),
            'users'     => User::vendedores(),
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) { redirect('/deals'); }
        verify_csrf();

        $data = [
            'title'               => trim(input('title')),
            'client_id'           => input('client_id'),
            'vertical_id'         => input('vertical_id'),
            'stage_id'            => input('stage_id'),
            'assigned_to'         => input('assigned_to') ?: Auth::id(),
            'amount'              => input('amount', 0),
            'currency'            => input('currency', 'BOB'),
            'probability'         => input('probability', 50),
            'commission_rate'     => input('commission_rate'),
            'expected_close_date' => input('expected_close_date'),
            'notes'               => input('notes'),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::putOld($data);
            redirect('/deals/new');
        }

        $id = Deal::create($data);
        Activity::log($id, Auth::id(), 'note', 'Negocio creado.');
        Session::flash('success', 'Negocio creado exitosamente.');
        redirect('/deals/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireAuth();
        $deal = $this->findOrFail((int)$id);

        View::render('deals/show', [
            'title'      => $deal['title'],
            'deal'       => $deal,
            'activities' => Activity::forDeal((int)$id),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) { View::render('errors/403', ['title' => '403']); return; }
        $deal = $this->findOrFail((int)$id);

        View::render('deals/form', [
            'title'     => 'Editar negocio',
            'deal'      => $deal,
            'clients'   => Client::forSelect(),
            'verticals' => Vertical::active(),
            'stages'    => Stage::byVertical((int) $deal['vertical_id']),
            'users'     => User::vendedores(),
        ]);
    }

    public function update(string $id): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) redirect('/deals');
        verify_csrf();
        $deal = $this->findOrFail((int)$id);

        $data = [
            'title'               => trim(input('title')),
            'client_id'           => input('client_id'),
            'vertical_id'         => input('vertical_id'),
            'stage_id'            => input('stage_id'),
            'assigned_to'         => input('assigned_to'),
            'amount'              => input('amount', 0),
            'currency'            => input('currency', 'BOB'),
            'probability'         => input('probability', 50),
            'commission_rate'     => input('commission_rate'),
            'expected_close_date' => input('expected_close_date'),
            'notes'               => input('notes'),
            'lost_reason'         => input('lost_reason'),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            Session::flash('error', implode('<br>', $errors));
            Session::putOld($data);
            redirect("/deals/{$id}/edit");
        }

        // Un solo UPDATE: se calculan aquí los efectos del cambio de etapa
        // en lugar de escribir dos veces la misma fila (moveToStage + update)
        if ((int)$data['stage_id'] !== (int)$deal['stage_id']) {
            $newStage = Stage::find((int)$data['stage_id']);
            if ($newStage) {
                if ($newStage['is_won']) {
                    $data['actual_close_date'] = date('Y-m-d');
                    $data['probability']       = 100;
                } elseif ($newStage['is_lost']) {
                    $data['probability'] = 0;
                }
                Activity::log(
                    (int)$id, Auth::id(), 'stage_change',
                    'Movido de etapa "' . ($deal['stage_name'] ?? '?') . '" a "' . $newStage['name'] . '"',
                    (int)$deal['stage_id'], (int)$data['stage_id']
                );
            }
        }
        Deal::update((int)$id, $data);
        Session::flash('success', 'Negocio actualizado.');
        redirect('/deals/' . $id);
    }

    public function destroy(string $id): void
    {
        Auth::requireAuth();
        Auth::requireRole('admin', 'manager');
        verify_csrf();
        Deal::softDelete((int)$id);
        Session::flash('success', 'Negocio eliminado.');
        redirect('/deals');
    }

    private function findOrFail(int $id): array
    {
        $deal = Deal::find($id);
        if (!$deal) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Negocio no encontrado']);
            exit;
        }
        // Vendedor can only see own deals
        if (Auth::is('vendedor') && $deal['assigned_to'] != Auth::id()) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Sin acceso']);
            exit;
        }
        return $deal;
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['title']))       $errors[] = 'El título es requerido.';
        if (empty($data['client_id']))   $errors[] = 'El cliente es requerido.';
        if (empty($data['vertical_id'])) $errors[] = 'La vertical es requerida.';
        if (empty($data['stage_id']))    $errors[] = 'La etapa es requerida.';
        return $errors;
    }
}

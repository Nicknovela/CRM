<?php

namespace Controllers\Admin;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Models\Vertical;
use Models\Stage;

class VerticalController
{
    public function index(): void
    {
        Auth::requireRole('admin', 'manager');
        $verticals = Vertical::all();

        // Todas las etapas en una consulta (antes: una consulta por vertical)
        $allStages = Database::fetchAll("SELECT * FROM stages ORDER BY vertical_id, position ASC");
        $stagesByVertical = [];
        foreach ($allStages as $stage) {
            $stagesByVertical[$stage['vertical_id']][] = $stage;
        }
        foreach ($verticals as &$v) {
            $v['stages'] = $stagesByVertical[$v['id']] ?? [];
        }
        unset($v);

        View::render('admin/verticals', [
            'title'     => 'Verticales y Etapas',
            'verticals' => $verticals,
        ]);
    }

    public function store(): void
    {
        Auth::requireRole('admin');
        verify_csrf();

        $data = [
            'name'             => trim(input('name')),
            'description'      => trim(input('description')),
            'color'            => input('color', '#3B82F6'),
            'is_active'        => input('is_active') ? 1 : 0,
            'track_commission' => input('track_commission') ? 1 : 0,
        ];

        if (empty($data['name'])) {
            Session::flash('error', 'El nombre es requerido.');
            redirect('/admin/verticals');
        }

        Vertical::create($data);
        Session::flash('success', 'Vertical creada.');
        redirect('/admin/verticals');
    }

    public function update(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();

        $data = [
            'name'             => trim(input('name')),
            'description'      => trim(input('description')),
            'color'            => input('color', '#3B82F6'),
            'is_active'        => input('is_active') ? 1 : 0,
            'track_commission' => input('track_commission') ? 1 : 0,
        ];

        Vertical::update((int) $id, $data);
        Session::flash('success', 'Vertical actualizada.');
        redirect('/admin/verticals');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole('admin');
        verify_csrf();

        if (!Vertical::delete((int) $id)) {
            Session::flash('error', 'No se puede eliminar: tiene negocios asociados.');
        } else {
            Session::flash('success', 'Vertical eliminada.');
        }
        redirect('/admin/verticals');
    }

    public function storeStage(string $verticalId): void
    {
        Auth::requireRole('admin', 'manager');
        verify_csrf();

        $data = [
            'vertical_id' => (int) $verticalId,
            'name'        => trim(input('name')),
            'color'       => input('color', '#6B7280'),
            'is_won'      => input('is_won') ? 1 : 0,
            'is_lost'     => input('is_lost') ? 1 : 0,
        ];

        if (empty($data['name'])) {
            Session::flash('error', 'El nombre de etapa es requerido.');
            redirect('/admin/verticals');
        }

        Stage::create($data);
        Session::flash('success', 'Etapa creada.');
        redirect('/admin/verticals');
    }

    public function updateStage(string $id): void
    {
        Auth::requireRole('admin', 'manager');
        verify_csrf();

        Stage::update((int) $id, [
            'name'   => trim(input('name')),
            'color'  => input('color', '#6B7280'),
            'is_won' => input('is_won') ? 1 : 0,
            'is_lost'=> input('is_lost') ? 1 : 0,
        ]);
        Session::flash('success', 'Etapa actualizada.');
        redirect('/admin/verticals');
    }

    public function destroyStage(string $id): void
    {
        Auth::requireRole('admin', 'manager');
        verify_csrf();

        if (!Stage::delete((int) $id)) {
            Session::flash('error', 'No se puede eliminar: hay negocios en esta etapa.');
        } else {
            Session::flash('success', 'Etapa eliminada.');
        }
        redirect('/admin/verticals');
    }

    public function reorderStages(): void
    {
        Auth::requireRole('admin', 'manager');
        verify_csrf();

        $ids = json_decode(input('ids', '[]'), true) ?: [];
        if ($ids) Stage::reorder($ids);

        View::json(['ok' => true]);
    }
}

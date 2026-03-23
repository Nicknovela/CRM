<?php

namespace Controllers;

use Core\Auth;
use Core\View;
use Models\Vertical;
use Models\Stage;
use Models\Deal;

class KanbanController
{
    public function index(): void
    {
        Auth::requireAuth();
        $verticals  = Vertical::active();
        $verticalId = (int) query('vertical_id', $verticals[0]['id'] ?? 0);

        $stages = [];
        if ($verticalId) {
            foreach (Stage::byVertical($verticalId) as $stage) {
                $stage['deals'] = Deal::byStage($stage['id'], Auth::id(), Auth::role());
                $stages[] = $stage;
            }
        }

        View::render('kanban/index', [
            'title'      => 'Kanban',
            'verticals'  => $verticals,
            'stages'     => $stages,
            'verticalId' => $verticalId,
        ]);
    }
}

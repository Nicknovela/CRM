<?php

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Models\Vertical;
use Models\Stage;

class KanbanController
{
    public function index(): void
    {
        Auth::requireAuth();
        $verticals  = Vertical::active();
        $verticalId = (int) query('vertical_id', $verticals[0]['id'] ?? 0);

        $stages = [];
        if ($verticalId) {
            $stages = Stage::byVertical($verticalId);

            // Una sola consulta para todos los negocios de la vertical
            // (antes era una consulta por etapa)
            $params = [$verticalId];
            $filter = '';
            if (Auth::role() === 'vendedor') {
                $filter = "AND d.assigned_to = ?";
                $params[] = Auth::id();
            }
            $allDeals = Database::fetchAll(
                "SELECT d.*, c.name as client_name, c.company_name, u.name as assigned_name
                 FROM deals d
                 JOIN clients c ON c.id = d.client_id
                 LEFT JOIN users u ON u.id = d.assigned_to
                 WHERE d.vertical_id = ? AND d.deleted_at IS NULL $filter
                 ORDER BY d.updated_at DESC",
                $params
            );

            $dealsByStage = [];
            foreach ($allDeals as $deal) {
                $dealsByStage[$deal['stage_id']][] = $deal;
            }
            foreach ($stages as &$stage) {
                $stage['deals'] = $dealsByStage[$stage['id']] ?? [];
            }
            unset($stage);
        }

        View::render('kanban/index', [
            'title'      => 'Kanban',
            'verticals'  => $verticals,
            'stages'     => $stages,
            'verticalId' => $verticalId,
        ]);
    }
}

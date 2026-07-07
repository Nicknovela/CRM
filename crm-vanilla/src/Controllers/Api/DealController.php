<?php

namespace Controllers\Api;

use Core\Auth;
use Core\View;
use Models\Deal;
use Models\Activity;

class DealController
{
    public function move(string $id): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) View::json(['error' => 'Forbidden'], 403);
        verify_csrf();

        // Liberar el lock de sesión: evita que otras peticiones AJAX queden bloqueadas
        session_write_close();

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $stageId = (int) ($body['stage_id'] ?? 0);
        $dealId  = (int) $id;

        if (!$stageId) View::json(['error' => 'stage_id required'], 400);

        // Un vendedor solo puede mover sus propios negocios
        if (Auth::is('vendedor')) {
            $deal = Deal::find($dealId);
            if (!$deal || (int) $deal['assigned_to'] !== Auth::id()) {
                View::json(['error' => 'Forbidden'], 403);
            }
        }

        Deal::moveToStage($dealId, $stageId, Auth::id());
        View::json(['ok' => true]);
    }

    public function metrics(): void
    {
        Auth::requireAuth();
        session_write_close();

        $period     = (string) query('period', '30');
        $verticalId = (int) query('vertical_id', 0);
        View::json(Deal::metrics($verticalId, $period));
    }

    public function addActivity(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) View::json(['error' => 'Forbidden'], 403);
        verify_csrf();
        session_write_close();

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $dealId = (int) ($body['deal_id'] ?? 0);
        $type   = $body['type'] ?? 'note';
        $desc   = trim($body['description'] ?? '');

        if (!$dealId || !$desc) View::json(['error' => 'deal_id and description required'], 400);

        // Un vendedor solo puede registrar actividad en sus propios negocios
        if (Auth::is('vendedor')) {
            $deal = Deal::find($dealId);
            if (!$deal || (int) $deal['assigned_to'] !== Auth::id()) {
                View::json(['error' => 'Forbidden'], 403);
            }
        }

        Activity::log($dealId, Auth::id(), $type, $desc);
        View::json(['ok' => true]);
    }
}

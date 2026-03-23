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

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $stageId = (int) ($body['stage_id'] ?? 0);

        if (!$stageId) View::json(['error' => 'stage_id required'], 400);

        Deal::moveToStage((int) $id, $stageId, Auth::id());
        View::json(['ok' => true]);
    }

    public function metrics(): void
    {
        Auth::requireAuth();
        $period     = (string) query('period', '30');
        $verticalId = (int) query('vertical_id', 0);
        View::json(Deal::metrics($verticalId, $period));
    }

    public function addActivity(): void
    {
        Auth::requireAuth();
        if (Auth::is('viewer')) View::json(['error' => 'Forbidden'], 403);

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $dealId = (int) ($body['deal_id'] ?? 0);
        $type   = $body['type'] ?? 'note';
        $desc   = trim($body['description'] ?? '');

        if (!$dealId || !$desc) View::json(['error' => 'deal_id and description required'], 400);

        Activity::log($dealId, Auth::id(), $type, $desc);
        View::json(['ok' => true]);
    }
}

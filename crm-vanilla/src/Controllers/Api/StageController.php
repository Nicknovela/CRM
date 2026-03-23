<?php

namespace Controllers\Api;

use Core\Auth;
use Core\View;
use Models\Stage;

class StageController
{
    public function byVertical(): void
    {
        Auth::requireAuth();
        $verticalId = (int) query('vertical_id', 0);
        if (!$verticalId) {
            View::json(['error' => 'vertical_id required'], 400);
        }
        View::json(Stage::byVertical($verticalId));
    }
}

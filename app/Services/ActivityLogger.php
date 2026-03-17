<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Deal;

class ActivityLogger
{
    public static function log(
        Deal $deal,
        string $type,
        string $description,
        ?int $oldStageId = null,
        ?int $newStageId = null
    ): Activity {
        return $deal->activities()->create([
            'user_id' => auth()->id(),
            'type' => $type,
            'description' => $description,
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $newStageId,
        ]);
    }
}

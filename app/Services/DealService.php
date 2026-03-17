<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Stage;
use Carbon\Carbon;

class DealService
{
    public function moveToStage(Deal $deal, int $stageId, ?string $lostReason = null): Deal
    {
        $stage = Stage::findOrFail($stageId);

        $deal->stage_id = $stageId;

        if ($stage->is_won) {
            $deal->probability = 100;
            $deal->actual_close_date ??= Carbon::today();
        } elseif ($stage->is_lost) {
            $deal->probability = 0;
            $deal->actual_close_date ??= Carbon::today();
            if ($lostReason) {
                $deal->lost_reason = $lostReason;
            }
        }

        $deal->save();

        return $deal;
    }

    public function restore(Deal $deal): Deal
    {
        $deal->restore();
        return $deal;
    }

    public function forceDelete(Deal $deal): void
    {
        $deal->forceDelete();
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealClosingSoon;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendClosingSoonNotifications extends Command
{
    protected $signature = 'notifications:deals-closing-soon {--days=3 : How many days before closing}';
    protected $description = 'Send notifications for deals closing soon';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $targetDate = Carbon::today()->addDays($days);

        $deals = Deal::with(['assignedTo'])
            ->whereDate('expected_close_date', $targetDate)
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->get();

        foreach ($deals as $deal) {
            if ($deal->assignedTo) {
                $deal->assignedTo->notify(new DealClosingSoon($deal, $days));
            }
        }

        $this->info("Sent notifications for {$deals->count()} deals closing in {$days} days.");
    }
}

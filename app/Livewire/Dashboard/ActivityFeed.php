<?php

namespace App\Livewire\Dashboard;

use App\Models\Activity;
use App\Models\Deal;
use Livewire\Component;

class ActivityFeed extends Component
{
    public function getActivitiesProperty()
    {
        $dealIds = Deal::forUser(auth()->user())->pluck('id');

        return Activity::whereIn('deal_id', $dealIds)
            ->with(['user', 'deal.client'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.activity-feed', [
            'activities' => $this->activities,
        ]);
    }
}

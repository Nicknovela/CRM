<?php

namespace Controllers;

use Core\Auth;
use Core\View;
use Models\Deal;
use Models\Vertical;
use Models\Activity;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();
        $user      = Auth::user();
        $userId    = Auth::id();
        $role      = Auth::role();

        $period     = (int) query('period', 30);
        $verticalId = (int) query('vertical_id', 0);

        $metrics   = Deal::metrics($verticalId, (string) $period);
        $funnel    = Deal::funnelData($verticalId);
        $upcoming  = Deal::upcoming(7, $userId, $role);
        $activities = Activity::recent($userId, $role, 10);
        $verticals = Vertical::active();

        View::render('dashboard/index', [
            'title'      => 'Dashboard',
            'metrics'    => $metrics,
            'funnel'     => $funnel,
            'upcoming'   => $upcoming,
            'activities' => $activities,
            'verticals'  => $verticals,
            'period'     => $period,
            'verticalId' => $verticalId,
            'user'       => $user,
        ]);
    }
}

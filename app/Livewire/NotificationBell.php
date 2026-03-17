<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public bool $showDropdown = false;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
        $this->unreadCount = 0;
        $this->showDropdown = false;
    }

    public function render()
    {
        $notifications = auth()->user()
            ?->notifications()
            ->latest()
            ->take(10)
            ->get() ?? collect();

        return view('livewire.notification-bell', compact('notifications'));
    }
}

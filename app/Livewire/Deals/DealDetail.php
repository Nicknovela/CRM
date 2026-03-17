<?php

namespace App\Livewire\Deals;

use App\Models\Activity;
use App\Models\Deal;
use App\Services\ActivityLogger;
use Livewire\Component;

class DealDetail extends Component
{
    public int $dealId;
    public ?Deal $deal = null;
    public bool $showActivityForm = false;
    public array $activityForm = [
        'type'        => 'note',
        'description' => '',
    ];

    protected array $rules = [
        'activityForm.type'        => 'required|in:note,call,email,meeting',
        'activityForm.description' => 'required|string|max:2000',
    ];

    public function mount(int $dealId): void
    {
        $this->dealId = $dealId;
        $this->loadDeal();
    }

    protected function loadDeal(): void
    {
        $this->deal = Deal::with([
            'client',
            'vertical',
            'stage',
            'assignedTo',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ])->findOrFail($this->dealId);
    }

    public function saveActivity(): void
    {
        $this->validate();

        ActivityLogger::log(
            $this->deal,
            $this->activityForm['type'],
            $this->activityForm['description']
        );

        $this->activityForm = ['type' => 'note', 'description' => ''];
        $this->showActivityForm = false;
        $this->loadDeal();
        session()->flash('success', 'Actividad registrada.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.deals.deal-detail');
    }
}

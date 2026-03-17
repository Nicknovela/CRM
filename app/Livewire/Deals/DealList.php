<?php

namespace App\Livewire\Deals;

use App\Models\Deal;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vertical;
use Livewire\Component;
use Livewire\WithPagination;

class DealList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterVertical = '';
    public string $filterStage = '';
    public string $filterAssignee = '';
    public int $perPage = 15;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterVertical(): void
    {
        $this->filterStage = '';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }

    public function render(): \Illuminate\View\View
    {
        $user = auth()->user();

        $deals = Deal::forUser($user)
            ->with(['client', 'stage', 'vertical', 'assignedTo'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterVertical, fn ($q) => $q->where('vertical_id', $this->filterVertical))
            ->when($this->filterStage, fn ($q) => $q->where('stage_id', $this->filterStage))
            ->when($this->filterAssignee, fn ($q) => $q->where('assigned_to', $this->filterAssignee))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $verticals = Vertical::active()->orderBy('name')->get();
        $stages    = $this->filterVertical
            ? Stage::where('vertical_id', $this->filterVertical)->ordered()->get()
            : collect();
        $assignees = User::active()->orderBy('name')->get();

        return view('livewire.deals.deal-list', [
            'deals'     => $deals,
            'verticals' => $verticals,
            'stages'    => $stages,
            'assignees' => $assignees,
        ]);
    }
}

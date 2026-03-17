<?php

namespace App\Livewire\Kanban;

use App\Models\Deal;
use App\Models\Stage;
use App\Models\Vertical;
use App\Services\ActivityLogger;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class KanbanBoard extends Component
{
    public ?int $selectedVerticalId = null;
    public $verticals;

    public function mount(): void
    {
        $this->verticals = Vertical::active()->orderBy('name')->get();
        $this->selectedVerticalId = $this->verticals->first()?->id;
    }

    #[Computed]
    public function stages()
    {
        if (!$this->selectedVerticalId) {
            return collect();
        }

        $user = auth()->user();

        return Stage::where('vertical_id', $this->selectedVerticalId)
            ->ordered()
            ->with([
                'deals' => fn ($q) => Deal::forUser($user, $q)
                    ->with(['client', 'assignedTo'])
                    ->orderByDesc('updated_at'),
            ])
            ->get();
    }

    public function selectVertical(int $id): void
    {
        $this->selectedVerticalId = $id;
        unset($this->stages);
    }

    #[On('deal-moved')]
    public function dealMoved(int $dealId, int $stageId): void
    {
        $deal = Deal::findOrFail($dealId);
        $oldStage = $deal->stage;
        $newStage = Stage::findOrFail($stageId);

        $deal->update(['stage_id' => $stageId]);

        ActivityLogger::log(
            $deal,
            'stage_change',
            sprintf('Etapa cambiada de "%s" a "%s" (Kanban)', $oldStage?->name, $newStage->name),
            $oldStage?->id,
            $stageId
        );

        unset($this->stages);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.kanban.kanban-board');
    }
}

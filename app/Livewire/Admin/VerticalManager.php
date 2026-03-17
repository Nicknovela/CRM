<?php

namespace App\Livewire\Admin;

use App\Models\Stage;
use App\Models\Vertical;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VerticalManager extends Component
{
    // Vertical list & form
    public $verticals = [];
    public ?int $editingId = null;
    public array $form = [
        'name'             => '',
        'description'      => '',
        'color'            => '#6366f1',
        'track_commission' => false,
    ];
    public bool $showForm = false;

    // Stage management
    public ?int $selectedVerticalId = null;
    public array $stageForm = [
        'name'  => '',
        'color' => '#6366f1',
    ];

    // Delete confirmation
    public ?int $confirmingDeleteId = null;

    protected array $rules = [
        'form.name'             => 'required|string|max:100',
        'form.description'      => 'nullable|string|max:500',
        'form.color'            => 'required|string|size:7',
        'form.track_commission' => 'boolean',
        'stageForm.name'        => 'required|string|max:100',
        'stageForm.color'       => 'required|string|size:7',
    ];

    public function mount(): void
    {
        $this->loadVerticals();
    }

    protected function loadVerticals(): void
    {
        $this->verticals = Vertical::withCount('deals')
            ->with(['stages' => fn ($q) => $q->ordered()])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function showCreateForm(): void
    {
        $this->reset(['editingId', 'form']);
        $this->form = [
            'name'             => '',
            'description'      => '',
            'color'            => '#6366f1',
            'track_commission' => false,
        ];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $vertical = Vertical::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'name'             => $vertical->name,
            'description'      => $vertical->description ?? '',
            'color'            => $vertical->color,
            'track_commission' => (bool) $vertical->track_commission,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validateOnly('form.*');

        if ($this->editingId) {
            $vertical = Vertical::findOrFail($this->editingId);
            $vertical->update($this->form);
        } else {
            Vertical::create($this->form);
        }

        $this->showForm = false;
        $this->reset(['editingId', 'form']);
        $this->loadVerticals();
        session()->flash('success', 'Vertical guardado correctamente.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(int $id): void
    {
        $vertical = Vertical::withCount('deals')->findOrFail($id);

        if ($vertical->deals_count > 0) {
            session()->flash('error', 'No se puede eliminar un vertical con negocios asociados.');
            $this->confirmingDeleteId = null;
            return;
        }

        $vertical->delete();
        $this->confirmingDeleteId = null;
        $this->loadVerticals();
        session()->flash('success', 'Vertical eliminado.');
    }

    public function selectVertical(int $id): void
    {
        $this->selectedVerticalId = $id;
        $this->reset('stageForm');
        $this->stageForm = ['name' => '', 'color' => '#6366f1'];
    }

    public function saveStage(): void
    {
        $this->validateOnly('stageForm.*');

        $maxPos = Stage::where('vertical_id', $this->selectedVerticalId)->max('position') ?? 0;

        Stage::create([
            'vertical_id' => $this->selectedVerticalId,
            'name'         => $this->stageForm['name'],
            'color'        => $this->stageForm['color'],
            'position'     => $maxPos + 1,
        ]);

        $this->reset('stageForm');
        $this->stageForm = ['name' => '', 'color' => '#6366f1'];
        $this->loadVerticals();
    }

    public function deleteStage(int $id): void
    {
        $stage = Stage::withCount('deals')->findOrFail($id);

        if ($stage->deals_count > 0) {
            session()->flash('error', 'No se puede eliminar una etapa con negocios asociados.');
            return;
        }

        $stage->delete();
        $this->loadVerticals();
    }

    public function reorderStages(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $position => $stageId) {
                Stage::where('id', $stageId)
                    ->where('vertical_id', $this->selectedVerticalId)
                    ->update(['position' => $position + 1]);
            }
        });

        $this->loadVerticals();
    }

    public function render(): \Illuminate\View\View
    {
        $selectedVertical = $this->selectedVerticalId
            ? Vertical::with(['stages' => fn ($q) => $q->ordered()])->find($this->selectedVerticalId)
            : null;

        return view('livewire.admin.vertical-manager', [
            'selectedVertical' => $selectedVertical,
        ]);
    }
}

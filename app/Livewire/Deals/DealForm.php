<?php

namespace App\Livewire\Deals;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vertical;
use App\Services\ActivityLogger;
use Livewire\Component;

class DealForm extends Component
{
    public ?int $dealId = null;
    public array $form = [
        'title'               => '',
        'client_id'           => '',
        'vertical_id'         => '',
        'stage_id'            => '',
        'assigned_to'         => '',
        'amount'              => '',
        'currency'            => 'BOB',
        'probability'         => 0,
        'commission_rate'     => '',
        'expected_close_date' => '',
        'notes'               => '',
    ];
    public array $stages = [];
    public bool $showCommission = false;
    public bool $showLostModal = false;
    public string $lostReason = '';

    protected function rules(): array
    {
        return [
            'form.title'               => 'required|string|max:200',
            'form.client_id'           => 'required|exists:clients,id',
            'form.vertical_id'         => 'required|exists:verticals,id',
            'form.stage_id'            => 'required|exists:stages,id',
            'form.assigned_to'         => 'required|exists:users,id',
            'form.amount'              => 'required|numeric|min:0',
            'form.currency'            => 'required|string|size:3',
            'form.probability'         => 'required|integer|min:0|max:100',
            'form.commission_rate'     => 'nullable|numeric|min:0|max:100',
            'form.expected_close_date' => 'nullable|date',
            'form.notes'               => 'nullable|string|max:5000',
            'lostReason'               => 'required_if:showLostModal,true|nullable|string|max:500',
        ];
    }

    public function mount(?int $dealId = null): void
    {
        $this->dealId = $dealId;
        $this->form['assigned_to'] = auth()->id();

        if ($dealId) {
            $this->loadDeal($dealId);
        }
    }

    protected function loadDeal(int $id): void
    {
        $deal = Deal::findOrFail($id);
        $this->form = [
            'title'               => $deal->title,
            'client_id'           => $deal->client_id,
            'vertical_id'         => $deal->vertical_id,
            'stage_id'            => $deal->stage_id,
            'assigned_to'         => $deal->assigned_to,
            'amount'              => $deal->amount,
            'currency'            => $deal->currency,
            'probability'         => $deal->probability,
            'commission_rate'     => $deal->commission_rate ?? '',
            'expected_close_date' => $deal->expected_close_date?->format('Y-m-d') ?? '',
            'notes'               => $deal->notes ?? '',
        ];

        $this->loadStagesForVertical((int) $deal->vertical_id);

        $vertical = $deal->vertical;
        $this->showCommission = (bool) ($vertical?->track_commission);
    }

    public function updatedFormVerticalId(string $value): void
    {
        $this->form['stage_id'] = '';
        $this->stages = [];
        $this->showCommission = false;

        if ($value) {
            $this->loadStagesForVertical((int) $value);
            $vertical = Vertical::find($value);
            $this->showCommission = (bool) ($vertical?->track_commission);
        }
    }

    protected function loadStagesForVertical(int $verticalId): void
    {
        $this->stages = Stage::where('vertical_id', $verticalId)
            ->ordered()
            ->get(['id', 'name', 'color', 'is_won', 'is_lost'])
            ->toArray();
    }

    public function save(): void
    {
        $this->validate();

        $stage = Stage::find($this->form['stage_id']);

        if ($stage && $stage->is_lost && empty($this->lostReason) && !$this->showLostModal) {
            $this->showLostModal = true;
            return;
        }

        $data = [
            'title'               => $this->form['title'],
            'client_id'           => $this->form['client_id'],
            'vertical_id'         => $this->form['vertical_id'],
            'stage_id'            => $this->form['stage_id'],
            'assigned_to'         => $this->form['assigned_to'],
            'amount'              => $this->form['amount'],
            'currency'            => $this->form['currency'],
            'probability'         => $this->form['probability'],
            'commission_rate'     => $this->form['commission_rate'] ?: null,
            'expected_close_date' => $this->form['expected_close_date'] ?: null,
            'notes'               => $this->form['notes'] ?: null,
        ];

        if ($stage && $stage->is_lost) {
            $data['lost_reason'] = $this->lostReason;
        }

        if ($stage && $stage->is_won) {
            $data['actual_close_date'] = now();
        }

        if ($this->dealId) {
            $deal = Deal::findOrFail($this->dealId);
            $deal->update($data);
            $message = 'Negocio actualizado correctamente.';
        } else {
            $deal = Deal::create($data);
            ActivityLogger::log($deal, 'note', 'Negocio creado.');
            $message = 'Negocio creado correctamente.';
        }

        $this->showLostModal = false;
        session()->flash('success', $message);
        $this->redirect(route('deals.show', $deal), navigate: true);
    }

    public function cancelLost(): void
    {
        $this->showLostModal = false;
        $this->lostReason = '';
    }

    public function render(): \Illuminate\View\View
    {
        $clients   = Client::orderBy('name')->get(['id', 'name', 'company_name']);
        $verticals = Vertical::active()->orderBy('name')->get(['id', 'name', 'color', 'track_commission']);
        $assignees = User::active()->orderBy('name')->get(['id', 'name']);

        return view('livewire.deals.deal-form', [
            'clients'   => $clients,
            'verticals' => $verticals,
            'assignees' => $assignees,
        ]);
    }
}

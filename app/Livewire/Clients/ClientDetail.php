<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;

class ClientDetail extends Component
{
    public int $clientId;
    public ?Client $client = null;

    public function mount(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->loadClient();
    }

    protected function loadClient(): void
    {
        $this->client = Client::with([
            'deals' => fn ($q) => $q->with(['stage', 'vertical', 'assignedTo'])->latest(),
        ])->findOrFail($this->clientId);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.clients.client-detail');
    }
}

<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class ClientForm extends Component
{
    public ?int $clientId = null;
    public array $form = [
        'name'         => '',
        'company_name' => '',
        'email'        => '',
        'phone'        => '',
        'industry'     => '',
        'website'      => '',
        'address'      => '',
        'notes'        => '',
    ];

    protected function rules(): array
    {
        return [
            'form.name'         => 'required|string|max:150',
            'form.company_name' => 'nullable|string|max:150',
            'form.email'        => [
                'nullable', 'email', 'max:150',
                Rule::unique('clients', 'email')->ignore($this->clientId),
            ],
            'form.phone'    => 'nullable|string|max:30',
            'form.industry' => 'nullable|string|max:100',
            'form.website'  => 'nullable|url|max:255',
            'form.address'  => 'nullable|string|max:255',
            'form.notes'    => 'nullable|string|max:2000',
        ];
    }

    #[On('edit-client')]
    public function loadClient(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->clientId = $id;
        $this->form = [
            'name'         => $client->name,
            'company_name' => $client->company_name ?? '',
            'email'        => $client->email ?? '',
            'phone'        => $client->phone ?? '',
            'industry'     => $client->industry ?? '',
            'website'      => $client->website ?? '',
            'address'      => $client->address ?? '',
            'notes'        => $client->notes ?? '',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = array_map(fn ($v) => $v === '' ? null : $v, $this->form);
        $data['name'] = $this->form['name'];

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $client->update($data);
            $message = 'Cliente actualizado correctamente.';
        } else {
            $client = Client::create($data);
            $message = 'Cliente creado correctamente.';
        }

        $this->dispatch('client-saved', id: $client->id);
        session()->flash('success', $message);
        $this->reset();
        $this->form = array_fill_keys(array_keys($this->form), '');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.clients.client-form');
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;
    public array $form = [
        'name'      => '',
        'email'     => '',
        'password'  => '',
        'role'      => 'vendedor',
        'timezone'  => 'America/La_Paz',
        'is_active' => true,
    ];
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'form.name'      => 'required|string|max:100',
            'form.email'     => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'form.password'  => $this->editingId ? 'nullable|min:8' : 'required|min:8',
            'form.role'      => 'required|string|exists:roles,name',
            'form.timezone'  => 'required|string|max:50',
            'form.is_active' => 'boolean',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function showCreateForm(): void
    {
        $this->reset(['editingId']);
        $this->form = [
            'name'      => '',
            'email'     => '',
            'password'  => '',
            'role'      => 'vendedor',
            'timezone'  => 'America/La_Paz',
            'is_active' => true,
        ];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->form = [
            'name'      => $user->name,
            'email'     => $user->email,
            'password'  => '',
            'role'      => $user->roles->first()?->name ?? 'vendedor',
            'timezone'  => $user->timezone ?? 'America/La_Paz',
            'is_active' => $user->is_active,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->form['name'],
            'email'     => $this->form['email'],
            'timezone'  => $this->form['timezone'],
            'is_active' => $this->form['is_active'],
        ];

        if (!empty($this->form['password'])) {
            $data['password'] = Hash::make($this->form['password']);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        $user->syncRoles([$this->form['role']]);

        $this->showForm = false;
        $this->reset('editingId');
        session()->flash('success', 'Usuario guardado correctamente.');
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');
            return;
        }

        $user->delete();
        session()->flash('success', 'Usuario eliminado.');
    }

    public function render(): \Illuminate\View\View
    {
        $users = User::with('roles')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::orderBy('name')->pluck('name');

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}

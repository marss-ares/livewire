<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class UserCreate extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?int $roleId = null;

    protected $listeners = ['openCreateModal' => 'open'];

    public function open(): void
    {
        abort_if(!auth()->user()->hasPermission('users.create'), 403);

        $this->reset(['name', 'email', 'password', 'roleId']);
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_if(!auth()->user()->hasPermission('users.create'), 403);

        $this->validate([
            'name'     => 'required|min:3',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roleId'   => 'required|exists:roles,id',
        ]);

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => bcrypt($this->password),
            'role_id'  => $this->roleId,
        ]);

        $this->showModal = false;
        $this->dispatch('user-updated');
        $this->dispatch('toast', message: 'User created successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Users.user-create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}

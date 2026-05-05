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
        $this->reset(['name', 'email', 'password', 'roleId']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'     => 'required|min:3',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roleId'   => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => bcrypt($this->password),
            'role_id'  => $this->roleId ?: null,
        ]);

        $this->showModal = false;
        $this->dispatch('user-updated');
        $this->dispatch('toast', message: 'User creat cu succes!');
    }

    public function render()
    {
        return view('RBSMaterials.Users.user-create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
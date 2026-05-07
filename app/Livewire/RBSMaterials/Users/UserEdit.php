<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class UserEdit extends Component
{
    public bool $showModal = false;
    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?int $roleId = null;

    protected $listeners = ['openEditModal' => 'loadUser'];

    public function loadUser(int $user): void
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403);

        $this->user = User::findOrFail($user);
        abort_if($this->user->hasRole('admin') && auth()->id() !== $this->user->id, 403);
        $this->name     = $this->user->name;
        $this->email    = $this->user->email;
        $this->roleId   = $this->user->role_id;
        $this->password = '';
        $this->showModal = true;
    }

    public function update(): void
    {
        abort_if(!auth()->user()->hasPermission('users.edit'), 403);
        abort_if($this->user->hasRole('admin') && auth()->id() !== $this->user->id, 403);

        $this->validate([
            'name'     => 'required|min:3',
            'email'    => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|min:6',
            'roleId'   => 'required|exists:roles,id',
        ]);

        $data = [
            'name'    => $this->name,
            'email'   => $this->email,
            'role_id' => $this->roleId,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        $this->user->update($data);

        $this->showModal = false;
        $this->dispatch('user-updated');
        $this->dispatch('toast', message: 'User updated successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Users.user-edit', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}

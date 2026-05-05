<?php

namespace App\Livewire\RBSMaterials\Roles;

use App\Models\Role;
use Livewire\Component;

class RoleDelete extends Component
{
    public bool $showModal = false;
    public ?int $roleId = null;
    public string $roleName = '';

    protected $listeners = ['openDeleteRoleModal' => 'open'];

    public function open(int $role): void
    {
        $r = Role::findOrFail($role);

        if ($r->slug === 'admin') {
            $this->dispatch('toast', message: 'Admin role cannot be deleted');
            return;
        }

        $this->roleId   = $r->id;
        $this->roleName = $r->name;
        $this->showModal = true;
    }

    public function delete(): void
    {
        Role::findOrFail($this->roleId)->delete();

        $this->showModal = false;
        $this->dispatch('role-updated');
        $this->dispatch('toast', message: 'Rol șters cu succes!');
    }

    public function render()
    {
        return view('RBSMaterials.Roles.role-delete');
    }
}

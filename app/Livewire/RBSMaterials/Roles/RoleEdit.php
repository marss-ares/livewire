<?php

namespace App\Livewire\RBSMaterials\Roles;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class RoleEdit extends Component
{
    public bool $showModal = false;
    public ?int $roleId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public array $selectedPermissions = [];

    protected $listeners = ['openEditRoleModal' => 'open'];

    public function open(int $role): void
    {
        $r = Role::with('permissions')->findOrFail($role);

        if ($r->slug === 'admin') {
            $this->dispatch('toast', message: 'Admin role cannot be edited');
            return;
        }

        $this->roleId             = $r->id;
        $this->name               = $r->name;
        $this->slug               = $r->slug;
        $this->description        = $r->description ?? '';
        $this->selectedPermissions = $r->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->showModal          = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'required|min:2|max:100',
            'slug'        => "required|unique:roles,slug,{$this->roleId}|regex:/^[a-z0-9\-]+$/",
            'description' => 'nullable|max:255',
        ]);

        $role = Role::findOrFail($this->roleId);
        $role->update([
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
        ]);

        $role->permissions()->sync($this->selectedPermissions);

        $this->showModal = false;
        $this->dispatch('role-updated');
        $this->dispatch('toast', message: 'Rol actualizat cu succes!');
    }

    public function render()
    {
        $permissions = Permission::orderBy('category')->orderBy('name')->get()
            ->groupBy('category');

        return view('RBSMaterials.Roles.role-edit', [
            'permissionsByCategory' => $permissions,
        ]);
    }
}

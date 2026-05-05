<?php

namespace App\Livewire\RBSMaterials\Roles;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class RoleCreate extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public array $selectedPermissions = [];

    protected $listeners = ['openCreateRoleModal' => 'open'];

    public function open(): void
    {
        $this->reset(['name', 'slug', 'description', 'selectedPermissions']);
        $this->showModal = true;
    }

    public function updatedName(string $value): void
    {
        $this->slug = \Illuminate\Support\Str::slug($value);
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'required|min:2|max:100',
            'slug'        => 'required|unique:roles,slug|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|max:255',
        ]);

        $role = Role::create([
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
        ]);

        $role->permissions()->sync($this->selectedPermissions);

        $this->showModal = false;
        $this->dispatch('role-updated');
        $this->dispatch('toast', message: 'Rol creat cu succes!');
    }

    public function render()
    {
        $permissions = Permission::orderBy('category')->orderBy('name')->get()
            ->groupBy('category');

        return view('RBSMaterials.Roles.role-create', [
            'permissionsByCategory' => $permissions,
        ]);
    }
}

<?php

namespace App\Livewire\RBSMaterials\Roles;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RolesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $listeners = ['role-updated' => '$refresh'];

    public function render()
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('RBSMaterials.Roles.role-index', ['roles' => $roles]);
    }
}

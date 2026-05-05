<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $listeners = ['user-updated' => '$refresh'];

    public function render()
    {
        $users = User::query()
            ->with('role')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('RBSMaterials.Users.user-index', [
            'users' => $users,
        ]);
    }
}

<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\User;
use Livewire\Component;

class UserDelete extends Component
{
    public bool $showModal = false;
    public $userId;
    public bool $isSelf = false;

    protected $listeners = ['openDeleteModal' => 'open'];

    public function open(int $user): void
    {
        abort_if(!auth()->user()->hasPermission('users.delete'), 403);

        $this->userId  = $user;
        $this->isSelf  = ($user === auth()->id());
        $this->showModal = true;
    }

    public function delete(): void
    {
        abort_if(!auth()->user()->hasPermission('users.delete'), 403);

        if ($this->isSelf) {
            $this->dispatch('toast', message: 'You cannot delete your own account.', type: 'error');
            $this->showModal = false;
            return;
        }

        User::destroy($this->userId);

        $this->showModal = false;
        $this->dispatch('user-updated');
        $this->dispatch('toast', message: 'User deleted successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Users.user-delete');
    }
}

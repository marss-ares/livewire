<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\User;
use Livewire\Component;

class UserEdit extends Component
{
    public bool $showModal = false;
    public ?User $user = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';

    protected $listeners = ['openEditModal' => 'loadUser'];

    public function loadUser(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; 
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
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
        return view('RBSMaterials.Users.user-edit');
    }
}
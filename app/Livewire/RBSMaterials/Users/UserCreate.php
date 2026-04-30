<?php

namespace App\Livewire\RBSMaterials\Users;

use App\Models\User;
use Livewire\Component;

class UserCreate extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';

    protected  $listeners = ['openCreateModal' => 'open'];

    public function open()
    {
        $this->reset(['name', 'email', 'password']);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        $this->showModal = false;
        $this->dispatch('user-updated'); 
        $this->dispatch('toast', message: 'User created successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Users.user-create');
    }
}
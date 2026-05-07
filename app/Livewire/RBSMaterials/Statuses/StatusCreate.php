<?php

namespace App\Livewire\RBSMaterials\Statuses;

use App\Models\FormEntryStatus;
use Livewire\Component;

class StatusCreate extends Component
{
    public bool $showModal = false;
    public string $name  = '';
    public string $color = '#3b82f6';

    protected $listeners = ['openCreateModal' => 'open'];

    public function open(): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.create'), 403);

        $this->reset(['name']);
        $this->color = '#3b82f6';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.create'), 403);

        $this->validate([
            'name'  => 'required|min:1|max:100',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $nextOrder = (FormEntryStatus::max('order') ?? -1) + 1;

        FormEntryStatus::create([
            'name'  => $this->name,
            'color' => $this->color,
            'order' => $nextOrder,
        ]);

        $this->showModal = false;
        $this->dispatch('status-updated');
        $this->dispatch('toast', message: 'Status created successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Statuses.status-create');
    }
}

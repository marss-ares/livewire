<?php

namespace App\Livewire\RBSMaterials\Statuses;

use App\Models\FormEntryStatus;
use Livewire\Component;

class StatusEdit extends Component
{
    public bool $showModal = false;
    public ?FormEntryStatus $status = null;
    public string $name  = '';
    public string $color = '#3b82f6';

    protected $listeners = ['openEditModal' => 'loadStatus'];

    public function loadStatus(int $status): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.edit'), 403);

        $this->status = FormEntryStatus::findOrFail($status);
        $this->name   = $this->status->name;
        $this->color  = $this->status->color;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function update(): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.edit'), 403);

        $this->validate([
            'name'  => 'required|min:1|max:100',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $this->status->update([
            'name'  => $this->name,
            'color' => $this->color,
        ]);

        $this->showModal = false;
        $this->dispatch('status-updated');
        $this->dispatch('toast', message: 'Status updated successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Statuses.status-edit');
    }
}

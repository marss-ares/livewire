<?php

namespace App\Livewire\RBSMaterials\Statuses;

use App\Models\FormEntryStatus;
use Livewire\Component;

class StatusDelete extends Component
{
    public bool $showModal   = false;
    public ?int $statusId    = null;
    public string $statusName = '';

    protected $listeners = ['openDeleteModal' => 'open'];

    public function open(int $status): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.delete'), 403);

        $model = FormEntryStatus::findOrFail($status);
        $this->statusId   = $model->id;
        $this->statusName = $model->name;
        $this->showModal  = true;
    }

    public function delete(): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.delete'), 403);

        FormEntryStatus::findOrFail($this->statusId)->delete();

        $this->showModal = false;
        $this->dispatch('status-updated');
        $this->dispatch('toast', message: 'Status deleted successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Statuses.status-delete');
    }
}

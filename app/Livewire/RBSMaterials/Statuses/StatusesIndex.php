<?php

namespace App\Livewire\RBSMaterials\Statuses;

use App\Models\FormEntryStatus;
use Livewire\Component;

class StatusesIndex extends Component
{
    public string $name  = '';
    public string $color = '#3b82f6';

    public bool $showModal     = false;
    public ?int $editingId     = null;

    public bool   $showDeleteModal = false;
    public ?int   $deleteId        = null;
    public string $deleteName      = '';

    // =========================================================================
    // Create / Edit
    // =========================================================================

    public function openCreate(): void
    {
        $this->reset(['name', 'editingId']);
        $this->color = '#3b82f6';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $status = FormEntryStatus::where('owner_id', auth()->id())->findOrFail($id);

        $this->editingId = $status->id;
        $this->name      = $status->name;
        $this->color     = $status->color;

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'  => 'required|min:1|max:100',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        if ($this->editingId) {
            FormEntryStatus::where('owner_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update(['name' => $this->name, 'color' => $this->color]);

            $this->dispatch('toast', message: 'Status updated!');
        } else {
            $nextOrder = (FormEntryStatus::where('owner_id', auth()->id())->max('order') ?? -1) + 1;

            FormEntryStatus::create([
                'owner_id' => auth()->id(),
                'name'     => $this->name,
                'color'    => $this->color,
                'order'    => $nextOrder,
            ]);

            $this->dispatch('toast', message: 'Status created!');
        }

        $this->showModal = false;
    }

    // =========================================================================
    // Delete
    // =========================================================================

    public function confirmDelete(int $id): void
    {
        $status = FormEntryStatus::where('owner_id', auth()->id())->findOrFail($id);
        $this->deleteId   = $status->id;
        $this->deleteName = $status->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        FormEntryStatus::where('owner_id', auth()->id())
            ->findOrFail($this->deleteId)
            ->delete();

        $this->showDeleteModal = false;
        $this->dispatch('toast', message: 'Status deleted!');
    }

    // =========================================================================
    // Reorder
    // =========================================================================

    public function moveUp(int $id): void
    {
        $statuses = FormEntryStatus::where('owner_id', auth()->id())->orderBy('order')->get();
        $idx = $statuses->search(fn ($s) => $s->id === $id);

        if ($idx > 0) {
            $this->swapOrder($statuses[$idx], $statuses[$idx - 1]);
        }
    }

    public function moveDown(int $id): void
    {
        $statuses = FormEntryStatus::where('owner_id', auth()->id())->orderBy('order')->get();
        $idx = $statuses->search(fn ($s) => $s->id === $id);

        if ($idx !== false && $idx < $statuses->count() - 1) {
            $this->swapOrder($statuses[$idx], $statuses[$idx + 1]);
        }
    }

    private function swapOrder(FormEntryStatus $a, FormEntryStatus $b): void
    {
        [$a->order, $b->order] = [$b->order, $a->order];
        $a->save();
        $b->save();
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render()
    {
        $statuses = FormEntryStatus::where('owner_id', auth()->id())
            ->orderBy('order')
            ->get();

        return view('RBSMaterials.Statuses.statuses-index', compact('statuses'));
    }
}

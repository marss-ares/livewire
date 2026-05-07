<?php

namespace App\Livewire\RBSMaterials\Statuses;

use App\Models\FormEntryStatus;
use Livewire\Component;

class StatusesIndex extends Component
{
    public string $search = '';

    protected $listeners = ['status-updated' => '$refresh'];

    public function moveUp(int $id): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.reorder'), 403);

        $statuses = FormEntryStatus::orderBy('order')->get();
        $idx = $statuses->search(fn ($s) => $s->id === $id);

        if ($idx > 0) {
            $this->swapOrder($statuses[$idx], $statuses[$idx - 1]);
        }
    }

    public function moveDown(int $id): void
    {
        abort_if(!auth()->user()->hasPermission('statuses.reorder'), 403);

        $statuses = FormEntryStatus::orderBy('order')->get();
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

    public function render()
    {
        abort_if(!auth()->user()->hasPermission('statuses.view'), 403);

        $statuses = FormEntryStatus::orderBy('order')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->get();

        return view('RBSMaterials.Statuses.statuses-index', compact('statuses'));
    }
}

<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use Livewire\Component;
use Livewire\WithPagination;

class FormEntries extends Component
{
    use WithPagination;

    public Form $form;

    public function mount(Form $form): void
    {
        abort_if($form->user_id !== auth()->id(), 403);
        $this->form = $form;
    }

    public function deleteEntry(int $entryId): void
    {
        $this->form->entries()->findOrFail($entryId)->delete();
        $this->dispatch('toast', message: 'Entry deleted!');
    }

    public function render()
    {
        // Coloanele formului in ordine
        $columns = $this->form->columns()->orderBy('order')->get();

        // Entries cu valorile si submitter-ul
        $entries = $this->form->entries()
            ->with(['values', 'submitter'])
            ->latest()
            ->paginate(15);

        return view('RBSMaterials.Forms.form-entries', [
            'columns' => $columns,
            'entries' => $entries,
        ]);
    }
}

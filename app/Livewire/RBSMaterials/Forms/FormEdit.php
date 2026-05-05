<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormColumn;
use Illuminate\Support\Str;
use Livewire\Component;

class FormEdit extends Component
{
    public Form $form;

    // Date form
    public string $name = '';
    public string $description = '';

    // Date coloana noua
    public string $colName = '';
    public string $colType = 'text';
    public bool   $colRequired = false;
    public string $colOptions = ''; // pentru select: "opt1, opt2, opt3"

    // Coloana editata (null = niciuna)
    public ?int $editingColumnId = null;

    protected $listeners = ['column-deleted' => '$refresh'];

    public function mount(Form $form): void
    {
        abort_if($form->user_id !== auth()->id(), 403);

        $this->form        = $form;
        $this->name        = $form->name;
        $this->description = $form->description ?? '';
    }

    // Salveaza detaliile formului
    public function saveDetails(): void
    {
        $this->validate([
            'name'        => 'required|min:2|max:100',
            'description' => 'nullable|max:255',
        ]);

        $this->form->update([
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        $this->dispatch('toast', message: 'Form updated successfully!');
    }

    // Adauga o coloana noua
    public function addColumn(): void
    {
        $this->validate([
            'colName'     => 'required|min:2|max:100',
            'colType'     => 'required|in:text,email,number,date,textarea,select,checkbox',
            'colRequired' => 'boolean',
        ]);

        $nextOrder = $this->form->columns()->max('order') + 1;

        FormColumn::create([
            'form_id'  => $this->form->id,
            'name'     => $this->colName,
            'key'      => Str::slug($this->colName, '_'),
            'type'     => $this->colType,
            'required' => $this->colRequired,
            'options'  => $this->parseOptions(),
            'order'    => $nextOrder,
        ]);

        $this->resetColFields();
        $this->form->refresh();
        $this->dispatch('toast', message: 'Column added!');
    }

    // Incepe editarea unei coloane existente
    public function startEdit(int $columnId): void
    {
        $col = FormColumn::findOrFail($columnId);

        $this->editingColumnId = $col->id;
        $this->colName         = $col->name;
        $this->colType         = $col->type;
        $this->colRequired     = $col->required;
        $this->colOptions      = $col->options ? implode(', ', $col->options) : '';
    }

    // Salveaza coloana editata
    public function updateColumn(): void
    {
        $this->validate([
            'colName'     => 'required|min:2|max:100',
            'colType'     => 'required|in:text,email,number,date,textarea,select,checkbox',
            'colRequired' => 'boolean',
        ]);

        FormColumn::findOrFail($this->editingColumnId)->update([
            'name'     => $this->colName,
            'key'      => Str::slug($this->colName, '_'),
            'type'     => $this->colType,
            'required' => $this->colRequired,
            'options'  => $this->parseOptions(),
        ]);

        $this->resetColFields();
        $this->form->refresh();
        $this->dispatch('toast', message: 'Column updated!');
    }

    // Anuleaza editarea
    public function cancelEdit(): void
    {
        $this->resetColFields();
    }

    // Sterge o coloana
    public function deleteColumn(int $columnId): void
    {
        FormColumn::findOrFail($columnId)->delete();
        $this->form->refresh();
        $this->dispatch('toast', message: 'Column deleted!');
    }

    // Muta coloana in sus
    public function moveUp(int $columnId): void
    {
        $this->swapOrder($columnId, 'up');
    }

    // Muta coloana in jos
    public function moveDown(int $columnId): void
    {
        $this->swapOrder($columnId, 'down');
    }

    private function swapOrder(int $columnId, string $direction): void
    {
        $columns = $this->form->columns()->orderBy('order')->get();
        $index   = $columns->search(fn ($c) => $c->id === $columnId);

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex < 0 || $swapIndex >= $columns->count()) {
            return;
        }

        $current = $columns[$index];
        $swap    = $columns[$swapIndex];

        [$current->order, $swap->order] = [$swap->order, $current->order];

        $current->save();
        $swap->save();

        $this->form->refresh();
    }

    // Parseaza optiunile select din string "opt1, opt2" in array
    private function parseOptions(): ?array
    {
        if ($this->colType !== 'select' || blank($this->colOptions)) {
            return null;
        }

        return collect(explode(',', $this->colOptions))
            ->map(fn ($o) => trim($o))
            ->filter()
            ->values()
            ->all();
    }

    private function resetColFields(): void
    {
        $this->colName         = '';
        $this->colType         = 'text';
        $this->colRequired     = false;
        $this->colOptions      = '';
        $this->editingColumnId = null;
    }

    public function render()
    {
        return view('RBSMaterials.Forms.form-edit', [
            'columns' => $this->form->columns()->orderBy('order')->get(),
        ]);
    }
}

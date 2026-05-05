<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use Livewire\Component;

class FormCreate extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public string $description = '';

    protected $listeners = ['openCreateFormModal' => 'open'];

    public function open(): void
    {
        $this->reset(['name', 'description']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'        => 'required|min:2|max:100',
            'description' => 'nullable|max:255',
        ]);

        $form = Form::create([
            'user_id'     => auth()->id(),
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        $this->showModal = false;
        $this->dispatch('form-updated');
        $this->dispatch('toast', message: 'Form created successfully!');

        // Redirect catre pagina de editare pentru a adauga coloane
        $this->redirect(route('forms.edit', $form));
    }

    public function render()
    {
        return view('RBSMaterials.Forms.form-create');
    }
}

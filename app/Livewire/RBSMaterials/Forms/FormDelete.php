<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use Livewire\Component;

class FormDelete extends Component
{
    public bool $showModal = false;
    public ?int $formId = null;
    public string $formName = '';

    protected $listeners = ['openDeleteFormModal' => 'open'];

    public function open(int $form): void
    {
        $f = Form::findOrFail($form);

        abort_if($f->user_id !== auth()->id(), 403);

        $this->formId   = $f->id;
        $this->formName = $f->name;
        $this->showModal = true;
    }

    public function delete(): void
    {
        Form::findOrFail($this->formId)->delete();

        $this->showModal = false;
        $this->dispatch('form-updated');
        $this->dispatch('toast', message: 'Form deleted successfully!');
    }

    public function render()
    {
        return view('RBSMaterials.Forms.form-delete');
    }
}

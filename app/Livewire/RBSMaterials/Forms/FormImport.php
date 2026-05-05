<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormColumn;
use App\Models\FormEntry;
use App\Models\FormEntryValue;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FormImport extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public $file = null;
    public string $formName = '';

    // Starea importului
    public bool $importing = false;
    public int $imported = 0;
    public int $total = 0;

    protected $listeners = ['openImportModal' => 'open'];

    public function open(): void
    {
        $this->reset(['file', 'formName', 'importing', 'imported', 'total']);
        $this->showModal = true;
    }

    public function import(): void
    {
        $this->validate([
            'formName' => 'required|min:2|max:100',
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        $this->importing = true;

        $path = $this->file->getRealPath();

        // Citim fisierul Excel cu PhpSpreadsheet (inclus in maatwebsite/excel)
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true); // A, B, C... ca chei

        if (empty($rows)) {
            $this->addError('file', 'The file is empty.');
            $this->importing = false;
            return;
        }

        // Prima linie = headere (coloane)
        $headers = array_values(array_filter(reset($rows), fn ($v) => !is_null($v) && $v !== ''));

        if (empty($headers)) {
            $this->addError('file', 'Could not find column headers in the first row.');
            $this->importing = false;
            return;
        }

        // Cream form-ul
        $form = Form::create([
            'user_id' => auth()->id(),
            'name'    => $this->formName,
        ]);

        // Cream coloanele din headere
        $columns = [];
        foreach ($headers as $order => $header) {
            $columns[$header] = FormColumn::create([
                'form_id'  => $form->id,
                'name'     => $header,
                'key'      => Str::slug($header, '_') ?: 'col_' . ($order + 1),
                'type'     => 'text',
                'required' => false,
                'order'    => $order,
            ]);
        }

        // Restul randurilor = date
        $dataRows = array_slice($rows, 1);
        $this->total = count($dataRows);

        // Importam fiecare rand ca entry
        $letterMap = array_keys(reset($rows)); // ['A','B','C'...]

        foreach ($dataRows as $row) {
            // Skip randuri complet goale
            $values = array_values($row);
            if (empty(array_filter($values, fn ($v) => !is_null($v) && $v !== ''))) {
                $this->total--;
                continue;
            }

            $entry = FormEntry::create([
                'form_id' => $form->id,
                'user_id' => auth()->id(),
            ]);

            foreach ($headers as $index => $header) {
                $letter = $letterMap[$index] ?? null;
                $value  = $letter ? ($row[$letter] ?? null) : null;

                if (isset($columns[$header])) {
                    FormEntryValue::create([
                        'form_entry_id'  => $entry->id,
                        'form_column_id' => $columns[$header]->id,
                        'value'          => is_null($value) ? null : (string) $value,
                    ]);
                }
            }

            $this->imported++;
        }

        $this->importing  = false;
        $this->showModal  = false;

        $this->dispatch('form-updated');
        $this->dispatch('toast', message: "Imported {$this->imported} rows into \"{$form->name}\"!");

        $this->redirect(route('forms.entries', $form));
    }

    public function render()
    {
        return view('RBSMaterials.Forms.form-import');
    }
}

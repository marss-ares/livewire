<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormColumn;
use App\Models\FormEntry;
use App\Models\FormEntryStatus;
use App\Models\FormEntryValue;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FormImport extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public int  $step      = 1;

    public $file           = null;
    public string $formName = '';
    public ?int   $ownerId  = null;

    // Step 2: column mapping
    // fileHeaders[i] = column name from the file
    // fileLetters[i] = spreadsheet letter (A, B, C…) for that column
    // mapping[i]     = one of: 'system:full_name' | 'system:phone' | 'system:location'
    //                           'entry:status' | 'entry:source' | 'entry:owner'
    //                           'new' | 'skip'
    public array $fileHeaders = [];
    public array $fileLetters = [];
    public array $mapping     = [];

    // Import progress
    public bool $importing = false;
    public int  $imported  = 0;
    public int  $total     = 0;

    protected $listeners = ['openImportModal' => 'open'];

    public function open(): void
    {
        $this->reset(['file', 'formName', 'ownerId', 'fileHeaders', 'fileLetters', 'mapping', 'importing', 'imported', 'total']);
        $this->step = 1;
        $this->showModal = true;
    }

    // Step 1 → Step 2: parse headers and auto-detect mapping
    public function parseFile(): void
    {
        $this->validate([
            'formName' => 'required|min:2|max:100',
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $spreadsheet = IOFactory::load($this->file->getRealPath());
        $firstRow    = $spreadsheet->getActiveSheet()->toArray(null, true, true, true)[1] ?? [];

        if (empty($firstRow)) {
            $this->addError('file', 'The file is empty.');
            return;
        }

        $this->fileHeaders = [];
        $this->fileLetters = [];
        $this->mapping     = [];

        foreach ($firstRow as $letter => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }
            $idx = count($this->fileHeaders);
            $this->fileHeaders[$idx] = (string) $value;
            $this->fileLetters[$idx] = $letter;
            $this->mapping[$idx]     = $this->autoDetect((string) $value);
        }

        if (empty($this->fileHeaders)) {
            $this->addError('file', 'Could not find column headers in the first row.');
            return;
        }

        $this->step = 2;
    }

    public function back(): void
    {
        $this->step        = 1;
        $this->fileHeaders = [];
        $this->fileLetters = [];
        $this->mapping     = [];
        $this->resetErrorBag();
    }

    public function import(): void
    {
        $this->importing = true;

        $spreadsheet = IOFactory::load($this->file->getRealPath());
        $allRows     = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Row 1 = headers (already parsed); row 2+ = data
        $dataRows    = array_slice($allRows, 1, null, true);
        $this->total = count($dataRows);

        $form = Form::create([
            'user_id' => $this->ownerId ?? auth()->id(),
            'name'    => $this->formName,
        ]);

        // System column definitions (order ensures they always come first)
        $systemDefs = [
            'full_name' => ['name' => 'Full Name', 'order' => 0],
            'phone'     => ['name' => 'Phone',     'order' => 1],
            'location'  => ['name' => 'Location',  'order' => 2],
        ];

        // Create one FormColumn per system field that is actually mapped
        $systemColumns = []; // key => FormColumn
        foreach ($this->mapping as $target) {
            if (! str_starts_with($target, 'system:')) {
                continue;
            }
            $key = Str::after($target, 'system:');
            if (isset($systemColumns[$key]) || ! isset($systemDefs[$key])) {
                continue;
            }
            $systemColumns[$key] = FormColumn::create([
                'form_id'   => $form->id,
                'name'      => $systemDefs[$key]['name'],
                'key'       => $key,
                'type'      => 'text',
                'required'  => false,
                'order'     => $systemDefs[$key]['order'],
                'is_system' => true,
            ]);
        }

        // Create custom FormColumns for 'new' mappings (order starts after system)
        $customOrder   = 10;
        $customColumns = []; // index => FormColumn
        foreach ($this->mapping as $idx => $target) {
            if ($target !== 'new') {
                continue;
            }
            $header            = $this->fileHeaders[$idx];
            $customColumns[$idx] = FormColumn::create([
                'form_id'   => $form->id,
                'name'      => $header,
                'key'       => Str::slug($header, '_') ?: 'col_' . $idx,
                'type'      => 'text',
                'required'  => false,
                'order'     => $customOrder++,
                'is_system' => false,
            ]);
        }

        // Import each data row
        foreach ($dataRows as $row) {
            // Skip completely empty rows
            if (empty(array_filter(array_values($row), fn($v) => ! is_null($v) && $v !== ''))) {
                $this->total--;
                continue;
            }

            $entryData = [
                'form_id' => $form->id,
                'user_id' => auth()->id(),
                'source'  => $this->file->getClientOriginalName(),
            ];

            // Resolve entry-level fields first (status, source, owner)
            foreach ($this->fileHeaders as $idx => $header) {
                $target = $this->mapping[$idx] ?? 'skip';
                $value  = $row[$this->fileLetters[$idx]] ?? null;

                if (is_null($value) || $value === '') {
                    continue;
                }

                if ($target === 'entry:status') {
                    $status = FormEntryStatus::firstOrCreate(
                        ['name' => trim((string) $value)],
                        ['color' => 'zinc', 'order' => 99]
                    );
                    $entryData['status_id'] = $status->id;
                } elseif ($target === 'entry:source') {
                    $entryData['source'] = (string) $value;
                } elseif ($target === 'entry:owner') {
                    $user = User::where('name', $value)->orWhere('email', $value)->first();
                    if ($user) {
                        $entryData['user_id'] = $user->id;
                    }
                }
            }

            $entry = FormEntry::create($entryData);

            // Create FormEntryValue for system and custom columns
            foreach ($this->fileHeaders as $idx => $header) {
                $target = $this->mapping[$idx] ?? 'skip';
                $value  = $row[$this->fileLetters[$idx]] ?? null;

                $column = null;
                if (str_starts_with($target, 'system:')) {
                    $column = $systemColumns[Str::after($target, 'system:')] ?? null;
                } elseif ($target === 'new') {
                    $column = $customColumns[$idx] ?? null;
                }

                if ($column) {
                    FormEntryValue::create([
                        'form_entry_id'  => $entry->id,
                        'form_column_id' => $column->id,
                        'value'          => is_null($value) ? null : (string) $value,
                    ]);
                }
            }

            $this->imported++;
        }

        $this->importing = false;
        $this->showModal = false;

        $this->dispatch('form-updated');
        $this->dispatch('toast', message: "Imported {$this->imported} rows into \"{$form->name}\"!");

        $this->redirect(route('forms.entries', $form));
    }

    private function autoDetect(string $header): string
    {
        $lower = strtolower(trim($header));

        $rules = [
            'system:full_name' => ['full name', 'fullname', 'name', 'full_name', 'nume', 'client', 'contact', 'denumire'],
            'system:phone'     => ['phone', 'tel', 'telefon', 'mobile', 'cell', 'phone number', 'telephone', 'mobil'],
            'system:location'  => ['location', 'address', 'city', 'oras', 'localitate', 'adresa', 'loc', 'judet'],
            'entry:status'     => ['status', 'state', 'stare'],
            'entry:source'     => ['source', 'sursa', 'origin', 'provenienta'],
            'entry:owner'      => ['owner', 'assigned', 'agent', 'responsabil', 'user'],
        ];

        foreach ($rules as $target => $keywords) {
            foreach ($keywords as $kw) {
                if ($lower === $kw || str_contains($lower, $kw)) {
                    return $target;
                }
            }
        }

        return 'new';
    }

    public function render()
    {
        $users = auth()->user()->hasRole('admin')
            ? User::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('RBSMaterials.Forms.form-import', compact('users'));
    }
}

<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormEntryStatus;
use App\Models\FormEntryValue;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FormAppend extends Component
{
    use WithFileUploads;

    public bool  $showModal     = false;
    public int   $step          = 1;

    // Step 1
    public ?int  $filterUserId  = null;
    public ?int  $formId        = null;
    public $file                = null;

    // Step 2
    public array $fileHeaders   = [];
    public array $fileLetters   = [];
    public array $mapping       = []; // index => 'col:{id}' | 'entry:status' | 'entry:source' | 'skip'

    // Progress
    public bool  $importing     = false;
    public int   $imported      = 0;
    public int   $total         = 0;

    protected $listeners = ['openAppendModal' => 'open'];

    public function open(): void
    {
        $this->reset(['filterUserId', 'formId', 'file', 'fileHeaders', 'fileLetters', 'mapping', 'importing', 'imported', 'total']);
        $this->step      = 1;
        $this->showModal = true;
    }

    public function updatedFilterUserId(): void
    {
        $this->formId = null;
    }

    // ── Step 1 → Step 2 ──────────────────────────────────────────────────────

    public function parseFile(): void
    {
        $this->validate([
            'formId' => 'required|integer',
            'file'   => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $form = Form::query()
            ->when(!auth()->user()->hasRole('admin'), fn ($q) => $q->where('user_id', auth()->id()))
            ->with('columns')
            ->findOrFail($this->formId);

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
            if (is_null($value) || $value === '') continue;

            $idx                     = count($this->fileHeaders);
            $this->fileHeaders[$idx] = (string) $value;
            $this->fileLetters[$idx] = $letter;
            $this->mapping[$idx]     = $this->autoDetect((string) $value, $form->columns);
        }

        if (empty($this->fileHeaders)) {
            $this->addError('file', 'Could not find column headers in the first row.');
            return;
        }

        $this->step = 2;
    }

    private function autoDetect(string $header, $columns): string
    {
        $lower = strtolower(trim($header));

        // Try to match an existing column by name
        foreach ($columns as $col) {
            if (strtolower(trim($col->name)) === $lower) {
                return 'col:' . $col->id;
            }
        }

        // Fuzzy: header contains column name or vice versa
        foreach ($columns as $col) {
            $colLower = strtolower(trim($col->name));
            if (str_contains($lower, $colLower) || str_contains($colLower, $lower)) {
                return 'col:' . $col->id;
            }
        }

        // Entry-level fields
        $entryRules = [
            'entry:status' => ['status', 'stare', 'state'],
            'entry:source' => ['source', 'sursa', 'origin'],
        ];
        foreach ($entryRules as $target => $keywords) {
            foreach ($keywords as $kw) {
                if ($lower === $kw || str_contains($lower, $kw)) return $target;
            }
        }

        return 'skip';
    }

    public function back(): void
    {
        $this->step        = 1;
        $this->fileHeaders = [];
        $this->fileLetters = [];
        $this->mapping     = [];
        $this->resetErrorBag();
    }

    // ── Step 2 → Import ───────────────────────────────────────────────────────

    public function append(): void
    {
        $this->importing = true;

        $form = Form::query()
            ->when(!auth()->user()->hasRole('admin'), fn ($q) => $q->where('user_id', auth()->id()))
            ->with('columns')
            ->findOrFail($this->formId);

        $spreadsheet = IOFactory::load($this->file->getRealPath());
        $allRows     = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $dataRows    = array_slice($allRows, 1, null, true);
        $this->total = count($dataRows);

        $colById = $form->columns->keyBy('id');

        foreach ($dataRows as $row) {
            if (empty(array_filter(array_values($row), fn ($v) => $v !== null && $v !== ''))) {
                $this->total--;
                continue;
            }

            $entryData = [
                'form_id' => $form->id,
                'user_id' => auth()->id(),
                'source'  => $this->file->getClientOriginalName(),
            ];

            // Resolve entry-level fields first
            foreach ($this->fileHeaders as $idx => $header) {
                $target = $this->mapping[$idx] ?? 'skip';
                $value  = $row[$this->fileLetters[$idx]] ?? null;
                if ($value === null || $value === '') continue;

                if ($target === 'entry:status') {
                    $status = FormEntryStatus::firstOrCreate(
                        ['name' => trim((string) $value)],
                        ['color' => 'zinc', 'order' => 99]
                    );
                    $entryData['status_id'] = $status->id;
                } elseif ($target === 'entry:source') {
                    $entryData['source'] = (string) $value;
                }
            }

            $entry = FormEntry::create($entryData);

            // Create values for mapped columns
            foreach ($this->fileHeaders as $idx => $header) {
                $target = $this->mapping[$idx] ?? 'skip';
                $value  = $row[$this->fileLetters[$idx]] ?? null;

                if (!str_starts_with($target, 'col:')) continue;

                $colId = (int) str_replace('col:', '', $target);
                if (!$colById->has($colId)) continue;

                FormEntryValue::create([
                    'form_entry_id'  => $entry->id,
                    'form_column_id' => $colId,
                    'value'          => $value !== null ? (string) $value : null,
                ]);
            }

            $this->imported++;
        }

        $this->importing = false;
        $this->showModal = false;

        $this->dispatch('form-updated');
        $this->dispatch('toast', message: "Added {$this->imported} rows to \"{$form->name}\"!");
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $isAdmin = auth()->user()->hasRole('admin');

        $users = $isAdmin
            ? User::orderBy('name')->get(['id', 'name'])
            : collect();

        $forms = Form::query()
            ->when(!$isAdmin, fn ($q) => $q->where('user_id', auth()->id()))
            ->when($isAdmin && $this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $formColumns = collect();
        if ($this->formId) {
            $formColumns = Form::find($this->formId)?->columns()->orderBy('order')->get() ?? collect();
        }

        return view('RBSMaterials.Forms.form-append', compact('forms', 'users', 'formColumns'));
    }
}

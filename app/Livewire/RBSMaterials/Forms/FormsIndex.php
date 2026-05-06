<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormColumn;
use App\Models\FormEntry;
use App\Models\FormEntryStatus;
use App\Models\FormEntryValue;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FormsIndex extends Component
{
    use WithFileUploads;

    // ── Import modal ──────────────────────────────────────────────────────────
    public bool   $showImportModal = false;
    public string $formName = '';
    public $file = null;

    // ── Delete modal ──────────────────────────────────────────────────────────
    public bool   $showDeleteModal = false;
    public ?int   $deleteFormId   = null;
    public string $deleteFormName = '';

    // ── Column manager modal ──────────────────────────────────────────────────
    public bool $showColumnMenu   = false;
    public ?int $columnMenuFormId = null;

    // ── Per-form sort state ───────────────────────────────────────────────────
    // [formId => ['key' => string|null, 'dir' => 'asc'|'desc']]
    public array $sortState = [];

    // ── Per-form column visibility ────────────────────────────────────────────
    // [formId => [key => bool]]  keys: col IDs as strings, 'status', 'source'
    public array $colVisible = [];

    // ── Per-form column order ─────────────────────────────────────────────────
    // [formId => [key, ...]]  keys: col IDs as strings, 'status', 'source'
    public array $colOrder = [];

    // ── Per-form status filter ────────────────────────────────────────────────
    // [formId => statusId|null]  null = show all
    public array $statusFilter = [];

    // =========================================================================
    // Boot — load persisted preferences
    // =========================================================================

    public function mount(): void
    {
        $saved = session('form_view_prefs.' . auth()->id(), []);
        $this->colOrder   = $saved['colOrder']   ?? [];
        $this->colVisible = $saved['colVisible'] ?? [];
    }

    private function savePrefs(): void
    {
        session()->put('form_view_prefs.' . auth()->id(), [
            'colOrder'   => $this->colOrder,
            'colVisible' => $this->colVisible,
        ]);
    }

    // =========================================================================
    // Import
    // =========================================================================

    public function openImportModal(): void
    {
        $this->reset(['formName', 'file']);
        $this->resetErrorBag();
        $this->showImportModal = true;
    }

    public function import(): void
    {
        $this->validate([
            'formName' => 'required|min:2|max:100',
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $path        = $this->file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            $this->addError('file', 'The file is empty.');
            return;
        }

        $firstRow = reset($rows);
        $headers  = array_values(array_filter($firstRow, fn ($v) => !is_null($v) && $v !== ''));

        if (empty($headers)) {
            $this->addError('file', 'Could not find column headers in the first row.');
            return;
        }

        $form = Form::create([
            'user_id' => auth()->id(),
            'name'    => $this->formName,
        ]);

        $letterMap = array_keys($firstRow);
        $columns   = [];

        foreach ($headers as $order => $header) {
            $columns[$order] = FormColumn::create([
                'form_id'  => $form->id,
                'name'     => (string) $header,
                'key'      => Str::slug((string) $header, '_') ?: 'col_' . ($order + 1),
                'type'     => 'text',
                'required' => false,
                'order'    => $order,
            ]);
        }

        $imported   = 0;
        $sourceName = $this->file->getClientOriginalName();

        foreach (array_slice($rows, 1) as $row) {
            if (empty(array_filter(array_values($row), fn ($v) => !is_null($v) && $v !== ''))) {
                continue;
            }

            $entry = FormEntry::create([
                'form_id' => $form->id,
                'user_id' => auth()->id(),
                'source'  => $sourceName,
            ]);

            foreach ($headers as $index => $header) {
                $letter = $letterMap[$index] ?? null;
                $value  = $letter ? ($row[$letter] ?? null) : null;

                FormEntryValue::create([
                    'form_entry_id'  => $entry->id,
                    'form_column_id' => $columns[$index]->id,
                    'value'          => is_null($value) ? null : (string) $value,
                ]);
            }

            $imported++;
        }

        $this->showImportModal = false;
        $this->dispatch('toast', message: "Imported {$imported} rows into \"{$form->name}\"!");
    }

    // =========================================================================
    // Delete form
    // =========================================================================

    public function confirmDelete(int $formId): void
    {
        $form = Form::where('user_id', auth()->id())->findOrFail($formId);
        $this->deleteFormId   = $form->id;
        $this->deleteFormName = $form->name;
        $this->showDeleteModal = true;
    }

    public function deleteForm(): void
    {
        Form::where('user_id', auth()->id())->findOrFail($this->deleteFormId)->delete();

        unset(
            $this->sortState[$this->deleteFormId],
            $this->colVisible[$this->deleteFormId],
            $this->colOrder[$this->deleteFormId],
        );
        $this->savePrefs();

        $this->showDeleteModal = false;
        $this->dispatch('toast', message: 'Deleted!');
    }

    // =========================================================================
    // Entry status
    // =========================================================================

    public function updateEntryStatus(int $entryId, ?int $statusId): void
    {
        $entry = FormEntry::whereHas('form', fn ($q) => $q->where('user_id', auth()->id()))
            ->findOrFail($entryId);

        $entry->update(['status_id' => $statusId ?: null]);
    }

    // =========================================================================
    // Sorting
    // =========================================================================

    public function setStatusFilter(int $formId, ?int $statusId): void
    {
        $this->statusFilter[$formId] = $statusId;
    }

    public function export(int $formId): void
    {
        session()->put("form_export_{$formId}", [
            'statusFilter' => $this->statusFilter[$formId] ?? null,
            'colOrder'     => $this->colOrder[$formId]     ?? [],
            'colVisible'   => $this->colVisible[$formId]   ?? [],
        ]);

        $this->js('window.location.href = "' . route('forms.export', $formId) . '"');
    }

    public function sortBy(int $formId, string $key): void
    {
        $current = $this->sortState[$formId] ?? ['key' => null, 'dir' => 'asc'];

        if (($current['key'] ?? '') === $key) {
            $this->sortState[$formId]['dir'] = $current['dir'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortState[$formId] = ['key' => $key, 'dir' => 'asc'];
        }
    }

    // =========================================================================
    // Column manager
    // =========================================================================

    public function openColumnMenu(int $formId): void
    {
        $this->columnMenuFormId = $formId;
        $this->showColumnMenu   = true;
    }

    public function toggleColumn(int $formId, string $key): void
    {
        $this->colVisible[$formId][$key] = !($this->colVisible[$formId][$key] ?? true);
        $this->savePrefs();
    }

    public function moveColUp(int $formId, string $key): void
    {
        $order = $this->colOrder[$formId] ?? [];
        $idx   = array_search($key, $order);

        if ($idx !== false && $idx > 0) {
            [$order[$idx - 1], $order[$idx]] = [$order[$idx], $order[$idx - 1]];
            $this->colOrder[$formId] = array_values($order);
            $this->savePrefs();
        }
    }

    public function moveColDown(int $formId, string $key): void
    {
        $order = $this->colOrder[$formId] ?? [];
        $idx   = array_search($key, $order);

        if ($idx !== false && $idx < count($order) - 1) {
            [$order[$idx + 1], $order[$idx]] = [$order[$idx], $order[$idx + 1]];
            $this->colOrder[$formId] = array_values($order);
            $this->savePrefs();
        }
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render()
    {
        $rawForms = Form::query()
            ->when(!auth()->user()->hasRole('admin'), fn ($q) => $q->where('user_id', auth()->id()))
            ->with([
                'columns'        => fn ($q) => $q->orderBy('order'),
                'entries.values',
                'entries.status',
                'owner',
            ])
            ->latest()
            ->get();

        $statuses = FormEntryStatus::orderBy('order')->get();

        $formsData = $rawForms->map(function ($form) {
            $id        = $form->id;
            $allColIds = $form->columns->pluck('id')->map(fn ($v) => (string) $v)->toArray();

            // ── Init / sync colOrder (data cols + system cols) ────────────────
            $systemKeys = ['status', 'source', 'owner'];

            if (!isset($this->colOrder[$id])) {
                $this->colOrder[$id] = array_merge($allColIds, $systemKeys);
            } else {
                $current  = $this->colOrder[$id];
                $filtered = array_values(array_filter(
                    $current,
                    fn ($k) => in_array($k, $allColIds) || in_array($k, $systemKeys)
                ));
                $newCols = array_values(array_diff($allColIds, $filtered));
                $merged  = array_merge($filtered, $newCols);
                foreach ($systemKeys as $sk) {
                    if (!in_array($sk, $merged)) $merged[] = $sk;
                }
                $this->colOrder[$id] = $merged;
            }

            // ── Init / sync colVisible ───────────────────────────────────────
            if (!isset($this->colVisible[$id])) {
                $this->colVisible[$id] = array_merge(
                    array_fill_keys($allColIds, true),
                    array_fill_keys($systemKeys, true)
                );
            } else {
                foreach ($allColIds as $cid) {
                    $this->colVisible[$id][$cid] ??= true;
                }
                foreach ($systemKeys as $sk) {
                    $this->colVisible[$id][$sk] ??= true;
                }
            }

            if (!isset($this->sortState[$id])) {
                $this->sortState[$id] = ['key' => null, 'dir' => 'asc'];
            }

            $colById = $form->columns->keyBy('id');
            $vis     = $this->colVisible[$id];

            // ── Build unified ordered items ───────────────────────────────────
            $items = collect($this->colOrder[$id])
                ->map(function ($key) use ($colById, $vis) {
                    if (!($vis[$key] ?? true)) return null;
                    if ($key === 'status') return ['type' => 'status', 'key' => 'status'];
                    if ($key === 'source') return ['type' => 'source', 'key' => 'source'];
                    if ($key === 'owner')  return ['type' => 'owner',  'key' => 'owner'];
                    if ($colById->has((int) $key)) {
                        return ['type' => 'data', 'key' => $key, 'col' => $colById[(int) $key]];
                    }
                    return null;
                })
                ->filter()
                ->values();

            // ── Filter entries by status ──────────────────────────────────────
            $sort         = $this->sortState[$id];
            $activeFilter = $this->statusFilter[$id] ?? null;
            $entries      = $form->entries;

            if ($activeFilter !== null) {
                $entries = $entries->filter(fn ($e) => $e->status_id === $activeFilter)->values();
            }

            // ── Sort entries ──────────────────────────────────────────────────
            if ($sort['key']) {
                $ownerName = $form->owner?->name ?? '';
                $getter = match (true) {
                    $sort['key'] === 'status' => fn ($e) => $e->status?->name ?? '',
                    $sort['key'] === 'source' => fn ($e) => $e->source ?? '',
                    $sort['key'] === 'owner'  => fn ($e) => $ownerName,
                    default                   => fn ($e) => $e->valueFor((int) $sort['key']) ?? '',
                };

                $entries = $sort['dir'] === 'asc'
                    ? $entries->sortBy($getter)
                    : $entries->sortByDesc($getter);

                $entries = $entries->values();
            }

            return compact('form', 'items', 'entries', 'sort', 'activeFilter');
        });

        // ── Column manager modal data ─────────────────────────────────────────
        $menuItems = collect();

        if ($this->columnMenuFormId) {
            $menuForm = $rawForms->firstWhere('id', $this->columnMenuFormId);

            if ($menuForm) {
                $colById = $menuForm->columns->keyBy('id');
                $order   = $this->colOrder[$this->columnMenuFormId] ?? [];
                $vis     = $this->colVisible[$this->columnMenuFormId] ?? [];

                $menuItems = collect($order)
                    ->map(function ($key) use ($colById, $vis) {
                        $visible = $vis[$key] ?? true;
                        if ($key === 'status') {
                            return ['key' => 'status', 'label' => 'Status', 'system' => true, 'visible' => $visible];
                        }
                        if ($key === 'source') {
                            return ['key' => 'source', 'label' => 'Source', 'system' => true, 'visible' => $visible];
                        }
                        if ($key === 'owner') {
                            return ['key' => 'owner', 'label' => 'Owner', 'system' => true, 'visible' => $visible];
                        }
                        if ($colById->has((int) $key)) {
                            return ['key' => $key, 'label' => $colById[(int) $key]->name, 'system' => false, 'visible' => $visible];
                        }
                        return null;
                    })
                    ->filter()
                    ->values();
            }
        }

        return view('RBSMaterials.Forms.form-index', compact('formsData', 'statuses', 'menuItems'));
    }
}

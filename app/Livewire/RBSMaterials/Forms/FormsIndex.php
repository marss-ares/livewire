<?php

namespace App\Livewire\RBSMaterials\Forms;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormEntryStatus;
use App\Models\User;
use Livewire\Component;

class FormsIndex extends Component
{
    // ── User filter (admin only) ──────────────────────────────────────────────
    public ?int $selectedUserId = null;

    // ── Active tab ────────────────────────────────────────────────────────────
    public ?int $activeFormId = null;

    // ── Delete modal ──────────────────────────────────────────────────────────
    public bool   $showDeleteModal = false;
    public ?int   $deleteFormId   = null;
    public string $deleteFormName = '';

    // ── Edit modal ────────────────────────────────────────────────────────────
    public bool   $showEditModal = false;
    public ?int   $editFormId    = null;
    public string $editFormName  = '';
    public ?int   $editFormOwnerId = null;

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

    // ── Per-form date filter ──────────────────────────────────────────────────
    // [formId => ['from' => 'Y-m-d', 'to' => 'Y-m-d']]
    public array $dateFilter = [];

    // ── Per-form search filter ────────────────────────────────────────────────
    // [formId => string]  search query
    public array $searchQuery = [];

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

    public function setActiveForm(int $id): void
    {
        $this->activeFormId = $id;
    }

    public function updated($property, $value): void
    {
        if ($property === 'selectedUserId') {
            $this->activeFormId = null;
        }
    }

    // =========================================================================
    // Delete form
    // =========================================================================

    public function confirmDelete(int $formId): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $form = Form::findOrFail($formId);
        $this->deleteFormId   = $form->id;
        $this->deleteFormName = $form->name;
        $this->showDeleteModal = true;
    }

    public function deleteForm(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        Form::findOrFail($this->deleteFormId)->delete();

        unset(
            $this->sortState[$this->deleteFormId],
            $this->colVisible[$this->deleteFormId],
            $this->colOrder[$this->deleteFormId],
        );
        $this->savePrefs();

        $this->showDeleteModal = false;
        $this->dispatch('toast', message: 'Deleted!');
    }

    public function openEditModal(int $formId): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $form = Form::findOrFail($formId);

        $this->editFormId = $form->id;
        $this->editFormName = $form->name;
        $this->editFormOwnerId = $form->user_id;
        $this->showEditModal = true;
    }

    public function saveFormChanges(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $form = Form::findOrFail($this->editFormId);

        $form->update([
            'name' => $this->editFormName,
            'user_id' => $this->editFormOwnerId,
        ]);

        $this->showEditModal = false;
        $this->dispatch('toast', message: 'Form updated!');
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

    public function setDateFilter(int $formId, string $field, string $value): void
    {
        $this->dateFilter[$formId][$field] = $value;
    }

    public function clearDateFilter(int $formId): void
    {
        $this->dateFilter[$formId] = ['from' => '', 'to' => ''];
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
            ->when(auth()->user()->hasRole('admin') && $this->selectedUserId, fn ($q) => $q->where('user_id', $this->selectedUserId))
            ->with([
                'columns'        => fn ($q) => $q->orderBy('order'),
                'entries.values',
                'entries.status',
                'owner',
            ])
            ->latest()
            ->get();

        $users = auth()->user()->hasRole('admin') ? User::orderBy('name')->get() : collect();

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

            // ── Filter entries by status & date ──────────────────────────────
            $sort         = $this->sortState[$id];
            $activeFilter = $this->statusFilter[$id] ?? null;
            $dateFrom     = $this->dateFilter[$id]['from'] ?? '';
            $dateTo       = $this->dateFilter[$id]['to']   ?? '';
            $entries      = $form->entries;

            if ($activeFilter !== null) {
                $entries = $entries->filter(fn ($e) => $e->status_id === $activeFilter)->values();
            }

            if ($dateFrom !== '') {
                $from    = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $entries = $entries->filter(fn ($e) => $e->created_at->gte($from))->values();
            }

            if ($dateTo !== '') {
                $to      = \Carbon\Carbon::parse($dateTo)->endOfDay();
                $entries = $entries->filter(fn ($e) => $e->created_at->lte($to))->values();
            }

            // ── Search filter ─────────────────────────────────────────────────
            $searchTerm = strtolower($this->searchQuery[$id] ?? '');
            if ($searchTerm !== '') {
                $entries = $entries->filter(function ($e) use ($searchTerm, $form, $colById) {
                    foreach ($form->columns as $col) {
                        $value = strtolower($e->valueFor($col->id) ?? '');
                        if (str_contains($value, $searchTerm)) return true;
                    }
                    if (str_contains(strtolower($e->status?->name ?? ''), $searchTerm)) return true;
                    if (str_contains(strtolower($e->source ?? ''), $searchTerm)) return true;
                    return false;
                })->values();
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

            return compact('form', 'items', 'entries', 'sort', 'activeFilter', 'dateFrom', 'dateTo');
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

        $validIds = $formsData->pluck('form.id')->all();
        if (!in_array($this->activeFormId, $validIds)) {
            $this->activeFormId = $formsData->first()['form']->id ?? null;
        }

        $activeFormData = $formsData->firstWhere('form.id', $this->activeFormId);

        $allUsers = User::orderBy('name')->get();

        return view('RBSMaterials.Forms.form-index', compact('formsData', 'statuses', 'menuItems', 'users', 'allUsers', 'activeFormData'));
    }
}

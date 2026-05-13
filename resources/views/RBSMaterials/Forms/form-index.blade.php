<div class="space-y-6">

    {{-- Top bar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:heading size="xl" level="1" class="!font-bold tracking-tight">Forms</flux:heading>
            @if (auth()->user()->hasRole('admin'))
                <select wire:model.live="selectedUserId"
                    class="px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-600
                           bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300
                           focus:outline-none focus:ring-1 focus:ring-blue-400 cursor-pointer">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if ($formsData->isNotEmpty() && auth()->user()->hasPermission('forms.import'))
                <flux:button icon="plus" variant="ghost" wire:click="$dispatch('openAppendModal')">
                    Add to table
                </flux:button>
            @endif
            @if (auth()->user()->hasPermission('forms.import'))
                <flux:button icon="arrow-up-tray" variant="primary" wire:click="$dispatch('openImportModal')">
                    Import Excel
                </flux:button>
            @endif
        </div>
    </div>

    @if ($formsData->isEmpty())
        <div class="flex flex-col items-center gap-4 py-24 text-zinc-400">
            <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm font-medium">No imports yet</p>
            @if(auth()->user()->hasPermission('forms.import'))
                <flux:button variant="primary" icon="arrow-up-tray" wire:click="$dispatch('openImportModal')">
                    Import your first Excel
                </flux:button>
            @endif
        </div>
    @else

        {{-- ── Tab bar ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-0.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 w-fit flex-wrap">
            @foreach ($formsData as $data)
                @php $isActive = $activeFormId === $data['form']->id; @endphp
                <button
                    wire:click="setActiveForm({{ $data['form']->id }})"
                    class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap
                           {{ $isActive
                               ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm'
                               : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                    {{ $data['form']->name }}
                </button>
            @endforeach
        </div>

        {{-- ── Active form content ──────────────────────────────────────────── --}}
        @if ($activeFormData)
            @php
                $form         = $activeFormData['form'];
                $items        = $activeFormData['items'];
                $entries      = $activeFormData['entries'];
                $sort         = $activeFormData['sort'];
                $activeFilter = $activeFormData['activeFilter'];
                $dateFrom     = $activeFormData['dateFrom'];
                $dateTo       = $activeFormData['dateTo'];
            @endphp

            <div wire:key="form-{{ $form->id }}">

                {{-- Title row --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->hasRole('admin'))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $form->owner?->name ?? '—' }}
                            </span>
                        @endif
                        <span class="text-xs text-zinc-400">
                            {{ $form->columns->count() }} cols · {{ $form->entries->count() }} rows ·
                            {{ $form->created_at->format('d M Y') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Date filters --}}
                        <div class="flex items-center gap-1.5">
                            <input type="date" value="{{ $dateFrom }}"
                                wire:change="setDateFilter({{ $form->id }}, 'from', $event.target.value)"
                                class="text-xs rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800
                                       text-zinc-700 dark:text-zinc-300 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-400
                                       cursor-pointer" />
                            <span class="text-xs text-zinc-400">—</span>
                            <input type="date" value="{{ $dateTo }}"
                                wire:change="setDateFilter({{ $form->id }}, 'to', $event.target.value)"
                                class="text-xs rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800
                                       text-zinc-700 dark:text-zinc-300 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-400
                                       cursor-pointer" />
                            @if($dateFrom || $dateTo)
                                <button wire:click="clearDateFilter({{ $form->id }})"
                                    class="text-xs text-zinc-400 hover:text-red-500 transition-colors" title="Clear dates">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        @if (auth()->user()->hasPermission('forms.export'))
                            <flux:button variant="ghost" size="xs" icon="arrow-down-tray" circle
                                wire:click="export({{ $form->id }})" title="Export to Excel" />
                        @endif
                        @if (auth()->user()->hasRole('admin'))
                            <flux:button variant="ghost" size="xs" icon="pencil-square" circle
                                wire:click="openEditModal({{ $form->id }})" title="Edit form" />
                        @endif
                        <flux:button variant="ghost" size="xs" icon="adjustments-horizontal" circle
                            wire:click="openColumnMenu({{ $form->id }})" title="Manage columns" />
                        @if (auth()->user()->hasRole('admin'))
                            <flux:button variant="ghost" size="xs" icon="trash" circle
                                wire:click="confirmDelete({{ $form->id }})" />
                        @endif
                    </div>
                </div>

                {{-- Search filter --}}
                <div class="mb-4">
                    <input type="text" placeholder="Search table..."
                        wire:model.live="searchQuery.{{ $form->id }}"
                        class="w-full max-w-sm px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-600
                               bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 placeholder-zinc-400
                               focus:outline-none focus:ring-1 focus:ring-blue-400 focus:border-transparent" />
                </div>

                {{-- Status filter pills --}}
                @if ($statuses->isNotEmpty())
                    <div class="flex items-center gap-1.5 flex-wrap mb-3">
                        <span class="text-xs text-zinc-400 mr-0.5">Filter:</span>

                        <button wire:click="setStatusFilter({{ $form->id }}, null)"
                            class="text-xs px-2.5 py-1 rounded-full border transition-colors
                                   {{ $activeFilter === null
                                       ? 'bg-zinc-800 dark:bg-zinc-100 text-white dark:text-zinc-900 border-transparent'
                                       : 'border-zinc-200 dark:border-zinc-600 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}">
                            All
                            <span class="ml-1 text-[10px] opacity-70">{{ $form->entries->count() }}</span>
                        </button>

                        @foreach ($statuses as $status)
                            @php $isActiveStatus = $activeFilter === $status->id; @endphp
                            <button wire:click="setStatusFilter({{ $form->id }}, {{ $status->id }})"
                                class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border transition-all
                                       {{ $isActiveStatus
                                           ? 'bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-100 font-medium'
                                           : 'border-zinc-200 dark:border-zinc-600 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-700/50' }}"
                                style="{{ $isActiveStatus ? 'border-color:' . $status->color : '' }}">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                    style="background-color: {{ $status->color }}"></span>
                                {{ $status->name }}
                                <span class="text-[10px] opacity-60">
                                    {{ $form->entries->where('status_id', $status->id)->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Data table --}}
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-300 dark:border-zinc-600">
                                <th class="w-10 px-3 py-2 text-center text-xs font-semibold text-zinc-400 border-r border-zinc-200 dark:border-zinc-700 select-none">
                                    #
                                </th>
                                @foreach ($items as $item)
                                    @if ($item['type'] === 'owner' && !auth()->user()->hasRole('admin'))
                                        @continue
                                    @endif
                                    @php
                                        $isActiveSortCol = ($sort['key'] ?? null) === $item['key'];
                                        $label = match ($item['type']) {
                                            'status' => 'Status',
                                            'source' => 'Source',
                                            'owner'  => 'Owner',
                                            default  => $item['col']->name,
                                        };
                                    @endphp
                                    <th wire:click="sortBy({{ $form->id }}, '{{ $item['key'] }}')"
                                        class="px-4 py-2 text-left text-xs font-semibold whitespace-nowrap cursor-pointer select-none group
                                               border-r border-zinc-200 dark:border-zinc-700
                                               {{ $isActiveSortCol
                                                   ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20'
                                                   : 'text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700/50' }}">
                                        <span class="flex items-center gap-1">
                                            {{ $label }}
                                            @if ($isActiveSortCol)
                                                @if ($sort['dir'] === 'asc')
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                @else
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-3 h-3 shrink-0 opacity-0 group-hover:opacity-40 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @endif
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $i => $entry)
                                <tr wire:key="entry-{{ $entry->id }}"
                                    class="border-b border-zinc-100 dark:border-zinc-700/50 hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors
                                           {{ $i % 2 === 0 ? 'bg-white dark:bg-zinc-800' : 'bg-zinc-50 dark:bg-zinc-700/40' }}">

                                    <td class="px-3 py-2 text-center text-xs text-zinc-400 border-r border-zinc-200 dark:border-zinc-700 select-none">
                                        {{ $i + 1 }}
                                    </td>

                                    @foreach ($items as $item)
                                        @if ($item['type'] === 'owner' && !auth()->user()->hasRole('admin'))
                                            @continue
                                        @endif
                                        @if ($item['type'] === 'data')
                                            <td class="px-4 py-2 border-r border-zinc-100 dark:border-zinc-700/50 text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $entry->valueFor($item['col']->id) ?? '' }}
                                            </td>
                                        @elseif ($item['type'] === 'status')
                                            @php $selectedStatus = $statuses->firstWhere('id', $entry->status_id); @endphp
                                            <td class="px-3 py-1.5 border-r border-zinc-100 dark:border-zinc-700/50
                                                       {{ $i % 2 === 0 ? 'bg-white dark:bg-zinc-800 hover:!bg-white dark:hover:!bg-zinc-800' : 'bg-zinc-50 dark:bg-zinc-700/40 hover:!bg-zinc-50 dark:hover:!bg-zinc-700/40' }}">
                                                @if ($statuses->isEmpty())
                                                    <span class="text-xs text-zinc-400 italic">No statuses —
                                                        <a href="{{ route('statuses.index') }}" wire:navigate class="underline">create some</a>
                                                    </span>
                                                @else
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full shrink-0 transition-colors"
                                                            style="background-color: {{ $selectedStatus?->color ?? 'transparent' }};
                                                                   {{ $selectedStatus ? '' : 'border: 1.5px solid #d1d5db;' }}">
                                                        </span>
                                                        <select
                                                            wire:change="updateEntryStatus({{ $entry->id }}, $event.target.value)"
                                                            class="text-xs rounded-lg px-2 py-1 border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 focus:outline-none focus:ring-1 focus:ring-blue-400 cursor-pointer">
                                                            <option value="">— none —</option>
                                                            @foreach ($statuses as $status)
                                                                <option value="{{ $status->id }}" @selected($entry->status_id === $status->id) style="background-color: {{ $status->color }} !important; color: white !important; background-image: none; appearance: none;">
                                                                    {{ $status->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                            </td>
                                        @elseif ($item['type'] === 'source')
                                            <td class="px-4 py-2 text-xs text-zinc-400 whitespace-nowrap border-r border-zinc-100 dark:border-zinc-700/50">
                                                {{ $entry->source ?? '—' }}
                                            </td>
                                        @elseif ($item['type'] === 'owner')
                                            <td class="px-4 py-2 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                                {{ $form->owner?->name ?? '—' }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $items->count() + 1 }}" class="px-4 py-6 text-center text-xs text-zinc-400">
                                        No data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @endif

    {{-- ── Column Manager Modal ─────────────────────────────────────────── --}}
    <flux:modal wire:model="showColumnMenu" class="md:w-[400px] space-y-5">
        <div>
            <flux:heading size="lg">Manage Columns</flux:heading>
            <flux:subheading>Reorder or hide columns for this table.</flux:subheading>
        </div>

        @if ($columnMenuFormId && $menuItems->isNotEmpty())
            <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                @foreach ($menuItems as $i => $item)
                    @if ($item['key'] === 'owner' && !auth()->user()->hasRole('admin'))
                        @continue
                    @endif
                    <div class="flex items-center gap-3 py-2.5">
                        <div class="flex flex-col gap-0.5">
                            <button wire:click="moveColUp({{ $columnMenuFormId }}, '{{ $item['key'] }}')"
                                @class([
                                    'p-0.5 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors',
                                    'opacity-20 pointer-events-none' => $loop->first,
                                ])>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button wire:click="moveColDown({{ $columnMenuFormId }}, '{{ $item['key'] }}')"
                                @class([
                                    'p-0.5 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors',
                                    'opacity-20 pointer-events-none' => $loop->last,
                                ])>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <input type="checkbox"
                            wire:click="toggleColumn({{ $columnMenuFormId }}, '{{ $item['key'] }}')"
                            @checked($item['visible'])
                            class="rounded border-zinc-300 dark:border-zinc-600 text-blue-500 cursor-pointer" />

                        <span class="text-sm text-zinc-700 dark:text-zinc-300 flex-1">{{ $item['label'] }}</span>

                        @if ($item['system'])
                            <span class="text-xs px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-400">system</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex justify-end">
            <flux:button variant="primary" wire:click="$set('showColumnMenu', false)">Done</flux:button>
        </div>
    </flux:modal>

    <livewire:rbs-materials.forms.form-import />
    <livewire:rbs-materials.forms.form-append />

    {{-- ── Delete Modal ──────────────────────────────────────────────────── --}}
    <flux:modal wire:model="showDeleteModal" class="md:w-[400px] space-y-6">
        <div>
            <flux:heading size="lg">Delete Import</flux:heading>
            <flux:subheading>This will delete all rows. Cannot be undone.</flux:subheading>
        </div>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Are you sure you want to delete
            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleteFormName }}</span>?
        </p>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="deleteForm">Delete</flux:button>
        </div>
    </flux:modal>

    {{-- ── Edit Modal ───────────────────────────────────────────────────── --}}
    <flux:modal wire:model="showEditModal" class="md:w-[400px] space-y-5">
        <div>
            <flux:heading size="lg">Edit Form</flux:heading>
            <flux:subheading>Update form details.</flux:subheading>
        </div>

        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Form Name</label>
                <input type="text" wire:model="editFormName"
                    class="w-full mt-1 px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-600
                           bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300
                           focus:outline-none focus:ring-1 focus:ring-blue-400" />
            </div>

            @if (auth()->user()->hasRole('admin'))
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Assign to User</label>
                    <select wire:model="editFormOwnerId"
                        class="w-full mt-1 px-3 py-2 text-sm rounded-lg border border-zinc-200 dark:border-zinc-600
                               bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300
                               focus:outline-none focus:ring-1 focus:ring-blue-400 cursor-pointer">
                        @foreach ($allUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showEditModal', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="saveFormChanges">Save</flux:button>
        </div>
    </flux:modal>

</div>

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('forms.index')" wire:navigate />
        <flux:heading size="xl" level="1" class="!font-bold tracking-tight">Edit Form</flux:heading>
    </div>

    {{-- Form Details --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 shadow-sm space-y-4">
        <flux:heading size="lg">Details</flux:heading>

        <div class="grid grid-cols-1 gap-4">
            <flux:input label="Form Name" wire:model="name" />
            <flux:input label="Description" wire:model="description" />
        </div>

        <div class="flex justify-end">
            <flux:button variant="primary" wire:click="saveDetails">Save Details</flux:button>
        </div>
    </div>

    {{-- Columns --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
            <flux:heading size="lg">Fields</flux:heading>
            <span class="text-xs text-zinc-500">{{ $columns->count() }} field(s)</span>
        </div>

        {{-- Existing columns --}}
        @forelse ($columns as $column)
            <div wire:key="col-{{ $column->id }}"
                class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/50 last:border-0">

                @if($editingColumnId === $column->id)
                    {{-- Edit mode --}}
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input label="Field Name" wire:model="colName" />
                            <div>
                                <flux:label class="mb-1">Type</flux:label>
                                <select wire:model.live="colType"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="checkbox">Checkbox</option>
                                </select>
                            </div>
                        </div>

                        @if($colType === 'select')
                            <flux:input label="Options (comma separated)" placeholder="Option 1, Option 2, Option 3"
                                wire:model="colOptions" />
                        @endif

                        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" wire:model="colRequired"
                                class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                            Required field
                        </label>

                        <div class="flex gap-2">
                            <flux:button size="sm" variant="primary" wire:click="updateColumn">Save</flux:button>
                            <flux:button size="sm" variant="ghost" wire:click="cancelEdit">Cancel</flux:button>
                        </div>
                    </div>
                @else
                    {{-- View mode --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex flex-col gap-0.5">
                                <flux:button variant="ghost" size="xs" icon="chevron-up" circle
                                    wire:click="moveUp({{ $column->id }})" />
                                <flux:button variant="ghost" size="xs" icon="chevron-down" circle
                                    wire:click="moveDown({{ $column->id }})" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $column->name }}</span>
                                    @if($column->required)
                                        <span class="text-red-500 text-xs font-semibold">Required</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                        {{ $column->type }}
                                    </span>
                                    @if($column->options)
                                        <span class="text-xs text-zinc-400">
                                            {{ implode(', ', $column->options) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <flux:button variant="ghost" size="xs" icon="pencil" circle
                                wire:click="startEdit({{ $column->id }})" />
                            <flux:button variant="ghost" size="xs" icon="trash" circle
                                wire:click="deleteColumn({{ $column->id }})"
                                wire:confirm="Delete this field?" />
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="px-6 py-10 text-center text-zinc-400 text-sm">
                No fields yet. Add your first field below.
            </div>
        @endforelse

        {{-- Add new column form --}}
        @if(is_null($editingColumnId))
            <div class="px-6 py-5 bg-zinc-50 dark:bg-zinc-900/30 border-t border-zinc-200 dark:border-zinc-700 space-y-3">
                <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400">Add New Field</flux:heading>

                <div class="grid grid-cols-2 gap-3">
                    <flux:input placeholder="Field name" wire:model="colName" />
                    <select wire:model.live="colType"
                        class="rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Select</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </div>

                @if($colType === 'select')
                    <flux:input placeholder="Options: Option 1, Option 2, Option 3" wire:model="colOptions" />
                @endif

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        <input type="checkbox" wire:model="colRequired"
                            class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                        Required
                    </label>
                    <flux:button size="sm" variant="primary" icon="plus" wire:click="addColumn">
                        Add Field
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <flux:button variant="ghost" :href="route('forms.entries', $form)" wire:navigate icon="table-cells">
            View Entries ({{ $form->entries()->count() }})
        </flux:button>
    </div>

</div>

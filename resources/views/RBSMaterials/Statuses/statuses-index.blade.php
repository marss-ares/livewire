<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1" class="!font-bold tracking-tight">Statuses</flux:heading>
        <flux:button icon="plus" variant="primary" wire:click="openCreate">
            Add Status
        </flux:button>
    </div>

    @if($statuses->isEmpty())
        <div class="flex flex-col items-center gap-4 py-24 text-zinc-400">
            <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-medium">No statuses yet</p>
            <flux:button variant="primary" icon="plus" wire:click="openCreate">
                Create your first status
            </flux:button>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                        <th class="w-10 px-3 py-2.5 text-center text-xs font-semibold text-zinc-400 select-none">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400">Color</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400">Name</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @foreach ($statuses as $status)
                        <tr wire:key="status-{{ $status->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors group">

                            {{-- Reorder arrows --}}
                            <td class="px-3 py-2.5 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <button wire:click="moveUp({{ $status->id }})"
                                        @class(['p-0.5 rounded text-zinc-300 dark:text-zinc-600 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors',
                                                'opacity-20 pointer-events-none' => $loop->first])>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    <button wire:click="moveDown({{ $status->id }})"
                                        @class(['p-0.5 rounded text-zinc-300 dark:text-zinc-600 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors',
                                                'opacity-20 pointer-events-none' => $loop->last])>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            {{-- Color swatch + hex --}}
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full shrink-0 border border-black/10"
                                          style="background-color: {{ $status->color }}"></span>
                                    <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $status->color }}</span>
                                </span>
                            </td>

                            {{-- Name --}}
                            <td class="px-4 py-2.5 font-medium text-zinc-800 dark:text-zinc-100">
                                {{ $status->name }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button variant="ghost" size="xs" icon="pencil" circle
                                        wire:click="openEdit({{ $status->id }})" />
                                    <flux:button variant="ghost" size="xs" icon="trash" circle
                                        wire:click="confirmDelete({{ $status->id }})" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Create / Edit Modal ─────────────────────────────────────────────── --}}
    <flux:modal wire:model="showModal" class="md:w-[420px] space-y-5">
        <div>
            <flux:heading size="lg">{{ $editingId ? 'Edit Status' : 'New Status' }}</flux:heading>
            <flux:subheading>{{ $editingId ? 'Update the name or color.' : 'Choose a name and pick a color.' }}</flux:subheading>
        </div>

        <flux:input label="Name" placeholder="e.g. Pending Review" wire:model="name" autofocus />

        {{-- Hex color picker --}}
        <div x-data="{ hex: @entangle('color').live }">
            <flux:label class="mb-2">Color</flux:label>
            <div class="flex items-center gap-3">
                {{-- Native color wheel --}}
                <input
                    type="color"
                    x-model="hex"
                    @change="$wire.color = hex"
                    class="w-10 h-10 rounded-lg cursor-pointer border border-zinc-200 dark:border-zinc-600 p-0.5 bg-white dark:bg-zinc-800"
                    title="Pick a color"
                />

                {{-- Hex text input --}}
                <input
                    type="text"
                    x-model="hex"
                    @blur="if (/^#[0-9a-fA-F]{6}$/.test(hex)) $wire.color = hex"
                    @keydown.enter="if (/^#[0-9a-fA-F]{6}$/.test(hex)) $wire.color = hex"
                    maxlength="7"
                    placeholder="#3b82f6"
                    class="font-mono text-sm w-28 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-400"
                />

                {{-- Live preview --}}
                <span class="w-8 h-8 rounded-full shrink-0 border border-black/10 transition-colors"
                      :style="`background-color: ${hex}`"></span>
            </div>
            @error('color') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="save">
                {{ $editingId ? 'Save Changes' : 'Create' }}
            </flux:button>
        </div>
    </flux:modal>

    {{-- ── Delete Modal ────────────────────────────────────────────────────── --}}
    <flux:modal wire:model="showDeleteModal" class="md:w-[380px] space-y-5">
        <div>
            <flux:heading size="lg">Delete Status</flux:heading>
            <flux:subheading>Entries using this status will lose their status value.</flux:subheading>
        </div>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Are you sure you want to delete
            <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deleteName }}</span>?
        </p>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="delete">Delete</flux:button>
        </div>
    </flux:modal>

</div>

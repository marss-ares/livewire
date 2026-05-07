<div>
    <header class="relative mb-6 flex flex-col gap-4">
        <div class="flex items-center justify-between w-full">
            <flux:heading size="xl" level="1" class="!font-bold tracking-tight">
                Statuses
            </flux:heading>
            <div class="flex-none">
                <flux:input type="search" size="sm" icon="magnifying-glass" placeholder="Search statuses..."
                    wire:model.live="search" class="w-64 flex-none" />
            </div>
        </div>

        <div class="relative z-10 flex items-center gap-2 bg-white dark:bg-zinc-800 p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            @if(auth()->user()->hasPermission('statuses.create'))
                <flux:button size="sm" icon="plus" variant="primary" wire:click="$dispatch('openCreateModal')">
                    Add Status
                </flux:button>
            @endif

            <flux:spacer />

            <div class="px-3 py-1 text-xs font-semibold text-zinc-500 bg-zinc-100 dark:bg-zinc-700 rounded-md">
                Count: {{ $statuses->count() }}
            </div>
        </div>
    </header>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                <tr>
                    @if(auth()->user()->hasPermission('statuses.reorder') && !$search)
                        <th class="w-10 px-3 py-3 text-center text-xs font-semibold text-zinc-500 uppercase tracking-wider">#</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Color</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Name</th>
                    @if(auth()->user()->hasPermission('statuses.edit') || auth()->user()->hasPermission('statuses.delete'))
                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @forelse ($statuses as $status)
                    <tr wire:key="status-{{ $status->id }}"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">

                        @if(auth()->user()->hasPermission('statuses.reorder') && !$search)
                            <td class="px-3 py-4 text-center">
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
                        @endif

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full shrink-0 border border-black/10"
                                      style="background-color: {{ $status->color }}"></span>
                                <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $status->color }}</span>
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $status->name }}
                        </td>

                        @if(auth()->user()->hasPermission('statuses.edit') || auth()->user()->hasPermission('statuses.delete'))
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                @if(auth()->user()->hasPermission('statuses.edit'))
                                    <flux:button variant="ghost" size="xs" icon="pencil" circle
                                        wire:click="$dispatch('openEditModal', { status: {{ $status->id }} })" />
                                @endif
                                @if(auth()->user()->hasPermission('statuses.delete'))
                                    <flux:button variant="ghost" size="xs" icon="trash" circle
                                        wire:click="$dispatch('openDeleteModal', { status: {{ $status->id }} })" />
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-400">
                            No statuses found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <livewire:r-b-s-materials.statuses.status-create />
    <livewire:r-b-s-materials.statuses.status-edit />
    <livewire:r-b-s-materials.statuses.status-delete />
</div>

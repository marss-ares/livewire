<div>
    <header class="relative mb-6 flex flex-col gap-4">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="!font-bold tracking-tight">
                    Users
                </flux:heading>
            </div>

            <div class="flex-none">
                <flux:input size="sm" icon="magnifying-glass" placeholder="Search user..." wire:model.live="search"
                    class="w-64" />
            </div>
        </div>

        <div
            class="relative z-10 flex items-center gap-2 bg-white dark:bg-zinc-800 p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <flux:button size="sm" icon="plus" variant="primary" wire:click="$dispatch('openCreateModal')">
                Add User
            </flux:button>

            <flux:spacer />

            <div class="px-3 py-1 text-xs font-semibold text-zinc-500 bg-zinc-100 dark:bg-zinc-700 rounded-md">
                Count: {{ $users->total() }}
            </div>
        </div>
    </header>

    @php
        $headers = [['label' => 'Name'], ['label' => 'Email'], ['label' => 'Actions', 'class' => 'text-right']];
    @endphp

    <div
        class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <x-table :headers="$headers" :rows="$users">
            @foreach ($users as $user)
                <tr wire:key="user-{{ $user->id }}"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors border-b border-zinc-100 dark:border-zinc-700/50 last:border-0">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <flux:button variant="ghost" size="xs" icon="pencil" circle
                            wire:click="$dispatch('openEditModal', { user: {{ $user->id }} })" />
                        <flux:button variant="ghost" size="xs" icon="trash" circle
                            wire:click="$dispatch('openDeleteModal', { user: {{ $user->id }} })" />
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <livewire:r-b-s-materials.users.user-create />
    <livewire:r-b-s-materials.users.user-edit />
    <livewire:r-b-s-materials.users.user-delete />
</div>

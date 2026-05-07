<div>
    <header class="relative mb-6 flex flex-col gap-4">
        <div class="flex items-center justify-between w-full">
            <flux:heading size="xl" level="1" class="!font-bold tracking-tight">
                Users
            </flux:heading>
            <div class="flex-none">
                <div x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 100)">
                    <template x-if="ready">
                        <input
                            x-on:input.debounce.300ms="$wire.set('search', $el.value)"
                            placeholder="Search users..."
                            class="w-64 h-8 px-3 text-sm rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-800 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </template>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex items-center gap-2 bg-white dark:bg-zinc-800 p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            @if(auth()->user()->hasPermission('users.create'))
                <flux:button size="sm" icon="plus" variant="primary" wire:click="$dispatch('openCreateModal')">
                    Add User
                </flux:button>
            @endif

            <flux:spacer />

            <div class="px-3 py-1 text-xs font-semibold text-zinc-500 bg-zinc-100 dark:bg-zinc-700 rounded-md">
                Count: {{ $users->total() }}
            </div>
        </div>
    </header>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Role</th>
                    @if(auth()->user()->hasPermission('users.edit') || auth()->user()->hasPermission('users.delete'))
                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-zinc-600 dark:text-zinc-400">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            @if($user->role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    {{ $user->role->name }}
                                </span>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </td>
                        @if(auth()->user()->hasPermission('users.edit') || auth()->user()->hasPermission('users.delete'))
                            @php $canModify = !$user->hasRole('admin') || auth()->id() === $user->id; @endphp
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                @if(auth()->user()->hasPermission('users.edit') && $canModify)
                                    <flux:button variant="ghost" size="xs" icon="pencil" circle
                                        wire:click="$dispatch('openEditModal', { user: {{ $user->id }} })" />
                                @endif
                                @if(auth()->user()->hasPermission('users.delete') && $canModify)
                                    <flux:button variant="ghost" size="xs" icon="trash" circle
                                        wire:click="$dispatch('openDeleteModal', { user: {{ $user->id }} })" />
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-zinc-400">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    <livewire:r-b-s-materials.users.user-create />
    <livewire:r-b-s-materials.users.user-edit />
    <livewire:r-b-s-materials.users.user-delete />
</div>

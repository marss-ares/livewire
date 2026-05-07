<div>
    <header class="relative mb-6 flex flex-col gap-4">
        <div class="flex items-center justify-between w-full">
            <flux:heading size="xl" level="1" class="!font-bold tracking-tight">
                Roles
            </flux:heading>
            <div class="flex-none">
                <flux:input size="sm" icon="magnifying-glass" placeholder="Search roles..."
                    wire:model.live="search" class="w-64"
                    autocomplete="off" readonly
                    x-on:focus="$el.removeAttribute('readonly')"
                    x-on:blur="$el.setAttribute('readonly', '')" />
            </div>
        </div>

        <div class="relative z-10 flex items-center gap-2 bg-white dark:bg-zinc-800 p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            @if(auth()->user()->hasPermission('roles.create'))
                <flux:button size="sm" icon="plus" variant="primary" wire:click="$dispatch('openCreateRoleModal')">
                    Add Role
                </flux:button>
            @endif

            <flux:spacer />

            <div class="px-3 py-1 text-xs font-semibold text-zinc-500 bg-zinc-100 dark:bg-zinc-700 rounded-md">
                Total: {{ $roles->total() }}
            </div>
        </div>
    </header>

    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Users</th>
                    @if(auth()->user()->hasPermission('roles.edit') || auth()->user()->hasPermission('roles.delete'))
                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @forelse ($roles as $role)
                    <tr wire:key="role-{{ $role->id }}"
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $role->name }}</div>
                                @if($role->slug === 'admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                        System
                                    </span>
                                @endif
                            </div>
                            @if($role->description)
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $role->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300">
                                {{ $role->slug }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                {{ $role->permissions_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                {{ $role->users_count }}
                            </span>
                        </td>
                        @if(auth()->user()->hasPermission('roles.edit') || auth()->user()->hasPermission('roles.delete'))
                            <td class="px-6 py-4 text-right space-x-1">
                                @if($role->slug === 'admin')
                                    <flux:tooltip content="Admin role cannot be edited">
                                        <flux:button variant="ghost" size="xs" icon="pencil" circle disabled />
                                    </flux:tooltip>
                                    <flux:tooltip content="Admin role cannot be deleted">
                                        <flux:button variant="ghost" size="xs" icon="trash" circle disabled />
                                    </flux:tooltip>
                                @else
                                    @if(auth()->user()->hasPermission('roles.edit'))
                                        <flux:button variant="ghost" size="xs" icon="pencil" circle
                                            wire:click="$dispatch('openEditRoleModal', { role: {{ $role->id }} })" />
                                    @endif
                                    @if(auth()->user()->hasPermission('roles.delete'))
                                        <flux:button variant="ghost" size="xs" icon="trash" circle
                                            wire:click="$dispatch('openDeleteRoleModal', { role: {{ $role->id }} })" />
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-400">
                            No roles found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $roles->links() }}</div>

    <livewire:r-b-s-materials.roles.role-create />
    <livewire:r-b-s-materials.roles.role-edit />
    <livewire:r-b-s-materials.roles.role-delete />
</div>

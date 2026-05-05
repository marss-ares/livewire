<flux:modal wire:model="showModal" class="md:w-[560px] space-y-6">
    <div>
        <flux:heading size="lg">Edit Role</flux:heading>
        <flux:subheading>Modify the role details and permissions.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input label="Role Name" wire:model="name" />
        <flux:input label="Slug" wire:model="slug" />
        <flux:input label="Description" wire:model="description" />

        <div>
            <flux:label class="mb-3">Permissions</flux:label>
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                @foreach ($permissionsByCategory as $category => $permissions)
                    <div class="border-b border-zinc-200 dark:border-zinc-700 last:border-0">
                        <div class="bg-zinc-50 dark:bg-zinc-900/30 px-3 py-2">
                            <h4 class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                                {{ $category }}
                            </h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3 p-3">
                            @foreach ($permissions as $permission)
                                <label class="flex items-center gap-2 cursor-pointer select-none text-sm text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-zinc-100">
                                    <input
                                        type="checkbox"
                                        value="{{ $permission->id }}"
                                        wire:model="selectedPermissions"
                                        class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    {{ $permission->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Update Role</flux:button>
    </div>
</flux:modal>

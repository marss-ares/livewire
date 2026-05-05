<flux:modal wire:model="showModal" class="md:w-[420px] space-y-6">
    <div>
        <flux:heading size="lg">Delete Role</flux:heading>
        <flux:subheading>This action cannot be undone.</flux:subheading>
    </div>

    <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Are you sure you want to delete the role
        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $roleName }}</span>?
        Users with this role will lose associated permissions.
    </p>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="danger" wire:click="delete">Delete</flux:button>
    </div>
</flux:modal>

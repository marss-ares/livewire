<flux:modal wire:model="showModal" class="md:w-[420px] space-y-6">
    <div>
        <flux:heading size="lg">Delete Form</flux:heading>
        <flux:subheading>This will also delete all columns and entries.</flux:subheading>
    </div>

    <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Are you sure you want to delete
        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $formName }}</span>?
        This action cannot be undone.
    </p>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="danger" wire:click="delete">Delete</flux:button>
    </div>
</flux:modal>

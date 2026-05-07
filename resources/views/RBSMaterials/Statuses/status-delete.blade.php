<flux:modal wire:model="showModal" class="md:w-[380px] space-y-5">
    <div>
        <flux:heading size="lg">Delete Status</flux:heading>
        <flux:subheading>Entries using this status will lose their status value.</flux:subheading>
    </div>

    <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Are you sure you want to delete
        <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $statusName }}</span>?
        This action cannot be undone.
    </p>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="danger" wire:click="delete">Delete Status</flux:button>
    </div>
</flux:modal>

<flux:modal wire:model="showModal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">Delete User</flux:heading>

        @if ($isSelf)
            <flux:subheading class="text-red-500 font-bold">
                You can not delete your own account.
            </flux:subheading>
        @else
            <flux:subheading>
                Are you sure you want to delete this user? This action cannot be undone.
            </flux:subheading>
        @endif
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>


        @if ($isSelf)
            <flux:button variant="filled" wire:click="delete"  :disabled="$isSelf" >Delete User</flux:button>
        @else
            <flux:button variant="danger" wire:click="delete" >Delete User</flux:button>
        @endif


    </div>
</flux:modal>

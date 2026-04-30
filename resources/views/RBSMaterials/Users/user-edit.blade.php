<flux:modal wire:model="showModal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">Edit User</flux:heading>
        <flux:subheading>Modify the details of the selected user.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input label="Name" placeholder="e.g. John Doe" wire:model="name" />
        <flux:input label="Email" type="email" placeholder="john@example.com" wire:model="email" />
        <flux:input label="Password" type="password" wire:model="password" 
            description="Leave blank if you don't want to change the password" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="update">Update</flux:button>
    </div>
</flux:modal>
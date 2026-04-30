<flux:modal wire:model="showModal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">Add New User</flux:heading>
        <flux:subheading>Enter the details for the new team member.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input label="Name" placeholder="e.g., John Doe" wire:model="name" />
        <flux:input label="Email" type="email" placeholder="john@example.com" wire:model="email" />
        <flux:input label="Password" type="password" wire:model="password" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Save User</flux:button>
    </div>
</flux:modal>
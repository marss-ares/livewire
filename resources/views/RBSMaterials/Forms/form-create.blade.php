<flux:modal wire:model="showModal" class="md:w-[480px] space-y-6">
    <div>
        <flux:heading size="lg">Create New Form</flux:heading>
        <flux:subheading>You'll be able to add fields after creation.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input label="Form Name" placeholder="e.g. Contact Form" wire:model="name" />
        <flux:input label="Description" placeholder="Optional description..." wire:model="description" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Create & Add Fields</flux:button>
    </div>
</flux:modal>

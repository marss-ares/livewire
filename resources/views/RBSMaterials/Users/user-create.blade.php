<flux:modal wire:model="showModal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">Add New User</flux:heading>
        <flux:subheading>Enter the details for the new team member.</flux:subheading>
    </div>

    <div class="space-y-4">
        <flux:input label="Name" placeholder="e.g., John Doe" wire:model="name" />
        <flux:input label="Email" type="email" placeholder="john@example.com" wire:model="email" />
        <flux:input label="Password" type="password" wire:model="password" />
        <div>
            <flux:label class="mb-1">Role (Optional)</flux:label>
            <select wire:model="roleId"
                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">— No Role —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Save User</flux:button>
    </div>
</flux:modal>

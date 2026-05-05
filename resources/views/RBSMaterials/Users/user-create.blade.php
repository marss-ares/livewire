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
            <flux:label class="mb-1">Role <span class="text-red-500">*</span></flux:label>
            <select wire:model="roleId"
                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                       bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100
                       @error('roleId') border-red-400 dark:border-red-500 @else border-zinc-300 dark:border-zinc-600 @enderror">
                <option value="">— Select a role —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('roleId')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Save User</flux:button>
    </div>
</flux:modal>

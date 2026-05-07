<flux:modal wire:model="showModal" class="md:w-[420px] space-y-5">
    <div>
        <flux:heading size="lg">Add New Status</flux:heading>
        <flux:subheading>Choose a name and pick a color for the new status.</flux:subheading>
    </div>

    <flux:input label="Name" placeholder="e.g. Pending Review" wire:model="name" autofocus />

    <div x-data="{ hex: @entangle('color').live }">
        <flux:label class="mb-2">Color</flux:label>
        <div class="flex items-center gap-3">
            <input
                type="color"
                x-model="hex"
                @change="$wire.color = hex"
                class="w-10 h-10 rounded-lg cursor-pointer border border-zinc-200 dark:border-zinc-600 p-0.5 bg-white dark:bg-zinc-800"
                title="Pick a color"
            />
            <input
                type="text"
                x-model="hex"
                @blur="if (/^#[0-9a-fA-F]{6}$/.test(hex)) $wire.color = hex"
                @keydown.enter="if (/^#[0-9a-fA-F]{6}$/.test(hex)) $wire.color = hex"
                maxlength="7"
                placeholder="#3b82f6"
                class="font-mono text-sm w-28 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-400"
            />
            <span class="w-8 h-8 rounded-full shrink-0 border border-black/10 transition-colors"
                  :style="`background-color: ${hex}`"></span>
        </div>
        @error('color') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
        <flux:button variant="primary" wire:click="save">Save Status</flux:button>
    </div>
</flux:modal>

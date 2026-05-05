<flux:modal wire:model="showModal" class="md:w-[520px] space-y-6">
    <div>
        <flux:heading size="lg">Import Excel File</flux:heading>
        <flux:subheading>
            The first row must contain the column headers. Each subsequent row becomes an entry.
        </flux:subheading>
    </div>

    @if(!$importing)
        <div class="space-y-4">
            <flux:input
                label="Table Name"
                placeholder="e.g. Sales Report Q1"
                wire:model="formName"
            />

            {{-- File drop zone --}}
            <div>
                <flux:label class="mb-2">Excel File (.xlsx, .xls, .csv)</flux:label>
                <label
                    class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer
                           border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/30
                           hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-colors">
                    <div class="flex flex-col items-center gap-2 text-zinc-500 dark:text-zinc-400">
                        @if($file)
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ $file->getClientOriginalName() }}
                            </span>
                            <span class="text-xs text-zinc-400">Click to change</span>
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span class="text-sm font-medium">Drop file here or click to browse</span>
                            <span class="text-xs">.xlsx, .xls, .csv — max 10MB</span>
                        @endif
                    </div>
                    <input type="file" class="hidden" wire:model="file" accept=".xlsx,.xls,.csv" />
                </label>
                @error('file') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Preview info --}}
            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">How it works:</p>
                <ul class="text-xs text-blue-600 dark:text-blue-400 space-y-0.5 list-disc list-inside">
                    <li>Row 1 → Column headers (Name, Email, Phone...)</li>
                    <li>Row 2+ → Data rows (one entry per row)</li>
                    <li>Empty rows are skipped automatically</li>
                </ul>
            </div>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
            <flux:button variant="primary" icon="arrow-up-tray" wire:click="import"
                :disabled="!$file || !$formName">
                Import
            </flux:button>
        </div>
    @else
        {{-- Importing state --}}
        <div class="flex flex-col items-center gap-4 py-6">
            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <div class="text-center">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Importing...</p>
                <p class="text-sm text-zinc-500 mt-1">{{ $imported }} / {{ $total }} rows</p>
            </div>
        </div>
    @endif
</flux:modal>

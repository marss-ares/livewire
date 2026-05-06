<flux:modal wire:model="showModal" class="md:w-[680px] space-y-6">

    {{-- ─── STEP 1: Upload ─── --}}
    @if($step === 1)

        <div>
            <flux:heading size="lg">Import from Excel / CSV</flux:heading>
            <flux:subheading>
                Row 1 must contain column headers. Each subsequent row becomes an entry.
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:input
                label="Table Name"
                placeholder="e.g. Sales Leads Q2"
                wire:model="formName"
            />

            <div>
                <flux:label class="mb-2">File (.xlsx, .xls, .csv)</flux:label>
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
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
            <flux:button variant="primary" icon="arrow-right" wire:click="parseFile"
                :disabled="!$file || !$formName">
                Preview Columns
            </flux:button>
        </div>

    {{-- ─── STEP 2: Map columns ─── --}}
    @elseif($step === 2 && !$importing)

        <div>
            <flux:heading size="lg">Map Columns</flux:heading>
            <flux:subheading>
                Assign each column from
                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $file?->getClientOriginalName() }}</span>
                to the correct field.
            </flux:subheading>
        </div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide w-[45%]">
                            File Column
                        </th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wide w-[55%]">
                            Maps To
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @foreach($fileHeaders as $index => $header)
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-zinc-400 font-mono">{{ $fileLetters[$index] }}</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-200 truncate max-w-[180px]">
                                        {{ $header }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <select
                                    wire:model="mapping.{{ $index }}"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600
                                           bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200
                                           text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500
                                           focus:border-transparent transition-colors">
                                    <option value="skip">— Skip this column —</option>
                                    <optgroup label="System Columns">
                                        <option value="system:full_name">Full Name</option>
                                        <option value="system:phone">Phone</option>
                                        <option value="system:location">Location</option>
                                    </optgroup>
                                    <optgroup label="Entry Fields">
                                        <option value="entry:status">Status</option>
                                        <option value="entry:source">Source</option>
                                        <option value="entry:owner">Owner</option>
                                    </optgroup>
                                    <optgroup label="Custom">
                                        <option value="new">Create as new column</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary --}}
        @php
            $cSystem = collect($mapping)->filter(fn($v) => str_starts_with($v, 'system:'))->count();
            $cEntry  = collect($mapping)->filter(fn($v) => str_starts_with($v, 'entry:'))->count();
            $cNew    = collect($mapping)->filter(fn($v) => $v === 'new')->count();
            $cSkip   = collect($mapping)->filter(fn($v) => $v === 'skip')->count();
        @endphp
        <div class="flex flex-wrap gap-2 text-xs">
            @if($cSystem)
                <span class="px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium">
                    {{ $cSystem }} system {{ Str::plural('column', $cSystem) }}
                </span>
            @endif
            @if($cEntry)
                <span class="px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-medium">
                    {{ $cEntry }} entry {{ Str::plural('field', $cEntry) }}
                </span>
            @endif
            @if($cNew)
                <span class="px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 font-medium">
                    {{ $cNew }} new {{ Str::plural('column', $cNew) }}
                </span>
            @endif
            @if($cSkip)
                <span class="px-2.5 py-1 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-500 font-medium">
                    {{ $cSkip }} skipped
                </span>
            @endif
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" icon="arrow-left" wire:click="back">Back</flux:button>
            <flux:spacer />
            <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
            <flux:button variant="primary" icon="arrow-up-tray" wire:click="import"
                :disabled="($cSystem + $cEntry + $cNew) === 0">
                Import {{ count($fileHeaders) - $cSkip }} {{ Str::plural('column', count($fileHeaders) - $cSkip) }}
            </flux:button>
        </div>

    {{-- ─── Importing spinner ─── --}}
    @else

        <div class="flex flex-col items-center gap-4 py-8">
            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <div class="text-center">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">Importing...</p>
                <p class="text-sm text-zinc-500 mt-1">{{ $imported }} / {{ $total }} rows</p>
            </div>
        </div>

    @endif

</flux:modal>

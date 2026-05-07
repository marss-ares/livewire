<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6 flex-wrap">
        <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('forms.index')" wire:navigate />
        <div>
            <flux:heading size="xl" level="1" class="!font-bold tracking-tight">
                {{ $form->name }}
            </flux:heading>
            <p class="text-sm text-zinc-400 mt-0.5">
                {{ $columns->count() }} columns · {{ $entries->count() }} rows
            </p>
        </div>
    </div>

    @if($columns->isEmpty())
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-12 text-center text-zinc-400 shadow-sm">
            This file has no columns.
        </div>
    @elseif($entries->isEmpty())
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-12 text-center text-zinc-400 shadow-sm">
            No data in this file.
        </div>
    @else
        {{-- Excel-style table --}}
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    {{-- Row numbers column header + column headers --}}
                    <thead>
                        <tr class="bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-300 dark:border-zinc-600">
                            {{-- Row number cell (like Excel row numbers) --}}
                            <th class="w-12 px-3 py-2 text-center text-xs font-semibold text-zinc-400 border-r border-zinc-300 dark:border-zinc-600 select-none sticky left-0 bg-zinc-100 dark:bg-zinc-900">
                                #
                            </th>
                            {{-- Column headers (like Excel A, B, C...) --}}
                            @foreach ($columns as $col)
                                <th class="px-4 py-2 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300 border-r border-zinc-200 dark:border-zinc-700 whitespace-nowrap min-w-[120px]">
                                    {{ $col->name }}
                                </th>
                            @endforeach
                            {{-- Actions column --}}
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-400 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $i => $entry)
                            <tr wire:key="entry-{{ $entry->id }}"
                                class="border-b border-zinc-100 dark:border-zinc-700/50 hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors group">
                                {{-- Row number --}}
                                <td class="px-3 py-2 text-center text-xs text-zinc-400 border-r border-zinc-200 dark:border-zinc-700 select-none sticky left-0 bg-white dark:bg-zinc-800 group-hover:bg-blue-50/40 dark:group-hover:bg-blue-900/10">
                                    {{ $loop->iteration }}
                                </td>
                                {{-- Values for each column --}}
                                @foreach ($columns as $col)
                                    <td class="px-4 py-2 border-r border-zinc-100 dark:border-zinc-700/50 text-zinc-700 dark:text-zinc-300 whitespace-nowrap max-w-xs truncate">
                                        {{ $entry->valueFor($col->id) ?? '' }}
                                    </td>
                                @endforeach
                                {{-- Delete action --}}
                                <td class="px-3 py-2 text-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button variant="ghost" size="xs" icon="trash" circle
                                        wire:click="deleteEntry({{ $entry->id }})"
                                        wire:confirm="Delete this row?" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif
</div>

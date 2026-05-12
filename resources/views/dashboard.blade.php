<x-layouts::app :title="__('Dashboard')">
    @php
        $totalForms = \App\Models\Form::count();
        $totalEntries = \App\Models\FormEntry::count();
        $totalUsers = \App\Models\User::count();
        $recentEntries = \App\Models\FormEntry::with(['form', 'submitter', 'status'])->latest()->limit(5)->get();
        $recentForms = \App\Models\Form::with(['owner'])->withCount('entries')->latest()->limit(5)->get();
        $statuses = \App\Models\FormEntryStatus::withCount('entries')->get();
        $topContributors = \App\Models\User::join('form_entries', 'users.id', '=', 'form_entries.user_id')
            ->selectRaw('users.id, users.name, count(*) as submission_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('submission_count')
            ->limit(5)
            ->get();
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Stats Cards --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            {{-- Total Forms --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Forms</p>
                        <p class="text-3xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalForms }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Active forms in system</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Entries --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Entries</p>
                        <p class="text-3xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalEntries }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">Form submissions</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Total Users</p>
                        <p class="text-3xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalUsers }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">System users</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Entries & Recent Forms Row --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Recent Entries --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Recent Entries</h3>
                <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                    @forelse($recentEntries as $entry)
                        <div class="flex items-center gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-700 last:border-b-0">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $entry->status?->color ?? '#d1d5db' }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ $entry->form->name }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $entry->submitter?->name ?? 'Anonymous' }}</p>
                            </div>
                            <span class="text-xs text-neutral-400 whitespace-nowrap">{{ $entry->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No entries yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Forms --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Recent Forms</h3>
                <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                    @forelse($recentForms as $form)
                        <div class="flex items-center gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-700 last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ $form->name }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $form->entries_count }} entries • {{ $form->owner?->name ?? 'No owner' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No forms yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Status Distribution & Top Contributors --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Status Distribution --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Entry Status Breakdown</h3>
                <div class="space-y-3">
                    @if($statuses->isNotEmpty())
                        @php
                            $totalStatusEntries = $statuses->sum('entries_count');
                        @endphp
                        @foreach($statuses as $status)
                            @php
                                $percentage = $totalStatusEntries > 0 ? ($status->entries_count / $totalStatusEntries) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1 items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $status->color }}"></span>
                                        <span class="text-neutral-600 dark:text-neutral-400">{{ $status->name }}</span>
                                    </div>
                                    <span class="font-semibold text-neutral-900 dark:text-white">{{ $status->entries_count }}</span>
                                </div>
                                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all" style="width: {{ $percentage }}%; background-color: {{ $status->color }}"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No statuses defined</p>
                    @endif
                </div>
            </div>

            {{-- Top Contributors --}}
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-4">Top Contributors</h3>
                <div class="space-y-3">
                    @forelse($topContributors as $idx => $user)
                        <div class="flex items-center gap-3 pb-3 border-b border-neutral-200 dark:border-neutral-700 last:border-b-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-xs font-semibold text-white">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $user->submission_count }} submission{{ $user->submission_count != 1 ? 's' : '' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">#{{ $idx + 1 }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center py-4">No contributors yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</x-layouts::app>

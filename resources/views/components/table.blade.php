@props([
    'headers' => [],
    'rows' => []
])

<div {{ $attributes->merge(['class' => 'overflow-hidden border border-zinc-300 dark:border-zinc-700 rounded-xl shadow-sm bg-white dark:bg-zinc-800']) }}>
    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
        <thead class="bg-zinc-50 dark:bg-zinc-900/50">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider {{ $header['class'] ?? '' }}">
                        {{ $header['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-800">
            {{ $slot }}

            @if($rows->isEmpty())
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-10 text-center text-zinc-400">
                        Nu am găsit nicio înregistrare.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
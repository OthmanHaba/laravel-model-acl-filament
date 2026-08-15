<x-filament-panels::page>
    {{ $this->form }}

    @php($rows = $this->rows)

    @if (filled($rows))
        @php($summary = $this->summary)

        <div class="flex flex-wrap gap-2">
            <x-filament::badge color="success">Can {{ strtolower($summary['label']) }}: {{ $summary['granted'] }}</x-filament::badge>
            <x-filament::badge color="danger">Cannot: {{ $summary['denied'] }}</x-filament::badge>
            @if ($summary['noRule'])
                <x-filament::badge color="gray">No rule: {{ $summary['noRule'] }}</x-filament::badge>
            @endif
        </div>

        <x-filament::section>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2">Record</th>
                        <th class="py-2 text-right">Access</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="py-2">{{ $row['title'] }}</td>
                            <td class="py-2 text-right">
                                @if ($row['decision'] === true)
                                    <x-filament::badge color="success">Granted</x-filament::badge>
                                @elseif ($row['decision'] === false)
                                    <x-filament::badge color="danger">Denied</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray">No rule</x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @else
        <x-filament::section>
            <p class="text-sm text-gray-500">Pick a user, a model and an action to see what they can and can't access.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>

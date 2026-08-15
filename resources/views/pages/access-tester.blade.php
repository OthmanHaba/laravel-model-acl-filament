<x-filament-panels::page>
    <form wire:submit="runTest" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Run access test
        </x-filament::button>
    </form>

    @if ($tested)
        @php
            $color = $decision === true ? 'success' : ($decision === false ? 'danger' : 'gray');
            $label = $decision === true
                ? 'Granted'
                : ($decision === false ? 'Denied' : 'No opinion (no applicable rules)');
        @endphp

        <div class="mt-6">
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium">Result:</span>
                    <x-filament::badge :color="$color">{{ $label }}</x-filament::badge>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>

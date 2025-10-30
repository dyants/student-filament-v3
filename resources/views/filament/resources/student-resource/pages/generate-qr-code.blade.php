<x-filament-panels::page>
{!! QrCode::size(200)->generate($this->getRecord()->email); !!}
{{ $this->getRecord()->email }}
</x-filament-panels::page>

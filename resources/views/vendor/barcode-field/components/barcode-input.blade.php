<script src="https://unpkg.com/@zxing/library@latest"></script>
<script src="{{ asset('vendor/barcode-field/barcode-scanner.js') }}"></script>

<div class="grid gap-y-2">
    <div class="flex items-center gap-x-3 justify-between">
        <label for="{{ $getId() }}" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ $getLabel() ?? 'Barcode Input' }}
                @if($isRequired())
                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                @endif
            </span>
        </label>
    </div>

    <x-filament::input.wrapper :attributes="$getExtraAttributeBag()">
        <x-filament::input
            type="text"
            name="{{ $getName() }}"
            id="{{ $getId() }}"
            x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }"
            x-model="state"
            placeholder="{{ $getPlaceholder() }}"
            :attributes="$getExtraInputAttributeBag()"
            class="w-full" />

        <x-slot name="suffix">
            <button
                type="button"
                onclick="openScannerModal()"
                class="flex items-center justify-center p-2 focus:outline-none text-gray-400 hover:text-gray-500 dark:text-gray-200"
                aria-label="Scan Barcode">
                @if($icon = ($getExtraAttributes()['icon'] ?? null))
                <x-dynamic-component :component="$icon" class="w-5 h-5" />
                @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 4h2v16H3V4zm4 0h2v16H7V4zm4 0h2v16h-2V4zm4 0h2v16h-2V4zm4 0h2v16h-2V4z" />
                </svg>
                @endif
            </button>
        </x-slot>
    </x-filament::input.wrapper>

    <x-filament::modal id="barcode-scanner-modal">
        <x-slot name="header">
            <h2 class="text-lg font-semibold">
                Scan Barcode
            </h2>
        </x-slot>

        <div class="p-4">
            <div id="scanner-container">
                <video id="scanner" autoplay class="rounded-lg shadow w-full" style="display: none;"></video>
                <div class="overlay mt-2 text-center text-sm text-gray-500">
                    <div class="scan-area">Arahkan kamera ke barcode</div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button onclick="closeScannerModal()" color="danger">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
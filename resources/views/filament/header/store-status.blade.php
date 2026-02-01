@php
$setting = \App\Models\Setting::first();
$isTutup = $setting?->is_toko_tutup ?? false;
$onColor = '#22c55e'; // Green-500
$offColor = '#ef4444'; // Red-500
@endphp

<div class="flex items-center mr-4">
    <a href="{{ route('admin.store.toggle') }}" class="inline-flex items-center cursor-pointer group no-underline" style="text-decoration: none;">
        <div class="flex items-center">

            <div class="relative w-10 h-6 rounded-full transition-colors duration-200 flex items-center shadow-inner"
                style="background-color: {{ $isTutup ? $offColor : $onColor }};">

                <div class="h-4 w-4 bg-white rounded-full transition-all duration-200 shadow-md"
                    style="margin-left: {{ $isTutup ? '4px' : '20px' }};">
                </div>
            </div>

            <span class="select-none ms-3 text-sm font-bold whitespace-nowrap"
                style="color: {{ $isTutup ? $offColor : $onColor }}; line-height: 1;">
                {{ $isTutup ? 'TOKO TUTUP' : 'TOKO BUKA' }}
            </span>

        </div>
    </a>
</div>
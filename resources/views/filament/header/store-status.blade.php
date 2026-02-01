@php
$setting = \App\Models\Setting::first();
$isTutup = $setting?->is_toko_tutup ?? false;
$onColor = '#22c55e'; // Green-500
$offColor = '#ef4444'; // Red-500
@endphp

<div class="flex items-center mr-4">
    <a href="{{ route('admin.store.toggle') }}" class="inline-flex items-center cursor-pointer group no-underline" style="text-decoration: none;">
        <div class="flex items-center">
            <!-- Toggle Body -->
            <div class="relative w-10 h-5 rounded-full transition-colors duration-200"
                style="background-color: {{ $isTutup ? $offColor : $onColor }};">

                <!-- Toggle Knob -->
                <div class="absolute top-[2px] h-4 w-4 bg-white rounded-full transition-all duration-200 shadow-sm"
                    style="left: {{ $isTutup ? '2px' : '22px' }};">
                </div>
            </div>

            <!-- Label -->
            <span class="select-none ms-3 text-sm font-bold whitespace-nowrap"
                style="color: {{ $isTutup ? $offColor : $onColor }};">
                {{ $isTutup ? 'TOKO TUTUP' : 'TOKO BUKA' }}
            </span>
        </div>
    </a>
</div>
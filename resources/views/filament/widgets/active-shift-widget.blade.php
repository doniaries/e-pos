<x-filament-widgets::widget>
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-clock class="w-6 h-6 text-primary-600" />
                    Manajemen Shift Kerja
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pastikan shift Anda sudah benar sebelum melakukan transaksi.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex flex-col items-end">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Shift Aktif</span>
                    <span class="text-sm font-medium {{ $activeShiftId ? 'text-primary-600' : 'text-danger-600' }}">
                        {{ $currentShift?->nama ?? 'Belum Dipilih' }}
                    </span>
                    @if($currentShift)
                    <span class="text-[10px] text-gray-400">
                        ({{ $currentShift->jam_mulai }} - {{ $currentShift->jam_selesai }})
                    </span>
                    @endif
                </div>

                <div class="w-64">
                    <select
                        wire:model.live="activeShiftId"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->nama }} ({{ $shift->jam_mulai }} - {{ $shift->jam_selesai }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
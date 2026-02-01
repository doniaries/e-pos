@if ($showShiftModal)
<div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-90" @click="$wire.set('showShiftModal', false)"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all w-full max-w-md border border-gray-100 dark:border-gray-700">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 border-b dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pilih Shift
                </h3>
                <button @click="$wire.set('showShiftModal', false)" class="text-white hover:text-gray-200 bg-white/20 hover:bg-white/30 p-2 rounded-full transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Pilih shift kerja Anda untuk melanjutkan transaksi:
            </p>

            <div class="space-y-3">
                @foreach ($this->availableShifts as $shift)
                <button
                    wire:click="changeShift({{ $shift->id }})"
                    class="w-full p-4 rounded-xl border-2 transition-all {{ $activeShiftId === $shift->id ? 'bg-blue-50 border-blue-500 dark:bg-blue-900/50 dark:border-blue-500' : 'bg-white border-gray-200 hover:border-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:hover:border-blue-400' }}"
                    wire:key="shift-{{ $shift->id }}">
                    <div class="flex items-center justify-between">
                        <div class="text-left">
                            <div class="font-bold text-lg text-gray-900 dark:text-white">
                                {{ $shift->nama }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($shift->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->jam_selesai)->format('H:i') }}
                            </div>
                        </div>
                        @if ($activeShiftId === $shift->id)
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <button
                @click="$wire.set('showShiftModal', false)"
                class="w-full px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-bold transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif
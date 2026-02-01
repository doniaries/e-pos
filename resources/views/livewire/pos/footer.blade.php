<div class="bg-slate-900 border-t border-slate-700 px-2 md:px-6 py-2 md:py-2 flex flex-col md:flex-row justify-between items-center h-auto md:h-[50px] z-30 select-none text-slate-300 gap-2 md:gap-0">
    <!-- Shortcuts Grid - 2 rows on mobile, 1 row on desktop -->
    <div class="w-full md:w-auto grid grid-cols-4 md:flex md:flex-row gap-1 md:gap-4 text-xs font-bold">
        <!-- F2: Cari -->
        <div @click="showProductTable = !showProductTable"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F2</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider md:tracking-wider">Cari</span>
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Daftar Produk (F2)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F4: Tunda -->
        <div @click="$wire.pendingTransaction()"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F4</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider">Tunda</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Tunda Transaksi (F4)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F8: Riwayat -->
        <div @click="showHistoryModal = !showHistoryModal"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F8</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider text-blue-400">Riwayat</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Riwayat Penjualan (F8)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F9: Draft -->
        <div @click="showPendingModal = !showPendingModal"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F9</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider text-amber-500">Draft</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Daftar Draft (F9)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F10: Tutup Hari -->
        <div @click="showCloseDayModal = !showCloseDayModal"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F10</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider text-red-500">Tutup</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Tutup Hari (F10)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F11: Fullscreen -->
        <div @click="toggleFullScreen()"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F11</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider">Layar</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Toggle Fullscreen (F11)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- F12: Batalkan -->
        <div @click="$wire.resetCart()"
            class="relative group flex flex-col md:flex-row items-center justify-center md:justify-start gap-0.5 md:gap-1.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 md:py-1 rounded-md active:scale-95">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <kbd class="hidden md:inline px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F12</kbd>
            </div>
            <span class="text-[9px] md:text-xs uppercase tracking-wider text-gray-400">Batal</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Batalkan Transaksi (F12)
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>

        <!-- ESC: Reset (Mobile only) -->
        <div @click="showProductTable = false; showHistoryModal = false; showPendingModal = false; showCloseDayModal = false; $wire.resetCart()"
            class="relative group md:hidden flex flex-col items-center justify-center gap-0.5 opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1.5 rounded-md active:scale-95">
            <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="text-[9px] uppercase tracking-wider text-red-400">ESC</span>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-800 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 border border-slate-600">
                Reset / Tutup Modal
                <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-800"></div>
            </div>
        </div>
    </div>

    <!-- App Info -->
    <div class="flex items-center gap-3 text-[9px] md:text-[10px] font-bold text-slate-500">
        @php $app = \App\Models\Aplikasi::first(); @endphp
        <span class="bg-slate-800/50 px-2 py-0.5 rounded border border-slate-700/50">
            {{ $app->nama_aplikasi ?? 'POS App' }} v{{ $app->version ?? '1.2' }}
        </span>
        <span class="hidden lg:inline whitespace-nowrap">© {{ $app->tahun ?? date('Y') }} {{ $app->pembuat ?? 'Developer' }}</span>
    </div>

    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</div>
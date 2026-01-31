<div class="bg-slate-900 border-t border-slate-700 px-6 py-2 flex justify-between items-center h-[50px] z-30 select-none text-slate-300">
    <div class="flex items-center gap-4 text-xs font-bold overflow-hidden h-full">
        <!-- F2: Cari -->
        <div @click="showProductTable = !showProductTable"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm group-hover:bg-slate-700">F2</kbd>
            <span class="uppercase tracking-wider">Cari</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F4: Tunda -->
        <div @click="$wire.pendingTransaction()"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F4</kbd>
            <span class="uppercase tracking-wider">Tunda</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F8: Riwayat -->
        <div @click="showHistoryModal = !showHistoryModal"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F8</kbd>
            <span class="uppercase tracking-wider text-blue-400">Riwayat</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F9: Draft -->
        <div @click="showPendingModal = !showPendingModal"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F9</kbd>
            <span class="uppercase tracking-wider text-amber-500">Draft</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F10: Tutup Hari -->
        <div @click="showCloseDayModal = !showCloseDayModal"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F10</kbd>
            <span class="uppercase tracking-wider text-red-500">Tutup Hari</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F11: Fullscreen -->
        <div @click="toggleFullScreen()"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F11</kbd>
            <span class="uppercase tracking-wider">Layar</span>
        </div>
        <div class="w-px h-3 bg-slate-700"></div>

        <!-- F12: Batalkan -->
        <div @click="$wire.resetCart()"
            class="flex items-center gap-1.5 whitespace-nowrap opacity-80 hover:opacity-100 transition-all cursor-pointer hover:bg-slate-800 px-2 py-1 rounded-md active:scale-95">
            <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-600 rounded text-[10px] font-mono shadow-sm">F12</kbd>
            <span class="uppercase tracking-wider text-gray-400">Batalkan</span>
        </div>

    </div>

    <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500">
        <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500">

            @php $app = \App\Models\Aplikasi::first(); @endphp
            <span class="bg-slate-800/50 px-2 py-0.5 rounded border border-slate-700/50">
                {{ $app->nama_aplikasi ?? 'POS App' }} v{{ $app->version ?? '1.2' }}
            </span>
            <span class="hidden lg:inline whitespace-nowrap">© {{ $app->tahun ?? date('Y') }} {{ $app->pembuat ?? 'Developer' }}</span>
        </div>
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
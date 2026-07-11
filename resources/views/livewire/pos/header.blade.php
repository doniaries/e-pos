<div class="bg-gradient-to-r from-blue-600 to-indigo-600 border-b border-blue-500 px-4 md:px-6 py-2 md:py-3 flex justify-between items-center shadow-lg h-[50px] md:h-[60px] z-30 relative transition-colors duration-300">
    <!-- Left: App Name + Printer Switch -->
    <div class="flex-shrink-0 flex items-center gap-2 md:gap-4">
        @php $app = \App\Models\Setting::first(); @endphp
        <div class="flex items-center gap-2 md:gap-3">
            <!-- App Name -->
            <h1 class="text-base md:text-xl font-black text-white tracking-wider md:tracking-widest uppercase truncate max-w-[80px] md:max-w-none">{{ $app->nama_aplikasi ?? 'POS' }}</h1>

            <!-- Printer Mode Toggle & Status -->
            <div class="flex items-center bg-white/10 rounded-lg p-0.5 md:p-1 border border-white/10 backdrop-blur-sm shadow-sm transition-all duration-300"
                x-data="{ 
                      btConnected: false,
                      mode: @entangle('printerConnectionMode')
                  }"
                x-init="
                      if (window.connectedPrinter) btConnected = true;
                      window.addEventListener('bluetooth-connected', () => btConnected = true);
                      window.addEventListener('bluetooth-disconnected', () => btConnected = false);
                  ">

                <!-- USB Option -->
                <button
                    @click="$wire.togglePrinterMode('usb')"
                    :class="mode === 'usb' ? 'bg-blue-600 text-white shadow-md' : 'text-blue-100 hover:text-white hover:bg-white/10'"
                    class="px-2 md:px-3 py-1 md:py-1.5 rounded-md text-[9px] md:text-[11px] font-bold transition-all flex items-center gap-1 md:gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-3.5 md:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    USB
                </button>

                <!-- Bluetooth Option -->
                <button
                    @click="$wire.togglePrinterMode('bluetooth'); handleBluetoothConnection()"
                    :class="mode === 'bluetooth' ? 'bg-indigo-600 text-white shadow-md' : 'text-blue-100 hover:text-white hover:bg-white/10'"
                    class="px-2 md:px-3 py-1 md:py-1.5 rounded-md text-[9px] md:text-[11px] font-bold transition-all flex items-center gap-1 md:gap-1.5 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-3.5 md:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    BT

                    <!-- Connection Status Dot -->
                    <span x-show="mode === 'bluetooth'"
                        :class="btConnected ? 'bg-emerald-400' : 'bg-rose-500'"
                        class="w-2 md:w-2.5 h-2 md:h-2.5 rounded-full absolute -top-1 -right-1 border-2 border-gray-900 shadow-sm animate-pulse"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Center: Store Name & Cashier Info (Compact on mobile, full on desktop) -->
    <div class="flex items-center gap-2 md:gap-6 justify-center flex-1 mx-2 overflow-hidden">
        <!-- Store Info -->
        <div class="flex items-center gap-1.5 md:gap-3 overflow-hidden">
            <div class="h-6 w-6 md:h-10 md:w-10 bg-white/20 rounded-full flex-shrink-0 flex items-center justify-center text-white overflow-hidden shadow-inner">
                <svg class="w-3 h-3 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <div class="flex flex-col text-left truncate">
                <h1 class="text-[10px] md:text-lg font-bold text-white tracking-tight leading-none truncate">Toko Jaya Terus</h1>
                <p class="text-[7px] md:text-[10px] text-blue-100 font-medium mt-0.5 truncate hidden sm:block">Jl. Dr. Sutomo 14</p>
            </div>
        </div>

        <div class="w-px h-4 md:h-8 bg-white/20"></div>

        <!-- Cashier Info -->
        <div class="flex items-center gap-1.5 md:gap-3 overflow-hidden">
            <div class="flex flex-col text-left truncate">
                <span class="text-[7px] md:text-[9px] text-blue-200 uppercase font-black tracking-wider md:tracking-widest opacity-70 hidden sm:block">Kasir</span>
                <div class="flex items-center gap-1 md:gap-2">
                    <svg class="w-3 h-3 md:w-4 md:h-4 text-blue-200 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-white font-bold text-[10px] md:text-sm truncate max-w-[60px] md:max-w-none">{{ auth()->user()->name ?? 'Guest' }}</span>
                </div>
            </div>
        </div>

        @if(\App\Helpers\ShiftHelper::shouldUseShift())
        <div class="w-px h-4 md:h-8 bg-white/20"></div>

        <!-- Shift Info -->
        <div class="flex items-center gap-1.5 md:gap-3 overflow-hidden">
            <div class="flex flex-col text-left truncate">
                <span class="text-[7px] md:text-[9px] text-blue-200 uppercase font-black tracking-wider md:tracking-widest opacity-70 hidden sm:block">Shift</span>
                <div class="flex items-center gap-1 md:gap-2">
                    <svg class="w-3 h-3 md:w-4 md:h-4 text-blue-200 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <button wire:click="$set('showShiftModal', true)" class="text-white font-bold text-[10px] md:text-sm truncate max-w-[60px] md:max-w-none hover:text-blue-100 transition-colors">
                        {{ $this->currentShift?->nama ?? 'Pilih Shift' }}
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Date/Time & Dark Mode -->
    <div class="flex items-center gap-2 md:gap-4 flex-shrink-0 justify-end">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" class="p-1.5 md:p-2 rounded-lg bg-white/10 text-white hover:bg-white/20 transition-colors focus:outline-none backdrop-blur-sm">
            <svg x-show="!darkMode" class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="darkMode" x-cloak class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>

        <div class="bg-white/10 rounded-lg px-2 md:px-4 py-1 md:py-2 border border-blue-400/30 backdrop-blur-sm hidden md:block">
            <p class="text-[8px] md:text-xs text-blue-100 font-bold uppercase tracking-wider mb-0.5 hidden lg:block" x-data x-text="new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></p>
            <p class="text-sm md:text-xl font-mono font-bold text-white leading-none" x-data x-init="setInterval(() => $el.innerText = new Date().toLocaleTimeString('id-ID'), 1000)" x-text="new Date().toLocaleTimeString('id-ID')"></p>
        </div>
    </div>
</div>
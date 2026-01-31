<div class="flex flex-col h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden transition-colors duration-300"
    x-data="{
        showNotification: false,
        showErrorNotification: false,
        errorMessage: '',
        showProductAddedNotification: false,
        productAddedData: { name: '', quantity: 0, isNew: false },
        showProductTable: @entangle('showProductTable'),
        showHistoryModal: @entangle('showHistoryModal'),
        showPendingModal: @entangle('showPendingModal'),
        showCloseDayModal: @entangle('showCloseDayModal'),
        showPaymentModal: @entangle('showPaymentModal'),
        showShiftModal: @entangle('showShiftModal'),
        showWarningOldTransactions: @entangle('showWarningOldTransactions'),
        isProcessing: false,
        init() {
            Livewire.on('product-added', (data) => {
                console.log('Product added event:', data);
                this.productAddedData = {
                    name: data[0]?.name || data.name || 'Produk',
                    quantity: data[0]?.quantity || data.quantity || 1,
                    isNew: data[0]?.isNew || data.isNew || false
                };
                this.showProductAddedNotification = true;
                this.isProcessing = false;
                setTimeout(() => { this.showProductAddedNotification = false; }, 3000);
            });

            Livewire.on('transaction-success', (data) => {
                this.isProcessing = false;
                this.showNotification = true;
                setTimeout(() => { this.showNotification = false; }, 3000);
            });

            Livewire.on('open-print-window', (data) => {
                let trx = Array.isArray(data) ? data[0] : data; 
                if (trx && (trx.id || trx.nomor)) {
                    const id = trx.id || trx.nomor;
                    window.open('/pos/print-struk/' + id, '_blank', 'width=400,height=600');
                }
            });

            Livewire.on('pos-error', (data) => {
                this.isProcessing = false;
                let msg = Array.isArray(data) ? data[0]?.message : data.message;
                this.errorMessage = msg || 'Terjadi kesalahan pada pencetakan.';
                this.showErrorNotification = true;
                setTimeout(() => { this.showErrorNotification = false; }, 5000);
            });

            Livewire.on('pos-notification', (data) => {
                let msg = Array.isArray(data) ? data[0]?.message : data.message;
                // Reuse success toast style or add new one. Let's redirect to success toast for simplicity
                // but maybe we should have a generic one.
                this.productAddedData = { name: msg, quantity: '', isNew: true };
                this.showProductAddedNotification = true;
                setTimeout(() => { this.showProductAddedNotification = false; }, 3000);
            });

            // Watchers for Auto-Focus when modals close
            this.$watch('showHistoryModal', value => { if(!value) { this.isProcessing = false; Livewire.dispatch('modal-closed'); } });
            this.$watch('showPendingModal', value => { if(!value) { this.isProcessing = false; Livewire.dispatch('modal-closed'); } });
            this.$watch('showProductTable', value => { if(!value) { this.isProcessing = false; Livewire.dispatch('modal-closed'); } });
            this.$watch('showCloseDayModal', value => { if(!value) { this.isProcessing = false; Livewire.dispatch('modal-closed'); } });
            this.$watch('showPaymentModal', value => { if(!value) { this.isProcessing = false; } });
        }
    }"
    @keydown.window.f2.prevent="showProductTable = !showProductTable"
    @keydown.window.f4.prevent="$wire.pendingTransaction()"
    @keydown.window.f8.prevent="showHistoryModal = !showHistoryModal"
    @keydown.window.f9.prevent="showPendingModal = !showPendingModal"
    @keydown.window.f10.prevent="showCloseDayModal = !showCloseDayModal"
    @keydown.window.f11.prevent="toggleFullScreen()"
    @keydown.window.f12.prevent="$wire.resetCart()"
    @keydown.window.enter.prevent="if(!$wire.showPaymentModal && !showProductTable && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) $wire.openPaymentModal(); else if($wire.showPaymentModal && !isProcessing) { isProcessing = true; $wire.processAndPrint(); }"
    @keydown.window.escape.prevent="showProductTable = false; showHistoryModal = false; showPendingModal = false; showCloseDayModal = false; $wire.resetCart()">

    <!-- Include Header -->
    @include('livewire.pos.header')

    <!-- Error Notification (Custom Toast) -->
    <div x-show="showErrorNotification"
        x-transition:enter="virtual-keyboard-transition transform ease-out duration-300"
        x-transition:enter-start="translate-y-2 opacity-0 scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed top-20 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 border-l-4 border-red-500 shadow-lg rounded-r-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5"
        style="display: none;">
        <div class="flex-1 w-0 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0 pt-0.5">
                    <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Terjadi Kesalahan</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="errorMessage"></p>
                </div>
            </div>
        </div>
        <div class="flex border-l border-gray-200 dark:border-gray-700">
            <button @click="showErrorNotification = false" class="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-red-600 hover:text-red-500 focus:outline-none">
                Tutup
            </button>
        </div>
    </div>

    <!-- Include Style (Moved inside root) -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            margin: 4px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #64748b;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <!-- Top Notifications -->
    <div x-cloak x-show="showNotification" x-transition
        class="absolute top-4 right-4 z-50 px-4 py-2 bg-green-500 text-white rounded-lg shadow-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>Transaksi Berhasil!</span>
    </div>

    <!-- Product Added Notification -->
    <div x-cloak x-show="showProductAddedNotification" x-transition
        class="absolute top-4 right-4 z-[95] px-6 py-4 bg-green-600 text-white rounded-xl shadow-2xl border-2 border-green-400 min-w-[380px]">
        <div class="flex items-start gap-4">
            <svg class="w-8 h-8 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <div class="flex-1">
                <p class="font-black text-base mb-2" x-text="productAddedData.isNew ? '✨ Produk Ditambahkan' : '🔄 Jumlah Diperbarui'"></p>
                <p class="text-lg font-bold mb-1.5" x-text="productAddedData.name"></p>
                <p class="text-sm font-semibold opacity-95">
                    Jumlah: <span x-text="productAddedData.quantity"></span> pcs
                </p>
            </div>
        </div>
    </div>
    <!-- Error Notification -->
    <div x-cloak x-show="showErrorNotification" x-transition
        class="absolute top-4 right-4 z-[96] px-6 py-4 bg-red-600 text-white rounded-xl shadow-2xl border-2 border-red-400 min-w-[380px]">
        <div class="flex items-start gap-4">
            <svg class="w-8 h-8 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <p class="font-black text-base mb-2">⚠️ Kesalahan</p>
                <p class="text-lg font-bold mb-1.5" x-text="errorMessage"></p>
            </div>
        </div>
    </div>

    @if (session()->has('error'))
    <div class="absolute top-4 right-4 z-50 px-4 py-2 bg-red-500 text-white rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
    @endif

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
        <!-- FULL WIDTH: Cart & Search -->
        <div class="w-full flex flex-col bg-white dark:bg-gray-900 transition-colors duration-300 min-0">
            <!-- Search Bar Header -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm z-30 relative">
                <div class="relative flex gap-2">
                    <div class="flex-1 relative" x-data="{
                        selectedIndex: -1,
                        resultsCount: 0,
                        init() {
                            const focusInput = () => {
                                this.$nextTick(() => { 
                                    if(this.$refs.searchInput) {
                                        this.$refs.searchInput.focus(); 
                                    }
                                });
                            };

                            // Auto focus on load
                            focusInput();

                            // Re-focus on specific system events
                            Livewire.on('modal-closed', focusInput);
                            Livewire.on('product-added', focusInput);
                            Livewire.on('transaction-success', focusInput);
                            Livewire.on('cart-updated', focusInput);
                            
                            // Re-focus when window regains focus
                            window.addEventListener('focus', focusInput);
                            
                            // Re-focus on UI state changes
                            window.addEventListener('dark-mode-toggled', focusInput);
                            window.addEventListener('bluetooth-connected', focusInput);
                            window.addEventListener('bluetooth-disconnected', focusInput);
                            
                            // Watch for printer mode changes (from header)
                            this.$watch('$wire.printerConnectionMode', focusInput);

                            // Listen for global escape to refocus
                            window.addEventListener('keydown', (e) => {
                                if (e.key === 'Escape') focusInput();
                            });
                        },
                        navigate(dir) {
                            if (this.resultsCount === 0) return;
                            if (dir === 'down') { this.selectedIndex = (this.selectedIndex + 1) % this.resultsCount; }
                            else { this.selectedIndex = (this.selectedIndex - 1 + this.resultsCount) % this.resultsCount; }
                            this.scrollToSelectedItem();
                        },
                        select() {
                            if (this.selectedIndex >= 0) {
                                let el = document.getElementById('search-result-' + this.selectedIndex);
                                if (el) el.click();
                            } else if (this.resultsCount > 0) {
                                // If results exist but none selected, pick the first one
                                let el = document.getElementById('search-result-0');
                                if (el) el.click();
                            } else {
                                // Scanner Case: Bypass debounce and push current raw value immediately
                                let rawValue = this.$refs.searchInput.value ? this.$refs.searchInput.value.trim() : '';
                                if (rawValue) {
                                    $wire.performSearch(rawValue);
                                    // Direct DOM clear for immediate feedback
                                    this.$refs.searchInput.value = '';
                                } else {
                                    // If empty and cart has items, open payment modal
                                    // Using $wire.get('cart') to check items in JS context
                                    let cartItems = $wire.get('cart');
                                    if (cartItems && Object.keys(cartItems).length > 0) {
                                        this.showPaymentModal = true;
                                        $wire.openPaymentModal();
                                    }
                                }
                            }
                        },
                        scrollToSelectedItem() {
                            this.$nextTick(() => {
                                let active = document.getElementById('search-result-' + this.selectedIndex);
                                if (active) active.scrollIntoView({ block: 'nearest' });
                            });
                        }
                    }"
                        @reset-search.window="if(!document.getElementById('modal-search-input')) { $nextTick(() => { if($refs.searchInput) { $refs.searchInput.value = ''; $refs.searchInput.focus(); } selectedIndex = -1; }) }"
                        @keydown.arrow-down.prevent="navigate('down')"
                        @keydown.arrow-up.prevent="navigate('up')"
                        @keydown.enter.stop.prevent="select()">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input x-ref="searchInput" type="text"
                            wire:model.live.debounce.300ms="searchQuery"
                            x-on:input="selectedIndex = -1; resultsCount = $el.dataset.results"
                            placeholder="Scan Barcode / Cari Nama Produk..."
                            class="block w-full pl-10 pr-12 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-black dark:focus:ring-gray-500 focus:border-transparent text-base transition-all shadow-inner"
                            data-results="{{ count($searchResults) }}"
                            autofocus>

                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <!-- Helper Text / Icon -->
                            @if(empty($searchQuery))
                            <svg class="h-6 w-6 text-gray-400 opacity-60 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7V5a2 2 0 012-2h2m10 0h2a2 2 0 012 2v2m0 10v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M9 7h6M9 12h6M9 17h6" />
                            </svg>
                            @else
                            <button wire:click="$set('searchQuery', '')" @click="$nextTick(() => $refs.searchInput.focus())" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @endif
                        </div>

                        @if (!empty($searchQuery))
                        <div x-cloak class="absolute top-full left-0 w-full mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-20">
                            @if(!empty($searchResults) && count($searchResults) > 0)
                            @foreach($searchResults as $index => $product)
                            <div id="search-result-{{ $index }}" wire:key="search-result-{{ $product->id }}"
                                wire:click="selectProduct({{ $product->id }})"
                                @mouseenter="selectedIndex = {{ $index }}"
                                :class="{ 'bg-blue-600 text-white dark:bg-blue-600': selectedIndex === {{ $index }}, 'hover:bg-blue-50 dark:hover:bg-blue-900/20': selectedIndex !== {{ $index }} }"
                                class="p-4 cursor-pointer border-b border-gray-50 dark:border-gray-700 last:border-0 flex justify-between items-center transition-colors">
                                <div>
                                    <h4 class="font-bold text-base mb-0.5" :class="selectedIndex === {{ $index }} ? 'text-white' : 'text-gray-800 dark:text-gray-200'">{{ $product->nama }}</h4>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded transition-colors"
                                        :class="selectedIndex === {{ $index }} ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">
                                        {{ $product->kode_produk }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="block font-bold text-lg" :class="selectedIndex === {{ $index }} ? 'text-white' : 'text-blue-600 dark:text-blue-400'">
                                        Rp {{ number_format((float) $product->harga_jual) }}
                                    </span>
                                    <span class="text-xs {{ $product->stok <= 0 ? 'text-red-500 font-bold' : ($product->stok <= 5 ? 'text-amber-500 font-bold' : '') }}"
                                        :class="selectedIndex === {{ $index }} ? 'text-white' : '{{ $product->stok <= 0 ? 'text-red-400' : ($product->stok <= 5 ? 'text-amber-400' : 'text-gray-400 dark:text-gray-500') }}'">
                                        Stok: {{ $product->stok }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="p-8 text-center bg-gray-50 dark:bg-gray-800/50">
                                <div class="flex justify-center mb-4">
                                    <div class="p-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                </div>
                                <h4 class="text-xl font-black text-gray-800 dark:text-white mb-2 uppercase tracking-tight">Produk Tidak Terdaftar</h4>
                                <p class="text-gray-500 dark:text-gray-400 mb-6 font-medium italic">Barcode atau nama produk tidak ditemukan dalam database</p>
                                <button wire:click="$set('searchQuery', '')" @click="$nextTick(() => $refs.searchInput.focus())" class="px-6 py-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-sm font-black rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg">
                                    RESET PENCARIAN
                                </button>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <button @click="showPendingModal = !showPendingModal" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 whitespace-nowrap group" title="Daftar Draft (F9)">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">Draft (F9)</span>
                        </button>
                        <button @click="showProductTable = !showProductTable" class="px-5 py-3 bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-600 text-white font-black rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 whitespace-nowrap group">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                            <span class="text-sm">Produk (F2)</span>
                        </button>
                        <button @click="showHistoryModal = !showHistoryModal" class="px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white font-black rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 whitespace-nowrap group" title="Riwayat Penjualan (F8)">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="text-sm">Riwayat (F8)</span>
                        </button>
                        <button @click="$wire.pendingTransaction()" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 text-white font-black rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 whitespace-nowrap group" title="Tunda Transaksi (F4)">
                            <svg class="h-5 w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">Tunda (F4)</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cart Table Area -->
            <div class="flex-1 overflow-x-auto overflow-y-auto bg-white dark:bg-gray-900 relative custom-scrollbar">
                @if (count($cart) > 0)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Kode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Produk</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Jumlah</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Harga</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Subtotal</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($cart as $id => $item)
                        <tr wire:key="cart-item-{{ $id }}" class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors even:bg-gray-50/50 dark:even:bg-gray-800/30">
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                {{ $item['kode_produk'] ?? '-' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-gray-300 font-bold">
                                {{ $item['name'] }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex justify-center items-center">
                                    <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden shadow-sm">
                                        <button wire:click="updateQuantity('{{ $id }}', -1)" class="p-2 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors focus:outline-none">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <input type="text" readonly value="{{ $item['quantity'] }}" class="w-12 text-center text-sm font-bold text-gray-700 dark:text-gray-200 border-none p-0 focus:ring-0 bg-white dark:bg-gray-800 h-full cursor-default">
                                        <button wire:click="updateQuantity('{{ $id }}', 1)" class="p-2 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors focus:outline-none">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-right font-mono">
                                {{ number_format($item['price']) }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-blue-700 dark:text-blue-400 font-bold text-right font-mono bg-blue-50/40 dark:bg-blue-900/20">
                                {{ number_format($item['price'] * $item['quantity']) }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                <button wire:click="removeFromCart('{{ $id }}')" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors p-1.5 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-full group">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <!-- Empty State -->
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 p-10 select-none bg-gray-50/50 dark:bg-gray-900/50 transition-colors duration-300 min-h-full">
                    <svg class="w-24 h-24 mb-4 opacity-30 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-xl font-bold text-gray-500 dark:text-gray-400 mb-2">Keranjang Kosong</h3>
                    <p class="text-sm">Scan barcode atau pilih produk untuk memulai.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    @include('livewire.pos.footer')

    <!-- MODALS SECTION -->

    <!-- Product Modal (F2) -->
    <div x-cloak x-show="showProductTable" class="fixed inset-0 z-[90] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 dark:bg-black/90" @click="showProductTable = false"></div>
            <div class="relative flex flex-col w-full h-[80vh] overflow-hidden text-left transition-all transform bg-white dark:bg-gray-900 shadow-xl rounded-2xl sm:max-w-4xl border dark:border-gray-800"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b dark:border-gray-800">
                    <div class="flex justify-between items-center mb-6 border-b dark:border-gray-800 pb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Produk</h3>
                        </div>
                        <button @click="showProductTable = false" class="text-gray-400 hover:text-gray-500 bg-gray-100 dark:bg-gray-800 dark:hover:text-gray-300 p-2 rounded-full transition hover:bg-gray-200">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div x-data="{ query: @entangle('modalSearchQuery') }"
                        x-init="$nextTick(() => $refs.modalSearch.focus())"
                        @reset-search.window="$nextTick(() => { if($refs.modalSearch) { $refs.modalSearch.value = ''; $refs.modalSearch.focus(); } })"
                        class="relative">
                        <input x-ref="modalSearch" id="modal-search-input"
                            wire:model.live.debounce.300ms="modalSearchQuery" type="text"
                            class="w-full text-lg p-4 pr-12 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-all placeholder-gray-400 dark:placeholder-gray-500 dark:text-gray-100"
                            placeholder="Ketik nama produk atau scan kode sekarang...">
                        <button x-show="query && query.length > 0" @click="$wire.set('modalSearchQuery', ''); $nextTick(() => $refs.modalSearch.focus())" class="absolute right-4 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-red-500 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-800/50 custom-scrollbar">
                    <div class="p-4">
                        <table class="w-full relative border-separate border-spacing-y-2">
                            <thead class="sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider rounded-l-lg shadow-sm text-left">Kode</th>
                                    <th class="px-4 py-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider shadow-sm text-left">Nama Produk</th>
                                    <th class="px-4 py-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider shadow-sm text-right">Harga</th>
                                    <th class="px-4 py-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider shadow-sm text-center">Stok</th>
                                    <th class="px-4 py-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider rounded-r-lg shadow-sm text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->produks as $produk)
                                <tr wire:click="addToCart({{ $produk->id }})" wire:key="modal-product-{{ $produk->id }}" class="group hover:scale-[1.01] transition-transform duration-200 cursor-pointer">
                                    <td class="bg-white dark:bg-gray-800 p-4 rounded-l-lg border-y border-l border-gray-100 dark:border-gray-700 font-mono text-sm text-gray-500 dark:text-gray-400">{{ $produk->kode_produk }}</td>
                                    <td class="bg-white dark:bg-gray-800 p-4 border-y border-gray-100 dark:border-gray-700">
                                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $produk->nama }}</div>
                                    </td>
                                    <td class="bg-white dark:bg-gray-800 p-4 border-y border-gray-100 dark:border-gray-700 text-right font-medium text-blue-600 dark:text-blue-400">Rp {{ number_format($produk->harga_jual) }}</td>
                                    <td class="bg-white dark:bg-gray-800 p-4 border-y border-gray-100 dark:border-gray-700 text-center">
                                        <span class="py-1 px-2 rounded text-xs font-bold {{ $produk->stok <= 0 ? 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800' : ($produk->stok <= 5 ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300') }}">
                                            {{ $produk->stok }}
                                        </span>
                                    </td>
                                    <td class="bg-white dark:bg-gray-800 p-4 rounded-r-lg border-y border-r border-gray-100 dark:border-gray-700 text-center"><button class="bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">Pilih</button></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400 font-bold">Produk Tidak Ditemukan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal (F8) -->
    <div x-cloak x-show="showHistoryModal" class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/80 transition-opacity" @click="showHistoryModal = false"></div>
            <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border dark:border-gray-700">
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <div class="border-b dark:border-gray-700 pb-4 mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Riwayat Transaksi ({{ now()->format('d/m/Y') }})
                            </h3>
                            <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-500 bg-gray-100 dark:bg-gray-700 p-2 rounded-full"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg></button>
                        </div>
                        <!-- Revenue Summary -->
                        <div class="px-4 py-3 bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-emerald-600 dark:bg-emerald-500 flex items-center justify-center">
                                        <span class="text-white font-black text-xs">Rp</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Pendapatan Hari Ini</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Transaksi</div>
                                        <div class="text-lg font-black text-gray-900 dark:text-white">{{ $this->todayTotalTransactions }}</div>
                                    </div>
                                    <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Total</div>
                                        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($this->todayTotalRevenue, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-y-auto rounded-xl border dark:border-gray-700 shadow-sm max-h-[60vh] custom-scrollbar">
                        <table class="min-w-full divide-y dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0 z-20">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">No Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y dark:divide-gray-700">
                                @forelse ($this->recentTransactions as $trx)
                                <tr wire:key="history-row-{{ $trx->id }}" class="hover:bg-blue-50/10 transition-colors">
                                    <td class="px-6 py-4 font-bold text-blue-500 dark:text-blue-400">{{ $trx->nomor }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-200 font-medium">{{ $trx->pelanggan->nama ?? 'Umum' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 rounded-full text-xs font-black border border-blue-100 dark:border-blue-800">
                                            {{ number_format($trx->item_count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-gray-900 dark:text-white">Rp {{ number_format($trx->total) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 rounded-md text-xs font-bold border border-gray-200 dark:border-gray-600/50">
                                            {{ $trx->created_at->format('d/m/y H:i') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button wire:click="reprint({{ $trx->id }})"
                                            class="text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 p-2 rounded-lg transition-all hover:scale-110 active:scale-90 group">
                                            <svg class="w-5 h-5 transition-transform group-hover:rotate-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">Belum ada transaksi selesai hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t dark:border-gray-700 sticky bottom-0 z-20">
                    {{ $this->recentTransactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Draft Modal (F9) -->
    <div x-cloak x-show="showPendingModal" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" @click="showPendingModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 flex justify-between items-center">
                        Daftar Draft Transaksi
                        <button @click="showPendingModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg></button>
                    </h3>
                    <div class="overflow-x-auto rounded-xl border dark:border-gray-700">
                        <table class="min-w-full divide-y dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold uppercase">Total</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                @forelse($this->pendingTransactions as $trx)
                                <tr wire:click="recallTransaction({{ $trx->id }})" class="hover:bg-blue-50/10 cursor-pointer transition-colors border-b dark:border-gray-700/50">
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $trx->created_at->format('H:i') }} <span class="text-xs opacity-60">({{ $trx->created_at->diffForHumans() }})</span></td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800 dark:text-white">{{ $trx->pelanggan ? $trx->pelanggan->nama : 'Umum' }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-black text-gray-900 dark:text-blue-400">Rp {{ number_format((float)$trx->total) }}</td>
                                    <td class="px-6 py-4 text-center text-sm">
                                        <button wire:click.stop="recallTransaction({{ $trx->id }})" class="text-blue-600 dark:text-blue-400 font-bold bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded transition-colors">Pilih</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center opacity-50 italic font-bold">Tidak ada transaksi pending.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 text-right">
                    <button @click="showPendingModal = false" class="bg-white dark:bg-gray-800 border px-6 py-2 rounded-lg font-bold">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Day Modal (F10) -->
    <div x-cloak x-show="showCloseDayModal" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/90 transition-opacity" @click="showCloseDayModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border dark:border-gray-700">
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"><svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg></div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Tutup Hari (Close Shift)</h3>
                            <p class="text-sm text-gray-500 mb-6">Laporan hari ini akan dibuat dan sesi akan ditutup.</p>
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-xl space-y-4 shadow-inner mb-6">
                                <div class="flex justify-between items-center"><span class="opacity-60">Jumlah Transaksi:</span><span class="font-bold border-b-2 border-dashed">{{ number_format($closeDaySummary['count'], 0, ',', '.') }}</span></div>
                                <div class="flex justify-between items-center text-xl"><span class="font-bold">Total Penjualan:</span><span class="font-black text-blue-600">Rp {{ number_format($closeDaySummary['omset'], 0, ',', '.') }}</span></div>
                                <div class="border-t dark:border-gray-600"></div>
                                <div class="flex justify-between"><span class="font-bold opacity-60">Tunai:</span><span class="font-bold">Rp {{ number_format($closeDaySummary['cash'], 0, ',', '.') }}</span></div>
                                <div class="flex justify-between"><span class="font-bold opacity-60">Non-Tunai:</span><span class="font-bold">Rp {{ number_format($closeDaySummary['non_cash'], 0, ',', '.') }}</span></div>
                            </div>
                            <div x-data="{ cash_in_drawer: @entangle('closeDaySummary.cash_in_drawer').live, formatIndo(v) { return v ? parseInt(v.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID') : '0'; }, updateCash(e) { let raw = e.target.value.replace(/[^0-9]/g, ''); this.cash_in_drawer = raw ? parseInt(raw) : 0; } }">
                                <label class="block text-sm font-bold uppercase mb-2">Uang Fisik di Laci</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none font-bold opacity-50">Rp</div><input type="text" :value="formatIndo(cash_in_drawer)" @input="updateCash($event)" class="w-full pl-12 py-3 text-xl font-black border rounded-xl dark:bg-gray-700 dark:border-gray-600" placeholder="0">
                                </div>
                                @php $diff = $closeDaySummary['cash'] - ($closeDaySummary['cash_in_drawer'] ?? 0); @endphp
                                @if(isset($closeDaySummary['cash_in_drawer']))<p class="mt-4 text-sm font-bold bg-white dark:bg-gray-900 p-3 rounded-xl border {{ $diff == 0 ? 'text-green-600 border-green-200' : 'text-red-500 border-red-200' }}">Selisih: Rp {{ number_format(abs($diff), 0, ',', '.') }} {{ $diff > 0 ? '(Kurang)' : ($diff < 0 ? '(Lebih)' : '(Pas)') }}</p>@endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex flex-row-reverse gap-3"><button wire:click="processCloseDay" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg shadow-red-500/20">Tutup Hari & Cetak Laporan</button><button @click="showCloseDayModal = false" class="bg-white dark:bg-gray-800 border px-6 py-2 rounded-lg font-bold">Batal</button></div>
            </div>
        </div>
    </div>

    <!-- Warning Old Transactions Modal (Mandatory - Smaller & Fresh) -->
    <div x-cloak x-show="showWarningOldTransactions" class="fixed inset-0 z-[150] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xl transition-opacity"></div>

            <div class="relative bg-white rounded-[2.5rem] text-left overflow-hidden shadow-[0_20px_70px_-15px_rgba(0,0,0,0.2)] transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-white px-8 py-10">
                    <div class="flex flex-col items-center text-center">
                        <!-- Sleek Minimal Icon -->
                        <div class="flex items-center justify-center h-20 w-20 rounded-3xl bg-amber-50 mb-6 group">
                            <svg class="h-10 w-10 text-amber-500 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <h3 class="text-2xl font-black text-gray-900 mb-4 tracking-tight">Peringatan Tanggal</h3>

                        <div class="space-y-3">
                            <p class="text-lg leading-snug text-gray-700 font-bold px-4">
                                Tanggal Transaksi dengan tanggal Komputer <span class="text-red-500 font-black">tidak sama</span>.
                            </p>
                            <p class="text-sm text-gray-400 font-medium italic">
                                Lakukan tutup hari untuk bisa memulai transaksi awal.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white px-8 pb-10">
                    <button @click="showWarningOldTransactions = false; showCloseDayModal = true"
                        class="w-full bg-black hover:bg-gray-800 active:scale-[0.97] text-white px-8 py-4 rounded-2xl font-black text-base shadow-2xl shadow-black/10 transition-all flex items-center justify-center gap-2">
                        <span>Lakukan Tutup Hari</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                    <p class="mt-4 text-center text-[10px] text-gray-300 font-bold uppercase tracking-widest">Aksi ini wajib dilakukan setiap pergantian hari</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Include -->
    @include('livewire.pos.payment-modal')

    <!-- Shift Selection Modal -->
    <div x-cloak x-show="showShiftModal" class="fixed inset-0 z-[110] overflow-y-auto" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" @click="showShiftModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border dark:border-gray-700">
                <div class="bg-white dark:bg-gray-800 px-6 py-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4">
                        <svg class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 mb-2">Pilih Shift Kerja</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">Tentukan shift aktif untuk pencatatan transaksi Anda.</p>

                    <div class="space-y-3">
                        @foreach (\App\Models\Shift::all() as $shift)
                        <button wire:click="changeShift({{ $shift->id }})"
                            class="w-full p-4 rounded-xl border-2 transition-all flex items-center justify-between group
                            {{ $activeShiftId == $shift->id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700' }}">
                            <div class="text-left">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $shift->nama }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Jam: {{ substr($shift->jam_mulai, 0, 5) }} - {{ substr($shift->jam_selesai, 0, 5) }}</div>
                            </div>
                            @if($activeShiftId == $shift->id)
                            <div class="h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                    <button @click="showShiftModal = false" class="w-full bg-white dark:bg-gray-800 border-2 dark:border-gray-600 py-3 rounded-xl font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global Bluetooth State
        let printCharacteristic = null;
        let bluetoothDevice = null;
        let isConnecting = false;
        window.connectedPrinter = false; // Shared state

        async function handleBluetoothConnection() {
            if (isConnecting) return;

            if (printCharacteristic) {
                // Disconnect
                disconnectPrinter();
                return;
            }

            // Connect
            isConnecting = true;

            try {
                const device = await getPrinter();
                if (device) {
                    bluetoothDevice = device;
                    window.connectedPrinter = true;

                    // Add disconnect listener
                    device.addEventListener('gattserverdisconnected', onDisconnected);

                    // Notify UI components
                    window.dispatchEvent(new CustomEvent('bluetooth-connected'));

                    // Show success toast (using Livewire event to trigger frontend toast if possible, or just console)
                    // We can reuse the success notification logic
                    Livewire.dispatch('transaction-success'); // Reuse success tick for connection

                } else {
                    // Cancelled or Failed
                }
            } catch (error) {
                console.error("Bluetooth Connection Error:", error);
                Livewire.dispatch('pos-error', {
                    message: 'Gagal menghubungkan printer: ' + error.message
                });
                onDisconnected();
            } finally {
                isConnecting = false;
            }
        }

        function disconnectPrinter() {
            if (bluetoothDevice && bluetoothDevice.gatt.connected) {
                bluetoothDevice.gatt.disconnect();
            }
            // State cleanup happens in onDisconnected
        }

        function onDisconnected() {
            printCharacteristic = null;
            bluetoothDevice = null;
            window.connectedPrinter = false;

            // Notify UI components
            window.dispatchEvent(new CustomEvent('bluetooth-disconnected'));

            console.log('Printer disconnected');
        }

        async function getPrinter() {
            try {
                const device = await navigator.bluetooth.requestDevice({
                    filters: [{
                            namePrefix: "RPP"
                        },
                        {
                            namePrefix: "Thermal"
                        },
                        {
                            namePrefix: "POS"
                        },
                        {
                            namePrefix: "PT"
                        },
                        {
                            namePrefix: "MP"
                        },
                        {
                            namePrefix: "Iware"
                        },
                        {
                            namePrefix: "Eppos"
                        },
                        {
                            namePrefix: "Blueprint"
                        },
                        {
                            namePrefix: "Panda"
                        },
                        {
                            namePrefix: "VSC"
                        }
                    ],
                    optionalServices: [
                        '000018f0-0000-1000-8000-00805f9b34fb', // Standard Print Service
                        'e7810a71-73ae-499d-8c15-faa9aeb0c51e', // VSCPrinter
                        "49535343-fe7d-4ae5-8fa9-9fafd205e455", // ISSC
                        "0000ff00-0000-1000-8000-00805f9b34fb" // Generic Serial
                    ],
                    acceptAllDevices: false
                });

                const server = await device.gatt.connect();

                // Add delay for stability
                await new Promise(resolve => setTimeout(resolve, 500));

                const services = [
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    'e7810a71-73ae-499d-8c15-faa9aeb0c51e',
                    "49535343-fe7d-4ae5-8fa9-9fafd205e455",
                    "0000ff00-0000-1000-8000-00805f9b34fb"
                ];

                for (const serviceId of services) {
                    try {
                        const service = await server.getPrimaryService(serviceId);
                        const characteristics = await service.getCharacteristics();
                        for (const c of characteristics) {
                            if (c.properties.write || c.properties.writeWithoutResponse) {
                                printCharacteristic = c;
                                console.log('Printer connected. Service:', serviceId);
                                return device;
                            }
                        }
                    } catch (e) {
                        // Continue searching
                    }
                }

                // Deep search if specific services fail
                try {
                    const allServices = await server.getPrimaryServices();
                    for (const service of allServices) {
                        const characteristics = await service.getCharacteristics();
                        for (const c of characteristics) {
                            if (c.properties.write || c.properties.writeWithoutResponse) {
                                printCharacteristic = c;
                                console.log('Printer connected via Deep Search. Service:', service.uuid);
                                return device;
                            }
                        }
                    }
                } catch (e) {
                    console.warn("Deep search failed", e);
                }


                if (!printCharacteristic) {
                    Livewire.dispatch('pos-error', {
                        message: 'Printer terhubung tapi tidak ditemukan service cetak yang cocok.'
                    });
                    device.gatt.disconnect();
                    return null;
                }

                return device;

            } catch (error) {
                if (error.name === 'NotFoundError') {
                    // User cancelled - silent
                    return null;
                }
                throw error;
            }
        }

        async function printThermal(dataInput) {
            if (!printCharacteristic) {
                Livewire.dispatch('pos-error', {
                    message: 'Printer Bluetooth belum terhubung. Klik tombol BT di header.'
                });
                return;
            }

            try {
                let data;
                if (typeof dataInput === 'string') {
                    const encoder = new TextEncoder();
                    data = encoder.encode(dataInput);
                } else {
                    data = dataInput; // Assume Uint8Array or ArrayBuffer
                }

                // Convert ArrayBuffer to Uint8Array if needed
                if (data instanceof ArrayBuffer) {
                    data = new Uint8Array(data);
                }

                // Chunking for BLE mtu
                const CHUNK_SIZE = 100;
                for (let i = 0; i < data.length; i += CHUNK_SIZE) {
                    const chunk = data.slice(i, i + CHUNK_SIZE);
                    await printCharacteristic.writeValue(chunk);
                    await new Promise(r => setTimeout(r, 20)); // Buffer delay
                }

                // If it was a string, append feed manually. If binary, assume backend handled it.
                if (typeof dataInput === 'string') {
                    const feed = new Uint8Array([0x0A, 0x0A]);
                    await printCharacteristic.writeValue(feed);
                }

            } catch (error) {
                console.error("Print Error:", error);
                Livewire.dispatch('pos-error', {
                    message: 'Gagal mencetak: ' + error.message
                });

                if (error.toString().includes('GATT') || error.toString().includes('disconnected')) {
                    onDisconnected();
                }
            }
        }

        // Hook into Livewire events
        document.addEventListener('livewire:initialized', () => {
            // Event from Backend (Base64 Binary)
            Livewire.on('print-bluetooth', async (data) => {
                console.log("Received print-bluetooth event", data);
                const payload = Array.isArray(data) ? data[0] : data;

                if (payload && payload.data) {
                    try {
                        // Decode Base64 to Uint8Array
                        const binaryString = atob(payload.data);
                        const bytes = new Uint8Array(binaryString.length);
                        for (let i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }

                        await printThermal(bytes);

                    } catch (e) {
                        console.error("Failed to decode receipt data", e);
                        Livewire.dispatch('pos-error', {
                            message: "Gagal memproses data struk."
                        });
                    }
                }
            });

            // Legacy/Frontend text event
            Livewire.on('print-receipt-bluetooth', async (data) => {
                const content = Array.isArray(data) ? data[0] : data;
                if (content && content.text) {
                    await printThermal(content.text);
                }
            });
        });
    </script>

    <!-- Store Closed Overlay -->
    @if($setting && $setting->is_toko_tutup)
    <div class="fixed inset-0 z-[200] flex items-center justify-center backdrop-blur-md bg-gray-900/60 transition-all px-4">
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl max-w-lg w-full p-10 text-center border-t-8 border-red-600 animate-in fade-in zoom-in duration-300">
            <div class="w-24 h-24 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-8">
                <x-heroicon-o-no-symbol class="w-12 h-12 text-red-600" />
            </div>

            <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-4 tracking-tight uppercase">
                Toko Sedang Tutup
            </h1>

            <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 italic text-lg text-gray-600 dark:text-gray-300 mb-8 font-medium">
                "{{ $setting->pesan_tutup ?? 'Mohon maaf, saat ini kami sedang libur. Silakan kembali lagi nanti.' }}"
            </div>

            <div class="space-y-4">
                <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">Akses Transaksi Ditangguhkan</p>
                <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-bold transition-colors">
                    <x-heroicon-m-arrow-left class="w-5 h-5" />
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
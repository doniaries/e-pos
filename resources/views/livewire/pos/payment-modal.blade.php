@if ($showPaymentModal)
<div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-90" @click="$wire.closePaymentModal()"></div>

    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all w-full max-w-6xl border border-gray-100 dark:border-gray-700">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 border-b dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Pembayaran
                </h3>
                <button @click="$wire.closePaymentModal()" class="text-white hover:text-gray-200 bg-white/20 hover:bg-white/30 p-2 rounded-full transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content (No Scroll) -->
        <div class="px-8 py-4">
            <!-- Total Belanja -->
            <div class="mb-3">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">Jumlah Belanja</label>
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 rounded-xl p-4 border-2 border-gray-200 dark:border-gray-700">
                    <div class="text-4xl font-black text-gray-900 dark:text-white text-center tracking-tight">
                        <span class="text-xl opacity-60 mr-1">Rp</span>{{ number_format($grandTotal) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-3">
                <!-- Customer Type & Selection -->
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">Tipe Konsumen</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('customerType', 'umum')"
                            class="px-5 py-3 text-base rounded-lg border-2 transition-all font-bold {{ $customerType === 'umum' ? 'bg-blue-50 border-blue-500 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-gray-300' }}">
                            👤 Umum
                        </button>
                        <button wire:click="$set('customerType', 'pelanggan')"
                            class="px-5 py-3 text-base rounded-lg border-2 transition-all font-bold {{ $customerType === 'pelanggan' ? 'bg-blue-50 border-blue-500 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-gray-300' }}">
                            ⭐ Member
                        </button>
                    </div>
                    @if ($customerType === 'pelanggan')
                    <div class="mt-2">
                        <select wire:model.live="customer" class="w-full text-sm bg-white dark:bg-gray-700 border-2 border-blue-500 dark:border-gray-600 rounded-lg p-3 focus:ring-blue-500 dark:text-gray-200 font-medium">
                            <option value="">-- Pilih Member --</option>
                            @foreach ($this->pelangganList as $pelanggan)
                            <option value="{{ $pelanggan->id }}" wire:key="pelanggan-{{ $pelanggan->id }}">{{ $pelanggan->kode_member }} - {{ $pelanggan->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Payment Method -->
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">Metode Pembayaran</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['tunai' => '💵 Tunai', 'transfer' => '🏦 Transfer', 'qris' => '📱 QRIS', 'kartu_debit' => '💳 Debit'] as $val => $label)
                        <button wire:click="$set('paymentMethod', '{{ $val }}')"
                            class="px-4 py-3 text-base rounded-lg border-2 transition-all font-bold {{ $paymentMethod === $val ? 'bg-blue-50 border-blue-500 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-gray-300' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Payment Input & Change Display -->
            <div x-data="{
                payment: @entangle('payment').live,
                total: @entangle('grandTotal'),
                get change() {
                    return this.payment - this.total;
                },
                formatNumber(v) { 
                    return v ? parseInt(v.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID') : '0'; 
                },
                updatePayment(e) { 
                    let raw = e.target.value.replace(/[^0-9]/g, '');
                    this.payment = raw ? parseInt(raw) : 0;
                }
            }">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Uang Input -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">UANG INPUT</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-lg">Rp</span>
                            <input type="text"
                                x-ref="paymentInput"
                                x-init="$nextTick(() => $el.focus())"
                                :value="formatNumber(payment)"
                                @input="updatePayment($event)"
                                class="w-full pl-12 pr-3 py-3 bg-white dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-3xl font-black text-gray-800 dark:text-gray-100 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right"
                                placeholder="0">
                        </div>
                        <!-- Quick Cash Buttons -->
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button type="button" @click="payment = 50000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">50rb</button>
                            <button type="button" @click="payment = 100000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">100rb</button>
                            <button type="button" @click="payment = 150000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">150rb</button>
                            <button type="button" @click="payment = 200000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">200rb</button>
                        </div>
                    </div>

                    <!-- Kembalian -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">Kembalian</label>
                        <div class="p-5 rounded-xl border-2 transition-all"
                            :class="change < 0 ? 'bg-red-50 text-red-700 border-red-300 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700' : 'bg-green-50 text-green-800 border-green-300 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700'">
                            <div class="text-xs font-bold opacity-70 mb-1.5" x-text="change < 0 ? '⚠️ KURANG BAYAR' : '✅ KEMBALI'"></div>
                            <div class="text-3xl font-black tracking-tight" x-text="'Rp ' + formatNumber(Math.abs(change))"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex gap-3">
                <button wire:click="closePaymentModal" type="button" class="w-36 inline-flex justify-center items-center gap-2 rounded-xl border-2 border-gray-300 dark:border-gray-600 shadow-sm px-5 py-4 bg-white dark:bg-gray-800 text-base font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal
                </button>
                <button @click="if(!isProcessing) { isProcessing = true; $wire.processSale(); }"
                    wire:loading.attr="disabled"
                    {{ !$this->canProcessSale() ? 'disabled' : '' }}
                    :disabled="isProcessing"
                    type="button"
                    class="flex-1 inline-flex justify-center items-center gap-3 rounded-xl border-2 border-transparent shadow-lg px-6 py-4 bg-green-600 hover:bg-green-700 text-xl font-black text-white focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-show="!isProcessing">SELESAI</span>
                    <span x-show="isProcessing" class="animate-pulse">LOADING...</span>
                </button>
                <button @click="if(!isProcessing) { isProcessing = true; $wire.processAndPrint(); }"
                    wire:loading.attr="disabled"
                    {{ !$this->canProcessSale() ? 'disabled' : '' }}
                    :disabled="isProcessing"
                    type="button"
                    class="flex-1 inline-flex justify-center items-center gap-3 rounded-xl border-2 border-transparent shadow-lg px-6 py-4 bg-blue-600 hover:bg-blue-700 text-xl font-black text-white focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span x-show="!isProcessing">SELESAI & CETAK</span>
                    <span x-show="isProcessing" class="animate-pulse">PRINTING...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
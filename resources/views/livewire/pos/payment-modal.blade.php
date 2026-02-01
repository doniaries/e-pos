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

                        {{-- Display Member Debt Info --}}
                        @if ($customer)
                        @php
                        $selectedMember = $this->pelangganList->firstWhere('id', $customer);
                        $currentDebt = $selectedMember ? (float)$selectedMember->hutang : 0;
                        $newDebt = max(0, $grandTotal - ($payment ?? 0));
                        $totalDebtAfter = $currentDebt + $newDebt;
                        @endphp

                        {{-- Current Debt Warning --}}
                        @if ($currentDebt > 0)
                        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-400 dark:border-yellow-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="text-sm font-bold text-yellow-800 dark:text-yellow-300">Hutang Saat Ini:</span>
                                </div>
                                <span class="text-lg font-black text-yellow-900 dark:text-yellow-200">
                                    Rp {{ number_format($currentDebt) }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- New Debt Alert (if payment < total) --}}
                        @if ($newDebt > 0)
                        <div class="mt-3 p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-400 dark:border-red-700 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-bold uppercase">Pembayaran Tidak Lunas</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-white dark:bg-gray-800 p-2 rounded">
                                        <div class="text-gray-500 dark:text-gray-400">Hutang Lama</div>
                                        <div class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($currentDebt) }}</div>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 p-2 rounded">
                                        <div class="text-gray-500 dark:text-gray-400">Hutang Baru</div>
                                        <div class="font-bold text-red-600 dark:text-red-400">Rp {{ number_format($newDebt) }}</div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t-2 border-red-300 dark:border-red-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-red-700 dark:text-red-300">TOTAL HUTANG SETELAH TRANSAKSI:</span>
                                        <span class="text-2xl font-black text-red-800 dark:text-red-200">Rp {{ number_format($totalDebtAfter) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif ($payment >= $grandTotal && $currentDebt > 0)
                        {{-- Payment is enough for this transaction, but member still has old debt --}}
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-400 dark:border-blue-700 rounded-lg">
                            <div class="flex items-center gap-2 text-blue-700 dark:text-blue-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium">Transaksi ini lunas, tapi member masih punya hutang lama: <span class="font-black">Rp {{ number_format($currentDebt) }}</span></span>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Member Payment Mode Selection --}}
                @if ($customerType === 'pelanggan' && $customer)
                <div class="col-span-2 mb-4" x-data="{ paymentMode: @entangle('payment').live > 0 ? 'bayar' : 'hutang' }">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Mode Pembayaran Member</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button"
                            @click="paymentMode = 'hutang'; $wire.set('payment', 0)"
                            :class="paymentMode === 'hutang' ? 'bg-red-500 text-white border-red-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                            class="px-4 py-4 rounded-xl border-2 transition-all font-bold flex items-center justify-center gap-2 hover:scale-105">
                            <span>💳 Hutang Penuh</span>
                        </button>
                        <button type="button"
                            @click="paymentMode = 'bayar'; if($wire.payment === 0) $wire.set('payment', $wire.grandTotal)"
                            :class="paymentMode === 'bayar' ? 'bg-green-500 text-white border-green-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'"
                            class="px-4 py-4 rounded-xl border-2 transition-all font-bold flex items-center justify-center gap-2 hover:scale-105">
                            <span>💰 Bayar Sebagian/Lunas</span>
                        </button>
                    </div>

                    <div x-show="paymentMode === 'hutang'" x-transition class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border-2 border-red-400 dark:border-red-700 rounded-lg">
                        <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm font-bold">Transaksi ini akan dicatat sebagai HUTANG PENUH (Rp {{ number_format($grandTotal) }})</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment Method -->
                <div class="col-span-2" @if($customerType==='pelanggan' && $customer) x-show="paymentMode === 'bayar'" x-transition @endif>
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
            }" @if($customerType==='pelanggan' && $customer) x-show="paymentMode === 'bayar'" x-transition @endif>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Uang Input -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">UANG INPUT</label>

                        {{-- Member Hint --}}
                        @if ($customerType === 'pelanggan' && $customer)
                        <div class="mb-2 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-700 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-blue-700 dark:text-blue-300">
                                    <span class="font-bold">Member bisa hutang:</span>
                                    <ul class="mt-1 space-y-0.5 ml-2">
                                        <li>• Kosongkan (Rp 0) = <span class="font-semibold">Hutang Penuh</span></li>
                                        <li>• Bayar sebagian = <span class="font-semibold">Hutang Sisanya</span></li>
                                        <li>• Bayar lunas = <span class="font-semibold">Tidak ada hutang</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
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
                            @if ($customerType === 'pelanggan' && $customer)
                            <button type="button" @click="payment = 0" class="px-3 py-4 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-xl text-sm font-black transition-all shadow-sm flex items-center justify-center border-2 border-red-300 dark:border-red-700">💳 Hutang Penuh</button>
                            <button type="button" @click="payment = 50000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">50rb</button>
                            <button type="button" @click="payment = 100000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">100rb</button>
                            <button type="button" @click="payment = 150000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">150rb</button>
                            @else
                            <button type="button" @click="payment = 50000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">50rb</button>
                            <button type="button" @click="payment = 100000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">100rb</button>
                            <button type="button" @click="payment = 150000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">150rb</button>
                            <button type="button" @click="payment = 200000" class="px-3 py-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-lg font-black transition-all shadow-sm flex items-center justify-center">200rb</button>
                            @endif
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
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div @class([ 'flex items-center justify-center w-12 h-12 rounded-full' , 'bg-success-100 text-success-600 dark:bg-success-500/10'=> !$isTutup,
                    'bg-danger-100 text-danger-600 dark:bg-danger-500/10' => $isTutup,
                    ])>
                    @if(!$isTutup)
                    <x-heroicon-o-building-storefront class="w-6 h-6" />
                    @else
                    <x-heroicon-o-no-symbol class="w-6 h-6" />
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-bold tracking-tight">Status Toko:
                        <span @class([ 'px-2 py-0.5 rounded-full text-sm font-medium' , 'bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-400'=> !$isTutup,
                            'bg-danger-100 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400' => $isTutup,
                            ])>
                            {{ $isTutup ? 'SEDANG TUTUP / LIBUR' : 'SEDANG BUKA' }}
                        </span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $isTutup ? 'Pelanggan tidak dapat melakukan transaksi di POS.' : 'Sistem POS siap menerima transaksi.' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                @if($isTutup)
                <div class="flex-1 min-w-[200px] w-full">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model.defer="pesanTutup"
                            placeholder="Pesan libur..."
                            class="text-sm" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::button wire:click="updatePesan" color="gray" size="sm" icon="heroicon-m-check">
                    Simpan Pesan
                </x-filament::button>
                @endif

                <x-filament::button
                    wire:click="toggleStatus"
                    :color="$isTutup ? 'success' : 'danger'"
                    :icon="$isTutup ? 'heroicon-m-play' : 'heroicon-m-pause'">
                    {{ $isTutup ? 'Buka Toko Sekarang' : 'Tutup Toko (Libur)' }}
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
<?php

namespace App\Filament\Resources\Penjualans\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class PenjualanSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        // Header Section - Span 12 columns
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('kasir')
                                ->label('Kasir')
                                ->default(Auth::user()?->name)
                                ->disabled(),
                            Forms\Components\TextInput::make('nomor')
                                ->label('Nomor')
                                ->default(fn() => Penjualan::generateNomor())
                                ->disabled(),
                            Forms\Components\DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default(now())
                                ->disabled()
                                ->displayFormat('d F Y H:i')
                                ->locale('id')
                                ->native(false),
                        ])
                            ->columns(3)
                            ->columnSpan(['lg' => 12]),

                        // Cart Section - Span 8 columns
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Detail Penjualan')
                                ->schema([
                                    Forms\Components\TextInput::make('scan_barcode')
                                        ->label('Scan Barcode / Kode Produk')
                                        ->placeholder('Scan atau ketik kode barang')
                                        ->autofocus()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            if (!$state) return;

                                            $produk = Produk::where('kode_produk', $state)
                                                ->where('stok', '>', 0)
                                                ->first();

                                            if (!$produk) {
                                                Notification::make()
                                                    ->title('Produk tidak ditemukan atau stok habis')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            $details = collect($get('details') ?? [])->toArray();
                                            $existing = collect($details)->firstWhere('kode_produk', $produk->kode_produk);

                                            if ($existing) {
                                                if ($existing['jumlah'] >= $produk->stok) {
                                                    Notification::make()
                                                        ->title('Stok tidak mencukupi')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                $details = collect($details)->map(function ($item) use ($produk) {
                                                    if ($item['kode_produk'] === $produk->kode_produk) {
                                                        $item['jumlah']++;
                                                        $item['subtotal'] = $item['jumlah'] * $item['harga'];
                                                    }
                                                    return $item;
                                                })->toArray();
                                            } else {
                                                $details[] = [
                                                    'kode_produk' => $produk->kode_produk,
                                                    'nama_produk' => $produk->nama,
                                                    'harga' => $produk->harga_jual,
                                                    'jumlah' => 1,
                                                    'subtotal' => $produk->harga_jual,
                                                    'stok' => $produk->stok,
                                                ];
                                            }

                                            $set('details', $details);
                                            $set('scan_barcode', null);
                                            static::recalculateTotal($set, $get);
                                        }),

                                    Forms\Components\Repeater::make('details')
                                        ->schema([
                                            Forms\Components\Grid::make()
                                                ->schema([
                                                    Forms\Components\TextInput::make('kode_produk')
                                                        ->label('Kode')
                                                        ->disabled(),
                                                    Forms\Components\TextInput::make('nama_produk')
                                                        ->label('Nama')
                                                        ->disabled(),
                                                    Forms\Components\TextInput::make('jumlah')
                                                        ->label('Qty')
                                                        ->numeric()
                                                        ->live()
                                                        ->rules([
                                                            fn($get) => function ($value) use ($get) {
                                                                $stok = $get('stok');
                                                                if ($value > $stok) {
                                                                    return "Stok tersedia: {$stok}";
                                                                }
                                                                return null;
                                                            }
                                                        ])
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            $harga = $get('harga');
                                                            $set('subtotal', $state * $harga);
                                                            static::recalculateTotal($set, $get);
                                                        }),
                                                    Forms\Components\TextInput::make('harga')
                                                        ->label('Harga')
                                                        ->disabled()
                                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                        ->prefix('Rp'),
                                                    Forms\Components\TextInput::make('subtotal')
                                                        ->label('Subtotal')
                                                        ->disabled()
                                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                        ->prefix('Rp'),
                                                    Forms\Components\Hidden::make('stok'),
                                                ])
                                                ->columns(5)
                                        ])
                                        ->deleteAction(
                                            fn(Forms\Set $set, Forms\Get $get) => static::recalculateTotal($set, $get)
                                        )
                                        ->reorderable(false)
                                        ->columnSpan('full'),
                                ])
                                ->collapsible()
                        ])->columnSpan(['lg' => 8]),

                        // Sidebar Section - Span 4 columns
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Pembayaran')
                                ->schema([
                                    Forms\Components\Placeholder::make('grand_total_display')
                                        ->label('TOTAL BELANJA')
                                        ->content(function (Forms\Get $get) {
                                            $total = $get('total') ?? 0;
                                            return new \Illuminate\Support\HtmlString('<div style="font-size: 2rem; font-weight: bold; color: #16a34a; text-align: right; padding: 10px; background-color: #f0fdf4; border-radius: 8px;">Rp ' . number_format($total, 0, ',', '.') . '</div>');
                                        })
                                        ->columnSpanFull(),

                                    Forms\Components\Select::make('tipe_pelanggan')
                                        ->label('Tipe Pelanggan')
                                        ->options([
                                            'umum' => 'Umum',
                                            'member' => 'Member',
                                        ])
                                        ->default('umum')
                                        ->live()
                                        ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => static::recalculateTotal($set, $get)),

                                    Forms\Components\Select::make('pelanggan_id')
                                        ->label('Pelanggan Tetap')
                                        ->options(Pelanggan::pluck('nama', 'id'))
                                        ->searchable()
                                        ->visible(fn(Forms\Get $get) => $get('tipe_pelanggan') === 'member'),

                                    Forms\Components\Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'tunai' => 'Tunai',
                                            'transfer' => 'Transfer',
                                        ])
                                        ->default('tunai')
                                        ->live(),

                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('nama_bank')
                                            ->label('Nama Bank')
                                            ->placeholder('BCA')
                                            ->visible(fn(Forms\Get $get) => $get('metode_pembayaran') === 'transfer'),
                                        Forms\Components\TextInput::make('nomor_rekening')
                                            ->label('No. Ref / Kartu')
                                            ->placeholder('1234xxxx')
                                            ->visible(fn(Forms\Get $get) => $get('metode_pembayaran') !== 'tunai'),
                                    ])->columns(2),

                                    Forms\Components\TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->disabled()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('Rp'),

                                    Forms\Components\TextInput::make('diskon_persen')
                                        ->label('Diskon (%)')
                                        ->numeric()
                                        ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $subtotal = $get('subtotal') ?? 0;
                                            $set('diskon_nilai', round($subtotal * ($state / 100)));
                                            static::recalculateTotal($set, $get);
                                        }),

                                    Forms\Components\TextInput::make('diskon_nilai')
                                        ->label('Diskon (Rp)')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->live()
                                        ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => static::recalculateTotal($set, $get)),

                                    Forms\Components\TextInput::make('pajak_persen')
                                        ->label('PPN (%)')
                                        ->numeric()
                                        ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $subtotal = $get('subtotal') ?? 0;
                                            $diskon = $get('diskon_nilai') ?? 0;
                                            $total_setelah_diskon = $subtotal - $diskon;
                                            $set('pajak_nilai', round($total_setelah_diskon * ($state / 100)));
                                            static::recalculateTotal($set, $get);
                                        }),

                                    Forms\Components\TextInput::make('total')
                                        ->label('Total')
                                        ->disabled()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('Rp'),

                                    Forms\Components\TextInput::make('bayar')
                                        ->label('Bayar')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('Rp')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $total = $get('total') ?? 0;
                                            $set('kembali', $state - $total);
                                        }),

                                    Forms\Components\TextInput::make('kembali')
                                        ->label('Kembali')
                                        ->disabled()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('Rp'),

                                    Forms\Components\Textarea::make('catatan')
                                        ->label('Catatan')
                                        ->rows(2),

                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('clear')
                                            ->label('Batal')
                                            ->color('danger')
                                            ->icon('heroicon-m-trash')
                                            ->action(function (Forms\Set $set) {
                                                $set('details', []);
                                                $set('subtotal', 0);
                                                $set('diskon_persen', 0);
                                                $set('diskon_nilai', 0);
                                                $set('pajak_persen', 0);
                                                $set('pajak_nilai', 0);
                                                $set('total', 0);
                                                $set('bayar', null);
                                                $set('kembali', 0);
                                                $set('catatan', null);
                                            }),

                                        Forms\Components\Actions\Action::make('process')
                                            ->label('Bayar')
                                            ->color('success')
                                            ->icon('heroicon-m-check')
                                            ->action(function (array $data, Forms\Set $set) {
                                                DB::beginTransaction();
                                                try {
                                                    // Validasi
                                                    if (empty($data['details'])) {
                                                        Notification::make()
                                                            ->title('Keranjang masih kosong')
                                                            ->danger()
                                                            ->send();
                                                        return;
                                                    }

                                                    // Cek jika status bukan pending, pembayaran harus lunas/lebih
                                                    // User minta pending transaksi, jadi bayar bisa < total, tapi status jadi pending/belum_lunas

                                                    $isPaid = ($data['bayar'] ?? 0) >= ($data['total'] ?? 0);
                                                    $statusPembayaran = $isPaid ? 'lunas' : 'belum_lunas';
                                                    $status = $isPaid ? 'selesai' : 'pending';

                                                    // Buat penjualan
                                                    $penjualan = Penjualan::create([
                                                        'nomor' => $data['nomor'],
                                                        'user_id' => Auth::id(),
                                                        'pelanggan_id' => $data['pelanggan_id'] ?? null,
                                                        'subtotal' => $data['subtotal'],
                                                        'diskon_persen' => $data['diskon_persen'],
                                                        'diskon_nilai' => $data['diskon_nilai'],
                                                        'pajak_persen' => $data['pajak_persen'],
                                                        'pajak_nilai' => $data['pajak_nilai'],
                                                        'total' => $data['total'],
                                                        'metode_pembayaran' => $data['metode_pembayaran'],
                                                        'nama_bank' => $data['nama_bank'] ?? null,
                                                        'nomor_rekening' => $data['nomor_rekening'] ?? null,
                                                        'bayar' => $data['bayar'] ?? 0,
                                                        'kembali' => $data['kembali'] ?? 0,
                                                        'status_pembayaran' => $statusPembayaran,
                                                        'status' => $status,
                                                        'catatan' => $data['catatan'],
                                                    ]);

                                                    // Simpan detail dan update stok
                                                    foreach ($data['details'] as $detail) {
                                                        $produk = Produk::where('kode_produk', $detail['kode_produk'])->first();

                                                        if ($produk->stok < $detail['jumlah']) {
                                                            throw new \Exception("Stok {$produk->nama} tidak mencukupi");
                                                        }

                                                        $penjualan->details()->create([
                                                            'produk_id' => $produk->id,
                                                            'jumlah' => $detail['jumlah'],
                                                            'satuan_id' => $produk->satuan_id,
                                                            'harga' => $detail['harga'],
                                                            'subtotal' => $detail['subtotal'],
                                                        ]);

                                                        // Catat di Kartu Stok
                                                        // Observer akan menghandle update stok Produk & perhitungan stok_awal/akhir
                                                        \App\Models\Stok::create([
                                                            'produk_id' => $produk->id,
                                                            'jenis' => 'penjualan',
                                                            'jumlah' => -$detail['jumlah'], // Keluar = negatif
                                                            'referensi_type' => Penjualan::class,
                                                            'referensi_id' => $penjualan->id,
                                                            'keterangan' => 'Penjualan #' . $penjualan->nomor,
                                                            'user_id' => Auth::id(),
                                                        ]);
                                                    }

                                                    // Pembayarans table removed.

                                                    DB::commit();

                                                    Notification::make()
                                                        ->title('Transaksi berhasil' . ($status == 'pending' ? ' (Pending)' : ''))
                                                        ->success()
                                                        ->send();

                                                    // Reset form
                                                    $set('details', []);
                                                    $set('subtotal', 0);
                                                    $set('diskon_persen', 0);
                                                    $set('diskon_nilai', 0);
                                                    $set('pajak_persen', 0);
                                                    $set('pajak_nilai', 0);
                                                    $set('total', 0);
                                                    $set('bayar', null);
                                                    $set('kembali', 0);
                                                    $set('catatan', null);
                                                    $set('nomor', Penjualan::generateNomor());
                                                } catch (\Exception $e) {
                                                    DB::rollBack();
                                                    Notification::make()
                                                        ->title('Terjadi kesalahan')
                                                        ->body($e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                            ->visible(
                                                fn(Forms\Get $get) => !empty($get('details'))
                                            ),
                                    ])->fullWidth(),
                                ])
                                ->collapsible(),
                        ])->columnSpan(['lg' => 4]),
                    ])
                    ->columns(12)
            ]);
    }
}

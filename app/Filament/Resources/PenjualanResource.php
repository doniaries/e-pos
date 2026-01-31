<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
// use App\Http\Controllers\PenjualanController;
use App\Models\Produk;
use App\Models\Pelanggan;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $pluralLabel = 'Penjualan';
    protected static ?string $modelLabel = 'Penjualan';
    protected static ?string $slug = 'penjualan';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }



    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Penjualan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nomor')
                                    ->label('No Invoice'),
                                TextEntry::make('created_at')
                                    ->label('Tanggal')
                                    ->badge()
                                    ->dateTime('d F Y H:i'),
                                TextEntry::make('kasir.name')
                                    ->label('Kasir'),
                                TextEntry::make('shift.nama')
                                    ->label('Shift')
                                    ->badge()
                                    ->color('primary')
                                    ->default('-'),
                                TextEntry::make('pelanggan.nama')
                                    ->label('Pelanggan')
                                    ->default('Umum')
                                    ->weight('bold'),
                                TextEntry::make('pelanggan')
                                    ->label('Tipe')
                                    ->badge()
                                    ->formatStateUsing(function ($record) {
                                        if (!$record->pelanggan) return 'Umum';
                                        return $record->pelanggan->kode_member
                                            ? 'Pelanggan Tetap (' . $record->pelanggan->kode_member . ')'
                                            : 'Pelanggan Tetap';
                                    })
                                    ->color(fn($record) => $record->pelanggan ? 'success' : 'gray'),

                                TextEntry::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                                TextEntry::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'hutang' => 'danger',
                                        'bayar_sebagian' => 'warning',
                                        'lunas' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('debt_amount')
                                    ->label('Hutang Ditambahkan')
                                    ->money('IDR')
                                    ->badge()
                                    ->color('danger')
                                    ->formatStateUsing(fn($record) => number_format($record->total - $record->bayar, 0, ',', '.'))
                                    ->visible(fn($record) => $record->status_pembayaran !== 'lunas')
                                    ->helperText(fn($record) => 'Total: Rp ' . number_format($record->total, 0, ',', '.') . ' | Bayar: Rp ' . number_format($record->bayar, 0, ',', '.')),
                            ]),
                    ]),

                Section::make('Detail Item')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('produk.nama')
                                            ->label('Produk'),
                                        TextEntry::make('jumlah')
                                            ->label('Qty'),
                                        TextEntry::make('harga')
                                            ->label('Harga')
                                            ->money('IDR'),
                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('IDR'),
                                    ]),
                            ])
                            ->grid(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->money('IDR'),
                                TextEntry::make('diskon_nilai')
                                    ->label('Diskon')
                                    ->money('IDR'),
                                TextEntry::make('pajak_nilai')
                                    ->label('Pajak')
                                    ->money('IDR'),
                                TextEntry::make('total')
                                    ->money('IDR')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('bayar')
                                    ->money('IDR'),
                                TextEntry::make('kembali')
                                    ->money('IDR'),
                            ]),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                                            'qris' => 'QRIS',
                                            'kartu_debit' => 'Kartu Debit',
                                        ])
                                        ->default('tunai')
                                        ->live(),

                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('nama_bank')
                                            ->label('Nama Bank')
                                            ->placeholder('BCA')
                                            ->visible(fn(Forms\Get $get) => in_array($get('metode_pembayaran'), ['transfer', 'kartu_debit'])),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kasir.name')
                    ->label('Kasir')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->default('Umum')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pelanggan')
                    ->label('Pelanggan Tetap')
                    ->badge()
                    ->formatStateUsing(fn($record) => $record->pelanggan ? 'Pelanggan Tetap' : 'Umum')
                    ->color(fn($record) => $record->pelanggan ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('bayar')
                    ->label('Bayar')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('kembali')
                    ->label('Kembali')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hutang' => 'danger',
                        'bayar_sebagian' => 'warning',
                        'lunas' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '>=', \Carbon\Carbon::parse($date)->startOfDay()),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '<=', \Carbon\Carbon::parse($date)->endOfDay()),
                            );
                    }),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'nama')
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\Select::make('shift_id')
                            ->label('Filter Shift')
                            ->relationship('shift', 'nama')
                            ->placeholder('Semua Shift'),
                    ])
                    ->action(function ($data) {
                        try {
                            $query = Penjualan::with('kasir', 'shift')
                                ->whereBetween('created_at', [$data['dari'] . ' 00:00', $data['sampai'] . ' 23:59']);

                            if (!empty($data['shift_id'])) {
                                $query->where('shift_id', $data['shift_id']);
                            }

                            $records = $query->get();
                            $start = $data['dari'];
                            $end = $data['sampai'];

                            $setting = \App\Models\Setting::first();
                            $storeName = $setting->nama_perusahaan ?? 'POS System';
                            $storeAddress = $setting->alamat ?? 'Alamat Toko Belum Diatur';
                            $userName = auth()->user()?->name ?? 'System';

                            $logoPath = public_path('images/logo e-pos.png');
                            if ($setting && $setting->logo && file_exists(storage_path('app/public/' . $setting->logo))) {
                                $logoPath = storage_path('app/public/' . $setting->logo);
                            }

                            $pdf = Pdf::loadView('pdf.laporan-penjualan', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath'))
                                ->setPaper('a4', 'landscape');
                            return response()->streamDownload(fn() => print($pdf->output()), 'laporan-penjualan.pdf');
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Cetak PDF')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Struk')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Penjualan $record): string => route('pos.print-struk', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            // 'view' => Pages\ViewPenjualan::route('/{record}'),
        ];
    }

    protected static function recalculateTotal(Forms\Set $set, Forms\Get $get): void
    {
        $details = collect($get('details') ?? [])->toArray();
        $subtotal = collect($details)->sum('subtotal');

        $set('subtotal', $subtotal);

        $diskon_persen = $get('diskon_persen') ?? 0;
        $diskon_nilai = $get('diskon_nilai') ?? 0;

        if ($diskon_persen > 0) {
            $diskon_nilai = round($subtotal * ($diskon_persen / 100));
            $set('diskon_nilai', $diskon_nilai);
        }

        $total_setelah_diskon = $subtotal - $diskon_nilai;

        $pajak_persen = $get('pajak_persen') ?? 0;
        $pajak_nilai = round($total_setelah_diskon * ($pajak_persen / 100));
        $set('pajak_nilai', $pajak_nilai);

        $total = $total_setelah_diskon + $pajak_nilai;
        $set('total', $total);

        $bayar = $get('bayar') ?? 0;
        $set('kembali', $bayar - $total);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'nomor',
                'user_id',
                'pelanggan_id',
                'subtotal',
                'diskon_nilai',
                'pajak_nilai',
                'total',
                'bayar',
                'kembali',
                'status_pembayaran',
                'created_at'
            ])
            ->with([
                'kasir:id,name',
                'pelanggan:id,nama',
                'details:id,penjualan_id,produk_id,jumlah,subtotal'
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor', 'kasir.name', 'pelanggan.nama'];
    }
}

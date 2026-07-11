<?php

namespace App\Filament\Resources\Produks\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class ProdukSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Tabs::make('Produk Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Detail Produk')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Section::make('Informasi Utama')
                                            ->description('Detail identitas produk')
                                            ->schema([
                                                Forms\Components\TextInput::make('kode_produk')
                                                    ->label('Barcode / Kode Produk')
                                                    ->placeholder('Scan barcode atau ketik manual...')
                                                    ->required()
                                                    ->extraInputAttributes([
                                                        'style' => 'text-transform: uppercase',
                                                        'x-init' => 'setTimeout(() => $el.focus(), 100)'
                                                    ])
                                                    ->helperText('Gunakan barcode scanner untuk input otomatis')
                                                    ->dehydrateStateUsing(fn($state) => Str::upper($state))
                                                    ->maxLength(255)
                                                    ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, \Filament\Forms\Get $get) {
                                                        return $rule->where('kode_tambahan', $get('kode_tambahan'));
                                                    }),

                                                Forms\Components\TextInput::make('kode_tambahan')
                                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                                    ->dehydrateStateUsing(fn($state) => Str::upper($state))
                                                    ->maxLength(8)
                                                    ->placeholder('Contoh: "slope" untuk rokok')
                                                    ->helperText('Kode tambahan untuk produk dengan volume berbeda')
                                                    ->nullable()
                                                    ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, \Filament\Forms\Get $get) {
                                                        return $rule->where('kode_produk', $get('kode_produk'));
                                                    }),

                                                Forms\Components\TextInput::make('nama')
                                                    ->label('Nama Produk')
                                                    ->required()
                                                    ->helperText('Masukan nama Produk dan Volume, Contoh: "Sampo 500ml"')
                                                    ->columnSpanFull()
                                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                                    ->dehydrateStateUsing(fn($state) => Str::upper($state))
                                                    ->maxLength(255),

                                                Forms\Components\Select::make('kategori_produk_id')
                                                    ->relationship('kategori_produk', 'nama')
                                                    ->preload()
                                                    ->placeholder('Pilih Kategori Produk')
                                                    ->searchable()
                                                    ->searchDebounce(300)
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('nama')
                                                            ->required()
                                                            ->label('Nama Kategori')
                                                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                                            ->maxLength(255)
                                                            ->dehydrateStateUsing(fn($state) => Str::upper($state))
                                                            ->unique('kategori_produks', 'nama')
                                                            ->autofocus()
                                                            ->placeholder('Contoh: Makanan, Minuman, Alat Tulis')
                                                            ->helperText('Masukkan nama kategori produk')
                                                    ])
                                                    ->createOptionUsing(function (array $data) {
                                                        return \App\Models\KategoriProduk::create($data)->getKey();
                                                    })
                                                    ->createOptionAction(
                                                        fn(\Filament\Forms\Components\Actions\Action $action) => $action
                                                            ->modalHeading('Tambah Kategori Baru')
                                                            ->modalSubmitActionLabel('Simpan')
                                                            ->modalCancelActionLabel('Batal')
                                                    )
                                                    ->required(),

                                                Forms\Components\Select::make('satuan_id')
                                                    ->relationship('satuan', 'nama')
                                                    ->preload()
                                                    ->searchable()
                                                    ->searchDebounce(300)
                                                    ->required()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('nama')
                                                            ->label('Nama Satuan')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->dehydrateStateUsing(fn($state) => Str::upper($state))
                                                            ->unique('satuans', 'nama', ignoreRecord: true)
                                                            ->autofocus()
                                                            ->placeholder('Contoh: PCS, KG, LITER')
                                                            ->helperText('Masukkan nama satuan (contoh: PCS, KG, LITER)'),
                                                    ])
                                                    ->createOptionUsing(function (array $data) {
                                                        return \App\Models\Satuan::create($data)->getKey();
                                                    })
                                                    ->createOptionAction(
                                                        fn(\Filament\Forms\Components\Actions\Action $action) => $action
                                                            ->modalHeading('Tambah Satuan Baru')
                                                            ->modalSubmitActionLabel('Simpan')
                                                            ->modalCancelActionLabel('Batal')
                                                    )
                                                    ->required(),
                                            ])
                                            ->columnSpan(2)
                                            ->columns(2),

                                        Forms\Components\Section::make('Harga & Stok')
                                            ->description('Harga dan ketersediaan')
                                            ->schema([
                                                Forms\Components\TextInput::make('harga_beli')
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->numeric(),

                                                Forms\Components\TextInput::make('harga_jual')
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->numeric()
                                                    ->minValue(fn(Forms\Get $get): int => (int) $get('harga_beli'))
                                                    ->validationMessages([
                                                        'min' => 'Harga jual tidak boleh kurang dari harga beli (:min)',
                                                    ]),

                                                Forms\Components\TextInput::make('harga_grosir')
                                                    ->label('Harga Grosir')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->required()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->minValue(fn(Forms\Get $get): int => (int) $get('harga_beli'))
                                                    ->maxValue(fn(Forms\Get $get): int => (int) $get('harga_jual'))
                                                    ->validationMessages([
                                                        'min' => 'Harga grosir tidak boleh kurang dari harga beli (:min)',
                                                        'max' => 'Harga grosir tidak boleh melebihi harga jual (:max)',
                                                    ]),

                                                Forms\Components\TextInput::make('stok')
                                                    ->label('Stok Awal')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->required()
                                                    ->minValue(0)
                                                    ->helperText('Masukan stok awal jika ada')
                                                    ->hiddenOn('edit'),

                                                Forms\Components\Hidden::make('stok_minimum')
                                                    ->default(1),

                                                Forms\Components\Hidden::make('stok_maksimum'),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Media')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('gambar_produk')
                                    ->image()
                                    ->hiddenLabel()
                                    ->directory('gambar_produk')
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth('800')
                                    ->imageResizeTargetHeight('800')
                                    ->helperText('Format: JPG, PNG, WebP. Maksimal 5MB. Disarankan 800x800px.'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Satuan Lanjutan')
                            ->icon('heroicon-m-arrows-right-left')
                            ->schema([
                                Forms\Components\Repeater::make('units')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('satuan_id')
                                            ->relationship('satuan', 'nama')
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\TextInput::make('konversi')
                                            ->numeric()
                                            ->required()
                                            ->label('Isi (Pcs)')
                                            ->helperText('1 Satuan ini = berapa Pcs?'),
                                        Forms\Components\TextInput::make('harga_jual_khusus')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('Rp')
                                            ->label('Harga Jual Khusus')
                                            ->helperText('Kosongkan jika ikut harga hitungan'),
                                    ])
                                    ->defaultItems(0)
                                    ->columns(3)
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

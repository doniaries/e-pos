<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProdukResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProdukResource\RelationManagers;

use Filament\Support\Enums\MaxWidth;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Produk')
                    ->copyable()
                    ->searchable(['nama', 'kode_produk', 'kode_tambahan'])
                    ->description(fn(Produk $record) => $record->kode_produk . ($record->kode_tambahan ? ' | ' . $record->kode_tambahan : ''))
                    ->wrap(),
                Tables\Columns\TextColumn::make('kategori_produk.nama')
                    ->label('Kategori / Satuan')
                    ->description(fn(Produk $record) => 'Satuan: ' . $record->satuan->nama)
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->alignRight()
                    ->color('info')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format(self::formatCurrency($state), 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga (Jual/Grosir)')
                    ->alignRight()
                    ->color('success')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->description(fn(Produk $record) => 'Grosir: Rp ' . number_format($record->harga_grosir, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn($state) => $state > 4 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->description(fn($state) => $state > 4 ? 'Tersedia' : ($state > 0 ? 'Stok Rendah' : 'Habis'))
                    ->tooltip(fn($state) => $state > 4 ? 'Stok produk di gudang masih mencukupi' : ($state > 0 ? 'Peringatan: Stok menipis, segera re-stok produk ini!' : 'Bahaya: Stok kosong! Unit tidak bisa dijual.'))
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_produk_id')
                    ->label('Kategori')
                    ->relationship('kategori_produk', 'nama')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn() => \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\ProductTemplateExport,
                        'template_produk.xlsx'
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->modalAutofocus(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\Action::make('cetak_barcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Produk $record) => route('barcode.label103', ['products' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('cetak_barcode_bulk')
                        ->label('Cetak Barcode Label 103')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function ($records) {
                            $products = $records;

                            // If only 1 product selected, duplicate to fill 12 labels (Full Sheet)
                            if ($products->count() === 1) {
                                $singleProduct = $products->first();
                                $products = collect(array_fill(0, 12, $singleProduct));
                            }

                            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                            $barcodes = [];

                            foreach ($products as $product) {
                                $barcodeData = $product->kode_produk;
                                $barcodeImage = base64_encode($generator->getBarcode(
                                    $barcodeData,
                                    $generator::TYPE_CODE_128,
                                    3,
                                    50
                                ));

                                $barcodes[] = [
                                    'image' => $barcodeImage,
                                    'code' => $barcodeData,
                                    'name' => $product->nama,
                                    'price' => 'Rp ' . number_format($product->harga_jual, 0, ',', '.'),
                                ];
                            }

                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('barcode.label-103', [
                                'barcodes' => collect($barcodes)
                            ]);

                            $pdf->setPaper([0, 0, 595.28, 456.38], 'portrait');

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'barcodes-' . now()->format('YmdHis') . '.pdf');
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListProduks::route('/'),
            // 'create' => Pages\CreateProduk::route('/create'),
            // 'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }


    // In ProdukResource.php - Optimize search
    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_produk', 'kode_tambahan', 'nama'];
    }

    //Optimize Query di Resource
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'kode_produk', 'kode_tambahan', 'nama', 'kategori_produk_id', 'satuan_id', 'harga_beli', 'harga_jual', 'harga_grosir', 'stok', 'gambar_produk'])
            ->with(['kategori_produk:id,nama', 'satuan:id,nama']);
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }
}

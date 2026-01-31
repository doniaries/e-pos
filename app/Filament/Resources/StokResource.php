<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokResource\Pages;
use App\Filament\Resources\StokResource\RelationManagers;
use App\Models\Stok;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class StokResource extends Resource
{
    protected static ?string $model = Stok::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Stok';


    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Detail Transaksi Stok')
                            ->schema([
                                Forms\Components\Select::make('produk_id')
                                    ->relationship('produk', 'nama')
                                    ->label('Produk')
                                    ->disabled(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('jenis')
                                            ->label('Jenis')
                                            ->options([
                                                'pembelian' => 'Pembelian (Barang Masuk)',
                                                'penjualan' => 'Penjualan (Barang Keluar)',
                                            ])
                                            ->default('pembelian')
                                            ->required()
                                            ->disabledOn('edit'),

                                        Forms\Components\TextInput::make('jumlah')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->prefix(fn($state) => $state > 0 ? '+' : ''),

                                        Forms\Components\TextInput::make('harga_beli')
                                            ->label('Harga Beli (Satuan)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('Rp')
                                            ->visible(fn(Forms\Get $get) => $get('jenis') === 'pembelian')
                                            ->helperText('Hanya direcord untuk pembelian baru'),

                                        Forms\Components\TextInput::make('created_at')
                                            ->label('Tanggal')
                                            ->disabled()
                                            ->visibleOn('view'),
                                    ]),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('stok_awal')
                                            ->label('Stok Awal')
                                            ->numeric()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('stok_akhir')
                                            ->label('Stok Akhir')
                                            ->numeric()
                                            ->disabled(),
                                    ]),

                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ]),
                    ])->columnSpan(['lg' => 8]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Tambahan')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Admin / User')
                                    ->disabled(),

                                Forms\Components\TextInput::make('referensi_type')
                                    ->label('Tipe Referensi')
                                    ->formatStateUsing(fn($state) => class_basename($state))
                                    ->disabled(),

                                Forms\Components\TextInput::make('referensi_id')
                                    ->label('ID Referensi')
                                    ->disabled(),
                            ]),
                    ])->columnSpan(['lg' => 4]),
            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('produk.nama')
                    ->label('Produk')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('jenis')
                //     ->badge()
                //     ->color(fn(string $state): string => match ($state) {
                //         'pembelian' => 'success',
                //         'penjualan' => 'danger',
                //     }),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->formatStateUsing(fn($state) => ($state > 0 ? '+' : '') . number_format((float) $state, 0, ',', '.'))
                    ->color(fn(int $state): string => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ','),
                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Sisa')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->weight('bold')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state < 5 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                // Tables\Columns\TextColumn::make('referensi_type')
                //     ->label('Ref')
                //     ->formatStateUsing(fn($state) => class_basename($state))
                //     ->badge()
                //     ->color('gray')
                //     ->toggleable(),
                // Tables\Columns\TextColumn::make('keterangan')
                //     ->limit(30)
                //     ->tooltip(fn($state) => $state),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pengguna'),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'pembelian' => 'Pembelian',
                        'penjualan' => 'Penjualan',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->displayFormat('d F Y')
                            ->default(today())
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->displayFormat('d F Y')
                            ->default(today())
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
                            $query = Stok::with('produk', 'user', 'shift')
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

                            $pdf = Pdf::loadView('pdf.laporan-stok', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath'))
                                ->setPaper('a4', 'landscape');
                            return response()->streamDownload(fn() => print($pdf->output()), 'laporan-stok.pdf');
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListStoks::route('/'),
            // 'create' => Pages\CreateStok::route('/create'),
            'view' => Pages\ViewStok::route('/{record}'),
            'edit' => Pages\EditStok::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\RelationManagers;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian Produk';
    protected static ?string $navigationGroup = 'Transaksi';
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
                        Forms\Components\Section::make('Informasi Pembelian')
                            ->schema([
                                Forms\Components\TextInput::make('nomor_pembelian')
                                    ->default(fn() => 'PO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)))
                                    ->required()
                                    ->readOnly(),
                                Forms\Components\Select::make('distributor_id')
                                    ->relationship('distributor', 'nama_distributor')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_distributor')->required()->label('Nama Perusahaan/Distributor'),
                                        Forms\Components\TextInput::make('telepon'),
                                        Forms\Components\TextInput::make('kontak_person'),
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('tanggal_pembelian')
                                    ->default(now())
                                    ->required()
                                    ->live()
                                    ->displayFormat('d F Y')
                                    ->locale('id')
                                    ->native(false)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $dateCode = date('Ymd', strtotime($state));
                                            $set('nomor_pembelian', 'PO-' . $dateCode . '-' . strtoupper(\Illuminate\Support\Str::random(4)));
                                        }
                                    }),

                            ])->columns(2),

                        Forms\Components\Section::make('Item Pembelian')
                            ->schema([
                                Forms\Components\Repeater::make('details')
                                    ->relationship('details')
                                    ->schema([
                                        Forms\Components\Select::make('produk_id')
                                            ->relationship('produk', 'nama')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $produk = \App\Models\Produk::find($state);
                                                    $set('harga_beli', $produk->harga_beli ?? 0);
                                                }
                                            })
                                            ->columnSpan(6)
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('kode_produk')
                                                    ->required()
                                                    ->default(fn() => 'BRG-' . strtoupper(\Illuminate\Support\Str::random(5))),
                                                Forms\Components\TextInput::make('kode_tambahan')
                                                    ->unique('produks', 'kode_tambahan', ignoreRecord: true),
                                                Forms\Components\TextInput::make('nama')
                                                    ->required()
                                                    ->label('Nama Produk'),
                                                Forms\Components\Select::make('kategori_produk_id')
                                                    ->relationship('kategori_produk', 'nama')
                                                    ->preload()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('nama')->required()->unique('kategori_produks', 'nama'),
                                                    ])
                                                    ->required(),
                                                Forms\Components\Select::make('satuan_id')
                                                    ->relationship('satuan', 'nama')
                                                    ->preload()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('nama')->required()->unique('satuans', 'nama'),
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('harga_beli')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('harga_jual')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('stok')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->disabled(),
                                            ])
                                            ->editOptionForm([
                                                Forms\Components\TextInput::make('kode_produk')->required(),
                                                Forms\Components\TextInput::make('kode_tambahan'),
                                                Forms\Components\TextInput::make('nama')->required(),
                                                Forms\Components\Select::make('kategori_produk_id')
                                                    ->relationship('kategori_produk', 'nama')
                                                    ->required(),
                                                Forms\Components\Select::make('satuan_id')
                                                    ->relationship('satuan', 'nama')
                                                    ->required(),
                                                Forms\Components\TextInput::make('harga_beli')->numeric()->required(),
                                                Forms\Components\TextInput::make('harga_jual')->numeric()->required(),
                                            ]),

                                        Forms\Components\TextInput::make('jumlah')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => $set('total_harga', (int)$state * self::parseCurrency($get('harga_beli'))))
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('harga_beli')
                                            ->label('Harga Beli (@)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => $set('total_harga', self::parseCurrency($state) * (int)$get('jumlah')))
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('total_harga')
                                            ->label('Total')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->readOnly()
                                            ->default(0)
                                            ->columnSpan(2),
                                    ])
                                    ->columns(12)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        $details = $get('details');
                                        $total = 0;
                                        foreach ($details as $detail) {
                                            $total += ((int)($detail['jumlah'] ?? 0)) * self::parseCurrency($detail['harga_beli'] ?? 0);
                                        }
                                        $set('total_harga', $total);
                                    })
                            ]),

                        Forms\Components\Section::make('Rincian Biaya')
                            ->schema([
                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Pembelian')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->readOnly(),
                            ])->columns(1),

                        Forms\Components\Section::make('Catatan')
                            ->schema([
                                Forms\Components\Textarea::make('catatan')
                                    ->columnSpanFull(),
                            ])->columns(1),
                    ])->columnSpan(['lg' => 8]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('System Info')
                            ->schema([
                                Forms\Components\Placeholder::make('created_by_name')
                                    ->label('Dibuat Oleh')
                                    ->content(fn($record) => $record ? ($record->creator->name ?? 'N/A') : (auth()->user()?->name ?? 'System')),
                                Forms\Components\Hidden::make('created_by')
                                    ->default(fn() => auth()->user()?->id),
                            ]),
                    ])->columnSpan(['lg' => 4]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pembelian')
                    ->label('Nomor Pembelian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('distributor.nama_distributor')
                    ->label('Distributor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal Pembelian')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Pembelian')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_pembelian', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal_pembelian')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai')
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
                            ->when($data['dari'], fn($query, $date) => $query->whereDate('tanggal_pembelian', '>=', $date))
                            ->when($data['sampai'], fn($query, $date) => $query->whereDate('tanggal_pembelian', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('distributor_id')
                    ->label('Distributor')
                    ->relationship('distributor', 'nama_distributor')
                    ->searchable()
                    ->preload(),
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
                            $query = Pembelian::with('distributor', 'creator', 'shift')
                                ->whereBetween('tanggal_pembelian', [$data['dari'], $data['sampai']]);

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

                            $pdf = Pdf::loadView('pdf.laporan-pembelian', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath'))
                                ->setPaper('a4', 'landscape');
                            return response()->streamDownload(fn() => print($pdf->output()), 'laporan-pembelian.pdf');
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }

    private static function parseCurrency($number): int
    {
        if (empty($number)) return 0;
        if (is_int($number)) return $number;
        // Strip thousand separator (.) and handle decimal separator if any (,)
        return (int) str_replace(['.', ','], ['', '.'], $number);
    }
}

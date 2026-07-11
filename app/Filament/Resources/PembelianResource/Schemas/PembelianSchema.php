<?php

namespace App\Filament\Resources\PembelianResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class PembelianSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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
}

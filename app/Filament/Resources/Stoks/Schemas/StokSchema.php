<?php

namespace App\Filament\Resources\Stoks\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class StokSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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
}

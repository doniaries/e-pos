<?php

namespace App\Filament\Resources\Pembayarans\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class PembayaranSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('penjualan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('metode')
                    ->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nomor_kartu')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bukti_pembayaran')
                    ->maxLength(255),
                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }
}

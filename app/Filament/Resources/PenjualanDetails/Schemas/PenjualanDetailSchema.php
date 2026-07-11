<?php

namespace App\Filament\Resources\PenjualanDetails\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class PenjualanDetailSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('penjualan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('produk_id')
                    ->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('satuan_id')
                    ->required(),
                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }
}

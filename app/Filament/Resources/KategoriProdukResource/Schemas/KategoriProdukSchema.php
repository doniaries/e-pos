<?php

namespace App\Filament\Resources\KategoriProdukResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class KategoriProdukSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('nama')
                    ->unique(ignoreRecord: true)
                    ->autofocus()
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}

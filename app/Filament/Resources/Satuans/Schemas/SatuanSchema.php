<?php

namespace App\Filament\Resources\Satuans\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class SatuanSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('nama')
                    ->unique(ignoreRecord: true)
                    ->autofocus()
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}

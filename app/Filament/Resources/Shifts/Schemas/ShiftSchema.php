<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class ShiftSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Section::make('Informasi Shift')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Shift 1, Shift 2, Reguler'),
                        Forms\Components\TimePicker::make('jam_mulai')
                            ->required()
                            ->native(false)
                            ->seconds(false),
                        Forms\Components\TimePicker::make('jam_selesai')
                            ->required()
                            ->native(false)
                            ->seconds(false),
                    ])->columns(3)
            ]);
    }
}

<?php

namespace App\Filament\Resources\AppInfoResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class AppInfoSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Informasi Aplikasi')->schema([
                    Forms\Components\TextInput::make('nama_aplikasi')
                        ->label('Nama Aplikasi')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('version')
                        ->label('Versi')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('pembuat')
                        ->label('Pembuat')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tahun')
                        ->label('Tahun')
                        ->required()
                        ->maxLength(255),
                ])->columns(2),
            ]);
    }
}

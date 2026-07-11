<?php

namespace App\Filament\Resources\DistributorResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class DistributorSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('kode_distributor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_distributor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_perusahaan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kota')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('provinsi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_pos')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('telepon')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kontak_person')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('no_hp')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('status_aktif')
                    ->required(),
            ]);
    }
}

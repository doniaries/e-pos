<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class UserSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_kontak')
                    ->tel(),
                Forms\Components\TextInput::make('alamat')
                    ->maxLength(255),
                Forms\Components\Select::make('tipe')
                    ->options([
                        'admin' => 'admin',
                        'kasir' => 'kasir',
                        'petugas stok' => 'petugas stok',
                    ])
                    ->default('kasir')
                    ->required(),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
            ]);
    }
}

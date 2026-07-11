<?php

namespace App\Filament\Resources\PelangganResource\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class PelangganSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Section::make('Data Member')
                    ->description('Informasi identitas pelanggan tetap (member).')
                    ->schema([
                        Forms\Components\TextInput::make('kode_member')
                            ->label('ID Member')
                            ->placeholder('Otomatis')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn($record) => $record !== null),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->dehydrateStateUsing(fn($state) => strtoupper($state))
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('tanggal_bergabung')
                            ->label('Tanggal Bergabung')
                            ->default(now())
                            ->required()
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false),
                        Forms\Components\TextInput::make('kontak')
                            ->label('No. Telepon / WhatsApp')
                            ->placeholder('08xxx')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Rumah')
                            ->placeholder('Jl. ...')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Status Keuangan')
                    ->description('Informasi hutang atau saldo member.')
                    ->schema([
                        Forms\Components\TextInput::make('hutang')
                            ->label('Total Hutang')
                            ->currencyMask(
                                thousandSeparator: '.',
                                decimalSeparator: ',',
                                precision: 0
                            )
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Jumlah hutang yang belum dibayar oleh member.')
                            ->numeric()
                    ])->columns(1),
            ]);
    }
}

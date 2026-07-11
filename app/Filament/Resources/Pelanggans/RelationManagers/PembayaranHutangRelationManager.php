<?php

namespace App\Filament\Resources\Pelanggans\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PembayaranHutangRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaranHutang';

    protected static ?string $title = 'Riwayat Pembayaran Hutang';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\DateTimePicker::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->disabled()
                    ->displayFormat('d F Y H:i')
                    ->locale('id')
                    ->native(false),
                Forms\Components\TextInput::make('jumlah_bayar')
                    ->label('Jumlah Bayar')
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\TextInput::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->numeric()
                    ->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.'))
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\TextInput::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->disabled(),
                Forms\Components\Textarea::make('catatan')
                    ->label('Keterangan')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal_bayar')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_bayar')
                    ->label('Jumlah Bayar')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 0, ',', '.') . ',-')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 0, ',', '.') . ',-')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir'),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Keterangan')
                    ->limit(30),
            ])
            ->defaultSort('tanggal_bayar', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}

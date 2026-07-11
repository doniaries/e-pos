<?php

namespace App\Filament\Resources\Pelanggans\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualansRelationManager extends RelationManager
{
    protected static string $relationship = 'penjualans';

    protected static ?string $title = 'Riwayat Transaksi (Hutang)';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor')
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total Transaksi')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.') . ',-')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('bayar')
                    ->label('Dibayar')
                    ->numeric(thousandsSeparator: '.', decimalSeparator: ',')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.') . ',-')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'lunas' => 'success',
                        'bayar_sebagian' => 'warning',
                        'hutang' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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

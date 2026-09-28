<?php

namespace App\Filament\Resources\AppInfoResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class AppInfoTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_aplikasi')
                    ->label('Nama Aplikasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Versi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pembuat')
                    ->label('Pembuat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\LaporanHarians\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Filters;

class LaporanHarianTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_tutup')
                    ->dateTime('H:i')
                    ->label('Jam Tutup'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
                Tables\Columns\TextColumn::make('jumlah_transaksi')
                    ->label('Trx')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_omset')
                    ->label('Omset')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uang_tunai_di_laci')
                    ->label('Fisik Laci')
                    ->numeric(),
                Tables\Columns\TextColumn::make('selisih')
                    ->label('Selisih')
                    ->numeric()
                    ->color(fn($state) => $state == 0 ? 'success' : 'danger'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (LaporanHarian $record) {
                        $storeStatus = \App\Models\StoreStatus::where('tanggal', $record->tanggal->toDateString())->first();
                        $pdf = Pdf::loadView('pdf.laporan-harian-detail', compact('record', 'storeStatus'));
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "laporan-harian-" . \Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d') . ".pdf");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

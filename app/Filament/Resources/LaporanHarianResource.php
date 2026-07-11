<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanHarianResource\Pages;
use App\Filament\Resources\LaporanHarianResource\RelationManagers;
use App\Filament\Resources\LaporanHarianResource\RelationManagers\PenjualansRelationManager;
use App\Models\LaporanHarian;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class LaporanHarianResource extends Resource
{
    protected static ?string $model = LaporanHarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Riwayat Tutup Toko';
    protected static ?string $pluralLabel = 'Riwayat Tutup Toko';
    protected static ?string $modelLabel = 'Riwayat Tutup Toko';
    // protected static ?string $navigationGroup = 'Laporan';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\LaporanHarianResource\Schemas\LaporanHarianSchema::form($schema);
    }

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

    public static function getRelations(): array
    {
        return [
            RelationManagers\PenjualansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanHarians::route('/'),
            // 'create' => Pages\CreateLaporanHarian::route('/create'),
            // 'edit' => Pages\EditLaporanHarian::route('/{record}/edit'),
        ];
    }
}

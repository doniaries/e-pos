<?php

namespace App\Filament\Resources\LaporanHarians;

use App\Filament\Resources\LaporanHarians\Pages;
use App\Filament\Resources\LaporanHarians\RelationManagers;
use App\Filament\Resources\LaporanHarians\RelationManagers\PenjualansRelationManager;
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
        return \App\Filament\Resources\LaporanHarians\Schemas\LaporanHarianSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\LaporanHarians\Tables\LaporanHarianTable::table($table);
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

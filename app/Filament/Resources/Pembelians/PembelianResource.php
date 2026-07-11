<?php

namespace App\Filament\Resources\Pembelians;

use App\Filament\Resources\Pembelians\Pages;
use App\Filament\Resources\Pembelians\RelationManagers;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $slug = 'pembelians';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian Produk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Pembelians\Schemas\PembelianSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Pembelians\Tables\PembelianTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }

    private static function parseCurrency($number): int
    {
        if (empty($number)) return 0;
        if (is_int($number)) return $number;
        // Strip thousand separator (.) and handle decimal separator if any (,)
        return (int) str_replace(['.', ','], ['', '.'], $number);
    }
}

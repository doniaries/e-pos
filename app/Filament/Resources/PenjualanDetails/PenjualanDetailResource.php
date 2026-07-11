<?php

namespace App\Filament\Resources\PenjualanDetails;

use App\Filament\Resources\PenjualanDetails\Pages;
use App\Filament\Resources\PenjualanDetails\RelationManagers;
use App\Models\PenjualanDetail;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualanDetailResource extends Resource
{
    protected static ?string $model = PenjualanDetail::class;
    protected static ?string $slug = 'penjualan-details';

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\PenjualanDetails\Schemas\PenjualanDetailSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\PenjualanDetails\Tables\PenjualanDetailTable::table($table);
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
            'index' => Pages\ListPenjualanDetails::route('/'),
            'create' => Pages\CreatePenjualanDetail::route('/create'),
            'edit' => Pages\EditPenjualanDetail::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'penjualan_id', 'produk_id', 'satuan_id', 'jumlah', 'harga', 'subtotal'])
            ->with(['produk:id,nama,kode_produk,harga_jual']);
    }
}

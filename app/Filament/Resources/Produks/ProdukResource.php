<?php

namespace App\Filament\Resources\Produks;

use Filament\Forms;
use Filament\Tables;
use App\Models\Produk;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Produks\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Produks\RelationManagers;

use Filament\Support\Enums\MaxWidth;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Produks\Schemas\ProdukSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Produks\Tables\ProdukTable::table($table);
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
            'index' => Pages\ListProduks::route('/'),
            // 'create' => Pages\CreateProduk::route('/create'),
            // 'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }


    // In ProdukResource.php - Optimize search
    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_produk', 'kode_tambahan', 'nama'];
    }

    //Optimize Query di Resource
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'kode_produk', 'kode_tambahan', 'nama', 'kategori_produk_id', 'satuan_id', 'harga_beli', 'harga_jual', 'harga_grosir', 'stok', 'gambar_produk'])
            ->with(['kategori_produk:id,nama', 'satuan:id,nama']);
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }
}

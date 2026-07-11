<?php

namespace App\Filament\Resources\KategoriProduks;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use App\Models\KategoriProduk;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\KategoriProduks\Pages;
use App\Filament\Resources\KategoriProduks\RelationManagers;

class KategoriProdukResource extends Resource
{
    protected static ?string $model = KategoriProduk::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\KategoriProduks\Schemas\KategoriProdukSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\KategoriProduks\Tables\KategoriProdukTable::table($table);
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
            'index' => Pages\ListKategoriProduks::route('/'),
            'create' => Pages\CreateKategoriProduk::route('/create'),
            'edit' => Pages\EditKategoriProduk::route('/{record}/edit'),
        ];
    }

    public static function getAllCached()
    {
        return Cache::remember('kategori_produk_all', 3600, function () {
            return static::select(['id', 'nama'])->get();
        });
    }
}

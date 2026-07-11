<?php

namespace App\Filament\Resources\Satuans;

use Filament\Forms;
use Filament\Tables;
use App\Models\Satuan;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Satuans\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Satuans\RelationManagers;

class SatuanResource extends Resource
{
    protected static ?string $model = Satuan::class;
    protected static ?string $slug = 'satuans';

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Satuans\Schemas\SatuanSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Satuans\Tables\SatuanTable::table($table);
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
            'index' => Pages\ListSatuans::route('/'),
            // 'create' => Pages\CreateSatuan::route('/create'),
            // 'edit' => Pages\EditSatuan::route('/{record}/edit'),
        ];
    }

    public static function getAllCached()
    {
        return Cache::remember('satuan_all', 3600, function () {
            return static::select(['id', 'nama'])->get();
        });
    }
}

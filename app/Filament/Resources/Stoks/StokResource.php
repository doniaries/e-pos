<?php

namespace App\Filament\Resources\Stoks;

use App\Filament\Resources\Stoks\Pages;
use App\Filament\Resources\Stoks\RelationManagers;
use App\Models\Stok;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class StokResource extends Resource
{
    protected static ?string $model = Stok::class;
    protected static ?string $slug = 'stoks';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Stok';


    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Stoks\Schemas\StokSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Stoks\Tables\StokTable::table($table);
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
            'index' => Pages\ListStoks::route('/'),
            // 'create' => Pages\CreateStok::route('/create'),
            'view' => Pages\ViewStok::route('/{record}'),
            'edit' => Pages\EditStok::route('/{record}/edit'),
        ];
    }
}

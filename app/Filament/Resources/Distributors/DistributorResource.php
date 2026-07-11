<?php

namespace App\Filament\Resources\Distributors;

use App\Filament\Resources\Distributors\Pages;
use App\Filament\Resources\Distributors\RelationManagers;
use App\Models\Distributor;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistributorResource extends Resource
{
    protected static ?string $model = Distributor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Distributors\Schemas\DistributorSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Distributors\Tables\DistributorTable::table($table);
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
            'index' => Pages\ListDistributors::route('/'),
            'create' => Pages\CreateDistributor::route('/create'),
            'edit' => Pages\EditDistributor::route('/{record}/edit'),
        ];
    }
}

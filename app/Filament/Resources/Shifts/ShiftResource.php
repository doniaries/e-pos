<?php

namespace App\Filament\Resources\Shifts;

use App\Filament\Resources\Shifts\Pages;
use App\Filament\Resources\Shifts\RelationManagers;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Shift';
    protected static ?string $pluralModelLabel = 'Shift';
    protected static ?string $navigationLabel = 'Jadwal Shift';
    protected static ?string $pluralLabel = 'Jadwal Shift';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Shifts\Schemas\ShiftSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Shifts\Tables\ShiftTable::table($table);
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}

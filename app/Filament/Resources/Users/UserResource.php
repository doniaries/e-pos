<?php

namespace App\Filament\Resources\Users;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Users\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use App\Filament\Resources\Users\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Users\Schemas\UserSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Users\Tables\UserTable::table($table);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

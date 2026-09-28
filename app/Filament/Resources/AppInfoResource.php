<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppInfoResource\Pages;
use App\Models\AppInfo;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppInfoResource extends Resource
{
    protected static ?string $model = AppInfo::class;
    
    protected static ?string $slug = 'app-infos';

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    
    protected static ?string $navigationLabel = 'Informasi Aplikasi';
    
    protected static ?string $navigationGroup = 'Pengaturan';
    
    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }

    public static function canCreate(): bool
    {
        return AppInfo::count() < 1;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\AppInfoResource\Schemas\AppInfoSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\AppInfoResource\Tables\AppInfoTable::table($table);
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
            'index' => Pages\ListAppInfos::route('/'),
            // 'create' => Pages\CreateAppInfo::route('/create'),
            'edit' => Pages\EditAppInfo::route('/{record}/edit'),
        ];
    }
}

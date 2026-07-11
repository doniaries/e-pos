<?php

namespace App\Filament\Resources\Aplikasis;

use App\Filament\Resources\Aplikasis\Pages;
use App\Filament\Resources\Aplikasis\RelationManagers;
use App\Models\Aplikasi;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AplikasiResource extends Resource
{
    protected static ?string $model = Aplikasi::class;
    protected static ?string $slug = 'aplikasis';

    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->email === 'superadmin@gmail.com';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->email === 'superadmin@gmail.com';
    }


    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Aplikasis\Schemas\AplikasiSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Aplikasis\Tables\AplikasiTable::table($table);
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
            'index' => Pages\ListAplikasis::route('/'),
            'create' => Pages\CreateAplikasi::route('/create'),
            'edit' => Pages\EditAplikasi::route('/{record}/edit'),
        ];
    }
}

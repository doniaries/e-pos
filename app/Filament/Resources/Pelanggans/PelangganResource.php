<?php

namespace App\Filament\Resources\Pelanggans;

use App\Filament\Resources\Pelanggans\Pages;
use App\Models\Pelanggan;
use App\Models\PembayaranHutangMember;
use App\Filament\Resources\Pelanggans\RelationManagers\PembayaranHutangRelationManager;
use App\Filament\Resources\Pelanggans\RelationManagers\PenjualansRelationManager;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PelangganResource extends Resource
{
    protected static ?string $model = Pelanggan::class;
    protected static ?string $slug = 'pelanggans';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $pluralLabel = 'Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Pelanggans\Schemas\PelangganSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Pelanggans\Tables\PelangganTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            PenjualansRelationManager::class,
            PembayaranHutangRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPelanggans::route('/'),
            'create' => Pages\CreatePelanggan::route('/create'),
            'edit' => Pages\EditPelanggan::route('/{record}/edit'),
        ];
    }
}

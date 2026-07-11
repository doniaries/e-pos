<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages;
use App\Filament\Resources\Settings\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $slug = 'settings';

    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return Setting::count() < 1;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Settings\Schemas\SettingSchema::form($schema);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Toko')
                    ->schema([
                        Components\TextEntry::make('nama_perusahaan')
                            ->label('Nama Perusahaan'),
                        Components\TextEntry::make('alamat')
                            ->label('Alamat'),
                        Components\TextEntry::make('kontak')
                            ->label('Kontak'),
                        Components\TextEntry::make('pimpinan')
                            ->label('Pimpinan'),
                        Components\TextEntry::make('tipe_toko')
                            ->label('Tipe Toko')
                            ->badge(),
                        Components\ImageEntry::make('logo')
                            ->label('Logo')
                            ->disk('public')
                            ->circular(),
                    ])->columns(2),

                Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Components\TextEntry::make('nama_bank')
                            ->label('Nama Bank'),
                        Components\TextEntry::make('no_rekening')
                            ->label('Nomor Rekening')
                            ->copyable(),
                        Components\ImageEntry::make('qr_code_image')
                            ->label('QR Code')
                            ->disk('public')
                            ->columnSpanFull(),
                    ])->columns(2),

                Components\Section::make('Pengaturan Printer')
                    ->schema([
                        Components\TextEntry::make('printer_tipe')
                            ->label('Tipe Printer')
                            ->badge(),
                        Components\TextEntry::make('printer_lebar_kertas')
                            ->label('Lebar Kertas')
                            ->visible(fn($record) => $record->printer_tipe === 'thermal'),
                        Components\TextEntry::make('printer_koneksi')
                            ->label('Metode Koneksi')
                            ->badge()
                            ->color('warning'), // Always USB now
                        Components\TextEntry::make('printer_nama')
                            ->label('Nama Printer')
                            ->default('-'),
                        // Removed Auto Cetak display
                        Components\TextEntry::make('printer_footer')
                            ->label('Footer Struk')
                            ->columnSpanFull()
                            ->default('Terima Kasih'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Settings\Tables\SettingTable::table($table);
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
            'index' => Pages\ListSettings::route('/'),
            // 'create' => Pages\CreateSetting::route('/create'),
            // 'view' => Pages\ViewSetting::route('/{record}'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}

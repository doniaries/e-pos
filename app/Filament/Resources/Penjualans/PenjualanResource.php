<?php

namespace App\Filament\Resources\Penjualans;

use App\Filament\Resources\Penjualans\Pages;
use App\Models\Penjualan;
// use App\Http\Controllers\PenjualanController;
use App\Models\Produk;
use App\Models\Pelanggan;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form as Schema;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $pluralLabel = 'Penjualan';
    protected static ?string $modelLabel = 'Penjualan';
    protected static ?string $slug = 'penjualan';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return !(\App\Models\Setting::first()?->is_toko_tutup ?? false);
    }



    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Penjualan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nomor')
                                    ->label('No Invoice'),
                                TextEntry::make('created_at')
                                    ->label('Tanggal')
                                    ->badge()
                                    ->dateTime('d F Y H:i'),
                                TextEntry::make('kasir.name')
                                    ->label('Kasir'),
                                TextEntry::make('shift.nama')
                                    ->label('Shift')
                                    ->badge()
                                    ->color('primary')
                                    ->default('-'),
                                TextEntry::make('pelanggan.nama')
                                    ->label('Pelanggan')
                                    ->default('Umum')
                                    ->weight('bold'),
                                TextEntry::make('pelanggan')
                                    ->label('Tipe')
                                    ->badge()
                                    ->formatStateUsing(function ($record) {
                                        if (!$record->pelanggan) return 'Umum';
                                        return $record->pelanggan->kode_member
                                            ? 'Pelanggan Tetap (' . $record->pelanggan->kode_member . ')'
                                            : 'Pelanggan Tetap';
                                    })
                                    ->color(fn($record) => $record->pelanggan ? 'success' : 'gray'),

                                TextEntry::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                                TextEntry::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'hutang' => 'danger',
                                        'bayar_sebagian' => 'warning',
                                        'lunas' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('debt_amount')
                                    ->label('Hutang Ditambahkan')
                                    ->money('IDR')
                                    ->badge()
                                    ->color('danger')
                                    ->formatStateUsing(fn($record) => number_format($record->total - $record->bayar, 0, ',', '.'))
                                    ->visible(fn($record) => $record->status_pembayaran !== 'lunas')
                                    ->helperText(fn($record) => 'Total: Rp ' . number_format($record->total, 0, ',', '.') . ' | Bayar: Rp ' . number_format($record->bayar, 0, ',', '.')),
                            ]),
                    ]),

                Section::make('Detail Item')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('produk.nama')
                                            ->label('Produk'),
                                        TextEntry::make('jumlah')
                                            ->label('Qty'),
                                        TextEntry::make('harga')
                                            ->label('Harga')
                                            ->money('IDR'),
                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('IDR'),
                                    ]),
                            ])
                            ->grid(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->money('IDR'),
                                TextEntry::make('diskon_nilai')
                                    ->label('Diskon')
                                    ->money('IDR'),
                                TextEntry::make('pajak_nilai')
                                    ->label('Pajak')
                                    ->money('IDR'),
                                TextEntry::make('total')
                                    ->money('IDR')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('bayar')
                                    ->money('IDR'),
                                TextEntry::make('kembali')
                                    ->money('IDR'),
                            ]),
                    ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Penjualans\Schemas\PenjualanSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Penjualans\Tables\PenjualanTable::table($table);
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            // 'view' => Pages\ViewPenjualan::route('/{record}'),
        ];
    }

    protected static function recalculateTotal(Forms\Set $set, Forms\Get $get): void
    {
        $details = collect($get('details') ?? [])->toArray();
        $subtotal = collect($details)->sum('subtotal');

        $set('subtotal', $subtotal);

        $diskon_persen = $get('diskon_persen') ?? 0;
        $diskon_nilai = $get('diskon_nilai') ?? 0;

        if ($diskon_persen > 0) {
            $diskon_nilai = round($subtotal * ($diskon_persen / 100));
            $set('diskon_nilai', $diskon_nilai);
        }

        $total_setelah_diskon = $subtotal - $diskon_nilai;

        $pajak_persen = $get('pajak_persen') ?? 0;
        $pajak_nilai = round($total_setelah_diskon * ($pajak_persen / 100));
        $set('pajak_nilai', $pajak_nilai);

        $total = $total_setelah_diskon + $pajak_nilai;
        $set('total', $total);

        $bayar = $get('bayar') ?? 0;
        $set('kembali', $bayar - $total);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'nomor',
                'user_id',
                'pelanggan_id',
                'subtotal',
                'diskon_nilai',
                'pajak_nilai',
                'total',
                'bayar',
                'kembali',
                'metode_pembayaran',
                'status_pembayaran',
                'created_at'
            ])
            ->with([
                'kasir:id,name',
                'pelanggan:id,nama',
                'details:id,penjualan_id,produk_id,jumlah,subtotal'
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nomor', 'kasir.name', 'pelanggan.nama'];
    }
}

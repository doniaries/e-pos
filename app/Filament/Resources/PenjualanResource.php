<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
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
        return \App\Filament\Resources\PenjualanResource\Schemas\PenjualanSchema::form($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('3s') //hot reload data
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kasir.name')
                    ->label('Kasir')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->default('Umum')
                    ->searchable()
                    ->description(fn($record) => $record->pelanggan ? 'Member: ' . $record->pelanggan->kode_member : null),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Cara Bayar')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tunai' => 'Tunai',
                        'transfer' => 'Transfer',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('bayar')
                    ->label('Bayar')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('kembali')
                    ->label('Kembali')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('IDR')),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hutang' => 'danger',
                        'bayar_sebagian' => 'warning',
                        'lunas' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '>=', \Carbon\Carbon::parse($date)->startOfDay()),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date) => $query->where('created_at', '<=', \Carbon\Carbon::parse($date)->endOfDay()),
                            );
                    }),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'nama')
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(fn($livewire) => $livewire->dispatch('$refresh')),
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\DatePicker::make('sampai')
                            ->required()
                            ->default(today())
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->format('Y-m-d')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->maxDate(now()),
                        Forms\Components\Select::make('shift_id')
                            ->label('Filter Shift')
                            ->relationship('shift', 'nama')
                            ->placeholder('Semua Shift'),
                    ])
                    ->action(function ($data) {
                        try {
                            $query = Penjualan::with('kasir', 'shift')
                                ->whereBetween('created_at', [$data['dari'] . ' 00:00', $data['sampai'] . ' 23:59']);

                            if (!empty($data['shift_id'])) {
                                $query->where('shift_id', $data['shift_id']);
                            }

                            $records = $query->get();
                            $start = $data['dari'];
                            $end = $data['sampai'];

                            $setting = \App\Models\Setting::first();
                            $storeName = $setting->nama_perusahaan ?? 'POS System';
                            $storeAddress = $setting->alamat ?? 'Alamat Toko Belum Diatur';
                            $userName = auth()->user()?->name ?? 'System';

                            $logoPath = public_path('images/logo e-pos.png');
                            if ($setting && $setting->logo && file_exists(storage_path('app/public/' . $setting->logo))) {
                                $logoPath = storage_path('app/public/' . $setting->logo);
                            }

                            $storeStatuses = \App\Models\StoreStatus::whereBetween('tanggal', [$data['dari'], $data['sampai']])
                                ->where('is_tutup', true)
                                ->get();

                            $pdf = Pdf::loadView('pdf.laporan-penjualan', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath', 'storeStatuses'))
                                ->setPaper('a4', 'landscape');
                            $shiftName = 'semua-shift';
                            if (!empty($data['shift_id'])) {
                                $shift = \App\Models\Shift::find($data['shift_id']);
                                if ($shift) {
                                    $shiftName = 'shift-' . \Illuminate\Support\Str::slug($shift->nama);
                                }
                            }

                            $filename = 'laporan-penjualan-' . $start;
                            if ($start !== $end) {
                                $filename .= '-sd-' . $end;
                            }
                            $filename .= '-' . $shiftName . '.pdf';

                            return response()->streamDownload(fn() => print($pdf->output()), $filename);
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Cetak PDF')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Struk')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Penjualan $record): string => route('pos.print-struk', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
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

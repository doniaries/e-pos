<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanHarianResource\Pages;
use App\Filament\Resources\LaporanHarianResource\RelationManagers;
use App\Filament\Resources\LaporanHarianResource\RelationManagers\PenjualansRelationManager;
use App\Models\LaporanHarian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class LaporanHarianResource extends Resource
{
    protected static ?string $model = LaporanHarian::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Riwayat Tutup Toko';
    protected static ?string $pluralLabel = 'Riwayat Tutup Toko';
    protected static ?string $modelLabel = 'Riwayat Tutup Toko';
    // protected static ?string $navigationGroup = 'Laporan';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tutup Buku')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->default(now())
                            ->live()
                            ->displayFormat('d F Y')
                            ->locale('id')
                            ->native(false)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $penjualan = \App\Models\Penjualan::whereDate('created_at', $state)
                                        ->where('status', 'selesai')
                                        ->get();

                                    $omset = $penjualan->sum('total');
                                    $tunai = $penjualan->where('metode_pembayaran', 'tunai')->sum('total');
                                    $nontunai = $penjualan->where('metode_pembayaran', '!=', 'tunai')->sum('total');
                                    $jumlah = $penjualan->count();

                                    $set('total_omset', $omset);
                                    $set('total_tunai', $tunai);
                                    $set('total_nontunai', $nontunai);
                                    $set('jumlah_transaksi', $jumlah);
                                }
                            }),
                        Forms\Components\DateTimePicker::make('waktu_tutup')
                            ->default(now())
                            ->required()
                            ->displayFormat('d F Y H:i')
                            ->locale('id')
                            ->native(false),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ])->columns(2),

                Forms\Components\Section::make('Ringkasan Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_transaksi')
                            ->label('Jml Transaksi')
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_omset')
                            ->label('Total Omset')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_tunai')
                            ->label('Total Tunai (Sistem)')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_nontunai')
                            ->label('Total Non-Tunai')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly(),
                    ])->columns(4),

                Forms\Components\Section::make('Rekonsiliasi Kas')
                    ->description('Bandingkan uang fisik di laci dengan hitungan sistem')
                    ->schema([
                        Forms\Components\TextInput::make('uang_tunai_di_laci')
                            ->label('Uang Fisik di Laci')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $sistem = $get('total_tunai') ?? 0;
                                $selisih = $sistem - $state;
                                $set('selisih', $selisih);
                            }),
                        Forms\Components\TextInput::make('selisih')
                            ->label('Selisih (Kurang/Lebih)')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly()
                            ->helperText('Jika positif berarti kurang setor, negatif berarti lebih.'),
                    ])->columns(2),

                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('waktu_tutup')
                    ->dateTime('H:i')
                    ->label('Jam Tutup'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
                Tables\Columns\TextColumn::make('jumlah_transaksi')
                    ->label('Trx')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_omset')
                    ->label('Omset')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uang_tunai_di_laci')
                    ->label('Fisik Laci')
                    ->numeric(),
                Tables\Columns\TextColumn::make('selisih')
                    ->label('Selisih')
                    ->numeric()
                    ->color(fn($state) => $state == 0 ? 'success' : 'danger'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (LaporanHarian $record) {
                        $storeStatus = \App\Models\StoreStatus::where('tanggal', $record->tanggal->toDateString())->first();
                        $pdf = Pdf::loadView('pdf.laporan-harian-detail', compact('record', 'storeStatus'));
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "laporan-harian-" . \Illuminate\Support\Carbon::parse($record->tanggal)->format('Y-m-d') . ".pdf");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PenjualansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanHarians::route('/'),
            // 'create' => Pages\CreateLaporanHarian::route('/create'),
            // 'edit' => Pages\EditLaporanHarian::route('/{record}/edit'),
        ];
    }
}

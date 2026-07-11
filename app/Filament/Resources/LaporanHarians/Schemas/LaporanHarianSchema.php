<?php

namespace App\Filament\Resources\LaporanHarians\Schemas;

use Filament\Forms;
use Filament\Forms\Form as Schema;

class LaporanHarianSchema
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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
}

<?php

namespace App\Filament\Resources\LaporanHarians\Pages;

use App\Filament\Resources\LaporanHarians\LaporanHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanHarians extends ListRecords
{
    protected static string $resource = LaporanHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetak_laporan_periodik')
                ->label('Cetak Laporan Periodik')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('jenis_laporan')
                        ->options([
                            'penjualan' => 'Laporan Penjualan',
                            'pembelian' => 'Laporan Pembelian',
                            'stok' => 'Laporan Riwayat Stok',
                        ])
                        ->required()
                        ->default('penjualan'),
                    \Filament\Forms\Components\DatePicker::make('tanggal_awal')
                        ->default(now())
                        ->required()
                        ->displayFormat('d F Y')
                        ->locale('id')
                        ->native(false),
                    \Filament\Forms\Components\DatePicker::make('tanggal_akhir')
                        ->default(now())
                        ->required()
                        ->displayFormat('d F Y')
                        ->locale('id')
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $jenis = $data['jenis_laporan'];
                    $start = $data['tanggal_awal'];
                    $end = $data['tanggal_akhir'];

                    if ($jenis == 'penjualan') {
                        $records = \App\Models\Penjualan::with('kasir')
                            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                            ->latest()
                            ->get();
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-penjualan', compact('records', 'start', 'end'));
                        return response()->streamDownload(fn() => print($pdf->output()), 'laporan-penjualan.pdf');
                    } elseif ($jenis == 'pembelian') {
                        $records = \App\Models\Pembelian::with('distributor')
                            ->whereBetween('tanggal_pembelian', [$start, $end])
                            ->latest()
                            ->get();
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-pembelian', compact('records', 'start', 'end'));
                        return response()->streamDownload(fn() => print($pdf->output()), 'laporan-pembelian.pdf');
                    } elseif ($jenis == 'stok') {
                        $records = \App\Models\Stok::with(['produk', 'user'])
                            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                            ->latest()
                            ->get();
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-stok', compact('records', 'start', 'end'));
                        return response()->streamDownload(fn() => print($pdf->output()), 'laporan-riwayat-stok.pdf');
                    }
                }),
            // Actions\CreateAction::make(),
        ];
    }
}

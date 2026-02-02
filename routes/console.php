<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;
use App\Models\StoreStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\LaporanHarian;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    Log::info('Menjalankan tugas Tutup Hari Transaksi otomatis.');

    // Ambil transaksi yang belum masuk laporan harian
    $pendingQuery = Penjualan::whereNull('laporan_harian_id')
        ->where('status', 'selesai');

    if ($pendingQuery->count() === 0) {
        Log::info('Tidak ada transaksi penjualan untuk ditutup hari ini.');
        return;
    }

    $pendingSales = $pendingQuery->with('pembayarans')->get();

    // Hitung total
    $count = $pendingSales->count();
    $omset = $pendingSales->sum('total');
    $totalTunai = 0;
    $totalNonTunai = 0;

    foreach ($pendingSales as $sale) {
        foreach ($sale->pembayarans as $payment) {
            if (strtolower($payment->metode) === 'tunai') {
                $totalTunai += $payment->jumlah;
            } else {
                $totalNonTunai += $payment->jumlah;
            }
        }
    }

    try {
        DB::beginTransaction();

        // Gunakan user pertama (biasanya Superadmin) sebagai pembuat laporan
        $systemUserId = \App\Models\User::first()->id ?? 1;

        $laporan = LaporanHarian::create([
            'tanggal' => now()->toDateString(),
            'waktu_tutup' => now(),
            'user_id' => $systemUserId,
            'jumlah_transaksi' => $count,
            'total_omset' => $omset,
            'total_tunai' => $totalTunai,
            'total_nontunai' => $totalNonTunai,
            'uang_tunai_di_laci' => 0, // Auto-close: dianggap 0 atau tidak dihitung
            'selisih' => $totalTunai, // System - Fisik(0) = Selisih (+)
            'catatan' => 'Tutup Hari Otomatis oleh Sistem (Jadwal 23:59)'
        ]);

        // Update semua penjualan terkait
        Penjualan::whereIn('id', $pendingSales->pluck('id'))
            ->update(['laporan_harian_id' => $laporan->id]);

        DB::commit();
        Log::info('Tutup Hari Transaksi berhasil. Laporan ID: ' . $laporan->id);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal Tutup Hari Otomatis: ' . $e->getMessage());
    }
})->dailyAt('23:59');

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Setting;
use App\Models\StoreStatus;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    Log::info('Menjalankan tugas menutup toko otomatis.');

    // Update Setting Token
    $setting = Setting::first();
    if ($setting) {
        $setting->update(['is_toko_tutup' => true]);
    }

    // Catat di StoreStatus
    StoreStatus::updateOrCreate(
        ['tanggal' => now()->toDateString()],
        [
            'is_tutup' => true,
            'catatan' => 'Tutup otomatis oleh sistem (Jadwal 23:59)'
        ]
    );

    Log::info('Toko berhasil ditutup otomatis.');
})->dailyAt('23:59');

<?php

use App\Livewire\Pos;
use App\Models\Setting;
use App\Livewire\CetakStruk;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\PenjualanController;


Route::name('login')->get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::redirect('/', '/admin/login');

Route::get('/pos', Pos::class)->middleware('auth')->name('pos');

// Route to display the logo
Route::get('/logo', function () {
    try {
        $setting = Setting::first();

        if ($setting && $setting->logo) {
            $path = is_array($setting->logo) ? $setting->logo[0] : $setting->logo;
            $logoPath = storage_path('app/public/' . ltrim($path, '/'));

            if (file_exists($logoPath)) {
                return response()->file($logoPath);
            }
        }
    } catch (\Exception $e) {
        Log::error('Error loading logo: ' . $e->getMessage());
    }

    // Return the placeholder if logo is not found or error occurs
    $placeholderPath = public_path('images/placeholder.jpg');

    if (!file_exists($placeholderPath)) {
        // Create a simple placeholder if it doesn't exist
        $placeholder = '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="200" height="100" viewBox="0 0 200 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="100" fill="#f0f0f0"/>
            <text x="100" y="50" font-family="Arial" font-size="12" text-anchor="middle" fill="#999">No Logo</text>
        </svg>';

        file_put_contents($placeholderPath, $placeholder);
    }

    return response()->file($placeholderPath);
})->name('logo');

// Route for barcode printing
Route::get('/barcode/label-103', [BarcodeController::class, 'printLabel103'])
    ->middleware('auth')
    ->name('barcode.label103');

// Route for POS receipt printing
Route::get('/pos/print-struk/{id}', [PosController::class, 'printStruk'])
    ->middleware('auth')
    ->name('pos.print-struk');

Route::get('/pos/test-print', [PosController::class, 'testPrint'])
    ->middleware('auth')
    ->name('pos.test-print');

// Direct thermal printing routes
Route::post('/pos/direct-print/{id}', [PosController::class, 'directPrint'])
    ->middleware('auth')
    ->name('pos.direct-print');

Route::post('/pos/test-direct-print', [PosController::class, 'testDirectPrint'])
    ->middleware('auth')
    ->name('pos.test-direct-print');
Route::get('/pos/print-struk/{id}', CetakStruk::class)
    ->middleware('auth')
    ->name('pos.print-struk');
Route::get('/admin/toggle-store', function () {
    $setting = Setting::first();
    if ($setting) {
        $setting->update([
            'is_toko_tutup' => !$setting->is_toko_tutup,
        ]);

        // Record to store_statuses
        \App\Models\StoreStatus::updateOrCreate(
            ['tanggal' => now()->toDateString()],
            [
                'is_tutup' => $setting->is_toko_tutup,
                'catatan' => $setting->is_toko_tutup ? ($setting->pesan_tutup ?? 'Toko Tutup') : 'Toko Buka',
            ]
        );

        $status = $setting->is_toko_tutup ? 'DITUTUP' : 'DIBUKA';
        return back()->with('notification', "Toko berhasil $status");
    }
    return back();
})->middleware(['auth'])->name('admin.store.toggle');

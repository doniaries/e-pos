<?php
$data = ['dari' => '2026-07-01', 'sampai' => '2026-07-30', 'shift_id' => null];
$query = \App\Models\Penjualan::with('kasir', 'shift')
    ->whereBetween('created_at', [$data['dari'] . ' 00:00', $data['sampai'] . ' 23:59']);
$records = $query->get();
$start = $data['dari'];
$end = $data['sampai'];
$setting = \App\Models\Setting::first();
$storeName = $setting->nama_perusahaan ?? 'POS System';
$storeAddress = $setting->alamat ?? 'Alamat Toko Belum Diatur';
$userName = 'System';
$logoPath = public_path('images/logo e-pos.png');
$storeStatuses = \App\Models\StoreStatus::whereBetween('tanggal', [$data['dari'], $data['sampai']])
    ->where('is_tutup', true)
    ->get();
try {
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-penjualan', compact('records', 'start', 'end', 'storeName', 'storeAddress', 'userName', 'logoPath', 'storeStatuses'))
        ->setPaper('a4', 'landscape');
    $pdf->output();
    echo "SUCCESS";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

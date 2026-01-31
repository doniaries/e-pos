<?php

namespace App\Observers;

use App\Models\PembelianDetail;

class PembelianDetailObserver
{
    /**
     * Handle the PembelianDetail "created" event.
     */
    public function created(PembelianDetail $pembelianDetail): void
    {
        // Otomatis tambah stok saat detail pembelian dibuat
        \App\Models\Stok::create([
            'produk_id' => $pembelianDetail->produk_id,
            'jenis' => 'pembelian',
            'jumlah' => $pembelianDetail->jumlah, // Quantity pembelian
            'harga_beli' => $pembelianDetail->harga_beli, // Record harga beli untuk HPP
            'user_id' => auth()->id() ?? 1, // Fallback to admin if running from Seeder
            'referensi_type' => \App\Models\Pembelian::class,
            'referensi_id' => $pembelianDetail->pembelian_id,
            'keterangan' => 'Pembelian #' . ($pembelianDetail->pembelian->nomor_pembelian ?? '-'),
        ]);
    }

    /**
     * Handle the PembelianDetail "updated" event.
     */
    public function updated(PembelianDetail $pembelianDetail): void
    {
        // Handle logic jika jumlah pembelian diedit (opsional, future improvement)
    }

    /**
     * Handle the PembelianDetail "deleted" event.
     */
    public function deleted(PembelianDetail $pembelianDetail): void
    {
        // Otomatis kurangi stok jika detail pembelian dihapus (void)
        \App\Models\Stok::create([
            'produk_id' => $pembelianDetail->produk_id,
            'jenis' => 'penjualan', // Anggap keluar/retur/batal beli
            'jumlah' => - ($pembelianDetail->jumlah),
            'user_id' => auth()->id() ?? 1,
            'referensi_type' => \App\Models\Pembelian::class,
            'referensi_id' => $pembelianDetail->pembelian_id,
            'keterangan' => 'Pembelian Dibatalkan (Detail Deleted)',
        ]);
    }

    /**
     * Handle the PembelianDetail "restored" event.
     */
    public function restored(PembelianDetail $pembelianDetail): void
    {
        //
    }

    /**
     * Handle the PembelianDetail "force deleted" event.
     */
    public function forceDeleted(PembelianDetail $pembelianDetail): void
    {
        //
    }
}

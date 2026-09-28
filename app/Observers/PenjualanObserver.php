<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\Stok;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "deleted" (soft delete / void) event.
     *
     * [Bug #2 Fix] Stok dikembalikan dengan benar karena:
     *   - POS sekarang mencatat ke tabel stoks via Stok::create() dengan jumlah negatif
     *   - Saat Stok dihapus di sini, StokObserver::deleted() menjalankan:
     *     $stok->produk()->decrement('stok', $stok->jumlah)
     *     decrement(-5) = increment 5 → stok kembali otomatis
     */
    public function deleted(Penjualan $penjualan): void
    {
        // 1. Soft delete detail transaksi
        $penjualan->details()->delete();

        // 2. Hapus entri stok ledger — StokObserver::deleted() akan otomatis
        //    membalik perubahan stok ke tabel produks (karena jumlah negatif jadi increment)
        Stok::where('referensi_type', get_class($penjualan))
            ->where('referensi_id', $penjualan->id)
            ->each(function (Stok $stok) {
                $stok->delete(); // Trigger StokObserver::deleted()
            });

        // 3. Kembalikan hutang pelanggan jika ada
        if ($penjualan->pelanggan_id && $penjualan->status_pembayaran !== Penjualan::PAYMENT_PAID) {
            $debtAmount = $penjualan->total - $penjualan->bayar;
            if ($debtAmount > 0) {
                $penjualan->pelanggan()->decrement('hutang', $debtAmount);
            }
        }
    }

    /**
     * Handle the Penjualan "restored" event (un-void).
     *
     * [R5 Fix] Sebelumnya logika restore stok tidak diimplementasi (diakui di komentar).
     *           Sekarang: restore detail lalu re-create entri stoks agar StokObserver
     *           mengurangi stok kembali seperti semula.
     */
    public function restored(Penjualan $penjualan): void
    {
        // 1. Restore detail transaksi
        $penjualan->details()->withTrashed()->restore();

        // 2. Re-create entri stok ledger untuk setiap item
        //    StokObserver::created() akan update produks.stok secara otomatis
        $penjualan->details()->each(function ($detail) use ($penjualan) {
            // Pastikan tidak ada duplikat (jika restore dipanggil berkali-kali)
            $exists = Stok::where('referensi_type', Penjualan::class)
                ->where('referensi_id', $penjualan->id)
                ->where('produk_id', $detail->produk_id)
                ->exists();

            if (! $exists) {
                Stok::create([
                    'produk_id'      => $detail->produk_id,
                    'jenis'          => 'penjualan',
                    'jumlah'         => -abs($detail->jumlah), // Pastikan negatif
                    'referensi_type' => Penjualan::class,
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Restore Penjualan #' . $penjualan->nomor,
                    'user_id'        => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
        });

        // 3. Tambah kembali hutang pelanggan jika relevan
        if ($penjualan->pelanggan_id && $penjualan->status_pembayaran !== Penjualan::PAYMENT_PAID) {
            $debtAmount = $penjualan->total - $penjualan->bayar;
            if ($debtAmount > 0) {
                $penjualan->pelanggan()->increment('hutang', $debtAmount);
            }
        }
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(Penjualan $penjualan): void
    {
        $penjualan->details()->withTrashed()->forceDelete();
    }
}

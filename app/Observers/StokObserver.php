<?php

namespace App\Observers;

use App\Models\Stok;

class StokObserver
{
    public function creating(Stok $stok): void
    {
        // Load fresh product instance to ensure accuracy
        $produk = $stok->produk()->lockForUpdate()->find($stok->produk_id);

        if (!$produk) return;

        $currentStok = $produk->stok;

        if ($stok->is_from_product_observer) {
            // Kasus: Update Manual di Data Produk (Produk sudah berubah duluan)
            // Maka stok_awal adalah stok sekarang dikurangi jumlah perubahan
            $stok->stok_awal = $currentStok - $stok->jumlah;
            $stok->stok_akhir = $currentStok;
        } else {
            // Kasus: Transaksi (Penjualan/Pembelian) atau Adjustment murni
            // Produk belum berubah, stok observer yang akan mengubahnya
            $stok->stok_awal = $currentStok;
            $stok->stok_akhir = $currentStok + $stok->jumlah;
        }
    }

    public function created(Stok $stok): void
    {
        // Jika perubahan bukan berasal dari ProdukObserver (artinya dari Transaksi),
        // maka kita wajib update stok di tabel Produk.
        if (! $stok->is_from_product_observer) {
            $stok->produk->updateQuietly([
                'stok' => $stok->stok_akhir
            ]);
        }
    }

    public function updating(Stok $stok): void
    {
        if ($stok->isDirty('jumlah')) {
            // ... (keep current updating logic)
            $oldJumlah = $stok->getOriginal('jumlah');
            $newJumlah = $stok->jumlah;
            $diff = $newJumlah - $oldJumlah;

            $stok->stok_akhir = $stok->stok_awal + $newJumlah;

            if ($stok->produk) {
                $stok->produk()->increment('stok', $diff);
            }
        }
    }

    public function deleted(Stok $stok): void
    {
        // Reverse the stock change: if we added 5, now we subtract 5.
        // If we subtracted 5 (sale), now we add 5.
        if ($stok->produk) {
            $stok->produk()->decrement('stok', $stok->jumlah);
        }
    }
}

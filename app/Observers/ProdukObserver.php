<?php

namespace App\Observers;

use App\Models\Produk;

class ProdukObserver
{
    public function created(Produk $produk): void
    {
        if ($produk->stok != 0) {
            $stok = new \App\Models\Stok([
                'produk_id' => $produk->id,
                'jenis' => 'pembelian', // Stok awal dianggap pembelian (modal awal)
                'jumlah' => $produk->stok,
                // Stok awal/akhir will be handled by StokObserver logic, 
                // but since we flag it, it knows product is already updated.
                'keterangan' => 'Stok awal produk baru',
                'user_id' => auth()->id() ?? 1,
            ]);
            $stok->is_from_product_observer = true;
            $stok->save();
        }
    }

    public function updated(Produk $produk): void
    {
        if ($produk->isDirty('stok')) {
            $stokLama = $produk->getOriginal('stok');
            $stokBaru = $produk->stok;
            $selisih = $stokBaru - $stokLama;

            if ($selisih != 0) {
                // Positif = Pembelian (Masuk), Negatif = Penjualan (Keluar)
                $jenis = $selisih > 0 ? 'pembelian' : 'penjualan';

                $stok = new \App\Models\Stok([
                    'produk_id' => $produk->id,
                    'jenis' => $jenis,
                    'jumlah' => $selisih, // Jumlah tetap +/- sesuai selisih
                    'keterangan' => 'Penyesuaian manual (Edit Produk)',
                    'user_id' => auth()->id() ?? 1,
                ]);
                $stok->is_from_product_observer = true;
                $stok->save();
            }
        }
    }

    public function deleted(Produk $produk): void
    {
        // For deletion, product is gone, so sync back is irrelevant, 
        // but we record the 'keluar' for history.
        if ($produk->stok > 0) {
            $stok = new \App\Models\Stok([
                'produk_id' => $produk->id,
                'jenis' => 'penjualan', // Penghapusan produk dianggap barang keluar/terjual/hilang
                'jumlah' => -$produk->stok,
                'keterangan' => 'Produk dihapus',
                'user_id' => auth()->id() ?? 1,
            ]);
            $stok->is_from_product_observer = true;
            $stok->save();
        }
    }
}

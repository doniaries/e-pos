<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\Stok;

class PenjualanObserver
{
    /**
     * Handle the Penjualan "deleted" event.
     */
    public function deleted(Penjualan $penjualan): void
    {
        // 1. Soft delete associated details
        $penjualan->details()->delete();

        // 2. Delete associated Stok records (this reverses master stock via StokObserver)
        Stok::where('referensi_type', get_class($penjualan))
            ->where('referensi_id', $penjualan->id)
            ->delete();

        // 3. Reverse customer debt if applicable
        if ($penjualan->pelanggan_id && $penjualan->status_pembayaran !== 'lunas') {
            $debtAmount = $penjualan->total - $penjualan->bayar;
            if ($debtAmount > 0) {
                $penjualan->pelanggan()->decrement('hutang', $debtAmount);
            }
        }
    }

    /**
     * Handle the Penjualan "restored" event.
     */
    public function restored(Penjualan $penjualan): void
    {
        // Restore details
        $penjualan->details()->withTrashed()->restore();

        // Note: Full stock restoration logic would involve re-creating stok records
        // which matches the complex logic in the POS/Livewire component.
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(Penjualan $penjualan): void
    {
        // Ensure everything is cleaned up even on force delete
        $penjualan->details()->withTrashed()->forceDelete();
    }
}

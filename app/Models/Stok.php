<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Stok extends Model
{
    use \App\Traits\HasShift;

    /**
     * Flag to indicate if the stock change is triggered by ProdoukObserver
     * (meaning the product stock has already been updated).
     */
    public bool $is_from_product_observer = false;

    protected $table = 'stoks'; // Explicit table name just in case

    protected $fillable = [
        'produk_id',
        'shift_id',
        'jenis',
        'referensi_type',
        'referensi_id',
        'jumlah',
        'stok_awal',
        'stok_akhir',
        'keterangan',
        'user_id',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent referensi model (transaction).
     */
    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukUnit extends Model
{
    protected $fillable = [
        'produk_id',
        'satuan_id',
        'konversi',
        'harga_jual_khusus',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}

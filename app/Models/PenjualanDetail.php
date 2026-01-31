<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanDetail extends Model
{
    use SoftDeletes;

    protected $table = 'penjualan_details';
    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'jumlah',
        'satuan_id',
        'harga',
        'diskon_persen',
        'diskon_nilai',
        'subtotal',
        'catatan'
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga' => 'decimal:0',
        'diskon_persen' => 'integer',
        'subtotal' => 'decimal:0'
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}

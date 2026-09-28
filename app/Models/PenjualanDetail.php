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
        'nama_produk',  // [S3 Fix] Snapshot nama produk saat transaksi
        'harga_beli',   // [S3 Fix] Snapshot harga modal untuk laporan laba akurat
        'jumlah',
        'satuan_id',
        'harga',
        'diskon_persen',
        'diskon_nilai',
        'subtotal',
        'catatan',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:3', // [S4 Fix] Decimal agar bisa jual 0.5 kg, dll
        'harga'        => 'decimal:0',
        'harga_beli'   => 'decimal:0',
        'diskon_persen' => 'integer',
        'subtotal'     => 'decimal:0',
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

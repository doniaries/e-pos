<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembelianDetail extends Model
{
    use SoftDeletes;
    //
    // Cache untuk data detail pembelian
    public static function getAllCached()
    {
        return \Cache::remember('pembelian_detail_all', 3600, function () {
            return static::with(['pembelian'])->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            \Cache::forget('pembelian_detail_all');
        });
        static::deleted(function () {
            \Cache::forget('pembelian_detail_all');
        });
    }

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'harga_beli',
        'jumlah',
        'total_harga',
    ];

    // protected $with = ['pembelian']; // Removed to prevent infinite loop

    public function pembelian()
    {
        return $this->belongsTo(\App\Models\Pembelian::class, 'pembelian_id');
    }

    public function produk()
    {
        return $this->belongsTo(\App\Models\Produk::class, 'produk_id');
    }
}

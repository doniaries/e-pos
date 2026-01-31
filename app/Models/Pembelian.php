<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Pembelian extends Model
{
    use SoftDeletes, \App\Traits\HasShift;

    // Cache untuk data pembelian
    public static function getAllCached()
    {
        return Cache::remember('pembelian_all', 3600, function () {
            return static::with('details')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('pembelian_all');
        });
        static::deleted(function () {
            Cache::forget('pembelian_all');
        });
    }

    protected $fillable = [
        'nomor_pembelian',
        'distributor_id',
        'shift_id',
        'tanggal_pembelian',
        'total_harga',
        'catatan',
        'created_by',
    ];

    // protected $with = ['details']; // Removed to prevent infinite loop with PembelianDetail

    public function details()
    {
        return $this->hasMany(\App\Models\PembelianDetail::class, 'pembelian_id');
    }

    public function distributor()
    {
        return $this->belongsTo(\App\Models\Distributor::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}

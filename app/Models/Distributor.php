<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use SoftDeletes;

    protected $table = 'distributors';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_distributor',
        'nama_distributor',
        'nama_perusahaan',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'telepon',
        'email',
        'kontak_person',
        'no_hp',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get all pembelians for the distributor.
     */
    public function pembelians()
    {
        return $this->hasMany(\App\Models\Pembelian::class, 'distributor_id');
    }

    // Cache untuk data distributor
    public static function getAllCached()
    {
        return \Illuminate\Support\Facades\Cache::remember('distributor_all', 3600, function () {
            return static::with('pembelians')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('distributor_all');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('distributor_all');
        });
    }

    // protected $with = ['pembelians']; // Removed to prevent infinite loop/memory issues
}

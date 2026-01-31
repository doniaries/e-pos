<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKeuangan extends Model
{
    // Cache untuk data laporan keuangan
    public static function getAllCached()
    {
        return \Cache::remember('laporan_keuangan_all', 3600, function () {
            return static::all();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            \Cache::forget('laporan_keuangan_all');
        });
        static::deleted(function () {
            \Cache::forget('laporan_keuangan_all');
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'nama_aplikasi',
        'version',
        'pembuat',
        'tahun',
        'nama_perusahaan',
        'alamat',
        'kontak',
        'pimpinan',
        'tipe_toko',
        'logo',
        'printer_tipe',
        'printer_lebar_kertas',
        'printer_auto_cetak',
        'printer_koneksi',
        'printer_nama',
        'printer_share_name',
        'printer_ip_address',
        'printer_port',
        'printer_footer',
        'nama_bank',
        'no_rekening',
        'qr_code_image',
        'is_toko_tutup',
        'pesan_tutup',
    ];

    protected $casts = [
        // 'logo' => 'array', // Removed to support single file string path
    ];

    // Cache untuk data setting
    public static function getAllCached()
    {
        return Cache::remember('setting_all', 3600, function () {
            return static::all();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('setting_all');
        });
        static::deleted(function () {
            Cache::forget('setting_all');
        });
    }
}

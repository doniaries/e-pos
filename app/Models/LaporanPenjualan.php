<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LaporanPenjualan extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'total_transaksi',
        'total_omset',
        'total_tunai',
        'total_non_tunai',
        'uang_diterima',
        'selisih',
        'catatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LaporanHarian extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_tutup' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //
}

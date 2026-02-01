<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreStatus extends Model
{
    protected $fillable = [
        'tanggal',
        'is_tutup',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_tutup' => 'boolean',
    ];
}

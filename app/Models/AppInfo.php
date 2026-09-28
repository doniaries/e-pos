<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppInfo extends Model
{
    protected $fillable = [
        'nama_aplikasi',
        'version',
        'pembuat',
        'tahun',
    ];
}

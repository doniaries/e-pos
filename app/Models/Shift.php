<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'jam_mulai',
        'jam_selesai',
    ];

    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class);
    }

    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class);
    }

    public function stoks(): HasMany
    {
        return $this->hasMany(Stok::class);
    }
}

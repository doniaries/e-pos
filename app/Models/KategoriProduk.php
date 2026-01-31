<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriProduk extends Model
{
    use HasFactory;

    protected $table = 'kategori_produks';



    protected $fillable = [
        'nama',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class)->select(['id', 'kategori_produk_id', 'nama', 'stok']);
    }

    // Cache untuk data yang sering diakses
    public static function getAllCached()
    {
        return Cache::remember('kategori_produk_all', 3600, function () {
            return static::all();
        });
    }

    // Auto uppercase untuk nama
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }
}

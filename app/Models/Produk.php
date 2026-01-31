<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory, SoftDeletes;



    protected $table = 'produks';

    // Selective eager loading dengan kolom yang diperlukan saja
    protected $with = [
        'kategori_produk:id,nama',
        'satuan:id,nama'
    ];

    protected $fillable = [
        'kode_produk',
        'kode_tambahan',
        'nama',
        'kategori_produk_id',
        'satuan_id',
        'harga_beli',
        'harga_jual',
        'harga_grosir',
        'stok',
        'stok_minimum',
        'stok_maksimum',
        'gambar_produk',
    ];

    protected $casts = [
        'harga_beli' => 'integer',
        'harga_jual' => 'integer',
        'harga_grosir' => 'integer',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'stok_maksimum' => 'integer',
    ];

    // Cache query yang sering digunakan
    public static function getInStockCached()
    {
        return Cache::remember('produk_in_stock', 3600, function () {
            return static::inStock()->get();
        });
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stok', '>', 0);
    }


    // Load relasi dengan select spesifik
    public function kategori_produk()
    {
        return $this->belongsTo(KategoriProduk::class)->select(['id', 'nama']);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class)->select(['id', 'nama']);
    }

    public function units()
    {
        return $this->hasMany(ProdukUnit::class);
    }

    public function stoks()
    {
        return $this->hasMany(Stok::class)->orderBy('created_at', 'desc');
    }

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }
}

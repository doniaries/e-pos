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
        'spesifikasi',
    ];

    protected $casts = [
        'harga_beli'   => 'integer',
        'harga_jual'   => 'integer',
        'harga_grosir' => 'integer',
        'stok'         => 'decimal:3', // [S4 Fix] Decimal untuk satuan pecahan (kg, liter, dll)
        'stok_minimum' => 'decimal:3',
        'stok_maksimum' => 'decimal:3',
        'spesifikasi'  => 'array',
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
        // [S5 Fix] Pindahkan UPPERCASE ke sisi tampil (get), bukan sisi tulis (set).
        // Sebelumnya set: strtoupper() memutasi data permanen di DB sehingga tidak bisa
        // dikembalikan ke format asli tanpa update massal. Sekarang DB menyimpan format asli.
        return Attribute::make(
            get: fn($value) => strtoupper($value),
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';
    protected $fillable = ['nama'];

    // Cache untuk data yang jarang berubah
    public static function getAllCached()
    {
        return Cache::remember('satuan_all', 3600, function () {
            return static::all();
        });
    }

    public function produk()
    {
        return $this->hasMany(Produk::class)->select(['id', 'satuan_id', 'nama', 'stok']);
    }

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }
}

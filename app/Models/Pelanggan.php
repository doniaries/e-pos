<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggans';

    protected $fillable = [
        'kode_member',
        'nama',
        'kontak',
        'alamat',
        'hutang',
        'tanggal_bergabung',
    ];

    protected $casts = [
        'hutang' => 'decimal:0',
        'tanggal_bergabung' => 'date',
    ];

    // Scope untuk pelanggan dengan hutang
    public function scopeHasDebt(Builder $query): Builder
    {
        return $query->where('hutang', '>', 0);
    }

    // Auto uppercase untuk nama dan alamat
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }

    protected function alamat(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }

    // Generate Kode Member
    public static function generateKodeMember()
    {
        $prefix = 'MBR-' . date('Y') . '-';
        $lastMember = static::where('kode_member', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastMember ? (int) substr($lastMember->kode_member, -4) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    // Cache untuk data pelanggan
    public static function getAllCached()
    {
        return Cache::remember('pelanggan_all', 3600, function () {
            return static::all();
        });
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_member)) {
                $model->kode_member = static::generateKodeMember();
            }
            if (empty($model->tanggal_bergabung)) {
                $model->tanggal_bergabung = now();
            }
        });

        static::saved(function () {
            Cache::forget('pelanggan_all');
        });
        static::deleted(function () {
            Cache::forget('pelanggan_all');
        });
    }

    public function pembayaranHutang()
    {
        return $this->hasMany(PembayaranHutangMember::class, 'pelanggan_id');
    }
}

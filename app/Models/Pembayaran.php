<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'penjualan_id',
        'metode',
        'jumlah',
        'nomor_kartu',
        'bank',
        'bukti_pembayaran',
        'catatan'
    ];

    protected $casts = [
        'jumlah' => 'decimal:0'
    ];

    // Load dengan penjualan
    protected $with = ['penjualan:id,nomor,total'];

    // Relasi ke penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }





    // Scope untuk filter berdasarkan metode
    public function scopeMetode($query, $metode)
    {
        return $query->where('metode', $metode);
    }

    // Menyimpan bukti pembayaran
    public function simpanBukti($file)
    {
        if ($file) {
            $filename = 'bukti_' . $this->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/bukti_pembayaran', $filename);

            $this->update(['bukti_pembayaran' => $filename]);
        }
    }

    protected static function booted()
    {
        // Update status pembayaran penjualan setelah pembayaran disimpan
        static::created(function ($pembayaran) {
            $pembayaran->penjualan->updateStatusPembayaran();
        });

        static::updated(function ($pembayaran) {
            $pembayaran->penjualan->updateStatusPembayaran();
        });

        static::deleted(function ($pembayaran) {
            $pembayaran->penjualan->updateStatusPembayaran();
        });
    }
}

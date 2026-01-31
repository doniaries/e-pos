<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranHutangMember extends Model
{
    protected $table = 'pembayaran_hutang_members';

    protected $fillable = [
        'pelanggan_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'sisa_hutang',
        'metode_pembayaran',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'sisa_hutang' => 'decimal:2',
        'tanggal_bayar' => 'datetime',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

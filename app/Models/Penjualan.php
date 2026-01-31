<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\PenjualanDetail;
use App\Models\LaporanHarian;

class Penjualan extends Model
{
    use SoftDeletes, \App\Traits\HasShift;

    // Status transaksi
    const STATUS_DRAFT = 'draft';
    const STATUS_PROCESS = 'proses';
    const STATUS_COMPLETED = 'selesai';
    const STATUS_CANCELLED = 'batal';

    // Status pembayaran
    const PAYMENT_UNPAID = 'belum_bayar';
    const PAYMENT_PARTIAL = 'sebagian';
    const PAYMENT_PAID = 'lunas';

    protected $fillable = [
        'nomor',
        'user_id',
        'shift_id',
        'pelanggan_id',
        'subtotal',
        'diskon_persen',
        'diskon_nilai',
        'pajak_persen',
        'total',
        'metode_pembayaran',
        'nama_bank',
        'nomor_rekening',
        'bayar',
        'kembali',
        'status_pembayaran',
        'status',
        'catatan'
    ];

    protected $casts = [
        'subtotal' => 'decimal:0',
        'diskon_persen' => 'decimal:2',
        'diskon_nilai' => 'decimal:0',
        'pajak_persen' => 'integer',
        'total' => 'decimal:0',
        'bayar' => 'decimal:0',
        'kembali' => 'decimal:0'
    ];

    protected $with = ['kasir', 'pelanggan'];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }


    public static function generateNomor()
    {
        $today = now();
        $prefix = 'INV';
        $tahun = $today->format('Y');
        $bulan = $today->format('m');
        $hari = $today->format('d');

        // Pastikan menyertakan data yang sudah dihapus (soft-deleted) agar nomor unik tidak bertabrakan
        $lastInvoice = self::withTrashed()
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->whereDay('created_at', $hari)
            ->latest()
            ->first();

        $urutan = $lastInvoice ? intval(substr($lastInvoice->nomor, -4)) + 1 : 1;

        return sprintf("%s/%s/%s/%s/%04d", $prefix, $tahun, $bulan, $hari, $urutan);
    }

    // In Penjualan.php
    public function laporan_harian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }

    public function updateStatusPembayaran()
    {
        $totalBayar = $this->pembayarans()->sum('jumlah'); // Assuming relations exist, or use logic below
        // Since Pembayaran model exists and based on grep it seems related.
        // Wait, Penjualan doesn't have 'pembayarans' relation defined yet in this file. I should add it too.

        $this->bayar = $totalBayar;

        if ($totalBayar >= $this->total) {
            $this->status_pembayaran = self::PAYMENT_PAID;
            $this->kembali = (string)($totalBayar - $this->total);
        } elseif ($totalBayar > 0) {
            $this->status_pembayaran = self::PAYMENT_PARTIAL;
            $this->kembali = '0';
        } else {
            $this->status_pembayaran = self::PAYMENT_UNPAID;
            $this->kembali = '0';
        }

        $this->saveQuietly();
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class)->latest();
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function scopePendingReport($query)
    {
        return $query->whereNull('laporan_harian_id')->where('status', 'selesai');
    }
}

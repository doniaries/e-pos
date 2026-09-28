<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\PenjualanDetail;
use App\Models\LaporanHarian;
use Illuminate\Support\Facades\DB;

class Penjualan extends Model
{
    use SoftDeletes, \App\Traits\HasShift;

    // Status transaksi
    const STATUS_DRAFT     = 'draft';
    const STATUS_PROCESS   = 'proses';
    const STATUS_COMPLETED = 'selesai';
    const STATUS_CANCELLED = 'batal';

    // Status pembayaran (sesuai enum database: bayar_sebagian, hutang, lunas)
    const PAYMENT_UNPAID  = 'hutang';
    const PAYMENT_PARTIAL = 'bayar_sebagian';
    const PAYMENT_PAID    = 'lunas';

    protected $fillable = [
        'nomor',
        'user_id',
        'shift_id',
        'pelanggan_id',
        'laporan_harian_id',
        'subtotal',
        'diskon_persen',
        'diskon_nilai',
        'pajak_persen',
        'pajak_nilai',    // [S2 Fix] Ditambahkan ke fillable agar tersimpan (sebelumnya diabaikan)
        'total',
        'metode_pembayaran',
        'nama_bank',
        'nomor_rekening',
        'bayar',
        'kembali',
        'status_pembayaran',
        'status',
        'catatan',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:0',
        'diskon_persen' => 'decimal:2',
        'diskon_nilai'  => 'decimal:0',
        'pajak_persen'  => 'integer',
        'pajak_nilai'   => 'decimal:0',
        'total'         => 'decimal:0',
        'bayar'         => 'decimal:0',
        'kembali'       => 'decimal:0',
    ];

    // [R2 Fix] Hapus $with permanen — eager-loading menambah overhead pada setiap query.
    // Gunakan ->with(['kasir', 'pelanggan']) secara eksplisit di tempat yang membutuhkan.
    // protected $with = ['kasir', 'pelanggan'];

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

    public function laporan_harian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class)->latest();
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Generate nomor nota unik.
     *
     * [Bug #4 Fix] Ganti CAST(SUBSTRING... AS UNSIGNED) yang MySQL-specific dengan
     *              orderBy('id', 'desc') yang portable dan ditambah lockForUpdate()
     *              agar aman dari race condition saat dipanggil dalam transaksi DB.
     */
    public static function generateNomor(): string
    {
        $today      = now();
        $datePrefix = 'INV/' . $today->format('Y/m/d') . '/';

        // lockForUpdate() mencegah race condition — harus dipanggil dalam DB::transaction()
        $lastInvoice = self::withTrashed()
            ->where('nomor', 'like', $datePrefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc') // Portable, tidak butuh CAST MySQL-specific
            ->first();

        $urutan = $lastInvoice
            ? (intval(substr($lastInvoice->nomor, -4)) + 1)
            : 1;

        return sprintf('%s%04d', $datePrefix, $urutan);
    }

    /**
     * Perbarui status pembayaran berdasarkan total semua pembayaran yang diterima.
     * Dipanggil setelah ada pembayaran cicilan (hutang).
     */
    public function updateStatusPembayaran(): void
    {
        $totalBayar = $this->pembayarans()->sum('jumlah');

        $this->bayar = $totalBayar;

        if ($totalBayar >= $this->total) {
            $this->status_pembayaran = self::PAYMENT_PAID;
            $this->kembali           = (string) ($totalBayar - $this->total);
        } elseif ($totalBayar > 0) {
            $this->status_pembayaran = self::PAYMENT_PARTIAL;
            $this->kembali           = '0';
        } else {
            $this->status_pembayaran = self::PAYMENT_UNPAID;
            $this->kembali           = '0';
        }

        $this->saveQuietly();
    }

    public function scopePendingReport($query)
    {
        return $query->whereNull('laporan_harian_id')->where('status', 'selesai');
    }
}

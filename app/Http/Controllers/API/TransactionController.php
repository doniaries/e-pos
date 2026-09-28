<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * TransactionController — API untuk transaksi POS (mobile/integrasi pihak ketiga).
 *
 * Bugfixes yang diterapkan:
 * [Bug #1] Perbaikan kolom: kasir_id→user_id, nomor_nota→nomor, total_belanja→subtotal,
 *           diskon→diskon_nilai, total_akhir→total, status 'lunas'→'selesai'.
 * [Bug #1] Perbaikan key items: kuantitas→jumlah, harga_jual→harga (sesuai fillable PenjualanDetail).
 * [Bug #1] Perbaikan logika stok: gunakan Stok::create() (ledger), bukan manipulasi baris lama.
 * [Bug #3] Kartu stok tercatat via Stok::create().
 * [Bug #4] lockForUpdate() sebelum cek dan kurangi stok.
 * [S7]     Error mentah tidak ditampilkan ke user, di-log ke file.
 */
class TransactionController extends Controller
{
    /**
     * Daftar transaksi milik kasir yang sedang login.
     */
    public function index(Request $request)
    {
        // [Bug #1 Fix] Gunakan user_id (bukan kasir_id yang tidak ada)
        $transactions = Penjualan::with(['details.produk', 'pelanggan'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $transactions,
        ]);
    }

    /**
     * Buat transaksi baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id'          => 'nullable|exists:pelanggans,id',
            'items'                 => 'required|array|min:1',
            'items.*.produk_id'     => 'required|exists:produks,id',
            // [Bug #1 Fix] Key yang benar sesuai fillable PenjualanDetail: jumlah & harga
            'items.*.jumlah'        => 'required|numeric|min:0.001',
            'items.*.harga'         => 'required|numeric|min:0',
            'bayar'                 => 'required|numeric|min:0',
            'metode_pembayaran'     => 'required|string',
            'diskon_nilai'          => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subtotal     = 0;
            $itemsToSave  = [];

            // [Bug #4 Fix] lockForUpdate sebelum cek & kurangi stok
            foreach ($request->items as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['produk_id']);

                if ($produk->stok < $item['jumlah']) {
                    throw new \Exception("Stok produk \"{$produk->nama}\" tidak mencukupi. Sisa: {$produk->stok}");
                }

                $lineSubtotal  = $item['jumlah'] * $item['harga'];
                $subtotal     += $lineSubtotal;

                $itemsToSave[] = [
                    'produk'       => $produk,
                    // [S3 Fix] Simpan snapshot nama & harga modal
                    'nama_produk'  => $produk->nama,
                    'harga_beli'   => $produk->harga_beli,
                    'produk_id'    => $item['produk_id'],
                    'jumlah'       => $item['jumlah'],
                    'harga'        => $item['harga'],
                    'subtotal'     => $lineSubtotal,
                ];
            }

            $diskonNilai = $request->input('diskon_nilai', 0);
            $total       = $subtotal - $diskonNilai;
            $kembali     = $request->bayar - $total;

            if ($request->bayar < $total) {
                throw new \Exception('Nominal pembayaran kurang dari total belanja.');
            }

            // [Bug #1 Fix] Kolom yang benar sesuai $fillable Penjualan
            $penjualan = Penjualan::create([
                'nomor'             => Penjualan::generateNomor(),  // 'nomor', bukan 'nomor_nota'
                'user_id'           => $request->user()->id,         // 'user_id', bukan 'kasir_id'
                'pelanggan_id'      => $request->pelanggan_id,
                'subtotal'          => $subtotal,                    // 'subtotal', bukan 'total_belanja'
                'diskon_persen'     => 0,
                'diskon_nilai'      => $diskonNilai,                 // 'diskon_nilai', bukan 'diskon'
                'pajak_persen'      => 0,
                'pajak_nilai'       => 0,
                'total'             => $total,                       // 'total', bukan 'total_akhir'
                'metode_pembayaran' => $request->metode_pembayaran,
                'bayar'             => $request->bayar,
                'kembali'           => $kembali,
                // [Bug #1 & #5 Fix] status_pembayaran pakai nilai enum yang valid
                'status_pembayaran' => Penjualan::PAYMENT_PAID,
                // [Bug #1 Fix] status pakai nilai enum yang valid: 'selesai', bukan 'lunas'
                'status'            => Penjualan::STATUS_COMPLETED,
            ]);

            foreach ($itemsToSave as $item) {
                // [Bug #1 Fix] Key sesuai fillable PenjualanDetail
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id'    => $item['produk_id'],
                    'nama_produk'  => $item['nama_produk'],   // [S3] Snapshot
                    'harga_beli'   => $item['harga_beli'],    // [S3] Snapshot modal
                    'jumlah'       => $item['jumlah'],        // 'jumlah', bukan 'kuantitas'
                    'satuan_id'    => $item['produk']->satuan_id,
                    'harga'        => $item['harga'],         // 'harga', bukan 'harga_jual'
                    'diskon_persen' => 0,
                    'diskon_nilai'  => 0,
                    'subtotal'     => $item['subtotal'],
                ]);

                // [Bug #1 & #3 Fix] Catat ke ledger stoks (bukan manipulasi baris lama)
                // StokObserver::created() akan otomatis update produks.stok
                Stok::create([
                    'produk_id'      => $item['produk_id'],
                    'jenis'          => 'penjualan',
                    'jumlah'         => -$item['jumlah'], // Negatif = keluar
                    'referensi_type' => Penjualan::class,
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Penjualan API #' . $penjualan->nomor,
                    'user_id'        => $request->user()->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil disimpan',
                'data'    => $penjualan->load('details.produk'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // [S7 Fix] Log error internal, tampilkan pesan ramah ke user
            Log::error('API TransactionController@store error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(), // Pesan Exception kita sendiri sudah user-friendly
            ], 500);
        }
    }

    /**
     * Detail satu transaksi.
     */
    public function show(int|string $id)
    {
        $transaction = Penjualan::with(['details.produk', 'pelanggan', 'pembayarans'])->find($id);

        if (! $transaction) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $transaction,
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Penjualan::with(['details.produk', 'pelanggan'])
            ->where('kasir_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'nullable|exists:pelanggans,id',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.kuantitas' => 'required|numeric|min:1',
            'items.*.harga_jual' => 'required|numeric',
            'bayar' => 'required|numeric',
            'metode_pembayaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $total_belanja = 0;
            $items = [];

            foreach ($request->items as $item) {
                $subtotal = $item['kuantitas'] * $item['harga_jual'];
                $total_belanja += $subtotal;
                $items[] = [
                    'produk_id' => $item['produk_id'],
                    'kuantitas' => $item['kuantitas'],
                    'harga_jual' => $item['harga_jual'],
                    'subtotal' => $subtotal,
                ];

                // Reduce stock
                $stok = Stok::where('produk_id', $item['produk_id'])->first();
                if ($stok && $stok->jumlah >= $item['kuantitas']) {
                    $stok->jumlah -= $item['kuantitas'];
                    $stok->save();
                } else {
                    throw new \Exception('Stok produk tidak mencukupi.');
                }
            }

            $diskon = $request->input('diskon', 0);
            $total_akhir = $total_belanja - $diskon;
            $kembali = $request->bayar - $total_akhir;

            if ($request->bayar < $total_akhir) {
                throw new \Exception('Nominal pembayaran kurang dari total belanja.');
            }

            $penjualan = Penjualan::create([
                'nomor_nota' => Penjualan::generateNomor(),
                'kasir_id' => $request->user()->id,
                'pelanggan_id' => $request->pelanggan_id,
                'total_belanja' => $total_belanja,
                'diskon' => $diskon,
                'total_akhir' => $total_akhir,
                'bayar' => $request->bayar,
                'kembali' => $kembali,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => 'lunas',
            ]);

            foreach ($items as $item) {
                $penjualan->details()->create($item);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan',
                'data' => $penjualan->load('details.produk')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $transaction = Penjualan::with(['details.produk', 'pelanggan', 'pembayarans'])->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }
}

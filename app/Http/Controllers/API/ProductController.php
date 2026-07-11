<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

class ProductController extends Controller
{
    public function index()
    {
        // Get products with related category and units, stoks
        $products = Produk::with(['kategori_produk', 'satuan', 'stoks' => function($query) {
            $query->where('jumlah', '>', 0);
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Produk::with(['kategori_produk', 'satuan', 'stoks'])->find($id);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriProduk;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = KategoriProduk::all();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function show($id)
    {
        $category = KategoriProduk::find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }
}

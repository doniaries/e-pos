<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use Illuminate\Database\Seeder;

class KategoriProdukSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'MAKANAN',
            'MINUMAN',
            'ALAT TULIS KANTOR',
            'ALAT PANCING',
            'ALAT LISTRIK',
            'ALAT ELEKTRONIK',
            'ALAT RUMAH TANGGA',
            'OBAT-OBATAN',
            'ALAT OLAH RAGA'
        ];

        foreach ($categories as $category) {
            KategoriProduk::create(['nama' => $category]);
        }
    }
}

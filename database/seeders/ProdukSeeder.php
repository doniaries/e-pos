<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan produk lama agar data baru dengan variasi stok muncul
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Produk::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Define realistic Indonesian grocery items with estimated buy prices (harga beli)
        $items = [
            // Sembako
            ['nama' => 'Minyak Goreng Sania 2L', 'kategori' => 'Sembako', 'satuan' => 'Pouch', 'harga_beli_min' => 28000, 'harga_beli_max' => 32000],
            ['nama' => 'Beras Pandan Wangi 5kg', 'kategori' => 'Sembako', 'satuan' => 'Karung', 'harga_beli_min' => 65000, 'harga_beli_max' => 75000],
            ['nama' => 'Gula Pasir Gulaku 1kg', 'kategori' => 'Sembako', 'satuan' => 'Bungkus', 'harga_beli_min' => 15000, 'harga_beli_max' => 17000],
            ['nama' => 'Tepung Terigu Segitiga Biru 1kg', 'kategori' => 'Sembako', 'satuan' => 'Bungkus', 'harga_beli_min' => 11000, 'harga_beli_max' => 13000],
            ['nama' => 'Telur Ayam Negeri 1kg', 'kategori' => 'Sembako', 'satuan' => 'Kg', 'harga_beli_min' => 24000, 'harga_beli_max' => 28000],

            // Mie & Pasta
            ['nama' => 'Indomie Goreng Original', 'kategori' => 'Makanan Instan', 'satuan' => 'Bungkus', 'harga_beli_min' => 2800, 'harga_beli_max' => 3100],
            ['nama' => 'Mie Sedaap Soto', 'kategori' => 'Makanan Instan', 'satuan' => 'Bungkus', 'harga_beli_min' => 2700, 'harga_beli_max' => 3000],

            // Minuman
            ['nama' => 'Kopi Kapal Api Special 165g', 'kategori' => 'Minuman', 'satuan' => 'Bungkus', 'harga_beli_min' => 12000, 'harga_beli_max' => 14000],
            ['nama' => 'Teh Sari Wangi Isi 25', 'kategori' => 'Minuman', 'satuan' => 'Kotak', 'harga_beli_min' => 6000, 'harga_beli_max' => 8000],
            ['nama' => 'Susu Kental Manis Frisian Flag Pouch', 'kategori' => 'Minuman', 'satuan' => 'Pouch', 'harga_beli_min' => 14000, 'harga_beli_max' => 16000],
            ['nama' => 'Aqua Galon 19L', 'kategori' => 'Minuman', 'satuan' => 'Galon', 'harga_beli_min' => 17000, 'harga_beli_max' => 19000],

            // Bumbu
            ['nama' => 'Kecap Bango Manis 550ml', 'kategori' => 'Bumbu Dapur', 'satuan' => 'Botol', 'harga_beli_min' => 21000, 'harga_beli_max' => 24000],
            ['nama' => 'Saos Sambal ABC 135ml', 'kategori' => 'Bumbu Dapur', 'satuan' => 'Botol', 'harga_beli_min' => 7000, 'harga_beli_max' => 9000],
            ['nama' => 'Garam Beryodium Kapal 250g', 'kategori' => 'Bumbu Dapur', 'satuan' => 'Bungkus', 'harga_beli_min' => 2000, 'harga_beli_max' => 3000],
            ['nama' => 'Masako Rasa Ayam Sachet', 'kategori' => 'Bumbu Dapur', 'satuan' => 'Renceng', 'harga_beli_min' => 4500, 'harga_beli_max' => 5500],

            // Perlengkapan Mandi & Cuci
            ['nama' => 'Sabun Lifebuoy Total 10 450ml', 'kategori' => 'Perlengkapan Mandi', 'satuan' => 'Pouch', 'harga_beli_min' => 18000, 'harga_beli_max' => 22000],
            ['nama' => 'Shampoo Sunsilk Hitam 170ml', 'kategori' => 'Perlengkapan Mandi', 'satuan' => 'Botol', 'harga_beli_min' => 19000, 'harga_beli_max' => 23000],
            ['nama' => 'Detergen Rinso Anti Noda 770g', 'kategori' => 'Perlengkapan Cuci', 'satuan' => 'Bungkus', 'harga_beli_min' => 22000, 'harga_beli_max' => 26000],
            ['nama' => 'Sunlight Jeruk Nipis 755ml', 'kategori' => 'Perlengkapan Cuci', 'satuan' => 'Pouch', 'harga_beli_min' => 15000, 'harga_beli_max' => 18000],
            ['nama' => 'Pasta Gigi Pepsodent 190g', 'kategori' => 'Perlengkapan Mandi', 'satuan' => 'Tube', 'harga_beli_min' => 12000, 'harga_beli_max' => 15000],
        ];

        $count = count($items);
        $this->command->info("Menyiapkan {$count} data produk...");
        $created = 0;
        $skipped = 0;

        foreach ($items as $item) {
            // 1. Handle Kategori
            // Model KategoriProduk automatically upper-cases the name via a mutator
            $kategori = KategoriProduk::firstOrCreate([
                'nama' => $item['kategori']
            ]);
            $kategoriId = $kategori->id;

            // 2. Handle Satuan
            $satuan = Satuan::firstOrCreate(
                ['nama' => $item['satuan']]
            );
            $satuanId = $satuan->id;

            // 3. Generate Random Harga
            $hargaBeli = rand($item['harga_beli_min'], $item['harga_beli_max']);

            // Round to nearest 500 for tidiness
            $hargaBeli = round($hargaBeli / 500) * 500;

            // Margin ~15-25% for Sell Price
            $margin = rand(15, 25) / 100;
            $hargaJual = $hargaBeli + ($hargaBeli * $margin);
            $hargaJual = round($hargaJual / 500) * 500; // Rounding

            // Wholesale Price (~5-10% margin, or just slightly cheaper than sell price)
            $hargaGrosir = $hargaJual - ($hargaJual * 0.05); // 5% cheaper than sell price
            $hargaGrosir = round($hargaGrosir / 500) * 500;

            // 4. Generate Random Stock (Weighted: 10% zero, 20% low, 70% normal)
            $randStock = rand(1, 100);
            if ($randStock <= 10) {
                $stok = 0;
            } elseif ($randStock <= 30) {
                $stok = rand(1, 4);
            } else {
                $stok = rand(10, 100);
            }

            // 5. Generate Random Barcode (13 digits)
            // Start with '899' (Indonesia prefix) + 10 random digits
            $kodeProduk = '899' . str_pad((string)rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

            // Generate kode_tambahan (unique)
            $kodeTambahan = 'ADD-' . strtoupper(Str::random(8));

            // 6. Create Produk
            // Check if product already exists to avoid duplicates if run multiple times
            $exists = Produk::where('nama', $item['nama'])->exists();

            if (!$exists) {
                Produk::create([
                    'nama' => $item['nama'],
                    'kategori_produk_id' => $kategoriId,
                    'satuan_id' => $satuanId,
                    'kode_produk' => $kodeProduk,
                    'kode_tambahan' => $kodeTambahan,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'harga_grosir' => $hargaGrosir,
                    'stok' => $stok,
                    // 'gambar_produk' => null,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("Selesai! {$created} produk baru ditambahkan. {$skipped} produk dilewati (sudah ada).");
    }
}

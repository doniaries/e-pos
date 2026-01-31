<?php

namespace Database\Factories;

use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        $kategoriIds = \App\Models\KategoriProduk::pluck('id')->toArray();
        $satuanIds = \App\Models\Satuan::pluck('id')->toArray();

        // Indonesian product categories
        $categories = [
            'Makanan',
            'Minuman',
            'Peralatan',
            'Elektronik',
            'Fashion',
            'Buku',
            'Alat Tulis',
            'Perawatan',
            'Perlengkapan',
            'Olahraga',
            'Hobi',
            'Mainan',
            'Kesehatan',
            'Alat Rumah Tangga'
        ];

        // Generate a random category
        $category = $categories[array_rand($categories)];

        // Generate Indonesian product name based on category
        $nama = match ($category) {
            'Makanan' => $this->faker->randomElement(['Biskuit', 'Keripik', 'Roti', 'Snack', 'Coklat', 'Permen', 'Bubur', 'Sereal']) . ' ' . $this->faker->randomElement(['Enak', 'Lezat', 'Pedas', 'Manis', 'Gurih', 'Kriuk']),
            'Minuman' => $this->faker->randomElement(['Susu', 'Air', 'Teh', 'Kopi', 'Jus', 'Soda']) . ' ' . $this->faker->randomElement(['Segar', 'Natural', 'Dingin', 'Hangat', 'Manis', 'Kuat']),
            'Peralatan' => $this->faker->randomElement(['Penggaris', 'Penghapus', 'Pulpen', 'Pensil', 'Tipex', 'Pengikat']) . ' ' . $this->faker->randomElement(['Standar', 'Premium', 'Eksklusif', 'Besar', 'Kecil']),
            'Elektronik' => $this->faker->randomElement(['Handphone', 'Laptop', 'Kamera', 'Speaker', 'Headset', 'Charger']) . ' ' . $this->faker->randomElement(['Original', 'Gaming', 'Pro', 'Max', 'Mini', 'Ultra']),
            'Fashion' => $this->faker->randomElement(['Baju', 'Celana', 'Jaket', 'Sepatu', 'Topi', 'Sarung']) . ' ' . $this->faker->randomElement(['Kaos', 'Jeans', 'Sport', 'Casual', 'Formal', 'Kerja']),
            'Buku' => $this->faker->randomElement(['Buku', 'Komik', 'Novel', 'Ensiklopedia', 'Atlas', 'Kamus']) . ' ' . $this->faker->randomElement(['Pelajaran', 'Umum', 'Anak', 'Remaja', 'Dewasa', 'Referensi']),
            'Alat Tulis' => $this->faker->randomElement(['Buku', 'Pulpen', 'Pensil', 'Penggaris', 'Tipex', 'Penghapus']) . ' ' . $this->faker->randomElement(['Tulis', 'Gambar', 'Anak', 'Sekolah', 'Kantor', 'Kuliah']),
            default => $this->faker->words(2, true)
        };

        // Generate Indonesian product code based on category
        $kodeAwal = match ($category) {
            'Makanan' => 'MKN',
            'Minuman' => 'MNK',
            'Peralatan' => 'PRL',
            'Elektronik' => 'ELK',
            'Fashion' => 'FSH',
            'Buku' => 'BUK',
            'Alat Tulis' => 'TLT',
            default => 'PRD'
        };

        return [
            'kode_produk' => $this->faker->unique()->regexify($kodeAwal . '[0-9]{5}'),
            'kode_tambahan' => $this->faker->unique()->regexify($kodeAwal . '-[0-9]{3}'),
            'nama' => $nama,
            'kategori_produk_id' => $kategoriIds[array_rand($kategoriIds)],
            'satuan_id' => $satuanIds[array_rand($satuanIds)],
            // Generate prices based on category
            'harga_beli' => match ($category) {
                'Makanan', 'Minuman' => $this->faker->numberBetween(1000, 50000),
                'Alat Tulis', 'Peralatan' => $this->faker->numberBetween(5000, 100000),
                'Fashion' => $this->faker->numberBetween(50000, 500000),
                'Elektronik' => $this->faker->numberBetween(100000, 1000000),
                'Buku' => $this->faker->numberBetween(10000, 150000),
                'Perawatan', 'Kesehatan' => $this->faker->numberBetween(15000, 200000),
                'Olahraga', 'Hobi', 'Mainan' => $this->faker->numberBetween(20000, 300000),
                default => $this->faker->numberBetween(5000, 200000)
            },
            'harga_jual' => match ($category) {
                'Makanan', 'Minuman' => $this->faker->numberBetween(1500, 60000),
                'Alat Tulis', 'Peralatan' => $this->faker->numberBetween(7000, 120000),
                'Fashion' => $this->faker->numberBetween(70000, 600000),
                'Elektronik' => $this->faker->numberBetween(150000, 1200000),
                'Buku' => $this->faker->numberBetween(15000, 180000),
                'Perawatan', 'Kesehatan' => $this->faker->numberBetween(20000, 240000),
                'Olahraga', 'Hobi', 'Mainan' => $this->faker->numberBetween(25000, 350000),
                default => $this->faker->numberBetween(7000, 240000)
            },
            'harga_grosir' => match ($category) {
                'Makanan', 'Minuman' => $this->faker->numberBetween(1200, 45000),
                'Alat Tulis', 'Peralatan' => $this->faker->numberBetween(6000, 90000),
                'Fashion' => $this->faker->numberBetween(60000, 450000),
                'Elektronik' => $this->faker->numberBetween(120000, 900000),
                'Buku' => $this->faker->numberBetween(12000, 140000),
                'Perawatan', 'Kesehatan' => $this->faker->numberBetween(17000, 180000),
                'Olahraga', 'Hobi', 'Mainan' => $this->faker->numberBetween(22000, 280000),
                default => $this->faker->numberBetween(6000, 180000)
            },
            'stok' => $this->faker->numberBetween(0, 500),
            'gambar_produk' => null,
        ];
    }
}

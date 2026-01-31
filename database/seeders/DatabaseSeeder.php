<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            ShiftSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            KategoriProdukSeeder::class,
            SatuanSeeder::class,
            ProdukSeeder::class,
            // StokSeeder::class, // Diganti otomatis via PembelianSeeder + Observer
            DistributorSeeder::class,
            PembelianSeeder::class,
            PenjualanSeeder::class, // 90 transaksi: 50 (2 hari lalu) + 40 (hari ini)
        ]);
    }
}

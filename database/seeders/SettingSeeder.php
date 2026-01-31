<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'nama_perusahaan' => 'Toko Jaya Terus',
            'alamat' => 'Jl. M. Yamin SH',
            'kontak' => '081234567890',
            'pimpinan' => 'Budi Surya',
            'tipe_toko' => 'toko umum',
            'logo' => null, // Biarkan null atau string path seperti 'logos/logo.png'
            'nama_bank' => 'Bank BRI',
            'no_rekening' => '1234-5678-9012',
            'qr_code_image' => null,
        ]);
    }
}

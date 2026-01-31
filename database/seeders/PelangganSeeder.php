<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        // Reset data pelanggan
        Schema::disableForeignKeyConstraints();
        Pelanggan::truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('Membuat data pelanggan...');

        // Create 5 members (with kode_member)
        Pelanggan::create([
            'kode_member' => 'MBR001',
            'nama' => 'Budi Santoso',
            'alamat' => 'Jl. Merdeka No. 123',
            'kontak' => '081234567890',
            'tanggal_bergabung' => Carbon::now()->subMonths(6),
            'hutang' => 0,
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR002',
            'nama' => 'Siti Nurhaliza',
            'alamat' => 'Jl. Sudirman No. 45',
            'kontak' => '081234567891',
            'tanggal_bergabung' => Carbon::now()->subMonths(5),
            'hutang' => 0,
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR003',
            'nama' => 'Ahmad Hidayat',
            'alamat' => 'Jl. Gatot Subroto No. 67',
            'kontak' => '081234567892',
            'tanggal_bergabung' => Carbon::now()->subMonths(3),
            'hutang' => 150000, // Member dengan hutang
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR004',
            'nama' => 'Rina Wati',
            'alamat' => 'Jl. Diponegoro No. 34',
            'kontak' => '081234567893',
            'tanggal_bergabung' => Carbon::now()->subMonths(2),
            'hutang' => 75000, // Member dengan hutang
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR005',
            'nama' => 'Joko Widodo',
            'alamat' => 'Jl. Thamrin No. 56',
            'kontak' => '081234567894',
            'tanggal_bergabung' => Carbon::now()->subMonth(),
            'hutang' => 0,
        ]);

        // Create 3 more members
        Pelanggan::create([
            'kode_member' => 'MBR006',
            'nama' => 'Dewi Permata',
            'alamat' => 'Jl. Ahmad Yani No. 89',
            'kontak' => '081234567895',
            'tanggal_bergabung' => Carbon::now()->subWeeks(3),
            'hutang' => 0,
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR007',
            'nama' => 'Rudi Hartono',
            'alamat' => 'Jl. Gajah Mada No. 12',
            'kontak' => '081234567896',
            'tanggal_bergabung' => Carbon::now()->subWeeks(2),
            'hutang' => 50000, // Member dengan hutang
        ]);

        Pelanggan::create([
            'kode_member' => 'MBR008',
            'nama' => 'Linda Sari',
            'alamat' => 'Jl. Hayam Wuruk No. 78',
            'kontak' => '081234567897',
            'tanggal_bergabung' => Carbon::now()->subWeek(),
            'hutang' => 0,
        ]);

        $this->command->info('✅ Selesai! 8 pelanggan tetap telah dibuat.');
    }
}

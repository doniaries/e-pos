<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distributors = [
            [
                'kode_distributor' => 'DIST001',
                'nama_distributor' => 'PT Sumber Makmur Jaya',
                'nama_perusahaan' => 'PT Sumber Makmur Jaya Abadi',
                'alamat' => 'Jl. Raya Cipinang Jaya No. 45',
                'kota' => 'Jakarta Timur',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '13410',
                'telepon' => '021-85901234',
                'email' => 'info@sumbermakmurjaya.com',
                'kontak_person' => 'Budi Santoso',
                'no_hp' => '081234567890',
                'keterangan' => 'Distributor utama untuk produk makanan dan minuman',
                'status_aktif' => true,
            ],
            [
                'kode_distributor' => 'DIST002',
                'nama_distributor' => 'CV Sejahtera Baru',
                'nama_perusahaan' => 'CV Sejahtera Baru Makmur',
                'alamat' => 'Jl. Pangeran Jayakarta No. 12',
                'kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '10730',
                'telepon' => '021-34567890',
                'email' => 'cs@sejahterabaru.co.id',
                'kontak_person' => 'Andi Wijaya',
                'no_hp' => '082112345678',
                'keterangan' => 'Distributor peralatan elektronik dan gadget',
                'status_aktif' => true,
            ],
            [
                'kode_distributor' => 'DIST003',
                'nama_distributor' => 'UD Sinar Abadi',
                'nama_perusahaan' => 'UD Sinar Abadi Jaya',
                'alamat' => 'Jl. Raya Bekasi Timur No. 89',
                'kota' => 'Bekasi',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '17111',
                'telepon' => '021-88990011',
                'email' => 'admin@sinarabadi.com',
                'kontak_person' => 'Siti Rahayu',
                'no_hp' => '085711223344',
                'keterangan' => 'Distributor peralatan rumah tangga',
                'status_aktif' => true,
            ],
            [
                'kode_distributor' => 'DIST004',
                'nama_distributor' => 'PT Barokah Sejahtera',
                'nama_perusahaan' => 'PT Barokah Sejahtera Mandiri',
                'alamat' => 'Jl. Raya Bogor KM 30',
                'kota' => 'Depok',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '16431',
                'telepon' => '021-77889900',
                'email' => 'info@barokahsejahtera.co.id',
                'kontak_person' => 'Ahmad Fauzi',
                'no_hp' => '081299887766',
                'keterangan' => 'Distributor bahan bangunan',
                'status_aktif' => true,
            ],
            [
                'kode_distributor' => 'DIST005',
                'nama_distributor' => 'CV Mitra Usaha',
                'nama_perusahaan' => 'CV Mitra Usaha Jaya',
                'alamat' => 'Jl. Raya Tangerang No. 56',
                'kota' => 'Tangerang',
                'provinsi' => 'Banten',
                'kode_pos' => '15111',
                'telepon' => '021-55667788',
                'email' => 'mitrausaha@example.com',
                'kontak_person' => 'Dewi Lestari',
                'no_hp' => '085677889900',
                'keterangan' => 'Distributor peralatan kantor',
                'status_aktif' => false,
            ],
        ];

        foreach ($distributors as $distributor) {
            Distributor::create($distributor);
        }

        $this->command->info('Data distributor berhasil ditambahkan!');
    }
}

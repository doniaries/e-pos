<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\Produk;
use App\Models\User;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PembelianSeeder extends Seeder
{
    public function run(): void
    {
        // Reset data pembelian
        Schema::disableForeignKeyConstraints();
        PembelianDetail::truncate();
        Pembelian::truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info("Seeding pembelian data...");

        // Check if required data exists
        if (Distributor::count() === 0) {
            $this->command->error("Pastikan sudah ada data Distributor!");
            return;
        }

        if (Produk::count() === 0) {
            $this->command->error("Pastikan sudah ada data Produk!");
            return;
        }

        if (User::count() === 0) {
            $this->command->error("Pastikan sudah ada data User!");
            return;
        }

        // Get data yang dibutuhkan
        $distributors = Distributor::all();
        $produks = Produk::with('satuan')->get();
        $users = User::all();

        // Tanggal untuk seeding
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();

        $this->command->info('Seeding pembelian kemarin (10 transaksi)...');
        $this->createTransactions($yesterday, 10, $distributors, $produks, $users);

        $this->command->info('Seeding pembelian hari ini (10 transaksi)...');
        $this->createTransactions($today, 10, $distributors, $produks, $users);

        $this->command->info('✅ Selesai! Total 20 transaksi pembelian telah dibuat (10 kemarin, 10 hari ini).');
    }

    private function createTransactions($date, $count, $distributors, $produks, $users)
    {
        for ($i = 0; $i < $count; $i++) {
            // Waktu transaksi: Acak dalam hari ini
            $createdAt = (clone $date)->startOfDay()->addMinutes(rand(0, 1439));
            // Jika hari ini, jangan buat di masa depan
            if ($createdAt->isToday() && $createdAt->isFuture()) {
                $createdAt = now()->subMinutes(rand(0, 60));
            }

            // Generate nomor dengan format: PO/YYYY/MM/DD/####
            $prefix = 'PO';
            $tahun = $createdAt->format('Y');
            $bulan = $createdAt->format('m');
            $tanggal = $createdAt->format('d');

            $lastPO = Pembelian::withTrashed()
                ->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan)
                ->whereDay('created_at', $tanggal)
                ->orderBy('id', 'desc')
                ->first();

            $urutan = $lastPO ? intval(substr($lastPO->nomor_pembelian, -4)) + 1 : 1;
            $nomorPembelian = sprintf("%s/%s/%s/%s/%04d", $prefix, $tahun, $bulan, $tanggal, $urutan);

            // Random distributor dan user
            $distributor = $distributors->random();
            $user = $users->random();

            // Buat pembelian
            $pembelian = Pembelian::create([
                'nomor_pembelian' => $nomorPembelian,
                'distributor_id' => $distributor->id,
                'tanggal_pembelian' => $createdAt->format('Y-m-d'),
                'total_harga' => 0, // akan dihitung dari detail
                'catatan' => rand(1, 100) <= 30 ? 'Pembelian rutin' : null,
                'created_by' => $user->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Buat detail (2-5 item per transaksi)
            $itemCount = rand(2, 5);
            $totalHarga = 0;

            // Get random products without duplicates
            $selectedProduks = $produks->random(min($itemCount, $produks->count()));

            foreach ($selectedProduks as $produk) {
                $jumlah = rand(5, 20); // Pembelian biasanya dalam jumlah lebih besar
                $hargaBeli = $produk->harga_beli;
                $itemTotal = $hargaBeli * $jumlah;
                $totalHarga += $itemTotal;

                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'produk_id' => $produk->id,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'total_harga' => $itemTotal,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // Update pembelian dengan total
            $pembelian->update([
                'total_harga' => $totalHarga,
            ]);
        }
    }
}

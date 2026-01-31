<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Satuan;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;

class PenjualanSeeder extends Seeder
{
    public function run(): void
    {
        // Reset data penjualan
        Schema::disableForeignKeyConstraints();
        PenjualanDetail::truncate();
        Penjualan::truncate();
        Schema::enableForeignKeyConstraints();

        // Get data yang dibutuhkan
        $users = User::all();
        $produks = Produk::with('satuan')->get();

        if ($users->isEmpty() || $produks->isEmpty()) {
            $this->command->error('Pastikan sudah ada data User dan Produk!');
            return;
        }

        // Ensure customers exist (run PelangganSeeder first if needed)
        if (Pelanggan::count() === 0) {
            $this->command->warn('Tidak ada data pelanggan! Jalankan PelangganSeeder terlebih dahulu.');
            $this->command->info('Menjalankan PelangganSeeder...');
            $this->call(PelangganSeeder::class);
        }

        // Ambil semua pelanggan (semuanya dianggap member sekarang)
        $pelanggans = Pelanggan::all();

        // Tanggal untuk seeding
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();

        $this->command->info('Seeding penjualan kemarin (10 transaksi)...');
        $this->createTransactions($yesterday, 10, $users, $produks, $pelanggans);

        $this->command->info('Seeding penjualan hari ini (10 transaksi)...');
        $this->createTransactions($today, 10, $users, $produks, $pelanggans);

        $this->command->info('✅ Selesai! Total 20 transaksi penjualan telah dibuat (10 kemarin, 10 hari ini).');
    }

    private function createTransactions($date, $count, $users, $produks, $pelanggans)
    {
        for ($i = 0; $i < $count; $i++) {
            // Waktu transaksi: Acak dalam hari ini
            $createdAt = (clone $date)->startOfDay()->addMinutes(rand(0, 1439));
            // Jika hari ini, jangan buat di masa depan
            if ($createdAt->isToday() && $createdAt->isFuture()) {
                $createdAt = now()->subMinutes(rand(0, 60));
            }

            // Generate nomor dengan format: INV/YYYY/MM/DD/####
            $prefix = 'INV';
            $tahun = $createdAt->format('Y');
            $bulan = $createdAt->format('m');
            $tanggal = $createdAt->format('d');

            $lastInvoice = Penjualan::withTrashed()
                ->whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan)
                ->whereDay('created_at', $tanggal)
                ->orderBy('id', 'desc')
                ->first();

            $urutan = $lastInvoice ? intval(substr($lastInvoice->nomor, -4)) + 1 : 1;
            $nomor = sprintf("%s/%s/%s/%s/%04d", $prefix, $tahun, $bulan, $tanggal, $urutan);

            // Kasir
            $kasir = $users->random();

            // Distribusi pelanggan: 30% Umum, 70% Pelanggan Tetap (semua pelanggan terdaftar)
            $pelanggan = null;
            $rand = rand(1, 100);

            if ($rand <= 30) {
                // 30% transaksi tanpa pelanggan (Umum)
                $pelanggan = null;
            } else {
                // 70% transaksi dari member (semua pelanggan yang terdaftar)
                if ($pelanggans->isNotEmpty()) {
                    $pelanggan = $pelanggans->random();
                }
            }

            // Metode pembayaran (60% tunai, 30% qris, 10% transfer)
            $rand = rand(1, 100);
            if ($rand <= 60) {
                $metodePembayaran = 'tunai';
                $namaBank = null;
                $nomorRekening = null;
            } elseif ($rand <= 90) {
                $metodePembayaran = 'qris';
                $namaBank = null;
                $nomorRekening = null;
            } else {
                $metodePembayaran = 'transfer';
                $namaBank = collect(['BCA', 'BNI', 'Mandiri', 'BRI'])->random();
                $nomorRekening = '12345678' . rand(10, 99);
            }

            // Buat penjualan
            $penjualan = Penjualan::create([
                'nomor' => $nomor,
                'user_id' => $kasir->id,
                'pelanggan_id' => $pelanggan?->id,
                'subtotal' => 0, // akan dihitung dari detail
                'diskon_persen' => 0,
                'diskon_nilai' => 0,
                'pajak_persen' => 0,
                'pajak_nilai' => 0,
                'total' => 0, // akan dihitung dari detail
                'metode_pembayaran' => $metodePembayaran,
                'nama_bank' => $namaBank,
                'nomor_rekening' => $nomorRekening,
                'bayar' => 0,
                'kembali' => 0,
                'status_pembayaran' => 'lunas',
                'status' => 'selesai',
                'catatan' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Buat detail (2-5 item per transaksi)
            $itemCount = rand(2, 5);
            $subtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $produk = $produks->random();
                $satuan = $produk->satuan;
                $jumlah = rand(1, 5);
                $harga = $produk->harga_jual;
                $itemSubtotal = $harga * $jumlah;
                $subtotal += $itemSubtotal;

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $produk->id,
                    'jumlah' => $jumlah,
                    'satuan_id' => $satuan->id,
                    'harga' => $harga,
                    'diskon_persen' => 0,
                    'diskon_nilai' => 0,
                    'subtotal' => $itemSubtotal,
                    'catatan' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // Update penjualan dengan total
            $total = $subtotal; // no discount/tax for simplicity

            // Tentukan status pembayaran dan bayar
            // 20% kemungkinan ada hutang untuk pelanggan tetap
            $statusPembayaran = 'lunas';
            $bayar = $total;
            $kembali = 0;

            if ($pelanggan && rand(1, 100) <= 20) {
                // 20% transaksi pelanggan tetap berhutang
                $randHutang = rand(1, 100);
                if ($randHutang <= 50) {
                    // 50% dari hutang: tidak bayar sama sekali = HUTANG
                    $statusPembayaran = 'hutang';
                    $bayar = 0;
                    $sisaHutang = $total;
                } else {
                    // 50% dari hutang: bayar sebagian = BAYAR SEBAGIAN
                    $statusPembayaran = 'bayar_sebagian';
                    $bayar = $total * rand(30, 70) / 100; // bayar 30-70%
                    $sisaHutang = $total - $bayar;
                }

                // Update hutang pelanggan
                $pelanggan->increment('hutang', $sisaHutang);
            } else {
                // Transaksi lunas
                $statusPembayaran = 'lunas';
                if ($metodePembayaran == 'tunai') {
                    // Simulasi pembayaran tunai dengan uang pas atau lebih
                    $bayar = $total + (rand(0, 1) ? 0 : (rand(1, 5) * 1000));
                } else {
                    $bayar = $total;
                }
                $kembali = max(0, $bayar - $total);
            }

            $penjualan->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'bayar' => $bayar,
                'kembali' => $kembali,
                'status_pembayaran' => $statusPembayaran,
            ]);
        }
    }
}

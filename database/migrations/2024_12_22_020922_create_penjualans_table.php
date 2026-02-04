<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualansTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique(); // format: INV/tahun/bulan/nomor urut
            $table->foreignId('user_id')->constrained('users'); // kasir

            // Merged from: 2026_01_18_140312_add_shift_id_to_transactions_tables.php
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans');

            // Merged from: 2026_01_16_031140_add_laporan_harian_id_to_penjualans_table.php
            $table->foreignId('laporan_harian_id')->nullable()->constrained('laporan_harians')->nullOnDelete();

            $table->decimal('subtotal', 10, 0); // total sebelum diskon/pajak
            $table->decimal('diskon_persen', 5, 2)->default(0); // diskon dalam persen
            $table->decimal('diskon_nilai', 10, 0)->default(0); // diskon dalam rupiah
            $table->decimal('pajak_persen', 5, 2)->default(0); // PPN jika ada
            $table->decimal('pajak_nilai', 10, 0)->default(0); // nilai PPN
            $table->decimal('total', 10, 0); // total akhir

            // Penggabungan dari tabel pembayaran
            $table->enum('metode_pembayaran', ['tunai', 'transfer'])->default('tunai');
            $table->decimal('bayar', 10, 0)->default(0); // Uang yang diterima
            $table->decimal('kembali', 10, 0)->default(0); // Uang kembalian, jika bayar > total

            // Status pembayaran: 'belum_lunas' (jika pending/utang), 'lunas' (jika selesai)
            $table->enum('status_pembayaran', ['belum_lunas', 'lunas'])->default('lunas');

            // Info Bank / No Rek jika non-tunai
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();

            // Status Transaksi: 'pending' (Hold/Lupa Bawa Uang), 'selesai' (Final), 'batal'
            $table->enum('status', ['pending', 'selesai', 'batal'])->default('selesai');

            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes(); // untuk keamanan data histori


            // Indexes
            $table->index('user_id');
            $table->index('shift_id');
            $table->index('pelanggan_id');
            $table->index('laporan_harian_id');
            $table->index('status_pembayaran');
            $table->index(['created_at', 'id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
}

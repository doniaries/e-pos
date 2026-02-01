<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->dateTime('waktu_tutup');
            $table->foreignId('user_id')->constrained('users');

            // Statistik Transaksi
            $table->integer('jumlah_transaksi')->default(0);
            $table->decimal('total_omset', 15, 0)->default(0);

            // Rincian Pembayaran
            $table->decimal('total_tunai', 15, 0)->default(0);
            $table->decimal('total_nontunai', 15, 0)->default(0); // Transfer, QRIS, dll

            // Rekonsiliasi Kas (Opsional tapi penting untuk Tutup Hari)
            $table->decimal('uang_tunai_di_laci', 15, 0)->default(0); // Input manual pengguna
            $table->decimal('selisih', 15, 0)->default(0); // Tunai Sistem - Tunai Laci

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harians');
    }
};

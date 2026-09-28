<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Bugfix Migration — 2026-09-28
 *
 * Fixes:
 * [S3] Add nama_produk & harga_beli snapshot to penjualan_details
 * [S4] Change jumlah to decimal(10,3) in penjualan_details & stoks
 * [R6] Change metode_pembayaran from enum to string in penjualans
 */
return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // [S3] Snapshot kolom di penjualan_details
        // ----------------------------------------------------------------
        Schema::table('penjualan_details', function (Blueprint $table) {
            // Simpan nama produk saat transaksi agar histori tidak rusak jika produk di-rename/hapus
            $table->string('nama_produk')->nullable()->after('produk_id');
            // Simpan harga beli (modal) untuk laporan laba yang akurat
            $table->decimal('harga_beli', 10, 0)->default(0)->after('nama_produk');
        });

        // ----------------------------------------------------------------
        // [S4] Ubah jumlah menjadi decimal agar bisa jual satuan pecahan (0.5 kg, dll)
        // ----------------------------------------------------------------
        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->decimal('jumlah', 10, 3)->change();
        });

        Schema::table('stoks', function (Blueprint $table) {
            $table->decimal('jumlah', 10, 3)->change();
            $table->decimal('stok_awal', 10, 3)->change();
            $table->decimal('stok_akhir', 10, 3)->change();
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->decimal('stok', 10, 3)->default(0)->change();
            $table->decimal('stok_minimum', 10, 3)->nullable()->change();
            $table->decimal('stok_maksimum', 10, 3)->nullable()->change();
        });

        // ----------------------------------------------------------------
        // [R6] Ubah enum metode_pembayaran menjadi string agar bisa tambah QRIS, e-wallet, dll
        //      tanpa ALTER TABLE di production
        // ----------------------------------------------------------------
        Schema::table('penjualans', function (Blueprint $table) {
            $table->string('metode_pembayaran')->default('tunai')->change();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->dropColumn(['nama_produk', 'harga_beli']);
            $table->integer('jumlah')->change();
        });

        Schema::table('stoks', function (Blueprint $table) {
            $table->integer('jumlah')->change();
            $table->integer('stok_awal')->change();
            $table->integer('stok_akhir')->change();
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->integer('stok')->default(0)->change();
            $table->integer('stok_minimum')->nullable()->change();
            $table->integer('stok_maksimum')->nullable()->change();
        });

        Schema::table('penjualans', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['tunai', 'transfer'])->default('tunai')->change();
        });
    }
};

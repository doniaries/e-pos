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
        Schema::create('stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->enum('jenis', ['pembelian', 'penjualan']);
            $table->nullableMorphs('referensi'); // referensi_id & referensi_type (Penjualan, Pembelian, dll)
            $table->integer('jumlah'); // Positif untuk masuk, Negatif untuk keluar
            $table->integer('harga_beli')->nullable(); // Untuk hitung HPP / Profit saat barang masuk
            $table->integer('stok_awal');
            $table->integer('stok_akhir');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Merged from: 2026_01_18_140312_add_shift_id_to_transactions_tables.php
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['produk_id', 'created_at']);
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};

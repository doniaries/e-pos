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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk')->unique()->index(); //kode barcode
            $table->string('kode_tambahan')->nullable()->unique()->index();
            $table->string('nama')->index();
            $table->foreignId('kategori_produk_id')->constrained('kategori_produks')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuans')->cascadeOnDelete();
            $table->decimal('harga_beli', 10, 0);
            $table->decimal('harga_jual', 10, 0);
            $table->decimal('harga_grosir', 10, 0);
            $table->integer('stok')->default(0);

            // Merged from: 2026_01_15_020445_add_stock_limits_to_produks_table.php
            $table->integer('stok_minimum')->default(0);
            $table->integer('stok_maksimum')->nullable()->comment('Kosong berarti tidak ada batasan');

            $table->string('gambar_produk')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kategori_produk_id');
            $table->index('satuan_id');
            $table->index(['stok', 'id']);
            $table->index(['created_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};

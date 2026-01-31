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
        Schema::create('penjualan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks');
            $table->integer('jumlah');
            $table->foreignId('satuan_id')->constrained('satuans');
            $table->decimal('harga', 10, 0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('diskon_nilai', 10, 0)->default(0);
            $table->decimal('subtotal', 10, 0);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Tambahkan index
            $table->index('penjualan_id');
            $table->index('produk_id');
            $table->index('satuan_id');
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_details');
    }
};

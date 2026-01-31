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
        Schema::create('produk_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuans')->cascadeOnDelete();
            $table->integer('konversi'); // Misal: 1 Lusin = 12 Pcs (konversi = 12)
            $table->decimal('harga_jual_khusus', 15, 2)->nullable(); // Harga khusus untuk satuan ini (opsional)
            $table->timestamps();

            $table->unique(['produk_id', 'satuan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_units');
    }
};

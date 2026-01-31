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
        Schema::table('produks', function (Blueprint $table) {
            // Drop individual unique indexes
            $table->dropUnique(['kode_produk']);
            $table->dropUnique(['kode_tambahan']);

            // Add composite unique index
            // Note: Use a custom name to avoid overflow or conflicts
            $table->unique(['kode_produk', 'kode_tambahan'], 'produks_kode_produk_tambahan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropUnique('produks_kode_produk_tambahan_unique');
            $table->unique('kode_produk');
            $table->unique('kode_tambahan');
        });
    }
};

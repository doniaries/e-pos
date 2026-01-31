<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum values for status_pembayaran to only: bayar_sebagian, hutang, lunas
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status_pembayaran ENUM('bayar_sebagian', 'hutang', 'lunas') DEFAULT 'lunas'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status_pembayaran ENUM('belum_lunas', 'lunas') DEFAULT 'lunas'");
    }
};

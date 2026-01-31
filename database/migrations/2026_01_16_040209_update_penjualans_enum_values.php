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
        // status enum: draft, pending, selesai, batal
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status ENUM('draft', 'pending', 'selesai', 'batal') DEFAULT 'selesai'");

        // status_pembayaran enum: belum_bayar, sebagian, lunas
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status_pembayaran ENUM('belum_bayar', 'sebagian', 'lunas') DEFAULT 'lunas'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status ENUM('pending', 'selesai', 'batal') DEFAULT 'selesai'");
        DB::statement("ALTER TABLE penjualans MODIFY COLUMN status_pembayaran ENUM('belum_lunas', 'lunas') DEFAULT 'lunas'");
    }
};

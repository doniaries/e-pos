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
        Schema::table('settings', function (Blueprint $table) {
            $table->enum('printer_tipe', ['thermal', 'standard'])->default('thermal')->after('logo');
            $table->enum('printer_lebar_kertas', ['58mm', '80mm'])->default('58mm')->after('printer_tipe');
            $table->boolean('printer_auto_cetak')->default(true)->after('printer_lebar_kertas');
            $table->enum('printer_koneksi', ['browser', 'bluetooth', 'usb'])->default('browser')->after('printer_auto_cetak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['printer_tipe', 'printer_lebar_kertas', 'printer_auto_cetak', 'printer_koneksi']);
        });
    }
};

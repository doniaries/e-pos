<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
            $table->string('alamat');
            $table->string('kontak');
            $table->string('pimpinan');
            $table->string('tipe_toko');
            $table->string('logo')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('qr_code_image')->nullable();

            // Merged from: 2026_01_31_203257_add_status_toko_to_settings_table.php
            $table->boolean('is_toko_tutup')->default(false);
            $table->string('pesan_tutup')->default('Toko sedang libur, silakan ubah jika tidak libur.');

            // Merged from: 2026_01_20_105440_add_printer_settings_to_settings_table.php
            $table->enum('printer_tipe', ['thermal', 'standard'])->default('thermal');
            $table->enum('printer_lebar_kertas', ['58mm', '80mm'])->default('58mm');
            $table->boolean('printer_auto_cetak')->default(true);
            $table->string('printer_koneksi')->default('browser'); // Changed from enum to string

            // Merged from: 2026_01_20_105811_add_extra_printer_fields_to_settings_table.php
            $table->string('printer_nama')->nullable();

            // Merged from: 2026_01_27_105425_add_printer_share_name_to_settings_table.php
            $table->string('printer_share_name')->nullable();

            // Merged from: 2026_01_20_131311_add_network_printer_fields_to_settings_table.php
            $table->string('printer_ip_address')->nullable();
            $table->integer('printer_port')->default(9100);

            $table->text('printer_footer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

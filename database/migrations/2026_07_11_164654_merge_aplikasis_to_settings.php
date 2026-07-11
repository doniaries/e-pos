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
        // 1. Tambahkan kolom dari aplikasis ke settings
        Schema::table('settings', function (Blueprint $table) {
            $table->string('nama_aplikasi')->nullable()->after('id');
            $table->string('version')->nullable()->after('nama_aplikasi');
            $table->string('pembuat')->nullable()->after('version');
            $table->string('tahun')->nullable()->after('pembuat');
        });

        // 2. Pindahkan data dari aplikasis ke settings jika ada
        try {
            $aplikasi = \Illuminate\Support\Facades\DB::table('aplikasis')->first();
            if ($aplikasi) {
                \Illuminate\Support\Facades\DB::table('settings')->update([
                    'nama_aplikasi' => $aplikasi->nama_aplikasi,
                    'version' => $aplikasi->version,
                    'pembuat' => $aplikasi->pembuat,
                    'tahun' => $aplikasi->tahun,
                ]);
            }
        } catch (\Exception $e) {
            // Abaikan jika error / tabel belum ada
        }

        // 3. Hapus tabel aplikasis
        Schema::dropIfExists('aplikasis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate aplikasis table
        Schema::create('aplikasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi');
            $table->string('version');
            $table->string('pembuat');
            $table->string('tahun');
            $table->timestamps();
        });

        // Remove columns from settings
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['nama_aplikasi', 'version', 'pembuat', 'tahun']);
        });
    }
};

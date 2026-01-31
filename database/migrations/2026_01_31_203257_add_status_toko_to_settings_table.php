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
            $table->boolean('is_toko_tutup')->default(false)->after('qr_code_image');
            $table->string('pesan_tutup')->default('Toko sedang libur, silakan ubah jika tidak libur.')->after('is_toko_tutup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['is_toko_tutup', 'pesan_tutup']);
        });
    }
};

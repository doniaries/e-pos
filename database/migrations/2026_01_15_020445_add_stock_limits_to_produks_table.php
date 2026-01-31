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
            if (!Schema::hasColumn('produks', 'stok_minimum')) {
                $table->integer('stok_minimum')->default(0)->after('stok');
            }
            if (!Schema::hasColumn('produks', 'stok_maksimum')) {
                $table->integer('stok_maksimum')->nullable()->after('stok_minimum')->comment('Kosong berarti tidak ada batasan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['stok_minimum', 'stok_maksimum']);
        });
    }
};

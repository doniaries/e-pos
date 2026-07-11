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
        $this->addIndexIfNotExists('pelanggans', 'nama');
        $this->addIndexIfNotExists('pelanggans', 'kode_pelanggan');
        $this->addIndexIfNotExists('produks', 'nama');
        $this->addIndexIfNotExists('penjualans', 'nomor');
        $this->addIndexIfNotExists('penjualans', 'tanggal');
        $this->addIndexIfNotExists('pembayarans', 'tanggal');
    }

    private function addIndexIfNotExists(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        } catch (\Exception $e) {
            // Ignore if index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropIndex(['nama']);
            $table->dropIndex(['kode_pelanggan']);
        });

        Schema::table('produks', function (Blueprint $table) {
            $table->dropIndex(['nama']);
        });

        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropIndex(['nomor']);
            $table->dropIndex(['tanggal']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });
    }
};

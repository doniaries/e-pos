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
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();

            // Merged from: 2026_01_17_020048_add_member_fields_to_pelanggans_table.php
            $table->string('kode_member')->nullable()->unique();

            $table->string('nama');
            $table->string('alamat')->nullable();
            $table->string('kontak')->nullable();

            // Merged from: 2026_01_17_020048_add_member_fields_to_pelanggans_table.php
            $table->date('tanggal_bergabung')->nullable()->default(now());

            $table->decimal('hutang', 10, 0)->default(0);
            $table->timestamps();

            // Indexes
            $table->index('nama');
            $table->index('kontak');
            $table->index('kode_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};

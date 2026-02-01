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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pembelian')->unique()->index();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->date('tanggal_pembelian');
            $table->decimal('total_harga', 15, 2)->default(0); // Total akhir
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Merged from: 2026_01_18_140312_add_shift_id_to_transactions_tables.php
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};

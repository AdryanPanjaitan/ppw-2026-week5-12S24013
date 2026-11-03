<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam')->unique();
            $table->foreignId('buku_id')->constrained('bukus')->cascadeOnDelete();
            $table->string('nim_peminjam');
            $table->string('nama_peminjam');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_jatuh_tempo');
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->timestamps();

            $table->index(['nim_peminjam', 'buku_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};

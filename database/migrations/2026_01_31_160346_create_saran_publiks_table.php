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
        if (!Schema::hasTable('saran_publiks')) {
            Schema::create('saran_publiks', function (Blueprint $table) {
                $table->id('id_saran');
                $table->string('nama_pengirim', 100);
                $table->string('email', 100)->nullable();
                $table->string('no_telepon', 20)->nullable();
                $table->enum('kategori_pengirim', ['Alumni', 'Orang Tua', 'Masyarakat Umum', 'Lainnya'])->default('Lainnya');
                $table->foreignId('id_kategori')->nullable()->constrained('kategori', 'id_kategori')->nullOnDelete();
                $table->text('isi_saran');
                $table->enum('status', ['Baru', 'Dibaca', 'Ditindaklanjuti'])->default('Baru');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saran_publiks');
    }
};

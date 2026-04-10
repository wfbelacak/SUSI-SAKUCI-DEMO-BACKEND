<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('input_aspirasi', function (Blueprint $table) {
            $table->enum('status_review', ['pending', 'diterima', 'ditolak'])->default('pending')->after('keterangan');
            $table->timestamp('created_at')->nullable()->useCurrent()->after('status_review');
        });
    }

    public function down(): void
    {
        Schema::table('input_aspirasi', function (Blueprint $table) {
            $table->dropColumn(['status_review', 'created_at']);
        });
    }
};

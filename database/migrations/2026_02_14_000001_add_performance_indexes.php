<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations — add performance indexes.
     */
    public function up(): void
    {
        // Indexes for input_aspirasi table (most queried table)
        Schema::table('input_aspirasi', function (Blueprint $table) {
            // Foreign key lookups
            $table->index('nis', 'idx_input_aspirasi_nis');
            $table->index('id_kategori', 'idx_input_aspirasi_kategori');
            
            // Filter columns
            $table->index('status_review', 'idx_input_aspirasi_status_review');
            $table->index('created_at', 'idx_input_aspirasi_created_at');
            
            // Composite index for review + date filtering
            $table->index(['status_review', 'created_at'], 'idx_input_aspirasi_review_date');
        });

        // Indexes for aspirasi table
        Schema::table('aspirasi', function (Blueprint $table) {
            $table->index('id_pelaporan', 'idx_aspirasi_pelaporan');
            $table->index('id_kategori', 'idx_aspirasi_kategori');
            $table->index('id_admin', 'idx_aspirasi_admin');
            $table->index('status', 'idx_aspirasi_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('input_aspirasi', function (Blueprint $table) {
            $table->dropIndex('idx_input_aspirasi_nis');
            $table->dropIndex('idx_input_aspirasi_kategori');
            $table->dropIndex('idx_input_aspirasi_status_review');
            $table->dropIndex('idx_input_aspirasi_created_at');
            $table->dropIndex('idx_input_aspirasi_review_date');
        });

        Schema::table('aspirasi', function (Blueprint $table) {
            $table->dropIndex('idx_aspirasi_pelaporan');
            $table->dropIndex('idx_aspirasi_kategori');
            $table->dropIndex('idx_aspirasi_admin');
            $table->dropIndex('idx_aspirasi_status');
        });
    }
};

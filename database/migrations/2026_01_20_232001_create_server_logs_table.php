<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel server_logs
 * Tabel ini menyimpan data log server beserta hasil prediksi anomali
 */
return new class extends Migration
{
    /**
     * Menjalankan migration untuk membuat tabel server_logs.
     */
    public function up(): void
    {
        Schema::create('server_logs', function (Blueprint $table) {
            $table->id();
            
            // Informasi request
            $table->string('ip_address', 45)->index(); // IPv4 atau IPv6
            $table->string('method', 10); // GET, POST, PUT, DELETE, dll
            $table->text('url'); // URL endpoint yang diakses
            $table->integer('status_code'); // HTTP status code
            $table->text('user_agent')->nullable(); // Browser/client info
            $table->float('response_time')->default(0); // Waktu response dalam ms
            
            // Hasil prediksi dari ML Service
            $table->enum('prediction_result', ['normal', 'anomaly'])->default('normal');
            $table->float('severity_score')->default(0); // Skor keparahan 0-100
            $table->float('confidence_score')->default(0); // Tingkat kepercayaan 0-1
            
            // Metadata tambahan
            $table->string('request_id')->nullable()->index(); // ID unik untuk tracking
            $table->json('additional_data')->nullable(); // Data tambahan dalam format JSON
            
            $table->timestamps();
            
            // Index untuk query performa
            $table->index(['prediction_result', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Menghapus tabel server_logs.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_logs');
    }
};

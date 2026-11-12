<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('bisnis_unit')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->timestamps();

            // Indeks disesuaikan untuk pencarian berdasarkan event dan nama
            $table->index(['event_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};

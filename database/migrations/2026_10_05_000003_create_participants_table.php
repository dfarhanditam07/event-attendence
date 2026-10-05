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
            $table->string('nik', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('organization')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->timestamps();

            // NIK boleh kosong, tapi kalau diisi harus unik di dalam satu event.
            $table->unique(['event_id', 'nik']);
            $table->index(['event_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};

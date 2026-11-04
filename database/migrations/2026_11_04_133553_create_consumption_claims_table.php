<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumption_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumption_pickup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->timestamp('claimed_at');
            $table->timestamps();

            // Satu peserta hanya boleh diambil konsumsinya SEKALI. Dijaga di level database
            // supaya aman dari race condition (mis. dua transaksi serah-terima bersamaan).
            $table->unique('participant_id');
            $table->index(['event_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumption_claims');
    }
};

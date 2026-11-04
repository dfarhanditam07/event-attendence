<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumption_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            // Nama divisi (snapshot nilai `organization` peserta) saat penyerahan dilakukan,
            // supaya riwayat tetap utuh walau data peserta diubah kemudian.
            $table->string('division');
            $table->string('representative_name');
            $table->string('representative_contact', 32)->nullable();
            // Jumlah porsi yang benar-benar diserahkan (= jumlah peserta hadir yang ditandai).
            $table->unsignedInteger('portions_count');
            $table->foreignId('handed_over_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('picked_up_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'picked_up_at']);
            $table->index(['event_id', 'division']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumption_pickups');
    }
};

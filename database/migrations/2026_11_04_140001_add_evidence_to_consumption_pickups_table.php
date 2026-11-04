<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumption_pickups', function (Blueprint $table) {
            // Nama panitia penyerah saat penyerahan dicatat lewat kiosk publik (tanpa login,
            // jadi tidak ada user id). Pada alur panel, nama ini dikosongkan dan
            // `handed_over_by` yang dipakai.
            $table->string('handed_over_by_name')->nullable();

            // Path foto bukti penyerahan pada disk privat (lihat ConsumptionPickupService::EVIDENCE_DISK).
            $table->string('photo_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('consumption_pickups', function (Blueprint $table) {
            $table->dropColumn(['handed_over_by_name', 'photo_path']);
        });
    }
};

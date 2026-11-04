<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // PIN untuk mengesahkan penyerahan konsumsi lewat kiosk publik (tanpa login).
            // Disimpan sebagai hash (lihat cast 'hashed' pada model Event). Kosong = kiosk nonaktif.
            $table->string('consumption_pin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('consumption_pin');
        });
    }
};

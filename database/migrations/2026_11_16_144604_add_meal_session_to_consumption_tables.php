<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumption_pickups', function (Blueprint $table) {
            // Sesi makan penyerahan ini. Data lama dianggap sebagai makan siang.
            $table->string('meal_session')->default('lunch')->after('division');

            $table->index(['event_id', 'meal_session']);
        });

        Schema::table('consumption_claims', function (Blueprint $table) {
            $table->string('meal_session')->default('lunch')->after('event_id');

            // Aturan lama "satu peserta hanya sekali" diganti menjadi
            // "satu peserta hanya sekali PER SESI", supaya bisa mengambil
            // porsi makan siang dan makan malam.
            $table->dropUnique(['participant_id']);
            $table->unique(['participant_id', 'meal_session']);
        });
    }

    public function down(): void
    {
        Schema::table('consumption_claims', function (Blueprint $table) {
            $table->dropUnique(['participant_id', 'meal_session']);
            $table->unique(['participant_id']);
            $table->dropColumn('meal_session');
        });

        Schema::table('consumption_pickups', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'meal_session']);
            $table->dropColumn('meal_session');
        });
    }
};

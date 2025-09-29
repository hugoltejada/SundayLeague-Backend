<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            // JSON con los días y horas de inicio
            $table->json('default_schedules')->nullable()->after('image_url');
            // duración del partido (en minutos)
            $table->integer('match_duration')->default(60)->after('default_schedules');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['default_schedules', 'match_duration']);
        });
    }
};

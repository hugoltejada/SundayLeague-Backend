<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Seasons table: a club can have many seasons
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->unsignedInteger('season_number'); // sequential number inside the club
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'season_number']);
        });

        // Pivot for player stats per season (no is_active here)
        Schema::create('season_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');

            // Stats similar to club_player but without is_active
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('matches_played')->default(0);

            $table->timestamps();
            $table->unique(['season_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_player');
        Schema::dropIfExists('seasons');
    }
};

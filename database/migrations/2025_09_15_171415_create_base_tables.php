<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Config
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Clubs
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stadium')->nullable();
            $table->string('schedule')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Players
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->string('name');
            $table->integer('age')->nullable();
            $table->string('position')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('role', ['president', 'president_player', 'player', 'player_staff'])->default('player');
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('matches_played')->default(0);
            $table->text('description')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->enum('strong_foot', ['left', 'right', 'both'])->nullable();
            $table->timestamps();
        });

        // Ahora que players existe, añadimos president_id en clubs
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('president_id')->nullable()->constrained('players');
        });

        // Phones
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('device_id')->nullable();
            $table->string('platform')->nullable();
            $table->string('notification_token')->nullable();
            $table->unsignedBigInteger('player_id')->nullable();

            // Solo para registro con email
            $table->string('password')->nullable();
            $table->string('auth_code', 10)->nullable();
            $table->boolean('auth')->default(false);
            $table->timestamp('authorized_at')->nullable();

            // Para Google puedes guardar el "google_id" opcional
            $table->string('google_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Matches
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('players');
            $table->dateTime('match_date');
            $table->string('location')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->timestamps();
        });

        // Match_Player
        Schema::create('match_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->enum('team_side', ['home', 'away']);
            $table->boolean('is_captain')->default(false);
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_player');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('phones');
        Schema::dropIfExists('players');
        Schema::dropIfExists('clubs');
    }
};

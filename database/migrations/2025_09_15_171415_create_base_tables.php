<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Config
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Clubs
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('stadium')->nullable();
            $table->string('location')->nullable();
            $table->string('invitation_code')->unique();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });


        // Phones (usuarios)
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('platform')->nullable();
            $table->string('notification_token')->nullable();

            // Solo para registro con email
            $table->string('password')->nullable();
            $table->string('auth_code', 10)->nullable();
            $table->boolean('auth')->default(false);
            $table->timestamp('authorized_at')->nullable();

            // Para Google
            $table->string('firebase_id')->nullable();

            // Nuevo campo para auth seguro
            $table->string('auth_token', 80)->unique()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Players (relacionados con phones)
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_id')->unique()->constrained('phones')->onDelete('cascade');
            $table->string('name');
            $table->integer('age')->nullable();
            $table->string('position')->nullable();
            $table->string('nationality')->nullable();
            $table->text('description')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->enum('strong_foot', ['left', 'right', 'both'])->nullable();
            $table->timestamps();
        });

        // Supporters (relacionados con phones)
        Schema::create('supporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_id')->unique()->constrained('phones')->onDelete('cascade');
            $table->string('nickname')->nullable();
            $table->text('preferences')->nullable();
            $table->timestamps();
        });

        // Ahora que players existe, añadimos president_id en clubs
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('president_id')->nullable()->constrained('players');
        });

        // Relación muchos a muchos: players - clubs
        Schema::create('club_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->boolean('is_active')->default(false);

            // Estadísticas dentro del club
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('matches_played')->default(0);

            $table->timestamps();
        });


        // Relación muchos a muchos: supporters - clubs
        Schema::create('club_supporter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->foreignId('supporter_id')->constrained('supporters')->onDelete('cascade');
            $table->timestamps();
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

    public function down(): void
    {
        Schema::dropIfExists('match_player');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('club_supporter');
        Schema::dropIfExists('club_player');
        Schema::dropIfExists('supporters');
        Schema::dropIfExists('players');
        Schema::dropIfExists('phones');
        Schema::dropIfExists('clubs');
        Schema::dropIfExists('config');
    }
};

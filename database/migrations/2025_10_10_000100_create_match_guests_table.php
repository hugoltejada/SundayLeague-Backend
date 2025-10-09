<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matches_id')->constrained('matches')->onDelete('cascade');
            $table->string('name');
            $table->enum('team_side', ['home', 'away']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_guests');
    }
};

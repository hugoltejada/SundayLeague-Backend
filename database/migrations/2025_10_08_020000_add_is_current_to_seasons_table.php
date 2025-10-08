<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('end_date');
            $table->index(['club_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropIndex(['club_id', 'is_current']);
            $table->dropColumn('is_current');
        });
    }
};

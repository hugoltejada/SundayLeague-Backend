<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'season_number',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'season_player')
            ->withPivot('goals', 'assists', 'matches_played')
            ->withTimestamps();
    }

    public function matches()
    {
        // Ajusta el nombre del modelo si realmente es Match
        return $this->hasMany(Matches::class);
    }
}

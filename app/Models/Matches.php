<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'season_id',
        'created_by',
        'match_date',
        'location',
        'home_score',
        'away_score',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function creator()
    {
        return $this->belongsTo(Player::class, 'created_by');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'match_player')
            ->withPivot('team_side', 'is_captain', 'goals', 'assists')
            ->withTimestamps();
    }

    public function guests()
    {
        return $this->hasMany(MatchGuest::class, 'matches_id');
    }
}

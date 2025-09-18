<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
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
}

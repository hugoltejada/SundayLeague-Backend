<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'name',
        'age',
        'position',
        'nationality',
        'role',
        'goals',
        'assists',
        'matches_played',
        'description',
        'height',
        'weight',
        'preferred_foot',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function matches()
    {
        return $this->belongsToMany(Match::class, 'match_player')
                    ->withPivot('team_side', 'is_captain', 'goals', 'assists')
                    ->withTimestamps();
    }
}

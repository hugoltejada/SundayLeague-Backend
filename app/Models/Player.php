<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_id',
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
        'strong_foot',
        'avatar',
    ];

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_player')
            ->withTimestamps()
            ->withPivot('is_active');
    }

    public function matches()
    {
        return $this->belongsToMany(Matches::class, 'match_player')
            ->withPivot('team_side', 'is_captain', 'goals', 'assists')
            ->withTimestamps();
    }
}

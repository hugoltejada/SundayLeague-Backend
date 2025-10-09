<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    use HasFactory;

    protected $table = 'match_player';

    protected $fillable = [
        'matches_id',
        'player_id',
        'team_side',
        'is_captain',
        'goals',
        'assists',
    ];

    public function match()
    {
        return $this->belongsTo(Matches::class, 'matches_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}

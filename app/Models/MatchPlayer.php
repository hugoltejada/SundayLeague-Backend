<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    use HasFactory;

    protected $table = 'match_player';

    protected $fillable = [
        'match_id',
        'player_id',
        'team_side',
        'is_captain',
        'goals',
        'assists',
    ];

    public function match()
    {
        return $this->belongsTo(Matches::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}

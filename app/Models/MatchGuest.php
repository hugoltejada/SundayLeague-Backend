<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchGuest extends Model
{
    use HasFactory;

    protected $fillable = [
        'matches_id',
        'name',
        'team_side',
    ];

    public function match()
    {
        return $this->belongsTo(Matches::class);
    }
}

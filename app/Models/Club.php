<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stadium',
        'schedule',
        'description',
        'president_id',
    ];

    public function president()
    {
        return $this->belongsTo(Player::class, 'president_id');
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function matches()
    {
        return $this->hasMany(Match::class);
    }
}

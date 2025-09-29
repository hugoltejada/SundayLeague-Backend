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
        'location',
        'invitation_code',
        'description',
        'president_id',
        'image_url',
        'default_schedules',
        'match_duration',
    ];

    protected $casts = [
        'default_schedules' => 'array',
    ];

    public function president()
    {
        return $this->belongsTo(Player::class, 'president_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'club_player')
            ->withTimestamps()
            ->withPivot('is_active');
    }

    public function supporters()
    {
        return $this->belongsToMany(Supporter::class, 'club_supporter')
            ->withTimestamps();
    }

    public function matches()
    {
        return $this->hasMany(Matches::class);
    }
}

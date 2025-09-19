<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'player_id',
        'name',
        'email',
        'password',
        'device_id',
        'platform',
        'notification_token',
        'auth',
        'auth_code',
        'authorized_at',
        'google_id',
    ];

    protected $casts = [
        'auth' => 'boolean',
        'authorized_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}

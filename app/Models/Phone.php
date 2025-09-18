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
        'device_id',
        'email',
        'phone',
        'platform',
        'notification_token',
        'auth_code',
        'auth',
        'authorized_at',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'platform',
        'notification_token',
        'auth',
        'auth_code',
        'authorized_at',
        'firebase_id',
        'auth_token',
    ];

    protected $casts = [
        'auth' => 'boolean',
        'authorized_at' => 'datetime',
    ];

    public function player()
    {
        return $this->hasOne(Player::class);
    }

    public function supporter()
    {
        return $this->hasOne(Supporter::class);
    }
}

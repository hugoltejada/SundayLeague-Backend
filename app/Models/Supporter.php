<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supporter extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_id',
        'nickname',
        'preferences',
    ];

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_supporter')
            ->withTimestamps();
    }
}

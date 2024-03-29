<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'likeable_id',
        'likeable_type',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function tweet()
    // {
    //     return $this->belongsTo(Tweet::class);
    // }

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function likeable()
    {
        return $this->morphto();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'like_id',
        'tweet_text',
        'tweet_photo',
        'tweet_video',
    ];

    public function tweep()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class, 'reply_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

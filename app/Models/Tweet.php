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
        'slug',
    ];

    protected $with = [
        'tweep', 'images', 'likes', 'replies',
    ];

    public function tweep()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function videos()
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}

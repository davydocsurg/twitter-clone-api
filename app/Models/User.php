<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tweet_id',
        'first_name',
        'last_name',
        'profile_picture',
        'cover_picture',
        'email',
        'handle',
        'bio',
        'website',
        'location',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function getRouteKeyName()
    {
        return 'handle';
    }

    public function like()
    {
        return $this->hasOne(Like::class);
    }
}

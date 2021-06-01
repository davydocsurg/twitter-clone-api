<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    public function tweep()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'videoable_id',
        'videoable_type',
        'url',
    ];

    public function videoable()
    {
        return $this->morphto();
    }
}

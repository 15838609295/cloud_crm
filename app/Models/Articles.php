<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Articles extends Model
{
    protected $fillable = [
        'id', 'title', 'thumb', 'typeid', 'description', 'content', 'is_display'
    ];

}

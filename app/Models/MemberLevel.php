<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberLevel extends Model
{
    protected $table='member_level';

    protected $fillable = [
        'id', 'name', 'discount','created_at', 'updated_at'
    ];

}

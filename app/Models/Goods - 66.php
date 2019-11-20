<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table='goods';

    protected $fillable = [
        'id', 'goods_name', 'goods_type', 'price', 'price_type', 'goods_pic', 'body', 'long', 'status',
        'goods_version','is_del','created_at','updated_at'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class MessageBase extends Model
{
    protected $msg_type_id;

    abstract public function send();

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}

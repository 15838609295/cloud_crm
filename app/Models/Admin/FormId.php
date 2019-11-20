<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormId extends Model
{
    protected $table='form_id';

    public function addData($data){
        $data['create_time'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
    }
}

<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Modular extends Model
{
    protected $table='modular';

    public function getList(){
        $res = DB::table($this->table.' as m')
            ->select('m.*','at.name as label')
            ->leftJoin('articles_type as at','m.type_id','=','at.id')
            ->whereNull('at.deleted_at')
            ->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }
}

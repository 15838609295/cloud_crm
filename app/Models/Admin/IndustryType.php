<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IndustryType extends Model
{
    protected $table='industry_type';

    public function addInfo($data){
        $count = DB::table($this->table)->count();
        if ($count >= 20){
            $res = -1;
            return $res;
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    public function delInfo($id){
        $res = DB::table($this->table)->delete($id);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    public function getList(){
        $res = DB::table($this->table)->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    public function updateInfoId($id,$data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

}

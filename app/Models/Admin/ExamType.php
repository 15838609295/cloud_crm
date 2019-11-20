<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExamType extends Model
{
    protected $table='exam_type';

    //添加分类
    public function addData($data){
        $count = DB::table($this->table)->count();
        //数量限制
        if ($count >= 20){
            $a = -1;
            return $a;
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改分类名称
    public function updateData($id,$data){
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除分类
    public function delData($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }

    //类型列表
    public function getList(){
        $res = DB::table($this->table)->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }































}

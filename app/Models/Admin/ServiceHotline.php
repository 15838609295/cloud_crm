<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceHotline extends Model
{
    protected $table='service_hotline';

    //热线列表
    public function getList($fields){
        $res = DB::table($this->table)->select('*');
        if ($fields['search'] != ''){
            $searchKey = $fields['search'];
            $res->where('user_name','LIKE', '%' . $searchKey . '%')
                ->orwhere('name','LIKE', '%' . $searchKey . '%');
        }
        if ($fields['status'] != ''){
            $res->where('status',$fields['status']);
        }
        $list['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'],$fields['sortOrder'])->get();
        if (!$result){
            return [];
        }
        $list['rows'] = json_decode(json_encode($result),true);
        return $list;
    }

    //添加热线
    public function addData($fields){
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改服务热线
    public function updateInfo($id,$fields){
        $fields['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除服务热线
    public function delInfo($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }
}
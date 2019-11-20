<?php

namespace App\Models\Member;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberAssignLog extends Model
{
    protected $table='member_assign_log';

    //指派列表
    public function assignLogList($id){
        $res = DB::table($this->table)->where('member_id',$id)->select('assign_admin','created_at')->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //添加指派记录
    public function addAssignLog($fields){
        $lod_log = DB::table($this->table)->where('member_id',$fields['member_id'])->orderBy('id','desc')->first();
        $where = [];
        if ($lod_log){
            $lod_log = json_decode(json_encode($lod_log),true);
            $where['assign_name'] = $lod_log['assign_admin'];
            $where['assign_uid'] = $lod_log['assign_touid'];
        }
        $where['member_id'] = $fields['member_id'];
        $where['operation_uid'] = $fields['operation_uid'];
        $where['assign_admin'] = $fields['assign_admin'];
        $where['assign_touid'] = $fields['admin_id'];
        $where['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($where);
        if (!$res){
            return false;
        }else{
            return true;
        }
    }
}

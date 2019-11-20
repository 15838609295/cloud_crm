<?php

namespace App\Models\Member;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberContactLog extends Model
{
    protected $table='member_contact_log';

    //沟通记录列表
    public function getMemberContactList($id){
        $res = DB::table($this->table.' as mcl')
            ->select('mcl.*','au.name as admin_name')
            ->leftJoin('admin_users as au','mcl.admin_user_id','=','au.id')
            ->where('member_id',$id)
            ->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $data['data'] = $res;
        $data['next_time'] = '';
        foreach ($res as $v){
            if ($data['next_time'] == ''){
                $data['next_time'] = $v['comm_time'];
            }else{
                if ($data['next_time'] < $v['comm_time']){
                    $data['next_time'] = $v['comm_time'];
                }
            }
        }
        return $data;
    }

    //添加沟通记录
    public function addContact($fields){
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($fields);
        if (!$res){
            return false;
        }
        return true;
    }
}

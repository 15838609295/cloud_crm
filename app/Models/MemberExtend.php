<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberExtend extends Model
{
    protected $table_name='member_extend';


    public function update_money($id,$data){
        $res = DB::table($this->table_name)->where('member_id','=',$id)->update($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    public function getUserInfo($id){
        $res = DB::table('member_extend as m')
            ->select('me.name','m.avatar','m.position','m.company','m.qq','m.wechat','me.mobile','me.email','m.realname')
            ->leftJoin('member as me','me.id','=','m.member_id')
            ->where('m.member_id','=' ,$id)
            ->first();
        return $res;
    }
}

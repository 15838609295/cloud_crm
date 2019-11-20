<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    protected $table='comment';

    //添加评论
    public function addData($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['status'] = 0;
        $avatar = DB::table('member_extend')->where('member_id',$data['member_id'])->select('avatar')->first();
        $avatar = json_decode(json_encode($avatar),true);
        $data['avatar'] = $avatar['avatar'];
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //审核评论
    public function updateStatus($data){
        $where['status'] = $data['status'];
        $res = DB::table($this->table)->where('id',$data['id'])->update($where);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //评论列表
    public function commemtList($id){
        $res = DB::table($this->table)->where('activity_id',$id)->where('status',1)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //用户评论列表
    public function getMemberList($id){
        $res = DB::table($this->table.' as c')
            ->leftJoin('activity as a','c.activity_id','=','a.id')
            ->select('a.name as activity_name','a.picture','c.*')
            ->where('c.member_id',$id)
            ->where('c.status',1)
            ->orderBy('c.id','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //删除评论
    public function delComment($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }
}

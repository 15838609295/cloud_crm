<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserBranch extends Model
{
    protected $table_name='user_branch';

    /* 获取用户加入的所有团队 */
    public function getUserBranchList($user_id)
    {
        $res = DB::table($this->table_name.' as ub')
            ->select('ub.branch_id','b.branch_name')
            ->leftJoin('branchs as b','b.id','=','ub.branch_id')
            ->where('ub.user_id',$user_id)
            ->orderBy('ub.branch_id','ASC')
            ->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 获取团队所有成员 */
    public function getBranchUserList($fields,$columns)
    {
        $res = DB::table($this->table_name.' as ub')
            ->select($columns)
            ->leftJoin('admin_users as au','au.id','=','ub.user_id')
            ->where('au.status',0)
            ->where('ub.branch_id',$fields['branch_id']);
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    public function isBranchUser($branch_id,$user_id)
    {
        $res = DB::table($this->table_name)->where('branch_id',$branch_id)->where('user_id',$user_id)->first();
        if(!$res){
            return false;
        }
        return true;
    }

    public function userBranchInsert($data)
    {
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function userBranchUpdate($user_id,$data)
    {
        $this->userBranchDelete($user_id);
        $res = $this->userBranchInsert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function userBranchDelete($user_id)
    {
        $res = DB::table($this->table_name)->where('user_id',$user_id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    /* 获取团队所有成员 */
    public function getBranchUserLists($branch_id,$columns)
    {DB::connection()->enableQueryLog();  // 开启QueryLog
        $res = DB::table($this->table_name.' as ub')
            ->select($columns)
            ->leftJoin('admin_users as au','au.id','=','ub.user_id')
            ->where('au.status',0)
            ->whereIn('ub.branch_id',$branch_id);

        $result = $res->get();
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }
}

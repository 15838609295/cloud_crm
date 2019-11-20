<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
    protected $table_name='branchs';

    //通过ID获取团队
    public function getBranchByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //通过名字获取团队
    public function getBranchByName($name,$id=null)
    {
        $res = DB::table($this->table_name)
            ->where('branch_name',$name);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $result = $res->first();
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    //获取团队列表
    public function getBranchList($fields=['*'])
    {
        if(!is_array($fields)){
            return array();
        }
        $res = DB::table($this->table_name)->select($fields)->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    //获取团队列表(带筛选条件)
    public function getBranchWithFilter($fields)
    {
        $searchKey = $fields['searchKey'];
        $res = DB::table($this->table_name)
        ->when($searchKey,function ($query) use ($searchKey){
            $query->where('branch_name', 'LIKE', '%' . $searchKey . '%');
        });
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

    //增加团队
    public function branchInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //通过ID修改团队
    public function branchUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)
            ->where('id',$id)
            ->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //删除团队
    public function branchDelete($id)
    {
        DB::beginTransaction();
        try {
            $del_res = DB::table($this->table_name)->where('id',$id)->delete();
            DB::table('user_branch')->where('branch_id',$id)->delete();
            if(!$del_res){
                DB::rollback();
                return false;
            }
            DB::commit();
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            return false;
        }
        return true;
    }

    /* 添加指定用户团队绑定关系 */
    public function userBranchInsert($data)
    {
        $res = DB::table('user_branch')->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除指定用户团队绑定关系 */
    public function userBranchDelete($id)
    {
        $res = DB::table('user_branch')->where('user_id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

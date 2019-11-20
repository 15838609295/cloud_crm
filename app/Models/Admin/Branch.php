<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
    protected $table='branchs';
    protected $table_name='branchs';

    protected $fillable = [
        'id', 'branch_name', 'created_at', 'updated_at'
    ];

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
    public function getBranchByName($name)
    {
        $res = DB::table($this->table_name)->where('branch_name',$name)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取团队列表
    public function getBranchList()
    {
        $res = DB::table($this->table_name)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取团队列表(带筛选条件)
    public function getBranchWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=""){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('branch_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    //增加团队
    public function branchInsert($data)
    {
        $time = date("Y-m-d H:i:s",time());
        $data["created_at"]=$time;
        $data["updated_at"]=$time;
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //通过ID修改团队
    public function branchUpdate($id,$data)
    {
        $time = date("Y-m-d H:i:s",time());
        $data["updated_at"]=$time;

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
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
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

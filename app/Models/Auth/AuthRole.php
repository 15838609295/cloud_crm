<?php

namespace App\Models\Auth;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuthRole extends Model
{
    protected $table_name = 'admin_role_user';

    public function getUserRole($admin_id)
    {
        $res = DB::table($this->table_name)->where('user_id',$admin_id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res['role_id'];
    }

    public function getRoleList($fields = ['*'])
    {
        $res = DB::table('admin_roles')->select($fields)->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    public function getRoleListWithFilter($fields)
    {
        $res = DB::table('admin_roles');
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('description', 'like', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->get();
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

    public function roleInsert($data,$permissions)
    {
        DB::beginTransaction();
        try {
            $data['created_at'] = Carbon::now();
            $data['updated_at'] = Carbon::now();
            $id = DB::table('admin_roles')->insertGetId($data);
            if(!$id){
                return false;
            }
            $tmp_arr = [];
            foreach ($permissions as $value){
                $tmp_arr[] = ['permission_id'=>$value,'role_id'=>$id];
            }
            $res = DB::table('admin_permission_role')->insert($tmp_arr);
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;

        }
    }

    /*用户角色添加*/
    public function adminRoleInsert($role_id,$user_id)
    {
        $data = $this->getRoleList();
        $role_list = [];
        foreach ($data as $key=>$value){
            $role_list[] = $value['id'];
        }
        if(!in_array($role_id,$role_list,true)){
            return false;
        }
        $res = DB::table('admin_role_user')->insert(['role_id'=>$role_id,'user_id'=>$user_id]);
        if(!$res){
            return false;
        }
        return true;
    }

    /*用户角色修改*/
    public function adminRoleUpdate($role_id,$user_id)
    {
        $data = $this->getRoleList();
        $role_list = [];
        foreach ($data as $key=>$value){
            $role_list[] = $value['id'];
        }
        if(!in_array($role_id,$role_list,true)){
            return false;
        }
        $userRole = $this->getUserRole($user_id);
        if(!$userRole){
            $res = DB::table('admin_role_user')->insert(['role_id'=>$role_id,'user_id'=>$user_id]);
        }else{
            $res = DB::table('admin_role_user')->where('user_id',$user_id)->update(['role_id'=>$role_id]);
        }
        if(!$res){
            return false;
        }
        return true;
    }

    public function roleUpdate($id,$data,$permissions)
    {
        DB::beginTransaction();
        try {
            $data['updated_at'] = Carbon::now();
            $res = DB::table('admin_roles')->where('id',$id)->update($data);
            if(!$res){
                return false;
            }
            DB::table('admin_permission_role')->where('role_id',$id)->delete();
            $tmp_arr = [];
            foreach ($permissions as $value){
                $tmp_arr[] = ['permission_id'=>$value,'role_id'=>$id];
            }
            $res = DB::table('admin_permission_role')->insert($tmp_arr);
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    public function roleDelete($id)
    {
        DB::beginTransaction();
        try {
            $res = DB::table('admin_roles')->where('id',$id)->delete();
            if(!$res){
                return false;
            }
            $del_res = DB::table('admin_permission_role')->where('role_id',$id)->delete();
            if(!$res || !$del_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }
}

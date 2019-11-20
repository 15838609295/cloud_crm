<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    protected $table='admin_roles';
    protected $table_name = 'admin_roles';
    //
    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'admin_permission_role','role_id','permission_id');
    }

    public function users()
    {
        return $this->belongsToMany(AdminUser::class,'admin_role_user','role_id','user_id');
    }

    //给角色添加权限
    public function givePermissionTo($permission)
    {
        return $this->permissions()->save($permission);
    }

    public function getRoleByID($id)
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

    public function getRoleList()
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

    public function getRoleListWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    public function getAdminRoleList($user_id)
    {
        $res = DB::table('admin_role_user')->where('user_id',$user_id)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $result = [];
        foreach ($res as $key=>$value){
            $result[] = $value['role_id'];
        }
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    public function insertAdminRole($role_id,$user_id)
    {
        $role_list = $this->getAdminRoleList($user_id);
        if(is_array($role_list) && in_array($role_id,$role_list)){
            return false;
        }
        $res = DB::table('admin_role_user')->insert(['role_id'=>$role_id[0],'user_id'=>$user_id]);
        if(!$res){
            return false;
        }
        return true;
    }

    public function updateAdminRole($role_list,$user_id)
    {
        $res = $this->getAdminRoleList($user_id);
        if(is_array($res)){
            $res = $this->deleteAdminRole($user_id);
            if(!$res){ return false; }
        }
        $data = [];
        foreach ($role_list as $key=>$value){
            $data[] = ['role_id'=>$value,'user_id'=>$user_id];
        }
        $res = DB::table('admin_role_user')->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function deleteAdminRole($id)
    {
        $res = DB::table('admin_role_user')->where('user_id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    protected $table='admin_permissions';
    protected $table_name='admin_permissions';

    public function roles()
    {
        return $this->belongsToMany(Role::class,'admin_permission_role','permission_id','role_id');
    }

    /* 获取全部权限规则 */
    public function getPermissionList()
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

    /* 通过id获取 */
    public function getPermissionListById($id)
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

    /* 通过cid获取 */
    public function getPermissionListByCid($cid=0)
    {
        $res = DB::table($this->table_name)->where('cid',$cid)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过筛选条件获取权限规则列表 */
    public function getPermissionListWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if(isset($fields['cid'])){
            $res->where('cid',$fields['cid']);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('label', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 删除权限规则 */
    public function permisionRoleDelete($id)
    {
        DB::table('admin_permission_role')->where('permission_id',$id)->delete();
      	$res = DB::table('admin_permissions')->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

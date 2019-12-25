<?php

namespace App\Models\Auth;

use App\Library\Tools;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthPermission extends Model
{
    protected $table_name = 'admin_permission_role';

    public static function getPermissionList($id = null)
    {
//        $res = DB::table('admin_permissions')
        $res = DB::table('permissions')
            ->where('display',0)
            ->select('id','name','label','cid','icon','display','sort','show_mode as showMode');
        if(is_array($id)){
            $res->whereIn('id',$id);
        }
        $result = $res->get();
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    public function rolePermission($role_id = null)
    {
//        $result = DB::table('admin_permissions as ap')
        $result = DB::table('permissions as ap')
            ->select('ap.id','ap.name','ap.label','ap.cid','ap.icon','ap.display','ap.sort')
            ->leftJoin($this->table_name.' as apr','apr.permission_id','=','ap.id');
        if($role_id!=null){
            $result->where('apr.role_id',$role_id);
        }
        $res = $result->orderBy('ap.cid','asc')->get();
        if(!$res){
            return array();
        }

        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 获取角色权限列表 */
    public function getRolePermission($role_id)
    {
        $list = self::getPermissionList();
        $role_permission_list = $this->rolePermission($role_id);
        return $this->iterationTree($list, $role_permission_list, $role_id);
//        $tmp = $cid = [];
//        foreach ($list as $key=>$value){
//            if($value['cid']<1){
//                $tmp[$value['id']] = $value;
//                $tmp[$value['id']]['child'] = [];
//                $cid["id"][] = $value['id'];
//                continue;
//            }
//            $list[$key]['has_power'] = 0;
//            $cid["cid"][] = $value['cid'];
//            foreach ($role_permission_list as $k=>$v){
//                if($value['id']==$v['id'] && $role_id!=null){
//                    $list[$key]['has_power'] = 1;
//                    continue;
//                }
//            }
//        }
//        foreach ($list as $key=>$value){
//            if($value['cid']<1){
//                continue;
//            }
//            $tmp[$value['cid']]['child'][] = $value;
//        }
//        $result=array_diff($cid["id"],$cid["cid"]);
//        if($result)
//            foreach ($result as $id){
//                unset($tmp[$id]);
//            }
//        return array_values($tmp);
    }

    /* 迭代无限极分类 */
    public function iterationTree($list, $role_permission_list, $role_id, $id='id', $pid='cid',$root=0)
    {
        $data = array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(!empty($list)){
                    $child = $this->iterationTree($list, $role_permission_list, $role_id, $id,$pid,$val[$id]);
                    if(!empty($child)){
                        $val['child']=$child;
                    }
                }
                if($root != '0'){
                    $val['has_power'] = 0;
                    foreach ($role_permission_list as $k=>$v){
                        if($val['id']==$v['id'] && $role_id!=null){
                            $val['has_power'] = 1;
                            continue;
                        }
                    }
                    $data[]=$val;
                }else{
                    if(!empty($val["child"]))
                        $data[] = $val;
                }
            }
        }
        return $data;
    }

    public function getUserPermission($user_id)
    {
//        $res = DB::table('admin_permissions as ap')
        $res = DB::table('permissions as ap')
            ->select('ap.id','ap.name','ap.label','ap.cid','ap.icon','ap.display','ap.sort')
            ->leftJoin($this->table_name.' as apr','apr.permission_id','=','ap.id')
            ->leftJoin('admin_role_user as ar','ar.role_id','=','apr.role_id')
            ->where('ar.user_id',$user_id)
            ->orderBy('ap.cid','asc')
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

    /* 通过id获取 */
    public function getPermissionListById($id)
    {
//        $res = DB::table('admin_permissions')->where('id',$id)->first();
        $res = DB::table('permissions')->where('id',$id)->first();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 通过cid获取 */
    public function getPermissionListByCid($cid=0)
    {
//        $res = DB::table('admin_permissions')->where('cid',$cid)->get();
        $res = DB::table('permissions')->where('cid',$cid)->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 通过name获取 */
    public function getPermissionListByName($user_id,$name)
    {
//        $res = DB::table('admin_permissions as ap')
        $res = DB::table('permissions as ap')
            ->select('ap.id','ap.name','ap.label','ap.cid','ap.icon','ap.display','ap.sort')
            ->leftJoin($this->table_name.' as apr','apr.permission_id','=','ap.id')
            ->leftJoin('admin_role_user as ar','ar.role_id','=','apr.role_id')
            ->where('ap.name','LIKE', $name.'%');
        if($user_id>1){
            $res->where('ar.user_id',$user_id);
        }
        $result = $res->orderBy('ap.cid','asc')->get();
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    public static function getPermissionListByFilter($fields)
    {
//        $res = DB::table('admin_permissions')
        $res = DB::table('permissions')
            ->where('display',0);;
        if(isset($fields['cid'])){
            $res->where('cid',$fields['cid']);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('description', 'like', '%' . $searchKey . '%')
                    ->orWhere('label', 'like', '%' . $searchKey . '%');
            });
        }
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

    /* 插入permission */
    public static function permissionInsert($fields)
    {
        $fields['created_at'] = Carbon::now();
        $fields['updated_at'] = Carbon::now();
        $res = DB::table('permissions')->insertGetId($fields);
//        $res = DB::table('admin_permissions')->insertGetId($fields);
        if($res>0){
            return $res;
        }
        return false;
    }

    /* 修改permission */
    public static function permissionUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table('permissions')->where('id',$id)->update($data);
//        $res = DB::table('admin_permissions')->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除权限规则 */
    public function permisionDelete($id)
    {
        //开启事务
        DB::beginTransaction();
        try {
//            $permission_del = DB::table('admin_permissions')->where('id',$id)->delete();
            $permission_del = DB::table('permissions')->where('id',$id)->delete();
            $permission_role_del = DB::table('admin_permission_role')->where('permission_id',$id)->delete();
            if($permission_del){
                DB::commit();
                return true;
            }
            DB::rollback();
            return false;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            return false;
        }
    }
}

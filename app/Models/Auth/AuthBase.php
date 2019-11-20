<?php

namespace App\Models\Auth;

use App\Library\Tools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

class AuthBase extends Model
{
    //protected $table_name='members';
    public static function verifyUserAuth($user_id)
    {
        $authRoleModel = new AuthRole();
        $role_id = $authRoleModel->getUserRole($user_id);
        if(!$role_id){
            return false;
        }
        $path = self::getRequestPath();
        $res = DB::table('admin_permission_role as apr')
            ->leftJoin('permissions as ap','apr.permission_id','=','ap.id')
//            ->leftJoin('admin_permissions as ap','apr.permission_id','=','ap.id')
            ->where('apr.role_id',$role_id)
            ->where('ap.name',$path)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public static function verifyUserPathAuth($user_id,$path)
    {
        $authRoleModel = new AuthRole();
        $role_id = $authRoleModel->getUserRole($user_id);
        if(!$role_id){
            return false;
        }
        $res = DB::table('admin_permission_role as apr')
            ->leftJoin('permissions as ap','apr.permission_id','=','ap.id')
//            ->leftJoin('admin_permissions as ap','apr.permission_id','=','ap.id')
            ->where('apr.role_id',$role_id)
            ->where('ap.name',$path)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public static function getSuMenu()
    {
//        $res = DB::table('admin_permissions')->get();
        $res = DB::table('permissions')->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        $permission_list = AuthPermission::getPermissionList();
        $tmp_arr = [];
        foreach ($permission_list as $key=>$value){
            $tmp_arr[$value['id']] = $value;
        }
        $list = self::getMenuSort($res,$tmp_arr);
        return array_values($list);
    }

    public static function getUserPermission($user_id)
    {
        $authRoleModel = new AuthRole();
        $role_id = $authRoleModel->getUserRole($user_id);
        if(!$role_id){
            return array();
        }
        $res = DB::table('admin_permission_role as apr')
            ->leftJoin('permissions as ap','apr.permission_id','=','ap.id')
//            ->leftJoin('admin_permissions as ap','apr.permission_id','=','ap.id')
            ->where('apr.role_id',$role_id)
            ->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        $permission_list = AuthPermission::getPermissionList();
        $tmp_arr = [];
        foreach ($permission_list as $key=>$value){
            $tmp_arr[$value['id']] = $value;
        }
        $list = self::getMenuSort($res,$tmp_arr);
        return array_values($list);
    }

    public static function getMenuSort($data,$list)
    {
        $tmp = [];
        foreach ($data as $key=>$value){
            if(!isset($tmp[$value['cid']])){
                if($value['cid']==0){
                    continue;
                }
                $tmp[$value['cid']] = $list[$value['cid']];
                $tmp[$value['cid']]['child'] = [$value];
                continue;
            }
            $tmp[$value['cid']]['child'][] = $value;
        }
        return $tmp;
    }

    private function _permissionSort($data)
    {
        $list = Tools::iterationTree($data,'id','cid');
        return $list;
    }

    public static function getRequestPath()
    {
        $path = $_SERVER['REQUEST_URI'];
        if(strpos($path,'?') !== false){
            $tmp_path = explode('?',$path);
            $path = $tmp_path[0];
        }
        $path = str_replace('/','.',$path);
        $path = trim($path,'.');
        return $path;
    }
}

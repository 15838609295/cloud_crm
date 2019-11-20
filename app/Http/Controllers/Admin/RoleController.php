<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Auth\AuthPermission;
use App\Models\Auth\AuthRole;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    protected $fields = [
        'name' => '',
        'description' => '',
        'permissions' => [],
    ];

    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','asc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search',''))                                                //关键词
        );
        $roleModel = new AuthRole();
        $data = $roleModel->getRoleListWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    public function roleList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $columns = ['id','name'];
        $branchModel = new AuthRole();
        $res = $branchModel->getRoleList($columns);
        if(count($res)<1){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '暂无数据';
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 角色权限列表 */
    public function rolePermission(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $role_id = $request->id ? $request->id : null;
        $permissionModel = new AuthPermission();
        $data = $permissionModel->getRolePermission($role_id);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 添加角色 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $tmp_str = trim($request->post('permissions',''));
        $permission_arr = explode(',',$tmp_str);
        $authPermissionModel = new AuthPermission();
        $permission_list = $authPermissionModel->getPermissionList($permission_arr);
        if(count($permission_arr)!=count($permission_list)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '角色权限错误';
            return response()->json($this->returnData);
        }
        $data = array(
            'name' => trim($request->post('name','')),
            'description' => trim($request->post('description','')),
            'admin_power' => 2
        );

        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '角色名称不能包含特殊字符';
            return response()->json($this->returnData);
        }

        $authRoleModel = new AuthRole();
        $res = $authRoleModel->roleInsert($data,$permission_arr);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '角色添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '角色添加成功';
        return response()->json($this->returnData);
    }

	/* 修改角色 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if ($this->AU['id'] != 1){
            $data['code'] = 1;
            $data['msg'] = '无权限';
            $data['data'] = '';
            return response()->json($data);
        }
        $id = trim($request->id);
        if($id==null || !is_numeric($id)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $tmp_str = trim($request->post('permissions',''));
        $permission_arr = explode(',',$tmp_str);
        $authPermissionModel = new AuthPermission();
        $permission_list = $authPermissionModel->getPermissionList($permission_arr);
        if(count($permission_arr)!=count($permission_list)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '角色权限错误';
            return response()->json($this->returnData);
        }
        $data = array(
            'name' => trim($request->post('name','')),
            'description' => trim($request->post('description','')),
            'admin_power' => 2
        );
        $authRoleModel = new AuthRole();
        $res = $authRoleModel->roleUpdate($id,$data,$permission_arr);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '角色修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '角色修改成功';
        return response()->json($this->returnData);
    }

    /* 删除角色 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($id==null || !is_numeric($id)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $authRoleModel = new AuthRole();
        $res = $authRoleModel->roleDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '角色删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '角色删除成功';
        return response()->json($this->returnData);
    }
}

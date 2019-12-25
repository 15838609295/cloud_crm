<?php

namespace App\Http\Controllers\Admin;


use App\Http\Config\ErrorCode;
use App\Models\Auth\AuthPermission;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    protected $fields = [
        'name'        => '',
        'label'       => '',
        'description' => '',
        'cid'         => 0,
        'icon'        => '',
    ];

    /* 权限列表 */
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
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
            'cid' => $request->post('cid',0)
        );
        $data = AuthPermission::getPermissionListByFilter($searchFilter);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //添加权限
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data = [];
        foreach (array_keys($this->fields) as $field) {
            $data[$field] = $request->post($field, $this->fields[$field]);
        }
        $permissionModel = new AuthPermission();
        $res = $permissionModel->permissionInsert($data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            return $this->return_result($this->returnData);
        }
        return $this->return_result($this->returnData);
    }
    
    //修改权限
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = trim($request->id);
        if($id==null || !is_numeric($id)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $permissionModel = new AuthPermission();
        $data = $permissionModel->getPermissionListById((int)$id);
        if(count($data)<1){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '权限记录不存在';
            return $this->return_result($this->returnData);
        }
        foreach (array_keys($this->fields) as $field) {
            $data[$field] = $request->post($field, $this->fields[$field]);
        }
        $res = $permissionModel->permissionUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return $this->return_result($this->returnData);
    }

	//删除权限
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($id==null || !is_numeric($id)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $permission = new AuthPermission();
        $child = $permission->getPermissionListByCid((int)$id);
        if (is_array($child) && count($child)>1) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '请先将该权限的子权限删除后再做删除操作';
            return $this->return_result($this->returnData);
        }
        $res = $permission->permisionDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }
}

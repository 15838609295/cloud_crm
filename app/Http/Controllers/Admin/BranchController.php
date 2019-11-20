<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

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
            'searchKey' => trim($request->post('search',''))                                                //搜索条件
        );
        $branchModel = new Branch();
        $res = $branchModel->getBranchWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    public function branchList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $columns = ['id','branch_name'];
        $branchModel = new Branch();
        $res = $branchModel->getBranchList($columns);
        if(count($res)<1){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '暂无数据';
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 添加团队 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $branch_name = trim($request->post('branch_name',''));
        if($branch_name==''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        //正则验证只能包括汉字字母和数字
        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $branch_name)) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '团队名称不能包含特殊字符';
            return response()->json($this->returnData);
        }
        $branchModel = new Branch();
        $res = $branchModel->getBranchByName($branch_name);
        if(is_array($res)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '团队名称已使用,请重新输入';
            return response()->json($this->returnData);
        }
        $res = $branchModel->branchInsert(['branch_name'=>$branch_name]);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 团队修改页面 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $branch_name = trim($request->post('branch_name',''));
        if($branch_name==''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $branchModel = new Branch();
        $res = $branchModel->getBranchByName($branch_name,$id);
        if(is_array($res)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '团队名称已使用,请重新输入';
            return response()->json($this->returnData);
        }
        $res = $branchModel->branchUpdate($id,['branch_name'=>$branch_name]);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }

    /* 团队删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $branchModel = new Branch();
        $res = $branchModel->branchDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
}
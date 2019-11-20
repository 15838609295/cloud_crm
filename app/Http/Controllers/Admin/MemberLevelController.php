<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Member\MemberLevel;
use Illuminate\Http\Request;

class MemberLevelController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','created_at'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                               //排序（desc，asc）
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
        );
        $memberLevelModel = new MemberLevel();
        $data = $memberLevelModel->getMemberLevelWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberLevel();
        $data = $memberModel->getMemberLevelByID($id);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //等级增加
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name']   = $request->input("name");
        $data['discount']    = $request->input("discount", 0);
        if(empty($data["name"])){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "等级名称不能为空";
            return response()->json($this->returnData);
        }

        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data["name"])) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '等级名称不能包含特殊字符';
            return response()->json($this->returnData);
        }

        $memberLevelModel = new MemberLevel();
        $res = $memberLevelModel->getMemberLevelByName($data['name']);
        if($res){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "名称已存在";
            return response()->json($this->returnData);
        }
        $res = $memberLevelModel->memberLevelInsert($data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "添加失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "添加成功";
        return response()->json($this->returnData);
    }

    //等级更新
    public function update(Request $request, $id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name']   = $request->input("name");
        $data['discount']    = $request->input("discount", 0);
        if(empty($data["name"])){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "等级名称不能为空";
            return response()->json($this->returnData);
        }
        $memberLevelModel = new MemberLevel();
        $res = $memberLevelModel->checkNameRepeat($id,$data['name']);
        if($res){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "名称已存在";
            return response()->json($this->returnData);
        }
        $res = $memberLevelModel->memberLevelUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "更新失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "更新成功";
        return response()->json($this->returnData);
    }

    //等级删除
    public function destroy($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberLevelModel = new MemberLevel();
        $res = $memberLevelModel->memberLevelDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "删除失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "删除成功";
        return response()->json($this->returnData);
    }
}

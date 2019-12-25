<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\MemberSource;
use Illuminate\Http\Request;

class MemberSourceController extends BaseController
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
            'sortName' => $request->post('sortName','order'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                               //排序（desc，asc）
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
        );
        $memberSourceModel = new MemberSource();
        $data = $memberSourceModel->getMemberSourceWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //来源增加
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data["source_name"] = $request->input("source_name");
        $data["order"] = $request->input("order", 0);
        if(empty($data["source_name"])){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "来源名称不能为空";
            return $this->return_result($this->returnData);
        }
        $memberSourceModel = new MemberSource();
        $info = $memberSourceModel->getMemberSourceByName($data["source_name"]);
        if($info){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "名称已存在";
            return $this->return_result($this->returnData);
        }
        $res = $memberSourceModel->memberSourceInsert($data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "添加失败";
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = "添加成功";
        return $this->return_result($this->returnData);
    }

    /* 详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberSourceModel = new MemberSource();
        $data = $memberSourceModel->getMemberSourceByID($id);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //来源更新
    public function update(Request $request, $id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberSourceModel = new MemberSource();
    	$data["source_name"]   = $request->input("source_name");
        $data["order"] = $request->input("order", 0);
        if(empty($data["source_name"])){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "来源名称不能为空";
            return $this->return_result($this->returnData);
        }
        $res = $memberSourceModel->getMemberSourceByName($data["source_name"],$id);
        if($res){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = "名称已存在";
            return $this->return_result($this->returnData);
        }
        $res = $memberSourceModel->memberSourceUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "更新失败";
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = "更新成功";
        return $this->return_result($this->returnData);
    }

    //来源删除
    public function destroy($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberSourceModel = new MemberSource();
        $res = $memberSourceModel->memberSourceDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "删除失败";
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = "删除成功";
        return $this->return_result($this->returnData);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use Illuminate\Http\Request;
use App\Models\Admin\Problem;

class ProblemController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

	public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageno = $request->post("page_no") ? $request->post("page_no") : 1;
        $pagesize = $request->post("page_size") ? $request->post("page_size") : 10;
        $searchFilter = array(
            'sortName' => $request->post("sortName", "created_at"), //排序列名
            'sortOrder' => $request->post("sortOrder", "desc"), //排序（desc，asc）
            'pageNumber' => $pageno, //当前页码
            'pageSize' => $pagesize, //一页显示的条数
            'start' => ($pageno-1) * $pagesize, //开始位置
            'searchKey' => trim($request->post("search",'')) //搜索条件
        );
        $problemModel = new Problem();
        $data = $problemModel->getProblemListWithFilter($this->AU['id'],$searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }
	
	//处理问题
	public function update(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        if(empty($id)){
            $this->returnData = ErrorCode::$admin_enum["params_error"];
            $this->returnData["msg"] = "参数id不存在";
            return response()->json($this->returnData);
        }
        $data['remarks'] = $request->post("remarks", "");
        $data['state'] = 1;
        $problemModel = new Problem();
        $res = $problemModel->problemUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum["error"];
            $this->returnData['msg'] = '处理失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '处理成功';
        return response()->json($this->returnData);
	}
	
	//创建问题
	public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['admin_user_id']=$this->AU['id'];
        $data['problem_doc']=$request->post("problem_doc", "");
        $data['state']=0;
        $problemModel = new Problem();
        $res = $problemModel->problemInsert($data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum["error"];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '处理成功';
        return response()->json($this->returnData);
	}
}
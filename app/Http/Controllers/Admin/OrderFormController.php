<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\OperateLog;
use Illuminate\Http\Request;

class OrderFormController extends BaseController{

    public function __construct(Request $request){
        parent::__construct($request);
    }

    //销售成单率排行
    public function allList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $fields = [
            'branch_id' => $request->post('branch_id',''),
            'company_id' => $request->post('company_id',''),
            'order_name' => $request->post('order_name',''),
        ];
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->orderFormAllList($fields);
        if (!$res){
            $this->returnData['data'] = '';
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //部门成单率排行
    public function companyList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $fields = [
            'order_name' => $request->post('order_name','')
        ];
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->orderFormCompanyList($fields);
        if (!$res){
            $this->returnData['data'] = '';
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //团队成单率排行
    public function branchList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $fields = [
            'order_name' => $request->post('order_name','')
        ];
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->orderFormBranchList($fields);
        if (!$res){
            $this->returnData['data'] = '';
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }
}

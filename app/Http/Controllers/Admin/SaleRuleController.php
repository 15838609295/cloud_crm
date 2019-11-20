<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Salebonusrule;
use Illuminate\Http\Request;

class SaleRuleController extends BaseController
{
    protected $fields = array(
        'rule_name' => '',
        'rule_type' => 0,
        'cost' => 0,
        'pre_bonus' => 0,
        'after_bonus' => 0,
        'after_first_bonus' => 0,
        'bonus' => 0,
        'first_bonus' => 0,
        'type' => 1,
    );

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 规则列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search',''))                                                //搜索关键词
        );
        $salebonusRuleModel = new Salebonusrule();
        $data = $salebonusRuleModel->getSaleBonusRuleListWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 获取全部规则列表 */
    public function saleRuleList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type','');
        $columns = ['id','rule_name'];
        $branchModel = new Salebonusrule();
        $res = $branchModel->getSaleRuleList($type,$columns);
        if(count($res)<1){
            $res = [];
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 获取提成规则详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $saleRuleModel = new Salebonusrule();
        $data = $saleRuleModel->getSaleRuleDetail((int)$id);
        if (!is_array($data)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 规则添加 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $saleRule = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $saleRule[$field] = $request->input($field,$this->fields[$field]);
        }

        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $saleRule['rule_name'])) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '提成名称不能包含特殊字符';
            return response()->json($this->returnData);
        }

        $saleRuleModel = new Salebonusrule();
        $res = $saleRuleModel->saleRuleInsert($saleRule);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 规则编辑 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $saleRule = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $saleRule[$field] = $request->post($field,$this->fields[$field]);
        }
        $saleRuleModel = new Salebonusrule();
        $data = $saleRuleModel->getSaleRuleDetail((int)$id);
        if (!is_array($data)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return response()->json($this->returnData);
        }
        $saleRuleModel = new Salebonusrule();
        $res = $saleRuleModel->saleRuleUpdate((int)$id, $saleRule);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }

    //提成规则状态修改
    public function updateSaleRule(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['status'] = $request->post('status','');
        $saleRuleModel = new Salebonusrule();
        $res = $saleRuleModel->updateStatus($data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    /* 规则删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $saleRuleModel = new Salebonusrule();
        $res = $saleRuleModel->saleRuleDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
}

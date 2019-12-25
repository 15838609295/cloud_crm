<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AfterSale;
use Illuminate\Http\Request;

class AftersaleController extends BaseController
{
    protected $fields = array(
        'after_sale_id' => '',
        'goods_name' => '',
        'goods_money' => 0,
        'order_number' => '',
        'buy_time' => '',
        'buy_length' => 0,
        'member_name' => '',
        'member_phone' => '',
        'sbr_id' => 0,
        'after_type' => 1,
        'after_money' => 0,
        'remarks' => ''
    );

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 售后订单列表 */
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
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time','')),                                          //订单创建时间(开始)
            'end_time' => trim($request->post('end_time','')),                                              //订单创建时间(结束)
            'branch_id' => trim($request->post('branch_id','')),                                            //团队ID
            'user_id' => trim($request->post('user_id','')),                                                //销售ID
            'after_status' => trim($request->post('status','')),                                            //业绩订单状态
            'admin_id' => $this->AU['id'],
            'is_del' => 0
        );
        $afterSaleModel = new AfterSale();
        $list = $afterSaleModel->getAfterSaleList($searchFilter);
        $list['rows'] = $afterSaleModel->buildAfterSaleListFields($list['rows'],trim($request->post('surplus_time','')));
        $this->returnData['data'] = $list;
        return $this->return_result($this->returnData);
    }

    /* 售后订单详情 */
    public function detail(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $afterSaleModel = new AfterSale();
        $data = $afterSaleModel->getAfterSaleOrderByID((int)$id);
        if (!$data){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return $this->return_result($this->returnData);
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 添加售后订单 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $after_sale = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $after_sale[$field] = $request->post($field,$this->fields[$field]);
        }
        $afterSaleModel = new AfterSale();
        $res = $afterSaleModel->afterSaleInsert($after_sale);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return $this->return_result($this->returnData);
    }

    /* 售后修改 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $after_sale = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===NULL){
                continue;
            }
            $after_sale[$field] = $request->post($field,$this->fields[$field]);
        }
        $afterSaleModel = new AfterSale();
        $data = $afterSaleModel->getAfterSaleOrderByID((int)$id);
        if (!$data){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return $this->returnData;
        }
        $afterSaleModel = new AfterSale();
        $res = $afterSaleModel->afterSaleUpdate((int)$id,$after_sale);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->returnData;
        }
        $this->returnData['msg'] = '修改成功';
        return $this->returnData;
    }

    /* 售后订单操作 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['prohibit','open'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $id = $request->id;
        //更新订单状态
        if($request->action == 'prohibit'){
            $afterSaleModel = new AfterSale();
            $flag = $afterSaleModel->afterSaleUpdateByFields($id,['after_status'=>2]);
            if(!$flag){
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '禁用失败';
                return $this->return_result($this->returnData);
            }
            $this->returnData['msg'] = '禁用成功';
            return $this->return_result($this->returnData);
        }elseif ($request->action == 'open'){
            $afterSaleModel = new AfterSale();
            $flag = $afterSaleModel->afterSaleUpdateByFields($id,['after_status'=>0]);
            if(!$flag){
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '开启失败';
                return $this->return_result($this->returnData);
            }
            $this->returnData['msg'] = '开启成功';
            return $this->return_result($this->returnData);
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '未知操作';
        return $this->return_result($this->returnData);
    }

    //售后转移
    public function exchangeOrder(Request $request) {
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $touid = $request->post("exchange_user");
        if($touid==null){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '接受人ID不能为空';
            return $this->return_result($this->returnData);
        }
        $list = $request->post("exchange_list");
        if($list==null || $list=="0"){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '转移订单不能为空';
            return $this->return_result($this->returnData);
        }
        $list = json_decode($list,1);
        foreach ($list as $k=>$v){
            if($v=="0"){
                unset($list[$k]);
            }
        }

        $afterSaleModel = new AfterSale();
        $result = $afterSaleModel->getBaseAfterSaleList(['id'],[['id','in',$list]]);
        if(!is_array($result)){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '无权转移';
            return $this->return_result($this->returnData);
        }
        $data_list = [];
        foreach ($result as $key=>$value){
            $data_list[] = $value['id'];
        }
        $bool = $afterSaleModel->afterSaleUpdateByFields($data_list,['after_sale_id'=>$touid]);
        if(!$bool){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "修改失败";
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = "修改成功";
        return $this->return_result($this->returnData);
    }

    //删除售后订单
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $afterSaleModel = new AfterSale();
        $res = $afterSaleModel->aftersaleDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }
}

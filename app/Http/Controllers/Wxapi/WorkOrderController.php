<?php
namespace App\Http\Controllers\Wxapi;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Findings;
use App\Models\Admin\Questionnaire;
use App\Models\Admin\Street;
use App\Models\Admin\WorkOrder;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends BaseController{

    public function __construct(){
        parent::__construct();
    }
    //街道二级列表
    public function getStreetList(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $streetModel = new Street();
        $res = $streetModel->getStreetListNoPage();
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '信息不存在';
        }else{
            $this->result['data'] = array_values($res);
        }
        return response()->json($this->result);
    }

    //标签列表
    public function feedbackLabel(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
//        $pageNo = request()->post('pageNo',1);
//        $data['pageSize'] = request()->post('pageSize',20);
//        $data['start'] = ($pageNo - 1)*$data['pageSize'];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getFeedbackList('');
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '问题类别未设置，无法提交反馈，请先联系工作人员';
        }else{
            $this->result['data'] = $res;
        }
        return response()->json($this->result);
    }

    //提交工单
    public function workOrderSubmit(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $order_log['street_id'] = request()->post('father_id','');
        $order_log['c_street_id'] = request()->post('son_id','');
        $order_log['description'] = request()->post('description','');
        $order_log['pic_list'] = request()->post('pic_list','');
        $order_log['address'] = request()->post('address','');
        $order_log['type_id'] = request()->post('type_id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->workOrderInsert($order_log,$this->user);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '添加失败';
        }
       return response()->json($this->result);
    }

    //我的反馈
    public function myFeedbackList(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $data['id'] = $this->user['id'];
        $pageNo = request()->post('pageNo',1);
        $data['pageSize'] = request()->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getMyFeedbackList($data);
        if (!$res){
            $this->result['data'] = [];
        }else{
            $this->result['data'] = $res;
        }
        return response()->json($this->result);
    }

    //反馈详情
    public function feedbackInfo(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $id = request()->post('id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getWorkOrderInfo($id,0);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '信息不存在';
        }else{
            if(is_array($res['pic_list'])){
                foreach ($res['pic_list'] as &$v){
                    $v = $this->processingPictures($v);
                }
            }
            if (is_array($res['work_order_log'])){
                foreach ($res['work_order_log'] as &$w_v){
                    if (is_array($w_v['annex'])){
                        foreach ($w_v['annex'] as &$a_v){
                            $a_v = $this->processingPictures($a_v);
                        }
                    }
                }
            }
            $this->result['data'] = $res;
        }
        return response()->json($this->result);
    }

    //补充反馈
    public function supplementWorkOrder(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $data['type'] = 1;
        $data['u_id'] = $this->user['id'];
        $data['remarks'] = request()->input('remarks','');
        $data['annex'] = request()->input('annex','');
        $data['log_id'] = request()->input('log_id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->addWorkOrderLog($data);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '添加失败';
        }
        return response()->json($this->result);
    }

    //确认结单
    public function endWorkOrder(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $id = request()->post('id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->endWorkOrder($id);
        if (!$res){
            $this->result = ErrorCode::$admin_enum['modifyfail'];
        }
        return response()->json($this->result);
    }
}






?>
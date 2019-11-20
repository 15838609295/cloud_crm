<?php

namespace App\Http\Controllers\Api\Member;


use App\Http\Config\ErrorCode;
use App\Models\Admin\WorkOrder;
use App\Models\Member\MemberBase;

class MemberController extends BaseController
{

    //获取客户详情
    public function memberDetail()
    {
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberDetailByID($this->user['id']);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '该客户不存在';
            echo json_encode($this->result);exit;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['status'] = 1;
            $this->result['msg'] = '该客户不存在';
            echo json_encode($this->result);exit;
        }
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }

    //客户账单记录
    public function memberWalletRecord()
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        $type = isset($params['type']) && $params['type']!='' ? $params['type'] : 'wallet';
        $pageno = isset($params['pageNumber']) && $params['pageNumber']!='' ? $params['pageNumber'] : 1;                         //当前页码
        $pagesize = isset($params['pageSize']) && $params['pageSize']!='' ? $params['pageSize'] : 10;                            //一页显示的条数
        $sortName = isset($params['sortName']) && $params['sortName']!='' ? $params['sortName'] : 'id';
        $sortOrder = isset($params['sortOrder']) && $params['sortOrder']!='' ? $params['sortOrder'] : 'desc';
        $searchFilter = array(
            'sortName' => $sortName,                                                                                    //排序列名
            'sortOrder' => $sortOrder,                                                                                  //排序（desc，asc）
            'pageNumber' => $pageno,                                                                                    //当前页码
            'pageSize' => $pagesize,                                                                                    //一页显示的条数
            'start' => ($pageno-1) * $pagesize,                                                                         //开始位置
            'searchKey' => ''                                                                                           //搜索条件
        );
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberRichListByMemberID($this->user['id'],$searchFilter,$type);
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }

    //客户工单
    public function workOrderList($params)
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        $type = isset($params['type']) && $params['type']!='' ? $params['type'] : '';
        $pageno = isset($params['pageNumber']) && $params['pageNumber']!='' ? $params['pageNumber'] : 1;                         //当前页码
        $pagesize = isset($params['pageSize']) && $params['pageSize']!='' ? $params['pageSize'] : 10;                            //一页显示的条数
        $sortName = isset($params['sortName']) && $params['sortName']!='' ? $params['sortName'] : 'id';
        $sortOrder = isset($params['sortOrder']) && $params['sortOrder']!='' ? $params['sortOrder'] : 'desc';
        $searchFilter = array(
            'sortName' => $sortName,                                                                                    //排序列名
            'sortOrder' => $sortOrder,                                                                                  //排序（desc，asc）
            'pageNumber' => $pageno,                                                                                    //当前页码
            'pageSize' => $pagesize,                                                                                    //一页显示的条数
            'start' => ($pageno-1) * $pagesize,                                                                         //开始位置
            'user_id' => $this->user['id'],                                                                             //客户ID
            'type' => $type,                                                                                               //工单类型
            'searchKey' => ''                                                                                           //搜索条件
        );
        $workOrderModel = new WorkOrder();
        $data = $workOrderModel->getWorkOrderListWithFilter($searchFilter);
        $this->result['data'] = $data;
        return $this->result;
    }

    public function workOrderSubmit()
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        if(!isset($params['type']) || !in_array($params['type'],[1,2,3])){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '工单类型(type)参数错误');
        }
        if(!isset($params['content']) || $params['content']==''){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '工单内容不能为空');
        }
        switch ($params['type']){
            case 1:
                $log_id = $this->_buildDemandData($params);
                break;
            case 2:
                $log_id = $this->_buildComplainData($params);
                break;
            case 3:
                $log_id = $this->_buildOtherData($params);
                break;
        }
        if(!$log_id){
            $this->result['status'] = 1;
            $this->result['msg'] = '提交工单失败';
            echo json_encode($this->result);exit;
        }
        $data = array(
            'u_id' => $this->user['id'],
            'log_id' => $log_id,
            'status' => 0,
            'type' => $params['type']
        );
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->workOrderInsert($data);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '提交工单失败';
        }
        echo json_encode($this->result);exit;
    }

    // 构建需求工单
    private function _buildDemandData($data)
    {
        $extend = array(
            'product' => isset($data['options']) ? $data['options'] : '',
            'user_account' => isset($data['user_account']) ? $data['user_account'] : '',
            'user_password' => isset($data['user_password']) ? $data['user_password'] : '',
        );
        $log = array(
            'cid' => 0,
            'admin_id' => null,
            'description' => $data['content'],
            'pic_list' => isset($data['pic_list']) ? $data['pic_list'] : json_encode(array()),
            'extend' => json_encode($extend)
        );
        $workOrderModel = new WorkOrder();
        $res_id = $workOrderModel->workOrderLogInsert($log);
        return $res_id;
    }

    // 构建投诉工单
    private function _buildComplainData($data)
    {
        $extend = array(
            'admin_info' => isset($data['options']) ? $data['options'] : '',
            'user_account' => isset($data['user_account']) ? $data['user_account'] : '',
            'user_password' => isset($data['user_password']) ? $data['user_password'] : '',
        );
        $log = array(
            'cid' => 0,
            'admin_id' => null,
            'description' => $data['content'],
            'pic_list' => isset($data['pic_list']) ? $data['pic_list'] : json_encode(array()),
            'extend' => json_encode($extend)
        );
        $workOrderModel = new WorkOrder();
        $res_id = $workOrderModel->workOrderLogInsert($log);
        return $res_id;
    }

    // 构建其他工单
    private function _buildOtherData($data)
    {
        $extend = array(
            'title' => isset($data['options']) ? $data['options'] : '',
            'user_account' => isset($data['user_account']) ? $data['user_account'] : '',
            'user_password' => isset($data['user_password']) ? $data['user_password'] : '',
        );
        $log = array(
            'cid' => 0,
            'admin_id' => null,
            'description' => $data['content'],
            'pic_list' => isset($data['pic_list']) ? $data['pic_list'] : json_encode(array()),
            'extend' => json_encode($extend)
        );
        $workOrderModel = new WorkOrder();
        $res_id = $workOrderModel->workOrderLogInsert($log);
        return $res_id;
    }

    public function passUpdate(){
        $params = request()->post();
        if(!isset($params['member_id']) || trim($params['member_id']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'member_id');
        }
        if(!isset($params['password']) || trim($params['password']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'password');
        }
        $memberModel = new MemberBase();
        $user = $memberModel->getMemberByID($params["member_id"]);
        $user["password"] = bcrypt($params['password']);
        $bool = $memberModel->memberUpdate($params["member_id"], $user);
        if($bool){
            echo json_encode($this->result);exit;
        }else{
            $this->result['status'] = 1;
            echo json_encode($this->result);exit;
        }
    }
}
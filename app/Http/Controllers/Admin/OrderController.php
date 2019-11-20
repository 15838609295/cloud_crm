<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Achievement;
use App\Models\Admin\WalletLogs;
use App\Models\Member\MemberBase;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /* 订单列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $type = $request->post('type', '');
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time','')),                                          //订单创建时间(开始)
            'end_time' => trim($request->post('end_time','')),                                              //订单创建时间(结束)
            'pay_start_time' => trim($request->post('pay_start_time','')),                                  //支付时间(开始)
            'pay_end_time' => trim($request->post('pay_end_time','')),                                      //支付时间(结束)
            'admin_id' => $this->AU['id'],
            'type' => $type,
            'is_del' => 0
        );
        $orderModel = new Order();
        $data = $orderModel->getOrderListWithFilter($searchFilter);
        foreach ($data['rows'] as &$v){
            $goods_version = json_decode($v['goods_version'],true);
            $v['goods_version'] = $goods_version['subitem']['goods_version'];
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /*
     * 异步修改订单状态
     *
     * pay_status 支付状态 -2:退款完成 -1:申请退款 0:待付款 1:已付款 2:已完成
     * status 订单状态 -2:已取消 -1:申请退款 0:待处理 1:已完成
     */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['confirm','cancel','refund','refuse'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $id = $request->id;
        switch ($request->action)
        {
            case 'confirm':
                $data = $this->_confirmOrder($id);
                break;
            case 'cancel':
                $data = $this->_cancelOrder();
                break;
            case 'refund':
                $data = $this->_confirmRefund($id);
                break;
            case 'refuse':
                $data = $this->_refuseRefund();
                break;
            default:
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '未知操作';
                return response()->json($this->returnData);
        }
        $orderModel = new Order();
        $res = $orderModel->orderUpdate((int)$id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '更新失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '更新成功';
        return response()->json($this->returnData);
    }

    private function _confirmOrder($id){
        $orderModel = new Order();
        $orderData = $orderModel->getOrderByID((int)$id);
        $memberModel = new MemberBase();
        $memberData = $memberModel->getMemberDetailWithFilter(['id'=>$orderData['uid']]);
        if($orderData['flag']==1){
            $fields = array(
                'uid' => $orderData['uid'],
                'type' => 0,
                'operation' => '直接购买增加余额',
                'money' => $orderData['total_price'],
                'wallet' => $memberData['wallet'],
                'remarks' => '订单号：'.$orderData['order_sn'].',直接购买增加充值！',
                'manage' => $this->AU['name']
            );
            $walletLogModel = new WalletLogs();
            $walletLogModel->walletLogInsert($fields);
        }
        return array('status' => 1, 'pay_status' => 2);
    }

    private function _createAchievementRecord($orderData, $memberData){
        $pay_time = date('Y-m-d H:i:s',$orderData['pay_time']);
        $tmp = explode(' ',$pay_time)[1];
        $fields = array(
            'member_name' => $memberData['realname'],
            'member_phone' => $memberData['mobile'],
            'admin_users_id' => $memberData['admin_user_id'],
            'goods_money' => $orderData['price'],
            'order_bonus' => 0,
            'sbr_id' => 0,
            'goods_name' => $orderData['title'],
            'order_number' => $orderData['order_sn'],
            'after_sale_id' => 0,
            'remarks' => '代理商订单',
            'refuse_remarks' => null,
            'sale_proof' => null,
            'status' => 1,
            'buy_time' => date('Y-m-d H:i:s',$orderData['pay_time']),
            'buy_length' => 0,
            'ach_state' => 1
        );
        if($orderData['long']==1){
            $fields['buy_length'] = $orderData['amount'] * 12;
        }else if($orderData['long']==2){
            $fields['buy_length'] = $orderData['amount'];
        }
        $end_time = strtotime('+'.$fields['buy_length'].' month',strtotime($pay_time));
        $end_time = date('Y-m-d',$end_time).' '.$tmp;
        $fields['end_time'] = $end_time;
        $achievementModel = new Achievement();
        $res = $achievementModel->achievementInsert($fields);
        if(!$res){
            Log::info('order to achievement fail:',array('result'=>$res,'data'=>$fields));
        }
    }

    //取消订单
    private function _cancelOrder(){
        return array('status' => -2);
    }

    //确定退款
    private function _confirmRefund($id){
        $orderModel = new Order();
        $orderData = $orderModel->getOrderByID((int)$id);
        if($orderData['pay_status'] == '-1'){
            $log['uid'] = $orderData['uid'];
            $log['money'] = $orderData['total_price'];
            $log['remarks'] = '';
            $log['operation'] = '订单号：'.$orderData['order_sn'].',退款入账！';
            $memberModel = new MemberBase();
            $memberData = $memberModel->getMemberExtendByID((int)$orderData['uid']);
            if($orderData['pay_type'] == 2){//余额
                $log['type'] = 5;
                $log['wallet'] = $memberData['balance'] + $orderData['total_price'];
                $memberModel->memberExtendUpdate($orderData['uid'], ['balance'=>$log['wallet']]);
            }
            if($orderData["pay_type"] == 3){//赠送金
                $log['type'] = 3;
                $log['wallet'] = $memberData['cash_coupon' ] +$orderData['total_price'];
                $memberModel->memberExtendUpdate($orderData['uid'], ['cash_coupon'=>$log["wallet"]]);
            }
            $walletLogModel = new WalletLogs();
            $walletLogModel->walletLogInsert($log);
        }
        return array('status' => 1, 'pay_status' => -2);
    }

    //拒绝退款
    private function _refuseRefund(){
        return array('status' => 1, 'pay_status' => -3);
    }
    
}

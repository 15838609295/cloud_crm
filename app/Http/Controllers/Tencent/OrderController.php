<?php

namespace App\Http\Controllers\Tencent;

use App\Http\Config\ErrorCode;
use App\Models\Goods;
use App\Models\MemberLevel;
use App\Models\Orders;
use App\Models\WalletLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{

	//客户订单
	public function memberOrder()
    {
        $params = request()->post();
        $sortName = 'id';    //排序列名
        $sortOrder = 'desc';   //排序（desc，asc）
        $pageNumber = $params["pageNumber"];  //当前页码
        $pageSize = $params["pageSize"];   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $start_time = isset($params['start_time']) && $params['start_time']!='' ? $params['start_time'] : '';
        $type = isset($params['type']) && $params['type']!='' ? $params['type'] : '';
        $rows = Orders::where('is_del', '=',0)->where("uid","=",$this->user['id']);
        if ($type == '1'){
            $rows->where('type',0);
        }elseif ($type == '2'){
            $rows->where('type',1);
        }elseif ($type == '3'){
            $rows->where('type',2);
        }
        if ($start_time){
            $start_time=date('Y-m-01', strtotime($start_time));
            $endtime = date('Y-m-d', strtotime("$start_time +1 month"));
            $rows->whereBetween('created_at',[$start_time,$endtime]);
        }
        $data['data']['total'] = $rows->count();
        $data['data']['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();

        foreach ($data['data']['rows'] as $v){
            $v->dlprice =  number_format($v->price*$v->discount/100,2);
        }
        $data['status'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
	}

	//客户订单详情
    public function memberOrderDetail()
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        if(!isset($params['order_id']) || trim($params['order_id']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'order_id');
        }
        $this->result['data'] = [];
        $res = Orders::where('id', $params['order_id'])->first();
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '订单不存在';
            echo json_encode($this->result);exit;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['status'] = 1;
            $this->result['msg'] = '订单不存在';
            echo json_encode($this->result);exit;
        }
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }

    /* 提交订单 */
    public function submitOrder()
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        if(!isset($params['goods_id']) || trim($params['goods_id']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_id');
        }
        if(!isset($params['amount']) || trim($params['amount']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'amount');
        }
        if(!isset($params['goods_type_name']) || trim($params['goods_type_name']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_type_name');
        }
        if(!isset($params['pay_type']) || trim($params['pay_type']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'pay_type');
        }
        if(trim($params['goods_type_name'])!='初始版' && (!isset($params['goods_version']) || trim($params['goods_version']) == '')){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_version');
        }
        $goods = Goods::find((int)$params['goods_id']);
        $level = MemberLevel::find((int)$this->user['level']);
        //判断是否有商品规格
        if(trim($params['goods_type_name'])=='初始版'){
            if($goods->price_type == 1){
                $agentPrice = $goods->price;
            }elseif($goods->price_type == 0){
                $agentPrice = round($goods->price*$level->discount/100,2);
            }
            $long = $goods->long;
            $goods_price = $goods->price;  //单价
        }else{
            $tmp_arr = json_decode($params['goods_version'],true);
            foreach($tmp_arr as $k=>$v){
                if($v['goods_version']==trim($params['goods_type_name']) && $goods->price_type == 1){
                    $agentPrice = $v['originalPrice'];
                    $goods_price = $v['originalPrice'];  //单价
                    $long = $v["time_length"];
                }else if($v['goods_version'] == trim($params['goods_type_name']) && $goods->price_type == 0){
                    $agentPrice = round($v['originalPrice'] * $level->discount/100,2);
                    $goods_price = $v['originalPrice'];  //单价
                    $long = $v["time_length"];
                }
            }
        }
        if($goods->price_type == 1){
            $discount = 100;
        }else{
            $discount = $level->discount;
        }
        $order["order_sn"] = $this->getOrderSn();
        $order["title"] = $goods->goods_name.'('.$params['goods_type_name'].')';
        $order["type"] = $goods->goods_type;
        $order["uid"]  = $this->user['id'];
        $order["uname"] = $this->user['name'];
        $order["price"] = $goods_price;  //单价
        $order["amount"] = $params['amount'];
        $order["submitter"] = $this->user['name'];
        $order["total_price"] = round($params['amount']*$agentPrice,2);
        $order["discount"] = $discount;
        $order["long"] = $long;
        //0:余额   1:赠送金
        $order["pay_type"] = $params['pay_type'];
        //入库
        $res_id = Orders::insertGetId($order);
        if(!$res_id){
            $this->result["status"] = 1;
            $this->result["msg"] = "下单失败,稍后重试！";
            return response()->json($this->result);
        }
        $this->payOrder($res_id,$order);
        echo json_encode($this->result);exit;
    }

    /* 扣款 */
    public function payOrder($id,$order)
    {
        if(!is_numeric($order["pay_type"])){
            $this->result["status"] = 1;
            $this->result["msg"] = "参数错误！";
            echo json_encode($this->result);exit;
        }
        //0:余额   1:赠送金
        if($order["pay_type"] == 0){
            //余额支付
            $donation_amount = $this->user['balance'] - $order["total_price"];
            if($donation_amount < 0){
                $this->result["status"] = 1;
                $this->result["msg"] = "余额不足,请选择其他支付方式或联系客服充值！";
                echo json_encode($this->result);exit;
            }
            $log["uid"] = $this->user['id'];
            $log["type"] = 9;
            $log["money"] = '-'.$order["total_price"];
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "";
            $log["operation"] = "余额消费￥".$order["total_price"].",商品名称：".$order["title"].",订单号：".$order['order_sn'];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            DB::table('member_extend')->where('member_id',$this->user['id'])->update(['balance'=>$donation_amount, "update_time" => Carbon::now()]);
            $pay_time = time();
            if($order['long']==0){
                $expire_time =  null;
            }else if($order['long']==1){
                $expire_time =  strtotime('+1 year');
            }else if($order['long']==2){
                $expire_time =  strtotime('+1 month');
            }else{
                $expire_time =  $pay_time + 24 * 60 * 60;
            }
            Orders::where("id","=",$id)->update(array("status"=>0,"pay_status"=>1,"pay_time"=>$pay_time,"pay_type"=>2,'expire_time'=>$expire_time));
            return $this->result;

        }elseif($order["pay_type"] == 1){
            //赠送金支付
            $donation_amount = $this->user['cash_coupon'] - $order["total_price"];
            if($donation_amount < 0){
                $this->result["status"] = 1;
                $this->result["msg"] = "赠送金不足,请选择其他支付方式或联系客服充值！";
                echo json_encode($this->result);exit;
            }
            $log["uid"] = $this->user['id'];
            $log["type"] = 3;
            $log["money"] = '-'.$order["total_price"];
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "";
            $log["operation"] = "赠送金消费￥".$order["total_price"].",商品名称：".$order["title"].",订单号：".$order['order_sn'];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            DB::table('member_extend')->where('member_id',$this->user['id'])->update(['cash_coupon'=>$donation_amount, "update_time" => Carbon::now()]);
            $pay_time = time();
            if($order['long']==0){
                $expire_time =  null;
            }else if($order['long']==1){
                $expire_time =  strtotime('+1 year');
            }else if($order['long']==2){
                $expire_time =  strtotime('+1 month');
            }else{
                $expire_time =  $pay_time + 24 * 60 * 60;
            }
            Orders::where("id","=",$id)->update(array("status"=>0,"pay_status"=>1,"pay_time"=>time(),"pay_type"=>3,'expire_time'=>$expire_time));
            echo json_encode($this->result);exit;
        }
        $this->result["status"] = 1;
        $this->result["msg"] = "参数错误！";
        echo json_encode($this->result);exit;
    }


    /* 取消订单 继续支付 申请退款等操作 */
    public function orderAction()
    {
        $params = request()->post();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        if(!isset($params['id']) || trim($params['id']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'id');
        }
        $id = $params['id'];
        $c = $params['action'];
        //取消订单
        if($c == "cancel"){
            $updata["status"] = "-2";
            $msg = "取消成功";
        }
        //申请退款
        if($c == "refund"){
            $updata["status"] = "0";
            $updata["pay_status"] = "-1";
            $msg = "已提交申请";
        }
        //删除订单
        if($c == "del"){
            $updata["is_del"] = "-1";
            $msg = "删除成功";
        }
        //继续付款
        if($c == "pay"){
            $order = Orders::find((int)$id);
            $data["order_sn"] = $order->order_sn;
            $data["total_price"] = $order->total_price;
            $data["long"] = $order->long;
            $data["title"] = $order->title;
            //0:余额   1:赠送金
            $data["pay_type"] = $params['pay_type'];
            $res = $this->payOrder($id,$data);
            if($res["status"] == 1){
                $this->result=$res;
                echo json_encode($this->result);exit;
            }
            $this->result['msg']='付款成功';
            echo json_encode($this->result);exit;
        }
        $res = Orders::where("id","=",$id)->update($updata);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg']='操作失败';
            echo json_encode($this->result);exit;
        }
        $this->result['msg']=$msg;
        echo json_encode($this->result);exit;
    }

    /**
     * 获取订单号
     * @return json
     */
    private function getOrderSn()
    {
        $order_sn = date("ymdHis").rand(1000,9999);
        $count = Orders::where("order_sn",$order_sn)->count();
        if($count>0){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }
}
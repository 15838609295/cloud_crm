<?php

namespace App\Http\Controllers\Api\Member;

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
        if($this->result['status']>0) {
            echo json_encode($this->result);exit;
        }
        $type = isset($params['type']) && $params['type']!='' ? $params['type'] : 1;
        $pageNumber = isset($params['pageNumber']) && $params['pageNumber']!='' ? $params['pageNumber'] : 1;                     //当前页码
        $pageSize = isset($params['pageSize']) && $params['pageSize']!='' ? $params['pageSize'] : 10;                            //一页显示的条数
        $start = ($pageNumber-1) * $pageSize;                                                                           //开始位置
        $search = isset($params['search']) && $params['search']!='' ? $params['search'] : '';                                    //搜索条件
        $sortName = isset($params['sortName']) && $params['sortName']!='' ? $params['sortName'] : 'id';
        $sortOrder = isset($params['sortOrder']) && $params['sortOrder']!='' ? $params['sortOrder'] : 'desc';
        $rows = Orders::where('is_del', 0)->where('uid',$this->user['id']);
        switch ($type){
            case 1:
                $rows->where('pay_status',0)->where('status',0);
                break;
            case 2:
                $rows->where('status',0)->where('pay_status',1)->orWhere("pay_status", "-1");
                break;
            case 3:
                $rows->where('status',1)->orWhere("pay_status", "-2")->orWhere("pay_status", "-3");
                break;
            case 4:
                $rows->where('expire_time','<',time())->whereNotNull('expire_time');
                break;
            default:
                $rows->where('pay_status',0)->where('status',0);
                break;
        }
        if(trim($search)!=''){
            $rows->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
            });
        }
        $total = $rows;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $res = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();
        if(!$res){
            $this->result['data'] = $data;
            echo json_encode($this->result);exit;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['data'] = $data;
            echo json_encode($this->result);exit;
        }
        foreach ($res as $k=>$v){
            $v['dlprice'] =  number_format($v['price']*$v['discount']/100,2);
            //暂时加的
            $res[$k]["total_price"] = sprintf('%.2f', $v['total_price']/$v['discount']*100);
        }
        $data['rows'] = $res;
        $this->result['data'] = $data;
        echo json_encode($this->result);exit;
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
        if(!isset($params['goods_version']) || trim($params['goods_version']) == ''){  //规格父子数组
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_version');
        }
//        if(!isset($params['goods_type_name']) || trim($params['goods_type_name']) == ''){
//            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_type_name');
//        }
        if(!isset($params['pay_type']) || trim($params['pay_type']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'pay_type');
        }
//        if(trim($params['goods_type_name'])!='初始版' && (!isset($params['goods_version']) || trim($params['goods_version']) == '')){
//            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'goods_version');
//        }
        $goods = Goods::find((int)$params['goods_id']);
        $level = MemberLevel::find((int)$this->user['level']);
        //判断是否有商品规格
//        if(trim($params['goods_type_name'])=='初始版'){
//            if($goods->price_type == 1){
//                $agentPrice = $goods->price;
//            }elseif($goods->price_type == 0){
//                $agentPrice = round($goods->price*$level->discount/100,2);
//            }
//            $long = $goods->long;
//            $goods_price = $goods->price;  //单价
//        }else{
//            $tmp_arr = json_decode($params['goods_version'],true);
//            foreach($tmp_arr as $k=>$v){
//                if($v['goods_version']==trim($params['goods_type_name']) && $goods->price_type == 1){
//                    $agentPrice = $v['originalPrice'];
//                    $goods_price = $v['originalPrice'];  //单价
//                    $long = $v["time_length"];
//                }else if($v['goods_version'] == trim($params['goods_type_name']) && $goods->price_type == 0){
//                    $agentPrice = round($v['originalPrice'] * $level->discount/100,2);
//                    $goods_price = $v['originalPrice'];  //单价
//                    $long = $v["time_length"];
//                }
//            }
//        }
        $goods = json_decode(json_encode($goods),true);
        $params['goods_version'] = json_decode($params['goods_version'],1);
        $goods_version = json_decode($goods['goods_version'],1);
        $version_info = $goods_version[$params['goods_version'][0]]['subitem'][$params['goods_version'][1]];
        $number = $version_info['count'];
        if ($params['amount'] > $number){ //库存不足
            $this->result['status'] = 3;
            $this->result['msg'] = '库存不足';
            echo json_encode($this->result);exit;
        }else{  //库存充足
            $goods_version[$params['goods_version'][0]]['subitem'][$params['goods_version'][1]]['count'] = $number -  $params['amount'];
            $goods['goods_version'] = json_encode($goods_version);
            //修改库存
            $id = $goods['id'];
            unset($goods['id']);
            DB::table('goods')->where('id',$id)->update($goods);
        }
        $goods_price = $version_info['salePrice'];
        $new_goods_version['goods_version'] = $goods_version[$params['goods_version'][0]]['goods_version'];
        $new_goods_version['image'] = $goods_version[$params['goods_version'][0]]['image'];
        $new_goods_version['subitem'] = $version_info;
        if($goods['price_type'] == 1){
            $discount = 100;
        }else{
            if ($level->discount == 0){
                $discount = 100;
            }else{
                $discount = $level->discount;
            }
        }
        if ($goods['goods_type'] != 1){
            $agentPrice = round($goods_price * $level->discount/100,2);
        }else{
            $agentPrice = $goods_price ;
        }

        $long = 1;
        $order["order_sn"] = $this->getOrderSn();
        $order["title"] = $goods['goods_name'].'('.$new_goods_version['goods_version'].')';
        $order["type"] = $goods['goods_type'];
        $order["uid"]  = $this->user['id'];
        $order["goods_id"]  = $params['goods_id'];
        $order["uname"] = $this->user['name'];
        $order["price"] = $goods_price;  //单价
        $order["amount"] = $params['amount'];
        $order["submitter"] = $this->user['name'];
        $order["total_price"] = round($params['amount']*$agentPrice,2);
        $order["discount"] = $discount;
        $order["long"] = $long;
        $order["goods_version"] = json_encode($new_goods_version);
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
                $this->result["status"] = 206;
                $this->result["msg"] = "余额不足,请选择其他支付方式或联系客服充值！";
                //加回库存
                $goods = Goods::find((int)$order['goods_id']);
                $goods = json_decode(json_encode($goods),true);
                $goods_version = json_decode($goods['goods_version'],1);
                $order_version = json_decode($order['goods_version'],1);
                foreach ($goods_version as &$v){
                    if ($v['goods_version'] == $order_version['goods_version']){
                        foreach ($v['subitem'] as &$s){
                            if ($s['goodsNum'] = $order_version['subitem']['goodsNum']){
                                $s['count'] = $s['count'] + $order['amount'];
                            }
                        }
                    }
                }
                $goods['goods_version'] = json_encode($goods_version);
                $goods_id = $goods['id'];
                unset($goods['id']);
                DB::table('goods')->where('id',$goods_id)->update($goods);
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
                $this->result["status"] = 206;
                $this->result["msg"] = "赠送金不足,请选择其他支付方式或联系客服充值！";
                //加回库存
                $goods = Goods::find((int)$order['goods_id']);
                $goods = json_decode(json_encode($goods),true);
                $goods_version = json_decode($goods['goods_version'],1);
                $order_version = json_decode($order['goods_version'],1);
                foreach ($goods_version as &$v){
                    if ($v['goods_version'] == $order_version['goods_version']){
                        foreach ($v['subitem'] as &$s){
                            if ($s['goodsNum'] = $order_version['subitem']['goodsNum']){
                                $s['count'] = $s['count'] + $order['amount'];
                            }
                        }
                    }
                }
                $goods['goods_version'] = json_encode($goods_version);
                $goods_id = $goods['id'];
                unset($goods['id']);
                DB::table('goods')->where('id',$goods_id)->update($goods);
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
            $data["goods_id"] = $order->goods_id;
            $data["total_price"] = $order->total_price;
            $data["long"] = $order->long;
            $data["title"] = $order->title;
            $data["goods_version"] = $order->goods_version;
            $data["amount"] = $order->amount;
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
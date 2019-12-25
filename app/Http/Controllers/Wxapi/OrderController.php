<?php

namespace App\Http\Controllers\Wxapi;

use App\Http\Config\ErrorCode;
use App\Models\Goods;
use App\Models\MemberLevel;
use App\Models\NotifyBase;
use App\Models\Orders;
use App\Models\WalletLogs;
use Carbon\Carbon;
use App\Models\Admin\Configs;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController{

    public function __construct(){
        parent::__construct();
    }
    //客户订单
    public function memberOrder(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
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
                $rows->where('status',0)->where(function ($query){
                    $query->where('pay_status',1)
                        ->orWhere("pay_status", "-1");
                });
                break;
            case 3:
                $rows->where('status',1)->where(function ($query){
                    $query->Where("pay_status", "-2")
                        ->orWhere("pay_status", "-3")
                        ->orwhere("pay_status", "2");
                });
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
            return $this->return_result($this->result);
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['data'] = $data;
            return $this->return_result($this->result);
        }
        foreach ($res as $k=>$v){
            $v['dlprice'] =  number_format($v['price']*$v['discount']/100,2);
            //暂时加的
            $res[$k]["total_price"] = sprintf('%.2f', $v['total_price']/$v['discount']*100);
        }
        $data['rows'] = $res;
        $this->result['data'] = $data;
        return $this->return_result($this->result);
    }

    //客户订单详情
    public function memberOrderDetail(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        if(!isset($params['order_id']) || trim($params['order_id']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'order_id不能为空';
            return $this->return_result($this->result);
        }
        $this->result['data'] = [];
        $res = Orders::where('id', $params['order_id'])->first();
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '订单不存在';
            return $this->return_result($this->result);
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['status'] = 1;
            $this->result['msg'] = '订单不存在';
            return $this->return_result($this->result);
        }
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    /* 提交订单 */
    public function submitOrder(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        if(!isset($params['goods_id']) || trim($params['goods_id']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'goods_id不能为空';
            return $this->return_result($this->result);
        }
        if(!isset($params['amount']) || trim($params['amount']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'amount不能为空';
            return $this->return_result($this->result);
        }
        if(!isset($params['goods_version']) || trim($params['goods_version']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'goods_version不能为空';
            return $this->return_result($this->result);
        }
        if(!isset($params['pay_type']) || trim($params['pay_type']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'pay_type不能为空';
            return $this->return_result($this->result);
        }
        //选中的商品规格
        $params['goods_version'] = explode(',',$params['goods_version']);
        //商品信息
        $goods = Goods::find((int)$params['goods_id']);
        //等级信息
        $goods = json_decode(json_encode($goods),true);
        //全部的商品规格信息
        $goods_version = json_decode($goods['goods_version'],1);
        $version_info = $goods_version[$params['goods_version'][0]]['subitem'][$params['goods_version'][1]];
        //获取选中的商品库存
        $number = $version_info['count'];
        if ($params['amount'] > $number){ //库存不足
            $data['status'] = 1;
            $data['msg'] = '库存不足';
            return $this->return_result($data);
        }else{  //库存充足
            $goods_version[$params['goods_version'][0]]['subitem'][$params['goods_version'][1]]['count'] = $number -  $params['amount'];
            $goods['goods_version'] = json_encode($goods_version);
            //修改库存
            $id = $goods['id'];
            unset($goods['id']);
            DB::table('goods')->where('id',$id)->update($goods);
        }
        //商品单价
        $goods_price = $version_info['salePrice'];
        $new_goods_version['goods_version'] = $goods_version[$params['goods_version'][0]]['goods_version'];
        $new_goods_version['image'] = $goods_version[$params['goods_version'][0]]['image'];
        $new_goods_version['subitem'] = $version_info;

        if($goods['price_type'] == 1){
            $discount = 100;
        }else{
            if ($this->user['discount'] == 0){
                $discount = 100;
            }else{
                $discount = $this->user['discount'];
            }
        }
        //判断商品是否是折扣类型
        if ($goods['goods_type'] != 1){
            $agentPrice = round($goods_price * $discount/100,2);
        }else{
            $agentPrice = $goods_price;
        }
        $order["order_sn"] = $this->getOrderSn();
        $order["title"] = $goods['goods_name'].'('.$new_goods_version['goods_version'].')';
        $order["type"] = $goods['goods_type'];
        $order["goods_id"] = $params['goods_id'];
        $order["uid"]  = $this->user['id'];
        $order["uname"] = $this->user['name'];
        $order["price"] = $goods_price;  //单价
        $order["amount"] = $params['amount'];
        $order["submitter"] = $this->user['name'];
        $order["total_price"] = round($params['amount']*$agentPrice,2);
        $order["discount"] = $discount;
        $order["long"] = 1;
        $order["remarks"] = '购买商品：'.$goods['goods_name'].'('.$new_goods_version['goods_version'].')'.'备注：'.$params['remarks'];
        $order["goods_version"] = json_encode($new_goods_version);
        //0:余额   1:赠送金
        $order["pay_type"] = $params['pay_type'];
        //入库
        $res_id = Orders::insertGetId($order);
        if(!$res_id){
            $this->result["status"] = 1;
            $this->result["msg"] = "下单失败,稍后重试！";
            return $this->return_result($this->result);
        }
        $this->payOrder($res_id,$order,$order["remarks"]);
        return $this->return_result($this->result);
    }

    /* 扣款 */
    public function payOrder($id,$order,$remarks){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        if(!is_numeric($order["pay_type"])){
            $this->result["status"] = 1;
            $this->result["msg"] = "参数错误！";
            return $this->return_result($this->result);
        }
        //0:余额   1:赠送金
        if($order["pay_type"] == 0){
            //余额支付
            $donation_amount = $this->user['balance'] - $order["total_price"];
            if($donation_amount < 0){
                $this->result["status"] = 1;
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
                return $this->return_result($this->result);
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
            //购买成功。通知管理员
            $params = array(
                'type' => 'ach_success',
                'content' => $remarks,
                'receive_wechatid' => '',
            );
            $configModel = new Configs();
            $config = $configModel->getConfigByID(1);
            $notifyModel = new NotifyBase();
            if ($config['qywxLogin'] == 1){
                $notifyModel->sendQYWechat($params);
            }
            $data["status"] = 0;
            $data["msg"] = "购买成功";
            return $this->return_result($data);

        }elseif($order["pay_type"] == 1){
            //赠送金支付
            $donation_amount = $this->user['cash_coupon'] - $order["total_price"];
            if($donation_amount < 0){
                $this->result["status"] = 1;
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
                return $this->return_result($this->result);
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
            //购买成功。通知管理员
            $params = array(
                'type' => 'ach_success',
                'content' => $remarks,
                'receive_wechatid' => '',
            );
            $configModel = new Configs();
            $config = $configModel->getConfigByID(1);
            $notifyModel = new NotifyBase();
            if ($config['qywxLogin'] == 1){
                $notifyModel->sendQYWechat($params);
            }
            return $this->return_result($this->result);
        }
        $this->result["status"] = 1;
        $this->result["msg"] = "参数错误！";
        return $this->return_result($this->result);
    }


    /* 取消订单 继续支付 申请退款等操作 */
    public function orderAction(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        if(!isset($params['id']) || trim($params['id']) == ''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'id不能为空';
            return $this->return_result($this->result);
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
            $res = $this->payOrder($id,$data,$params['remarks']);
            if($res["status"] == 1){
                $this->result=$res;
                return $this->return_result($this->result);
            }
            $this->result['msg']='付款成功';
            return $this->return_result($this->result);
        }
        $res = Orders::where("id","=",$id)->update($updata);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg']='操作失败';
            return $this->return_result($this->result);
        }
        $this->result['msg']=$msg;
        return $this->return_result($this->result);
    }

    /**
     * 获取订单号
     * @return json
     */
    private function getOrderSn(){
        $order_sn = date("ymdHis").rand(1000,9999);
        $count = Orders::where("order_sn",$order_sn)->count();
        if($count>0){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }
}
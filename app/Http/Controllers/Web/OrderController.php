<?php

namespace App\Http\Controllers\Web;

use App\Models\Orders;
use App\Models\Goods;
use App\Models\WalletLogs;
use App\Models\MemberExtend;
use function GuzzleHttp\Psr7\_caseless_remove;
use Illuminate\Http\Request;
use App\Models\Admin\Configs;
//参数
use TencentCloud\Common\Credential;
//配置
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
//代付
use TencentCloud\Partners\V20180321\Models\AgentPayDealsRequest;
use TencentCloud\Partners\V20180321\PartnersClient;
//已审核客户列表
use TencentCloud\Partners\V20180321\Models\DescribeAgentAuditedClientsRequest;
//捕获错误信息
use TencentCloud\Common\Exception\TencentCloudSDKException;
use App\Models\NotifyBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
    }
    /**
     * 列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $sortName = $request->post("sortName",'id');    //排序列名
        $sortOrder = $request->post("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->post("pageNumber");  //当前页码
        $pageSize = $request->post("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $search = $request->post("search",'');  //搜索条件
        $rows = Orders::from('orders as o')
            ->select('o.*','gt.name as goods_type_txt')
            ->leftJoin('goods_type as gt','o.type','=','gt.id')
            ->where('o.is_del', '=',0)
            ->where("o.uid","=",$this->AU);

        if(trim($search)){
            $rows->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
            });
        }
        $data['data']['total'] = $rows->count();
        $data['data']['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();
        foreach ($data['data']['rows'] as $v){
            $v->dlprice =  number_format($v->price*$v->discount/100,2);
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    /**
     * 提交订单
     *
     */
    public function submitOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $id = $request->input("id");
        $amount = $request->input("amount",1);
        $get_version = $request->input("goods_version",'');
        $remarks = $request->input("remarks",'');
        if (!$get_version){
            $data['code'] = 1;
            $data['msg'] = '请选择规格';
            return response()->json($data);
        }
        //商品规格
        $get_version = explode(',',$get_version);
        $goods = Goods::find((int)$id);
        $goods = json_decode(json_encode($goods),true);

        $goods = json_decode(json_encode($goods),true);
        $goods_version = json_decode($goods['goods_version'],1);
        $version_info = $goods_version[$get_version[0]]['subitem'][$get_version[1]];
        $number = $version_info['count'];
        if ($amount > $number){ //库存不足
            $data['status'] = 1;
            $data['msg'] = '库存不足';
            return response()->json($data);
        }else{  //库存充足
            $goods_version[$get_version[0]]['subitem'][$get_version[1]]['count'] = $number -  $amount;
            $goods['goods_version'] = json_encode($goods_version);
            //修改库存
            $id = $goods['id'];
            unset($goods['id']);
            DB::table('goods')->where('id',$id)->update($goods);
        }
        $goods_price = $version_info['salePrice'];
        $new_goods_version['goods_version'] = $goods_version[$get_version[0]]['goods_version'];
        $new_goods_version['image'] = $goods_version[$get_version[0]]['image'];
        $new_goods_version['subitem'] = $version_info;

        if($goods['price_type'] == 1){
            $discount = 100;
        }else{
            if ($this->discount == 0){
                $discount = 100;
            }else{
                $discount = $this->discount;
            }
        }
        $agentPrice = round($goods_price * $discount/100,2);
        $order["order_sn"] = $this->getOrderSn();
        $order["title"] = $goods['goods_name'].'('.$new_goods_version['goods_version'].')';
        $order["type"]  = $goods['goods_type'];
        $order["uid"]   = $this->AU;
        $order["goods_id"]   = $id;
        $order["uname"]  = $this->username;
        $order["price"]     = $goods_price;  //单价
        $order["amount"]    = $amount;
        $order["submitter"]    = $this->username;
        $order["total_price"] = round($amount*$agentPrice,2);
        $order["remarks"] = '购买商品：'.$goods['goods_name'].'('.$new_goods_version['goods_version'].')'.'备注：'.$remarks;

        $order["discount"] = $discount;
        $order["long"] = 1;
        $order["goods_version"] = json_encode($new_goods_version);
        //0:余额   1:赠送金
        $order["pay_type"] = $request->input("pay_type");
        //入库
        $res_id = Orders::insertGetId($order);
        if(!$res_id){
            $this->returnData["code"] = 1;
            $this->returnData["msg"] = "下单失败,稍后重试！";
            return response()->json($this->returnData);
        }
        $res_data = $this->payOrder($res_id,$order,$order["remarks"]);
        return response()->json($res_data);
    }
    /**
     * 扣款
     * @return json
     */
    public function payOrder($id,$order,$remarks){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        if (!$remarks){
            $remarks = '代理商购买发送备注信息测试';
        }
        if(!is_numeric($order["pay_type"])){
            $data["code"] = 1;
            $data["msg"] = "参数错误！";
            return $data;
        }

        //0:余额   1:赠送金
        if($order["pay_type"] == 0){
            //余额支付
            $donation_amount = $this->balance - $order["total_price"];
		
            if($donation_amount < 0){
                $data["code"] = 1;
                $data["msg"] = "余额不足,请选择其他支付方式或联系客服充值！";
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
                return $data;
            }
            $log["uid"] = $this->AU;
            $log["type"] = 9;
            $log["money"] = '-'.$order["total_price"];
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "";
            $log["operation"] = "余额消费￥".$order["total_price"].",商品名称：".$order["title"].",订单号：".$order['order_sn'];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            
            $user["balance"] = $donation_amount;
            $memberModel = new MemberExtend();
            $memberModel->update_money($this->AU,$user);
            Orders::where("id","=",$id)->update(array("status"=>0,"pay_status"=>1,"pay_time"=>time(),"pay_type"=>2));
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
            $data["code"] = 0;
            $data["msg"] = "购买成功";
            return $data;

        }elseif($order["pay_type"] == 1){
            //赠送金支付
            $donation_amount = $this->cash_coupon - $order["total_price"];

            if($donation_amount < 0){
                $res["code"] = 1;
                $res["msg"] = "赠送金不足,请选择其他支付方式或联系客服充值！";
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
                return $res;
            }

            $log["uid"] = $this->AU;
            $log["type"] = 3;
            $log["money"] = '-'.$order["total_price"];
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "";
            $log["operation"] = "赠送金消费￥".$order["total_price"].",商品名称：".$order["title"].",订单号：".$order['order_sn'];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            
            $user["cash_coupon"] = $donation_amount;
            $memberModel = new MemberExtend();
            $memberModel->update_money($this->AU,$user);
            Orders::where("id","=",$id)->update(array("status"=>0,"pay_status"=>1,"pay_time"=>time(),"pay_type"=>3));
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
            $data["code"] = 0;
            $data["msg"] = "购买成功";
            return $data;

        }
        $data["code"] = 1;
        $data["msg"] = "参数错误！";
        return $data;
    }

    /**
     * 获取订单号
     * @return json
     */
    public function getOrderSn(){
        $order_sn = date("ymdHis").rand(1000,9999);
        $count = Orders::where("order_sn",$order_sn)->count();
        if($count>0){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }


    /**
     * 删除
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function del(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $c = $request->input("c");
        $remarks = $request->input("remarks");
        switch ($c) {
            case "cancel":
                $updata["status"] = "-2";
                $result['code'] = 0;
                $result['msg'] = "取消成功";
                Orders::where("id","=",$request->get('id'))->update($updata);
                return response()->json($result);
                break;
            case "refund":
                $updata["status"] = "0";
                $updata["pay_status"] = "-1";
                $result['code'] = 0;
                $result['msg'] = "已提交申请";
                Orders::where("id","=",$request->get('id'))->update($updata);
                return response()->json($result);
                break;
            case "del":
                $updata["is_del"] = "-1";
                $result['code'] = 0;
                $result['msg'] = "删除成功";
                Orders::where("id","=",$request->get('id'))->update($updata);
                return response()->json($result);
                break;
            case "pay":
                $order = Orders::find((int)$request->get('id'));
                $data["order_sn"] = $order->order_sn;
                $data["goods_id"] = $order->goods_id;
                $data["total_price"] = $order->total_price;
                $data["title"] = $order->title;
                $data["goods_version"] = $order->goods_version;
                $data["amount"] = $order->amount;
                //0:余额   1:赠送金
                $data["pay_type"] = $request->input("pay_type");
                $res = $this->payOrder($request->get('id'),$data,$remarks);
                return response()->json($res);
                break;
            default:
                $result['code'] = 1;
                $result['msg'] = "未知操作";
                return response()->json($result);
        }
    }

    /*
     * 查询腾讯云订单
     * */
    public function selectorder(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $time = time();
        $nonce = rand(11111,99999);
        $con = Configs::first();
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $start_time = date("Y-m-d H:i:s",$beginToday);
        $end_time = date("Y-m-d H:i:s",$endToday);
        $page_no = $request->input('page_no', 0);
        $owner_uin = $request->input('owner_uin', '');
        $page_size = $request->input('page_size', 5);
        $page_no = $page_no-1;
        $str = [
            "Action" => "QueryClientDeals",
            "Nonce" => $nonce,
            "Region" => '',
            "SecretId" => $con->tencent_secretid,
            "Timestamp" => $time,
            "creatTimeRangeEnd" => $end_time,
            "creatTimeRangeStart" => $start_time,
            "order" => 0,
            "ownerUin" => $owner_uin,
            "page" => $page_no,
            "rows" => $page_size,
        ];
        $url_str = http_build_query($str);
        //解决http_build_query()函数转义空格和冒号问题
        $replace_one = str_replace("+"," ",$url_str);
        $replace_two = str_replace("%3A",":",$replace_one);
        $new_str = 'GETpartners.api.qcloud.com/v2/index.php?'.$replace_two;
        $secretKey = $con->tencent_secrekey;
        $srcStr = $new_str;
        $signStr = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
        $signStr = urlencode($signStr);
        $url = "https://partners.api.qcloud.com/v2/index.php?Action=QueryClientDeals&Nonce=".$nonce."&Region=&SecretId=".$con->tencent_secretid."&Timestamp=".$time."&creatTimeRangeEnd=".$end_time."&creatTimeRangeStart=".$start_time."&order=0&ownerUin=".$owner_uin."&page=".$page_no."&rows=".$page_size."&Signature=".$signStr;
        $req =  file_get_contents($url);
        $res = json_decode($req,true);
        if ($res['code'] == 0){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['total'] = $res['data']['totalNum'];
            $data['data']['list'] = $res['data']['deals'];
            return response()->json($data);
        }else{
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['total'] = 0;
            $data['data']['list'] = '';
            return response()->json($data);
        }
    }

    //腾讯云代付
    public function agent_payment(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        //余额支付
        $price = $request->input('total_price', 0);
        $owner_uin = $request->input('owner_uin', '');
        $deal_name = $request->input('deal_name', '');
        $price = $price/100;
        /*
        * 待付限制
        * clientType 字段 new：新拓；old：存量；assign：指派 新拓客户可以代付 存量和指派不能代付
        * */
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $uid_info = new DescribeAgentAuditedClientsRequest();
        $uid_info->ClientUin = $owner_uin;

        $uid_resp = $client->DescribeAgentAuditedClients($uid_info);
        $uid_res = $uid_resp->toJsonString();
        $uid_res = json_decode($uid_res,true);
        $ClientType = $uid_res['AgentClientSet'][0]['ClientType'];
        if ($ClientType != 'new'){
            $this->result["code"] = 1;
            $this->result["msg"] = "非新拓展客户，不予以支付";
            return response()->json($this->result);
        }

        //获取用户折扣信息
        $user_discount = DB::table('member')->where('id',$this->AU)->select('tencent_status','tencent_discount','quota')->first();
        $user_discount = json_decode(json_encode($user_discount),true);
        if ($user_discount['tencent_status'] != 1){
            $this->result["code"] = 1;
            $this->result["msg"] = "无权限支付订单";
            return response()->json($this->result);
        }
        if ($price > $user_discount['quota']){
            $this->result["code"] = 1;
            $this->result["msg"] = "订单金额大于代付限额";
            return response()->json($this->result);
        }
        $price = ($price*$user_discount['tencent_discount'])/100;
        $donation_amount = $this->balance - $price;
        if($donation_amount < 0){
            $this->result["code"] = 1;
            $this->result["msg"] = "余额不足,请选择其他支付方式或联系客服充值！";
            return response()->json($this->result);
        }
        //引入腾讯云支付
        DB::beginTransaction();
        try{
            //添加本地订单
            $order["order_sn"] = $this->getOrderSn();
            $order["title"] = $request->input('title', '');
            $order["type"]  = 1;
            $order["uid"]   = $this->AU;
            $order["uname"]  = $this->username;
            $order["price"]     = $request->input('total_price', 0)/100;  //单价
            $order["amount"]    = $request->input('amount', 0);
            $order["submitter"]    = $this->username;
            $order["total_price"] = $price;
            $order["discount"] = $user_discount['tencent_discount'];
            $order["long"] = 4;
            $order["pay_status"] = 2;
            $order["status"] = 1;
            $order["pay_type"] = 2;
            $order["owner_uin"] = $owner_uin;
            $order["pay_time"] = Carbon::now()->toDateTimeString();
            $order["created_at"] = Carbon::now()->toDateTimeString();
            $order["updated_at"] = Carbon::now()->toDateTimeString();
            Orders::insertGetId($order);
            //添加消费记录
            $log["uid"] = $this->AU;
            $log["type"] = 9;
            $log["money"] = '-'.$price;
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "腾讯云订单付款";
            $log["operation"] = "余额消费￥".$price.",商品名称：".$request->input('title', 0).",订单号：".$order["order_sn"];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            //扣款
            $user["balance"] = $donation_amount;
            $memberModel = new MemberExtend();
            $memberModel->update_money($this->AU,$user);

            $con = Configs::first();
            $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("partners.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new PartnersClient($cred, "", $clientProfile);
            $req = new AgentPayDealsRequest();
            $req->OwnerUin = $owner_uin;//订单所有者Uin
            $req->AgentPay = 1;//1：代付  0：自付
            $req->DealNames = [$deal_name]; //订单号数组
            $resp = $client->AgentPayDeals($req);
            $res = $resp->toJsonString();
            $res = json_decode($res,true);
            Log::info($this->AU['id'].' tencent pay success. ',array('result'=>$res));
            DB::commit();
            $data['code'] = 0;
            $data['msg'] = '支付成功';
            $data['data'] = '';
            return response()->json($data);
        }
        catch(TencentCloudSDKException $e){
            Log::info('update achievement error:'.var_export(array('id'=>$this->AU['id'],'data'=>$e)));
            Log::info('tencent pay fail. ',array('result'=>$e,'id'=>$this->AU['id'],'owner_uin'=>$owner_uin,'tencent_orders'=>$deal_name));
            DB::rollback();
            //添加本地订单
            $order["order_sn"] = $this->getOrderSn();
            $order["title"] = $request->input('title', '').'腾讯云订单支付失败';
            $order["type"]  = 1;
            $order["uid"]   = $this->AU;
            $order["uname"]  = $this->username;
            $order["price"]     = $request->input('total_price', 0)/100;  //单价
            $order["amount"]    = $request->input('amount', 0);
            $order["submitter"]    = $this->username;
            $order["total_price"] = $price;
            $order["discount"] = $user_discount['tencent_discount'];
            $order["long"] = 4;
            $order["pay_status"] = -3;
            $order["status"] = -2;
            $order["pay_type"] = 2;
            $order["owner_uin"] = $owner_uin;
            $order["pay_time"] = Carbon::now()->toDateTimeString();
            $order["created_at"] = Carbon::now()->toDateTimeString();
            $order["updated_at"] = Carbon::now()->toDateTimeString();
            Orders::insertGetId($order);
            $data['code'] = 1;
            $data['msg'] = $e->getMessage();
            $data['data'] = '';
            return response()->json($data);
        }
    }

}

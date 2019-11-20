<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Member\MemberBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//捕获错误信息
use TencentCloud\Common\Exception\TencentCloudSDKException;
//参数
use TencentCloud\Common\Credential;
// 导入可选配置类
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
//已审核客户列表
use TencentCloud\Partners\V20180321\Models\DescribeAgentAuditedClientsRequest;
//业务明细
use TencentCloud\Partners\V20180321\Models\DescribeAgentBillsRequest;
//待审核客户列表
use TencentCloud\Partners\V20180321\Models\DescribeAgentClientsRequest;
//修改客户备注
use TencentCloud\Partners\V20180321\Models\ModifyClientRemarkRequest;
//代付
use TencentCloud\Partners\V20180321\Models\AgentPayDealsRequest;
//给客户转账
use TencentCloud\Partners\V20180321\Models\AgentTransferMoneyRequest;
//审核客户
use TencentCloud\Partners\V20180321\Models\AuditApplyClientRequest;
//代理商返佣信息
use TencentCloud\Partners\V20180321\Models\DescribeRebateInfosRequest;
//查询客户余额
use TencentCloud\Partners\V20180321\Models\DescribeClientBalanceRequest;

use TencentCloud\Partners\V20180321\PartnersClient;

class TencentController extends BaseController{

    public function __construct(Request $request){
        parent::__construct($request);
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
    }

    //业务明细
    public function index(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new DescribeAgentBillsRequest();

        $page_size = $request->input('page_size', 10);
        $page_no = $request->input('page_no', 1);
        $req->SettleMonth = $request->input('settle_month', 1);
        $req->Offset = $page_no-1;
        $req->Limit = $page_size;
        $resp = $client->DescribeAgentBills($req);
        $res = $resp->toJsonString();
        $res = json_decode($res,true);
        if (is_array($res['AgentBillSet'])){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['list'] = $res['AgentBillSet'];
            $data['data']['total'] = 0;
            return response()->json($data);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }
    }

    //已审核客户列表
    public function customer_examine(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            $data['code'] = 1;
            $data['msg'] = '腾讯云参数未配置';
            $data['data'] = '';
            return response()->json($data);
        }
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);

        $page_size = $request->input('page_size', 10);
        $page_no = $request->input('page_no', 1);
        $arrears = $request->input('arrears', '');
        $client_type = $request->input('client_type', '');
        $project_type = $request->input('project_type', '');
        $client_uin = $request->input('client_uin', '');
        $client_name= $request->input('client_name', '');
        $client_remark= $request->input('client_remark', '');
        $sortName= $request->input('sortName', '');
        $sortOrder= $request->input('sortOrder', '');
        $req = new DescribeAgentAuditedClientsRequest();
        $req->OrderDirection = "DESC";
        if ($arrears){
            $req->HasOverdueBill = $arrears;
        }
        if ($client_type){
            $req->ClientType = $client_type;
        }
        if ($project_type){
            $req->ProjectType = $project_type;
        }
        if ($client_uin){
            $req->ClientUin = $client_uin;
        }
        if ($client_name){
            $req->ClientName = $client_name;
        }
        if ($client_remark){
            $req->ClientRemark = $client_remark;
        }
        $req->Offset = $page_no -1;
        $req->Limit = $page_size;
        $resp = $client->DescribeAgentAuditedClients($req);
        $res = $resp->toJsonString();
        $res = json_decode($res,true);
        if (is_array($res['AgentClientSet'])){
            if ($sortName && $sortName = "ThisMonthAmt"){
                //根据字段last_name对数组$data进行降序排列
                $last_names = array_column($res['AgentClientSet'],'ThisMonthAmt');
                if ($sortOrder && $sortOrder = "desc"){
                    array_multisort($last_names,SORT_DESC,$res['AgentClientSet']);
                }else{
                    array_multisort($last_names,SORT_ASC,$res['AgentClientSet']);
                }
            }else if($sortName && $sortName = "LastMonthAmt"){
                $last_names = array_column($res['AgentClientSet'],'LastMonthAmt');
                if ($sortOrder && $sortOrder = "desc"){
                    array_multisort($last_names,SORT_DESC,$res['AgentClientSet']);
                }else{
                    array_multisort($last_names,SORT_ASC,$res['AgentClientSet']);
                }
            }
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['list'] = $res['AgentClientSet'];
            $data['data']['total']= $res['TotalCount'];
            return response()->json($data);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }
    }

    //待审核客户列表
    public function customer_be_audited(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
        $con = Configs::first();
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            $data['code'] = 1;
            $data['msg'] = '腾讯云参数未配置';
            $data['data'] = '';
            return response()->json($data);
        }
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $page_size = $request->input('page_size', 10);
        $page_no = $request->input('page_no', 1);
        $req = new DescribeAgentClientsRequest();
        $req->OrderDirection = "DESC";
        $req->Offset = $page_no -1;
        $req->Limit = $page_size;
        $resp = $client->DescribeAgentClients($req);
        $res = $resp->toJsonString();
        $res = json_decode($res,true);
        if (is_array($res['AgentClientSet'])){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['list'] = $res['AgentClientSet'];
            $data['data']['total']= $res['TotalCount'];
            return response()->json($data);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }

    }

    //审核客户
    public function  examine_customer(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            $data['code'] = 1;
            $data['msg'] = '腾讯云参数未配置';
            $data['data'] = '';
            return response()->json($data);
        }
        try {
            $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("partners.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new PartnersClient($cred, "", $clientProfile);

            $req = new AuditApplyClientRequest();
            $ClientUin = $request->input('ClientUin','');
            $Note = $request->input('Note','');
            $req = new AuditApplyClientRequest();
            $req->ClientUin = $ClientUin;
            $req->AuditResult = 'accept';   //同意
            $req->Note = $Note; //申请理由      B类客户审核时必须填写申请理由 有腾讯云审核

            $resp = $client->AuditApplyClient($req);

            $resp->toJsonString();
        }
        catch(TencentCloudSDKException $e) {
//            print $e->getMessage();
            $data['code'] = 1;
            $data['msg'] = '审核失败';
            $data['data'] = '';
            return response()->json($data);
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data'] = '';
        return response()->json($data);
    }

    //修改客户备注
    public function update_customer_remarks(Request $request){
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new ModifyClientRemarkRequest();
        $req->ClientUin = "";//客户账号ID
        $req->ClientRemark = "";//客户备注名称
        $resp = $client->ModifyClientRemark($req);

        print_r($resp->toJsonString());
    }

    //查询余额
    public function select_balance(Request $request){
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new DescribeClientBalanceRequest();
        $req->ClientUin = ""; //客户Uid
        $resp = $client->DescribeClientBalance($req);

        print_r($resp->toJsonString());
    }

    //给客户转账
    public function transfer_accounts_customer(Request $request){
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new AgentTransferMoneyRequest();
        $req->ClientUin = "";  //客户账号ID
        $req->Amount = 10;  //转账金额 单位 分
        $resp = $client->AgentTransferMoney($req);

        $resp->toJsonString();
    }

    //代付
    public function substitute_payment(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($this->AU['id'] !=1){
            $data['code'] = 1;
            $data['msg'] = '无权限';
            $data['data'] = '';
            return response()->json($data);
        }
        $con = Configs::first();
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            $data['code'] = 1;
            $data['msg'] = '腾讯云参数未配置';
            $data['data'] = '';
            return response()->json($data);
        }
        $price = $request->input('total_price', 0);
        $owner_uin = $request->input('owner_uin', '');
        $deal_name = $request->input('deal_name', '');
        $price = $price/100;
        //引入腾讯云支付
        DB::beginTransaction();
        try{
            //添加本地订单
            $order["order_sn"] = $this->getOrderSn();
            $order["title"] = $request->input('title', '');
            $order["type"]  = 1;
            $order["uid"]   = $this->AU['id'];
            $order["uname"]  = $this->AU['name'];
            $order["price"]     = $request->input('total_price', 0)/100;  //单价
            $order["amount"]    = $request->input('amount', 0);
            $order["submitter"]    = $this->AU['name'];
            $order["total_price"] = $price;
            $order["discount"] = '';
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
            $log["wallet"] = '后台用户代付';
            $log["remarks"] = "腾讯云订单付款";
            $log["operation"] = "余额消费￥".$price.",商品名称：".$request->input('title', 0).",订单号：".$order["order_sn"];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            //扣款
//            $user["balance"] = $donation_amount;
//            $memberModel = new MemberExtend();
//            $memberModel->update_money($this->AU,$user);

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

            DB::commit();
            $data['code'] = 0;
            $data['msg'] = '支付成功';
            $data['data'] = '';
            return response()->json($data);
        }
        catch(TencentCloudSDKException $e){
            Log::info('update achievement error:'.var_export(array('id'=>$this->user['id'],'data'=>$e->getMessage())));
            DB::rollback();
            $data['code'] = 1;
            $data['msg'] = $e->getMessage();
            $data['data'] = '';
            return response()->json($data);
        }
        $where['status'] = 2;
        $where['status'] = '已支付';
        DB::table('tencent_order')->where('dealName',$deal_name)->update($where);
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data'] = '';
        return response()->json($data);

    }

    //查询代理商返佣信息
    public function agent_discount(Request $request){
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);

        $page_size = $request->input('page_size', 10);
        $page_no = $request->input('page_no', 1);
        $req = new DescribeRebateInfosRequest();
        $req->RebateMonth = "2018-12"; //返佣月份
        $req->Offset = $page_no -1;
        $req->Limit = $page_size;
        $resp = $client->DescribeRebateInfos($req);
        $res = $resp->toJsonString();
    }

    //查询客户订单
    public function select_customer_orders(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            $data['code'] = 1;
            $data['msg'] = '腾讯云参数未配置';
            $data['data'] = '';
            return response()->json($data);
        }
        $time = time();
        $nonce = rand(11111,99999);
        $start_time = $request->input('start_time', date('Y-m-d 00:00:00',time()));
        $end_time = $request->input('end_time', date('Y-m-d 23:59:59',time()));
        $page_no = $request->input('page_no', 1);
        $owner_uin = $request->input('owner_uin', '');
        $page_size = $request->input('page_size', 5);
        $status = (int)$request->input('status', '');
        $page_no = $page_no - 1;
        if ($status){
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
                "payerMode" => 1,
                "rows" => $page_size,
                "status" => $status
            ];
        }else{
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
                "payerMode" => 1,
                "rows" => $page_size,
            ];
        }
        ksort($str);
//        $url_str = http_build_query($str);
//        //解决http_build_query()函数转义空格和冒号问题
//        $replace_one = str_replace("+"," ",$url_str);
//        $replace_two = str_replace("%3A",":",$replace_one);
        $replace_two = $this->formatBizQueryParaMap($str,false);
        $new_str = 'GETpartners.api.qcloud.com/v2/index.php?'.$replace_two;
        $secretKey = $con->tencent_secrekey;
        $signStr = base64_encode(hash_hmac('sha1', $new_str, $secretKey, true));
        $signStr = urlencode($signStr);
         if ($status){
             $url = "https://partners.api.qcloud.com/v2/index.php?Action=QueryClientDeals&Nonce=".$nonce."&Region=&SecretId=".$con->tencent_secretid."&Timestamp=".$time."&creatTimeRangeEnd=".$end_time."&creatTimeRangeStart=".$start_time."&order=0&ownerUin=".$owner_uin."&page=".$page_no."&payerMode=1&rows=".$page_size."&status=".$status."&Signature=".$signStr;
         }else{
             $url = "https://partners.api.qcloud.com/v2/index.php?Action=QueryClientDeals&Nonce=".$nonce."&Region=&SecretId=".$con->tencent_secretid."&Timestamp=".$time."&creatTimeRangeEnd=".$end_time."&creatTimeRangeStart=".$start_time."&order=0&ownerUin=".$owner_uin."&page=".$page_no."&payerMode=1&rows=".$page_size."&Signature=".$signStr;
         }
        $req =  file_get_contents($url);
        $res = json_decode($req,true);

        if ($res['code'] == 0){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['total'] = $res['data']['totalNum'];
            $data['data']['list'] = $res['data']['deals'];
            return response()->json($data);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }
    }

    ///作用：格式化参数，签名过程需要使用
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


    //获取腾讯云配置
    public function tencent_config(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $configModel = new Configs();
        $res = $configModel->getConfigByID();
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data["data"]['tencent_id'] = $this->Codereplace($res['tencent_id'],4);
        $data["data"]['tencent_appid'] = $this->Codereplace($res['tencent_appid'],4);
        $data["data"]['tencent_secretid'] = $this->Codereplace($res['tencent_secretid'],8);
        $data["data"]['tencent_secrekey'] = $this->Codereplace($res['tencent_secrekey'],8);
        return response()->json($data);
    }

    //字符串打码
    private function Codereplace($string,$length){
        $len = strlen($string);//10
        $start = $length/2;
        $replace_len = $len - $length;//6
        $code = '';
        for ($i=0;$i<$replace_len;$i++) {         //通过循环指定长度
            $code .= "*";
        }
        $new_string = substr_replace($string,$code,$start,$replace_len);
        return $new_string;
    }


    //修改腾讯云配置
    public function update_tencent_config(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = 1;
        $tencent_id = $request->input('tencent_id','*');                //腾讯云id
        $tencent_appid = $request->input('tencent_appid','*');          //腾讯云APPid
        $tencent_secretid = $request->input('tencent_secretid','*');   //腾讯云secretid
        $tencent_secrekey = $request->input('tencent_secrekey','*');   //腾讯云secrekey
        //验证参数
        $data = [];
        $info_1= strpbrk($tencent_id,"*");
        if (!$info_1){
            $data['tencent_id'] = $tencent_id;
        }
        $info_2= strpbrk($tencent_appid,"*");
        if (!$info_2){
            $data['tencent_appid'] = $tencent_appid;
        }
        $info_3= strpbrk($tencent_secretid,"*");
        if (!$info_3){
            $data['tencent_secretid'] = $tencent_secretid;
        }
        $info_4= strpbrk($tencent_secrekey,"*");
        if (!$info_4){
            $data['tencent_secrekey'] = $tencent_secrekey;
        }
        if (empty($data)){
            $this->returnData['msg'] = "未做修改，更新失败";
            return response()->json($this->returnData);
        }
        $configModel = new Configs();
        $res = $configModel->configUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '更新失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "更新成功";
        return response()->json($this->returnData);
    }

    //获取数据库保存的订单
    public function get_tencent_orders(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $dealName = $request->input('deal_name','');
        $status = $request->input('status','');
        $page_no = $request->input('page_no',1);
        $page_size = $request->input('page_size',20);

        $res = DB::table('tencent_order')->select();
        $res_price = DB::table('tencent_order_price')->get();
        $res_price = json_decode(json_encode($res_price),true);
        if($dealName){
            $res->where('dealName',$dealName);
        }
        if($status){
            $res->where('status',$status);
        }
        $start = ($page_no-1) * $page_size;
        $total = $res;
        $data['total'] = $total->count();
        $data['list'] = $res->skip($start)->take($page_size)->get();
        $data['list'] = json_decode(json_encode($data['list']),true);
        foreach ($data['list'] as $k=>&$v){
                $v['goodsPrice'] = $res_price[$v['id']-1];
        }
        $result['code'] = 0;
        $result['msg'] = '请求成功';
        $result['data'] = $data;
        return response()->json($result);
    }

    //修改客户腾讯云代付折扣
    public function update_discount(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['tencent_discount'] = $request->input('discount');
        $data['quota'] = $request->input('quota');
        $id = $request->input('id');
        $memberModel = new MemberBase();
        $res = $memberModel->memberUpdate($id,$data);
        if ($res){
            return response()->json($this->returnData);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }
    }

    //修改客户代付权限
    public function tencent_status(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['tencent_status'] = $request->input('status');
        $id = $request->input('id');
        $memberModel = new MemberBase();
        $res = $memberModel->memberUpdate($id,$data);
        if ($res){
            return response()->json($this->returnData);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
            return response()->json($this->returnData);
        }
    }


}


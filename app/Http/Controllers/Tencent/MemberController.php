<?php

namespace App\Http\Controllers\Tencent;

use App\Models\Admin\Configs;
use App\Models\Orders;
use App\Models\MemberExtend;
use App\Models\WalletLogs;
use Carbon\Carbon;
use App\Http\Config\ErrorCode;
use App\Models\Admin\WorkOrder;
use App\Models\Member\MemberBase;
use Illuminate\Support\Facades\DB;
//参数
use TencentCloud\Common\Credential;
//配置
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
//代付
use TencentCloud\Partners\V20180321\Models\AgentPayDealsRequest;
use TencentCloud\Partners\V20180321\PartnersClient;
//捕获错误信息
use TencentCloud\Common\Exception\TencentCloudSDKException;


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

    //客户交易明细
    public function transactionDetails()
    {

        $params = request()->input();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        $sortName ='id';    //排序列名
        $sortOrder = 'desc';   //排序（desc，asc）
        $pageno = $params['pageNumber'];                         //当前页码
        $pagesize = $params['pageSize'];                            //一页显示的条数
        $pageno = $pageno-1;
        $start = ($pageno-1)*$pagesize;
        $type = isset($params['type']) && $params['type']!='' ? $params['type'] : '';
        $start_time = isset($params['start_time']) && $params['start_time']!='' ? $params['start_time'] : '';
        $rows = WalletLogs::where("uid",$this->user['id']);
        if ($type == '1'){
            $rows->where('type',9);
        }elseif ($type == '2'){
            $rows->where('type',0);
        }else{
            $rows->where(function ($query) use ($type) {
                $query->where('type', '=',0)->orwhere('type', '=',9);
            });
        }
        if($start_time){
            $start_time=date('Y-m-01', strtotime($start_time));
            $end_time = date('Y-m-d', strtotime("$start_time +1 month"));
            $rows->whereBetween('created_at',[$start_time,$end_time]);
        }
        $data['data']['total'] = $rows->count();
        $data['data']['rows'] = $rows->skip($start)->take($pagesize)
            ->orderBy($sortName, $sortOrder)
            ->get();

        $data['status'] = 0;
        $data['msg'] = '请求成功';
        echo json_encode($data);exit;
    }

    //修改名称
    public function update_name(){
        $params = request()->post();
        $where['name'] = $params['name'];
        $where['update_time'] = Carbon::now();
        $res = DB::table('member')->where('id',$this->user['id'])->update($where);
        if ($res){
            $this->result['data'] = '';
            echo json_encode($this->result);exit;
        }else{
            $this->result['status'] = 1;
            $this->result['msg'] = '请求失败';
            echo json_encode($this->result);exit;
        }
    }

    //修改电话
    public function update_mobile(){
        $params = request()->post();
        $where['mobile'] = $params['mobile'];
        $where['update_time'] = Carbon::now();
        $res = DB::table('member')->where('id',$this->user['id'])->update($where);
        if ($res){
            $this->result['data'] = '';
            echo json_encode($this->result);exit;
        }else{
            $this->result['status'] = 1;
            $this->result['msg'] = '请求失败';
            echo json_encode($this->result);exit;
        }
    }

    /*
     * 查询腾讯云订单
     * */
    public function selectorder(){
        $params = request()->input();
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
        $time = time();
        $nonce = rand(11111,99999);
        $con = Configs::first();
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $start_time = date("Y-m-d H:i:s",$beginToday);
        $end_time = date("Y-m-d H:i:s",$endToday);
        $page_no = $params['page_no'];
        $owner_uin =$params['owner_uin'];
        $page_size = $params['page_size'];
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
            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['total'] = $res['data']['totalNum'];
            $data['data']['list'] = $res['data']['deals'];
            echo json_encode($data);exit;
        }else{
            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['total'] = 0;
            $data['data']['list'] = '';
            echo json_encode($data);exit;
        }
    }

    //腾讯云代付
    public function pay_order(){
        $params = request()->input();
        $user_discount = DB::table('member')->where('id',$this->user['id'])->select('tencent_discount','tencent_status','quota')->first();
        $user_discount = json_decode(json_encode($user_discount),true);
        if ($user_discount['tencent_status'] != 1){
            $this->result["code"] = 1;
            $this->result["msg"] = "无权限支付订单";
            echo json_encode($this->result);exit;
        }
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
        //余额支付
        $price = $params['total_price'];
        $price = $price/100;
        if ($price > $user_discount['quota']){
            $this->result["code"] = 1;
            $this->result["msg"] = "订单金额大于代付限额";
            echo json_encode($this->result);exit;
        }
        $price = ($price*$this->user['discount'])/100;
        $donation_amount = $this->user['balance'] - $price;
        if($donation_amount < 0){
            $this->result["status"] = 1;
            $this->result["msg"] = "余额不足,请选择其他支付方式或联系客服充值！";
            echo json_encode($this->result);exit;
        }
        //引入腾讯云支付
        DB::beginTransaction();
        try{
            //添加本地订单
            $order["order_sn"] = $this->getOrderSn();
            $order["title"] = $params['title'];
            $order["type"]  = 1;
            $order["uid"]   = $this->user['id'];
            $order["uname"]  = $this->user['name'];
            $order["price"]     = $price;  //单价
            $order["amount"]    = $params['amount'];
            $order["submitter"]    = $this->user['name'];
            $order["total_price"] = $price;
            $order["discount"] = $this->user['discount'];
            $order["long"] = '';
            $order["pay_status"] = 2;
            $order["status"] = 1;
            $order["pay_type"] = 2;
            $order["owner_uin"] = $params['owner_uin'];
            $order["pay_time"] = Carbon::now()->toDateTimeString();
            $order["created_at"] = Carbon::now()->toDateTimeString();
            $order["updated_at"] = Carbon::now()->toDateTimeString();
            Orders::insertGetId($order);
            //添加消费记录
            $log["uid"] = $this->user['id'];
            $log["type"] = 9;
            $log["money"] = '-'.$price;
            $log["wallet"] = $donation_amount;
            $log["remarks"] = "腾讯云订单付款";
            $log["operation"] = "余额消费￥".$price.",商品名称：".$params['title'].",订单号：".$order["order_sn"];
            $log["created_at"] = Carbon::now()->toDateTimeString();
            $log["updated_at"] = Carbon::now()->toDateTimeString();
            WalletLogs::insertGetId($log);
            //扣款
            $user["balance"] = $donation_amount;
            $memberModel = new MemberExtend();
            $memberModel->update_money($this->user['id'],$user);
            //腾讯云支付
            $con = Configs::first();
            $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("partners.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new PartnersClient($cred, "", $clientProfile);
            $req = new AgentPayDealsRequest();
            $owner_uin = $params['owner_uin'];
            $deal_name = $params['deal_name'];
            $req->OwnerUin = $owner_uin;//订单所有者Uin
            $req->AgentPay = 1;//1：代付  0：自付
            $req->DealNames = [$deal_name]; //订单号数组
            $client->AgentPayDeals($req);
            DB::commit();
            $data['status'] = 0;
            $data['msg'] = '支付成功';
            $data['data'] = '';
            echo json_encode($data);exit;
        }
        catch(\Exception $e){
            DB::rollback();
            $data['status'] = 1;
            $data['msg'] = '支付失败';
            $data['data'] = '';
            echo json_encode($data);exit;
        }
    }

    //获取订单号
    public function getOrderSn(){
        $order_sn = date("ymdHis").rand(1000,9999);
        $count = Orders::where("order_sn",$order_sn)->count();
        if($count>0){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }


    //修改密码
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

    //微信支付
    public function pay_order_api(){
        $params = request()->post();
        $openid = $params['tencent_openid'];    //  openid
        $total_fee = $params['total_fee'];      //  充值金额
        if (strripos($total_fee,'.')){
            $data['status'] = 1;
            $data['msg'] = '充值金额为整数';
            echo json_encode($data);exit;
        }
        $con = Configs::first();
        $appid = $con->tencent_wechat_appid;
        $body =  "帐号充值";
        $mch_id = $con->wx_pay_merchant_id;
        $nonce_str = $this->nonce_str();//随机字符串
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/tencent/tencent/xiaoNotifyUrl'; //回调的url【自己填写】
        $out_trade_no =$this->order_number($openid);//商户订单号
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];//服务器的ip【自己填写】;
        $total_fee = $total_fee * 100;// 微信支付单位是分，所以这里需要*100
        $trade_type = 'JSAPI';//交易类型 默认
        //这里是按照顺序的 因为下面的签名是按照顺序 排序错误 肯定出错
        $post['appid'] = $appid;
        $post['body'] = $body;
        $post['mch_id'] = $mch_id;
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url;
        $post['openid'] = $openid;
        $post['out_trade_no'] = $out_trade_no;
        $post['spbill_create_ip'] = $spbill_create_ip;//终端的ip
        $post['total_fee'] = $total_fee;//总金额 
        $post['trade_type'] = $trade_type;
        $post['sign'] = $this->sign($post);//签名
        $post_xml = $this->arrayToXml($post);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $array = $this->xmlToArray($this->postXmlCurl($post_xml,$url,60));
//        print_r($array);die;
        if($array['return_code'] == 'SUCCESS' && $array['result_code'] == 'SUCCESS'){
            $time = time();
            $tmp=array();//临时数组用于签名
            $tmp['appId'] = $appid;
            $tmp['nonceStr'] = $nonce_str;
            $tmp['package'] = 'prepay_id='.$array['prepay_id'];
            $tmp['signType'] = 'MD5';
            $tmp['timeStamp'] = $time;

            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['timeStamp'] = $time;//时间戳
            $data['data']['nonceStr'] = $nonce_str;//随机字符串
            $data['data']['signType'] = 'MD5';//签名算法，暂支持 MD5
            $data['data']['package'] = 'prepay_id='.$array['prepay_id'];//统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
            $data['data']['paySign'] = $this->sign($tmp);//签名,具体签名方案参见微信公众号支付帮助文档;
//            $data['out_trade_no'] = $out_trade_no;
        }else{
            $data['status'] = 1;
            $data['msg'] = "请求失败";
            $data['data']['return_code'] = $array['return_code'];
            $data['data']['return_msg'] = $array['return_msg'];
        }
        echo json_encode($data);exit;
    }

    //随机32位字符串
    private function nonce_str(){
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i=0;$i<32;$i++){
            $result .= $str[rand(0,48)];
        }
        return $result;
    }

    //生成订单号
    private function order_number($openid){
        //date('Ymd',time()).time().rand(10,99);//18位
        return md5($openid.time().rand(10,99));//32位
    }

    //签名 $data要先排好顺序
    private function sign($Obj){
        $con = Configs::first();
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=".$con->wx_pay_secret_key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    //curl请求
    public function http_request($url,$data = null,$headers=array()){
        $curl = curl_init();
        if( count($headers) >= 1 ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //数组转换成xml
    private function arrayToXml($arr)
    {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . $this->arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }

    //xml转换成数组
    private function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    ///作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode)
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

    /**
     * @param $xml
     * @param $url
     * @param int $second
     * @param bool $useCert
     * @return mixed|string
     */
    private function postXmlCurl($xml, $url, $second = 30, $useCert = false)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        if($useCert == true){
            //设置证书
            $apivlient_cert = getConf('certificate', 'wxapp', $this->uniacid);
            if(!is_dir(Env::get("root_path") . "/cert")){
                mkdir(Env::get("root_path") . "/cert");
            }
            if(!empty($apivlient_cert)){
                file_put_contents(Env::get("root_path") . "/cert/apivlient_cert.pem", $apivlient_cert);
            }
            $appiclient_key = getConf('secret_key', 'wxapp', $this->uniacid);
            if(!empty($appiclient_key)){
                file_put_contents(Env::get("root_path") . "/cert/appiclient_key.pem", $appiclient_key);
            }
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, Env::get("root_path") . "/cert/apivlient_cert.pem");
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, Env::get("root_path") . "/cert/appiclient_key.pem");
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);

        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return ("<meta charset='UTF-8'>" . iconv('UTF-8', 'GBK//TRANSLIT', "curl出错，错误码:$error" . "<br><a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>"));
        }
    }

}

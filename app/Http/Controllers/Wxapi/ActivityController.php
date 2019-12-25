<?php
namespace App\Http\Controllers\Wxapi;

use App\Models\Admin\Activity;
use App\Models\Admin\Album;
use App\Models\Admin\Attend;
use App\Models\Admin\Comment;
use App\Models\Admin\Proposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityController extends BaseController{

    public function __construct()
    {
        parent::__construct();
        //权限验证
        if (\request()->route()->getActionMethod() != 'comment_list'){
            $activity_is_limit = DB::table('permissions')->where('id',166)->first();
            $activity_is_limit = json_decode(json_encode($activity_is_limit),true);
            if ($activity_is_limit['is_limit'] == 1){   //需要验证 判断用户是否验证
                $member_info = DB::table('member')->where('id',$this->user['id'])->first();
                $member_info = json_decode(json_encode($member_info),true);
                if ($member_info['is_vip'] == 0){
                    $this->result['status'] = 203;
                    $this->result['msg'] = '账号尚未认证';
                    return $this->result;
                }elseif ($member_info['is_vip'] == 1){
                    $this->result['status'] = 204;
                    $this->result['msg'] = '认证信息待审核';
                    return $this->result;
                }elseif ($member_info['is_vip'] == 3){
                    $this->result['status'] = 205;
                    $this->result['msg'] = '认证未通过，请重新提交认证';
                    return $this->result;
                }
            }
        }

    }

    //活动报名列表
    public function get_activityList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $page_no = request()->input('page',1);
        $page_size = request()->input('page_size',10);
        $params = [
            'start' => ($page_no - 1)*$page_size,
            'pageSize' => $page_size,
            'sortName' => request()->input('sortName','id'),
            'sortOrder' => request()->input('sortOrder','desc'),
            'status' => [1,2,3],
            'activity_type' => request()->input('activity_type',''),
            'start_time' => request()->input('activity_type',''),
            'end_time' => request()->input('activity_type',''),
            'searchKey' => request()->input('activity_type',''),
        ];
        $activityModel = new Activity();
        $res = $activityModel->getActivityList($params);
        if ($res){
            $ids = [];
            foreach ($res['rows'] as $v){
                $ids[] = $v['id'];
            }
            $idsUserData = $activityModel->relation($this->user['id'],$ids);
            foreach ($res['rows'] as $k=>&$v){
                $v['sign_status'] = $idsUserData[$k]['sign_status'];
                $v['fabulous_status'] = $idsUserData[$k]['fabulous_status'];
                $v['collect_status'] = $idsUserData[$k]['collect_status'];
                if (isset($v['picture'])){
                    $v['picture'] = $this->processingPictures($v['picture']);
                }
            }
            $this->result['data'] = $res;
        }else{
            $this->result['data'] = [];
        }
        return $this->return_result($this->result);
    }

    //活动详情
    public function getInfo(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $id = request()->input('id','');
        $activityModel = new Activity();
        $res = $activityModel->getActivityInfo($id);
        $contact = $activityModel->relation($this->user['id'],[$id]);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['mag'] = '活动不存在';
        }else{
            $res['start_time'] =  date('Y年m月d日',strtotime($res['start_time']));
            $res['end_time'] = date('Y年m月d日',strtotime($res['end_time']));
            if (isset($res['picture'])){
                $res['picture'] = $this->processingPictures($res['picture']);
            }
            $data['info'] = $res;
            $data['contact'] = $contact[0];
            $this->result['data'] = $data;
        }
        return $this->return_result($this->result);
    }

    //报名活动
    public function signUp(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        global $scf_data;
        $activity_id = request()->input('activity_id','');
        $member_name = request()->input('member_name','');
        $mobile = request()->input('mobile','');
        $attendModel = new Attend();
        $activity_member = $attendModel->getMemberActivity($this->user['id'],$activity_id);
        if (!$activity_member){
            $this->result['status'] = 1;
            $this->result['msg'] = '活动已报名';
            return $this->return_result($this->result);
        }elseif ($activity_member === -1){
            $this->result['status'] = 1;
            $this->result['msg'] = '活动人数已满';
            return $this->return_result($this->result);
        }
        $openid = request()->input('openid','');
        $activity_data = DB::table('activity')->where('id',$activity_id)->first();
        $activity_data = json_decode(json_encode($activity_data),true);
        if ($activity_data['cost'] > 0){   //付费活动
            $total_fee = $activity_data['cost'];
            $appid ="";
            $body =  '参加'.$activity_data['name'].'报名费';
            $mch_id = "";
            $nonce_str = $this->nonce_str();//随机字符串
            if ($scf_data['IS_SCF'] == true){
                $notify_url = 'http://'.$scf_data['host'].'/wxapi/activity/NotifyUrl'; //回调的url【自己填写】
                $spbill_create_ip = $scf_data['ip'];
            }else{
                $notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/wxapi/activity/NotifyUrl'; //回调的url【自己填写】
                $spbill_create_ip = $_SERVER['REMOTE_ADDR'];//服务器的ip【自己填写】;
            }
            $out_trade_no = $this->order_number($openid);//商户订单号

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
            //print_r($array);
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
                $data['result'] = 1; //发起支付
                $data['data']['timeStamp'] = $time;//时间戳
                $data['data']['nonceStr'] = $nonce_str;//随机字符串
                $data['data']['signType'] = 'MD5';//签名算法，暂支持 MD5
                $data['data']['package'] = 'prepay_id='.$array['prepay_id'];//统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
                $data['data']['paySign'] = $this->sign($tmp);//签名,具体签名方案参见微信公众号支付帮助文档;
                //添加报名记录
                $params = [
                    'activity_id' => $activity_id,
                    'member_id' => $this->user['id'],
                    'member_name' => $member_name,
                    'mobile' => $mobile,
                    'status' => 0,
                    'openid' => request()->input('openid',''),
                    'money' => $activity_data['cost'],
                    'out_trade_no' => $out_trade_no,
                ];
                $res = $attendModel->addAttend($params);

            }else{
                $data['status'] = 1;
                $data['msg'] = "请求失败";
                $data['result'] = 2; //发起支付失败
                $data['data']['return_code'] = $array['return_code'];
                $data['data']['return_msg'] = $array['return_msg'];
            }
            return $this->return_result($data);

        }else{     //免费活动
            //判断活动报名截止时间
            $new_time = Carbon::now()->toDateTimeString();
            if ($new_time > $activity_data['stop_time']){
                $data['status'] = 1;
                $data['msg'] = '报名已截止';
                $data['result'] = 1;   //免费活动
                $data['data'] = '';
                return $this->return_result($data);
            }
            $status = '';
            if ($activity_data['audit_mode'] == 1){  //自动通过审核
                $status = 2;
            }elseif ($activity_data['audit_mode'] == 0){
                $status = 1;
            }
            $params = [
                'activity_id' => $activity_id,
                'member_id' => $this->user['id'],
                'member_name' => $member_name,
                'mobile' => $mobile,
                'openid' => request()->input('openid',''),
                'out_trade_no' => '',
                'status' => $status
            ];
            $res = $attendModel->addAttend($params);
            if (!$res){
                $data['status'] = 1;
                $data['msg'] = '报名失败';
                $data['result'] = 1;   //免费活动
                $data['data'] = '';
            }else{
                $data['status'] = 0;
                $data['msg'] = '报名成功';
                $data['result'] = 1;   //免费活动
                $data['data'] = '';
            }
            return $this->return_result($data);
        }
    }

    //已报名用户列表
    public function signUpPageList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->input();
        if (!$params['id']){
            $this->result['status'] = 1;
            $this->result['msg'] = 'id参数不存在';
            return $this->return_result($this->result);
        }
        $data['id'] = $params['id'];
        $pageNo = $params['pageNo'] ? $params['pageNo'] : 1;
        $data['pageSize'] = $params['pageSize'] ? $params['pageSize'] : 20;
        $data['start'] = ($pageNo -1) * $data['pageSize'];
        $attendModel = new Attend();
        $res = $attendModel->getAttendList($data);
        if (isset($res['rows'])){
            foreach ($res['rows'] as &$v){
                if (isset($v['avatar'])){
                    $v['avatar'] = $this->processingPictures($v['avatar']);
                }
            }
        }
        $this->result['data'] = $res;
        return $this->return_result($this->result);
     }


    //评论列表
    public function comment_list(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $id = request()->input('activityId','');
        $commentModel = new Comment();
        $res = $commentModel->commemtList($id);
        if (!$res){
            $this->result['data'] = [];
        }else{
            foreach ($res as &$v){
                if (strpos($v['avatar'],'https') != false){
                    $v['avatar'] = $this->processingPictures($v['avatar']);
                }
            }
            $this->result['data'] = $res;
            unset($res);
        }
        return $this->return_result($this->result);
    }

    //用户评论
    public function member_comment(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params['content'] = request()->post('content','');
        $params['member_id'] = $this->user['id'];
        $params['name'] = $this->user['name'];
//        $params['avatar'] = $this->user['avatar'];
        $params['activity_id'] = request()->post('activityId','');
        $activity_info = DB::table('activity')->where('id',$params['activity_id'])->select('start_time')->first();
        $activity_info = json_decode(json_encode($activity_info),true);
        $time = Carbon::now()->toDateTimeString();
        if ($time < $activity_info['start_time']){
            $this->result['status'] = 1;
            $this->result['msg'] = '活动尚未开始，不能评论';
            return $this->return_result($this->result);
        }
        $commentModel = new Comment();
        $res = $commentModel->addData($params);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '请求失败';
        }
        return $this->return_result($this->result);
    }

    //取消报名
    public function cancel_sign(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $activityId = request()->input('activityId');
        $activityModel = new Activity();
        $activity_status = $activityModel->getActivityStatus($activityId);
        if (!$activity_status || $activity_status['activity_status'] != 1){
            $this->result['status'] = 1;
            $this->result['msg'] = '活动结束，取消失败';
            return $this->return_result($this->result);
        }
        $res = $activityModel->cancelSignUp($this->user['id'],$activityId);
        if ($res == -1){
            $this->result['status'] = 1;
            $this->result['msg'] = '活动开始前24小时不能取消报名';
        }elseif ($res == -2){
            $this->result['status'] = 1;
            $this->result['msg'] = '退款失败，请联系主办方';
        }elseif ($res == -3){
            $this->result['status'] = 1;
            $this->result['msg'] = '取消报名失败，请联系主办方';
        }elseif ($res == 1){
            $this->result['status'] = 0;
            $this->result['msg'] = '取消报名成功';
        }else{
            $this->result['status'] = 1;
            $this->result['msg'] = '未知错误，请联系管理员';
        }
        return $this->return_result($this->result);
    }

    //用户点赞
    public function spotFabulous(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $activity_id = request()->input('activityId','');
        $activityModel = new Activity();
        $res = $activityModel->spotFabulous($this->user['id'],$activity_id);
        if (!$res){
            $this->result['stasus'] = 1;
            $this->result['msg'] = '点赞失败';
        }
        return $this->return_result($this->result);
    }

    //取消点赞
    public function cancelFabulous(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $activity_id = request()->input('activityId','');
        $activityModel = new Activity();
        $res = $activityModel->cancelFabulous($this->user['id'],$activity_id);
        if (!$res){
            $this->result['stasus'] = 1;
            $this->result['msg'] = '取消赞失败';
        }
        return $this->return_result($this->result);
    }

    //收藏活动
    public function memberCollect(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $activity_id = request()->input('activityId','');
        $activityModel = new Activity();
        $res = $activityModel->memebrCollect($this->user['id'],$activity_id);
        if (!$res){
            $this->result['stasus'] = 1;
            $this->result['msg'] = '收藏失败';
        }
        return $this->return_result($this->result);
    }
    //取消收藏
    public function memberCancelCollect(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $activity_id = request()->input('activityId','');
        $activityModel = new Activity();
        $res = $activityModel->cancelCollect($this->user['id'],$activity_id);
        if (!$res){
            $this->result['stasus'] = 1;
            $this->result['msg'] = '取消收藏失败';
        }
        return $this->return_result($this->result);
    }
    
    //用户留言
    public function memberAddProposal(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $data['content'] = request()->input('content','');
        $data['picture_list'] = request()->input('pictureList','');
        $data['member_id'] = $this->user['id'];
        $data['name'] = $this->user['name'];
        $data['mobile'] = $this->user['mobile'];
        $proposalModel = new Proposal();
        $res = $proposalModel->addData($data);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '留言失败';
        }
        return $this->return_result($this->result);
    }

    //客户留言列表
    public function memberProposalList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $page_no = request()->input('page',1);
        $page_size = 10;
        $data['start'] = ($page_no - 1) * $page_size;
        $data['pageSize'] = $page_size;
        $data['member_id'] = $this->user['id'];
        $proposalModel = new Proposal();
        $res = $proposalModel->memberIdList($data);
        if (!$res){
            $this->result['data'] = [];
        }else{
            foreach ($res as &$v){
                if ($v['picture_list']){
                    $v['picture_list'] = json_decode($v['picture_list'],true);
                }
            }
            $this->result['data'] = $res;
            unset($res);
        }
        return $this->return_result($this->result);
    }

    //获取往期活动
    public function endActivity(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $pageNo = request()->input('page',1);
        $pageSize = request()->input('pageSize',20);
        $data = [
            'start' => ($pageNo -1)*$pageSize,
            'sortName' => 'id',
            'sortOrder' => 'desc',
            'pageSize' => $pageSize,
            'activity_type' => '',
            'status' => 4,
            'start_time' => '',
            'end_time' => '',
            'searchKey' => ''
        ];
        $activityModel = new Activity();
        $res = $activityModel->getActivityList($data);
        if ($res){
            $ids = [];
            foreach ($res['rows'] as $v){
                $ids[] = $v['id'];
            }
            $idsUserData = $activityModel->relation($this->user['id'],$ids);
            foreach ($res['rows'] as $k=>&$v){
                $v['sign_status'] = $idsUserData[$k]['sign_status'];
                $v['fabulous_status'] = $idsUserData[$k]['fabulous_status'];
                $v['collect_status'] = $idsUserData[$k]['collect_status'];
                if (isset($v['picture'])){
                    $v['picture'] = $this->processingPictures($v['picture']);
                }
            }
            $this->result['data'] = $res;
        }else{
            $this->result['data'] = [];
        }
        return $this->return_result($this->result);
    }

    //生成订单号
    private function order_number($openid){
        //date('Ymd',time()).time().rand(10,99);//18位
        return md5($openid.time().rand(10,99));//32位
    }

    //签名 $data要先排好顺序
    private function sign($Obj){
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=";
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
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


















































?>
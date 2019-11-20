<?php

namespace App\Http\Controllers\Tencent;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Articles;
use App\Models\Admin\Configs;
use App\Models\WalletLogs;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
//引入短信发送类
use Qcloud\Sms\SmsSingleSender;

class CommonController extends Controller
{

    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");


    //获取客户信息
    public function getMember()
    {
        $params = request()->input();
        //判断必传参数
        if(!isset($params['code']) || trim($params['code']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], "code");                                                                 //返回必传参数为空
        }
        $con = Configs::first();
        $url = "https://api.weixin.qq.com/sns/jscode2session?";
        $url .= "appid=".$con->tencent_wechat_appid;
        $url .= "&secret=".$con->tencent_wechat_secret;
        $url .= "&js_code=".$params['code'];
        $url .= "&grant_type=authorization_code";
        $res = file_get_contents($url);                                                                                 //请求微信小程序获取用户接口
        $tmp_res = json_decode($res,true);
        if (isset($tmp_res['errcode']) && !empty($tmp_res['errcode'])) {
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], "请求微信接口报错！！！请联系管理员...");
        }
        $memberModel = new MemberBase();
        $where['tencent_openid'] = $tmp_res['openid'];
        $res = $memberModel->getTencentByOpenID($where['tencent_openid']);
        if(count($res)<1){
            $this->result['data'] = array('tencent_openid'=> $tmp_res['openid']);
            echo json_encode($this->result);exit;
        }
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }

    //注册新用户
    public function register(){
        $params = request()->input();
        if($this->result['status']>0){
            echo json_encode($this->result);exit;
        }
        $memberModel = new MemberBase();
        $res = $memberModel->validMemberRepeat(['mobile'=>$params['mobile']]);
        if($res['is_repeat']==1){
            $this->result['status'] = 1;
            $this->result['msg'] = '手机号已存在';
            echo json_encode($this->result);exit;
        }
        //查询tencent_openid是否绑定用户
        if(DB::table('member')->where('tencent_openid',$params['tencent_openid'])->where("id", "!=", $res)->count()){
            DB::table('member')->where('tencent_openid',$params['tencent_openid'])->update(["tencent_openid" => ""]);
        }
        //添加用户信息
        $member['name']           = $params['nickName'];                       //名称
        $member['mobile']         = $params['mobile'];                         //手机号
        $member['password']       = bcrypt($params['password']);               //密码
        $member['status']         = 1;                                          //客户状态
        $member['active_time']    = Carbon::now();                              //激活时间
        $member['create_time']    = Carbon::now();                              //注册时间
        $member['tencent_openid'] = $params['tencent_openid'];                //openid
        $member['email'] = '';                                                  //默认

        //添加用户详情信息
        $member_extend['realname'] = $params['nickName'];//"Guangdong"
        $member_extend['avatar']  = $params['avatarUrl'];
        $member_extend['type']  = 0;
        $member_extend['source']  = '腾讯云小程序';
        $member_extend['update_time']  = Carbon::now();
        $member_extend['recommend']  = 1;
        $member_extend['addperson']  = 'root';
        $member_extend['position']  = '';
        $member_extend['company']  = '';
        $member_extend['wechat']  = '';
        $member_extend['qq']  = '';
        $member_extend['source']  = '';
        $member_extend['project']  = '';
        $member_extend['remarks']  = '';

        $res = $memberModel->memberInsert($member,$member_extend);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '添加失败';
            echo json_encode($this->result);exit;
        }
        $this->result['msg'] = '添加成功';
        $this->result['data'] = ['result'=>0];
        echo json_encode($this->result);exit;

    }

    //客户绑定
    public function bindAccount()
    {
        $params = request()->input();
        if(!isset($params['tencent_openid']) || trim($params['tencent_openid']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'tencent_openid'); //返回必传参数为空
        }
        if(!isset($params['account']) || trim($params['account']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'account'); //返回必传参数为空
        }
        if(!isset($params['password']) || trim($params['password']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'password'); //返回必传参数为空
        }
        $preg_phone='/^1[3456789]\d{9}$/';
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/';
        if(!preg_match($preg_phone,$params['account']) && !preg_match($preg_email,$params['account'])){
            $this->result['status'] = 1;
            $this->result['msg'] = '账号必须为正确的手机号或者邮箱';
            echo json_encode($this->result);exit;
        }
        $data = array(
            'tencent_openid' => $params['tencent_openid']
        );
        $memberModel = new MemberBase();
        $res = $memberModel->validateMemberAccount($params['account'],trim($params['password']));
        if(!$res){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号或密码错误,请联系管理员');
        }elseif ($res < 0){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号已被禁用，请联系管理员');
        }
//        if($res['openid']!=''){
//            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号已绑定');
//        }
        if(DB::table('member')->where('tencent_openid',$params['tencent_openid'])->where("id", "!=", $res)->count()){
            DB::table('member')->where('tencent_openid',$params['tencent_openid'])->update(["tencent_openid" => ""]);
        }
        $member_res = $memberModel->memberUpdate($res,$data);
        if(!$member_res){
            $this->result['status'] = 1;
            $this->result['msg'] = '绑定失败';
            echo json_encode($this->result);exit;
        }
        echo json_encode($this->result);exit;
    }

    //发送短信
    public function sendingsms(){
        $con = Configs::first();
        require_once  base_path().'/vendor/qcloudsms_php-master/src/index.php';
        $params = request()->input();
        $mobile = $params['mobile'];
        // 短信应用SDK AppID
        $appid = $con->sms_appid; // 1400开头
        // 短信应用SDK AppKey
        $appkey = $con->sms_appkey;
        // 需要发送短信的手机号码
        $phoneNumbers = [$mobile];
        //判断短信发送类型选不同模版
        $memberModel = new MemberBase();
        if($params['type'] == 1){    //注册
            $res = $memberModel->getMemberByMobile($mobile);
            if ($res){
                $data['status'] = 0;
                $data['msg'] = '手机号已注册';
                $data['data']['result'] = 2;
                echo json_encode($data);exit;
            }
            $templateId ='';
        }else if($params['type'] == 2){
            if (isset($params['id'])){   //设置id为修改手机号
                $res = $memberModel->getMemberByID($params['id']);
                if ($res['mobile'] != $mobile ){
                    $data['status'] = 0;
                    $data['msg'] = '手机号与绑定账号手机不一致';
                    $data['data']['result'] = 3;
                    echo json_encode($data);exit;
                }
            }else{     //找回密码
                $res = $memberModel->getMemberByMobile($mobile);
                if (!$res){
                    $data['status'] = 0;
                    $data['msg'] = '手机号不存在';
                    $data['data']['result'] = 2;
                    echo json_encode($data);exit;
                }
            }
            $templateId ='';
        }
        // 签名
        $smsSign = "";
        // 指定模板ID单发短信
        $code = rand(1111,9999);
        $validate_res = DB::table('verification')->where('mobile',$mobile)->first();
        $validate_res = json_decode(json_encode($validate_res),true);
        if ($validate_res){
            $info['code'] = $code;
            $info['create_time'] = time();
            $info['overdue_time'] = time() + 300;
            DB::table('verification')->where('mobile',$mobile)->update($info);
        }else{
            $info['mobile'] = $mobile;
            $info['code'] = $code;
            $info['create_time'] = time();
            $info['overdue_time'] = time() + 300;
            DB::table('verification')->insert($info);
        }
        try {
            $ssender = new SmsSingleSender($appid, $appkey);
            $info = [$code];
            $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
                $info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['result'] = 0;
            echo json_encode($data);exit;
        } catch(\Exception $e) {
            $data['status'] = 1;
            $data['msg'] = '请求失败';
            $data['data']['result'] = 1;
            echo json_encode($data);exit;
        }
    }

    //修改密码
    public function not_login_update_passwoed(){
        $params = request()->input();
        $res = DB::table('verification')->where('mobile',$params['mobile'])->first();
        $res = json_decode(json_encode($res),true);
        if ($res['overdue_time'] < time()){
            $data['status'] = 0;
            $data['msg'] = '验证码过期';
            $data['data']['result'] = 2;
            echo json_encode($data);exit;
        }
        if ($res['code'] != $params['verification']){
            $data['status'] = 0;
            $data['msg'] = '修改秘密失败';
            $data['data']['result'] = 1;
            echo json_encode($data);exit;
        }else{
            $where['password'] = $params['password'];
            DB::table('member')->where('id',$res['id'])->update($where);
            $data['status'] = 0;
            $data['msg'] = '修改密码成功';
            $data['data']['result'] = 0;
            echo json_encode($data);exit;
        }
    }

    //验证用户输入的验证码
    public function check_verification(){
        $params = request()->input();
        $res = DB::table('verification')->where('mobile',$params['mobile'])->first();
        $res = json_decode(json_encode($res),true);
        if ($res['overdue_time'] < time()){
            $data['status'] = 0;
            $data['msg'] = '验证码过期';
            $data['data']['result'] = 2;
            echo json_encode($data);exit;
        }
        if ($res['code'] != $params['verification']){
            $data['status'] = 0;
            $data['msg'] = '验证码错误';
            $data['data']['result'] = 1;
            echo json_encode($data);exit;
        }else{
            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['result'] = 0;
            echo json_encode($data);exit;
        }
    }

	public function getNewsList()
    {
        $res = Articles::where('typeid',2)->first();
        $this->result['data'] = json_decode(json_encode($res),true);
        echo json_encode($this->result);exit;
    }

    //返回失败的原因
    private function verify_parameter($data,$text="")
    {
        if (isset($data['msg']) && strpos($data['msg'], "%s") !== false && $text) {
            $data['msg'] = sprintf($data['msg'], $text);
        }
        echo json_encode($data);exit;
    }

    /* 微信支付完成，回调地址url方法  xiao_notify_url() */
    public function xiao_notify_url(){
        $post_data = $_REQUEST;
        if($post_data==null){
            $post_data = file_get_contents("php://input");
        }
        if($post_data == null){
            $post_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if($post_data == null){
            Log::info('1321323213pay error: 获取不到微信返回的信息');
        }
        $post_data = $this->xml_to_array($post_data);   //微信支付成功，返回回调地址url的数据：XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']);

        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        $user_sign = $this->sign($post_data);   //再次生成签名，与$postSign比较
        if($post_data['return_code']=='SUCCESS'&& $postSign == $user_sign ){
            //增加金额
            $memberModel = new MemberBase();
            $res = $memberModel->getTencentByOpenID($post_data['openid']);
            $money = $post_data['total_fee']/100;
            $balance['balance'] =  $money + $res['balance'] ;
            $balance['update_time'] = Carbon::now();
            DB::table('member_extend')->where('member_id',$res['id'])->update($balance);
            //增加记录
            $list['uid'] = $res['id'];
            $list['type'] = 0;
            $list['operation'] = "增加余额";
            $list['money'] = $money;
            $list['wallet'] = $balance['balance'];
            $list['remarks'] = '小程序充值';
            $list['manage'] = '小程序充值';
            $list['created_at'] = Carbon::now();
            $list['updated_at'] = Carbon::now();
            WalletLogs::insertGetId($list);
            $this->return_success();
        }else{
            Log::info('1321323213pay error:验证失败00111');
        }
    }

    //活动报名支付回调
    /* 微信支付完成，回调地址url方法  xiao_notify_url() */
    public function activity_notify_url(){
        $post_data = $_REQUEST;
        if($post_data==null){
            $post_data = file_get_contents("php://input");
        }
        if($post_data == null){
            $post_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if($post_data == null){
            Log::info('1321323213pay error: 获取不到微信返回的信息');
        }
        $post_data = $this->xml_to_array($post_data);   //微信支付成功，返回回调地址url的数据：XML转数组Array
        $postSign = $post_data['sign'];
        unset($post_data['sign']);

        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        $user_sign = $this->sign($post_data);   //再次生成签名，与$postSign比较
        if($post_data['return_code']=='SUCCESS'&& $postSign == $user_sign ){
            //修改活动报名状态
            $out_trade_no = $post_data['out_trade_no'];
            $res = DB::table('attend')->where('out_trade_no',$out_trade_no)->first();
            $res = json_decode(json_encode($res),true);
            $where['status'] = 1;
            DB::table('attend')->where('id',$res['id'])->update($where);
//            $memberModel = new MemberBase();
//            $res = $memberModel->getTencentByOpenID($post_data['openid']);
//            $money = $post_data['total_fee']/100;
//            $balance['balance'] =  $money + $res['balance'] ;
//            $balance['update_time'] = Carbon::now();
//            DB::table('member_extend')->where('member_id',$res['id'])->update($balance);
//            //增加记录
//            $list['uid'] = $res['id'];
//            $list['type'] = 0;
//            $list['operation'] = "增加余额";
//            $list['money'] = $money;
//            $list['wallet'] = $balance['balance'];
//            $list['remarks'] = '腾讯云小程序充值';
//            $list['manage'] = '腾讯云小程序充值';
//            $list['created_at'] = Carbon::now();
//            $list['updated_at'] = Carbon::now();
//            WalletLogs::insertGetId($list);
            $this->return_success();
        }else{
            Log::info('1321323213pay error:验证失败00111');
        }
    }

    //给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
    private function return_success(){
        $return['return_code'] = 'SUCCESS';
        $return['return_msg'] = 'OK';
        $xml_post = '<xml>
                    <return_code>'.$return['return_code'].'</return_code>
                    <return_msg>'.$return['return_msg'].'</return_msg>
                    </xml>';
        echo $xml_post;exit;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_array($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }


    /**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
    public function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
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

    //获取关于我们
    public function getAbout()
    {
        $articleModel = new Articles();
        $res = $articleModel->getApiArticle(4);
        if(!$res){
            $res = '';
        }
        $data['status'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['result'] = $res;
        echo json_encode($data);exit;
    }
}
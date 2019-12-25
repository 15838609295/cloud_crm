<?php

namespace App\Http\Controllers\Wxapi;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use App\Library\WXBizDataCrypt;
use App\Models\Admin\Activity;
use App\Models\Admin\AdminUser;
use App\Models\Admin\China;
use App\Models\Admin\FormId;
use App\Models\Admin\Modular;
use App\Models\Admin\PlugInUnit;
use App\Models\Admin\Proposal;
use App\Models\Admin\ServiceHotline;
use App\Models\Admin\Street;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Articles;
use App\Models\Admin\Configs;
use App\Models\WalletLogs;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
//引入短信发送类
use Qcloud\Sms\SmsSingleSender;
use Symfony\Component\CssSelector\Tests\Parser\ReaderTest;
use TencentCloud\Cdb\V20170320\Models\VerifyRootAccountRequest;

class CommonController extends Controller
{

    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");

    public function return_result($data,$text = ""){
        if(strpos($data['msg'], "%s") !== false && $text){
            $data['msg'] = sprintf($data['msg'], $text);
        }
        return response()->json($data);
    }
    //获取客户信息
    public function getMember(){
        $params = request()->input();
        //判断必传参数
        if(!isset($params['code']) || trim($params['code']) == ''){
            return $this->return_result(ErrorCode::$api_enum['params_not_exist'],'code');
        }
        $con = Configs::first();
        if (isset($params['wxType'])&&trim($params['wxType'])==1){
            $url = "https://api.weixin.qq.com/sns/jscode2session?";
            $url .= "appid=".$con->member_wechat_appid;
            $url .= "&secret=".$con->member_wechat_secret;
            $url .= "&js_code=".$params['code'];
            $url .= "&grant_type=authorization_code";
            $res = file_get_contents($url);                                                                                 //请求微信小程序获取用户接口
            $tmp_res = json_decode($res,true);
            if (isset($tmp_res['errcode']) && !empty($tmp_res['errcode'])) {
                $this->result['status'] = 1;
                $this->result['msg'] = '请求微信接口报错！！！请联系管理员...';
                return $this->return_result($this->result);
            }
            $memberModel = new MemberBase();
            $where['openid'] = $tmp_res['openid'];
            $res = $memberModel->getMemberByOpenID($where['openid']);
            if(count($res)<1){  //绑定账号
                $this->result['status'] = 206;
                $this->result['msg'] = '未绑定账号信息';
                $this->result['data'] = array('openid'=> $tmp_res['openid']);
                return $this->return_result($this->result);
            }
            $this->result['data'] = $res;
            unset($res);
            return $this->return_result($this->result);
        }else{
            $res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->qy_appid);
            $ar = json_decode($res,true);
            $access_token = $ar['access_token'];
            $url = "https://qyapi.weixin.qq.com/cgi-bin/miniprogram/jscode2session?";
            $url .= "&access_token=".$access_token;
            $url .= "&js_code=".$params['code'];
            $url .= "&grant_type=authorization_code";
            $res = file_get_contents($url);  //请求微信小程序获取用户接口
            $params001 = json_decode($res,true);
            if (isset($params001['errcode']) && !empty($params001['errcode'])) {
                $this->result = ErrorCode::$api_enum["fail"];
                $this->result["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
            }
            $member = DB::table('member')->where('openid',$params001['userid'])->first();
            $member = json_decode(json_encode($member),true);
            if($member){
                if($member["status"] != 1){
                    $this->result = ErrorCode::$api_enum["fail"];
                    $this->result["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
                }else {
                    $this->result['data'] = $member;
                }
            }else{
                $this->result['status'] = 206;
                $this->result['msg'] = '未绑定账号';
                $this->result['data'] = array(
                    'openid'=> $params001['userid']
                );
            }
            return $this->return_result($this->result);
        }
    }

    //获取用户手机号
    public function phoneNumber(){
        $params = request()->input();
        if (isset($params['wxType'])&&trim($params['wxType'])==1){
            $con = Configs::first();
            $appid = $con->member_wechat_appid;
            $url = "https://api.weixin.qq.com/sns/jscode2session?";
            $url .= "appid=".$con->member_wechat_appid;
            $url .= "&secret=".$con->member_wechat_secret;
            $url .= "&js_code=".$params['code'];
            $url .= "&grant_type=authorization_code";
            $res = file_get_contents($url);                                                                                 //请求微信小程序获取用户接口
            $tmp_res = json_decode($res,true);
            if (isset($tmp_res['errcode']) && !empty($tmp_res['errcode'])) {
                $this->result['status'] = 1;
                $this->result['msg'] = '请求微信接口报错！！！请联系管理员...';
                return $this->return_result($this->result);
            }
            $sessionKey = $tmp_res['session_key'];
            $pc = new WXBizDataCrypt($appid,$sessionKey);
            $encryptedData = $params['encryptedData'];

            $iv = $params['iv'];
            $errCode = $pc->decryptData($encryptedData, $iv,$data);
            if ($errCode == 0){
                $data = json_decode($data,true);
                $this->result['data'] = $data;
                return $this->return_result($this->result);
            }else{
                $this->result['status'] = 1;
                $this->result['msg'] = '获取失败，请重试';
                return $this->return_result($this->result);
            }
        }else{
            $this->result['status'] = 1;
            $this->result['msg'] = '企业微信不支持微信授权登陆！！！';
            return $this->return_result($this->result);
        }
    }

    //注册新用户
    public function register(){
        $params = request()->input();
        $memberModel = new MemberBase();
        $res = $memberModel->validMemberRepeat(['mobile'=>$params['mobile']]);
        if($res['is_repeat']==1){
            $where['openid'] = $params['openid'];
            $result = DB::table('member')->where('mobile',$params['mobile'])->update($where);
            $this->result['status'] = 0;
            $this->result['msg'] = '该手机号已注册,是否前往绑定';
            return $this->return_result($this->result);
        }
        //查询openid是否绑定用户
//        if(DB::table('member')->where('openid',$params['openid'])->where("id", "!=", $res)->count()){
//            DB::table('member')->where('openid',$params['openid'])->update(["openid" => ""]);
//        }
        if (!$params['nickName']){
            $params['nickName'] = '新用户'.rand(0000,9999);
        }
        //添加用户信息
        $member['name']           = $params['nickName'];                       //名称
        $member['mobile']         = $params['mobile'];                         //手机号
        $member['password']       = bcrypt(123456);                      //密码
        $member['status']         = 1;                                          //客户状态
        $member['active_time']    = Carbon::now()->toDateTimeString();         //激活时间
        $member['create_time']    = Carbon::now()->toDateTimeString();         //注册时间
        $member['openid'] = $params['openid'];                //openid
        $member['email'] = '';                                                  //默认

        //添加用户详情信息
        $member_extend['realname'] = $params['nickName'];
        $member_extend['avatar']  = $params['avatarUrl'];
        $member_extend['type']  = 0;
        $member_extend['source'] = "客户小程序";
        $member_extend['update_time']  = Carbon::now();
        $member_extend['recommend']  = 1;
        $member_extend['addperson']  = 'root';
        $member_extend['position']  = '';
        $member_extend['company']  = '';
        $member_extend['wechat']  = '';
        $member_extend['qq']  = '';
        $member_extend['project']  = '';
        $member_extend['remarks']  = '';
        $res = $memberModel->memberInsert($member,$member_extend);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '添加失败';
            return $this->return_result($this->result);
        }
        $this->result['msg'] = '添加成功';
        $this->result['data'] = ['result'=>0];
        return $this->return_result($this->result);
    }

    //客户绑定
    public function bindAccount(){
        $params = request()->input();
        if(!isset($params['openid']) || trim($params['openid']) == ''){
            return $this->return_result(ErrorCode::$api_enum["params_not_exist"], 'openid'); //返回必传参数为空
        }
        if(!isset($params['account']) || trim($params['account']) == ''){
            return $this->return_result(ErrorCode::$api_enum["params_not_exist"], 'account'); //返回必传参数为空
        }
        if(!isset($params['password']) || trim($params['password']) == ''){
            return $this->return_result(ErrorCode::$api_enum["params_not_exist"], 'password'); //返回必传参数为空
        }
        $preg_phone='/^1[3456789]\d{9}$/';
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/';
        if(!preg_match($preg_phone,$params['account']) && !preg_match($preg_email,$params['account'])){
            return $this->return_result(ErrorCode::$api_enum["customized"], '账号必须为正确的手机号或者邮箱');
        }
        $data = array(
            'openid' => $params['openid']
        );
        $memberModel = new MemberBase();
        $res = $memberModel->validateMemberAccount($params['account'],trim($params['password']));
        if(!$res){
            return $this->return_result(ErrorCode::$api_enum["customized"], '账号或密码错误,请联系管理员');
        }elseif ($res < 0){
            return $this->return_result(ErrorCode::$api_enum["customized"], '账号已被禁用，请联系管理员');
        }
        $member_res = $memberModel->memberUpdate($res,$data);
        if(!$member_res){
            return $this->return_result(ErrorCode::$api_enum["customized"], '绑定失败');
        }
        return $this->return_result($this->result);
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
        if($params['type'] == 1){    //1注册
            $res = $memberModel->getMemberByMobile($mobile);
            if ($res){
                $data['status'] = 0;
                $data['msg'] = '手机号已注册';
                $data['data']['result'] = 2;
                return $this->return_result($data);
            }
            $templateId ='';
        }else if($params['type'] == 2){    //2修改密码/修改手机号
            if (isset($params['id'])){   //设置id为修改手机号
                if ($params['id'] == ''){
                    $data['status'] = 301;
                    $data['msg'] = '用户id不存在';
                    $data['data']['result'] = 3;
                    return $this->return_result($data);
                }else{
                    $res = $memberModel->getMemberByID($params['id']);
                    if ($res['mobile'] != $mobile ){
                        $data['status'] = 0;
                        $data['msg'] = '手机号与绑定账号手机不一致';
                        $data['data']['result'] = 3;
                        return $this->return_result($data);
                    }
                }
            }else{     //找回密码
                $res = $memberModel->getMemberByMobile($mobile);
                if (!$res){
                    $data['status'] = 0;
                    $data['msg'] = '手机号不存在';
                    $data['data']['result'] = 2;
                    return $this->return_result($data);
                }
            }
            $templateId ='424301';
        }elseif ($params['type'] == 3){   //修改手机号
            $res = $memberModel->getMemberByMobile($mobile);
            if ($res){
                $data['status'] = 0;
                $data['msg'] = '手机号已绑定';
                $data['data']['result'] = 2;
                return $this->return_result($data);
            }
            $templateId ='';
        }
        // 签名
        $smsSign = "梅城街长制";
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
            $rsp = json_decode($result,true);
            if ($rsp['result'] == 0){
                $data['status'] = 0;
                $data['msg'] = '请求成功';
                $data['data']['result'] = 0;
                return $this->return_result($data);
            }else{
                $data['status'] = 1;
                $data['msg'] = '请求失败';
                $data['data']['result'] = 1;
                return $this->return_result($data);
            }

        } catch(\Exception $e) {
            $data['status'] = 1;
            $data['msg'] = '请求失败';
            $data['data']['result'] = 1;
            return $this->return_result($data);
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
            return $this->return_result($data);
        }
        if ($res['code'] != $params['verification']){
            $data['status'] = 0;
            $data['msg'] = '修改秘密失败';
            $data['data']['result'] = 1;
            return $this->return_result($data);
        }else{
            $where['password'] = bcrypt($params['password']);
            DB::table('member')->where('mobile',$params['mobile'])->update($where);
            $data['status'] = 0;
            $data['msg'] = '修改密码成功';
            $data['data']['result'] = 0;
            return $this->return_result($data);
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
            return $this->return_result($data);
        }
        if ($res['code'] != $params['verification']){
            $data['status'] = 0;
            $data['msg'] = '验证码错误';
            $data['data']['result'] = 1;
            return $this->return_result($data);
        }else{
            $data['status'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['result'] = 0;
            return $this->return_result($data);
        }
    }

    //解除绑定
    public function unbind(){
        $params = request()->input();
        $member_res = DB::table('member')->where('openid',$params['openid'])->first();
        if (!$member_res){
            return $this->return_result(ErrorCode::$api_enum["customized"], '解除绑定失败，未找到账号');
        }
        $where['openid'] = '';
        $res = DB::table('member')->where('openid',$params['openid'])->update($where);
        if (!$res){
            return $this->return_result(ErrorCode::$api_enum["customized"], '解除绑定失败，未找到账号');
        }else{
            $data['status'] = 0;
            $data['msg'] = '解除绑定成功';
            return $this->return_result($data);
        }
    }

    //使用帮助
    public function getNewsList(){
        $res = Articles::where('typeid',2)->first();
        $this->result['data'] = json_decode(json_encode($res),true);
        return $this->return_result($this->result);
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
            $res = $memberModel->getMemberByOpenID($post_data['openid']);
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
        return $xml_post;
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
    public function getAbout(){
        $articleModel = new Articles();
        $res = $articleModel->getApiArticle(4);
        if(!$res){
            $res = '';
        }
        $this->result['data'] = ['result' => $res];
        return $this->return_result($this->result);
    }

    //获取首页排版信息
    public function homePage(){
        $plugUnitModel = new PlugInUnit();
        $articleModel = new Articles();
        $activityModel = new Activity();
        $data['type'] = 1;
        $res = $plugUnitModel->getHomeOrder($data);
        if (!$res){
            $this->result['data'] = [];
        }else{
            foreach ($res as &$v){
                if ($v['id'] == 1){  //轮播图
                    $v['bannerList'] = $plugUnitModel->showBanner($data['type']);
                    if ($v['bannerList']){
                        foreach ($v['bannerList'] as &$b_v){
                            if ($b_v['url']){
                                $b_v['url'] = $this->processingPictures($b_v['url']);
                            }
                        }
                    }
                }
                if ($v['id'] == 3){   //插件列表插进去
                    $v['plugUnitList'] = $this->getPlugUnitOrder();
                     foreach ($v['plugUnitList'] as &$p_v){
                         if ($p_v['icon']){
                             $p_v['icon'] = $this->processingPictures($p_v['icon']);
                         }
                     }
                }
                if ($v['id'] == 2){   //公告
                    $v['newsList'] = $articleModel->getNotice();
                }
                if ($v['id'] == 4){  //新闻
                    $v['newsList'] = $articleModel->getNews();
                    foreach ($v['newsList'] as &$n_v){
                        if ($n_v['thumb']){
                            $n_v['thumb'] = $this->processingPictures($n_v['thumb']);
                        }
                        if ($n_v['created_at']){
                            $n_v['created_at'] = (substr($n_v['created_at'],0,10));
                        }
                    }
                }
                if ($v['id'] == 5){  //推荐
                    $v['activityList'] = $activityModel->getNewActivity();
                    if ($v['activityList']){
                        foreach ($v['activityList'] as &$a_v){
                            if (isset($a_v['picture'])){
                                $a_v['picture'] = $this->processingPictures($a_v['picture']);
                            }
                        }
                    }
                }
            }
            $this->result['data'] = array_values($res);
            unset($res);
        }
        return $this->return_result($this->result);
    }

    //获取底部导航栏根据排序和状态
    private function getNavigationList($type){
        $plugUnitModel = new PlugInUnit();
        $data['type'] = $type;
        $res = $plugUnitModel->getNavigationList($data,0);
        return $res;
    }

    //获取插件根据排序和状态
    private function getPlugUnitOrder(){
        $ids = ['164','165','166','167','168','175','176','177','178','179','213','214','215','221'];
        $res = DB::table('permissions')->whereIn('id',$ids)->get();
        //获取专题
        $modularModel = new Modular();
        $list = $modularModel->getList();
        if (!$res){
            return array();
        }else{
            $res = json_decode(json_encode($res),1);
            if ($list){
                foreach ($list as &$v){
                    $v['type'] = 'NEWS';
                }
                $res = array_merge($res,$list);
            }
            $last_names = array_column($res,'sort');
            array_multisort($last_names,SORT_ASC,$res);
            return $res;
        }
    }

    //获取小程序名称和排版信息
    public function getWxconfig(){
        $data = DB::table('configs')->where('id',1)->select('wxapplet_name as wxappletName','wxapplet_color as wxappletColor','member_format as memberFormat','env','agent_wechat_configs')->first();
        $data = json_decode(json_encode($data),true);
        $navigationList = $this->getNavigationList(1);
        foreach ($navigationList as &$v){
            if ($v['iconPath']){
                $v['iconPath'] = $this->processingPictures($v['iconPath']);
            }
            if ($v['selectedIconPath']){
                $v['selectedIconPath'] = $this->processingPictures($v['selectedIconPath']);
            }
            if ($v['selectedIconSvg']){
                $v['selectedIconSvg'] = $this->processingPictures($v['selectedIconSvg']);
            }
        }
        //获取设置员工端的首页地址
        $admin_navigationList = $this->getNavigationList(0);
        if (isset($admin_navigationList[0]['pagePath'])){
            $data['adminHomePageUrl'] = $admin_navigationList[0]['pagePath'];
            unset($admin_navigationList);
        }else{
            $data['adminHomePageUrl'] = '';
        }
        //处理配置信息
        if (!$data['agent_wechat_configs']){
            $configs = [
                'album' => [
                    'status' => 0,
                    'bgImage' => ''
                ],
                'workOrder' => [
                    'status' => 0,
                    'bgImage' => ''
                ]
            ];
        }else {
            $configs = json_decode($data['agent_wechat_configs'],true);
            if (isset($configs['album'])){
                $configs['album']['bgImage'] = $this->processingPictures($configs['album']['bgImage']);
            }else{
                $configs['album'] = [
                    'status' => 0,
                    'bgImage' => ''
                ];
            }
            if (isset($configs['workOrder'])){
                $configs['workOrder']['bgImage'] = $this->processingPictures($configs['workOrder']['bgImage']);
            }else{
                $configs['workOrder'] = [
                    'status' => 0,
                    'bgImage' => ''
                ];
            }
        }
        unset($data['agent_wechat_configs']);
        $data['configs'] = $configs;
        $data['tabbarList'] = $navigationList;
        $this->result['data'] = $data;
        return $this->return_result($this->result);
    }

    //省市区地址
    public function region_list(){
        $chinaModel = new China();
        $res = $chinaModel->getRegionList();
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    //公告列表
    public function noticeList(){
        $page_no = request()->input('page_no', 1);
        $page_size = request()->input('page_size', 10);
        $searchFilter = array(
            'sortName' => "created_at",                                                  //排序列名
            'sortOrder' => "desc",                                               //排序（desc，asc）
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'typeid' => 3,
            'searchKey' => '',
            'is_display' => 0,
            'read_power' => [0,1],
            'articles_type_id' => ''
        );
        $articleModel = new Articles();
        $res = $articleModel->getCustomArticlesWithFilter($searchFilter);
        $this->result['data'] = $res['rows'];
        unset($res);
        return $this->return_result($this->result);
    }

    //公告详情
    public function noticeInfo(){
        $id = request()->input('id','');
        $articleModel = new Articles();
        $res = $articleModel->getArticlesByID($id);
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    //最新新闻
    public function latest_news(){
        $articleModel = new Articles();
        $res = $articleModel->getNews();
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    //获取form_id
    public function get_form_id(){
        $data['form_id'] = request()->input('formId','');
        $data['channel'] = 'other';
        $id = request()->input('id','');
        $data['form_user'] = 'member_'.$id;
        $data['is_used'] = 0;
        $formIdModle = new FormId();
        $res = $formIdModle->addData($data);
        return $this->return_result($this->result);
    }

    //获取管理员信息
    public function getUsers(){
        $params = request()->post();
        //判断必传参数
        if(!isset($params['code']) || trim($params['code']) == ''){
            return $this->return_result(ErrorCode::$api_enum['fail'],'code不能为空');
        }
        $con = Configs::first();
        if(isset($params['wxType'])&&trim($params['wxType'])==1){
            $url = "https://api.weixin.qq.com/sns/jscode2session?";
            $url .= "appid=".$con->member_wechat_appid;
            $url .= "&secret=".$con->member_wechat_secret;
            $url .= "&js_code=".$params['code'];
            $url .= "&grant_type=authorization_code";
            $res = file_get_contents($url);  //请求微信小程序获取用户接口
            $params001 = json_decode($res,true);

            if (isset($params001['errcode']) && !empty($params001['errcode'])) {
                $this->result['status'] = 1;
                $this->result['msg'] = "请求微信接口报错！！！请联系管理员...";
                return $this->return_result($this->result);
            }
            $admin = AdminUser::where('openid','=',$params001['openid'])->first();
            if($admin){
                if($admin["status"] != "0"){
                    $this->result['status'] = 1;
                    $this->result["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
                }else {
                    $admin = json_decode(json_encode($admin),true);
                    $this->result['data'] = $admin;
                    unset($admin);
                }
            }else{
                $this->result['data'] = array(
                    'openid'=> $params001['openid']
                );
            }
        }else{
            $res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->qy_appid);
            $ar = json_decode($res,true);
            $access_token	=	$ar['access_token'];
            $url = "https://qyapi.weixin.qq.com/cgi-bin/miniprogram/jscode2session?";
            $url .= "&access_token=".$access_token;
            $url .= "&js_code=".$params['code'];
            $url .= "&grant_type=authorization_code";
            $res = file_get_contents($url);  //请求微信小程序获取用户接口
            $params001 = json_decode($res,true);
            if (isset($params001['errcode']) && !empty($params001['errcode'])) {
                $this->result['status'] = 1;
                $this->result['msg'] = "请求微信接口报错！！！请联系管理员...";
                return $this->return_result($this->result);
            }
            $admin = AdminUser::where('openid','=',$params001['userid'])->first();
            if($admin){
                if($admin["status"] != "0"){
                    $this->result['status'] = 1;
                    $this->result["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
                }else {
                    $this->result['data'] = $admin->toArray();
                }
            }else{
                $this->result['data'] = array(
                    'openid'=> $params001['userid']
                );
            }
        }
        return $this->return_result($this->result);
    }

    //绑定管理员
    public function setUsers(){
        $params = request()->post();
        //判断必传参数
        if(!isset($params['username']) || trim($params['username']) == ''){
            return $this->return_result(ErrorCode::$api_enum['fail'],'username');
        }
        if(!isset($params['user_pass']) || trim($params['user_pass']) == ''){
            return $this->return_result(ErrorCode::$api_enum['fail'],'user_pass');
        }
        $data['openid'] = $params['openid'];
        $adminModel = new AdminUser();
        $admin_id = $adminModel->validateAdminAccount($params['username'],$params['user_pass']);
        if($admin_id<1){
            $this->result['status'] = 1;
            $this->result['msg'] = '账号或密码错误';
            return $this->return_result($this->result);
        }
        //去查询有没有管理占用此openid
        $old_admin_user = AdminUser::where('openid',$params['openid'])->select('id')->first();
        $old_admin_user = json_decode(json_encode($old_admin_user),true);
        if ($old_admin_user){
            $where['openid'] = '';
            AdminUser::where('id',$old_admin_user['id'])->update($where);
        }
        //绑定新用户
        $res = AdminUser::where('id',$admin_id)->update($data);
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '绑定失败';
            return $this->return_result($this->result);
        }
        return $this->return_result($this->result);
    }

    //公司简介
    public function companyInfo(){
        $articlesModel = new Articles();
        $res = $articlesModel->getEnterprise();
        if (!$res){
            $this->result['data'] = [];
        }else{
            $res['picture'] = $this->processingPictures($res['picture']);
            $this->result['data'] = $res;
        }
        return $this->return_result($this->result);
    }

    //服务热线
    public function hotlineList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $pageNo = request()->input('pageNo',1);
        $pageSize = request()->input('pageSize',10);
        $params = [
            'start' => ($pageNo - 1)*$pageSize,
            'pageSize' => $pageSize,
            'sortName' => request()->input('sortName','id'),
            'sortOrder' => request()->input('sortOrder','desc'),
            'status' => request()->input('status',1),
            'search' => request()->input('search',''),
        ];
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->getList($params);
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    //图片 视频 路径处理
    private function processingPictures($url){
        global $scf_data;
        if (!$url){
            return $url;
        }
        //去除路径一个点的字符
        if(substr($url,0,1) == '.'){
            $url = substr($url,1,(strlen($url)-1));
        }
        if(substr($url,0,1) != '/' && substr($url,0,1) != 'h'){
            $url = '/'.$url;
        }
        if(strstr($url,"http://")){
            $url = str_ireplace('http://','https://',$url);
        }
        if(!strstr($url,"https")){
            if ($scf_data['IS_SCF'] == true) {
	        $host = 'https://'.$scf_data['system']['bucketConfig']['bucket'].'.cos.'.$scf_data['system']['bucketConfig']['region'].'.myqcloud.com';
                $url = $host.$url;
            }else{
                $url = 'https://'.$_SERVER['SERVER_NAME'].$url;
            }
        }
        return $url;
    }

}
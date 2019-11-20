<?php

namespace App\Models;

use App\Http\Config\WechatAPI;
use App\Library\Tools;
use App\Models\Admin\Configs;
use App\Models\Admin\Withdrawal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Wechat extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function Conf()
    {
        $configModel = new Configs();
        $res = $configModel->getConfigByID();
        return $res;
    }

    public function getToken($type)
    {
        $data = Cache::get($type.'_access_token');
        if($data==null){
            $config = self::Conf();
            if($type=='wechat'){
                $url = '';
            }elseif ($type=='qywx'){
                $url = WechatAPI::$qywx_conf['get_token'].'?corpid='.$config['company_id'].'&corpsecret='.$config['tongxl_secret'];
            }else{
                $url = '';
            }
            $get_token_res = file_get_contents($url);
            $get_token_res = json_decode($get_token_res,true);
            Log::info('get token res:',array('result'=>$get_token_res));
            Cache::forever($type.'_access_token',json_encode(['access_token'=>$get_token_res['access_token'],'expire_time'=>time()]));
            return $get_token_res['access_token'];
        }
        $tmp_data = json_decode($data,true);
        if(!isset($tmp_data['expire_time']) || ($tmp_data['expire_time']-300)<time()){
            $config = self::Conf();
            if($type=='wechat'){
                $url = '';
            }elseif ($type=='qywx'){
                $url = WechatAPI::$qywx_conf['get_token'].'?corpid='.$config['company_id'].'&corpsecret='.$config['tongxl_secret'];
            }else{
                $url = '';
            }
            $get_token_res = file_get_contents($url);
            $get_token_res = json_decode($get_token_res,true);
            Log::info('get token res:',array('result'=>$get_token_res));
            Cache::forever($type.'_access_token',json_encode(['access_token'=>$get_token_res['access_token'],'expire_time'=>time()]));
            return $get_token_res['access_token'];
        }
        return $tmp_data['access_token'];
    }

    /* 邀请企业用户 */
    public function inviteQYUser($fields)
    {
        $access_token = $this->getToken('qywx');
        $url = WechatAPI::$qywx_conf['user_invite'].'?access_token='.$access_token;
        $data = json_encode($fields,JSON_UNESCAPED_UNICODE);
        $result = Tools::curl($url,$data);
        return json_decode($result,true);
    }

    /* 企业账号 [获取/增加/修改/删除/批量删除] */
    public function buildQYUserWechat($fields,$action)
    {
        $access_token = $this->getToken('qywx');
        if($action == 'create'){
            $url = WechatAPI::$qywx_conf['user_create'].'?access_token='.$access_token;
        }elseif ($action == 'update'){
            $url = WechatAPI::$qywx_conf['user_update'].'?access_token='.$access_token;
        }elseif ($action == 'delete'){
            $url = WechatAPI::$qywx_conf['user_delete'].'?access_token='.$access_token.'&userid='.$fields['userid'];
            $result = file_get_contents($url);
            return json_decode($result,true);
        }elseif ($action == 'batch_delete'){
            $url = WechatAPI::$qywx_conf['user_batch_delete'].'?access_token='.$access_token;
        }elseif ($action == 'get'){
            $url = WechatAPI::$qywx_conf['user_get'].'?access_token='.$access_token.'&userid='.$fields['userid'];
            $result = file_get_contents($url);
            return json_decode($result,true);
        }elseif ($action == 'branch_user'){
            $url = WechatAPI::$qywx_conf['branch_user'].'?access_token='.$access_token;
        }elseif ($action == 'branch_user_detail'){
            $url = WechatAPI::$qywx_conf['branch_user_detail'].'?access_token='.$access_token;
        }elseif ($action == 'user_openid'){
            $url = WechatAPI::$qywx_conf['user_openid'].'?access_token='.$access_token;
        }
        $data = json_encode($fields,JSON_UNESCAPED_UNICODE);
        $result = Tools::curl($url,$data);
        return json_decode($result,true);
    }

    /* 企业部门 [获取/增加/修改/删除/批量删除] */
    public function buildQYBranchWechat($fields,$action)
    {
//        $access_token = $this->getToken('qywx');
//        if($action == 'create'){
//            $url = WechatAPI::$qywx_conf['branch_create'].'?access_token='.$access_token;
//        }elseif ($action == 'update'){
//            $url = WechatAPI::$qywx_conf['branch_update'].'?access_token='.$access_token;
//        }elseif ($action == 'delete'){
//            $url = WechatAPI::$qywx_conf['branch_delete'].'?access_token='.$access_token;
//        }elseif ($action == 'get'){
//            $url = WechatAPI::$qywx_conf['branch_get'].'?access_token='.$access_token;
//        }else{
//            $url = WechatAPI::$qywx_conf['branch_detail_get'].'?access_token='.$access_token;
//        }
//        $data = json_encode($fields,JSON_UNESCAPED_UNICODE);
//        $result = Tools::curl($url,$data);
//        return json_decode($result,true);
    }

    /* 企业邮箱 [增加/修改/删除] */
    public function buildQYEmail($fields,$action)
    {
        $access_token = $this->getToken('qywx');
        if($action == 'create'){
            $url = WechatAPI::$qywx_conf['qyemail_create'].'?access_token='.$access_token;
        }elseif ($action == 'update'){
            $url = WechatAPI::$qywx_conf['qyemail_update'].'?access_token='.$access_token;
        }else{
            $url = WechatAPI::$qywx_conf['qyemail_delete'].'?access_token='.$access_token.'&userid='.$fields['userid'];
            $result = file_get_contents($url);
            return json_decode($result,true);
        }
        $data = json_encode($fields,JSON_UNESCAPED_UNICODE);
        $result = Tools::curl($url,$data);
        return json_decode($result,true);
    }

    /* 企业微信转账 */
    public function payment($data)
    {
        $config = self::Conf();
        $arr = array(
            'amount' => $data['bonus_money'] * 100,
            'appid' => $config['company_id'],
            'desc' => '售后提成',
            'mch_id' => $config['qy_mch_id'],
            'nonce_str' => Tools::createNoncestr(),
            'openid' => $data['openid'],
            'partner_trade_no' => $data['order_number'],
            'ww_msg_type' => 'NORMAL_MSG'
        );
        $arr['workwx_sign'] = $this->makeSign($arr,['column' => 'secret', 'value' => $config['qy_pay_secret']]);  //企业微信支付secret
        $arr['check_name'] = 'NO_CHECK';
        $arr['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];
        $arr['act_name'] = '售后提成';
        $arr['sign'] = $this->makeSign($arr,['column' => 'key', 'value' => $config['qy_wx_pay_key']]);//此处value填写商户号的API密钥
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/paywwsptrans2pocket';
        $xmlData = Tools::arrayToXml($arr);
        $res = Tools::xmlToArray($this->postXmlCurl($xmlData,$url,60));
        if($res['return_code']=='SUCCESS' && $res['result_code']=='SUCCESS'){
            $i_data['pay_number'] = $res['payment_no'];
            $i_data['pay_time'] = $res['payment_time'];
            $withdrawalModel = new Withdrawal();
            $fields = ['column' => 'id', 'value' => $data['id']];
            $withdrawalModel->takeMoneyUpdate($fields,$i_data);
            return true;
        }else{
            Log::info("企业微信转账失败结果：".var_export($res,1));
            return ["code" => 1, "msg" => $res["return_msg"] . "，$res[err_code_des]"];
        }
    }

    /* post传输xml格式curl函数 */
    public static function postXmlCurl($xml, $url, $second = 30)
    {
        $header[] = "Content-type: text/xml";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验

        curl_setopt($ch, CURLOPT_SSLCERT,$_SERVER['DOCUMENT_ROOT'].'/key/apiclient_cert.pem');
        curl_setopt($ch, CURLOPT_SSLKEY,$_SERVER['DOCUMENT_ROOT'].'/key/apiclient_key.pem');
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            curl_close($ch);
            return "curl出错，错误码:$error";
        }
    }

    /* 生成签名 */
    public function makeSign($fields,$extend)
    {
        //签名步骤一：按字典序排序参数
        ksort($fields);
        $sign = '';
        foreach ($fields as $key => $value) {
            $sign .= $key . '=' . $value . '&';
        }
        $sign_str = '';
        if (strlen($sign) > 0) {
            $sign_str = substr($sign, 0, strlen($sign) - 1);
        }
        //签名步骤二：在string后加入secret
        $sign_str = $sign_str.'&'.$extend['column'].'='.$extend['value'];
        //签名步骤三：MD5加密
        $sign_str = md5($sign_str);
        //签名步骤四：所有字符转为大写
        $sign_str = strtoupper($sign_str);
        return $sign_str;
    }
}

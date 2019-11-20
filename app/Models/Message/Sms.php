<?php

namespace App\Models\Message;

use App\Models\MessageBase;

use App\Models\Admin\Configs;


//var_dump(__DIR__."/../../../");exit();


use Qcloud\Sms\SmsSingleSender;
use Qcloud\Sms\SmsMultiSender;
use Qcloud\Sms\SmsVoiceVerifyCodeSender;
use Qcloud\Sms\SmsVoicePromptSender;
use Qcloud\Sms\SmsStatusPuller;
use Qcloud\Sms\SmsMobileStatusPuller;

use Qcloud\Sms\VoiceFileUploader;
use Qcloud\Sms\FileVoiceSender;
use Qcloud\Sms\TtsVoiceSender;
use Illuminate\Support\Facades\Log;



class Sms extends MessageBase
{
    private $config;
    protected $msg_type_id = 1;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->config = $this->_getConfig();
    }

    public function send($data = null)
    {
        // default send demo
//        $data = array(
//            'content' => $this->stringFormat('恭喜您成功注册微购儿，您的账号为本机号码，初始密码是：{0}，请您尽快登陆网站(http://www.wegouer.com)修改初始密码!',"test"),
//            'template_id' => null,
//            'sms_sign' => null,
//            'phone_number' => '12000000000' //字符串 单发 / 数组 群发
//        );

        //special send demo
//        $data = array(
//            'params' => array('params1','params2','paramsxxx'),
//            'content' => null,
//            'template_id' => 7839,
//            'sms_sign' => '腾讯云',
//            'phone_number' => ['12000000000','12100000000','12300000000'] //字符串 单发 / 数组 群发
//        );
        $data = array(
            'content' => $this->stringFormat('恭喜您成功注册微购儿，您的账号为本机号码，初始密码是：{0}，请您尽快登陆网站(http://www.wegouer.com)修改初始密码!',"test"),
            'template_id' => null,
            'sms_sign' => null,
            'phone_number' => '15118102653' //字符串 单发 / 数组 群发
        );
        if($data['template_id']!=null && $data['sms_sign']!=null){
            try {
                $msender = new SmsMultiSender($this->config['appId'], $this->config['appKey']);
                $params = $data["params"];
                $result = $msender->sendWithParam("86", $data['phone_number'], $data['template_id'], $params, $data['sms_sign'], "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
                Log::info('specail sms send success:',array('result'=>$result));
                return true;
            } catch(\Exception $e) {
                Log::debug('specail sms send fail:',array('result'=>json_encode($e)));
                return false;
            }
        }
        try {
            $ssender = new SmsSingleSender($this->config['appId'], $this->config['appKey']);
            $result = $ssender->send(0, "86", $data['phone_number'],$data['content'], "", "");
            Log::info('default sms send success:',array('result'=>$result));
            return true;
        } catch(\Exception $e) {
            Log::debug('default sms send fail:',array('result'=>json_encode($e)));
            return false;
        }
    }

    private function _afterSendEvent($data)
    {
        $fields = array(

        );
        if(is_array()){

        }
    }

    public function stringFormat($str)
    {
        // replaces str "Hello {0}, {1}, {0}" with strings, based on
        // index in array
        $numArgs = func_num_args () - 1;

        if ($numArgs > 0) {
            $arg_list = array_slice ( func_get_args (), 1 );

            // start after $str
            for($i = 0; $i < $numArgs; $i ++) {
                $str = str_replace ( "{" . $i . "}", $arg_list [$i], $str );
            }
        }

        return $str;
    }

    private function _getConfig()
    {
        $con = Configs::first();
        if(!$con){
            return false;
        }
        $data = array(
            'appId' => $con->sms_appid,
            'appKey' => $con->sms_appkey
        );
        return $data;
    }
}


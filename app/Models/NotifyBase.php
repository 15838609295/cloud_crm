<?php

namespace App\Models;

use App\Library\Tools;
use App\Models\Admin\Configs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;


class NotifyBase extends Model
{
    public static $message_enum = array(
        'ach_success' => '',
        'ach_fail' => '',
        'assign_customer' => '{0}! {1}指派给你一个新的客户{2} 电话：{3} 备注：{4}'
    );

    public function sendMail($params)
    {
        $allow_url_rules = array(
            'wstianxia.com',
            'wegouer.com',
            'netbcloud.com',
            'wangqudao.com',
            'qcloud0755.com'
        );
        $flag = false;
        foreach ($allow_url_rules as $key=>$value){
            if(strpos($params['receive_user'],$value)===false){
                continue;
            }
            $flag = true;
        }
        if(!$flag){
            return false;
        }
        switch ($params['title']){
            case '业绩订单审核成功':
                $symbol = 'mail.ach_success';
                break;
            case '业绩订单审核失败':
                $symbol = 'mail.ach_fail';
                break;
            case '指派客户通知':
                $symbol = 'mail.assign_customer';
                break;
            default:
                $symbol = null;
                break;
        }
        $receive_user = $params['receive_user'];
        $title = $params['title'];
        Mail::send($symbol,
            $params['options'],
            function($message) use ($receive_user,$title){
                $message->to($receive_user)->subject($title);
            }
        );
        if(count(Mail::failures()) > 1){
            return false;
        }
        return true;
    }

    public function sendQYWechat($params)
    {

        $msg_template = self::$message_enum[$params['type']];
        switch ($params['type']){
            case 'ach_success':
                $content = $params['content'];
                break;
            case 'ach_fail':
                $content = '';
                break;
            case 'assign_customer':
                $content = Tools::stringFormat($msg_template,$params['receive_uname'],$params['send_uame'],$params['uname'],$params['umobile'],$params['remark']);
                break;
            default:
                $content = '';
                break;
        }
        if(!Cache::has('xiaoxi_access_token')){
            $con = Configs::first();
            $res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->push_secret);
            $arr = json_decode($res,true);
            Cache::add('xiaoxi_access_token',$arr['access_token'],120);//键 值 有效时间（分钟）
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
        $data =array(
            'touser' => $params['receive_wechatid'],
            'msgtype' => 'text',
            'agentid' => 1000011,
            'text' => array('content' => $content),
            "safe"=> 0
        );
        $data = json_encode($data);
        $tools = new Tools();
        $res = $tools->request($url,$data);
        Log::info('wechat send result:',['return_info' => json_encode($res)]);
        return true;
    }
}

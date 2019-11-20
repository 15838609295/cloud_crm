<?php

namespace App\Http\Config;


class WechatAPI
{
    const IP = '127.0.0.1';
    const WECHAT_API_URL = 'https://api.weixin.qq.com';
    const QYWECHAT_API_URL = 'https://qyapi.weixin.qq.com';
    const QYWECHAT_EMAIL_API_URL = 'https://api.exmail.qq.com';
    const MCH_API_URL = 'https://api.mch.weixin.qq.com';
    /* 操作事件 */
    const EVENT_SUBSCRIBE = 'subscribe';       //订阅
    const EVENT_UNSUBSCRIBE = 'unsubscribe';   //取消订阅

    public static function wechatConf()
    {
        if(env('APP_DEBUG')){
            return array(
                'appid' => '',
                'appsecret' => '',
                'token' => '',
                'encodingaeskey' => ''
            );
        }
        return array(
            'appid' => '',
            'appsecret' => '',
            'token' => '',
            'encodingaeskey' => ''
        );
    }

    static $wechat_conf = [
        'get_token' => self::WECHAT_API_URL.'/cgi-bin/token?grant_type=client_credential',
        'user_info' => self::WECHAT_API_URL.'/cgi-bin/user/info?',
        'create_menu' => self::WECHAT_API_URL.'/cgi-bin/menu/create?',
        'get_ticket' => self::WECHAT_API_URL.'/cgi-bin/ticket/getticket?',
    ];

    static $miniwx_conf = [

    ];

    static $qywx_conf = [
        'get_token' => self::QYWECHAT_API_URL.'/cgi-bin/gettoken',                                                      //获取access_token
        'user_openid' => self::QYWECHAT_API_URL.'/cgi-bin/user/convert_to_openid',                                      //获得企业微信账号openid
        'user_get' => self::QYWECHAT_API_URL.'/cgi-bin/user/get',                                                       //获取企业微信账号
        'user_create' => self::QYWECHAT_API_URL.'/cgi-bin/user/create',                                                 //创建企业微信账号
        'user_update' => self::QYWECHAT_API_URL.'/cgi-bin/user/update',                                                 //修改企业微信账号
        'user_delete' => self::QYWECHAT_API_URL.'/cgi-bin/user/delete',                                                 //删除企业微信账号
        'qyemail_create' => self::QYWECHAT_EMAIL_API_URL.'/cgi-bin/user/create',                                        //创建企业邮箱
        'qyemail_update' => self::QYWECHAT_EMAIL_API_URL.'/cgi-bin/user/update',                                        //修改企业邮箱
        'qyemail_delete' => self::QYWECHAT_EMAIL_API_URL.'/cgi-bin/user/delete',                                        //删除企业邮箱
        'user_invite' => self::QYWECHAT_API_URL.'/cgi-bin/batch/invite',                                                //邀请用户
        'payment' => self::MCH_API_URL.'/mmpaymkttransfers/promotion/paywwsptrans2pocket'                               //商家转账
    ];
}

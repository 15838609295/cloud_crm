<?php

namespace App\Http\Config;


class ErrorCode
{
    public static function enum($type)
    {
        switch ($type){
            case 'admin':
                return self::$admin_enum;
            case 'web':
                return self::$web_enum;
            case 'api':
                return self::$api_enum;
            default:
                return false;
        }
    }

    static $admin_enum = [
        'success'           => ['code'=>0,'msg'=>'成功'],

        'fail'               => ['code'=>99,'msg'=>'失败'],
        'addfail'            => ['code'=>1,'msg'=>'添加失败'],
        'delfail'            => ['code'=>1,'msg'=>'删除失败'],
        'modifyfail'        => ['code'=>1,'msg'=>'修改失败'],
        'uploadfail'        => ['code'=>1,'msg'=>'上传失败'],
        'request_error'     => ['code'=>100,'msg'=>'请求方式错误'],
        'params_error'      => ['code'=>103,'msg'=>'参数错误'],
        'error'             => ['code'=>105,'msg'=>'错误'],
        'not_exist'         => ['code'=>106,'msg'=>'数据不存在'],
        'account_error'     => ['code'=>200,'msg'=>'账号或密码错误'],
        'token_expire'      => ['code'=>201,'msg'=>'用户凭证无效或已过期'],
        'auth_fail'         => ['code'=>205,'msg'=>'无访问权限'],
        'mobile_exist'      => ['code'=>311,'msg'=>'手机号已存在'],
        'email_exist'       => ['code'=>312,'msg'=>'邮箱已存在'],
        'openid_exist'      => ['code'=>313,'msg'=>'openid已存在'],
        'unionid_exist'     => ['code'=>314,'msg'=>'unionid已存在'],
        'id_card_error'     => ['code'=>321,'msg'=>'身份证错误'],
        'not_branch_user'   => ['code'=>331,'msg'=>'非团队成员'],
        'customized'        => ['code' => 1, 'msg' => '%s'],
    ];

    static $web_enum = [
        'success'           => ['code'=>0,'msg'=>'成功'],
        'fail'              => ['code'=>99,'msg'=>'失败'],
        'request_error'     => ['code'=>100,'msg'=>'请求方式错误'],
        'params_error'      => ['code'=>103,'msg'=>'参数错误'],
        'error'             => ['code'=>105,'msg'=>'错误'],
        'not_exist'         => ['code'=>106,'msg'=>'数据不存在'],
        'account_error'     => ['code'=>200,'msg'=>'账号或密码错误'],
        'token_expire'      => ['code'=>201,'msg'=>'用户凭证无效或已过期'],
        'auth_fail'         => ['code'=>205,'msg'=>'无访问权限'],
        'mobile_exist'      => ['code'=>311,'msg'=>'手机号已存在'],
        'email_exist'       => ['code'=>312,'msg'=>'邮箱已存在'],
        'openid_exist'      => ['code'=>313,'msg'=>'openid已存在'],
        'unionid_exist'     => ['code'=>314,'msg'=>'unionid已存在'],
        'id_card_error'     => ['code'=>321,'msg'=>'身份证错误'],
        'not_branch_user'   => ['code'=>331,'msg'=>'非团队成员']
    ];

    static $api_enum = [
        'success'           => ['status'=>0,'msg'=>'成功'],
        'fail'              => ['status'=>1,'msg'=>'失败'],
        'request_error'     => ['status'=>1,'msg'=>'请求方式错误'],
        'params_error'      => ['status'=>1,'msg'=>'$s参数错误'],
        'params_not_exist'  => ['status'=>1,'msg'=>'%s参数不存在'],
        'token_expire'      => ['status'=>1,'msg'=>'用户凭证无效或已过期'],
        'customized'        => ['status' => 1, 'msg' => '%s'],
    ];
    static $wsy_enum = [
        'not_bind'            => ['status'=> 202, 'msg' => '账号未绑定'],
        'params_not_exist'   => ['status'=>1,'msg'=>'%s参数不存在'],
        'customized'         => ['status' => 1, 'msg' => '%s'],
        'mobile_exist'       => ['status'=>311,'msg'=>'手机号已存在'],
        'not_openid'         => ['status'=>301,'msg'=>'openid不能为空'],
    ];

    static $common_enum = [
        'upload_fail'       => ['code'=>501,'msg'=>'上传失败'],
    ];
}

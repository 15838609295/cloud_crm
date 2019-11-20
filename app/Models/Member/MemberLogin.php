<?php

namespace App\Models\Member;


use App\Http\Config\ErrorCode;
use Illuminate\Database\Eloquent\Model;

class MemberLogin extends Model
{
    private static $returnData = array('code'=>0,'msg'=>'登录成功','data'=>[]);

    public static function LoginAction($fields)
    {
        $userBaseModel = new MemberBase();
        $member_id = $userBaseModel->validateMemberAccount($fields['user_name'],$fields['user_pass']);
        if(!$member_id){
            self::$returnData = ErrorCode::$web_enum['account_error'];
            return self::$returnData;
        }elseif ($member_id < 0){
            self::$returnData = ErrorCode::$web_enum['auth_fail'];
            return self::$returnData;
        }
        $userSessionModel = new MemberSession();
        $session_id = $userSessionModel->setSession(['member_id'=>$member_id,'msg'=> '']);
        if(!$session_id){
            self::$returnData = ErrorCode::$web_enum['fail'];
            return self::$returnData;
        }
        self::$returnData['data'] = array('token'=>$session_id);
        return self::$returnData;
    }

    public static function LogoutAction($token){

        $MemberSessionModel = new MemberSession();
        $res = $MemberSessionModel->getSession($token);
        if(!$res){
            self::$returnData = ErrorCode::$web_enum['token_expire'];
            return self::$returnData;
        }
        $res = $MemberSessionModel->sessionDelete($token);
        if(!$res){
            self::$returnData = ErrorCode::$web_enum['fail'];
            self::$returnData['msg'] = '注销失败';
            return self::$returnData;
        }
        self::$returnData['msg'] = '注销成功';
        return self::$returnData;
    }
}

<?php

namespace App\Models\User;

use App\Http\Config\ErrorCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserLogin extends Model
{
    private static $returnData = array('code'=>0,'msg'=>'登录成功','data'=>[]);

    public static function LoginAction($fields)
    {
        $userBaseModel = new UserBase();
        $admin_id = $userBaseModel->validateAdminAccount($fields['user_name'],$fields['user_pass']);
        if($admin_id === -1){
            $returnData = ErrorCode::$admin_enum['account_error'];
            $returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
            return $returnData;
        }
        if(!$admin_id){
            $returnData = ErrorCode::$admin_enum['account_error'];
            return $returnData;
        }
        $userSessionModel = new UserSession();
        $session_id = $userSessionModel->setSession(['admin_id'=>$admin_id]);
        if(!$session_id){
            $returnData = ErrorCode::$admin_enum['fail'];
            $returnData['msg'] = '登录失败';
            return $returnData;
        }
        $returnData['code'] = 0;
        $returnData['msg'] = '登录成功';
        $returnData['data'] = array('token'=>$session_id);
        return $returnData;
    }

    public static function QyWexinAction($fields)
    {
        //sleep(2);
        $userSessionModel = new UserSession();
        $sessionData = $userSessionModel->getSessionByCode($fields['code']);
        if(!$sessionData){
            self::$returnData = ErrorCode::$admin_enum['fail'];
            self::$returnData['msg'] = '登录失败';
            self::$returnData['data'] = array('token'=>null);
            return self::$returnData;
        }
        self::$returnData['data'] = array('token'=>$sessionData["session_id"]);
        return self::$returnData;
    }

    public static function LogoutAction($token)
    {
        $userBaseModel = new UserSession();
        $res = $userBaseModel->getSession($token);
        if(!$res){
            self::$returnData = ErrorCode::$admin_enum['token_expire'];
            return self::$returnData;
        }
        $res = $userBaseModel->sessionDelete($token);
        if(!$res){
            self::$returnData = ErrorCode::$admin_enum['fail'];
            self::$returnData['msg'] = '注销失败';
            return self::$returnData;
        }
        self::$returnData['msg'] = '注销成功';
        return self::$returnData;
    }
}


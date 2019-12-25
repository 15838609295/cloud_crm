<?php

namespace App\Models\User;

use App\Http\Config\ErrorCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserLogin extends Model
{
    public static function LoginAction($fields)
    {
        $userBaseModel = new UserBase();
        $admin_id = $userBaseModel->validateAdminAccount($fields['user_name'],$fields['user_pass']);
        if($admin_id === -1){
            $returnData['code'] = ErrorCode::$admin_enum['account_error'];
            $returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
            return $returnData;
        }
        if(!$admin_id){
            $returnData= ErrorCode::$admin_enum['account_error'];
            return $returnData;
        }
        $userSessionModel = new UserSession();
        $session_id = $userSessionModel->setSession(['admin_id'=>$admin_id]);
        if(!$session_id){
            $returnData = ErrorCode::$admin_enum['fail'];
            $returnData['msg'] = '登录失败';
            return $returnData;
        }
        $data['code'] = 0;
        $data['msg'] = '登录成功';
        $data['data'] = ['token' => $session_id];
        return $data;
    }

    public static function QyWexinAction($fields)
    {
        //sleep(2);
        $userSessionModel = new UserSession();
        $sessionData = $userSessionModel->getSessionByCode($fields['code']);
        if(!$sessionData){
            $returnData['code'] = ErrorCode::$admin_enum['fail'];
            $returnData['msg'] = '登录失败';
            $returnData['data'] = array('token'=>null);
            return $returnData;
        }
        $data['code'] = 0;
        $data['msg'] = '登录成功';
        $data['data'] = ['token' => $sessionData["session_id"]];
        return $data;
    }

    public static function LogoutAction($token)
    {
        $userBaseModel = new UserSession();
        $res = $userBaseModel->getSession($token);
        if(!$res){
            $returnData = ErrorCode::$admin_enum['token_expire'];
            return $returnData;
        }
        $res = $userBaseModel->sessionDelete($token);
        if(!$res){
            $returnData['code'] = ErrorCode::$admin_enum['fail'];
            $returnData['msg'] = '注销失败';
            return $returnData;
        }
        $data['code'] = 0;
        $data['msg'] = '注销成功';
        return $data;
    }
}


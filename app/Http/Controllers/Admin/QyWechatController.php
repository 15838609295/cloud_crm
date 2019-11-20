<?php

namespace App\Http\Controllers\Admin;

use App\Models\User\UserSession;
use Illuminate\Http\Request;
use App\Models\Admin\AdminUser;
use App\Models\Admin\Configs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class QyWechatController extends Controller
{
    //企业微信登录跳转验证
    public function qywechat_login(Request $request){
        if(!$request->input('code')){
            Log::info("qyWxError: code Empty");
            return redirect('/index.html');
        }
        $code = $request->input('code');
        $con = Configs::first();
        $res = file_get_contents('https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$con->company_id.'&corpsecret='.$con->push_secret);
        $arr = json_decode($res,true);
        $members = file_get_contents('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$arr["access_token"].'&code='.$code);
        $user = json_decode($members,true);
        if(!isset($user["UserId"])){
            Log::info("qyWxError: userId error");
            return redirect('/index.html');
        }
        $data = AdminUser::where('wechat_id','=',$user['UserId'])->select("id", 'status')->first();

        if(!isset($data["id"])){
            Log::info("qyWxError: userInfo No");
            return redirect('/index.html');
        }
        if($data["status"] != "0"){
            //Log::info("您的账号已被禁用，无法登陆，如有疑问，请联系管理员！");
            return redirect('/index.html#/login?msg=您的账号已被禁用，无法登陆，如有疑问，请联系管理员！&');
        }
        $userSessionModel = new UserSession();
        $session_id = $userSessionModel->setSession(['admin_id'=>$data["id"]], $code);
        if(!$session_id){
            Log::info("qyWxError: write login record error");
            return redirect('/index.html');
        }
//        return redirect('/admin/#/login?code='.$code . "&");
        return redirect('/index.html#/login?code='.$code . "&");
//        Auth::guard('admin')->loginUsingId($bool->id);
//        return redirect('/admin/index');
    }

    //个人微信登录跳转验证
    public function wechat_login(Request $request)
    {
        if($request->input('code')){
            return redirect('/admin/logout')->withErrors("跳转异常！！！！");
        }
        $code = $request->input('code');
        $res = file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.''.'&secret='.''.'&code='.$code.'&grant_type=authorization_code');
        $arr = json_decode($res,true);
        $members = file_get_contents('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token='.$arr["access_token"].'&code='.$code);
        $user = json_decode($members,true);
        $bool = AdminUser::where('wechat_id','=',$user['UserId'])->first();
        if(!$bool){
            return redirect('/admin/logout')->withErrors("查不到用户！！！！");
        }
        Auth::guard('admin')->loginUsingId($bool->id);
        return redirect('/admin/index');
    }

}

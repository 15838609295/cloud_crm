<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Library\Tools;
use App\Models\Admin\Configs;
use App\Models\User\UserLogin;
use App\Models\User\UserSession;
use App\Models\Validate\ValidBase;
use Illuminate\Http\Request;
use Validator;

class LoginController extends Controller
{
    public function __construct(){
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*--- end 跨域测试用---*/
    }

    private $returnData = array(
        'code' => 0,
        'msg' => '请求成功',
        'data' => '',
    );

    public function rule(){
        return [
            'user_name' => 'required',
//            'user_name' => 'required|email',
            'user_pass' => 'required|regex:/^.{6,16}$/',
        ];
    }

    public function message(){
        return [
            'user_name.required' => '账号不能为空',
//            'user_name.email' => '账号格式错误',
            'user_pass.required' => '密码不能为空',
            'user_pass.regex' => '密码格式不正确,请输入6~16位的密码'
        ];
    }

    public function login(Request $request){
        //登陆之前去检测站点状态
        $con = Configs::first();
        $site_status = $con->site_status;
        $verify_arr = array(
            'user_name' => $request->username,
            'user_pass' => $request->password
        );
        if ($site_status == 0 && $verify_arr['user_name'] != 'root@admin.com'){
            $data['code'] = 1;
            $data['msg'] = '站点正在维护中，请等待片刻再尝试登陆';
            return response()->json($data);
        }
        $validator = Validator::make($verify_arr,$this->rule(),$this->message());//验证参数
        if ($validator->fails()) {
            $this->returnData['code'] = 103;
            $this->returnData['msg'] = $validator->errors()->all()[0];
            return response()->json($this->returnData);
        }
        $login_res = UserLogin::LoginAction($verify_arr);
        return response()->json($login_res);
    }

    public function qyWexin(Request $request){
        //登陆之前去检测站点状态
        $con = Configs::first();
        $site_status = $con->site_status;
        $verify_arr = array(
            'code' => $request->code,
        );
        if ($site_status == 0){
            $data['code'] = 1;
            $data['msg'] = '站点正在维护中，请等待片刻再尝试登陆';
            return response()->json($data);
        }
        $validator = Validator::make($verify_arr,['code' => 'required'],["code.required" => "code不能为空"]);//验证参数
        if ($validator->fails()) {
            $this->returnData['code'] = 103;
            $this->returnData['msg'] = $validator->errors()->all()[0];
            return response()->json($this->returnData);
        }
        $login_res = UserLogin::QyWexinAction($verify_arr);
        return response()->json($login_res);
    }

    public function logout(Request $request){
        $verify_arr = array(
            'token' => $request->token
        );
        $validModel = ValidBase::factory('ValidRule');
        $verify_res = $validModel->c_token($verify_arr);
        if($verify_res['code']>0){
            return response()->json($verify_res);
        }
        $login_res = UserLogin::LogoutAction($request->token);
        return response()->json($login_res);
    }

    //平台免登陆
    public function Nolandfall(Request $request){
        $wsToken = $request->post('wsToken','');
        $url = $request->post('wsDomain','');
        if ($wsToken == '' || $url == ''){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '错误登陆';
        }
        $url = urldecode($url);
        $res = Tools::curl($url."/web/home/getCheckToken", json_encode(["wsToken" => $wsToken]));
        $res = json_decode($res, 1);
        if($res["code"] == "0"){
            $userSessionModel = new UserSession();
            $session_id = $userSessionModel->setSession(['admin_id'=>1]);
            $this->returnData['data'] = ['token' => $session_id];
        }else{
            $this->returnData['code'] = 1;
            $this->returnData["msg"] = "自动登录失败";
        }
        return response()->json($this->returnData);
    }
}

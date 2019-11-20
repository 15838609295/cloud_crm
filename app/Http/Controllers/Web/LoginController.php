<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin\Configs;
use App\Models\Member\MemberLogin;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Qcloud\Sms\SmsSingleSender;
use Validator;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function __construct()
    {
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*--- end 跨域测试用---*/
    }
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    //定义返回信息
    private $returnData = array(
        'code' => 0,
        'msg' => '请求成功',
        'data' => '',
    );
    //验证规则
    public function rule()
    {
        return [
            'user_name' => 'required',
            'user_pass' => 'required|regex:/^.{6,16}$/',
        ];
    }
    //提示信息
    public function message()
    {
        return [
            'user_name.required' => '账号不能为空',
            'user_pass.required' => '密码不能为空',
            'user_pass.regex' => '密码格式不正确,请输入6~16位的密码'
        ];
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        //退出登录
        return redirect('/login');
    }

    //管理后台跳转代理商端
    public function ad_web_login($id){
        $data['member_id'] = $id;
        $data['msg'] = '后台跳转登录';
        $user_id = DB::table('member')->where('id','=',$id)->first();
        if ($user_id){
            $res = MemberBase::SetTokengetUserInfo($data);
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['token'] = $res;
            return response()->json($data);
        }else{
            $data['code'] = 1;
            $data['msg'] = '无此用户';
            return response()->json($data);
        }
    }

	public function login(Request $request) {

        $verify_arr = array(
            'user_name' => $request->username,
            'user_pass' => $request->password
        );
        $validator = Validator::make($verify_arr,$this->rule(),$this->message());//验证参数
        if ($validator->fails()) {
            $this->returnData['code'] = 103;
            $this->returnData['msg'] = $validator->errors()->all()[0];
            return response()->json($this->returnData);
        }
        $login_res = MemberLogin::LoginAction($verify_arr);
        return response()->json($login_res);
	}

	public function weblogout(Request $request){
        //设置过期时间
        $login_res = MemberLogin::LogoutAction($request->token);
        return response()->json($login_res);
    }

    //发送验证码
    public function sendCode(Request $request){
	    $con = Configs::first();
        require_once  base_path().'/vendor/qcloudsms_php-master/src/index.php';
        $mobile = $request->post('mobile','');
        // 短信应用SDK AppID
        $appid = $con->sms_appid; // 1400开头
        // 短信应用SDK AppKey
        $appkey = $con->sms_appkey;
        // 需要发送短信的手机号码
        $phoneNumbers = [$mobile];
        //短信模版
        $templateId = '296336';
        $memberModel = new MemberBase();
        if($request->post('type') == 1){    //1注册
            $res = $memberModel->getMemberByMobile($mobile);
            if ($res){
                $data['code'] = 1;
                $data['msg'] = '手机号已注册';
                return response()->json($data);
            }
        }else if($request->post('type') == 2){    //2修改密码
            $res = $memberModel->getMemberByMobile($mobile);
            if (!$res){
                $data['code'] = 1;
                $data['msg'] = '手机号未注册';
                return response()->json($data);
            }
        }
        // 签名
        $smsSign = "网商天下";
        // 指定模板ID单发短信
        $code = rand(1111,9999);
        $validate_res = DB::table('verification')->where('mobile',$mobile)->first();
        $validate_res = json_decode(json_encode($validate_res),true);
        if ($validate_res){
            $info['code'] = $code;
            $info['create_time'] = time();
            $info['overdue_time'] = time() + 300;
            DB::table('verification')->where('mobile',$mobile)->update($info);
        }else{
            $info['mobile'] = $mobile;
            $info['code'] = $code;
            $info['create_time'] = time();
            $info['overdue_time'] = time() + 300;
            DB::table('verification')->insert($info);
        }
        try {
            $ssender = new SmsSingleSender($appid, $appkey);
            $code_info = [$code];
            $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
                $code_info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
            $rsp = json_decode($result,true);
            if ($rsp['result'] == 0){
                $data['code'] = 0;
                $data['msg'] = '请求成功';
                $data['data']['result'] = 0;
                return response()->json($data);
            }else{
                $data['code'] = 1;
                $data['msg'] = '发送失败';
                $data['data']['result'] = 1;
                return response()->json($data);
            }

        } catch(\Exception $e) {
            $data['code'] = 1;
            $data['msg'] = '请求失败';
            $data['data']['result'] = 1;
            return response()->json($data);
        }
    }

    //验证用户输入的验证码
    public function checkVerification(Request $request){
        $mobile = $request->post('mobile','');
        $code = $request->post('code','');
        $res = DB::table('verification')->where('mobile',$mobile)->first();
        $res = json_decode(json_encode($res),true);
        if ($res['overdue_time'] < time()){
            $data['code'] = 0;
            $data['msg'] = '验证码过期';
            $data['data']['result'] = 2;
            return response()->json($data);
        }
        if ($res['code'] != $code){
            $data['code'] = 0;
            $data['msg'] = '验证码错误';
            $data['data']['result'] = 1;
            return response()->json($data);
        }else{
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['result'] = 0;
            return response()->json($data);
        }
    }

    //注册
    public function register(Request $request){
        $memberModel = new MemberBase();
        //添加用户信息
        $member['name']           = $request->post('mobile');           //名称
        $member['mobile']         = $request->post('mobile');           //手机号
        $member['password']       = bcrypt(123456);                   //密码
        $member['status']         = 1;                                       //客户状态
        $member['active_time']    = Carbon::now()->toDateTimeString();         //激活时间
        $member['create_time']    = Carbon::now()->toDateTimeString();         //注册时间
        $member['openid'] = '';                //openid
        $member['email'] = '';                                                  //默认

        //添加用户详情信息
        $member_extend['realname'] = '';
        $member_extend['avatar']  = '';
        $member_extend['type']  = 0;
        $member_extend['source']  = '';
        $member_extend['update_time']  = Carbon::now();
        $member_extend['recommend']  = 1;
        $member_extend['addperson']  = 'root';
        $member_extend['position']  = '';
        $member_extend['company']  = '';
        $member_extend['wechat']  = '';
        $member_extend['qq']  = '';
        $member_extend['source']  = '';
        $member_extend['project']  = '';
        $member_extend['remarks']  = '';

        $res = $memberModel->memberInsert($member,$member_extend);
        if(!$res){
            $data['code'] = 1;
            $data['msg'] = '添加失败';
            return response()->json($data);
        }
        $data['code'] = 1;
        $data['msg'] = '添加成功';
        $data['data'] = ['result'=>0];
        return response()->json($data);
    }

    //修改密码
    public function updatePassword(Request $request){
        $mobile = $request->post('mobile','');
        $new_passwoed = $request->post('password','');
        $res = DB::table('member')->where('mobile',$mobile)->first();
        if (!$res){
            $data['code'] = 1;
            $data['msg'] = '用户不存在';
            return response()->json($data);
        }
        $res = json_decode(json_encode($res),true);
        $where['password'] = bcrypt($new_passwoed);
        DB::table('member')->where('id',$res['id'])->update($where);
        $data['code'] = 0;
        $data['msg'] = '修改密码成功';
        return response()->json($data);
    }

}

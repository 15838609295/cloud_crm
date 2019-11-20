<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\Articles;
use App\Models\Admin\Configs;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{

    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");

    //获取客户信息
    public function getMember()
    {
        $params = request()->post();
        //判断必传参数
        if(!isset($params['code']) || trim($params['code']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], "code");                                                                 //返回必传参数为空
        }
        $con = Configs::first();
        $url = "https://api.weixin.qq.com/sns/jscode2session?";
        $url .= "appid=".$con->member_wechat_appid;
        $url .= "&secret=".$con->member_wechat_secret;
        $url .= "&js_code=".$params['code'];
        $url .= "&grant_type=authorization_code";
        $res = file_get_contents($url);                                                                                 //请求微信小程序获取用户接口
        $tmp_res = json_decode($res,true);

        if (isset($tmp_res['errcode']) && !empty($tmp_res['errcode'])) {
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], "请求微信接口报错！！！请联系管理员...");
        }
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberByOpenID($tmp_res['openid']);
        if(count($res)<1){
            $this->result['data'] = array('openid'=> $tmp_res['openid']);
            echo json_encode($this->result);exit;
        }
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }

    //客户绑定
    public function bindAccount()
    {
        $params = request()->post();
        if(!isset($params['openid']) || trim($params['openid']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'openid'); //返回必传参数为空
        }
        if(!isset($params['account']) || trim($params['account']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'account'); //返回必传参数为空
        }
        if(!isset($params['password']) || trim($params['password']) == ''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'password'); //返回必传参数为空
        }
        $preg_phone='/^1[3456789]\d{9}$/';
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/';
        if(!preg_match($preg_phone,$params['account']) && !preg_match($preg_email,$params['account'])){
            $this->result['status'] = 1;
            $this->result['msg'] = '账号必须为正确的手机号或者邮箱';
            echo json_encode($this->result);exit;
        }
        $data = array(
            'openid' => $params['openid']
        );
        $memberModel = new MemberBase();
        $res = $memberModel->validateMemberAccount($params['account'],trim($params['password']));
        if(!$res){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号或密码错误,请联系管理员');
        }elseif ($res < 0){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号已被禁用，请联系管理员');
        }
//        if($res['openid']!=''){
//            return $this->verify_parameter(ErrorCode::$api_enum["customized"], '账号已绑定');
//        }
        if(DB::table('member')->where('openid',$params['openid'])->where("id", "!=", $res)->count()){
            DB::table('member')->where('openid',$params['openid'])->update(["openid" => ""]);
        }
        $member_res = $memberModel->memberUpdate($res,$data);
        if(!$member_res){
            $this->result['status'] = 1;
            $this->result['msg'] = '绑定失败';
            echo json_encode($this->result);exit;
        }
        echo json_encode($this->result);exit;
    }

	public function getNewsList()
    {
        $res = Articles::where('typeid',1)->where('is_display',0)->where('read_power','<',2)->orderBy('id','desc')->first();
        $this->result['data'] = json_decode(json_encode($res),true);
        echo json_encode($this->result);exit;
    }

    //返回失败的原因
    private function verify_parameter($data,$text="")
    {
        if (isset($data['msg']) && strpos($data['msg'], "%s") !== false && $text) {
            $data['msg'] = sprintf($data['msg'], $text);
        }
        echo json_encode($data);exit;
    }
}
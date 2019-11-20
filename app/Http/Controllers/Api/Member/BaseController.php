<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\Member\MemberBase;

class BaseController extends Controller
{
    public $user;
    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
    public $noCheckOpenidAction = []; //不校验token的方法名

    public function __construct()
    {
        header('Access-Control-Allow-Origin:http://www.wegouer.com');
        if(!in_array(\request()->route()->getActionMethod(), $this->noCheckOpenidAction)) {
            $openid = request()->post("openid", "");
            $res = $this->_memberInfo($openid);
            if ($res['status'] > 0) {
                return $res;
            }
            $this->user = $res['data'];
        }
        $this->result['data'] = '';
    }

    private function _memberInfo($openid)
    {
        if(!isset($openid) || $openid==''){
            return $this->verify_parameter(ErrorCode::$api_enum["customized"], "openid不能为空");
        }
        $memberModel = new MemberBase();
        $data = $memberModel->getMemberDetailByOpenid($openid);
        if(!is_array($data) || count($data)<1){
            $result = ErrorCode::$api_enum["customized"];
            $result["status"] = 202;
            return $this->verify_parameter($result, "openid未绑定");
        }
        $this->result['data'] = $data;
        return $this->result;
    }

    //返回失败的原因
    private function verify_parameter($data,$text="")
    {
        if(strpos($data['msg'], "%s") !== false && $text){
            $data['msg'] = sprintf($data['msg'], $text);
        }
        echo json_encode($data);exit;
    }
}
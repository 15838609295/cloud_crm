<?php

namespace App\Http\Controllers\Wxapi;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use App\Models\Member\MemberBase;

class BaseController extends Controller
{
    public $user = '';
    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
    public $noCheckOpenidAction = []; //不校验token的方法名
    public $verification = null;

    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
//        header('Access-Control-Allow-Origin:http://www.wegouer.com');
        if(!in_array(\request()->route()->getActionMethod(), $this->noCheckOpenidAction)) {
            $openid = request()->post("openid", "");
            $res = $this->_memberInfo($openid);
            if ($res['status'] > 0) {
                return $this->result;
            }
            $this->user = $res['data'];
        }
        $this->result['data'] = '';
    }

    private function _memberInfo($openid)
    {
        if(!isset($openid) || $openid==''){
            $this->result = ErrorCode::$wsy_enum["not_openid"];
            return $this->result;
        }
        $memberModel = new MemberBase();
        $data = $memberModel->getMemberByOpenID($openid);
        if(!is_array($data) || count($data)<1){
            $this->result["status"] = 202;
            $this->result["msg"] = '未绑定账号信息';
            return $this->result;
        }
        $this->result['data'] = $data;
        return $this->result;
    }

    //图片 视频 路径处理
    function processingPictures($url){
        global $scf_data;
        if (!$url){
            return $url;
        }
        //去除路径一个点的字符
        if(substr($url,0,1) == '.'){
            $url = substr($url,1,(strlen($url)-1));
        }
        if(substr($url,0,1) != '/' && substr($url,0,1) != 'h'){
            $url = '/'.$url;
        }
        if(strstr($url,"http://")){
            $url = str_ireplace('http://','https://',$url);
        }
        if(!strstr($url,"https")){
            if ($scf_data['IS_SCF'] == true) {
	        $host = 'https://'.$scf_data['system']['bucketConfig']['bucket'].'.cos.'.$scf_data['system']['bucketConfig']['region'].'.myqcloud.com';
                $url = $host.$url;
            }else{
                $url = 'https://'.$_SERVER['SERVER_NAME'].$url;
            }
        }
        return $url;
    }

    function return_result($data,$text="")
    {
        if(strpos($data['msg'], "%s") !== false && $text){
            $data['msg'] = sprintf($data['msg'], $text);
        }
        return response()->json($data);
    }
}
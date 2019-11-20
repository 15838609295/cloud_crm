<?php

namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AdminUser;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public $noCheckOpenidAction = []; //不校验token的方法名
    public $openid = '';
    public $user = null;
    public $returnData = array('status'=>0,'msg'=>'请求成功','data'=>'');

    public function __construct()
    {
        header('Access-Control-Allow-Origin:http://www.wegouer.com');
        if(!is_array($this->noCheckOpenidAction)){
            $this->returnData = ErrorCode::$api_enum['params_error'];
            $this->returnData['msg'] = 'noCheckOpenidAction';
            return $this->returnData;
        }

        //处理登录openid等信息
        if(!in_array(\request()->route()->getActionMethod(), $this->noCheckOpenidAction) && \request()->post("point_url", "") != "createCustomer"){
            $this->openid = \request()->post("openid");
            if(isset($this->openid)) { //就校验token是否有效或者过期
                $res = AdminUser::from('admin_users as au')
                    ->where('openid','=',$this->openid)
                    ->first();
                if(!$res){
                    $data['status'] = 206;
                    $data['msg'] = '用户凭证无效或已过期';
                    return $this->returnData;
                }
                $res = json_decode(json_encode($res),true);
                $this->user = $res;
            }else{
                $this->returnData['status'] = 1;
                $this->returnData['msg'] = 'openid不能为空';
                return $this->returnData;
            }
        }

        $this->returnData = ErrorCode::$api_enum['success'];
    }

    //处理图片路径
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
                $url = 'https://' . $scf_data['host'] .$url;
            }else{
                $url = 'https://'.$_SERVER['SERVER_NAME'].$url;
            }
        }
        return $url;
    }

}

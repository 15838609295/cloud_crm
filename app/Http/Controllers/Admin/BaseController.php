<?php

namespace App\Http\Controllers\Admin;


use App\Http\Config\ErrorCode;
use App\Models\Auth\AuthBase;
use App\Models\User\UserBase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Validator;
use Route;

class BaseController extends Controller
{
    public $is_su = false;
    public $AU = null;
    public $returnData = array('code'=>0,'msg'=>'请求成功','data'=>'');

    public function __construct($request)
    {
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*--- end 跨域测试用---*/

        if($request->getMethod() == "OPTIONS"){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            return $this->returnData;
        }
        $this->returnData = ErrorCode::$admin_enum['success'];
        $token_verify = $this->_validToken($request);
        if($token_verify['code']>0){
            return $this->returnData;
        }
        $this->is_su = self::isSuperUser($token_verify['data']);
        $this->AU = $token_verify['data'];
        $this->returnData['data'] = '';
        if($this->is_su){
            return $this->returnData;
        }
        $this->_validPermission($token_verify['data']['id']);
        if($this->returnData['code']>0){
            return $this->returnData;
        }
    }

    public function rule()
    {
        return ['token' => 'required|regex:/^[a-zA-z0-9]{32}$/'];
    }

    public function message()
    {
        return [
            'token.required' => '用户凭证不能为空',
            'token.regex' => '用户凭证格式不正确'
        ];
    }

    /* 验证是否为超管 */
    public static function isSuperUser($userData)
    {
        if($userData['id']!=1 && $userData['position']!='超级管理员'){
            return false;
        }
        return true;
    }

    /* 验证token有效性 */
    private function _validToken(Request $request)
    {
        $verify_arr = array(
            'token' => $request->token
        );
        $validator = Validator::make($verify_arr,$this->rule(),$this->message());//验证参数
        if ($validator->fails()) {
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = $validator->errors()->all()[0];
            return $this->returnData;
        }
        $userBaseModel =  new UserBase();
        $userData = $userBaseModel->getUserBasicBySessionID($request->token);
        if(!is_array($userData)){
            $this->returnData = ErrorCode::$admin_enum['token_expire'];
            return $this->returnData;
        }
        $this->returnData['data'] = $userData;
        return $this->returnData;
    }

    /* 验证路由权限 */
    private function _validPermission($admin_id){
        $parent_auth = Route::current()->action;
        if(isset($parent_auth['f_auth'])){
//            if($parent_auth["f_auth"] == "none"){ //针对不需要校验权限但又要访问
//                return $this->returnData;
//            }
            $verify_res = AuthBase::verifyUserPathAuth($admin_id,$parent_auth['f_auth']);
        }else{
            $verify_res = AuthBase::verifyUserAuth($admin_id);
        }
        if(!is_array($verify_res)){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            return $this->returnData;
        }
        return $this->returnData;
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
	     $host = 'https://'.$scf_data['system']['bucketConfig']['bucket'].'.cos.'.$scf_data['system']['bucketConfig']['region'].'.myqcloud.com';
                $url = $host.$url;
            }else{
                $path = $_SERVER['DOCUMENT_ROOT'];//获取网站根目录
                if (strstr($path,'public')){
                    $url = 'http://'.$_SERVER['SERVER_NAME'].$url;
                }else{
                    $url = 'https://'.$_SERVER['SERVER_NAME'].'/public'.$url;
                }
            }
        }
        return $url;
    }

    //处理输入的名称类
    function checkName($name,$txt = 'name'){
        if (!$name || $name == ''){
            $this->returnData = ErrorCode::$admin_enum['customized'];
            $this->returnData['msg'] = $txt.'不能为空';
            return $this->returnData;
        }
        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $name)){
            $this->returnData = ErrorCode::$admin_enum['customized'];
            $this->returnData['msg'] = $txt.'不能为空或包含特殊字符';
            return $this->returnData;
        }
        return true;
    }

    //统一返回数据
    public function return_result($data, $text = '')
    {
        if(!is_array($data['msg']) && $text && strpos($data['msg'], "%s") !== false){
            $data['msg'] = sprintf($data['msg'], $text);
        }
        return response()->json($data);
    }
}

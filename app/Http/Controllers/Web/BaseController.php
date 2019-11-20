<?php

namespace App\Http\Controllers\Web;


use App\Http\Config\ErrorCode;
use App\Models\Auth\AuthBase;
use App\Models\Member\MemberBase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Route;

class BaseController extends Controller
{
    public $discount = false;       //客户折扣
    public $level = false;          //客户等级
    public $levelname = false;      //客户等级名称
    public $AU = null;               //客户
    public $username = null;         //客户名
    public $balance = null;           //客户余额
    public $cash_coupon = null;  //客户赠送金
    public $returnData = array('code'=>0,'msg' => '请求成功','data'=>'');

    public function __construct($request)
    {
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers: x-requested-with,content-type');
        /*--- end 跨域测试用---*/

        if($request->getMethod() == "OPTIONS"){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            return $this->returnData;
        }
        $this->returnData = ErrorCode::$web_enum['success'];
        $token_verify = $this->_validToken($request);
        if($token_verify['code']>0){
            return $this->returnData;
        }
        $this->AU = $token_verify['data']['id'];
        $this->level = $token_verify['data']['level'];
        $this->discount = $token_verify['data']['discount'];
        $this->levelname = $token_verify['data']['level_name'];
        $this->username = $token_verify['data']['name'];
        $this->balance = $token_verify['data']['balance'];
        $this->cash_coupon = $token_verify['data']['cash_coupon'];
        $this->returnData['data'] = '';

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

    /* 验证token有效性 */
    private function _validToken(Request $request)
    {
        $verify_arr = array(
            'token' => $request->token
        );
        $validator = Validator::make($verify_arr,$this->rule(),$this->message());//验证参数
        if ($validator->fails()) {
            $this->returnData = ErrorCode::$web_enum['params_error'];
            $this->returnData['msg'] = $validator->errors()->all()[0];
            return $this->returnData;
        }

        $MemberBaseModel =  new MemberBase();
        $userData = $MemberBaseModel->getUserBasicBySessionID($request->token);
        if(!is_array($userData)){
            $this->returnData = ErrorCode::$web_enum['token_expire'];
            return $this->returnData;
        }
        $this->returnData['data'] = $userData;
        return $this->returnData;
    }



    function view_json($data)
    {
        header("Content-Type:application/json; charset=utf-8");
        $callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';
        if (!empty($callback)) {
            echo $callback . '(' . json_encode($data) . ')';
        }
        else {
            echo json_encode($data);
        }
        exit();
    }
}

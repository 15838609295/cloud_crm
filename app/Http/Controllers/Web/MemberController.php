<?php

namespace App\Http\Controllers\Web;

use App\Models\MemberExtend;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class MemberController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    //用户列表
    public function index(){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $memberExtendModel = new MemberExtend();
        $data = $memberExtendModel->getUserInfo($this->AU);
        $data = json_decode(json_encode($data),true);
        if (isset($data['avatar'])){
            $data['avatar'] = $this->processingPictures($data['avatar']);
        }
        $this->returnData['data'] = $data;
    	return response()->json($this->returnData);
    }

    public function updateUserInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data["name"]       = $request->input("name",'');
        $data["mobile"]     = $request->input("mobile",'');
        $data["email"]      = $request->input("email",'');
        $data["password"]   = $request->input("password",'');
        $data["password_confirmation"]   = $request->input("password_confirmation");
        //校验密码
        if($data["password"]!=""){
            if ($data["password"] != '' && $data["password_confirmation"] != '' && $data["password"] == $data["password_confirmation"]) {
                $data["password"] = bcrypt($request->input('password'));
                unset($data["password_confirmation"]);
            } else {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '密码或确认密码不能为空,且两个密码要一致';
                return response()->json($this->returnData);
            }
        }else{
            unset($data["password_confirmation"]);
            unset($data["password"]);
        }
        $data['update_time'] = Carbon::now();
        $member_res = DB::table('member')->where('id','=',$this->AU)->update($data);
        $member_info["avatar"]    = $request->input("avatar");
        $member_info["qq"]        = $request->input("qq");
        $member_info["wechat"]    = $request->input("wechat");
        $member_info["company"]   = $request->input("company");
        $member_info["position"]  = $request->input("position");
        $member_info['update_time'] = Carbon::now();
        $member_extend_res = DB::table('member_extend')->where('member_id','=',$this->AU)->update($member_info);
        if($member_res && $member_extend_res){
            return response()->json($this->returnData);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
    }
    
}

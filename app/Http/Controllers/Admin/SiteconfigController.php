<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Articles;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SiteconfigController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    //配置列表
    public function getDataList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $configModel = new Configs();
    	$data = $configModel->getConfigByID();
//        $file_path1 = base_path().'/public/adminkey/apiclient_cert.pem';
//        $file_path2 = base_path().'/public/adminkey/apiclient_key.pem';
//        $file_path3 = base_path().'/public/memberkey/apiclient_cert.pem';
//        $file_path4 = base_path().'/public/memberkey/apiclient_key.pem';
//        $str1 = '';
//        $str2 = '';
//        $str3 = '';
//        $str4 = '';
//        if(file_exists($file_path1) && file_exists($file_path2) && file_exists($file_path3) && file_exists($file_path4)){
//            $str1 = file_get_contents($file_path1);//将整个文件内容读入到一个字符串中
//            $str1 = str_replace("\r\n","<br />",$str1);
//            $str2 = file_get_contents($file_path2);//将整个文件内容读入到一个字符串中
//            $str2 = str_replace("\r\n","<br />",$str2);
//            $str3 = file_get_contents($file_path3);//将整个文件内容读入到一个字符串中
//            $str3 = str_replace("\r\n","<br />",$str3);
//            $str4 = file_get_contents($file_path4);//将整个文件内容读入到一个字符串中
//            $str4 = str_replace("\r\n","<br />",$str4);
//        }
//        $data['admin_apiclient_cert'] = $str1;
//        $data['admin_apiclient_key'] = $str2;
//        $data['member_apiclient_cert'] = $str3;
//        $data['member_apiclient_key'] = $str4;
        //用户协议加载
        $agreement = Articles::where('typeid',6)->first();
        if ($agreement){
            $agreement = json_decode(json_encode($agreement),true);
            $data['agreementTitle'] = $agreement['title'];
            $data['agreementContent'] = $agreement['content'];
        }else{
            $data['agreementTitle'] = $agreement['title'];
            $data['agreementContent'] = $agreement['content'];
        }
        //隐私协议
        $privacy = Articles::where('typeid',7)->first();
        if ($privacy){
            $privacy = json_decode(json_encode($privacy),true);
            $data['privacyTitle'] = $privacy['title'];
            $data['privacyContent'] = $privacy['content'];
        }else{
            $data['privacyTitle'] = $privacy['title'];
            $data['privacyContent'] = $privacy['content'];
        }
        $this->returnData["data"] = $data;
        return response()->json($this->returnData);
    }

    //更新系统配置
    public function update(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id');
        if ($request->post('type') == "basic") {
            $data['title'] = $request->post('title');
            $request->post('version') ? $data['version'] = $request->post('version') : "";
            $request->post('sms_appid') ? $data['sms_appid'] = $request->post('sms_appid') : "";
            $request->post('sms_appkey') ? $data['sms_appkey'] = $request->post('sms_appkey') : "";
            $data['qy_redirect'] = $request->post('qy_redirect'); //
            $data['agent_url'] = $request->post('agent_url');
            $data['avatar_status'] = $request->post('avatar_status');
            $data['seo_title'] = $request->post('seo_title');
            $data['shortcut'] = $request->post('shortcut');
            $data['site_status'] = $request->post('site_status');
            $data['shortcut_name'] = $request->post('shortcut_name');
            $data['shortcut_url'] = $request->post('shortcut_url');
        } else if ($request->post('type') == "qy") {
            $data['company_id'] = $request->post('company_id');
            $data['tongxl_secret'] = $request->post('tongxl_secret');
            $data['push_secret'] = $request->post('push_secret');
            $data['qy_mch_id'] = $request->post('qy_mch_id');
            $data['qy_pay_secret'] = $request->post('qy_pay_secret');
            $data['qy_appid'] = $request->post('qy_appid');
            $data['qywxLogin'] = $request->post('qywxLogin');
            $data['qy_wx_pay_key'] = $request->post('qy_wx_pay_key');
        } else if ($request->post('type') == "agentWxAppForm") {  //客户小程序配置
            $data['member_wechat_appid'] = $request->post('member_wechat_appid');
            $data['member_wechat_secret'] = $request->post('member_wechat_secret');
            $data['wxapplet_name'] = $request->post('wxapplet_name');
            $data['member_wechat_qr'] = $request->post('member_wechat_qr');
            $data['wx_pay_merchant_id'] = $request->post('wx_pay_merchant_id');
            $data['wx_pay_secret_key'] = $request->post('wx_pay_secret_key');
            $member_path1 = base_path().'/public/memberkey/apiclient_cert.pem';
            $membe_path2 = base_path().'/public/memberkey/apiclient_key.pem';
//            $data['member_apiclient_cert'] = $request->input('member_apiclient_cert','');
//            $data['member_apiclient_key'] = $request->input('member_apiclient_key','');
//            $numbytes3 = file_put_contents($member_path1, $data['member_apiclient_cert']); //如果文件不存在创建文件，并写入内容
//            $numbytes4 = file_put_contents($membe_path2, $data['member_apiclient_key']); //如果文件不存在创建文件，并写入内容
//            if (!$numbytes3 && !$numbytes4){
//                $this->returnData['code'] = 1;
//                $this->returnData['msg'] = '修改支付配置失败';
//                return response()->json($this->returnData);
//            }else{
//                unset($data['member_apiclient_cert']);
//                unset($data['member_apiclient_key']);
//            }
        }else if($request->post('type') == "adminWxAppForm"){   //员工小程序配置
            $data['wechat_appid'] = $request->post('wechat_appid');
            $data['wechat_secret'] = $request->post('wechat_secret');
            $data['wechat_name'] = $request->post('wechat_name');
            $data['admin_wechat_qr'] = $request->post('admin_wechat_qr');
            $data['qy_mch_id'] = $request->post('qy_mch_id');
            $data['qy_pay_secret'] = $request->post('qy_pay_secret');
            $data['admin_apiclient_cert'] = $request->input('admin_apiclient_cert','');
            $data['admin_apiclient_key'] = $request->input('admin_apiclient_key','');
//            $admin_path1 = base_path().'/public/adminkey/apiclient_cert.pem';
//            $admin_path2= base_path().'/public/adminkey/apiclient_key.pem';
//            $numbytes1 = file_put_contents($admin_path1, $data['admin_apiclient_cert']); //如果文件不存在创建文件，并写入内容
//            $numbytes2 = file_put_contents($admin_path2, $data['admin_apiclient_key']); //如果文件不存在创建文件，并写入内容
//            if(!$numbytes1 && !$numbytes2){
//                $this->returnData['code'] = 1;
//                $this->returnData['msg'] = '修改支付配置失败';
//                return response()->json($this->returnData);
//            }else{
//                unset($data['admin_apiclient_cert']);
//                unset($data['admin_apiclient_key']);
//            }
        }else if($request->post('type') == "bonusLimit") {  //奖金规则修改
            $data['bonus_alone'] = $request->post('bonus_alone');
            $data['bonus_proportion'] = $request->post('bonus_proportion');
            $data['bonus_today_second'] = $request->post('bonus_today_second');
            $data['bonus_month_second'] = $request->post('bonus_month_second');
            $data['bonus_small'] = $request->post('bonus_small');
            //奖金规则说明
            $data['bonus_explain'] = $request->post('bonus_explain');
        } else if ($request->post('type') == "agreement") {  //用户协议修改
            $res = Articles::where('typeid',6)->first();
            $where['title'] = $request->post('agreementTitle','');
            $where['content'] = $request->post('agreementContent','');
            if ($res){
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                $update_res = Articles::where('typeid',6)->update($where);
            }else{
                $where['typeid'] = 6;
                $where['created_at'] = Carbon::now()->toDateTimeString();
                $update_res = Articles::insert($where);
            }
            if (!$update_res){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '修改失败';
                return response()->json($this->returnData);
            }else{
                return response()->json($this->returnData);
            }
        }else if ($request->post('type') == "privacy") {  //隐私协议修改
            $res = Articles::where('typeid',7)->first();
            $where['title'] = $request->post('privacyTitle','');
            $where['content'] = $request->post('privacyContent','');
            if ($res){
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                $update_res = Articles::where('typeid',7)->update($where);
            }else{
                $where['typeid'] = 7;
                $where['created_at'] = Carbon::now()->toDateTimeString();
                $update_res = Articles::insert($where);
            }
            if (!$update_res){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '修改失败';
                return response()->json($this->returnData);
            }else{
                return response()->json($this->returnData);
            }
        }else{
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = 'type参数错误';
            return response()->json($this->returnData);
        }
    	$configModel = new Configs();
    	$res = $configModel->configUpdate($id,$data);
    	if(!$res){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '更新失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "更新成功";
        return response()->json($this->returnData);
    }
    
}

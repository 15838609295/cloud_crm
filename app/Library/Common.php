<?php
	
namespace App\Library;

use App\Http\Config\WechatAPI;
use foo\bar;
use Illuminate\Support\Facades\Cache;
use App\Models\Admin\Configs;
use Illuminate\Support\Facades\Mail;
use Qcloud\Sms\SmsSingleSender;
use TencentCloud\Cdb\V20170320\Models\VerifyRootAccountRequest;

//公共类
class Common{
	
	/**
	 * 邮件发送函数
	 *@ name 收件方名称
	 *@ admin 寄件方名称
	 *@ to 收件方邮箱
	 *@ info邮件内容数组 
	 *@ title邮件标题
	 * */
	public function sendMail($name,$admin,$to,$info,$tille)
    {
		
		$em1 = "wstianxia.com";
		$em2 = "wegouer.com";
		$em3 = "netbcloud.com";
		$em4 = "wangqudao.com";
		$em5 = "qcloud0755.com";
		if(strpos($to,$em1)||strpos($to,$em2)||strpos($to,$em3)||strpos($to,$em4)||strpos($to,$em5)){
			
		}else{
			return false;
		}
		
		if($tille=='业绩订单审核成功'){
			$view = 'mail.ach_success';
		}else if($tille=='业绩订单审核失败'){
			$view = 'mail.ach_fail';
		}else{
			return false;
		}
		
        $flag = Mail::send($view,['name'=>$name,'admin'=>$admin,'info'=>$info],function($message)use($to,$tille){  
            $message->to($to)->subject($tille);  
        });
      
        if(count(Mail::failures()) < 1){
            return true;
        }else{
            return false;
        }
	}

    //api更新企业微信账号
    public function setQyWechat($arr,$ccc=1)
    {
        $con = Configs::first();
        $res = file_get_contents(WechatAPI::$qywx_conf["get_token"] . '?corpid='.$con->company_id.'&corpsecret='.$con->tongxl_secret);
        $res = json_decode($res,true);
        if($ccc != 1){
            $url = WechatAPI::$qywx_conf["user_update"]."?access_token=".$res['access_token'];
        }else{
            $url = WechatAPI::$qywx_conf["user_create"]."?access_token=".$res['access_token'];
        }
        $data = json_encode($arr,JSON_UNESCAPED_UNICODE);

        $return = $this->curl($url,$data);
        return json_decode($return,true);
    }

    //api更新企业邮箱账号
    public function setQyEmail($arr,$ccc=1){
        $con = Configs::first();
        $res = file_get_contents(WechatAPI::$qywx_conf["get_token"] . '?corpid='.$con->company_id.'&corpsecret='.$con->tongxl_secret);
        $res = json_decode($res,true);
        if($ccc != 1){
            $url = WechatAPI::$qywx_conf["qyemail_create"] . "?access_token=".$res['access_token'];
        }else{
            $url = WechatAPI::$qywx_conf["qyemail_update"] . "?access_token=".$res['access_token'];
        }
        $data = json_encode($arr,JSON_UNESCAPED_UNICODE);

        $return = $this->curl($url,$data);
        return json_decode($return,true);
    }

    //api邀请用户
    public function invitationUser($arr){
        $con = Configs::first();
        $res = file_get_contents(WechatAPI::$qywx_conf["get_token"] . '?corpid='.$con->company_id.'&corpsecret='.$con->tongxl_secret);
        $res = json_decode($res,true);

        $url = WechatAPI::$qywx_conf["user_invite"] . "?access_token=".$res['access_token'];
        $data = json_encode($arr,JSON_UNESCAPED_UNICODE);

        $return = $this->curl($url,$data);
        return json_decode($return,true);
    }
	
	/**
	 * 企业微信自动推送
	 *@ user_id 被推送人企业微信ID
	 *@ content 推送内容
	 */
	public function QyWechatPush($user_id,$content){
		if(trim($user_id)==''){
			return false;
		}
		
		if(!Cache::has('xiaoxi_access_token')){
			$con = Configs::first();
			$res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->push_secret);
			$arr = json_decode($res,true);
			Cache::add('xiaoxi_access_token',$arr['access_token'],120);//键 值 有效时间（分钟）
		}
		
		$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
		
		$data =array(
			"touser" => $user_id,
		    "msgtype" => "text",
		    "agentid" => 1000011,
		    "text" => array(
		        "content" => $content
		    ),
		   "safe"=> 0
		);
		$data = json_encode($data);
		$res = $this->curl($url,$data);	
		if(!$res){
			return false;
		}else{
			return true;
		}
	}
    //微信自动推送
    public function WechatPush($member_openid,$content){
        if(trim($member_openid)==''){
            return false;
        }
        if(!Cache::has('tongzhi_access_token')){
            $con = Configs::first();
            $res = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$con->member_wechat_appid."&secret=".$con->member_wechat_secret);
            $arr = json_decode($res,true);
            Cache::add('tongzhi_access_token',$arr['access_token'],120);//键 值 有效时间（分钟）
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.Cache::get('tongzhi_access_token');

        $post = [];
        $post['touser'] = $member_openid;
        $post['page'] = 'index';
        $post['color'] = '#173177';
        $post['form_id'] = $content['form_id'];
        $post['emphasis_keyword'] = 'keyword1.DATA';
        switch ($content['type']){
            case 1:   //活动取消
                $post['template_id'] = 'yyKiEZ6mQHWc2Ub8zfizBvVTgZ3K5cSuLo3BVf-gPNw';
                $post['data'] = [
                    'keyword1'=>['value'=>$content['name']],
                    'keyword2'=>['value'=>$content['place']],
                    'keyword3'=>['value'=>$content['start_time']],
                    'keyword4'=>['value'=>$content['cause']],
                    'keyword5'=>['value'=>$content['host_party']],
                    'keyword6'=>['value'=>$content['host_contact']]
                ];
                break;
            case 2:  //审核通过
                $post['template_id'] = '2_c5lFlzZocPe53LwjEPXB5YgTh3i2DMfe11kw0yAqA';
                $post['data'] = [
                    'keyword1'=>['value'=>$content['name']],
                    'keyword2'=>['value'=>$content['type_name']],
                    'keyword3'=>['value'=>$content['place']],
                    'keyword4'=>['value'=>$content['start_time']],
                    'keyword5'=>['value'=>$content['host_contact']],
                    'keyword6'=>['value'=>$content['host_party']],
                    'keyword7'=>['value'=>'您已成功报名，准时参加'],
                ];
                break;
            case 3:   //审核拒绝通过
                $post['template_id'] = 'HOakjr6xEC8LsmqeQ_9nkYJ7DgA1YwtSlVL-v1onKow';
                $post['data'] = [
                    'keyword1'=>['value'=>$content['member_name']],
                    'keyword2'=>['value'=>$content['created_at']],
                    'keyword3'=>['value'=>$content['name']],
                    'keyword4'=>['value'=>$content['result']],
                    'keyword5'=>['value'=>$content['msg']],
                ];
                break;
            case 4:   //退款
                $post['template_id'] =  'jR904LkuddQKSoKe2IuvsZkOne_dCAEJXSP6VdKkAOc';
                $post['data'] = [
                    'keyword1'=>['value'=>'活动名称11'],
                    'keyword2'=>['value'=>'200元'],
                    'keyword3'=>['value'=>'您的申请未通过'],
                ];
                break;
        }
        $post = json_encode($post);
        $re = $this->curl($url,$post);
        return $re;
    }

    //发送短信
    public function sendSNS($mobile,$data){
        $con = Configs::first();
        require_once  base_path().'/vendor/qcloudsms_php-master/src/index.php';
        // 短信应用SDK AppID
        $appid = $con->sms_appid; // 1400开头
        // 短信应用SDK AppKey
        $appkey = $con->sms_appkey;
        // 需要发送短信的手机号码
        $phoneNumbers = [$mobile];
        // 签名
        $smsSign = "网商天下";
        //判断短信发送类型选不同模版
        $templateId = '';
        $info = '';
        try {
            $ssender = new SmsSingleSender($appid, $appkey);
            if($data['type'] == 1){    //取消活动
                $templateId =319329;
                $info = [$data['start_time'],$data['name'],$data['cause'],$data['host_contact']];
            }else if($data['type'] == 2){  //报名成功
                $templateId =319336;
                $info = [$data['name'],$data['start_time'],$data['place'],$data['host_contact']];
            }elseif($data['type'] == 3){  //报名失败
                $templateId =319337;
                $info = [$data['start_time'],$data['name'],$data['msg'],$data['host_contact']];
            }
            $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
                $info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信


            return $result;
        } catch(\Exception $e) {
            return $e;
        }
    }
	
	//curl函数
	private function  curl($url,$data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($data)
		));
		$output = curl_exec($ch);
		if (curl_errno($ch)) {
		    return false;
		}
		curl_close($ch);
        return $output;
    }

    /*
     * 问卷调查统计
     * $title_number  题号
     * $subject       题目
     * $findings      问卷结果
     * $type          访问类型 1是下载数据 2展示数据
     * */
     function statistics_data($title_number,$subject,$findings,$type){
         $final_result = [];
         if ($title_number){
             $final_result[$title_number]['title'] = $subject[$title_number]['title'];
             $answer = unserialize($subject[$title_number]['answer']);
             if ($subject[$title_number]['type'] != 3){
                 foreach ($findings as $val){
                     $result = unserialize($val['result']);
                     $member_res = $result[$title_number-1][0];
                     $answer[$member_res]['number'] += 1;
                 }
                 $final_result[$title_number]['title'] = $subject[$title_number]['title'];
                 $final_result[$title_number]['answer'] = $answer;
             }else{
                 $len = count($findings);
                 if ($len > 5){
                     $f_len = 5;
                 }else{
                     $f_len = $len;
                 }
                 for ($i = 0;$i < $f_len;$i++){
                     $result = unserialize($findings[$i]['result']);
                     $answer[$i] = $result[$title_number][0];
                 }
                 $final_result[$title_number]['answer'] = $answer;
             }
             return $final_result;

        }else if (!$title_number){
            foreach ($subject as $key=>$val){
                $answer = unserialize($val['answer']);
                $final_result[$key]['title'] = $val['title'];
                if ($val['type'] != 3){
                    foreach ($findings as $v){
                        $result = unserialize($v['result']);
                        $member_res = $result[$val['title_number']-1][0];
                        $answer[$member_res]['number'] += 1;
                    }
                    $final_result[$key]['answer'] = $answer;
                }else{
                    $len = count($findings);
                    if ($type == 1){
                        $f_len = $len;
                    }else{
                        if ($len > 5){
                            $f_len = 5;
                        }else{
                            $f_len = $len;
                        }
                    }
                    for ($i = 0;$i < $f_len;$i++){
                        $result = unserialize($findings[$i]['result']);
                        $answer[$i] = $result[$val['title_number'] - 1][0];
                    }
                    $final_result[$key]['answer'] = $answer;
                }
            }
            return $final_result;
        }
        return false;
    }
}

<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Members;
use App\Models\Admin\Adminuser;
use App\Models\Admin\MemberInfo;
use App\Models\Admin\AssignLog;
use App\Models\Admin\Configs;
use Illuminate\Support\Facades\Cache;
use Mail;
use Carbon\Carbon;

class IndexController extends Controller
{
		
	public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
	public $apikey = "b76db28c6bf857940c2df928e39742e6";
	
	public function customer(Request $request){

		if(!$this->checkSign($request->input())){
			$this->result["status"] = 1 ;
			$this->result["msg"] = "验签失败" ;
			return response()->json($this->result);
		}
		
		$data["company"]   = $request->input("company");
        $data["type"]       = $request->input("type");
        $data["source"]     = $request->input("source");
        $data["addperson"]  = $request->input("addperson");
        $data["recommend"]  = intval($request->input("recommend"))!='' ? intval($request->input("recommend")):1;
        $data["remarks"]    = $request->input("remarks");
        $data["project"]    = $request->input("project");
        $data['realname'] = $request->input("realname");
        $data["position"]   = $request->input("position");
        $data["mobile"]     = $request->input("mobile");
        $data["qq"]         = $request->input("qq");
        $data["wechat"]     = $request->input("wechat");
        $data["email"]      = $request->input("email");
		$data['created_at']=date('Y-m-d h:i:s');
		$data['updated_at']=date('y-m-d h:i:s');
		
        $user = Members::queryInfo($data["mobile"]);
        if($user){
			$this->result["status"] = 1 ;
			$this->result["msg"] = "手机号已经存在" ;
            return response()->json($this->result);
        }
        if($data["email"]){
	        $email = Members::queryEmail($data["email"]);
	        if($email){
	            $this->result["status"] = 1 ;
				$this->result["msg"] = "邮箱已经存在" ;
	            return response()->json($this->result);
	        }
	    }
        $mid = Members::insertGetId($data);
        $data_info['member_id']=$mid;
        MemberInfo::insertGetId($data_info);
        
        //自动推送
        $res = $this->autoCustomer($mid);
        return $res;
	}
	
	//验签方法
	public function checkSign($arr){
		$apikey=$this->apikey;//秘钥
    	$strSign='';
    	foreach($arr as $k=>$v){
    		if($k=='sign'){
    			$strSign=$arr[$k];
    			unset($arr[$k]);
    		}
    	}
    	$arr=array_filter($arr); // 过滤掉数组中键值为空的值
        ksort($arr);   // 按照键名首字母进行排序
        $str='';  
        foreach($arr as $k=>$v){
        	$str.=$k.'='.$v.'&';
        }
        $key=$apikey;
        $str.= "key=".$key;
        $sign=md5($str);
        if($sign != $strSign){
        	return false;   //验签失败
        }else{
        	return true;
        }
	}

	//自动推送函数
	private function autoCustomer($id){
		$cust = Members::where("id","=",$id)->first()->toArray();
		$res = Adminuser::where('id','=',$cust['recommend'])->first();
		
		if($cust["realname"]==''){
      		$cust["realname"] = '空';
        }
        
      	$this->testmail($res->name,$res->email,$cust);
        
		$content = $res->name.'!       root 指派给你一个新的客户   '.$cust["realname"].'，电话：'.$cust["mobile"].'，备注'.$cust["remarks"];
		
		if(!$this->ese_wechat($res->wechat_id,$content)){
			$this->request['msg'] = "企业信息有误！";
            $this->request['status'] = 1;
            return response()->json($this->result);
		}
		
		$boo = $this->assign_log($id,$res->name);
		if(!$boo){
			$this->request['msg'] = "记录日志失败！！！";
            $this->request['status'] = 1;
            return response()->json($this->result);
		}
		
		return response()->json($this->result);
	}
	
	//指派记录函数
	public function assign_log($mid,$assign_name=''){
		$data_assign['member_id']=$mid;
		$data_assign['assign_name']=$assign_name;
		$data_assign['assign_admin']='root';
		$data_assign['updated_at']=Carbon::now()->toDateTimeString();
		$data_assign['created_at']=Carbon::now()->toDateTimeString();
		
        $bool = AssignLog::insertGetId($data_assign);
        return $bool;
	}

	//邮件发送函数
	public function testmail($name,$to,$member,$tille="指派客户通知"){
		$admin = 'root';
		
		$em1 = "wstianxia.com";
		$em2 = "wegouer.com";
		$em3 = "netbcloud.com";
		$em4 = "wangqudao.com";
		$em5 = "qcloud0755.com";
		
		if(strpos($to,$em1)||strpos($to,$em2)||strpos($to,$em3)||strpos($to,$em4)||strpos($to,$em5)){
			
		}else{
			return false;
		}
		
        $flag = Mail::send('admin.test',['name'=>$name,'admin'=>$admin,'member'=>$member],function($message)use($to,$tille){  
            $message->to($to)->subject($tille);  
        });
        if(count(Mail::failures()) < 1){
            return true;
        }else{
            return false;
        }
	}
	
	//企业微信自动推送函数
	public function ese_wechat($user_name,$content){
		if(trim($user_name)==''){
			return false;
		}
		
		if(!Cache::has('xiaoxi_access_token')){
			$con = Configs::first();
			$res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->push_secret);
			$arr = json_decode($res,true);
			Cache::add('xiaoxi_access_token',$arr['access_token'],120);//键 值 有效时间（分钟）
			
			$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
			
			$data =array(
				"touser" => $user_name,
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
			
		}else{
			$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".Cache::get('xiaoxi_access_token');
			
			$data =array(
				"touser" => $user_name,
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
	}
	
	//curl函数
	public function  curl($url,$data){
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
    

}
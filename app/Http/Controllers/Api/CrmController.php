<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmController extends Controller
{
	public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
	public $apikey = "b76db28c6bf857940c2df928e39742e6";
	public $urlarr = array(
		'createCustomer','getSources','getProjects','getCustomer','getCommunicateLog',
		'updateCustomer','getCustomerList','getRankingList','getWorkStatus','updateWorkStatus',
		'getUsers','setUsers','getBranchs','getUsersInfo','createCommLog','getGlobalData','getTodayCustomer',
		'takeMoney','getNews','getBonusList','getTakeMoneyList','updateUsers','getAbout'
	);

	//CRM小程序端接口请求入口文件
	public function index(Request $request)
    {
		$data = $request->post();
		
		//判断传值是否正确
    	if(!isset($data['point_url']) || trim($data['point_url']) == ''){
    		return $this->verify_parameter('point_url'); //返回必传参数为空
    	}
    	if(!in_array($data['point_url'],$this->urlarr)){
    		return $this->verify_parameter('请求地址有误！！！');
    	}
    	
    	//验证签名
//    	$bool = $this->checkSign($data);
//    	if(!$bool){
//    		return $this->verify_parameter('验证签名失败！！！',0);
//    	}
		
		//添加用户接口
		if($data['point_url']=='createCustomer'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->createCustomer($data);
			return response()->json($arr);
		}
		
		//客户来源接口
		if($data['point_url']=='getSources'){
			$api =new \App\Http\Controllers\Api\SourcesController;
			$arr = $api->getSources($data);
			return response()->json($arr);
		}
		
		//项目列表接口
		if($data['point_url']=='getProjects'){
			$api =new \App\Http\Controllers\Api\SourcesController;
			$arr = $api->getProjects($data);
			return response()->json($arr);
		}
		
		//获取用户信息
		if($data['point_url']=='getCustomer'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->getCustomer($data);
			return response()->json($arr);
		}
		
		//获取客户沟通记录
		if($data['point_url']=='getCommunicateLog'){
			$api =new \App\Http\Controllers\Api\SourcesController;
			$arr = $api->getCommunicateLog($data);
			return response()->json($arr);
		}
		
		//修改客户信息
		if($data['point_url']=='updateCustomer'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->updateCustomer($data);
			return response()->json($arr);
		}
		
		//获取客户列表
		if($data['point_url']=='getCustomerList'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->getCustomerList($data);
			return response()->json($arr);
		}
		
		//获取业绩排行榜
		if($data['point_url']=='getRankingList'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->getRankingList($data);
			return response()->json($arr);
		}
		
		//获取工作状态
		if($data['point_url']=='getWorkStatus'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->getWorkStatus($data);
			return response()->json($arr);
		}
		
		//更改工作状态
		if($data['point_url']=='updateWorkStatus'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->updateWorkStatus($data);
			return response()->json($arr);
		}
		
		//获取管理员信息
		if($data['point_url']=='getUsers'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->getUsers($data);
			return response()->json($arr);
		}
		
		//绑定管理员
		if($data['point_url']=='setUsers'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->setUsers($data);
			return response()->json($arr);
		}
		
		//获取部门信息
		if($data['point_url']=='getBranchs'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->getBranchs($data);
			return response()->json($arr);
		}
		
		//根据openid获取管理员信息
		if($data['point_url']=='getUsersInfo'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->getUsersInfo($data);
			return response()->json($arr);
		}
		
		//添加沟通记录
		if($data['point_url']=='createCommLog'){
			$api =new \App\Http\Controllers\Api\SourcesController;
			$arr = $api->createCommLog($data);
			return response()->json($arr);
		}
		
		//获取全局数据
		if($data['point_url']=='getGlobalData'){
			$api =new \App\Http\Controllers\Api\AchievementController;
			$arr = $api->getGlobalData($data);
			return response()->json($arr);
		}
		
		//获取今日分配客户和逾期客户总数
		if($data['point_url']=='getTodayCustomer'){
			$api =new \App\Http\Controllers\Api\CustomerController;
			$arr = $api->getTodayCustomer($data);
			return response()->json($arr);
		}
		
		//提现申请
		if($data['point_url']=='takeMoney'){
			$api =new \App\Http\Controllers\Api\TakeMoneyController;
			$arr = $api->takeMoney($data);
			return response()->json($arr);
		}
		
		//获取新闻信息
		if($data['point_url']=='getNews'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->getNews($data);
			return response()->json($arr);
		}
		
		//获取提成记录
		if($data['point_url']=='getBonusList'){
			$api =new \App\Http\Controllers\Api\TakeMoneyController;
			$arr = $api->getBonusList($data);
			return response()->json($arr);
		}
		
		//获取提现记录
		if($data['point_url']=='getTakeMoneyList'){
			$api =new \App\Http\Controllers\Api\TakeMoneyController;
			$arr = $api->getTakeMoneyList($data);
			return response()->json($arr);
		}
		
		//修改管理员信息
		if($data['point_url']=='updateUsers'){
			$api =new \App\Http\Controllers\Api\TakeMoneyController;
			$arr = $api->updateUsers($data);
			return response()->json($arr);
		}
		
		//获取关于我们
		if($data['point_url']=='getAbout'){
			$api =new \App\Http\Controllers\Api\CommonController;
			$arr = $api->getAbout($data);
			return response()->json($arr);
		}
	}
	
	
	//验签方法
	public function checkSign($arr)
    {
		$apikey=$this->apikey;//秘钥
    	$strSign='';
    	// 过滤掉数组中键值为空的值
    	foreach($arr as $k=>$v){
    		if($k=='sign'){
    			$strSign=$arr[$k];
    			unset($arr[$k]);
    		}
    		if($v==''){
    			unset($arr[$k]);
    		}
    	}
        ksort($arr);   // 按照键名首字母进行排序
        $str='';  
        foreach($arr as $k=>$v){
        	$str.=$k.'='.$v.'&';
        }
        $str.= "key=".$apikey;
        $sign=md5($str);
        if($sign != $strSign){
        	return false;   //验签失败
        }else{
        	return true;
        }
	}
	
	//返回失败的原因
	private function verify_parameter($str,$type=1){
		$this->result['status'] = 1;
		if($type==1){
    		$this->result['msg'] = "必传参数".$str."为空";
		}else{
		  	$this->result['msg'] = $str;
		}
    	return response()->json($this->result);
	}
	
}
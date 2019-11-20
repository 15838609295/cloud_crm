<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\AdminUser as User;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Admin\Branch;
use App\Models\Admin\Members;
use App\Models\Admin\Achievement;
use App\Models\Admin\AssignLog;
use Illuminate\Support\Facades\DB;
use Route, URL, Auth;
use Carbon\Carbon;

class WorkstatusController extends Controller
{
	public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");

	public function index(){
    	$date = Carbon::now()->toDateString();
    	$arr= DB::table('admin_users as au')
    	->select('au.*','c.name as company_name')
    	->leftJoin('company as c','c.id','=','au.company_id')
    	->orderBy('au.work_time','desc')
    	->where('au.status','=',0)
    	->get();
        $arr = json_decode(json_encode($arr),true);
    	//冒泡排序把接待的和签到早的挪到前面
    	$len = count($arr);
    	$tmp_uid_list = [];
    	foreach ($arr as $key=>$value){
    	    $tmp_uid_list[] = $value['id'];
        }
    	for($k=0;$k <= $len; $k++){
		    for($j=$len-1;$j > $k; $j--){
		        if($arr[$j]['work_status'] < $arr[$j-1]['work_status']){
			        if(substr($arr[$j]['work_time'],0,10)==substr($date,0,10)){
			        	if(substr($arr[$j-1]['work_time'],0,10)==substr($date,0,10)){
		        			$temp = $arr[$j];
				            $arr[$j] = $arr[$j-1];
				            $arr[$j-1] = $temp;
				        }
			        }
		        }else if($arr[$j]['work_status'] == $arr[$j-1]['work_status']){
		        	if(substr($arr[$j]['work_time'],0,10)==substr($date,0,10)){
			        	if(substr($arr[$j-1]['work_time'],0,10)==substr($date,0,10)){
			        		if($arr[$j]['work_time'] < $arr[$j-1]['work_time']){
			        			$temp = $arr[$j];
					            $arr[$j] = $arr[$j-1];
					            $arr[$j-1] = $temp;
			        		}
				        }
			        }
		        }
		    }
		}
		$assignModel = new AssignLog();
        $day_in_list = $assignModel->getAssignLogByDateWithUserID($tmp_uid_list,'in',substr($date,0,10));
        $day_out_list = $assignModel->getAssignLogByDateWithUserID($tmp_uid_list,'out',substr($date,0,10));
    	$month_in_list = $assignModel->getAssignLogByDateWithUserID($tmp_uid_list,'in',substr($date,0,7));
        $month_out_list = $assignModel->getAssignLogByDateWithUserID($tmp_uid_list,'out',substr($date,0,7));
    	foreach($arr as $k=>$v){
    		//获取今天获得指派的客户数
            $arr[$k]['customer_number'] = 0;
            $arr[$k]['month_customer_number'] = 0;
    		if(substr($v['work_time'],0,10)==substr($date,0,10)){
                $arr[$k]['customer_number'] = $this->_calucateAssignRecord($v['id'],$day_in_list,$day_out_list);
                $arr[$k]['month_customer_number'] = $this->_calucateAssignRecord($v['id'],$month_in_list,$month_out_list);
    		}
    		//获取今天激活的客户数
    		$arr[$k]['deal_number'] = Members::where('act_time','LIKE','%'.$date.'%')->where('recommend','=',$v['id'])->count();
	    	
	    	//获取当月激活的客户数
	    	$arr[$k]['month_deal_number'] = Members::where('act_time','LIKE','%'.substr($date,0,7).'%')->where('recommend','=',$v['id'])->count();
	    	
	    	//获取今天的业绩
	    	$arr[$k]['bonus_number'] = Achievement::where('buy_time','LIKE','%'.$date.'%')->where('admin_users_id','=',$v['id'])->where('status','=',1)->sum('goods_money');
    	
    		//获取当月的业绩
    		$arr[$k]['month_bonus_number'] = Achievement::where('buy_time','LIKE','%'.substr($date,0,7).'%')->where('admin_users_id','=',$v['id'])->where('status','=',1)->sum('goods_money');
    	
    		//当月转化率
    		if($arr[$k]['month_customer_number']!=0){
    			$arr[$k]['month_conversion'] = round($arr[$k]['month_deal_number']/$arr[$k]['month_customer_number'],2)*100;
    		}else{
    			$arr[$k]['month_conversion'] = 0;
    		}
    	}
    	$data['data'] = $arr;
		return view('admin.workstatus.index',$data);
	}

	private function _calucateAssignRecord($user_id,$arr_in,$arr_out){
        $num = 0;
        if(is_array($arr_in)){
            foreach ($arr_in as $key=>$value){
                if($value['assign_touid']==$value['assign_uid'] && $value['assign_touid']==$user_id){
                    $num = $num + 1;
                    continue;
                }
                if($value['assign_touid']==$user_id){
                    $num = $num + 1;
                    continue;
                }
            }
        }
        if(is_array($arr_out)){
            foreach ($arr_out as $key=>$value){
                if($value['assign_touid']==$value['assign_uid'] && $value['assign_uid']==$user_id){
                    continue;
                }
                if($value['assign_uid']==$user_id){
                    $num = $num - 1;
                    continue;
                }
            }
        }
        return $num;
    }
}
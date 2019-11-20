<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\Admin\MemberSource;
use App\Models\Admin\Project;
use App\Models\Admin\Communicationlog;
use App\Models\Customer\CustomerBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SourcesController extends BaseController
{
	
	//获取客户来源
	public function getSources(){
        $memberSourceModel = new MemberSource();
        $res = $memberSourceModel->getSourceListWithFields();
        if(!is_array($res)){
            $res = [];
        }
        $data = ErrorCode::$api_enum['success'];
        $data['data'] = $res;
        return response()->json($data);
	}

	//获取项目接口
	public function getProjects(){
        $projectModel = new Project();
        $data = $projectModel->getProjectListWithFields();
        if(!is_array($data)){
            $data = [];
        }
		$data[] = array('name'=>'其他');
        $data = ErrorCode::$api_enum['success'];
        $data['data'] = $data;
        return response()->json($data);
	}

	//获取沟通记录
	public function getCommunicateLog(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params["member_id"])||trim($params["member_id"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'member_id不能为空';
            return response()->json($this->returnData);
		}
		$res = Communicationlog::from('communicationlog as cl')
        	->select('cl.*','au.name as adminname','c.name','c.realname','c.contact_next_time')
            ->leftJoin('admin_users as au', 'au.id', '=', 'cl.admin_user_id')
            ->leftJoin('customer as c', 'c.id', '=', 'cl.member_id')
            ->where('cl.member_id', '=', $params["member_id"])
            ->orderBy('cl.id',"desc")
            ->get()->toArray();
            
        if(!$res){
            $this->returnData["msg"] = "查不到数据";
            $this->returnData["data"] = [];
            return response()->json($this->returnData);
		}
		$this->returnData['data'] = $res;
        return response()->json($this->returnData);
	}
	
	//添加沟通记录
	public function createCommLog(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params["member_id"])||trim($params["member_id"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "member_id不能为空";
            return response()->json($this->returnData);
		}
		if(!isset($params["comm_time"])||trim($params["comm_time"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "comm_time不能为空";
            return response()->json($this->returnData);
		}
		if(!isset($params["contentlog"])||trim($params["contentlog"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "contentlog不能为空";
            return response()->json($this->returnData);
		}
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "admin_id不能为空";
            return response()->json($this->returnData);
		}
		if(!isset($params["customer_level"])||trim($params["customer_level"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "customer_level不能为空";
            return response()->json($this->returnData);
		}
		if(!isset($params["nextcontact"])||trim($params["nextcontact"])==''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "nextcontact不能为空";
            return response()->json($this->returnData);
		}
		if($params["nextcontact"] <= Carbon::now()->toDateTimeString()){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "下次联系时间有误";
            return response()->json($this->returnData);
		}
		$data['member_id'] = $params['member_id'];
		$data['comm_time'] = $params['comm_time'];
		$data['contentlog'] = $params['contentlog'];
		$data['admin_user_id'] = $params['admin_id'];
		$data_upd['contact_next_time'] = $params['nextcontact'];
		$data_upd['progress'] = $params['customer_level'];
		//开启事务
	    DB::beginTransaction();
        try {
			$communitcationModel = new Communicationlog();
			$contact_res = $communitcationModel->communicationLogInsert($data);
			$customerModel = new CustomerBase();
			$customer_upt_res = $customerModel->customerUpdate($data['member_id'],$data_upd);
			if($contact_res && $customer_upt_res){
                DB::commit();
            }else{
                DB::rollback();
                $this->returnData["status"] = 1;
                $this->returnData["msg"] = "添加沟通日志失败";
                return response()->json($this->returnData);
            }
		} catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "添加沟通日志失败";
            return response()->json($this->returnData);
        }
        return response()->json($this->returnData);
	}
}
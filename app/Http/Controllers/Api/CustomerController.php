<?php

namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AssignLog;
use App\Models\Admin\AdminUser;
use App\Models\Admin\Configs;
use App\Models\Admin\TransferWorkOrder;
use App\Models\Admin\WorkOrder;
use App\Models\Customer\CustomerBase;
use Carbon\Carbon;
use Guzzle\Tests\Plugin\Redirect\RedirectPluginTest;
use Illuminate\Support\Facades\DB;

class CustomerController extends BaseController
{
    protected $fields = [
        'name' => '',
        'realname' => '',
        'type' => '',
        'mobile' => '',
        'email' => '',
        'recommend' => '',
        'addperson' => '',
        'position' => '',
        'company' => '',
        'wechat' => '',
        'qq' => '',
        'contact_next_time' => '',
        'source' => '',
        'project' => '',
        'progress' => '初步接触',
        'status' => 0,
        'remarks' => '',
        'cust_state' => 0
    ];

    public function __construct()
    {
        $this->noCheckOpenidAction = ['getUsers', 'setUsers']; //不校验openid的接口方法名
        parent::__construct();
    }

    //创建客户
	public function createCustomer(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $customer = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $customer[$field] = request()->post($field,$this->fields[$field]);
        }
		//判断必传参数
        if(empty(request()->post("admin_id"))){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'member_id不能为空';
            return response()->json($this->returnData);
        }
        if(empty(request()->post("admin_name"))){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'admin_name不能为空';
            return response()->json($this->returnData);
        }
		if(!isset($customer["name"])||trim($customer["name"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'name不能为空';
            return response()->json($this->returnData);
		}
		if(!isset($customer["mobile"])||trim($customer["mobile"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'mobile不能为空';
            return response()->json($this->returnData);
		}
		if(!isset($customer["type"])||trim($customer["type"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'type不能为空';
            return response()->json($this->returnData);
		}
        $customer["recommend"]  = request()->post("admin_id");
        $customer["addperson"]  = request()->post("admin_name");

        $customerModel = new CustomerBase();
        $res = $customerModel->validCustomerRepeat(['mobile'=>$customer['mobile'], 'email'=>$customer['email'],'qq'=>$customer['qq']]);
        if($res['is_repeat']==1){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = $res['returnData']["msg"];
            return response()->json($this->returnData);
        }
        $customer['status'] = 1;
        $res = $customerModel->customerInsert($customer);
        if(!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        return response()->json($this->returnData);
	}

	//获取客户信息
	public function getCustomer(){
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
	$customModel = new CustomerBase();
	$res = $customModel->getCustomerDetail($params["member_id"]);
	if(!$res){
            $this->returnData["msg"] = "查不到数据";
            $this->returnData["data"] = [];
		}else{
            $this->returnData['data'] = [$res];
        }
        return response()->json($this->returnData);
	}

	//修改客户信息
	public function updateCustomer(){
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
	if(!isset($params["name"])||trim($params["name"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'name不能为空';
            return response()->json($this->returnData);
	}
	if(!isset($params["mobile"])||trim($params["mobile"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'mobile不能为空';
            return response()->json($this->returnData);
	}
	if(!isset($params["source"])||trim($params["source"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'source不能为空';
            return response()->json($this->returnData);
	}
	if(!isset($params["type"])||trim($params["type"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'type不能为空';
            return response()->json($this->returnData);
	}

        $data["name"]       = $params["name"];
        $data["company"]    = isset($params["company"])?trim($params["company"]):'';
        $data["source"]     = $params["source"];
        $data["remarks"]    = isset($params["remarks"])?trim($params["remarks"]):'';
        $data["project"]    = isset($params["project"])?trim($params["project"]):'';
        $data['realname']   = isset($params["realname"])?trim($params["realname"]):'';
        $data["position"]   = isset($params["position"])?trim($params["position"]):'';
        $data["mobile"]     = $params["mobile"];
        $data["type"]       = $params["type"];
        $data["qq"]         = isset($params["qq"])?trim($params["qq"]):'';
        $data["wechat"]     = isset($params["wechat"])?trim($params["wechat"]):'';
        $data["email"]      = isset($params["email"])?trim($params["email"]):'';
        $mobile = DB::table("customer")->where('id','!=',$params["member_id"])->where('mobile','=',$data["mobile"])->first();
        if($mobile){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '该手机已经被注册了';
            return response()->json($this->returnData);
        }
        $email = DB::table("customer")->where('id','!=',$params["member_id"])->where('email','=',$data["email"])->first();
        if($email){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '该邮箱已经被注册了';
            return response()->json($this->returnData);
        }
        $bool = DB::table("customer")->where('id','=',$params["member_id"])->update($data);
        if(!$bool){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '修改用户数据失败';
            return response()->json($this->returnData);
        }
        return response()->json($this->returnData);
	}

	//获取客户列表
	public function getCustomerList(){
        $params = request()->post();
	//判断必传参数
	if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'admin_id不能为空';
            return response()->json($this->returnData);
	}
	//判断是否有可选参数
    	if(!isset($params['page']) || trim($params['page']) == ''){
         	$params['page'] = 1;
    	}
    	$start = ((int)$params['page']-1)*10;  //截取部分数据

	$admin = AdminUser::where('id','=',$params["admin_id"])->first();
	if(!$admin){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "管理员数据有误";
            return response()->json($this->returnData);
	}
	$total = CustomerBase::from('customer as c')
		->select('c.*','m.active_time','me.level','me.cash_coupon','me.balance','au.name as admin_name')
		->leftJoin('admin_users as au','c.recommend','=','au.id')
		->leftJoin('member as m','m.id','=','au.id')
		->leftJoin('member_extend as me','m.id','=','me.member_id');

	//权限判断
	if(trim($admin->power)!=''){
	    $adminModel = new AdminUser();
            $user_list = $adminModel->getAdminSubuser($params["admin_id"]);
            $total->whereIn('c.recommend',$user_list);
//			$params002 = explode(',',$admin->power);
//			$total->whereIn('au.branch_id',$params002);
	}else{
	    $total->where('au.id','=',$admin->id);
	}

	$res = $total->skip($start)->take(10)
            ->orderBy('c.id','desc')
            ->get()->toArray();

	if(!$res){
            $this->returnData["msg"] = "查不到数据";
            $this->returnData["data"] = [];
            return response()->json($this->returnData);
	}
	$this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

	//获取管理员信息
	public function getUsers(){
        $params = request()->post();
	    //判断必传参数
	    if(!isset($params['code']) || trim($params['code']) == ''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = "code不能为空";
            return response()->json($this->returnData);
    	}

    	$con = Configs::first();
    	if(isset($params['wxType'])&&trim($params['wxType'])==1){
    		$url = "https://api.weixin.qq.com/sns/jscode2session?";
	    	$url .= "appid=".$con->wechat_appid;
	    	$url .= "&secret=".$con->wechat_secret;
	    	$url .= "&js_code=".$params['code'];
	    	$url .= "&grant_type=authorization_code";

    		$res = file_get_contents($url);  //请求微信小程序获取用户接口
    		$params001 = json_decode($res,true);

    		if (isset($params001['errcode']) && !empty($params001['errcode'])) {
                $this->returnData['status'] = 1;
                $this->returnData['msg'] = '请求微信接口报错！！！请联系管理员';
                return response()->json($this->returnData);
	        }
	        $admin = AdminUser::where('openid','=',$params001['openid'])->first();

	        if($admin){
                if($admin["status"] != "0"){
                    $this->returnData = ErrorCode::$api_enum["fail"];
                    $this->returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
                }else {
                    $this->returnData['data'] = $admin->toArray();
                }
	        }else{
	            $this->returnData['status'] = '206';
	            $this->returnData['msg'] = '未绑定账号';
	            $this->returnData['data'] = array(
	            	'openid'=> $params001['openid']
	            );
	        }
    	}else{
    		$res = file_get_contents("https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$con->company_id."&corpsecret=".$con->qy_appid);
		    $ar = json_decode($res,true);
		    $access_token = $ar['access_token'];
	      	$url = "https://qyapi.weixin.qq.com/cgi-bin/miniprogram/jscode2session?";
	      	$url .= "&access_token=".$access_token;
	    	$url .= "&js_code=".$params['code'];
	    	$url .= "&grant_type=authorization_code";
	    	$res = file_get_contents($url);  //请求微信小程序获取用户接口
	    	$params001 = json_decode($res,true);
    		if (isset($params001['errcode']) && !empty($params001['errcode'])) {
                $this->returnData = ErrorCode::$api_enum["fail"];
                $this->returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
	        }
	        $admin = AdminUser::where('openid','=',$params001['userid'])->first();
	        if($admin){
                if($admin["status"] != "0"){
                    $this->returnData = ErrorCode::$api_enum["fail"];
                    $this->returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
                }else {
                    $this->returnData['data'] = $admin->toArray();
                }
	        }else{
                $this->returnData['status'] = '206';
                $this->returnData['msg'] = '未绑定账号';
	          	$this->returnData['data'] = array(
	            	'openid'=> $params001['userid']
	            );
	        }
    	}
        return response()->json($this->returnData);
	}

	//绑定管理员
	public function setUsers(){
        $params = request()->post();
		//判断必传参数
    	if(!isset($params['username']) || trim($params['username']) == ''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'username不能为空';
            return response()->json($this->returnData);
    	}
    	if(!isset($params['user_pass']) || trim($params['user_pass']) == ''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'user_pass不能为空';
            return response()->json($this->returnData);
    	}
    	$data['openid'] = $params['openid'];
    	$adminModel = new AdminUser();
    	$admin_id = $adminModel->validateAdminAccount($params['username'],$params['user_pass']);
    	if($admin_id<1){
            $this->returnData['status'] = 207;
            $this->returnData['msg'] = '账号密码错误';
            return response()->json($this->returnData);
        }
        //去查询有没有管理占用此openid
        $old_admin_user = AdminUser::where('openid',$params['openid'])->select('id')->first();
        $old_admin_user = json_decode(json_encode($old_admin_user),true);
        if ($old_admin_user){
            $where['openid'] = '';
            AdminUser::where('id',$old_admin_user['id'])->update($where);
        }
        //绑定新用户
        $res = AdminUser::where('id',$admin_id)->update($data);
    	if(!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '绑定失败';
        }
        return response()->json($this->returnData);
	}

	//根据openid获取管理员信息
	public function getUsersInfo(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
    	$res = AdminUser::from('admin_users as au')
    		->select('b.branch_name','au.*')
    		->leftJoin('branchs as b','b.id','=','au.branch_id')
    		->where('openid','=',$this->openid)
    		->first();
        $res = json_decode(json_encode($res),true);
    	if(!$res){
            $this->returnData["msg"] = "查不到数据";
            return response()->json($this->returnData);
    	}
        if($res["status"] != "0"){
            $this->returnData = ErrorCode::$api_enum["fail"];
            $this->returnData["msg"] = "您的账号已被禁用，无法登陆，如有疑问，请联系管理员！";
        }else {
            //work_status 因为后台是1代表接待，但是小程序是0代表接待，为了跟后台状态保持一致，需要数据颠倒下
            $res["work_status"] = $res["work_status"] == '1' ? 0 : 1;
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
	}

	//获取当日分配和逾期客户
	public function getTodayCustomer(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params['admin_id']) || trim($params['admin_id']) == ''){
            $this->returnData["status"] = 1;
            $this->returnData["msg"] = 'admin_id不能为空';
            return response()->json($this->returnData);
    	}
    	$date = Carbon::now()->toDateTimeString();
    	$exist = AdminUser::where('id','=',$params['admin_id'])->count();
    	if(!$exist){
            $data['today_customer_number'] = 0;
            $data['over_customer_number'] = 0;
            $this->returnData['data'] = $data;
            return response()->json($this->returnData);
        }
        //获取今天获得指派的客户数
        $assignModel = new AssignLog();
        $day_in_list = $assignModel->getAssignLogByDateWithUserID([$params['admin_id']],'in',substr($date,0,10));
        $day_out_list = $assignModel->getAssignLogByDateWithUserID([$params['admin_id']],'out',substr($date,0,10));
        $in = isset($day_in_list[0]) ? $day_in_list[0]["total_assign"] : 0;
        $out = isset($day_out_list[0]) ? $day_out_list[0]["total_assign"] : 0;
    	$data['today_customer_number'] = $in - $out;
		//获取逾期客户数
        $customerModel = new CustomerBase();
		$data['over_customer_number'] = $customerModel->getOverCustomerCount($params['admin_id'], substr($date,0,10).' 00:00:00');
		$this->returnData['data'] = $data;
        return response()->json($this->returnData);
	}

	//指派记录函数
	public function assign_log($mid,$admin_id,$assign_name=''){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
	    $data = array(
	        'member_id' => $mid,
            'assign_name' => $assign_name,
            'assign_admin' => $assign_name,
            'assign_uid' => $admin_id,
            'assign_touid' => $admin_id,
            'operation_uid' => $admin_id,
        );
		$assignModel = new AssignLog();
		$res = $assignModel->assignLogInsert($data);
        return $res;
	}

    //管理员 我的反馈
    public function adminFeedbackList(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $data['admin_id'] = $this->user['id'];
        $data['type'] = trim(request()->post('type',''));
        $pageNo = request()->post('pageNo',1);
        $data['pageSize'] = request()->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getAdminFeedbackList($data);
        if (!$res){
            $this->returnData['data'] = ['total'=>0,'rows'=>[]];
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //转单记录
    public function changeOrderLog(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $type = request()->post('type','');
        $pageNo = request()->post('pageNo',1);
        $data['pageSize'] = request()->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $data['id'] = $this->user['id'];
        $workOrderModel = new TransferWorkOrder();
        $res = $workOrderModel->getTransferWorkOrder($data,$type);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //反馈详情
    public function feedbackInfo(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $id = request()->post('id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getWorkOrderInfo($id,1);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '信息不存在';
        }else{
            if (is_array($res['pic_list'])){
                foreach ($res['pic_list'] as &$p_v){
                    $p_v = $this->processingPictures($p_v);
                }
            }
            if (is_array($res['work_order_log'])){
                foreach ($res['work_order_log'] as &$w_v){
                    if (is_array($w_v['annex'])){
                        foreach ($w_v['annex'] as &$a_v){
                            $a_v = $this->processingPictures($a_v);
                        }
                    }
                }
            }
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //签收反馈
    public function acceptWorkOrder(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $id = request()->post('id','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->acceptWorkOrder($id,$this->user['id']);
        if ($res === -1){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '该工单不属于您';
            return response()->json($this->returnData);
        }
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '签收失败';
        }
        return response()->json($this->returnData);
    }

    //管理提交反馈
    public function adminWorkOrderSubmit(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $data['log_id'] = request()->post('id','');
        $data['remarks'] = request()->post('remarks','');
        $data['annex'] = request()->post('annex','');
        $data['u_id'] = $this->user['id'];
        $data['type'] = 2;
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->addWorkOrderLog($data);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '提交失败';
        }
        return response()->json($this->returnData);
    }

    //转单申请
    public function transferWorkOrder(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $data['work_order_id'] = request()->post('id','');
        $data['original_member_id'] = $this->user['id'];
        $data['change_remarks'] = request()->post('change_remarks','');
        $workOrderModel = new TransferWorkOrder();
        $res = $workOrderModel->applyChangeOrder($data);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '提交失败';
        }else if($res === -1){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '该工单不属于您';
        }
        return response()->json($this->returnData);
    }
}
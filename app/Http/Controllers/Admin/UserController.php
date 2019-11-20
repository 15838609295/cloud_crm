<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Library\Common;
use App\Models\Admin\Achievement;
use App\Models\Admin\AdminBonusLog;
use App\Models\Admin\AdminUser;
use App\Models\Admin\BonusSale;
use App\Models\Admin\Configs;
use App\Models\Admin\Street;
use App\Models\Admin\Withdrawal;
use App\Models\Admin\WorkOrder;
use App\Models\Company;
use App\Models\Customer\CustomerBase;
use App\Models\User\UserBase;
use App\Models\User\UserSession;
use App\Models\User\UserWechat;
use Illuminate\Http\Request;
use App\Library\UploadFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Excel;

class UserController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    protected $fields = [
        'name'  => '',
        'email' => '',
        'sex'   => '',
        'mobile'=> '',
        'ach_status' => 1,
        'wechat_id' => '',
        'position' => '',
        'hiredate' => '',
        'status' => 0,
        'company_id' => '',
        'power' => '',
        'formal_time' => '',
    ];

    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','hiredate'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'branch_id' => trim($request->post('branch_id','')),
            'status' => ($request->post('status','')),
            'company_id' => trim($request->post('company_id','')),
        );
        $adminUserModel = new UserBase();
        $data = $adminUserModel->getAdminUserListWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //管理员导出
    public function userToExcel(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $streetModel = new Street();
        $res = $streetModel->getAdminStreetInfo();
        if (!$res){
            $this->returnData['data'] = [];
            return response()->json($this->returnData);
        }

        $arr[] = ['管理员','电话','邮箱','入职时间','街道信息'];
        foreach ($res as $k=>&$v) {
            $arr[$k+1] = [
                $v['admin_name'],
                $v['mobile'],
                $v['email'],
                $v['hiredate'],
            ];
            if (count($v['street_data']) > 0){
                foreach ($v['street_data'] as $s_v){
                    array_push($arr[$k+1],$s_v);
                }
            }
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a = Excel::create('管理员信息',function($excel) use ($arr){
                $excel->sheet('工单反馈统计', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '管理员信息.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('管理员信息',function($excel) use ($arr){
                $excel->sheet('管理员信息', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->export('xlsx');
        }
    }

    public function adminList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $adminUserModel = new UserBase();
        $fields = ['id','name'];
        $data = $adminUserModel->getAdminUserList($fields);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    public function adminAllList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $incumbency = DB::table('admin_users')
            ->select('id','name')
            ->where('status','=',0)
            ->get();
        $quit = DB::table('admin_users')
            ->select('id','name')
            ->where('status','=',1)
            ->get();
        $incumbency = json_decode(json_encode($incumbency),true);
        $quit = json_decode(json_encode($quit),true);

        $data['label'] = '在职人员';
        $data['personnel'] =$incumbency;
        $data1['label'] = '离职人员';
        $data1['personnel'] =$quit;

        $this->returnData['data'] = array('list'=>array($data,$data1));
        return response()->json($this->returnData);
    }
  
    /* 管理员基础信息 */
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function basic(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $userModel = new UserBase();
        $data = $userModel->getAdminBasic($this->AU['id']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //修改管理员基本信息
    public function modify(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['wechat_pic'] = $request->post('wechat_pic','');
        $data['mobile'] = $request->post('mobile','');
        $data['password'] = bcrypt($request->post('password',''));
        $data['wechat_id'] = $request->post('wechat_id','');
        $adminBaseModel = new AdminUser();
        $res = $adminBaseModel->adminUserUpdate($this->AU['id'],$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    /* 管理员详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $userModel = new UserBase();
        $data = $userModel->getAdminDetailByID($id);
        if(!is_array($data) || count($data)<1){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '该数据不存在';
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //管理员添加
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $user = [];
        foreach (array_keys($this->fields) as $field) {
            $user[$field] = $request->post($field);
        }
        $user['status'] = 0;
        $user['password'] = bcrypt($request->post('password'));
        $user['power'] = $request->post('power');
        $branch_list = $request->post('branch');
        if($branch_list!=''){
            $branch_list = explode(',',$branch_list);
        }else{
            $branch_list = [];
        }
        $adminUserModel = new UserBase();
        $res = $adminUserModel->validAdminUserRepeat(['mobile'=>$user['mobile'],'email'=>$user['email']]);
        if($res['is_repeat']==1){
            $this->returnData = $res['returnData'];
            return response()->json($this->returnData);
        }
        $data = array(
            'role_id' => $request->post('role_id'),
            'branch' => $branch_list
        );
        $user_id = $adminUserModel->adminUserInsert($user,$data);
        if(!$user_id){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        //异步增加企业微信账号
        if($request->input('set_qy_wechat')==1){
            $companyModel = new Company();
            $res = $companyModel->getCompanyByID($request->input('company_id'));
            $department = $res['wechat_channel_id'];
            $gender = $request->input('sex')=='女'?2:1;
            if($user['power']!='' && count($user['power'])>0){
                $arr = array(
                    'userid' => $request->input('wechat_id'),
                    'name' => $request->input('name'),
                    'mobile' => $request->input('mobile'),
                    'department' => $department,
                    'position' => $request->input('position'),
                    'gender' => $gender,
                    'email' => $request->input('email'),
                    'isleader' => 1
                );
            }else{
                $arr = array(
                    'userid' => $request->input('wechat_id'),
                    'name' => $request->input('name'),
                    'mobile' => $request->input('mobile'),
                    'department' => $department,
                    'position' => $request->input('position'),
                    'gender' => $gender,
                    'email' => $request->input('email')
                );
            }
            $arr2 = array(
                'user' => $request->input('wechat_id')
            );
            $emailarr = array(
                'userid' => $request->input('wechat_id'),
                "name" => $request->input('name'),
                'department' => $department,
                'position' => $request->input('position'),
                'mobile' => $request->input('mobile'),
                'gender' => $gender,
                'password' => $request->input('password'),
                'cpwd_login' => 1
            );
            $configModel = new Configs();
            $config = $configModel->getConfigByID(1);
            if ($config['qywxLogin'] == 1){
                $common = new Common();
                $common->setQyWechat($arr);
                $common->invitationUser($arr2);
                $common->setQyEmail($emailarr);
            }
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

	//管理员修改
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        if($this->AU['id']!=1 && $this->AU['id']!=$id){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            return response()->json($this->returnData);
        }
        $user = [];
        foreach (array_keys($this->fields) as $field) {
            $user[$field] = $request->post($field);
        }
        if($request->post('password')){
            $user['password'] = bcrypt($request->post('password'));
        }
        $user['power'] = $request->post('power');
        $branch_list = $request->post('branch');
        if($branch_list!=''){
            $branch_list = explode(',',$branch_list);
        }
        $adminUserModel = new UserBase();
        $res = $adminUserModel->validAdminUserRepeat(['mobile'=>$user['mobile'],'email'=>$user['email']],$id);
        if($res['is_repeat']==1){
            $this->returnData = $res['returnData'];
            return response()->json($this->returnData);
        }
        $data = array(
            'role_id' => (int)$request->post('role_id'),
            'branch' => $branch_list
        );
        $result = $adminUserModel->adminUserUpdate($id,$user,$data);
        if(!$result){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        //异步修改企业微信账号
        $companyModel = new Company();
        $res = $companyModel->getCompanyByID($request->input('company_id'));
        $department = $res['wechat_channel_id'];
        $gender = $request->input('sex')=='女'?2:1;
        $arr = array(
            'userid' => $request->input('wechat_id'),
            'name' => $request->input('name'),
            'mobile' => $request->input('mobile'),
            'department' => $department,
            'position' => $request->input('position'),
            'gender' => $gender,
            'email' => $request->input('email')
        );

        if($user['power']!='' && count($user['power'])>0){
            $arr['isleader'] =1;
        }
        $emailarr = array(
            'userid' => $request->input('wechat_id'),
            "name" => $request->input('name'),
            'department' => $department,
            'position' => $request->input('position'),
            'mobile' => $request->input('mobile'),
            'gender' => $gender
        );
        $arr2 = array(
            'user' => $request->input('wechat_id')
        );
        $configModel = new Configs();
        $config = $configModel->getConfigByID(1);
        if ($config['qywxLogin'] == 1){
            $common = new Common();
            $common->setQyWechat($arr,2);
            $common->setQyEmail($emailarr,2);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }

    /* 管理员删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if ($id == 1){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = 'root账号不能删除';
            return response()->json($this->returnData);
        }

        $adminUserModel = new UserBase();
        $res = $adminUserModel->adminUserDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
    
    /* 管理员操作 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array($request->action,['synchronize','update_status'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $id = $request->id;
        if($this->AU['id']!=1 && $this->AU['id']!=$id){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            return response()->json($this->returnData);
        }
        switch ($request->action){
            case 'synchronize':                                                                                         //同步企业账号
                $res = $this->_buildUserQYAccount($request);
                return response()->json($res);
                break;
            case 'update_status':                                                                                       //更改管理员状态(启用/禁用)
                $res = $this->_updateStatus($request);
                if($res['code']>0){
                    return response()->json($res);
                }
                break;
            default:
                $this->returnData = ErrorCode::$admin_enum['request_error'];
                return response()->json($this->returnData);
        }
        $adminUserModel = new UserBase();
        $result = $adminUserModel->adminUserUpdate($id,$res['data']);
        if(!$result){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '操作失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '操作成功';
        return response()->json($this->returnData);
	}

    /* 管理员操作 */
    public function editBasic(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array($request->action,['update_avatar','update_workstatus','update_basic'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $id = $request->id;
        if($this->AU['id']!=1 && $this->AU['id']!=$id){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            return response()->json($this->returnData);
        }
        switch ($request->action){
            case 'update_avatar':                                                                                       //修改头像
                $res = $this->_updateUserAvatar($request);
                if($res['code']>0){
                    return response()->json($res);
                }
                break;
            case 'update_workstatus':                                                                                   //更改工作状态
                $res = $this->_updateWorkStatus($request);
                if($res['code']>0){
                    return response()->json($res);
                }
                break;
            case 'update_basic':                                                                                        //修改基础信息
                $res = $this->_updateUserBaseInfo($request,$id);
                if($res['code']>0){
                    return response()->json($res);
                }
                break;
            default:
                $this->returnData = ErrorCode::$admin_enum['request_error'];
                return response()->json($this->returnData);
        }
        $adminUserModel = new UserBase();
        $result = $adminUserModel->adminUserUpdate($id,$res['data']);
        if(!$result){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '操作失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '操作成功';
        return response()->json($this->returnData);
	}

    //业绩和用户转移
    public function exchangeOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $touid = $request->input("exchange_user");
        if($touid==null){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '转移管理员不能为空';
            return response()->json($this->returnData);
        }
        $id = $request->input("uid");
        if($id==null){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '参数错误';
            return response()->json($this->returnData);
        }
        $adminUserModel = new UserBase();
        $user_list = $adminUserModel->getAdminSubuser($this->AU["id"]);
        if(!in_array($id,$user_list)){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '无权转移';
            return response()->json($this->returnData);
        }
        DB::beginTransaction();
        $membersModel = new CustomerBase();
        $res_members_update = $membersModel->getExchangeRecommend($id,['recommend'=>$touid]);
        $achievementModel = new Achievement();
        $res_achievement_update = $achievementModel->achievementUpdateByColumn('admin_users_id',$id,['admin_users_id'=>$touid]);
        if(!$res_members_update || !$res_achievement_update){
            DB::rollback();
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '转移失败';
            return response()->json($this->returnData);
        }
        DB::commit();
        $this->returnData['msg'] = '转移成功';
        return response()->json($this->returnData);
    }

    private function _buildUserQYAccount($request){
        $id = $request->id;
        $userWechatModel = new UserWechat();
        $res = $userWechatModel->buildQYUser($id);
        return $res;
    }

    /* 更改用户头像 */
    private function _updateUserAvatar($request){
        $self_returnData = $this->returnData;
        $file = $request->file('avatar');
        if (!$file){
            $self_returnData = ErrorCode::$admin_enum['params_error'];
            $self_returnData['msg'] = '文件不存在,请重新上传';
            return $self_returnData;
        }
        $result = (new UploadFile([
            'upload_dir' => './uploads/avatar/',
            'type'       =>['image/jpg','image/png','image/jpeg','image/bmp','image/gif']]
        ))->upload($file);
        if($result['code']>0) {
            return $result;
        }
        $self_returnData['data'] = ['wechat_pic' => $result['data']];
        return $self_returnData;
    }

	/* 更新管理员状态 */
    private function _updateStatus($request){
        $self_returnData = $this->returnData;
        $status = trim($request->post('status',''));
        if($status==null || !in_array(strval($status),['0','1'],true)){
            $self_returnData = ErrorCode::$admin_enum['params_error'];
            return $self_returnData;
        }
        $self_returnData['data'] = ['status' => $status];
        return $self_returnData;
    }

    /* 打卡签到更新 */
    private function _updateWorkStatus($request){
        $self_returnData = $this->returnData;
        $status = trim($request->post('work_status',''));
        if($status==null || !in_array(strval($status),['0','1'],true)){
            $self_returnData = ErrorCode::$admin_enum['params_error'];
            return $self_returnData;
        }
        $work_status = $request->post('work_status');
        $work_time = Carbon::now()->toDateTimeString();
        if($work_time <= (substr($work_time,0,10).' 08:00:00')){
            $work_time =  Carbon::now()->subDays(1)->toDateTimeString();
        }
        $self_returnData['data'] = ['work_status' => $work_status,'work_time' => $work_time];
        return $self_returnData;
    }

    /* 修改用户基础资料 */
    private function _updateUserBaseInfo($request,$id){
        $self_returnData = $this->returnData;
        if(trim($request->post('mobile',''))==''){
            $self_returnData = ErrorCode::$admin_enum['params_error'];
            $self_returnData['msg'] = '手机号不能为空';
            return $self_returnData;
        }
        $adminUserModel = new UserBase();
        $res = $adminUserModel->validAdminUserRepeat(['mobile'=>$request->post('mobile')],$id);
        if($res['is_repeat']==1){
            $self_returnData = $res['returnData'];
            return $self_returnData;
        }
        $data = array(
//            'sex' => $request->post('sex'),
            'mobile' => $request->post('mobile'),
            'email' => $request->post('email'),
            'openid' => $request->post('openid'),
            'wechat_id' => $request->post('qy_wechat_id'),
        );
        if(trim($request->post('password',''))!=''){           //修改密码
            if (trim($request->post('old_password')) == ''){
                $self_returnData = ErrorCode::$admin_enum['params_error'];
                $self_returnData['msg'] = '原密码不能为空';
                return $self_returnData;
            }
            $user_info = $adminUserModel->getAdminUserPassword($id);
            if(!Hash::check($request->input('old_password'),$user_info['password'])){
                $self_returnData = ErrorCode::$admin_enum['params_error'];
                $self_returnData['msg'] = '原密码错误，请重新输入';
                return $self_returnData;
            }

            $data['password'] = bcrypt(trim($request->post('password')));
        }
        $self_returnData['data'] = $data;
        return $self_returnData;
    }

    //root用户跳转管理员帐号
    public function switch_user(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id');
        $adminUserModel = new AdminUser();
        $res = $adminUserModel->getAdminByID($id);
        if ($res){
            $userSessionModel = new UserSession();
            $session_id = $userSessionModel->setSession(['admin_id'=>$res['id']]);
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            $data['data']['token'] = $session_id;
            return response()->json($data);
        }else{
            $data['code'] = 1;
            $data['msg'] = '此用户不存在';
            return response()->json($data);
        }
    }

    //我的钱包
    public function my_wallet(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $type = $request->post('type', 1);
        $id = $request->post('id','');
        $adminUserModel = new AdminUser();
        if ($id != ''){
            $res = $adminUserModel->getTotalBonus($id);
        }else{
            if ($this->AU['id'] == 1){
                $res = $adminUserModel->getTotalBonus();
            }else{
                $res = $adminUserModel->getTotalBonus($this->AU['id']);
            }
        }
        $adminbonuslogModel = new AdminBonusLog();
        $searchFilter = array(
            'sortName' => $request->post("sortName",'id'), //排序列名
            'sortOrder' => $request->post("sortOrder",'desc'), //排序（desc，asc）
            'pageNumber' => $page_no, //当前页码
            'pageSize' => $page_size, //一页显示的条数
            'start' => ($page_no-1) * $page_size, //开始位置
            'searchKey' => '', //搜索条件
            'type' => $type, //搜索条件
            'user_id' => $this->AU['id'],
            'start_time' => $request->post('start_time',''),
            'end_time' => $request->post('end_time',''),
            'id' => $request->post('id',''),
        );
        $list = $adminbonuslogModel->getBonusLogWithFilter($searchFilter);

        //获取总奖金
        $bonussaleModel = new BonusSale();
        $adminUserModel = new UserBase();
        $id = $request->post('id','');
        if ($id == ''){
            $user_list = $adminUserModel->getAdminSubuser($this->AU['id']);
            $bonus_sale = DB::table('bonus_sale')->whereIn('after_sale_id',$user_list)->get();
        }else{
            $bonus_sale = DB::table('bonus_sale')->whereIn('after_sale_id',[(int)$id])->get();
        }
        if ($bonus_sale){
            $bonus_sale = json_decode(json_encode($bonus_sale),true);
            $expect_bonus = $bonussaleModel->getAfterSaleTotalMoney($bonus_sale);
            $not_bonus = sprintf("%.2f",$expect_bonus['not_bonus']);
            $already_bonus = sprintf("%.2f",$expect_bonus['already_bonus']);
        }else{
            $not_bonus = 0.00;
            $already_bonus = 0.00;
        }
        $res = sprintf("%.2f",$res);
        //钱包总额 提成+奖金
        $result['total_bonus'] = $res;
        //奖金总额 已获得的奖金 + 未获得的奖金
        $result['expect_bonus'] = sprintf("%.2f",$not_bonus + $already_bonus);
        foreach ($list['rows'] as &$v){
            if (!$v['member_phone']){
                $v['member_phone'] = $v['admin_mobile'];
            }
            if (!$v['member_name']){
                $v['member_name'] = $v['admin_name'];
            }
        }
        $result['list'] = $list;
        $this->returnData['data'] = $result;
        return response()->json($this->returnData);

    }

    //预期奖金列表
    public function expect_bonus(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time','')),                                          //订单创建时间(开始)
            'end_time' => trim($request->post('end_time','')),                                              //订单创建时间(结束)
            'branch_id' => trim($request->post('branch_id','')),                                            //团队ID
            'user_id' => trim($request->post('user_id','')),                                                //销售ID
            'after_status' => trim($request->post('status','')),                                            //业绩订单状态
            'admin_id' => $this->AU['id'],
            'is_del' => 0,
            'surplus_time' => $request->post('surplus_time','')
        );
        $bonussaleModel = new BonusSale();
        $list = $bonussaleModel->getAfterSaleList($searchFilter);
        $list['rows'] = $bonussaleModel->buildAfterSaleListFields($list['rows'],trim($request->post('surplus_time','')));
//        $list['percentage_money'] = $bonussaleModel->getPercentageMoney($list['rows']);

        $con = Configs::first();
        $data['bonus_explain'] = $con->bonus_explain;
        $data['total_money'] = $list['not_bonus'] + $list['already_bonus'];
        $data['expect_bonus'] = $list['not_bonus'];
        $data['get_bonus'] = $list['already_bonus'];
        $data['list'] = $list;
        $data['total_money'] = sprintf("%.2f",$data['total_money']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //奖金删除
    public function delExpectDonus($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $bounsSaleModel = new BonusSale();
        $res = $bounsSaleModel->aftersaleDelete($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return response()->json($this->returnData);
    }

    //提现申请
    public function apply_money(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $money = $request->input('money');
        $remarks = $request->input('remarks');
        $admin = DB::table('admin_users')->where('id',$this->AU['id'])->first();
        //验证提现规则
        $configsModel = new Configs();
        $res = $configsModel->checkTakeMoney($this->AU['id'],$money,2);
        if ($res == 1){
            $data['code'] = 1;
            $data['msg'] = '提现金额小于规定金额';
            $data['data'] = '';
            return response()->json($data);
        }elseif ($res == 2 || $res == 3){
            $data['code'] = 1;
            $data['msg'] = '提现金额大于规定金额';
            $data['data'] = '';
            return response()->json($data);
        }elseif ($res == 4 ){
            $data['code'] = 1;
            $data['msg'] = '今天提现次数已用尽';
            $data['data'] = '';
            return response()->json($data);
        }elseif ($res == 5){
            $data['code'] = 1;
            $data['msg'] = '当月提现次数已用尽';
            $data['data'] = '';
            return response()->json($data);
        }
        if ($admin->sale_bonus < $money){
            $data['code'] = 1;
            $data['msg'] = '提现金额不能大于奖金金额';
            $data['data'] = '';
            return response()->json($data);
        }
        $data_ins['admin_users_id'] = $this->AU['id'];
        $data_ins['order_number'] = $this->getOrderSn();
        $data_ins['bonus_money'] = $money;
        $data_ins['cur_bonus'] = $admin->bonus - $money;
        $data_ins['remarks'] = $remarks;
        $data_ins['status'] = 0;
        $data_ins['handle_id'] = 0;
        $data_ins['type'] = 2;
        //开启事务
        Db::beginTransaction();
        try {
            //提现表更新
            $withDrawalModel = new Withdrawal();
            $take_ins_res = $withDrawalModel->takeMoneyInsert($data_ins);
            //管理员表更新
            $data_upd['sale_bonus'] = $admin->sale_bonus - $money;
            $admin_upt_res = AdminUser::where('id','=',$admin->id)->update($data_upd);
            if($take_ins_res && $admin_upt_res){
                DB::commit();
            }else{
                DB::rollback();
                $data['code'] = 1;
                $data['msg'] = '提现申请提交失败';
                $data['data'] = '';
                return response()->json($data);
            }
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            $data['code'] = 1;
            $data['msg'] = '提现申请提交失败';
            $data['data'] = '';
            return response()->json($data);
        }
        return response()->json($this->returnData);
    }

    //获取订单号
    private function getOrderSn(){
        $order_sn = date("ymdHis").rand(1000,9999);
        $take_money_model = new Withdrawal();
        $res = $take_money_model->getTakeMoneyByOrderNo($order_sn);
        if(is_array($res)){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }

    //员工资金操作
    public function capitalOperation(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['uid']= $request->post('uid','');
        $data['type'] = $request->post('type','');
        $data['dotype'] = $request->post('doType','');
        $data['money'] = $request->post('money',0);
        $data['remarks'] = $request->post('remarks',0);
        $data['admin_name'] = $this->AU['name'];
        $userBaseModel = new UserBase();
        $res = $userBaseModel->capitalOperation($data);
        if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '用户金额不足';
        }
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '操作失败';
        }
        return response()->json($this->returnData);
    }
}

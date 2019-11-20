<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;

use App\Models\Admin\Achievement;
use App\Models\Admin\AchievementExtend;
use App\Models\Admin\AfterSale;
use App\Models\Admin\BonusSale;
use App\Models\Member\MemberAssignLog;
use App\Models\Member\MemberContactLog;
use App\Models\Admin\WalletLogs;
use App\Models\Member\MemberBase;
use App\Models\Member\MemberLevel;
use App\Models\Admin\MemberSource;
use Illuminate\Http\Request;

class MemberController extends BaseController
{
    protected $member_fields = [
        'name' => '',
        'mobile' => '',
        'password' => '',
        'email' => '',
        'status' => 0,
        'openid' => '',
        'active_time' => '',
        'create_time' => ''
    ];

    protected $member_extend_fields = [
        'realname' => '',
        'type' => '',
        'avatar' => '',
        'level' => '',
        'recommend' => '',
        'addperson' => '',
        'cash_coupon' => 0,
        'balance' => 0,
        'position' => '',
        'company' => '',
        'wechat' => '',
        'qq' => '',
        'source' => '',
        'project' => '',
        'certify_name' => '',
        'certify_no' => '',
        'remarks' => '',
        'tencent_id' => '',
        'telephone' => '',
        'spare_mobile' => '',
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 客户等级列表 */
    public function memberLevel(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberLevelModel = new MemberLevel();
        $data = $memberLevelModel->getMemberLevelList();
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 客户来源列表 */
    public function memberSource(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberSourceModel = new MemberSource();
        $data = $memberSourceModel->getMemberSourceList();
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 客户数据列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $tencent_status = $request->input('tencent_status');
        $searchFilter = array(
            'sortName' => $request->post('sortName','update_time'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'tencent_status' => $tencent_status,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
            'level' => trim($request->post('level','')),
            'status' => trim($request->post('status','')),
            'min_balance' => trim($request->post('min_balance','')),
            'max_balance' => trim($request->post('max_balance','')),
            'start_time' => trim($request->post('start_time','')),
            'end_time' => trim($request->post('end_time','')),
            'admin_id' => $this->AU['id']
        );
        $memberModel = new MemberBase();
        $data = $memberModel->getMemberListWithFilter($searchFilter);

        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 客户详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberBase();
        $data = $memberModel->getMemberDetailByID($id);
        if(!is_array($data) || count($data)<1){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '该数据不存在';
            return response()->json($this->returnData);
        }
        $data['avatar'] = $this->processingPictures($data['avatar']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 添加客户 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member = [];
        $member_extend = [];
        foreach (array_keys($this->member_fields) as $field) {
            /* 验证参数未做 */
            $member[$field] = $request->post($field);
        }
        foreach (array_keys($this->member_extend_fields) as $field) {
            /* 验证参数未做 */
            $member_extend[$field] = $request->post($field,$this->member_extend_fields[$field]);
        }
        $memberModel = new MemberBase();
        $res = $memberModel->validMemberRepeat(['mobile'=>$member['mobile'],'email'=>$member['email']]);
        if($res['is_repeat']==1){
            $this->returnData = $res['returnData'];
            return response()->json($this->returnData);
        }
        $member['status'] = 1;
        if($member['password'] == ''){
            unset($member['password']);
        }else{
            $member['password'] = bcrypt($member['password']);
        }
        $member['active_time'] = date('Y-m-d H:i:s');
        $member['create_time'] = date('Y-m-d H:i:s');
        if($member_extend['type'] == 0){                                                                                //个人
            $tmp_data = array(
                'idcard_front_side' => $request->post('idcard_front_side',''),
                'idcard_back_side' => $request->post('idcard_back_side','')
            );
        }else{                                                                                                          //企业
            $tmp_data = array('enterprise_pic' => $request->post('enterprise_pic',''));
        }
        $member_extend['certify_pic'] = json_encode($tmp_data);
        $member_extend['recommend'] = $this->AU['id'];
        $member_extend['addperson'] = $this->AU['name'];
        $res = $memberModel->memberInsert($member, $member_extend);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 修改用户 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $member = [];
        $member_extend = [];
        foreach (array_keys($this->member_fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===null){
                continue;
            }
            $member[$field] = $request->post($field);
        }
        foreach (array_keys($this->member_extend_fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===null){
                continue;
            }
            $member_extend[$field] = $request->post($field,$this->member_extend_fields[$field]);
        }
        $memberModel = new MemberBase();
        $res = $memberModel->validMemberRepeat(['mobile'=>$member['mobile'],'email'=>$member['email']],$id);
        if($res['is_repeat']==1){
            $this->returnData = $res['returnData'];
            return response()->json($this->returnData);
        }
        if(isset($member['password']) && $member['password'] != ''){
            $member['password'] = bcrypt($member['password']);
        }
        if(strval($member_extend['type']) == '0'){                                                                      //个人
            $tmp_data = array(
                'idcard_front_side' => $request->post('idcard_front_side',''),
                'idcard_back_side' => $request->post('idcard_back_side','')
            );
        }else{                                                                                                          //企业
            $tmp_data = array('enterprise_pic' => $request->post('enterprise_pic',''));
        }
        $member_extend['certify_pic'] = json_encode($tmp_data);
        $res = $memberModel->memberUpdate($id, $member, $member_extend);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }

    /* 客户删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberBase();
        $res = $memberModel->memberDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
    
    /* 用户状态操作 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['status'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $id = $request->id;
        if($request->action=='status'){
            $status = trim($request->post('status',''));
            if($status=='' || !in_array(strval($status),['0','1'],true)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                return response()->json($this->returnData);
            }
            $memberModel = new MemberBase();
            $res = $memberModel->memberUpdate($id,['status'=>$status]);
            if($res){
                $this->returnData['msg'] = '操作成功';
                return response()->json($this->returnData);
            }
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '操作失败';
        return response()->json($this->returnData);
    }
    
    /* 客户金额操作 */
    public function moneyEdit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        if (!$this->is_su){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '无权限访问';
            return response()->json($this->returnData);
        }
        if(trim($request->post('remarks',''))==''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '请填写备注';
            return response()->json($this->returnData);
        }
        if ($request->input('money_type','') == ''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '请填金额类型';
            return response()->json($this->returnData);
        }
        $memberModel = new MemberBase();
        $info = $memberModel->getMemberExtendByID((int)$id);
        $log = array(
            'uid' => $id,
            'remarks' => $request->input("remarks"),
            'manage' => $this->AU["name"]
        );
        if($request->input("c") == "upwallet") {   //余额
            $money = $request->input("money");      //填写的金额
            if ($request->input("money_type") < 0 ){
                $log["type"] = 9;
                $log["operation"] = "减少余额";
                if ($info['balance'] < $money){
                    $this->returnData = ErrorCode::$admin_enum['params_error'];
                    $this->returnData['msg'] = '客户余额不足扣除';
                    return response()->json($this->returnData);
                }
                $data["balance"] = $info['balance'] - $money;
                $log["money"] = '-'.$money;
            }else{
                $log["type"] = 0;
                $log["operation"] = "增加余额";
                $data["balance"] = $info['balance'] + $money;
                $log["money"] = $money;
            }
            $log["wallet"] = $data["balance"];
        }else if($request->input("c") == "updonationamount"){   //赠送金
            $money = $request->input("money");                   //填写的金额
            if ($request->input("money_type") < 0){
                if ($info['cash_coupon'] < $money){
                    $this->returnData = ErrorCode::$admin_enum['params_error'];
                    $this->returnData['msg'] = '客户赠送金不足扣除';
                    return response()->json($this->returnData);
                }
                $data['cash_coupon'] = $info['cash_coupon'] - $money;
                $log['operation'] = '减少赠送金';
                $log["money"] = '-'.$money;
            }else{
                $data['cash_coupon'] = $info['cash_coupon'] + $money;
                $log['operation'] = '增加赠送金';
                $log["money"] = $money;
            }
            $log["type"] = $request->input("type");
            $log["wallet"] = $data["cash_coupon"];
        }else{
            $this->returnData['status'] = 105;
            $this->returnData['msg'] = '未知操作';
            return response()->json($this->returnData);
        }
        $walletLogModel = new WalletLogs();
        $walletLogModel->walletLogInsert($log);
        $res = $memberModel->memberExtendUpdate($id,$data);
        if(!$res){
            $this->returnData['status'] = 99;
            $this->returnData['msg'] = '更新失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '更新成功';
        return response()->json($this->returnData);
    }

    /* 来源列表 */
    public function sourceDataList(Request $request){
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
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
            'admin_id' => $this->AU['id']
        );
        $customerModel = new MemberSource();
        $data = $customerModel->getMemberSourceWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //查看客户资金明细
    public function CapitalDetails(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $sortName = $request->input("sortName",'id');    //排序列名
        $sortOrder = $request->input("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->input("pageNumber");  //当前页码
        $pageSize = $request->input("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置

        if ($request->input('capita_type') == 1){   //余额明细
            $total = WalletLogs::where("uid","=",$request->input('uid'))
                ->where(function ($query) {
                    $query->where("type","=","0")
                        ->orwhere("type","=","9")
                        ->orwhere("type","=","5");
                });
            $rows =  WalletLogs::where("uid","=",$request->input('uid'))
                ->where(function ($query) {
                    $query->where("type","=","0")
                        ->orwhere("type","=","9")
                        ->orwhere("type","=","5");
                });
        }else{   //赠送金明细
            $total = WalletLogs::where("uid","=",$request->input('uid'))
                ->where("type","!=","0")
                ->where("type","!=","9")
                ->where("type","!=","5");
            $rows =  WalletLogs::where("uid","=",$request->input('uid'))
                ->where("type","!=","0")
                ->where("type","!=","9")
                ->where("type","!=","5");
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['total'] = $total->count();
        $data['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();

        return response()->json($data);
    }

    //售后服务列表
    public function afterSaleService(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',30);
        $data['start'] = ($pageNo -1)*$pageSize;
        $data['pageSize'] = $pageSize;
        $data['type'] = $request->post('type','');
        $data['search'] = $request->post('search','');
        $data['star_class'] = $request->post('star_class','');
        $data['sortName'] = $request->post('sortName','');
        $data['sortOrder'] = $request->post('sortOrder','desc');
        $data['admin_id'] = $this->AU['id'];
        $member = new Achievement();
        $res = $member->serviceList($data);
        if (!$res){
            $info['total'] = 0;
            $info['rows'] = [];
            $this->returnData['data'] = $info;
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //沟通记录
    public function contactLog($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        //客户信息
        $MemberModel = new MemberBase();
        $res = $MemberModel->getMemberByID($id);
        //联系记录
        $memberContactLogModel = new MemberContactLog();
        $contactLog = $memberContactLogModel->getMemberContactList($id);
        if (!$contactLog){
            $res['contact_next_time'] = '';
            $res['contact_log'] = [];
        }else{
            $res['contact_next_time'] = $contactLog['next_time'];
            $res['contact_log'] = $contactLog['data'];
        }
        //指派记录
        $memberAssignLogModel = new MemberAssignLog();
        $assignLog = $memberAssignLogModel->assignLogList($id);
        if (!$assignLog){
            $res['assign_log'] = [];
        }else{
            $res['assign_log'] = $assignLog;
        }
        //获取管理人员
        $achievementExtendModel = new AchievementExtend();
        $extend = $achievementExtendModel->getManageInfo($id);
        $res['manager_name'] = $extend['manager_name'];
        $res['manager_id'] = $extend['manager_id'];
        $res['maintain_name'] = $extend['maintain_name'];
        $res['maintain_id'] = $extend['maintain_id'];
        $res['duty_name'] = $extend['duty_name'];
        $res['duty_id'] = $extend['duty_id'];
        $res['star_class'] = $extend['star_class'];
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //修改管理人员
    public function memberAssignLog(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['manager_id'] = $request->post('manager_id','');
        $data['maintain_id'] = $request->post('maintain_id','');
        $data['duty_id'] = $request->post('duty_id','');
        $data['member_id'] = $request->post('member_id','');
        $data['operation_uid'] = $this->AU['id'];
        $AchievementExtendModel = new AchievementExtend();
        $res = $AchievementExtendModel->updateManage($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改指派人失败';
        }
        return response()->json($this->returnData);
    }

    //添加联系记录
    public function contact(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['comm_time'] = $request->post('comm_time','');
        $data['contentlog'] = $request->post('contentlog','');
        $data['member_id'] = $request->post('member_id','');
        $data['is_contact'] = $request->post('is_contact','');
        $data['admin_user_id'] = $this->AU['id'];
        $contactLogModel = new MemberContactLog();
        $res = $contactLogModel->addContact($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加沟通记录失败';
        }
        return response()->json($this->returnData);
    }

    //维护信息
    public function addmaintainList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->getDataInfo($member_id);
        if ($res){
            if ($res['annex']){
                foreach ($res['annex'] as &$v){
                    $v['url'] = $this->processingPictures($v['url']);
                }
            }
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //修改提醒信息
    public function updateRemind(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $data['remind_time'] = $request->post('remind_time','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //修改星级
    public function updateStarClass(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $data['star_class'] = $request->post('star_class','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //修改详情
    public function updateDetails(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $data['details'] = $request->post('details','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //添加账号信息
    public function addAccountNumber(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id');
        $data['name'] = $request->post('name','');
        $data['account'] = $request->post('account','');
        $data['password'] = $request->post('password','');
        $fields['account_number'] = $data;
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$fields);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //删除运维信息和合同信息
    public function delDataInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['type'] = $request->post('type','');
        $data['member_id'] = $request->post('member_id','');
        $data['id'] = $request->post('id','');
        $achievementExtendModel = new AchievementExtend();
        $res = $achievementExtendModel->delDataInfo($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return response()->json($this->returnData);
    }

    //添加代码托管
    public function addTrusteeship(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['url'] = $request->post('url','');
        $data['describe'] = $request->post('describe','');
        $member_id = $request->post('member_id','');
        $fields['trusteeship'] = $data;
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$fields);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //添加附件信息
    public function addAnnex(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['url'] = $request->post('url','');
        $data['describe'] = $request->post('describe','');
        $member_id = $request->post('member_id','');
        $fields['annex'] = $data;
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$fields);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //合同信息
    public function contractList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->getcontractList($member_id);
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            foreach ($res as &$v){
                $v['url'] = $this->processingPictures($v['url']);
            }
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //添加合同信息
    public function addContract(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->post('name','');
        $data['url'] = $request->post('url','');
        $member_id = $request->post('member_id','');
        $fields['contract'] = $data;
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->addDataInfo($member_id,$fields);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //订单列表
    public function ordersMember(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $member_id = $request->post('member_id','');
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$pageSize;
        $data['pageSize'] = $pageSize;
        $achievementModel = new Achievement();
        $res = $achievementModel->getMemberOrdersList($member_id,$data);
        if (!$res){
            $info['total'] = 0;
            $info['rows'] = [];
            $this->returnData['data'] = $info;
        }else{
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //修改账号信息
    public function updateAccount(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['name'] = $request->post('name','');
        $data['account'] = $request->post('account','');
        $data['password'] = $request->post('password','');
        $data['member_id'] = $request->post('member_id','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->updateAccount($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //修改托管地址
    public function updateTrusteeship(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['member_id'] = $request->post('member_id','');
        $data['url'] = $request->post('url','');
        $data['describe'] = $request->post('describe','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->updateTrusteeship($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //修改附件地址
    public function updateAnnex(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['member_id'] = $request->post('member_id','');
        $data['url'] = $request->post('file','');
        $data['describe'] = $request->post('describe','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->updateAnnex($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //修改合同信息
    public function updateContract(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['member_id'] = $request->post('member_id','');
        $data['name'] = $request->post('name','');
        $data['url'] = $request->post('url','');
        $achievementExtend = new AchievementExtend();
        $res = $achievementExtend->updateContract($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }




























}

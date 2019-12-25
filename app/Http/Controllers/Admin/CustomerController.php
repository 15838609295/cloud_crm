<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AssignLog;
use App\Models\Admin\Configs;
use App\Models\Admin\Picture;
use App\Models\Customer\CustomerBase;
use App\Models\Member\MemberBase;
use App\Models\NotifyBase;
use App\Models\User\UserBase;
use foo\bar;
use Illuminate\Http\Request;
use App\Models\Communicationlog;
use App\Models\Admin\MemberSource;
use Carbon\Carbon;
use Excel;

class CustomerController extends BaseController
{
    protected $fields = [
        'name' => '',
        'realname' => '',
        'type' => '',
        'mobile' => '',
        'spare_mobile' => '',
        'email' => '',
        'recommend' => '',
        'addperson' => '',
        'position' => '',
        'company' => '',
        'wechat' => '',
        'qq' => '',
        'tencent_id' => '',
        'contact_next_time' => '',
        'source' => '',
        'project' => '',
        'progress' => '初步接触',
        'status' => 0,
        'remarks' => '',
        'cust_state' => 0,
        'telephone' => '',
    ];

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
        'telephone' => '',
        'tencent_id' => '',
        'spare_mobile' => '',
        'type' => '',
        'level' => 5,
        'recommend' => '',
        'addperson' => '',
        'position' => '',
        'company' => '',
        'wechat' => '',
        'qq' => '',
        'source' => '',
        'project' => '',
        'remarks' => ''
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

	/* 获取客户列表 */
	public function dataList(Request $request){
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
            'source' => trim($request->post('source','')),
            'cust_state' => trim($request->post('cust_state','')),
            'recommend' => trim($request->post('recommend','')),
            'progress' => trim($request->post('progress','')),
            'contact_status' => trim($request->post('contact_status','')),
            'next_start_time' => trim($request->post('next_start_time','')),
            'next_end_time' => trim($request->post('next_end_time','')),
            'start_time' => trim($request->post('start_time','')),
            'end_time' => trim($request->post('end_time','')),
            'admin_id' => $this->AU['id']
        );
        $customerModel = new CustomerBase();
        $data = $customerModel->getCustomerListWithFilter($searchFilter);
        $endDate = date("Y-m-d",time());
        foreach ($data["rows"] as $k => &$v){
            switch ($v['cust_state']){
                case 0;
                    if ($v['contact_next_time'] != '' && $v['contact_next_time'] != null && $v['contact_next_time'] != "0000-00-00 00:00:00"){
                        $set_time = substr($v['contact_next_time'], 0, 10);
                        $temp = ceil((strtotime($set_time)-strtotime($endDate))/(24 * 60 * 60));
                        if($temp==0){
                            $v["contact_next_times"] = "今天";
                        }else if($temp>0 && $temp<30){
                            $v["contact_next_times"] = $temp."天";
                        }else if($temp>=30&&$temp<360){
                            $v["contact_next_times"] = ceil($temp/30)."月";
                        }else if($temp>=360){
                            $v["contact_next_times"] = ceil($temp/(30*12))."年";
                        }else{
                            $v["contact_next_times"] = "逾期$temp" ."天";
                        }
                    }else{
                        $v["contact_next_times"] = "未联系";
                    }
                    break;
                case 1;
                    $v['contact_next_times'] = '已成交';
                    break;
            }
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }


    /* 客户详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new CustomerBase();
        $data = $memberModel->getCustomerDetailByID($id);
        if(!is_array($data) || count($data)<1){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '该数据不存在';
            return $this->return_result($this->returnData);
        }
        $assignModel = new AssignLog();
        $assign_res = $assignModel->getAssignLogByCustomerID($id);
        $data['assign_log'] = [];
        if($assign_res && count($assign_res)>0){
            $tmp = [];
            foreach ($assign_res as $key=>$value){
                $tmp[] = ['name'=>$value['admin_name'],'contact_time'=>$value['created_at']];
            }
            $data['assign_log'] = $tmp;
        }
        $communicationModel = new Communicationlog();
        $contact_log = $communicationModel->getFollowUpRecordByUid($id);
        $data['contact_record'] = [];
        if($contact_log){
            $data['contact_record'] = $contact_log;
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /*修改客户信息记录*/
    public function customerLog($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $customerModel = new CustomerBase();
        $res = $customerModel->customerLog($id);
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }
	
    /* 添加客户 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $customer = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $customer[$field] = $request->post($field,$this->fields[$field]);
        }
        //过滤需要验证字段的空格
        if ($customer['mobile'] != ''){
            $customer['mobile'] = preg_replace('# #','',$customer['mobile']);
        }
        if ($customer['email'] != ''){
            $customer['email'] = preg_replace('# #','',$customer['email']);
        }
        if ($customer['wechat'] != ''){
            $customer['wechat'] = preg_replace('# #','',$customer['wechat']);
        }
        if ($customer['qq'] != ''){
            $customer['qq'] = preg_replace('# #','',$customer['qq']);
        }
        if ($customer['company'] != ''){
            $customer['company'] = preg_replace('# #','',$customer['company']);
        }
        $rule_res = preg_match("/1((((3[0-3,5-9])|(4[5,7,9])|(5[0-3,5-9])|(66)|(7[1-3,5-8])|(8[0-9])|(9[1,8,9]))[0-9]{8})|((34)[0-8]\d{7}))/",$customer['mobile']);
        if ($rule_res != 1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '手机号格式不正确';
            return response()->json($this->returnData);
        }
        $customerModel = new CustomerBase();
        $res = $customerModel->validCustomerRepeat(['mobile'=>$customer['mobile'],'email'=>$customer['email'],'qq'=>$customer['qq'],'wechat'=>$customer['wechat'],'company'=>$customer['company']]);
        if($res['is_repeat'] == 1){
            $this->returnData = $res['returnData'];
            return $this->return_result($this->returnData);
        }
        $customer['status'] = 1;
        $customer['recommend'] = $this->AU['id'];
        $customer['addperson'] = $this->AU['name'];
        $res = $customerModel->customerInsert($customer);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return $this->return_result($this->returnData);
    }

    /* 修改客户 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $customer = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===null){
                $customer[$field] = '';
            }
            $customer[$field] = $request->post($field);
        }
        $customerModel = new CustomerBase();
        $res = $customerModel->validCustomerRepeat(['mobile'=>$customer['mobile'],'email'=>$customer['email'],'qq'=>$customer['qq'],'wechat'=>$customer['wechat'],'company'=>$customer['company']],$id);
        if($res['is_repeat']==1){
            $this->returnData = $res['returnData'];
            return $this->return_result($this->returnData);
        }
        //添加修改记录
        $customerModel->updateCustomerLog($this->AU['id'],$this->AU['name'],$id);
        $res = $customerModel->customerUpdate($id,$customer);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return $this->return_result($this->returnData);
    }

    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['batch_assign','batch_lead_in','submit_log','active_customer'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        //批量指派
        if($request->action=='batch_assign'){
            $result = $this->_batchAssign($request);
            return $this->return_result($result);
        }
        //批量导入客户
        if($request->action=='batch_lead_in'){
            $result = $this->_batchLeading($request);
            return $this->return_result($result);
        }
        //提交沟通记录
        if($request->action=='submit_log'){
            $result = $this->_submitContactLog($request);
            return $this->return_result($result);
        }
        //提交激活信息
        if($request->action=='active_customer'){
            $result = $this->_activeCustomer($request);
            return $this->return_result($result);
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '操作失败';
        return $this->return_result($this->returnData);
    }

    /* 批量指派 */
    private function _batchAssign($request){
        $r_data = $this->returnData;
        if(trim($request->post('list',''))==''){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '勾选列表不能为空';
            return $r_data;
        }
        if($request->id===NULL){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '转移人不能为空';
            return $r_data;
        }
        $list = explode(',',trim($request->post('list')));
        $customerModel = new CustomerBase();
        $result = $customerModel->getCustomerByID($list);
        $tmp_data = [];
        foreach ($result as $key=>$value){
            if(in_array($value['recommend'],$tmp_data)){
                continue;
            }
            $tmp_data[] = $value['recommend'];
        }
        $tmp_arr = [];
        $assign_log_data = [];
        $adminUserModel = new UserBase();
        $admin_res = $adminUserModel->getAdminByID($tmp_data);
        $data['recommend'] = $request->id;
        $field = [['id','in',$list]];
        $res = $customerModel->customerBatchUpdate($field,$data);
        //同步正式客户表
        $memberModel = new MemberBase();
        $memberres = $memberModel->memberExtendUpdaterecommend($list,$data['recommend']);

        if(!$res && !$memberres){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '更新失败';
            return $r_data;
        }
        $to_admin_res = $adminUserModel->getAdminByID((int)$request->id);
        foreach ($result as $key=>$value){
            $tmp_arr[$value['recommend']] = $value;
            if($value['realname']==''){
                $tmp_arr[$value['recommend']]['realname'] = '空';
            }
            foreach ($admin_res as $k=>$v){
                if($v['id']==$value['recommend']){
                    $assign_log_data[] = array(
                        'member_id' => $value['id'],
                        'assign_name' => $v['name'],
                        'assign_admin' => $to_admin_res['name'],
                        'assign_uid' => $v['id'],
                        'assign_touid' => $to_admin_res['id'],
                        'operation_uid' => $this->AU['id']
                    );
                    $params = array(
                        'title' => '指派客户通知',
                        'receive_user' => $to_admin_res['email'],
                        'options' => [
                            'name' => $to_admin_res['name'],
                            'admin' => $this->AU['name'],
                            'member' => $tmp_arr[$value['recommend']]
                        ]
                    );
                    $notifyModel = new NotifyBase();
//                    $notifyModel->sendMail($params);
                    $params = array(
                        'type' => 'assign_customer',
                        'receive_wechatid' => $to_admin_res['wechat_id'],
                        'receive_uname' => $to_admin_res['name'],
                        'send_uame' => $this->AU['name'],
                        'uname' => $tmp_arr[$value['recommend']]['realname'],
                        'umobile' => $tmp_arr[$value['recommend']]['mobile'],
                        'remark' => $tmp_arr[$value['recommend']]['remarks']
                    );
                    $configModel = new Configs();
                    $config = $configModel->getConfigByID(1);
                    if ($config['qywxLogin'] == 1){
                        $notifyModel->sendQYWechat($params);
                    }
                }
            }
        }
        $assignLogModel = new AssignLog();
        $res = $assignLogModel->assignLogInsert($assign_log_data,2);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '转移失败';
            return $r_data;
        }
        $r_data['msg'] = '转移成功';
        return $r_data;
    }

    /* 批量导入 */
    private function _batchLeading(Request $request){
        $r_data = $this->returnData;
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $base64_excel = trim($request->post('file',''));
            if (!$base64_excel){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return $this->return_result($this->returnData);
            }
            $files = json_decode($base64_excel,true);
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            $img_name = time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = 'Excel上传失败';
                return $this->return_result($this->returnData);
            }
            //下载
            $path = urldecode($url['ObjectURL']);
            $path = substr($path,strripos($path,"/")+1);
            $body = $pictureModel->getObgect($path);
            if (empty($body)){
                $data['code'] = 1;
                $data['msg'] = '导入失败';
                return $data;
            }
            $file_path = tempnam(sys_get_temp_dir(),time());
            file_put_contents($file_path, $body->__toString());

        }else{
            if($request->file('file')===NULL){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入文件不能为空';
                return $r_data;
            }
            $file = $request->file('file')->store('temporary');
            $file_path = 'storage/app/'.iconv('UTF-8', 'GBK',$file);
        }
        $tmp_arr = [];
        Excel::load($file_path, function($reader) use(&$tmp_arr) {
            $reader = $reader->getSheet(0);
            $tmp_arr = $reader->toArray();
        });
        array_shift($tmp_arr);
        $customerModel = new CustomerBase();
        $mobile_tmp = [];
        $member_source_tmp = [];
        $mobile_list = $customerModel->getCustomerFields(['mobile']);
        $memberSourceModel = new MemberSource();
        $member_source_list = $memberSourceModel->getMemberSourceList();
        foreach ($mobile_list as $key=>$value){
            $mobile_tmp[] = $value['mobile'];
        }
        foreach ($member_source_list as $key=>$value){
            $member_source_tmp[] = $value['source_name'];
        }
        $arr_tx = $this->_buildLeadingData($tmp_arr,$mobile_tmp,$member_source_tmp);
        if(!is_array($arr_tx)){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '更新失败';
            return $r_data;
        }
        if(count($arr_tx)>0){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = implode(',',$arr_tx).'未导入成功,其他的导入成功';
            return $r_data;
        }
        $r_data['msg'] = '导入成功';
        return $r_data;
    }

    /* 提交沟通记录 */
    private function _submitContactLog(Request $request){
        if($request->id===NULL){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '客户ID不能为空';
            return $r_data;
        }
        if(trim($request->post('progress',''))==''){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '转移人不能为空';
            return $r_data;
        }
        if(trim($request->post('contact_time',''))==''){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '沟通时间不能为空';
            return $r_data;
        }
        if(trim($request->post('content',''))==''){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '沟通内容不能为空';
            return $r_data;
        }
        $id = $request->id;
        $customerModel = new CustomerBase();
        $customerData = $customerModel->getCustomerByID($id);
        if(!is_array($customerData) || count($customerData)<1){
            $r_data = ErrorCode::$admin_enum['not_exist'];
            return $r_data;
        }
        $data = array(
            'member_id' => $id,
            'admin_user_id' => $this->AU['id'],
            'comm_time' => trim($request->post('contact_time')),
            'contentlog' => trim($request->post('content'))
        );
        $communicateModel = new Communicationlog();
        $res = $communicateModel->communicationLogInsert($data);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '提交失败';
            return $r_data;
        }
        $data = array(
            'contact_next_time' => $request->post('next_contact_time'),
            'progress' => trim($request->post('progress'))
        );
        $res = $customerModel->customerUpdate($id,$data);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '提交失败';
            return $r_data;
        }
        $r_data = $this->returnData;
        return $r_data;
    }

    /* 激活客户 */
    private function _activeCustomer(Request $request){
        $id = $request->id;
        $customerModel = new CustomerBase();
        $customerData = $customerModel->getCustomerByID($id);
        if(!is_array($customerData) || count($customerData)<1){
            $r_data = ErrorCode::$admin_enum['not_exist'];
            return $r_data;
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
            return $this->returnData;
        }
        $member['status'] = 1;
        if($member['password'] == ''){
            unset($member['password']);
        }else{
            $member['password'] = bcrypt($member['password']);
        }
        $member['active_time'] = date('Y-m-d H:i:s');
        $member['create_time'] = $customerData['created_at'];
        $member_extend['recommend'] = $customerData['recommend'];
        $member_extend['addperson'] = $customerData['addperson'];
        $res = $memberModel->activeCustomer($member, $member_extend);
        $customerModel->customerUpdate($id,['cust_state'=>1,'activation_time' => Carbon::now()->toDateTimeString()]);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '激活失败';
            return $r_data;
        }
        $r_data = $this->returnData;
        $r_data['msg'] = '激活成功';
        return $r_data;
    }

    private function _buildLeadingData($list,$mobile_list,$member_source_list){
        $i = 0;
        $arr_tx = [];$data = [];
        foreach($list as $val){
            if(trim($val[3]) == ''){
                continue;
            }
            if(in_array($val[3],$mobile_list)){
                $arr_tx[] = $val[3];
                continue;
            }
            $data[$i]['mobile'] = $val[3];
            $data[$i]['name'] = $val[1];
            $data[$i]['wechat'] = $val[4];
            $data[$i]['qq'] = $val[5];
            $data[$i]['company'] = $val[9];
            $data[$i]['remarks'] = $val[17];
            $data[$i]['project'] = $val[14];
            $data[$i]['recommend'] = $this->AU['id'];
            $data[$i]["addperson"] = $this->AU['name'];
            $data[$i]['created_at'] = Carbon::now();
            $data[$i]['updated_at'] = Carbon::now();
            if($val[7] == '个人'){
                $data[$i]['type'] = 0;
            }else{
                $data[$i]['type'] = 1;
            }
            if(trim($val[13])!='' && in_array(trim($val[13]),$member_source_list)){
                $data[$i]['source'] = trim($val[13]);
            }else{
                $data[$i]['source'] = '其他';
            }
            $data[$i]['realname'] = $val[2];
            $data[$i]['email'] = $val[6];
            $data[$i]['position'] = $val[10];
            $data[$i]['contact_next_time'] = '';
            $data[$i]['cust_state'] = 0;
            $data[$i]['progress'] = '初步接触';
            $data[$i]['status'] = 0;
            $i++;
        }
        $customerModel = new CustomerBase();
        foreach ($data as $key=>$value){
            $customerModel->customerInsert($value);
        }
        return $arr_tx;
    }

    /* 客户数据导出 */
    public function export(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['demo','data'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        if($request->action=='demo'){
            $fileName = 'demo';
            $con = Configs::first();
            $env = $con->env;
            if ($env == 'CLOUD'){
                $data['code'] = 3;
                $data['name'] = '客户信息导入.xlsx';
                $data['data'] = realpath(base_path('public/download')).'/'.$fileName.'.xlsx';
                return $data;
            }else{
                if(is_file(realpath(base_path('public/download')).'/'.$fileName.'.xlsx')){
                    return response()->download(realpath(base_path('public/download')).'/'.$fileName.'.xlsx',$fileName.'.xlsx');
                }
            }
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '文件不存在';
            return $this->return_result($this->returnData);
        }
        if($request->action=='data'){
            if(trim($request->get('list',''))==''){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '勾选列表不能为空';
                return $this->return_result($this->returnData);
            }
            $list = explode(',',trim($request->get('list','')));
            $customerModel = new CustomerBase();
            $maxID = $customerModel->getMaxCustomerID();
            $searchFilter = array(
                'sortName' => $request->get('sortName','id'),                                              //排序列名
                'sortOrder' => $request->get('sortOrder','desc'),                                          //排序（desc，asc）
                'pageNumber' => 1,                                                                                      //当前页码
                'pageSize' => $maxID,                                                                                   //一页显示的条数
                'start' => 0,                                                                                           //开始位置
                'searchKey' => trim($request->get('search','')),                                           //搜索条件
                'source' => trim($request->get('source','')),
                'cust_state' => trim($request->get('cust_state','')),
                'recommend' => trim($request->get('recommend','')),
                'progress' => trim($request->get('progress','')),
                'contact_status' => trim($request->get('contact_status','')),
                'next_start_time' => trim($request->get('next_start_time','')),
                'next_end_time' => trim($request->get('next_end_time','')),
                'start_time' => trim($request->get('start_time','')),
                'end_time' => trim($request->get('end_time','')),
                'list' => $list,
                'admin_id' => $this->AU['id']
            );
            $res = $customerModel->getCustomerListWithFilter($searchFilter);
            $obj = $res['rows'];
            $arr=[['ID','名称','真实姓名','手机','微信','qq','邮箱','类型','客户意向度','公司','职位','指派','上传者','来源','项目','注册时间','下次联系','备注']];
            foreach($obj as $key => $val){
                if($key != 0 && $val['id'] == $obj[$key-1]['id']){
                    $len = count($arr)-1;
                    $arr[$len][16] = $arr[$len][16]."\r\n".$val['comm_time'].'  '.$val['contentlog'];
                }else{
                    $arr[]=array(
                        $val['id'],
                        $val['name'] = str_replace("=","",$val['name']),
                        $val['realname'],
                        $val['mobile'],
                        $val['wechat'],
                        $val['qq'],
                        $val['email'],
                        $val['type'] = $val['type']==0 ? '个人' : '企业',
                        $val['progress'],
                        $val['company'],
                        $val['position'],
                        $val['admin_name'],
                        $val['addperson'],
                        $val['source'],
                        $val['project'],
                        $val['created_at'],
                        $val['contact_next_time'],
                        $val['remarks']
                    );
                }
            }
            $con = Configs::first();
            if ($con->env == 'CLOUD'){
                $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
                $a = Excel::create('客户信息',function($excel) use ($arr){
                    $excel->sheet('客户信息', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->string('xlsx');
                file_put_contents($temp_file,$a);
                $data['code'] = 3;
                $data['name'] = '客户信息.xlsx';
                $data['data'] = $temp_file;
                return $data;
            }else{
                Excel::create('客户信息',function($excel) use ($arr){
                    $excel->sheet('客户信息', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->export('xlsx');
            }
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '操作失败';
        return $this->return_result($this->returnData);
    }
	
	/* 客户删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $customerModel = new CustomerBase();
        $res = $customerModel->customerDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }

    //批量删除
    public function deleteall(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $ids = explode(',',$request->input('id'));
        $customerModel = new CustomerBase();
        $res = $customerModel->customerDeleteAll($ids);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }
}
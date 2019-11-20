<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Achievement;
use App\Models\Admin\MemberSource;
use App\Models\Admin\OperateLog;
use App\Models\Customer\CustomerBase;
use App\Models\Member\MemberBase;
use App\Models\User\UserBase;
use Illuminate\Http\Request;


class PlatformController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    //平台运营数据统计
    public function index(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberBase();
        $achievementModel = new Achievement();
        $customerModel = new CustomerBase();
        //今天
        $today['start_time'] = date('Y-m-d 00:00:00',time());
        $today['end_time'] = date('Y-m-d 23:59:59',time());
        $today['ids'] = '';
        //本月
        $month['start_time'] =date('Y-m-01 00:00:00',time());
        $month['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m" ),date("t" ),date("Y")));
        $month['ids'] = '';
        //昨天
        $yesterday['start_time'] = date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $yesterday['end_time'] = date('Y-m-d 23:59:59',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        $yesterday['ids'] = '';
        //获取两个月内数据
        $fields['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month',strtotime(date('Y-m'))));
        $fields['end_time'] = date('Y-m-t 23:59:59',time());
        $customer_total = $customerModel->statisticsCustomers($fields);
        //当日录入客户数
        $data['today_customer'] = 0;
        //当月录入客户数
        $data['month_customer'] = 0;
        //昨日录入客户数
        $data['yesterday_customer'] = 0;
        //总客户
        $customer = $customerModel->getCustomerTotal('');
        foreach ($customer_total as $v){
            if ($v['created_at'] > $today['start_time'] && $v['created_at'] < $today['end_time']){
                $data['today_customer'] += 1;
            }
            if ($v['created_at'] > $month['start_time'] && $v['created_at'] < $month['end_time']){
                $data['month_customer'] += 1;
            }
            if ($v['created_at'] > $yesterday['start_time'] && $v['created_at'] < $yesterday['end_time']){
                $data['yesterday_customer'] += 1;
            }
        }
        //当日业绩 修改为当月客户数
        //$data['today_bonus_number'] = $achievementModel->statisticsAchievent($today);
        //当月业绩
        $data['month_bonus_number'] = $achievementModel->statisticsAchievent($month);
        //当月激活客户数
        $number = $memberModel->statisticsMemberNumbers($month);
        $month_member = count($number);
        $data['month_member'] = $month_member;
        //当月成单率
        if ($month_member == 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = 0;
        }elseif ($month_member != 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = $month_member;
        }else{
            $data['month_conversion'] =sprintf('%.2f',($month_member/$data['month_customer'])*100);
        }
        //当月丢单率
        $data['month_no_conversion'] = 100-$data['month_conversion'];
        //昨日业绩 修改为上月客户数
        //$data['yesterday_bonus_number'] = $achievementModel->statisticsAchievent($yesterday);
        //记录表获取上月数据信息
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->platformList();
        //上月录入客户数
        $data['last_month_customer'] = $res['customer_number'];
        $data['last_month_member'] = $res['member_number'];
        //上月业绩
        $data['last_month_bonus_number'] = $res['bonus_number'];
        //曲线图数据 客户
        $data['customer_list'] = $res['customer_list'];
        //曲线图数据 业绩
        $data['customer_number'] = $res['bonus_list'];
        //曲线图数据 成单
        $data['order_from'] = $res['order_from'];
        //全部正式客户数
        $total_number = $memberModel->getMemberTotal('');
        if ($total_number == 0 && $customer == 0){
            $data['conversion'] = 0;
        }elseif ($total_number != 0 && $customer == 0){
            $data['conversion'] = $total_number;
        }else{
            $data['conversion'] = sprintf('%.2f',($total_number/$customer)*100);
        }
        //总单率
        $data['no_conversion'] = 100-$data['conversion'];
        //统计用户来源
        $memberSourceModel = new MemberSource();
        $fields['ids'] = '';
        $data['source'] = $memberSourceModel->sourceStatistics($fields);

        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //部门运营数据统计
    public function company(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $userModel = new UserBase();
        $ids = $userModel->getCompanyIdList($id);
        $memberModel = new MemberBase();
        $achievementModel = new Achievement();
        $customerModel = new CustomerBase();
        //今天
        $today['start_time'] = date('Y-m-d 00:00:00',time());
        $today['end_time'] = date('Y-m-d 23:59:59',time());
        $today['ids'] = $ids;
        //本月
        $month['start_time'] =date('Y-m-01 00:00:00',time());
        $month['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m" ),date("t" ),date("Y")));
        $month['ids'] = $ids;
        //昨天
        $yesterday['start_time'] = date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $yesterday['end_time'] = date('Y-m-d 23:59:59',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        $yesterday['ids'] = $ids;

        //获取两个月内的数据
        $fields['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month',strtotime(date('Y-m'))));
        $fields['end_time'] = date('Y-m-t 23:59:59',time());
        $customer_total = $customerModel->statisticsCustomers($fields);
        //当日客户数
        $data['today_customer'] = 0;
        //当月客户数
        $data['month_customer'] = 0;
        //昨日指派客户数
        $data['yesterday_customer'] = 0;
        foreach ($customer_total as $v){
            if ($v['created_at'] > $today['start_time'] && $v['created_at'] < $today['end_time'] && in_array($v['recommend'],$ids)){
                $data['today_customer'] += 1;
            }
            if ($v['created_at'] > $month['start_time'] && $v['created_at'] < $month['end_time'] && in_array($v['recommend'],$ids)){
                $data['month_customer'] += 1;
            }
            if ($v['created_at'] > $yesterday['start_time'] && $v['created_at'] < $yesterday['end_time'] && in_array($v['recommend'],$ids)){
                $data['yesterday_customer'] += 1;
            }
        }
        //当月业绩
        $data['month_bonus_number'] = $achievementModel->statisticsAchievent($month);
        //当月激活数
        $month['type'] = '';
//        $month_member_number = $customerModel->getCustomerActivationTimeTotal($month);
        $month_member_number = $memberModel->getMemberActivationTimeTotal($month);
        //当月激活客户数
        $data['month_member'] = $month_member_number;
        if ($month_member_number == 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = 0;
        }elseif ($month_member_number != 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = $month_member_number;
        }else{
            $data['month_conversion'] = sprintf('%.2f',($month_member_number/$data['month_customer'])*100);
        }
        //当月丢单率
        $data['month_no_conversion'] = 100-$data['month_conversion'];
        //昨日业绩  修改为上月激活客户数
        //$data['yesterday_bonus_number'] = $achievementModel->statisticsAchievent($yesterday);
        $fields['ids'] = $ids;
        //统计用户来源
        $memberSourceModel = new MemberSource();
        $source = $memberSourceModel->sourceStatistics($fields);
        //记录表获取上月数据信息
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->CompanyList($id);
        $len = count($res['source']);
        //处理客户来源
        foreach ($source as &$v){
            if ($len < 1){
                $v['last_month_customer'] = 0;
            }else{
                foreach ($res['source'] as $r_v){
                    if ($v['source_name'] == $r_v['source_name']){
                        $v['last_month_customer'] = $r_v['month_customer'];
                    }
                }
            }
        }
        $data['source'] = $source;
        //上月录入客户数
        $data['last_month_customer'] = $res['customer_number'];
        //上月激活客户数
        $data['last_month_member'] = $res['member_number'];
        //上月业绩
        $data['last_month_bonus_number'] = $res['bonus_number'];
        //曲线图数据 客户
        $data['customer_list'] = $res['customer_list'];
        //曲线图数据 业绩
        $data['customer_number'] = $res['bonus_list'];
        //曲线图数据 成单
        $data['order_from'] = $res['order_from'];
        //计算总成单率
        $customer_where['ids'] = $ids;
        $customer_where['type'] = '';
        $customer = $customerModel->getCompanyCustomerTotal($customer_where);
        //获取激活人数
        $activation_customer = $customerModel->getCustomerActivationTotal($customer_where);
        if (!$activation_customer && !$customer){
            $data['conversion'] = 0;
        }elseif ($month == 0 && $customer == 0){
            $data['conversion'] = 0;
        }elseif ($activation_customer != 0 && $customer == 0){
            $data['conversion'] = $activation_customer;
        }else{
            $data['conversion'] = sprintf('%.2f',($activation_customer/$customer)*100);
        }
        //总丢单率
        $data['no_conversion'] = 100-$data['conversion'];
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //部门下个人数据列表
    public function companyUserList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $userModel = new UserBase();
        $customerModel = new CustomerBase();
        $memberModel = new MemberBase();
        $achievementModel = new Achievement();
        $operateLogModel = new OperateLog();
        //今天
        $today['start_time'] = date('Y-m-d 00:00:00',time());
        $today['end_time'] = date('Y-m-d 23:59:59',time());
        //本月
        $month['start_time'] =date('Y-m-01 00:00:00',time());
        $month['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m" ),date("t" ),date("Y")));
        //昨天
        $yesterday['start_time'] = date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $yesterday['end_time'] = date('Y-m-d 23:59:59',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        //获取两个月内的数据
        $fields['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month',strtotime(date('Y-m'))));
        $fields['end_time'] = date('Y-m-t 23:59:59',time());
        $customer_total = $customerModel->statisticsCustomers($fields);
        $ids = $userModel->getCompanyIdList($id);
        $list = [];
        foreach ($ids as $k=>$id){
            $list[$k]['id'] = $id;
            $list[$k]['today_customer'] = 0;
            $list[$k]['month_customer'] = 0;
            $list[$k]['yesterday_customer'] = 0;
            foreach ($customer_total as $v){
                if ($v['recommend'] == $id && $v['created_at'] > $today['start_time'] && $v['created_at'] < $today['end_time']){
                    $list[$k]['today_customer'] += 1;
                }
                if ($v['recommend'] == $id && $v['created_at'] > $month['start_time'] && $v['created_at'] < $month['end_time']){
                    $list[$k]['month_customer'] += 1;
                }
                if ($v['recommend'] == $id && $v['created_at'] > $yesterday['start_time'] && $v['created_at'] < $yesterday['end_time']){
                    $list[$k]['yesterday_customer'] += 1;
                }
            }
            $user_info = $userModel->getAdminByID($id);
            $list[$k]['name'] = $user_info['name'];
            //当日业绩
            $today['ids'] = [$id];
            $list[$k]['today_bonus_number'] = $achievementModel->statisticsAchievent($today);
            //当月业绩
            $month['ids'] = [$id];
            $list[$k]['month_bonus_number'] = $achievementModel->statisticsAchievent($month);
            //昨日业绩
            $yesterday['ids'] = [$id];
            $list[$k]['yesterday_bonus_number'] = $achievementModel->statisticsAchievent($yesterday);
            //当月激活数
            $month['type'] = '';
            //$month_activation_customer = $customerModel->getCustomerActivationTimeTotal($month);
            $month_activation_customer = $memberModel->getMemberActivationTimeTotal($month);
            if ($month_activation_customer == 0 && $list[$k]['month_customer'] == 0){
                $list[$k]['month_conversion'] = 0;
            }elseif ($month_activation_customer != 0 && $list[$k]['month_customer'] == 0){
                $list[$k]['month_conversion'] = $month_activation_customer;
            }else{
                $list[$k]['month_conversion'] = sprintf('%.2f',($month_activation_customer/$list[$k]['month_customer'])*100);
            }
            //当月丢单率
            $list[$k]['month_no_conversion'] = sprintf('%.2f',100- $list[$k]['month_conversion']);
            //计算总成单率
            $customer_where['ids'] = [$id];
            $customer_where['type'] = '';
            $customer = $customerModel->getCompanyCustomerTotal($customer_where);
            $activation_customer = $customerModel->getCustomerActivationTotal($customer_where);
            if (!$activation_customer && !$customer){
                $list[$k]['conversion'] = 0;
            }elseif ($activation_customer == 0 && $customer == 0){
                $list[$k]['conversion'] = 0;
            }elseif ($activation_customer != 0 && $customer == 0){
                $list[$k]['conversion'] = $activation_customer;
            }else{
                $list[$k]['conversion'] = sprintf('%.2f',($activation_customer/$customer)*100);
            }
            //总丢单率
            $list[$k]['no_conversion'] = sprintf('%.2f',100-$list[$k]['conversion']);
            //记录表获取上月数据信息
            $res = $operateLogModel->personalData($id);
            $list[$k]['last_customer_number'] = $res['customer_number'];
            $list[$k]['last_bonus_number'] = $res['bonus_number'];
        }
        $this->returnData['data'] = $list;
        return response()->json($this->returnData);
    }

    //个人运营数据统计
    public function user(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $ids = [$id];
        $memberModel = new MemberBase();
        $achievementModel = new Achievement();
        $customerModel = new CustomerBase();
        //今天
        $today['start_time'] = date('Y-m-d 00:00:00',time());
        $today['end_time'] = date('Y-m-d 23:59:59',time());
        $today['ids'] = $ids;
        //本月
        $month['start_time'] =date('Y-m-01 00:00:00',time());
        $month['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m" ),date("t" ),date("Y")));
        $month['ids'] = $ids;
        //昨天
        $yesterday['start_time'] = date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $yesterday['end_time'] = date('Y-m-d 23:59:59',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        $yesterday['ids'] = $ids;

        //获取两个月内的数据
        $fields['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month',strtotime(date('Y-m'))));
        $fields['end_time'] = date('Y-m-t 23:59:59',time());
        $customer_total = $customerModel->statisticsCustomers($fields);
        //当日客户数
        $data['today_customer'] = 0;
        //当月客户数
        $data['month_customer'] = 0;
        //昨日指派客户数
        $data['yesterday_customer'] = 0;
        foreach ($customer_total as $v){
            if ($v['created_at'] > $today['start_time'] && $v['created_at'] < $today['end_time'] && in_array($v['recommend'],$ids)){
                $data['today_customer'] += 1;
            }
            if ($v['created_at'] > $month['start_time'] && $v['created_at'] < $month['end_time'] && in_array($v['recommend'],$ids)){
                $data['month_customer'] += 1;
            }
            if ($v['created_at'] > $yesterday['start_time'] && $v['created_at'] < $yesterday['end_time'] && in_array($v['recommend'],$ids)){
                $data['yesterday_customer'] += 1;
            }
        }
        //当日业绩 修改为当月激活人数
        //$data['today_bonus_number'] = $achievementModel->statisticsAchievent($today);
        //当月业绩
        $data['month_bonus_number'] = $achievementModel->statisticsAchievent($month);
        //当月激活数
        $month['type'] = '';
        //$month_activation_member = $customerModel->getCustomerActivationTimeTotal($month);
        $month_activation_member = $memberModel->getMemberActivationTimeTotal($month);
        //当月激活人数
        $data['month_member'] = $month_activation_member;
        if ($month_activation_member == 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = 0;
        }elseif ($month_activation_member != 0 && $data['month_customer'] == 0){
            $data['month_conversion'] = $month_activation_member;
        }else{
            $data['month_conversion'] = sprintf('%.2f',($month_activation_member/$data['month_customer'])*100);
        }
        //当月丢单率
        $data['month_no_conversion'] = sprintf('%.2f',100-$data['month_conversion']);
        //昨日业绩  修改为上月激活客户人数
        //$data['yesterday_bonus_number'] = $achievementModel->statisticsAchievent($yesterday);
        $fields['ids'] = $ids;
        //统计用户来源
        $memberSourceModel = new MemberSource();
        $source = $memberSourceModel->sourceStatistics($fields);
        //记录表获取上月数据信息
        $operateLogModel = new OperateLog();
        $res = $operateLogModel->personalList($id);
        $len = count($res['source']);
        //处理客户来源
        foreach ($source as &$v){
            if ($len < 1){
                $v['last_month_customer'] = 0;
            }else{
                foreach ($res['source'] as $r_v){
                    if ($v['source_name'] == $r_v['source_name']){
                        $v['last_month_customer'] = $r_v['month_customer'];
                    }
                }
            }
        }
        $data['source'] = $source;
        //上月录入客户数
        $data['last_month_customer'] = $res['customer_number'];
        //上月激活客户数
        $data['last_month_member'] = $res['member_number'];
        //上月业绩
        $data['last_month_bonus_number'] = $res['bonus_number'];
        //曲线图数据 客户
        $data['customer_list'] = $res['customer_list'];
        //曲线图数据 业绩
        $data['customer_number'] = $res['bonus_list'];
        //曲线图数据 成单
        $data['order_from'] = $res['order_from'];
        //计算总成单率
        $customer_where['ids'] = $ids;
        $customer_where['type'] = '';
        $customer = $customerModel->getCompanyCustomerTotal($customer_where);
        $activation_customer = $customerModel->getCustomerActivationTotal($customer_where);
//        $member = $memberModel->getCompanyMemberTotal($customer_where);
        if ($activation_customer == 0 && $customer == 0){
            $data['conversion'] = 0;
        }elseif ($activation_customer != 0 && $customer == 0){
            $data['conversion'] = $activation_customer;
        }else{
            $data['conversion'] = sprintf('%.2f',($activation_customer/$customer)*100);
        }
        //总丢单率
        $data['no_conversion'] = sprintf('%.2f',100-$data['conversion']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }



}

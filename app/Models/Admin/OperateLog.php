<?php

namespace App\Models\Admin;

use App\Models\Company;
use App\Models\User\UserBranch;
use foo\bar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OperateLog extends Model
{
    protected $table='operate_log';

    //获取平台运营数据
    public function platformList(){
        $month = date('Y-m',strtotime('-1 month',strtotime(date('Y-m'))));
        $last_month = DB::table($this->table)->where('month',$month)->where('type',1)->where('id',0)->first();
        $last_month = json_decode(json_encode($last_month),true);
        $data['customer_number'] = $last_month['customer_number'];
        $data['member_number'] = $last_month['member_number'];
        $data['bonus_number'] = $last_month['bonus_number'];
        //取所有平台数据做曲线图
        $memberList['label'] = '正式客户';
        $customerList['label'] = '客户仓库';
        $bonus_number['label'] = '业绩数据';
        $lose_order['label'] = '丢单率';
        $order_from['label'] = '成单率';
        $res = DB::table($this->table)->where('type',1)->orderBy('month','asc')->get();
        $res = json_decode(json_encode($res),true);
        foreach ($res as $v){
            $memberList['dataList'][] = $v['member_number'];
            $customerList['dataList'][] = $v['customer_number'];
            $bonus_number['dataList'][] = $v['bonus_number'];
            $lose_order['dataList'][] = $v['lose_order'];
            $order_from['dataList'][] = $v['order_form'];
        }
        $data['customer_list'] = [$memberList,$customerList];
        $data['bonus_list'] = [$bonus_number];
        $data['order_from'] = [$lose_order,$order_from];
        return $data;
    }

    //获取部门运营数据
    public function CompanyList($id){
        $month = date('Y-m',strtotime('-1 month',strtotime(date('Y-m'))));
        $last_month = DB::table($this->table)->where('month',$month)->where('type',2)->where('id',$id)->first();
        if (!$last_month){
            $data['customer_number'] = '';
            $data['member_number'] = '';
            $data['bonus_number'] = '';
            $data['source'] = [];
        }else{
            $last_month = json_decode(json_encode($last_month),true);
            $data['customer_number'] = $last_month['customer_number'];
            $data['member_number'] = $last_month['member_number'];
            $data['bonus_number'] = $last_month['bonus_number'];
            $data['source'] = json_decode($last_month['source'],true);
        }

        //取所有平台数据做曲线图
        $memberList['label'] = '正式客户';
        $customerList['label'] = '客户仓库';
        $bonus_number['label'] = '业绩数据';
        $lose_order['label'] = '丢单率';
        $order_from['label'] = '成单率';
        $now_month = (int)date('m');
        for ($i = 1;$i <= $now_month;$i++){
            if ($i < 10){
                $m = '0'.$i;
                $where['month'] = date("Y-$m");
            }else{
                $where['month'] = date("Y-$i");
            }
            $res = DB::table($this->table)->where('type',2)->where('id',$id)->where($where)->first();
            if (!$res){
                $memberList['dataList'][] = '';
                $customerList['dataList'][] = '';
                $bonus_number['dataList'][] = '';
                $lose_order['dataList'][] = '';
                $order_from['dataList'][] = '';
            }else{
                $res = json_decode(json_encode($res),true);
                $memberList['dataList'][] = $res['member_number'];
                $customerList['dataList'][] = $res['customer_number'];
                $bonus_number['dataList'][] = $res['bonus_number'];
                $lose_order['dataList'][] = $res['lose_order'];
                $order_from['dataList'][] = $res['order_form'];
            }
        }
        $data['customer_list'] = [$memberList,$customerList];
        $data['bonus_list'] = [$bonus_number];
        $data['order_from'] = [$lose_order,$order_from];
        return $data;
    }

    public function personalList($id){
        $month = date('Y-m',strtotime('-1 month',strtotime(date('Y-m'))));
        $last_month = DB::table($this->table)->where('month',$month)->where('type',3)->where('id',$id)->first();
        if (!$last_month){
            $data['customer_number'] = '';
            $data['member_number'] = '';
            $data['bonus_number'] = '';
            $data['source'] = [];
        }else{
            $last_month = json_decode(json_encode($last_month),true);
            $data['customer_number'] = $last_month['customer_number'];
            $data['member_number'] = $last_month['member_number'];
            $data['bonus_number'] = $last_month['bonus_number'];
            $data['source'] = json_decode($last_month['source'],true);
        }

        //取所有平台数据做曲线图
        $memberList['label'] = '正式客户';
        $customerList['label'] = '客户仓库';
        $bonus_number['label'] = '业绩数据';
        $lose_order['label'] = '丢单率';
        $order_from['label'] = '成单率';
        $now_month = (int)date('m');
        for ($i = 1;$i <= $now_month;$i++){
            if ($i < 10){
                $m = '0'.$i;
                $where['month'] = date("Y-$m");
            }else{
                $where['month'] = date("Y-$i");
            }
            $res = DB::table($this->table)->where('type',3)->where('id',$id)->where($where)->first();
            if (!$res){
                $memberList['dataList'][] = '';
                $customerList['dataList'][] = '';
                $bonus_number['dataList'][] = '';
                $lose_order['dataList'][] = '';
                $order_from['dataList'][] = '';
            }else{
                $res = json_decode(json_encode($res),true);
                $memberList['dataList'][] = $res['member_number'];
                $customerList['dataList'][] = $res['customer_number'];
                $bonus_number['dataList'][] = $res['bonus_number'];
                $lose_order['dataList'][] = $res['lose_order'];
                $order_from['dataList'][] = $res['order_form'];
            }
        }
        $data['customer_list'] = [$memberList,$customerList];
        $data['bonus_list'] = [$bonus_number];
        $data['order_from'] = [$lose_order,$order_from];
        return $data;
    }

    //部门列表获取用户上月数据
    public function personalData($id){
        $month = date('Y-m',strtotime('-1 month',strtotime(date('Y-m'))));
        $last_month = DB::table($this->table)->where('month',$month)->where('type',3)->where('id',$id)->first();
        if (!$last_month){
            $data['customer_number'] = 0;
            $data['bonus_number'] = 0;
        }else{
            $last_month = json_decode(json_encode($last_month),true);
            $data['customer_number'] = $last_month['customer_number'];
            $data['bonus_number'] = $last_month['bonus_number'];
        }
        return $data;
    }

    //获取销售人员成单率排行榜
    public function orderFormAllList($fields){
        $month = date('Y-m',time());
        $res = DB::table($this->table.' as of')
            ->select('of.customer_number','of.member_number','au.id','au.name','c.name as company_name','au.wechat_pic')
            ->leftJoin('admin_users as au','of.id','=','au.id')
            ->leftJoin('company as c','au.company_id','=','c.id')
            ->where('au.status',0)
            ->where('au.ach_status',0)
            ->where('of.type',3)
            ->where('of.month',$month);
        if ($fields['company_id'] != ''){
            $res->where('au.company_id',$fields['company_id']);
        }
        if ($fields['branch_id'] != ''){
            $columns = ['au.id'];
            $branchModel = new UserBranch();
            $branch_list = $branchModel->getBranchUserLists([(int)$fields['branch_id']],$columns);
            $ids = [];
            foreach ($branch_list as $v){
                $ids[] = $v['id'];
            }
            $res->whereIn('au.id',$ids);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if (count($result) < 1){
            return false;
        }
        $member_number = 0;
        $customer_number = 0;
        foreach ($result as &$v){
            if ($v['member_number'] > $member_number){
                $member_number = $v['member_number'];
            }
            if ($v['customer_number'] > $customer_number){
                $customer_number = $v['customer_number'];
            }
            if ($v['member_number'] != 0 && $v['customer_number'] != 0){
                $v['conversion'] = sprintf('%.2f',($v['member_number']/$v['customer_number'])*100);
            }else if($v['member_number'] != 0 && $v['customer_number'] == 0){
                $v['conversion'] = $v['member_number']*100;
            }else{
                $v['conversion'] = 0;
            }
        }

        if ($fields['order_name'] != ''){
            $last_names = array_column($result,$fields['order_name']);
            array_multisort($last_names,SORT_DESC,$result);
        }else{
            $last_names = array_column($result,'conversion');
            array_multisort($last_names,SORT_DESC,$result);
        }
        $list['list'] = $result;
        $list['member_number'] = $member_number;
        $list['customer_number'] = $customer_number;
        return $list;
    }

    //部门成单率排行
    public function orderFormCompanyList($fields){
        $month = date('Y-m',time());
        $companyModel = new Company();
        $company = $companyModel->getCompanyList(['id','name']);
        $res = DB::table($this->table.' as of')
            ->select('of.member_number','of.customer_number','c.id','c.name')
            ->leftJoin('admin_users as au','of.id','=','au.id')
            ->leftJoin('company as c','au.company_id','=','c.id')
            ->where('au.status',0)
            ->where('au.ach_status',0)
            ->where('of.month',$month)
            ->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        $result = [];
        $member_number = 0;
        $customer_number = 0;
        foreach ($res as $k=>$v){
            if (isset($result[$v['id']])){
                $result[$v['id']]['member_number'] = $result[$v['id']]['member_number']+ $v['member_number'];
                $result[$v['id']]['customer_number'] = $result[$v['id']]['customer_number']+ $v['customer_number'];
            }else{
                $result[$v['id']]['id'] = $v['id'];
                $result[$v['id']]['name'] = $v['name'];
                $result[$v['id']]['member_number'] = $v['member_number'];
                $result[$v['id']]['customer_number'] = $v['customer_number'];
            }
        }
        foreach ($company as $v){
            if (isset($result[$v['id']])){
                if ($result[$v['id']]['member_number'] > $member_number){
                    $member_number = $result[$v['id']]['member_number'];
                }
                if ($result[$v['id']]['customer_number'] > $customer_number){
                    $customer_number = $result[$v['id']]['customer_number'];
                }
                if ($result[$v['id']]['member_number'] != 0 && $result[$v['id']]['customer_number'] != 0){
                    $result[$v['id']]['conversion'] = sprintf('%.2f',($result[$v['id']]['member_number']/$result[$v['id']]['customer_number'])*100);
                }else if($result[$v['id']]['member_number'] != 0 && $result[$v['id']]['customer_number'] == 0){
                    $result[$v['id']]['conversion'] = $result[$v['id']]['member_number']*100;
                }else{
                    $result[$v['id']]['conversion'] = 0;
                }
            }else{
                $result[$v['id']]['id'] = $v['id'];
                $result[$v['id']]['name'] = $v['name'];
                $result[$v['id']]['member_number'] = 0;
                $result[$v['id']]['customer_number'] = 0;
                $result[$v['id']]['conversion'] = 0;
            }
        }
        if ($fields['order_name'] != ''){
            $last_names = array_column($result,$fields['order_name']);
            array_multisort($last_names,SORT_DESC,$result);
        }else{
            $last_names = array_column($result,'conversion');
            array_multisort($last_names,SORT_DESC,$result);
        }
        $list['list'] = $result;
        $list['member_number'] =  $member_number;
        $list['customer_number'] = $customer_number;
        return $list;
    }

    //团队成单率排行
    public function orderFormBranchList($fields){
        $month = date('Y-m',time());
        $branchModel = new Branch();
        $branch = $branchModel->getBranchList();
        $res = DB::table($this->table.' as of')
            ->select('of.member_number','of.customer_number','b.id','b.branch_name as name')
            ->leftJoin('admin_users as au','of.id','=','au.id')
            ->leftJoin('user_branch as ub','of.id','=','ub.user_id')
            ->leftJoin('branchs as b','ub.branch_id','=','b.id')
            ->where('au.status',0)
            ->where('au.ach_status',0)
            ->where('of.month',$month)
            ->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        $result = [];
        $member_number = 0;
        $customer_number = 0;
        foreach ($res as $k=>$v){
            if (isset($result[$v['id']])){
                $result[$v['id']]['member_number'] = $result[$v['id']]['member_number']+ $v['member_number'];
                $result[$v['id']]['customer_number'] = $result[$v['id']]['customer_number']+ $v['customer_number'];
            }else{
                $result[$v['id']]['id'] = $v['id'];
                $result[$v['id']]['name'] = $v['name'];
                $result[$v['id']]['member_number'] = $v['member_number'];
                $result[$v['id']]['customer_number'] = $v['customer_number'];
            }
        }
        foreach ($res as $v){
            if (isset($result[$v['id']])){
                if ($result[$v['id']]['member_number'] > $member_number){
                    $member_number = $result[$v['id']]['member_number'];
                }
                if ($result[$v['id']]['customer_number'] > $customer_number){
                    $customer_number = $result[$v['id']]['customer_number'];
                }
                if ($result[$v['id']]['member_number'] != 0 && $result[$v['id']]['customer_number'] != 0){
                    $result[$v['id']]['conversion'] = sprintf('%.2f',($result[$v['id']]['member_number']/$result[$v['id']]['customer_number'])*100);
                }else if($result[$v['id']]['member_number'] != 0 && $result[$v['id']]['customer_number'] == 0){
                    $result[$v['id']]['conversion'] = $result[$v['id']]['member_number']*100;
                }else{
                    $result[$v['id']]['conversion'] = 0;
                }
            }else{
                $result[$v['id']]['id'] = $v['id'];
                $result[$v['id']]['name'] = $v['name'];
                $result[$v['id']]['member_number'] = 0;
                $result[$v['id']]['customer_number'] = 0;
                $result[$v['id']]['conversion'] = 0;
            }
        }
        if ($fields['order_name'] != ''){
            $last_names = array_column($result,$fields['order_name']);
            array_multisort($last_names,SORT_DESC,$result);
        }else{
            $last_names = array_column($result,'conversion');
            array_multisort($last_names,SORT_DESC,$result);
        }
        $list['list'] = $result;
        $list['member_number'] = $member_number;
        $list['customer_number'] = $customer_number;
        return $list;
    }
}

<?php

namespace App\Models\Admin;

use App\Models\Customer\CustomerBase;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberSource extends Model
{
    protected $table_name='member_source';

    protected $fillable = [
        'source_id', 'source_name','created_at', 'updated_at'
    ];

    public function getSourceListWithFields()
    {
        $res = DB::table($this->table_name)
            ->select('source_name')
            ->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过ID获用户来源 */
    public function getMemberSourceByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 来源名称是否重复 */
    public function getMemberSourceByName($name,$id = null)
    {
        $res = DB::table($this->table_name)
            ->where('source_name',$name);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $res = $res->count();
        return $res;
    }

    /* 获取用户来源列表 */
    public function getMemberSourceList()
    {
        $res = DB::table($this->table_name)->orderBy("order", "desc")->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    /* 获取用户来源列表(带筛选条件) */
    public function getMemberSourceWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=""){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('source_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 添加用户来源 */
    public function memberSourceInsert($data)
    {
        $data['created_at']=Carbon::now()->toDateTimeString();
        $data['updated_at']=Carbon::now()->toDateTimeString();
        $res_id = DB::table($this->table_name)->insertGetId($data);
        if(!$res_id){
            return false;
        }
        return true;
    }

    /* 修改用户来源 */
    public function memberSourceUpdate($id, $data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除用户来源 */
    public function memberSourceDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    //用户来源数据统计
    public function sourceStatistics($fields){
        $res = DB::table($this->table_name)->select('source_name')->get();
        if (!$res){
            return false;
        }
        $memberModel = new MemberBase();
        $customerModel = new CustomerBase();
        $fields['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month',strtotime(date('Y-m'))));
        $fields['end_time'] = date('Y-m-t 23:59:59',time());
        $total_customer = $customerModel->statisticsCustomers($fields);

        $today['start_time'] = date('Y-m-d 00:00:00',time());
        $today['end_time'] = date('Y-m-d 23:59:59',time());
        $today['ids'] = $fields['ids'];

        $month['start_time'] =date('Y-m-01 00:00:00',time());
        $month['end_time'] = date("Y-m-d H:i:s",mktime(23,59,59,date("m" ),date("t" ),date("Y")));
        $month['ids'] = $fields['ids'];
        $month_member =  $memberModel->statisticsMemberNumbers($month);

        $yesterday['start_time'] = date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
        $yesterday['end_time'] = date('Y-m-d 23:59:59',mktime(0,0,0,date('m'),date('d'),date('Y'))-1);
        $yesterday['ids'] = $fields['ids'];

        $last_month['start_time'] = date('Y-m-01 00:00:00',strtotime('-1 month'));
        $last_month['end_time'] = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
        $last_month['ids'] = $fields['ids'];
        $time['start_time'] = '';
        $time['end_time'] = '';
        $time['ids'] = $fields['ids'];
        if ($fields['ids'] != ''){
            $where['type'] = 1;
            $where['ids'] = $fields['ids'];
            $member = $memberModel->getCompanyMemberTotal($where);
            $customer = $customerModel->getCompanyCustomerTotal($where);
        }else{
            $member = $memberModel->getMemberTotal(1);
            $customer = $customerModel->getCustomerTotal(1);
        }
        $res = json_decode(json_encode($res),true);

        foreach ($res as &$v){
            //今日客户量
            $v['today_customer'] = 0;
            //本月客户量
            $v['month_customer'] = 0;
            //昨天客户量
            $v['yesterday_customer'] = 0;
            //上月客户量
            $v['last_month_customer'] = 0;
            //本月激活人数
            $month_member_number = 0;
            //总客户量
            $customer_number = 0;
            //总激活客户
            $total_member_number = 0;
            foreach ($total_customer as $t_v){  //处理客户信息
                //今天客户数
                if ($t_v['created_at'] > $today['start_time'] && $t_v['created_at'] < $today['end_time'] && $t_v['source'] == $v['source_name']){
                    if ($fields['ids'] != '' && in_array($t_v['recommend'],$fields['ids'])){
                        $v['today_customer'] += 1;  //今天客户
                    }elseif ($fields['ids'] == ''){
                        $v['today_customer'] += 1;  //今天客户
                    }
                }
                //本月客户数
                if ($t_v['created_at'] > $month['start_time'] && $t_v['created_at'] < $month['end_time'] && $t_v['source'] == $v['source_name']){
                    if ($fields['ids'] != '' && in_array($t_v['recommend'],$fields['ids'])){
                        $v['month_customer'] += 1;  //本月客户
                    }elseif ($fields['ids'] == ''){
                        $v['month_customer'] += 1;  //本月客户
                    }
                }
                //昨天客户数
                if ($t_v['created_at'] > $yesterday['start_time'] && $t_v['created_at'] < $yesterday['end_time'] && $t_v['source'] == $v['source_name']){
                    if ($fields['ids'] != '' && in_array($t_v['recommend'],$fields['ids'])){
                        $v['yesterday_customer'] += 1;  //昨天客户
                    }elseif ($fields['ids'] == ''){
                        $v['yesterday_customer'] += 1;  //昨天客户
                    }
                }
                //上月客户数
                if ($t_v['created_at'] > $last_month['start_time'] && $t_v['created_at'] < $last_month['end_time'] && $t_v['source'] == $v['source_name']){
                    if ($fields['ids'] != '' && in_array($t_v['recommend'],$fields['ids'])){
                        $v['last_month_customer'] += 1;  //上月客户
                    }elseif ($fields['ids'] == ''){
                        $v['last_month_customer'] += 1;  //上月客户
                    }
                }
            }
            foreach ($customer as $c_v){
                if ($c_v['source'] == $v['source_name']){
                    $customer_number = $c_v['total_number'];
                }
            }

            //处理本月激活客户信息
            foreach ($month_member as $m_v){
                if ($m_v['source'] == $v['source_name']){
                    $month_member_number += 1;
                }
            }
            //处理总激活客户数
            foreach ($member as $m_v){
                if ($m_v['source'] == $v['source_name']){
                    $total_member_number += 1;
                }
            }
            //本月成单率
            if ($month_member_number == 0 && $v['month_customer'] == 0){
                $v['month_conversion'] = 0;
            }elseif ($month_member_number != 0 && $v['month_customer'] == 0){
                $v['month_conversion'] = $month_member_number;
            }else{
                $v['month_conversion'] = sprintf('%.2f',($month_member_number/$v['month_customer'])*100);
            }
            $v['month_no_conversion'] = 100 - $v['month_conversion'];
            //总成单率
            if ($total_member_number == 0 && $customer_number == 0){
                $v['conversion'] = 0;
            }elseif ($total_member_number != 0 && $customer_number == 0){
                $v['conversion'] = $total_member_number*100;
            }else{
                $v['conversion'] = sprintf('%.2f',($total_member_number/$customer_number)*100);
            }
            $v['no_conversion'] = 100 - $v['conversion'];
        }
        return $res;
    }











































}

<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssignLog extends Model
{
    protected $table_name = 'assign_log';

    public function getAssignLogByCustomerID($member_id)
    {
        $res = DB::table($this->table_name.' as al')
            ->select('al.*','au.name as admin_name')
            ->leftJoin('admin_users as au','au.id','=','al.assign_touid')
            ->where('al.member_id',$member_id)
            ->orderBy('al.id','desc')
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

    public function getAssignLogByDateWithUserID($id,$type,$date = '')
    {
        $res = DB::table($this->table_name);
        if($type=='in'){
            $res->select('assign_touid as uid',DB::raw('SUM(1) AS total_assign'))
                ->whereRaw('assign_uid!=assign_touid')
                ->whereIn('assign_touid',$id);
            $res->groupBy('assign_touid');
        }else{
            $res->select('assign_uid as uid',DB::raw('SUM(1) AS total_assign'))
                ->whereRaw('assign_uid!=assign_touid')
                ->whereIn('assign_uid',$id);
            $res->groupBy('assign_uid');
        }
        if(!empty($date)){
            $res->where('created_at','LIKE','%'.$date.'%');
        }
        $result = $res->get();
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    public function assignLogInsert($data,$type=null)
    {
        if($type!=null){
            foreach ($data as $key=>$value){
                $data[$key]['updated_at']=Carbon::now();
                $data[$key]['created_at']=Carbon::now();
            }
        }else{
            $data['updated_at']=Carbon::now();
            $data['created_at']=Carbon::now();
        }
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //根据用户id获取实际接收人数
    public function statisticsRealNumber($fields){
        $accept_res = DB::table($this->table_name);
        if ($fields['ids'] != '') {
            $accept_res->whereIn('assign_uid',$fields['ids']);
        }
        if ($fields['start_time'] != '' && $fields['end_time'] != ''){
            $accept_res->whereBetween('created_at',[$fields['start_time'],$fields['end_time']]);
        }
        $accept = $accept_res->count();
        $transfer_res = DB::table($this->table_name)->whereIn('assign_touid',$fields['ids']);
        if ($fields['start_time'] != '' && $fields['end_time'] != ''){
            $transfer_res->whereBetween('created_at',[$fields['start_time'],$fields['end_time']]);
        }
        $transfer = $transfer_res->count();
        $real = $transfer - $accept;
        return $real;
    }

    //根据月份获取部门指派人数
    public function curveData($fields){
        $list['label'] = '客户仓库';
        $list['dataList'] = [];
        $month = (int)date('m');
        for ($i = 1;$i <= $month;$i++){
            if ($i < 10){
                $m = '0'.$i;
                $time = mktime(00,00,00,$m,01,date('Y'));
                $start_time = date('Y-m-d 00:00:00',$time);
                $end_time = date('Y-m-t 23:59:59',$time);
            }else{
                $time = mktime(00,00,00,$i,01,date('Y'));
                $start_time = date('Y-m-d 00:00:00',$time);
                $end_time = date('Y-m-t 23:59:59',$time);
            }
            $out_number = DB::table($this->table_name)->whereBetween('created_at',[$start_time,$end_time])->whereIn('assign_uid',$fields['ids'])->count();
            $to_number = DB::table($this->table_name)->whereBetween('created_at',[$start_time,$end_time])->whereIn('assign_touid',$fields['ids'])->count();
            $list['dataList'][] = $to_number - $out_number;
        }
        return $list;
    }
}

<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransferWorkOrder extends Model
{
    protected $table='transfer_work_order';

    //转单列表
    public function getWorkOrderChangeOrder($fields){
        $res = DB::table($this->table.' as two')
            ->select('two.id','au.name as old_admin_name','au.mobile as old_admin_mobile','wo.description','two.change_remarks',
                's.name as street_name','two.verify_time','aus.name as now_admin_name','two.status','wot.label as type_name','two.created_at')
            ->leftJoin('work_order as wo','two.work_order_id','=','wo.id')
            ->leftJoin('admin_users as au','two.original_member_id','=','au.id')
            ->leftJoin('admin_users as aus','two.now_member_id','=','aus.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->whereNull('wo.deleted_at');
        if ($fields['startTime'] != '' && $fields['endTime'] != ''){
            $res->whereBetween('two.created_at',[$fields['startTime'],$fields['endTime']]);
        }elseif ($fields['startTime'] != '' && $fields['endTime'] == ''){
            $res->where('two.created_at','>',$fields['startTime']);
        }else if ($fields['startTime'] == '' && $fields['endTime'] != ''){
            $res->where('two.created_at','<',$fields['endTime']);
        }
        if ($fields['status'] != ''){
            $res->where('two.status',$fields['status']);
        }
        if ($fields['searchKey'] != ''){
            $searchKey = $fields['searchKey'];
            $res->where('au.name','LIKE'.'%'.$searchKey.'%')
                ->orwhere('au.mobile',$searchKey);
        }
        $list['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $list['rows'] = json_decode(json_encode($result),true);
        return $list;
    }

    //转单详情
    public function getChangeOrderInfo($id){
        $res = DB::table($this->table.' as two')
            ->select('two.id','au.name as admin_name','two.work_order_id','au.mobile as admin_mobile','two.change_remarks','two.status as change_status','wo.status as work_order_status',
                'm.name as member_name','m.mobile as member_mobile','wo.description','wo.pic_list','s.name as street_father_name','ss.name as street_son_name',
                'wo.address','wot.label as type_name','aus.name as now_admin_name','aus.mobile as now_admin_mobile','two.created_at as apply_time','wo.created_at','two.verify_time')
            ->leftJoin('work_order as wo','two.work_order_id','=','wo.id')
            ->leftJoin('member as m','wo.member_id','=','m.id')
            ->leftJoin('admin_users as au','two.original_member_id','=','au.id')
            ->leftJoin('admin_users as aus','two.now_member_id','=','aus.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('street as ss','wo.c_street_id','=','ss.id')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->where('two.id',$id)
            ->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $workOrderModel = new WorkOrder();
        $res['work_order_log'] = $workOrderModel->getWorkOrderLog($res['work_order_id'],1);
        return $res;
    }

    //转单审核
    public function changeOrderAdmin($id,$fields){
        $res = DB::table($this->table)->where('id',$id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if($res['status'] == 3){
            return -1;
        }
        DB::beginTransaction();
        try{
            $data['now_member_id'] = $fields['admin_id'];
            if ($fields['status'] == 0){   //拒绝
                $data['status'] = 2;
            }else{                          //通过
                $data['status'] = 3;
                //修改工单表
                $order_res = DB::table('work_order')->where('id',$res['work_order_id'])->first();
                $order_res = json_decode(json_encode($order_res),true);
                $where['former_admin_id'] = $order_res['admin_id'];
                $where['admin_id'] = $fields['admin_id'];
                $update_order_res = DB::table('work_order')->where('id',$res['work_order_id'])->update($where);
                if (!$update_order_res){
                    DB::rollback();
                    return false;
                }
            }
            $data['verify_time'] = Carbon::now()->toDateTimeString();
            $update_res = DB::table($this->table)->where('id',$id)->update($data);
            if (!$update_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        }
        catch (\Illuminate\Database\QueryException $e){
            DB::rollback();
            return false;
        }
    }

    //小程序 员工端 转单列表
    public function getTransferWorkOrder($fields,$type){
        //查询管理负责的街道
        $streetModel = new Street();
        $ids = $streetModel->getAdminStreetId($fields['id']);
        array_push($ids,$fields['id']);
        $res = DB::table($this->table.' as two')
            ->select('wo.id','wo.description','wo.address','two.created_at','two.status')
            ->leftJoin('work_order as wo','two.work_order_id','=','wo.id')
            ->whereIn('two.original_member_id',$ids)
            ->whereNull('wo.deleted_at');
        if ($type == 1){  //待审核
            $res->where('two.status',1);
        }else{
            $res->whereIn('two.status',[2,3]);
        }
        $list['count'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->get();
        if ($result){
            $list['rows'] = json_decode(json_encode($result),true);
            foreach ($list['rows'] as &$v){
                switch($v['status']) {
                    case 1;
                        $v['status_txt'] = '转单，待审核';
                        break;
                    case 2;
                        $v['status_txt'] = '转单，已驳回';
                        break;
                    case 3;
                        $v['status_txt'] = '转单，已通过';
                        break;
                }
            }
            return $list;
        }
        $list['rows'] = [];
        return $list;
    }

    //提交转单申请
    public function applyChangeOrder($fields){
        $streetModel = new Street();
        $ids = $streetModel->getAdminStreetId($fields['original_member_id']);
        array_push($ids,$fields['original_member_id']);
        //工单信息
        $res = DB::table('work_order')->where('id',$fields['work_order_id'])->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if (!in_array($res['admin_id'],$ids)){
            return -1;
        }
        DB::beginTransaction();
        try{
            //添加申请
            $fields['status'] = 1;
            $fields['created_at'] = Carbon::now()->toDateTimeString();
            $log_res = DB::table($this->table)->insert($fields);
            if (!$log_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        }
        catch (\Illuminate\Database\QueryException $e){
            DB::rollback();
            return false;
        }
    }

    //根据工单id查询有无转单记录
    public function getWorkOrderIdLog($id){
        $res = DB::table($this->table)->where('work_order_id',$id)->orderBy('created_at','desc')->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }
}
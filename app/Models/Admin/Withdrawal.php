<?php

namespace App\Models\Admin;

use App\Http\Config\ErrorCode;
use App\Library\Tools;
use App\Models\User\UserBase;
use App\Models\Wechat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Withdrawal extends Model
{
    protected $table_name='take_bonus';

    protected $fillable = [
        'id', 'admin_users_id', 'bonus_money', 'remarks', 'status', 'handle_id', 'created_at', 'updated_at'
    ];

    public function getTakeMoneyByID($id)
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

    public function getTakeMoneyByOrderNo($order_no)
    {
        $res = DB::table($this->table_name)->where('order_number',$order_no)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过筛选条件获取提现记录 */
    public function getWithdrawalListWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as tb')
//            ->select('tb.*','au.name as admin_name','aus.name as handle_name')
            ->select('tb.*','au.name as admin_name','aus.name as handle_name','ausd.job_status')
            ->leftJoin('admin_users as au','au.id','=','tb.admin_users_id')
            ->leftJoin('admin_users_extend as ausd','ausd.admin_id','=','au.id')
            ->leftJoin('admin_users as aus','aus.id','=','tb.handle_id');
        if($fields['searchKey'] != ''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('aus.name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    public function getApiTakeMoneyList($admin_id,$start)
    {
        $res = DB::table($this->table_name.' as tb')
            ->select('tb.*','au.name as admin_name','aus.name as handle_name')
            ->leftJoin('admin_users as au','au.id','=','tb.admin_users_id')
            ->leftJoin('admin_users as aus','aus.id','=','tb.handle_id')
            ->where('tb.admin_users_id','=',$admin_id)
            ->orderBy('tb.id','desc')
            ->skip($start)->take(20)
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

    /* 处理提现 */
    public function dealWithdrawal($data)
    {
        $res = DB::table($this->table_name.' as tb')
            ->select('tb.*','au.bonus as balance','au.name','au.wechat_id')
            ->leftJoin('admin_users as au','au.id','=','tb.admin_users_id')
            ->where('tb.id',$data['id'])
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $res['admin_id'] = $data['admin_id'];
        $res['admin_name'] = $data['admin_name'];
        if($data['check_res'] == 'refuse'){
            $r_data = $this->_refuseWithDrawal($res);
            return $r_data;
        }
        $r_data = $this->_confirmWithDrawal($res);
        return $r_data;
    }

    public function takeMoneyInsert($data)
    {
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function takeMoneyUpdate($field,$data)
    {
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name);
        if($field['column'] == 'id'){
            $res->where('id',$field['value']);
        }else{
            $res->where('order_number',$field['value']);
        }
        $result = $res->update($data);
        if(!$result){
            return false;
        }
        return true;
    }

    /* 拒绝提现 */
    private function _refuseWithDrawal($data)
    {
        DB::beginTransaction();
        try {
            $i_data['status'] = 2;
            $i_data['handle_id'] = $data['admin_id'];
            $fields = ['column' => 'id','value' => (int)$data['id']];
            $take_money_res = $this->takeMoneyUpdate($fields,$i_data);
            $a_data['bonus'] = $data['balance'] + $data['bonus_money'];
            $userModel = new UserBase();
            $admin_bonus_res = $userModel->adminUserUpdate($data['admin_users_id'],$a_data);
            if(!$take_money_res || !$admin_bonus_res){
                DB::rollback();
                $r_data = ErrorCode::$admin_enum['fail'];
                return $r_data;
            }
            DB::commit();
            $r_data = ErrorCode::$admin_enum['success'];
            return $r_data;
        } catch(\Illuminate\Database\QueryException $e) {
            $r_data = ErrorCode::$admin_enum['fail'];
            return $r_data;
        }
    }

    /* 通过提现 */
    private function _confirmWithDrawal($data)
    {
        $wechatModel = new Wechat();
        $res = $wechatModel->buildQYUserWechat(['userid' => $data['wechat_id']],'user_openid');
        if(!is_array($res) || $res['errcode']>1){
            $r_data = ErrorCode::$admin_enum['error'];
            $r_data['msg'] = '获取openid失败，请联系管理员';
            return $r_data;
        }
        $data['order_number'] = Tools::createGUID();
        $payData = array(
            'id' => (int)$data['id'],
            'openid' => $res['openid'],
            'name' => $data['name'],
            'bonus_money' => $data['bonus_money'],
            'order_number' => $data['order_number']
        );
        DB::beginTransaction();
        try {
            $i_data = array(
                'status' => 1,
                'handle_id' => $data['admin_id'],
                'order_number' => $data['order_number']
            );
            $fields = ['column' => 'id','value' => (int)$data['id']];
            $take_money_res = $this->takeMoneyUpdate($fields,$i_data);                                                  //提现表更新
            $log_data = array(
                'admin_users_id' => $data['admin_users_id'],
                'type' => 2,
                'money' => -$data['bonus_money'],
                'cur_bonus' => $data['cur_bonus'],
                'remarks' => $data['remarks'],
                'submitter' =>$data['name'],
                'auditor' => $data['admin_name'],
            );
            $adminBonusLogModel = new AdminBonusLog();
            $admin_bonus_res = $adminBonusLogModel->adminBonusLogInsert($log_data);                                     //提现记录表更新
            if(!$take_money_res || !$admin_bonus_res){
                DB::rollback();
                $r_data = ErrorCode::$admin_enum['fail'];
                $r_data['msg'] = '同意失败';
                return $r_data;
            }
            $res = $wechatModel->payment($payData);                                                                     //执行企业转账个人
            if(isset($res["code"])){
                $r_data = ErrorCode::$admin_enum['fail'];
                $r_data['msg'] = $res["msg"];
                return $r_data;
            }
            DB::commit();
            $r_data = ErrorCode::$admin_enum['success'];
            return $r_data;
        } catch(Exception $e) {
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '同意失败';
            return $r_data;
        }
    }
}

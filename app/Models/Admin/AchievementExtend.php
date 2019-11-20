<?php

namespace App\Models\Admin;

use App\Models\Member\MemberAssignLog;
use App\Models\Member\MemberBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AchievementExtend extends Model
{
    protected $table='achievement_extend';

    //根据客户id获取提示信息
    public function getDataById($id){
        $res = DB::table($this->table)->where('member_id',$id)->select('remind_time','expire')->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //修改管理信息
    public function updateManage($fields){
        $adminModel = new AdminUser();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $member_id = $fields['member_id'];
        if ($fields['manager_id'] != ''){
            $data['manager_id'] = $fields['manager_id'];
            $manager_info = $adminModel->getAdminByID($fields['manager_id']);
            $data['manager_name'] = $manager_info['name'];
            //添加记录指派记录
            $memberAssignLog = new MemberAssignLog();
            $ass_info['member_id'] = $member_id;
            $ass_info['operation_uid'] = $fields['operation_uid'];
            $ass_info['admin_id'] = $data['manager_id'];
            $ass_info['assign_admin'] = $manager_info['name'];
            $ass_res = $memberAssignLog->addAssignLog($ass_info);
            //修改
            $memberBaseModel = new MemberBase();
            $res = $memberBaseModel->memberExtendUpdaterecommend([$member_id],$fields['manager_id']);
            if (!$ass_res && !$res){
                return false;
            }
        }
        if ($fields['maintain_id'] != ''){
            $data['maintain_id'] = $fields['maintain_id'];
            $maintain_info = $adminModel->getAdminByID($fields['maintain_id']);
            $data['maintain_name'] = $maintain_info['name'];
        }
        if ($fields['duty_id'] != ''){
            $data['duty_id'] = $fields['duty_id'];
            $duty_info = $adminModel->getAdminByID($fields['duty_id']);
            $data['duty_name'] = $duty_info['name'];
        }
        $res = DB::table($this->table)->where('member_id',$member_id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取客户的管理员信息
    public function getManageInfo($id){
        $res = DB::table($this->table)->where('member_id',$id)->first();
        if (!$res){
            $adminModel = new AdminUser();
            $member_res = DB::table('member_extend')->where('member_id',$id)->select('recommend')->first();
            $member_res = json_decode(json_encode($member_res),true);
            $data['manager_id'] = $member_res['recommend'];
            $manager_info = $adminModel->getAdminByID($data['manager_id']);
            $data['manager_name'] = $manager_info['name'];
            $data['member_id'] = $id;
            $data['created_at'] = Carbon::now()->toDateTimeString();
            DB::table($this->table)->insert($data);
            $list['manager_name'] = $data['manager_name'];
            $list['manager_id'] = $data['manager_id'];
            $list['maintain_name'] = '';
            $list['maintain_id'] = '';
            $list['duty_name'] = '';
            $list['duty_id'] = '';
            $list['star_class'] = 1;
            return $list;
        }
        $res = json_decode(json_encode($res),true);
        $list['manager_name'] = $res['manager_name'];
        $list['manager_id'] = $res['manager_id'];
        $list['maintain_name'] = $res['maintain_name'];
        $list['maintain_id'] = $res['maintain_id'];
        $list['duty_name'] = $res['duty_name'];
        $list['duty_id'] = $res['duty_id'];
        $list['star_class'] = $res['star_class'];
        return $list;
    }

    //添加运维信息
    public function addDataInfo($member_id,$fields){
        $fields['updated_at'] = Carbon::now()->toDateTimeString();
        if (isset($fields['account_number'])){ //存在账号信息 查询原来信息组合
            $account_number = DB::table($this->table)->where('member_id',$member_id)->select('account_number')->first();
            $account_number = json_decode(json_encode($account_number),true);
            if ($account_number['account_number']){
                $info = json_decode($account_number['account_number'],true);
                $len = count($info);
                $info[$len] = $fields['account_number'];
                $fields['account_number'] = json_encode($info);
            }else{
                $fields['account_number'] = json_encode(array($fields['account_number']));
            }
        }
        if (isset($fields['trusteeship'])){ //存在托管信息 查询原来信息组合
            $trusteeship = DB::table($this->table)->where('member_id',$member_id)->select('trusteeship')->first();
            $trusteeship = json_decode(json_encode($trusteeship),true);
            if ($trusteeship['trusteeship']){
                $info = json_decode($trusteeship['trusteeship'],true);
                $len = count($info);
                $info[$len] = $fields['trusteeship'];
                $fields['trusteeship'] = json_encode($info);
            }else{
                $fields['trusteeship'] = json_encode(array($fields['trusteeship']));
            }
        }
        if (isset($fields['annex'])){ //存在附件信息 查询原来信息组合
            $annex = DB::table($this->table)->where('member_id',$member_id)->select('annex')->first();
            $annex = json_decode(json_encode($annex),true);
            if ($annex['annex']){
                $info = json_decode($annex['annex'],true);
                $len = count($info);
                $info[$len] = $fields['annex'];
                $fields['annex'] = json_encode($info);
            }else{
                $fields['annex'] = json_encode(array($fields['annex']));
            }
        }
        if (isset($fields['contract'])) { //存在合同信息 查询原来信息组合
            $contract = DB::table($this->table)->where('member_id', $member_id)->select('contract')->first();
            $contract = json_decode(json_encode($contract),true);
            if ($contract['contract']) {
                $info = json_decode($contract['contract'], true);
                $len = count($info);
                $info[$len] = $fields['contract'];
                $fields['contract'] = json_encode($info);
            }else{
                $fields['contract'] = json_encode(array($fields['contract']));
            }
        }
        if (isset($fields['remind_time'])){  //修改提示时间
            $now_time = strtotime(date('Y-m-d 00:00:00',time()));
            $set_time = strtotime($fields['remind_time']);
            $expire = ($set_time-$now_time)/86400;
            $fields['expire'] = $expire;
        }
        $res = DB::table($this->table)->where('member_id',$member_id)->update($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取运维信息
    public function getDataInfo($member_id){
        $res = DB::table($this->table.' as ae')
            ->select('ae.*','m.mobile','m.email','me.tencent_id','me.wechat','me.telephone','me.spare_mobile','me.remarks')
            ->leftJoin('member_extend as me','ae.member_id','=','me.member_id')
            ->leftJoin('member as m','ae.member_id','=','m.id')
            ->where('ae.member_id',$member_id)->first();
        if(!$res){
            $info['account_number'] = [];
            $info['trusteeship'] = [];
            $info['annex'] = [];
            return $info;
        }
        $res = json_decode(json_encode($res),true);
        $res['account_number'] = json_decode($res['account_number'],true);
        $res['trusteeship'] = json_decode($res['trusteeship'],true);
        $res['annex'] = json_decode($res['annex'],true);
        return $res;
    }

    //获取合同信息
    public function getcontractList($member_id){
        $res = DB::table($this->table)->where('member_id',$member_id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $contract = json_decode($res['contract'],true);
        return $contract;
    }

    //删除运维信息和合同信息
    public function delDataInfo($fields){
        //取数据
        $res = DB::table($this->table)->where('member_id',(int)$fields['member_id']);
        if ($fields['type'] == 'account'){
            $data = $res->select('account_number')->first();
            $data = json_decode(json_encode($data),true);
            $info = json_decode($data['account_number'],true);
        }elseif ($fields['type'] == 'trusteeship'){
            $data = $res->select('trusteeship')->first();
            $data = json_decode(json_encode($data),true);
            $info = json_decode($data['trusteeship'],true);
        }elseif ($fields['type'] == 'annex'){
            $data = $res->select('annex')->first();
            $data = json_decode(json_encode($data),true);
            $info = json_decode($data['annex'],true);
        }elseif ($fields['type'] == 'contract'){
            $data = $res->select('contract')->first();
            $data = json_decode(json_encode($data),true);
            $info = json_decode($data['contract'],true);
        }else{
            return false;
        }
        foreach ($info as $k=>$v){
            if ($k == $fields['id']){
                unset($info[$k]);
            }
        }
        $info = array_values($info);
        //保存数据
        if ($fields['type'] == 'account'){
            if ($info){
                $where['account_number'] = json_encode($info);
            }else{
                $where['account_number'] = '';
            }
        }else{
            if ($info){
                $where[$fields['type']] = json_encode($info);
            }else{
                $where[$fields['type']] = '';
            }
        }
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('member_id',$fields['member_id'])->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改账号信息
    public function updateAccount($fields){
        $member_id = $fields['member_id'];
        $id = $fields['id'];
        $member_account = DB::table($this->table)->where('member_id',$member_id)->select('account_number')->first();
        if (!$member_account){
            return false;
        }
        $member_account = json_decode(json_encode($member_account),true);
        $account_number = json_decode($member_account['account_number'],true);
        foreach ($account_number as $k=>&$v){
            if ($k == $id){
                $v['name'] = $fields['name'];
                $v['account'] = $fields['account'];
                $v['password'] = $fields['password'];
            }
        }
        $account_number = json_encode($account_number);
        $where['account_number'] = $account_number;
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('member_id',$member_id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改托管信息
    public function updateTrusteeship($fields){
        $member_id = $fields['member_id'];
        $id = $fields['id'];
        $member_trusteeship = DB::table($this->table)->where('member_id',$member_id)->select('trusteeship')->first();
        if (!$member_trusteeship){
            return false;
        }
        $member_trusteeship = json_decode(json_encode($member_trusteeship),true);
        $trusteeship = json_decode($member_trusteeship['trusteeship'],true);
        foreach ($trusteeship as $k=>&$v){
            if ($k == $id){
                $v['url'] = $fields['url'];
                $v['describe'] = $fields['describe'];
            }
        }
        $trusteeship = json_encode($trusteeship);
        $where['trusteeship'] = $trusteeship;
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('member_id',$member_id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改附件信息
    public function updateAnnex($fields){
        $member_id = $fields['member_id'];
        $id = $fields['id'];
        $member_annex = DB::table($this->table)->where('member_id',$member_id)->select('annex')->first();
        if (!$member_annex){
            return false;
        }
        $member_annex = json_decode(json_encode($member_annex),true);
        $annex = json_decode($member_annex['annex'],true);
        foreach ($annex as $k=>&$v){
            if ($k == $id){
                $v['url'] = $fields['url'];
                $v['describe'] = $fields['describe'];
            }
        }
        $annex = json_encode($annex);
        $where['annex'] = $annex;
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('member_id',$member_id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改合同信息
    public function updateContract($fields){
        $member_id = $fields['member_id'];
        $id = $fields['id'];
        $member_contract = DB::table($this->table)->where('member_id',$member_id)->select('contract')->first();
        if (!$member_contract){
            return false;
        }
        $member_contract = json_decode(json_encode($member_contract),true);
        $contract = json_decode($member_contract['contract'],true);
        foreach ($contract as $k=>&$v){
            if ($k == $id){
                $v['name'] = $fields['name'];
                $v['url'] = $fields['url'];
            }
        }
        $contract = json_encode($contract);
        $where['contract'] = $contract;
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('member_id',$member_id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }


































}

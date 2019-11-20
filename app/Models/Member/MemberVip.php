<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MemberVip extends Model{

    protected $table='member_vip';

    //获取公司名去重
    public function getEnterprise(){
        $res = DB::table($this->table)->select('enterprise_name as enterprise')->groupBy("enterprise")->get();
        return $res;
    }

    //会员列表无分组
    public function getList($fields){
        $res = DB::table($this->table.' as mv')
            ->leftJoin('member as m','m.id','=','mv.member_id')
            ->leftJoin('industry_type as in','m.industry_type_id','=','in.id')
            ->leftJoin('member_extend as me','me.member_id','=','mv.member_id')
            ->select('m.id','me.realname as name','m.create_time as created_at','m.mobile','mv.enterprise_name','mv.position','mv.education','in.name as industry_type_name','m.email','m.is_vip');
//        if ($data['status'] !=''){
//            $res->where('status',$data['status']);
//        }
        if ($fields['industry_type_id'] != ''){
            $res->where('m.industry_type_id',$fields['industry_type_id']);
        }
        if ($fields['enterprise_name'] !=''){
            $res->where('mv.enterprise_name',$fields['enterprise_name']);
        }
        if ($fields['searchKey'] != ''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('m.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('m.mobile', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('m.email', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('mv.school', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('mv.major', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('mv.position', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $data['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy('mv.'.$fields['sortName'], $fields['sortOrder'])->get();
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

    //获取会员详情
    public function getIdInfo($id){
        $res = DB::table($this->table.' as mv')
            ->select('mv.*','m.name','m.mobile','m.email','me.wechat','me.avatar','me.realname','m.is_vip','m.industry_type_id','it.name as industry_type_name')
            ->leftJoin('member as m','m.id','=','mv.member_id')
            ->leftJoin('industry_type as it','m.industry_type_id','=','it.id')
            ->leftJoin('member_extend as me','me.member_id','=','mv.member_id')
            ->where('mv.member_id',$id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //添加会员
    public function addMemberVip($member,$member_extend,$member_vip){
        DB::beginTransaction();
        try {
            $member['create_time'] = Carbon::now()->toDateTimeString();
            $id = DB::table('member')->insertGetId($member);
            if(!$id){
                DB::rollback();
                return false;
            }
            $member_extend['member_id'] = $id;
            $res = DB::table('member_extend')->insert($member_extend);
            if(!$res){
                DB::rollback();
                return false;
            }
            $member_vip['member_id'] = $id;
            $res_id = DB::table($this->table)->insert($member_vip);
            if (!$res_id){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    //删除会员
    public function delMemberId($id){
        DB::beginTransaction();
        try {
            $member_res = DB::table('member')->where('id',$id)->delete();
            if(!$member_res){
                DB::rollback();
                return false;
            }
            $res = DB::table('member_extend')->where('member_id',$id)->delete();
            if(!$res){
                DB::rollback();
                return false;
            }
            $res_id = DB::table($this->table)->where('member_id',$id)->delete();
            if (!$res_id){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    //获取某个字段所有值
    public function getFields($fields,$where = null){
        $res = DB::table($this->table)->select($fields);
        if (is_array($where)){
            $res->where($where);
        }
        $data = $res->get();
        $data = json_decode(json_encode($data),true);
        return $data;
    }

    //修改会员信息
    public function updayeMemberVip($id,$member,$member_extend,$member_vip){
        DB::beginTransaction();
        try {
            $member['update_time'] = Carbon::now()->toDateTimeString();
            $member_res = DB::table('member')->where('id',$id)->update($member);
            if(!$member_res){
                DB::rollback();
                return false;
            }
            $member_extend['update_time'] = Carbon::now()->toDateTimeString();;
            $res = DB::table('member_extend')->where('member_id',$id)->update($member_extend);
            if(!$res){
                DB::rollback();
                return false;
            }
            $member_vip['updated_at'] = Carbon::now()->toDateTimeString();
            $member_vip['member_id'] = $id;
            $res_id = DB::table($this->table)->where('member_id',$id)->update($member_vip);
            if (!$res_id){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    //获取会员列表按类型分组
    public function getVipGroup($search){
        $typeList = DB::table('industry_type')->get();
        $typeList = json_decode(json_encode($typeList),true);
        $vipList = DB::table($this->table.' as mv')
            ->select('me.avatar','me.realname','me.position','m.industry_type_id','m.id','me.company')
            ->leftJoin('member_extend as me','me.member_id','=','mv.member_id')
            ->leftJoin('member as m','m.id','=','mv.member_id')
            ->where('m.is_vip',2)
            ->where(function ($query) use ($search) {
                  $query->where('m.name', 'LIKE', '%' . $search . '%')
                      ->orwhere('m.mobile', 'LIKE', '%' .$search . '%')
                      ->orwhere('me.realname', 'LIKE', '%' .$search . '%')
                      ->orwhere('me.position', 'LIKE', '%' .$search . '%');
              })
            ->get();
        $vipList = json_decode(json_encode($vipList),true);
        foreach ($typeList as &$v){
            $v['list'] = [];
            $v['count'] = '';
            foreach ($vipList as $value){
                if ($v['id'] == $value['industry_type_id']){
                    $v['list'][] = $value;
                    $v['count'] = count($v['list']);
                }
            }
        }
        return $typeList;
    }

    //前台认证
    public function authenticationVip($id,$member,$member_extend,$member_vip){
        DB::beginTransaction();
        try {
            $member['is_vip'] = 1;
            $member['update_time'] = Carbon::now()->toDateTimeString();
            $member_res = DB::table('member')->where('id',$id)->update($member);
            if(!$member_res){
                DB::rollback();
                return false;
            }
            $member_extend['update_time'] = Carbon::now()->toDateTimeString();
            $member_extend_res = DB::table('member_extend')->where('member_id',$id)->update($member_extend);
            if(!$member_extend_res){
                DB::rollback();
                return false;
            }
            $member_vip['member_id'] = $id;
            $member_vip_res = DB::table($this->table)->insert($member_vip);
            if (!$member_vip_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    //待认证列表
    public function notMemberVip($data){
        $res = DB::table($this->table.' as mv')
            ->select('me.realname','m.mobile','m.id','it.name as vip_type_name','mv.position','mv.enterprise_name','m.create_time as created_at','m.email','m.is_vip')
            ->leftJoin('member as m','mv.member_id','=','m.id')
            ->leftJoin('member_extend as me','mv.member_id','=','me.member_id')
            ->leftJoin('industry_type as it','m.industry_type_id','=','it.id');
        if ($data['status'] != ''){
            $res->where('m.is_vip',$data['status']);
        }
        if ($data['searchKey'] != ''){
            $res->where(function ($query) use ($data) {
                $query->where('m.nanme', 'LIKE', '%' . $data . '%')
                    ->orwhere('m.mobile', 'LIKE', '%' .$data . '%')
                    ->orwhere('me.position', 'LIKE', '%' .$data . '%');
            });
        }
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($data['start'])->take($data['pageSize'])->orderBy($data['orderName'],$data['orderSort'])->get();
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        return $result;
    }

    //认证审核
    public function examineMemberVip($id,$data){
        $data['update_time'] = Carbon::now()->toDateTimeString();
        $res = DB::table('member')->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除认证信息
    public function delMemberVipInfo($id){
        $res = DB::table($this->table)->where('member_id',$id)->delete();
        if (!$res){
            return false;
        }
        $where['is_vip'] = 0;
        DB::table('member')->where('id',$id)->update($where);
        return true;
    }


























}

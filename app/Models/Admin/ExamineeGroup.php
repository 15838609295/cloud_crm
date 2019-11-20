<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExamineeGroup extends Model
{
    //考生组表
    protected $table='examinee_group';

    //组列表
    public function examineeGroupList($type){
        if (!$type){
            $member = DB::table($this->table)->where('group_type',0)->get();
            $admin = DB::table($this->table)->where('group_type',1)->get();
            $all = DB::table($this->table)->where('id',1)->get();
            $member = json_decode(json_encode($member),1);
            $admin = json_decode(json_encode($admin),1);
            $all = json_decode(json_encode($all),1);
            $res['member'] = $member;
            $res['admin'] = $admin;
            $res['all'] = $all;
            return $res;
        }else{
            if ($type == 'member'){
                $res = DB::table($this->table)
                    ->where(function ($query) {
                    $query->where('id', 1)
                          ->Orwhere('group_type',0);
                })
                ->get();
            }else{
                $res = DB::table($this->table)
                    ->where(function ($query) {
                        $query->where('id', 1)
                            ->Orwhere('group_type',1);
                    })
                    ->get();
            }
            $res = json_decode(json_encode($res),1);
            return $res;
        }
    }

    //添加分组
    public function addExamineeGroupData($data){
        $count = DB::table($this->table)->where('group_type',$data['group_type'])->count();
        if ($count >= 20){
            return -1;
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除分组
    public function delExamineeGroup($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        //删除分组，随即删除这个组下的所有考生
        $count = DB::table('examinee_group_role')->where('examinee_group_id',$id)->count();
        if ($count){
            DB::table('examinee_group_role')->where('examinee_group_id',$id)->delete();
        }
        return true;
    }

    //获取分组下的考生
    public function getGroupPeople($id){
        $res = DB::table('examinee_group_role')->where('examinee_group_id',$id)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),1);
        return $res;
    }

    //修改分组
    public function updateExamineeGroup($data){
        $where['name'] = $data['name'];
        $res = DB::table($this->table)->where('id',$data['id'])->update($where);
        if (!$res){
            return false;
        }
        return true;

    }

    //根据id获取名称
    public function getGroupName($id){
        $res = DB::table($this->table)->where('id',$id)->select('name')->first();
        $res = json_decode(json_encode($res),true);
        $name = $res['name'];
        return $name;
    }
}

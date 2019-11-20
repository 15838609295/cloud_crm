<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proposal extends Model{

    //留言表
    protected $table='proposal';

    //添加留言
    public function addData($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //留言列表
    public function getList($data){
        $res = DB::table($this->table)->select('*');
        if ($data['type'] != ''){
            if ($data['type'] == 1){
                $res->whereNotNull('picture_list');
            }else{
                $res->whereNull('picture_list');
            }
        }
        if ($data['start_time'] != '' && $data['end_time'] != ''){
            $res->whereBetween('created_at',[$data['start_time'],$data['end_time']]);
        }elseif ($data['start_time'] !='' && $data['end_time'] == ''){
            $res->where('created_at','>=',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] !=''){
            $res->where('created_at','=<',$data['end_time']);
        }
        if ($data['searchKey'] != ''){
            $searchKey = $data['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('content', 'LIKE', '%' .$searchKey . '%');
            });
        }
        $list['total'] = $res->count();
        $rows = $res->skip($data['start'])->take($data['page_size'])->orderBy('id','desc')->get();
        $list['rows'] = json_decode(json_encode($rows),true);
        return $list;
    }

    //留言详情
    public function getInfo($id){
        $res = DB::table($this->table.' as p')
            ->select('p.*','m.name','m.mobile','m.create_time','ml.name as level_name','me.company','me.position','me.wechat','me.qq')
            ->leftJoin('member as m','m.id','=','p.member_id')
            ->leftJoin('member_extend as me','me.member_id','=','p.member_id')
            ->leftJoin('member_level as ml','ml.id','=','me.level')
            ->where('p.id',$id)
            ->first();
        if ($res){
            $res = json_decode(json_encode($res),true);
            return $res;
        }
        return false;
    }

    //个人留言列表
    public function memberIdList($data){
        $res = DB::table($this->table)
            ->where('member_id',$data['member_id'])
            ->skip($data['start'])->take($data['pageSize'])
            ->orderBy('id','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //删除留言
    public function delProposal($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }


}

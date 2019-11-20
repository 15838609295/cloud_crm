<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ItemBank extends Model
{
    protected $table='item_bank';

    //试题列表
    public function itemList($data){
        $res = DB::table($this->table)
            ->whereNull('deleted_at')
            ->select('id','name','type','fraction');
        if ($data['type'] != ''){
            $res->where('type',$data['type']);
        }
        if ($data['classify'] == 'test'){ //试卷添加题目 去除已添加的题目
            $item_ids = DB::table('test_paper')->where('id',$data['id'])->select('item_bank_list')->first();
            $item_ids = json_decode(json_encode($item_ids),1);
            $id_list = json_decode($item_ids['item_bank_list'],1);
            $res->whereNotIn('id',$id_list);
        }
        if ($data['search'] != ''){
            $searchKey = $data['search'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($data['start'])->take($data['pageSize'])->orderBy('id', 'desc')->get();
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        foreach ($result['rows'] as &$v){
            if ($v['type'] == 1){
                $v['type_name'] = '单选题';
            }elseif ($v['type'] == 2){
                $v['type_name'] = '多选题';
            }elseif ($v['type'] == 3){
                $v['type_name'] = '填空题';
            }
        }
        return $result;
    }

    //试题详情
    public function itemInfo($id){
        $res = DB::table($this->table)->where('id',$id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $res['url'] = 'https://'.$_SERVER['SERVER_NAME'].$res['annex'];
        if ($res['type'] != 3){
            $res['answer'] = json_decode($res['answer'],1);
        }
        $res['option'] = json_decode($res['option'],1);
        return $res;
    }

    //添加试题
    public function additem($data){
        if ($data['type'] != 3){
            $answer = explode(',',$data['answer']);
            foreach ($answer as &$v){
                $v = (int)$v;
            }
            $data['answer'] = json_encode($answer);
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改试题
    public function updateItem($id,$data){
        if ($data['type'] != 3){
            $answer = explode(',',$data['answer']);
            foreach ($answer as &$v){
                $v = (int)$v;
            }
            $data['answer'] = json_encode($answer);
        }
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除试题
    public function delItem($id){
        $where['deleted_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //批量删除
    public function batchDelItem($ids){
        if (!is_array($ids)){
            return false;
        }
        foreach ($ids as $v){
            $where['deleted_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->table)->where('id',$v)->update($where);
            if (!$res){
                return false;
            }
        }
        return true;
    }








































}

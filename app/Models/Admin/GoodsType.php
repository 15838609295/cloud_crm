<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GoodsType extends Model
{
    protected $table='goods_type';

    //列表
    public function goodsTypeList(){
        $res = DB::table($this->table)->get();
        $res = json_decode(json_encode($res),true);
        if ($res){
            return $res;
        }else{
            return false;
        }
    }

    //增
    public function addGoodsType($data){
        $count = DB::table($this->table)->count();
        if ($count > 20){
            return -1;
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //删
    public function delGoodsType($id){
        $res = DB::table($this->table)->delete($id);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //改
    public function updateGoodsType($data){
        $list['name'] = $data['name'];
        $res = DB::table($this->table)->where('id',$data['id'])->update($list);
        if ($res){
            return true;
        }else{
            return false;
        }
    }
    //根据类型获取商品集合
    public function getGoodsNames($type_id){
        $res = DB::table($this->table.' as gt')
            ->select('g.goods_name')
            ->leftJoin('goods as g','gt.id','=','g.goods_type')
            ->where('gt.id',$type_id)
            ->get();
        $res = json_decode(json_encode($res),true);
        $data = [];
        foreach ($res as $v){
            $data[] = $v['goods_name'];
        }
        return $data;
    }
}

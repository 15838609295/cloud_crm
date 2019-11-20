<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Goods extends Model
{
    protected $table_name='goods';

    protected $fillable = [
        'id', 'goods_name', 'goods_type', 'price', 'price_type', 'goods_pic', 'body', 'long', 'status',
        'goods_version','is_del','created_at','updated_at'
    ];

    public function getGoodByID($id)
    {
        $res = DB::table($this->table_name.' as g')
            ->select('g.*','gt.name as goods_type_txt')
            ->leftJoin('goods_type as gt','g.goods_type','=','gt.id')
            ->where('g.id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 获取商品列表 */
    public function getGoodsList($fields=['*'])
    {
        $res = DB::table($this->table_name)
            ->select($fields)
            ->where('is_del','=',0)
            ->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 获取商品列表(带筛选条件) */
    public function getGoodsWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as g')
            ->select('g.id','g.goods_name','g.goods_pic','g.goods_type','g.status','g.goods_top','gy.name as goods_type_name')
            ->leftJoin('goods_type as gy','gy.id','=','g.goods_type');
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('g.goods_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if(isset($fields['is_del'])){
            $res->where('g.is_del',$fields['is_del']);
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

    public function getGoodsByStatus($status)
    {
        $res = DB::table($this->table_name)->where('status',$status)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 添加商品 */
    public function goodsInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 修改商品 */
    public function goodsUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }
}

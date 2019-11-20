<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Goods extends Model
{
    protected $table_name='goods';

    protected $fillable = [
        'id', 'goods_name', 'goods_type', 'price_type', 'goods_pic', 'body', 'status',
        'goods_version','is_del','created_at','updated_at'
    ];

    public function getGoodByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $res['pic_list'] = json_decode($res['pic_list'],true);
        $res['goods_version'] = json_decode($res['goods_version'],true);
        return $res;
    }

    /* 获取商品列表(带筛选条件) */
    public function getGoodsWithFilter($fields)
    {
        $res = DB::table($this->table_name)
            ->select('id','goods_name','goods_pic','goods_type','status','goods_top');
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('goods_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if(isset($fields['is_del'])){
            $res->where('is_del',$fields['is_del']);
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

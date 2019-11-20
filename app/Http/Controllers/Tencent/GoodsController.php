<?php

namespace App\Http\Controllers\Tencent;

use App\Http\Config\ErrorCode;
use App\Models\Goods;

class GoodsController extends BaseController
{
    public function __construct()
    {
        $this->noCheckOpenidAction = ['goodsList']; //不校验openid
        parent::__construct();
    }

    //商品列表
    public function goodsList()
    {
        $params = request()->post();
        $pageNumber = isset($params['pageNumber']) && $params['pageNumber']!='' ? $params['pageNumber'] : 1;                     //当前页码
        $pageSize = isset($params['pageSize']) && $params['pageSize']!='' ? $params['pageSize'] : 10;                            //一页显示的条数
        $start = ($pageNumber-1) * $pageSize;                                                                           //开始位置
        $type = isset($params['type']) && trim($params['type'])!='' ? $params['type'] : '';
        $sortName = isset($params['sortName']) && $params['sortName']!='' ? $params['sortName'] : 'price';
        $sortOrder = isset($params['sortOrder']) && $params['sortOrder']!='' ? $params['sortOrder'] : 'asc';
        $rows = Goods::select('id','goods_name','goods_type','price','goods_pic','goods_version')->where('status',0);
        if($type!=''){
            $rows->where('goods_type',(int)$type);
        }
        $total = $rows;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $res = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();
        if(!$res){
            $this->result['data'] = $data;
            echo json_encode($this->result);exit;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['data'] = $data;
            return $this->result;
        }
        foreach ($res as &$v){
            $goods_data = json_decode($v['goods_version'],true);
            if (count($goods_data)> 0){
                $v['price'] = $goods_data['0']['originalPrice'];
            }else{
                $v['price'] = $v['price'];
            }
        }
        $data['rows'] = $res;
        $this->result['data'] = $data;
        echo json_encode($this->result);exit;
    }

    //商品详情
    public function goodsDetail()
    {
        $params = request()->post();
        if(!isset($params['id']) || $params['id']==''){
            return $this->verify_parameter(ErrorCode::$api_enum["params_not_exist"], 'id');
        }
        $res = Goods::where('id',$params['id'])->first();
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '该商品不存在';
            echo json_encode($this->result);exit;
        }
        $res = json_decode(json_encode($res),true);
        $goods_data = json_decode($res['goods_version'],true);
        if (count($goods_data)> 0){
            $res['price'] = $goods_data['0']['originalPrice'];
        }else{
            $res['price'] = $res['price'];
        }
        if(!is_array($res) || count($res)<1){
            $this->result['status'] = 1;
            $this->result['msg'] = '该商品不存在';
            echo json_encode($this->result);exit;
        }
        $res['goods_version'] = json_decode($res['goods_version'],true);
        $this->result['data'] = $res;
        echo json_encode($this->result);exit;
    }
}
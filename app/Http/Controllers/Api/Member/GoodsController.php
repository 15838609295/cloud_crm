<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Config\ErrorCode;
use App\Models\Admin\GoodsType;
use App\Models\Goods;

class GoodsController extends BaseController
{
    public function __construct()
    {
        $this->noCheckOpenidAction = ['goodsList','typeList']; //不校验openid
        parent::__construct();
    }

    //商品类型列表
    public function typeList(){
        $goodstypeModel = new GoodsType();
        $list = $goodstypeModel->goodsTypeList();
        $this->result['data'] = $list;
        echo json_encode($this->result);exit;
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
        $type_list = GoodsType::select('id','name')->get();
        $type_list = json_decode(json_encode($type_list),true);
        $type_data = [];
        foreach ($type_list as $v){
            $type_data[$v['id']] = $v['name'];
        }
        foreach ($res as &$v){
            $v['type_name'] = $type_data[$v['goods_type']];
            $goods_data = json_decode($v['goods_version'],true);
            if ($v['price_type'] != 1){
                foreach ($goods_data as $g){
                    if (count($g['subitem']) > 0){
                        $v['price'] = $g['subitem'][0]['salePrice'];
                        break;
                    }
                }
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
        //获取类型
        $type_list = GoodsType::select('id','name')->get();
        $type_list = json_decode(json_encode($type_list),true);
        $type_data = [];
        foreach ($type_list as $v){
            $type_data[$v['id']] = $v['name'];
        }
        $res = json_decode(json_encode($res),true);
        $res['type_name'] = $type_data[$res['goods_type']];
        $goods_data = json_decode($res['goods_version'],true);
        foreach ($goods_data as $g){
            if (count($g['subitem']) > 0){
                $res['price'] = $g['subitem'][0]['salePrice'];
                break;
            }
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
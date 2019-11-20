<?php

namespace App\Http\Controllers\Wxapi;

use App\Http\Config\ErrorCode;
use App\Models\Admin\GoodsType;
use App\Models\Goods;
use App\Models\MemberLevel;

class GoodsController extends BaseController
{
    public function __construct(){
        $this->noCheckOpenidAction = ['goodsList','typeList']; //不校验openid
        parent::__construct();
    }

    //商品列表
    public function goodsList(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $params = request()->post();
        $pageNumber = isset($params['pageNumber']) && $params['pageNumber']!='' ? $params['pageNumber'] : 1;                     //当前页码
        $pageSize = isset($params['pageSize']) && $params['pageSize']!='' ? $params['pageSize'] : 10;                            //一页显示的条数
        $start = ($pageNumber-1) * $pageSize;                                                                           //开始位置
        $type = isset($params['type']) && trim($params['type'])!='' ? $params['type'] : '';
        $sortName = isset($params['sortName']) && $params['sortName']!='' ? $params['sortName'] : 'price';
        $sortOrder = isset($params['sortOrder']) && $params['sortOrder']!='' ? $params['sortOrder'] : 'asc';
        $rows = Goods::select('id','goods_name','goods_type','price_type','price','goods_pic','goods_version')->where('status',0)->where('is_del',0);
        if($type!=''){
            $rows->where('goods_type',(int)$type);
        }
        $total = $rows;
        $data['total'] = $total->count();
        $res = $rows->get();
        if(!$res){
            $this->result['data'] = $data;
            return response()->json($this->result);
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            $this->result['data'] = $data;
            return response()->json($this->result);
        }
        //获取商品类型
        $goodsTypeModel = new GoodsType();
        $typeList = $goodsTypeModel->goodsTypeList();
        $newType = [];
        $typeList = json_decode(json_encode($typeList),true);
        foreach ($typeList as $t){
            $newType[$t['id']] = $t['name'];
        }
        $result = [];
        if (is_array($this->user) && $this->user['discount'] == 0){
            $this->user['discount'] = 100;
        }else{
            $this->user = ['discount' => 100];
        }
        //等级信息
        foreach($res as $k=>$v){
            $goods_data = json_decode($v['goods_version'],true);
            $price_info = $goods_data[0]['subitem'];
            $result[$k]['id'] = $v['id'];
            $result[$k]['name'] = $v['goods_name'];
            $result[$k]['goods_pic'] = $v['goods_pic'];
            $result[$k]['price'] = $price_info[0]['salePrice'];
            if ($v['price_type'] != 1){
                $result[$k]['dlprice'] = number_format($price_info[0]['salePrice']*$this->user['discount']/100,2);
            }else{
                $result[$k]['dlprice'] = $price_info[0]['salePrice'];
            }
            $result[$k]['goods_type'] = $newType[$v['goods_type']];
        }
        //根据字段last_name对数组$data进行降序排列
        $last_names = array_column($result,'dlprice');
        foreach ($last_names as &$l){
            if (strstr($l,',')){
                $l =  str_replace(',','',$l);
            }
        }
        if ($sortOrder == 'asc'){
            array_multisort($last_names,SORT_ASC,$result);
        }elseif ($sortOrder == 'desc'){
            array_multisort($last_names,SORT_DESC,$result);
        }
        $pagedata = array_slice($result,$start,$pageSize);
        $data['rows'] = $pagedata;
        $this->result['data'] = $data;
        return response()->json($this->result);
    }

    //商品详情
    public function goodsDetail(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $params = request()->post();
        if(!isset($params['id']) || $params['id']==''){
            $this->result['status'] = 1;
            $this->result['msg'] = 'id不能为空';
            return response()->json($this->result);
        }
        if (is_array($this->user) && $this->user['discount'] == 0){
            $this->user['discount'] = 100;
        }else{
            $this->user = ['discount' => 100];
        }
        $res = Goods::where('id',$params['id'])->first();
        if(!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '该商品不存在';
            return response()->json($this->result);
        }
        //获取商品类型
        $goodsTypeModel = new GoodsType();
        $typeList = $goodsTypeModel->goodsTypeList();
        $typeList = json_decode(json_encode($typeList),true);
        $newType = [];
        foreach ($typeList as $t){
            $newType[$t['id']] = $t['name'];
        }
        $res = json_decode(json_encode($res),true);
        $goods_version = json_decode($res['goods_version'],true);
        $res['goods_type'] = $newType[$res['goods_type']];
        foreach ($goods_version as &$v){
            if ($v['subitem']){
                if ($res['price_type'] != 1){
                    foreach ($v['subitem'] as &$s){
                        $s['dlprice'] = number_format($s['salePrice']*$this->user['discount']/100,2);
                    }
                }else{
                    foreach ($v['subitem'] as &$s){
                        $s['dlprice'] = $s['salePrice'];
                    }
                }

            }
        }
        $res['goods_version'] = $goods_version;
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //商品类型列表
    public function typeList(){
        global $scf_data;
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $goodsTypeModel = new GoodsType();
        $res = $goodsTypeModel->goodsTypeList();
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '类型列表不存在';
            return response()->json($this->result);
        }
        $this->result['data'] = $res;
        if ($scf_data['IS_SCF'] === true){
            $this->result['banner'] = 'https://'.$scf_data['host'].'/release/crm-api/2.png';
        }else{
            $this->result['banner'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/banners/2.png';
        }
        return response()->json($this->result);
    }
}
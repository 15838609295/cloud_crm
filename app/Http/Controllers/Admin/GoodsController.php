<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\GoodsType;
use App\Models\Goods;
use Illuminate\Http\Request;


class GoodsController extends BaseController
{
    protected $fields = [
        'goods_name' => '',
        'goods_pic' => '',
        'pic_list' => '',
        'goods_type' => '',
        'body' => '',
        'status' => '',
        'goods_top' => '',
        'price_type' => ''
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 商品列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','goods_top'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
            'is_del' => 0
        );
        $goodsModel = new Goods();
        $data = $goodsModel->getGoodsWithFilter($searchFilter);
        foreach ($data['rows'] as &$v){
            $v['goods_pic'] = $this->processingPictures($v['goods_pic']);
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 全部商品列表 */
    public function goodsList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goodsModel = new Goods();
        $data = $goodsModel->getGoodsList(['id','goods_name']);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 商品详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $userModel = new Goods();
        $data = $userModel->getGoodByID($id);
        if(!$data){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '数据不存在';
            return $this->return_result($this->returnData);
        }
        if (isset($data['goods_pic'])){
            $data['goods_pic'] = $this->processingPictures($data['goods_pic']);
        }
        if (isset($data['pic_list'])){
            $data['pic_list'] = $this->processingPictures($data['pic_list']);
        }
        $goods_version = json_decode($data['goods_version'],true);
        foreach ($goods_version as &$v){
            if (isset($v['image'])){
                $v['image'] = $this->processingPictures($v['image']);
            }
        }
        $data['goods_version'] = json_encode($goods_version);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 添加商品 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goods = [];
        $goods_attr = $request->post('goods_attr');
        if($goods_attr==null){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '产品规格不能为空';
            return $this->return_result($this->returnData);
        }
        $goods_attr = json_decode($goods_attr,true);
        foreach ($this->fields as $key=>$value) {
            /* 验证参数未做 */
            $goods[$key] = $request->post($key);
        }
        $tmp_arr = [];
        if(count($goods_attr)<1){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '产品规格不能为空';
            return $this->return_result($this->returnData);
        }
        foreach ($goods_attr as $key=>$value){
            if(trim($value['goods_version'])==''){
                continue;
            }
            if(trim($value['image'])==''){
                continue;
            }
            $arr = [];
            if(count($value['subitem']) > 0){
                foreach ($value['subitem'] as $k=>$v){
                    if (trim($v['count']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格库存不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['goodsNum']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格货号不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['goods_version']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格名称不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['originalPrice']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格原价不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['salePrice']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格售价不能为空';
                        return $this->return_result($this->returnData);
                    }
                    $arr[$k]['count'] = $v['count'];
                    $arr[$k]['goodsNum'] = $v['goodsNum'];
                    $arr[$k]['goods_version'] = $v['goods_version'];
                    $arr[$k]['originalPrice'] = $v['originalPrice'];
                    $arr[$k]['salePrice'] = $v['salePrice'];
                }
            }else{
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '请填写子规格信息';
                return $this->return_result($this->returnData);
            }
            $tmp_arr[] = array(
                'goods_version' => $value['goods_version'],
                'image' => $value['image'],
                'subitem' => $arr
            );
        }
        $goods['is_del'] = 0;
        $goods['goods_version'] = json_encode($tmp_arr);
        $goodsModel = new Goods();
        $res = $goodsModel->goodsInsert($goods);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return $this->return_result($this->returnData);
    }

    /* 修改商品 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $goodsModel = new Goods();
        $data = $goodsModel->getGoodByID((int)$id);
        if (!$data){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '数据不存在';
            return $this->return_result($this->returnData);
        }
        $goods = [];
        $goods_attr = $request->post('goods_attr');
        if($goods_attr==null){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '产品规格不能为空';
            return $this->return_result($this->returnData);
        }
        $goods_attr = json_decode($goods_attr,true);
        foreach ($this->fields as $key=>$value) {
            $goods[$key] = $request->post($key);
        }
        $tmp_arr = [];
        if(count($goods_attr)<1){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '产品规格不能为空';
            return $this->return_result($this->returnData);
        }
        foreach ($goods_attr as $key=>$value){
            if(trim($value['goods_version'])==''){
                continue;
            }
            if(trim($value['image'])==''){
                continue;
            }
            $arr = [];
            if(count($value['subitem']) > 0){
                foreach ($value['subitem'] as $k=>$v){
                    if (trim($v['count']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格库存不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['goodsNum']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格货号不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['goods_version']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格名称不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['originalPrice']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格原价不能为空';
                        return $this->return_result($this->returnData);
                    }
                    if (trim($v['salePrice']) == ''){
                        $this->returnData = ErrorCode::$admin_enum['params_error'];
                        $this->returnData['msg'] = '子规格售价不能为空';
                        return $this->return_result($this->returnData);
                    }
                    $arr[$k]['count'] = $v['count'];
                    $arr[$k]['goodsNum'] = $v['goodsNum'];
                    $arr[$k]['goods_version'] = $v['goods_version'];
                    $arr[$k]['originalPrice'] = $v['originalPrice'];
                    $arr[$k]['salePrice'] = $v['salePrice'];
                }
            }else{
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '子规格信息不能为空';
                return $this->return_result($this->returnData);
            }
            $tmp_arr[] = array(
                'goods_version' => $value['goods_version'],
                'image' => $value['image'],
                'subitem' => $arr
            );
        }
        $goods['goods_version'] = json_encode($tmp_arr);
        $goodsModel = new Goods();
        $res = $goodsModel->goodsUpdate($id,$goods);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return $this->return_result($this->returnData);
    }

    /* 商品操作 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        if (!isset($request->action) || !in_array($request->action,['status'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $status = $request->post('status','');
        if(!in_array(strval($status),['0','1'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $goodsModel = new Goods();
        $res = $goodsModel->goodsUpdate($id,['status'=>$status]);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '更新失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '更新成功';
        return $this->return_result($this->returnData);
    }
    
    /* 删除商品 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data = array(
            'is_del' => 1,
            'status' => 0
        );
        $goodsModel = new Goods();
        $res = $goodsModel->goodsUpdate($id,$data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }

    //商品类型列表
    public function goods_type_list(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goodstypeModel = new GoodsType();
        $list = $goodstypeModel->goodsTypeList();
        $this->returnData['data'] = $list;
        return $this->return_result($this->returnData);
    }

    //添加商品类型
    public function add_goods_type(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goodstypaModel = new GoodsType();
        $parameter['name'] = $request->input('name','');
        if (!$parameter['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $parameter['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        if (mb_strlen($parameter['name']) >= 20){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '类型名称长度不能超过20个字';
            return $this->return_result($this->returnData);
        }
        $res = $goodstypaModel->addGoodsType($parameter);
        if ($res === -1){
            $this->returnData['coed'] = 1;
            $this->returnData['msg'] = '类型数量已上限';
            return $this->return_result($this->returnData);
        }elseif ($res){
            $this->returnData['msg'] = '添加成功';
            return $this->return_result($this->returnData);
        }else{
            $this->returnData['coed'] = 1;
            $this->returnData['msg'] = '添加失败';
            return $this->return_result($this->returnData);
        }
    }
    //修改
    public function update_goods_type(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goodstypeModel = new GoodsType();
        $parameter['id'] = $request->input('id','');
        $parameter['name'] = $request->input('name','');
        $res = $goodstypeModel->updateGoodsType($parameter);
        if($res){
            $this->returnData['msg'] = '修改成功';
            return $this->return_result($this->returnData);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
    }

    //删除产品类型
    public function del_goods_type($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $goodstypeModel = new GoodsType();
        $res = $goodstypeModel->delGoodsType($id);
        if ($res){
            $this->returnData['msg'] = '删除成功';
            return $this->return_result($this->returnData);
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Models\Goods;
use App\Models\GoodType;
use App\Models\MemberLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }
    /**
     * 列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $sortName = $request->post("sortName",'goods_top');    //排序列名
        $sortOrder = $request->post("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->post("pageNumber");  //当前页码
        $pageSize = $request->post("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $search = $request->post("search",'');  //搜索条件
        $rows = Goods::from('goods as g')
            ->select('g.*','gt.name as goods_type_txt')
            ->leftJoin('goods_type as gt','g.goods_type','=','gt.id')
            ->where('status', '=', 0)
            ->where('is_del',0);

        if(trim($search)){
            $rows->where(function ($query) use ($search) {
                $query->where('goods_name', 'LIKE', '%' . $search . '%');
            });
        }
        $data['data']['total'] = $rows->count();
        $data['data']['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();
        //过滤等级为0的用户
        if ($this->discount == 0){
            $this->discount = 100;
        }
        //更改对应客户等级的价格
        $data['data']['rows'] = json_decode($data['data']['rows'],true);
        foreach($data['data']['rows'] as &$v){
            $goods_data = json_decode($v['goods_version'],true);
            $price_info = $goods_data[0]['subitem'];
            $v['price'] = $price_info[0]['salePrice'];
            if ($v['price_type'] != 1){
                $v['dlprice'] =  number_format($v['price']*$this->discount/100,2);
            }else{
                $v['dlprice'] = $v['price'];
            }
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    /**
     * 购买
     *
     * @return \Illuminate\Http\Response
     */
    public function buy(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $goods = Goods::find((int)$request->post("goods_id"));
        $level = MemberLevel::find((int)auth('web')->user()->level);

        if($goods->price_type == 1){
            $agentPrice = $goods->price;
        }elseif($goods->price_type == 0){
            $agentPrice = round($goods->price*$level->discount/100,2);
        }

        $goods->agentPrice = $agentPrice;
        $goods->discount = $level->discount;
        $goods->discountNanem = $level->name;
        $data['goods'] = $goods;
        return response()->json($data);
    }

    /**
     *商品详情 
	 *
     */
    public function details(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $goods = Goods::find((int)$request->get("goods_id"));
        $goods = json_decode(json_encode($goods),1);
        $discount = 0;
        if($goods['price_type'] == 1){
            $discount = 100;
        }elseif($goods['price_type'] == 0){
            //过滤0等级的问题
            if ($discount = $this->discount == 0){
                $discount = 100;
            }else{
                $discount = $this->discount;
            }
        }
        $type_name = DB::table('goods_type')->where('id',$goods['goods_type'])->select('name')->first();
        $type_name = json_decode(json_encode($type_name),1);
        $goods['goods_type_txt'] = $type_name['name'];
		$arr = json_decode($goods['goods_version'],true);
		//判断是否有商品规格
        if(count($arr)>0){
           foreach ($arr as &$v){
               if (is_array($v['subitem'])){
                   foreach ($v['subitem'] as &$s){
                       if ($goods['price_type'] != 1){
                           $s['dlsalePrice'] = number_format($s['salePrice']*$discount/100,2);
                       }else{
                           $v['dlsalePrice'] = $v['price'];
                       }
                   }
               }
           }
	    }

        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['goods_version'] = $arr;
        $data['data']['goods'] = $goods;
        return response()->json($data);
    }
    /**
     * 判断时间 
     */
    public function getUnit($obj){
        switch ($obj) {
            case "0":
                return '永久';
                break;
            case "2":
                return '月';
                break;
            case "3":
                return '日';
                break;
            default:
                return '年';
        }
    }
    
}

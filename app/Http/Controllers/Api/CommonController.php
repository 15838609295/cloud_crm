<?php

namespace App\Http\Controllers\Api;

use App\Library\UploadFile;
use App\Library\Character;
use App\Models\Admin\Company;
use App\Models\Admin\CompanyUser;
use App\Models\Admin\Configs;
use App\Models\Admin\Dispatch;
use App\Models\Admin\Godown;
use App\Models\Admin\GoodsAttr;
use App\Models\Admin\JoinDepot;
use App\Models\Admin\Members;
use App\Models\Admin\Monthly;
use App\Models\Admin\News;
use App\Models\Admin\AdminLog;
use App\Models\Admin\Opencut;
use App\Models\Admin\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Admin\Users;
use App\Models\Admin\Collection;
use Illuminate\Support\Facades\DB;

class CommonController
{
    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");

    //初始化各项配置
    public function __construct() {}

    /**
     * 小程序上传图片接口（记录图片上传成功数量）
     *
     * @param member_id
     * @param company_id
     * @param file
     */
    public function uploadImsage(Request $request) {
        $file = $request->file("file");
        if (!$file) {
            $this->result["status"] = 1;
            $this->result["msg"] = "上传失败,请重试";
            return $this->result;
        }
        if($file->getClientSize() > 15 * 1024 *1024){
            $this->result["status"] = 1;
            $this->result["msg"] = "无法上传大于15M图片";
            return $this->result;
        }

        $res = (new UploadFile([
                "upload_dir" => "./uploads/picture/",
                "type" => ["image/jpg","image/png","image/jpeg","image/bmp","image/gif"]]
        ))->upload($file);


        if ($res["status"] == 0) {
            //图片大于1M就做缩略图 小于1M不处理
            if ($file->getClientSize() > 512*512){
                $thumbnail = $this->imageThumbnail('.'.$res['data']);
                unlink('.'.$res['data']);
                if ($thumbnail == 2){
                    $this->result['status'] = 1;
                    $this->result['msg'] = '图片格式错误';
                    return $this->result;
                }elseif(!$thumbnail){
                    $this->result['status'] = 1;
                    $this->result['msg'] = '上传失败';
                    return $this->result;
                }
//            $arr = ['url' => $res["data"],'thumbnail_url' =>$thumbnail];
                $thumbnail = substr($thumbnail,1);
                $arr = ['url' => $thumbnail];
                $this->result["data"] = $arr;
            }else{
                $arr = ['url' => $res['data']];
                $this->result["data"] = $arr;
            }

            // 存储图集：用户ID，公司ID
            $data = $request->post();
            if (isset($data['member_id']) && trim($data['member_id'] != '') &&
                isset($data['company_id']) && trim($data['company_id'] != '')) {
                $cu = CompanyUser::where('user_id','=',$data['member_id'])->where('company_id','=',$data['company_id'])->first();
                $this->adminLog($data['company_id'],2,'上传图片',$data['member_id'],CompanyUser::IS_ADMIN[$cu->is_admin]);
            }
        }
        return $this->result;
    }

    // 获取套餐信息
    public function getMonthly(Request $request){
        $arr = Monthly::where('status','=',1)->get();
        if($arr){
            $arr = $arr->toArray();
        }
        $this->result['data'] = $arr;
        return response()->json($this->result);
    }

    /**
     * 创建企业
     */
    public function createCompany(Request $request) {
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['openid']) || trim($data['openid']) == ''){
            return $this->verify_parameter('openid'); //返回必传参数为空
        }
        if(!isset($data['company_name']) || trim($data['company_name']) == ''){
            return $this->verify_parameter('company_name'); //返回必传参数为空
        }
        if(!isset($data['realname']) || trim($data['realname']) == ''){
            return $this->verify_parameter('realname'); //返回必传参数为空
        }
        if(!isset($data['mobile']) || trim($data['mobile']) == ''){
            return $this->verify_parameter('mobile'); //返回必传参数为空
        }
        if(!isset($data['company_pass']) || trim($data['company_pass']) == ''){
            return $this->verify_parameter('company_pass'); //返回必传参数为空
        }

        // 判断公司名称是否重复
        $numb = Company::where('company_name', $data['company_name'])->count();
        if ($numb > 0) {
            return $this->verify_parameter('该企业名已被注册', 0);
        }

        /*$compUser = CompanyUser::where('user_id','=',$data['mem_id'])->where('is_admin','=',1)->where('status','=',1)->first();
        if($compUser){
            return $this->verify_parameter('你正在申请企业，请勿重复操作！！',0);
        }*/

        $con = Configs::first();

        $data_ins['company_name'] = $data['company_name'];
        $data_ins['company_number'] = $this->getCompanyNumber();
        $data_ins['company_pass'] = $data['company_pass'];
        $data_ins['company_status'] = 1;
        $data_ins['volid_time'] = Carbon::parse('+'.$con->test_time.' days')->toDateTimeString();
        $data_ins["created_at"] = Carbon::now()->toDateTimeString();
        $data_ins["updated_at"] = Carbon::now()->toDateTimeString();

        //写进数据库
        $cid = Company::insertGetId($data_ins);
        if($cid){

            //开启事务
            DB::beginTransaction();
            try {

                $data_upd['realname'] = $data['realname'];
                $data_upd['mobile'] = $data['mobile'];
                Members::where('openid','=',$data['openid'])->update($data_upd);

                $data_upd1['is_admin'] = 1;
                $data_upd1['company_id'] = $cid;
                $data_upd1['status'] = 1;
                $data_upd1['user_id'] = $data['mem_id'];
                $data_upd1["join_time"] = Carbon::now()->toDateTimeString();
                $data_upd1["created_at"] = Carbon::now()->toDateTimeString();
                $data_upd1["updated_at"] = Carbon::now()->toDateTimeString();

                CompanyUser::insert($data_upd1);

                DB::commit();
            } catch(\Illuminate\Database\QueryException $ex) {
                DB::rollback(); //回滚事务
                return $this->verify_parameter('创建企业失败！！',0);
            }

            return response()->json($this->result);

        }

        return $this->verify_parameter('创建企业失败',0);

    }

    //加入企业
    public function joinCompany(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['openid']) || trim($data['openid']) == ''){
            return $this->verify_parameter('openid'); //返回必传参数为空
        }
        if(!isset($data['company_id']) || trim($data['company_id']) == ''){
            return $this->verify_parameter('company_id'); //返回必传参数为空
        }
        if(!isset($data['realname']) || trim($data['realname']) == ''){
            return $this->verify_parameter('realname'); //返回必传参数为空
        }
        if(!isset($data['mobile']) || trim($data['mobile']) == ''){
            return $this->verify_parameter('mobile'); //返回必传参数为空
        }
        if(!isset($data['company_pass']) || trim($data['company_pass']) == ''){
            return $this->verify_parameter('company_pass'); //返回必传参数为空
        }

        $comp = Company::where('id','=',$data['company_id'])->where('company_pass','=',$data['company_pass'])->get();
        if(count($comp)<=0){
            return $this->verify_parameter('企业邀请码有误！！',0);
        }

        $compUser = CompanyUser::where('user_id','=',$data['mem_id'])->where('company_id','=',$data['company_id'])->first();
        if($compUser){
            return $this->verify_parameter('提交成功，请等待管理员审核',0);
        }

        //开启事务
        DB::beginTransaction();
        try {

            $data_upd['realname'] = $data['realname'];
            $data_upd['mobile'] = $data['mobile'];

            Members::where('openid','=',$data['openid'])->update($data_upd);

            $data_upd1['company_id'] = $data['company_id'];
            $data_upd1['is_admin'] = 0;
            $data_upd1['status'] = 0;
            $data_upd1['user_id'] = $data['mem_id'];
            $data_upd1["join_time"] = Carbon::now()->toDateTimeString();
            $data_upd1["created_at"] = Carbon::now()->toDateTimeString();
            $data_upd1["updated_at"] = Carbon::now()->toDateTimeString();

            CompanyUser::insert($data_upd1);

            DB::commit();
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            return $this->verify_parameter('加入企业失败！！',0);
        }

        return response()->json($this->result);
    }

    //搜索获取公司信息
    public function sosoCompany(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['soso']) || trim($data['soso']) == ''){
            return $this->verify_parameter('soso'); //返回必传参数为空
        }
        $soso = trim($data['soso']);

        $res = Company::from('company as c')
            ->select('c.id','c.company_name','c.company_number','m.realname','m.mobile','c.company_pass')
            ->leftJoin('company_user as cu','cu.company_id','=','c.id')
            ->leftJoin('members as m','m.id','=','cu.user_id')
            ->where('cu.is_admin','=',1)
            ->where(function ($query) use ($soso) {
                $query->where('c.company_name', '=', $soso)
                    ->orwhere('c.company_number', '=', $soso);
            })
            ->where('company_status','=',1)
            ->first();

        if(!$res){
            return $this->verify_parameter('查不到数据',0);
        }

        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    // 获取公司接口
    public function getCompany(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['company_id']) || trim($data['company_id']) == ''){
            return $this->verify_parameter('company_id'); //返回必传参数为空
        }

        $res = Company::from('company as c')
            ->select('c.id','c.company_name','c.company_number','m.realname','m.mobile','c.company_pass')
            ->leftJoin('company_user as cu','cu.company_id','=','c.id')
            ->leftJoin('members as m','m.id','=','cu.user_id')
            ->where('c.id','=',$data['company_id'])
            ->where('cu.is_admin','=',1)
            ->first();

        if(!$res){
            return $this->verify_parameter('查不到数据',0);
        }

        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //获取公司资讯
    public function getNews(Request $request){
        $data = $request->post();

        $res = News::from('news as n')
            ->select('n.title','n.type','n.content','n.created_at')
            ->get();

        if(!$res){
            return $this->verify_parameter('查不到数据',0);
        }

        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    // 更新登陆时间
    public function updateLoginTime(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['company_id']) || trim($data['company_id']) == ''){
            return $this->verify_parameter('company_id'); //返回必传参数为空
        }
        if(!isset($data['member_id']) || trim($data['member_id']) == ''){
            return $this->verify_parameter('member_id'); //返回必传参数为空
        }

        // 更新登陆时间
        $bool = CompanyUser::where('user_id','=',$data['member_id'])->where('company_id','=',$data['company_id'])->update(['login_time'=>Carbon::now()->toDateTimeString()]);
        if(!$bool){
            return $this->verify_parameter('更新操作失败', 0);
        }

        $cu = CompanyUser::where('user_id','=',$data['member_id'])->where('company_id','=',$data['company_id'])->first();
        //记录登陆操作
        $this->adminLog($data['company_id'],0,'登录',$data['member_id'],CompanyUser::IS_ADMIN[$cu->is_admin]);
        return response()->json($this->result);
    }

    // 模糊获取公司产品信息
    public function getCompanyGoDown(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['soso']) || trim($data['soso']) == ''){
            return $this->verify_parameter('soso'); //返回必传参数为空
        }

        //$comp1 = Company::where('company_name','like','%'.$data['soso'].'%')->orwhere('company_number','like','%'.$data['soso'].'%')->get();
        //$comp2 = Company::from('company as c')->select('c.*')
        //->join('goods_attr as ga','c.id','=','ga.company_id')
        //->where('ga.goods_attr_name','like','%'.$data['soso'].'%')
        //->get();
        $comp2 = Company::from('company as c')->select('c.*')
            ->join('goods_attr as ga','c.id','=','ga.company_id')
            ->where('ga.goods_attr_name','like','%'.$data['soso'].'%')
            ->orwhere('c.company_name','like','%'.$data['soso'].'%')
            ->orwhere('c.company_number','like','%'.$data['soso'].'%')
            ->get();

        $ids = array();
        //if($comp1){
        //$comp1 = $comp1->toArray();
        //foreach ($comp1 as $v){
        //if(!in_array($v['id'], $ids)){
        //$ids[] = $v['id'];
        //}
        //}
        //}

        if($comp2){
            $comp2 = $comp2->toArray();
            foreach ($comp2 as $v){
                if(!in_array($v['id'], $ids)){
                    $ids[] = $v['id'];
                }
            }
        }
        $res = GoodsAttr::from('goods_attr as ga')
            ->select('c.*','ga.goods_attr_name','m.mobile','g.updated_at as update_time')
            ->leftJoin('company as c','ga.company_id','=','c.id')
            ->leftJoin('godown as g','ga.id','=','g.goods_attr_id')
            ->leftJoin('company_user as cu','c.id','=','cu.company_id')
            ->leftJoin('members as m','cu.user_id','=','m.id')
            ->where('cu.is_admin','=',1)
            ->whereIn('c.id', $ids);

        //$res = Company::from('company as c')
        //->select('c.*','ga.goods_attr_name','m.mobile','g.updated_at as update_time')
        //->join('goods_attr as ga','c.id','=','ga.company_id')
        //->join('company_user as cu','cu.company_id','=','c.id')
        //->join('godown as g','ga.id','=','g.goods_attr_id')
        //->join('members as m','m.id','=','cu.user_id')
        //->where('cu.is_admin','=',1)
        //->whereIn('c.id', $ids)
        //->groupBy('ga.goods_attr_name');

        $comp =  $res->get();

        if($comp){
            $comp = $comp->toArray();
            $new = array();

            foreach ($comp as $k => &$v){
                if(isset($new[$v['id']])){
                    $new[$v['id']]['names'][] = $v['goods_attr_name'];
                }else{
                    $arr = array();
                    $arr['id'] = $v['id'];
                    $arr['company_name'] = $v['company_name'];
                    $arr['company_number'] = $v['company_number'];
                    $arr['company_pass'] = $v['company_pass'];
                    $arr['company_status'] = $v['company_status'];
                    $arr['volid_time'] = $v['volid_time'];
                    $arr['mobile'] = $v['mobile'];
                    $arr['created_at'] = $v['created_at'];
                    $arr['updated_at'] = $v['updated_at'];
                    if(!isset($new[$v['id']]['update_time'])){
                        $arr['update_time'] = $v['update_time'];
                    }else if(isset($new[$v['id']]['update_time']) &&  $new[$v['id']]['update_time'] < $v['update_time']){
                        $arr['update_time'] = $v['update_time'];
                    }
                    $arr['names'][] = $v['goods_attr_name'];
                    if ($v['label'] != ''){
                        $arr['label'] = json_decode($v['label'],1);
                    }else{
                        $arr['label'] = [];
                    }
                    $arr['sort'] = $v['sort'];
                    if ($v['logo']){
                        $arr['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/'.$v['logo'];
                    }else{
                        $arr['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/default/info.png';
                    }
                    if ($v['cover']){
                        $arr['cover'] = 'https://'.$_SERVER['SERVER_NAME'].'/'.$v['cover'];
                    }else{
                        $arr['cover'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/default/2.jpg';
                    }
                    $new[$v['id']] = $arr;
                }
                $v['label'] = json_encode($v['label']);
            }

            $comp = array_values($new);
            foreach($comp as $k => &$v){
                $comp[$k]['names'] =array_unique($v['names']);
                //$comp[$k]['names'] = array_values(array_filter($v['names']));
                if ($v['sort'] == 0){
                    $v['sort'] = 4;
                }
            }
            if(isset($data['xl_sort']) && $data['xl_sort'] != ''){
                foreach ($comp as &$v){
                    $v['c_number'] = count($v['names']);
                }
                //根据字段names对数组$comp进行降序排列
                $last_names = array_column($comp,'c_number');
                if (1 == $data['xl_sort']){
                    array_multisort($last_names,SORT_ASC,$comp);
                }else{
                    array_multisort($last_names,SORT_DESC,$comp);
                }
            }
            if(isset($data['up_sort']) && $data['up_sort'] != ''){
                $last_names = array_column($comp,'update_time');
                if (1 == $data['up_sort']){
                    array_multisort($last_names,SORT_ASC,$comp);
                }else{
                    array_multisort($last_names,SORT_DESC,$comp);
                }
            }
            if (isset($data['up_sort']) && $data['up_sort'] == 0 && isset($data['xl_sort']) && $data['xl_sort'] == 0 ){
                //根据后台设置的排序字段来排序
                $last_names = array_column($comp,'sort');
                array_multisort($last_names,SORT_ASC,$comp);
            }
        }


        $total = count($comp);
        $list['total'] = $total;
        $list['list'] = $comp;

        $this->result['data'] = $list;

        return response()->json($this->result);
    }

    // 查询单个公司的产品列表
    public function getCompanyGoDownList(Request $request){
        $data = $request->post();
        if (!$data['mem_id']){
            $this->result['status'] = 202;
            $this->result['msg'] = '无效用户id';
            return response()->json($this->result);
        }

        //判断传值是否正确
        if(!isset($data['company_id']) || trim($data['company_id']) == ''){
            return $this->verify_parameter('company_id'); //返回必传参数为空
        }

        //判断是否有可选参数
        if(!isset($data['page']) || trim($data['page']) == ''){
            $data['page'] = 1;
        }
        $start = ((int)$data['page']-1)*10;  //截取部分数据


        $godownIds = array();
        if(isset($data['soso']) &&  trim($data['soso']) != ''){

            $arr1 = Dispatch::where('remarks', 'like', '%'.$data['soso'].'%')->get(['godown_id']);
            $arr2 = JoinDepot::from('joindepot as j')
                ->select('g.id as godown_id')
                ->join('godown as g', 'g.godown_no', '=', 'j.godown_no')
                ->where('j.remarks', 'like', '%'.$data['soso'].'%')
                ->get();
            $arr3 = Opencut::where('remarks', 'like', '%'.$data['soso'].'%')->get(['godown_id']);
            $arr4 = Sale::where('remarks', 'like', '%'.$data['soso'].'%')->get(['godown_id']);

            if($arr1){
                foreach ($arr1 as $v){
                    if(!in_array($v->godown_id, $godownIds)){
                        $godownIds[] = $v->godown_id;
                    }
                }
            }
            if($arr2){
                foreach ($arr2 as $v){
                    if(!in_array($v->godown_id, $godownIds)){
                        $godownIds[] = $v->godown_id;
                    }
                }
            }
            if($arr3){
                foreach ($arr3 as $v){
                    if(!in_array($v->godown_id, $godownIds)){
                        $godownIds[] = $v->godown_id;
                    }
                }
            }
            if($arr4){
                foreach ($arr4 as $v){
                    if(!in_array($v->godown_id, $godownIds)){
                        $godownIds[] = $v->godown_id;
                    }
                }
            }

        }


        $godown= Godown::from('godown as g')
            ->select('g.id','g.type','ga.goods_attr_name','d.depot_name','g.godown_no','g.godown_weight','g.godown_length','g.godown_width','g.godown_height','g.godown_pic','g.godown_number','g.no_start','g.no_end')
            ->leftJoin('goods_attr as ga','g.goods_attr_id','=','ga.id')
            ->leftJoin('depots as d','d.id','=','g.depot_id')
            ->where('g.godown_pic','!=','')
            ->where(function ($query) {
                $query->where('type',0)
                    ->where('godown_weight','>',1)
                    ->orwhere(function($query){
                        $query->where('type',1)
                            ->where('godown_number','>',1)
                            ->orwhere('godown_weight','>=',3);
                    });
            })
            ->where('ga.status',1)
            ->where('ga.company_id','=',$data['company_id']);

        if(isset($data['goods_attr_id']) && trim($data['goods_attr_id']) != ''){
            $godown->where('g.goods_attr_id','=',$data['goods_attr_id']);
        }

        if(isset($data['type']) && trim($data['type']) != ''){
            $godown->where('g.type','=',$data['type']);
        }

        if(isset($data['depot_id']) && trim($data['depot_id']) != ''){
            $godown->where('g.depot_id','=',$data['depot_id']);
        }

        if(isset($data['soso']) && trim($data['soso']) != ''){
            $godown->where(function ($query) use ($data, $godownIds){
                $query->whereIn('g.id', $godownIds)
                    ->orwhere('g.godown_no','like','%'.$data['soso'].'%');
            });
        }

        //$res = $godown->skip($start)->take(10)->get();


        $total = $godown->count();
        //$res = $godown->orderBy('g.id','desc')->skip($start)->take(10)->get();
        $res = $godown->orderBy('g.id','desc')->skip($start)->take(10)->get();

        if(!$res){
            return $this->verify_parameter('查不到数据',0);
        }
        $res = json_decode(json_encode($res),true);
        //查询用户收藏的所有产品
        $collects = Collection::where('u_id',$data['mem_id'])->select('g_id')->get();
        $collects = json_decode(json_encode($collects),true);
        $ids = [];
        foreach ($collects as $v){
            $ids[] = $v['g_id'];
        }
        foreach ($res as $v){
            if (in_array($v['id'],$ids)){  //已收藏
                $v['collection_status'] = 1;
            }else{                         //未收藏
                $v['collection_status'] = 0;
            }
        }

        $list['total'] = $total;
        $list['list'] = $res;;
        $this->result['data'] = $list;

        return response()->json($this->result);
    }

    //品种查询
    public function getGoodsAttr(Request $request){
        $data = $request->post();

        //判断传值是否正确
        if(!isset($data['company_id']) || trim($data['company_id']) == ''){
            return $this->verify_parameter('company_id'); //返回必传参数为空
        }

        $res = GoodsAttr::select('id','goods_attr_name')->where('company_id','=',$data['company_id'])->get();
        if(!$res){
            return $this->verify_parameter('查不到数据',0);
        }

        $this->result['data'] = $res;
        return response()->json($this->result);

    }

    //公司列表
    public function companyList(Request $request){
        $data = $request->post();
        $start = ($data['page'] - 1)*15;
        $total = Company::count();
        $res = Company::from('company as c')
            ->select('c.company_name','c.logo','c.cover','c.label','c.sort','c.id','m.mobile','c.company_number')
            ->join('company_user as cu','cu.company_id','=','c.id')
            ->join('members as m','m.id','=','cu.user_id')
            ->where('cu.is_admin',1);
        if ($data['soso']){
            $soso = $data['soso'];
            $res->where(function ($query) use ($soso) {
                $query->where('c.company_name', 'LIKE', '%' . $soso . '%')
                    ->orwhere('c.company_number', 'LIKE', '%' . $soso . '%');
            });
        }
        $result = $res->get();
        if (!$res){
            return $this->verify_parameter('无公司信息',0);
        }
        $result = json_decode(json_encode($result),true);
        $new_list = [];
        //查询公司产品种类信息
        $goods_info = DB::table('goods_attr')->select('id','company_id')->get();
        $goods_info = json_decode(json_encode($goods_info),true);
        //查询公司种类规格信息
        $goods_godown = DB::table('godown')
            ->select('id','goods_attr_id')
            ->where('godown_pic','!=','')
            ->where(function ($query) {
                $query->where('type', 0)
                    ->where('godown_weight', '>', 1)
                    ->orwhere(function ($query) {
                        $query->where('type', 1)
                            ->where('godown_number', '>', 1)
                            ->orwhere('godown_weight', '>=', 3);
                    });
            })->get();
        $goods_godown = json_decode(json_encode($goods_godown),true);
        foreach ($result as $k=>&$v){
            //预置排序字段
            $v['update_time'] = '';
            $v['number'] = 0;
            if ($v['logo']){
                $v['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/'.$v['logo'];
            }else{
                $v['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/default/info.png';
            }
            if ($v['cover']){
                $v['cover'] = 'https://'.$_SERVER['SERVER_NAME'].'/'.$v['cover'];
            }else{
                $v['cover'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/default/2.jpg';
            }
            if ($v['label']){
                $v['label'] = json_decode($v['label'],true);
            }else{
                $v['label'] = [];
            }
            if ($v['sort'] != 0 && $v['sort'] < 4){
                $new_list[] = $v;
                unset($result[$k]);
            }
            foreach ($goods_info as $g_v){
                if ($g_v['company_id'] == $v['id']){ //查询公司下产品信息
                    foreach ($goods_godown as $g_g_v){
                        if ($g_g_v['goods_attr_id'] == $g_v['id']){
                            $v['number'] = $v['number'] + 1;
                        }
                    }
                }
            }
//            //查询公司产品信息  新查询待优化
//            $goods_info = DB::table('goods_attr')->where('company_id',$v['id'])->select('id')->get();
//            $goods_info = json_decode(json_encode($goods_info),true);
//            foreach ($goods_info as $g_v){
//                $number = DB::table('godown')->where('goods_attr_id',$g_v['id'])
//                    ->where('godown_pic','!=','')
//                    ->where(function ($query) {
//                        $query->where('type',0)
//                            ->where('godown_weight','>',1)
//                            ->orwhere(function($query){
//                                $query->where('type',1)
//                                    ->where('godown_number','>',1)
//                                    ->orwhere('godown_weight','>=',3);
//                            });
//                    })->count();
//                $v['number'] = $v['number'] + $number;
//            }

            //旧查询
//            foreach ($goods_info as $g_v){
//                $update_time = DB::table('godown')->where('goods_attr_id',$g_v['id'])->max('updated_at');
//                if ($v['update_time'] == ''){
//                    $v['update_time'] = strtotime($update_time);
//                }elseif ($v['update_time'] < $update_time){
//                    $v['update_time'] = $update_time;
//                }
//            }
        }
//        //按产品更新时间排序
//        $last_names = array_column($result,'update_time');
//        array_multisort($last_names,SORT_DESC,$result);
        //按产品数量排序（新）
        $last_names = array_column($result,'number');
        array_multisort($last_names,SORT_DESC,$result);

        $last_names = array_column($new_list,'sort');
        array_multisort($last_names,SORT_ASC,$new_list);

        $list = array_merge($new_list,$result);

        $pagedata=array_slice($list,$start,15);

        $list['total'] = $total;
        $list['list'] = $pagedata;
        $this->result['data'] = $list;
        return response()->json($this->result);
    }

    //产品列表
    public function productList(Request $request){
        $data = $request->post();
        $soso = $data['soso'];
        $authentication = $data['authentication'];
        // $page = $data['page'];
        // $start = ($page - 1)*2;
        $res = Godown::from('godown as g')
            ->select('ga.goods_attr_name','g.godown_no')
//            ->select('g.id','ga.goods_attr_name','g.godown_no','c.company_name','c.company_number')
            ->leftJoin('goods_attr as ga','g.goods_attr_id','=','ga.id');
        //  ->leftJoin('company as c','ga.company_id','=','c.id');
        if ($soso){
            $res->where(function ($query) use ($soso) {
                $query->where('ga.goods_attr_name', 'LIKE', '%' . $soso . '%');
                //  ->orwhere('g.godown_no', 'LIKE', '%' . $soso . '%');
            });
        }
        $res->where('ga.status',1);
        if ($authentication == 1){
            $res->where('ga.authentication',1);
        }
        $total = $res->count();
        $result = $res->groupBy('ga.goods_attr_name')->get();
        // $result = $res->get();
        //$result = $res->skip($start)->take(10)->get();
        $result = json_decode(json_encode($result),true);
        $arr = [];
        foreach ($result as $v){
            $s0 = mb_substr($v['goods_attr_name'],0,1,'utf-8');//获取名字的姓
            $p_one = $this->pinyin($s0);
            $p_one = strtoupper($p_one);
            $res = $this->array_multi_search($p_one,$arr);
            $id_number= count($arr) +1;
            $list = [];
            if (!$res){
                $list['id'] = $id_number;
                $list['region'] = $p_one;
                $list['items'][] = $v;
                $arr[] = $list;
            }else{
                foreach ($arr as $k=>$a){
                    if ($a['region'] === $p_one){
                        $arr[$k]['items'][] = $v;
                    }
                }
            }
        }
        //根据字段last_name对数组$data进行降序排列
        $last_names = array_column($arr,'region');
        array_multisort($last_names,SORT_ASC,$arr);
        foreach ($arr as $k=>&$v){
            $v['id'] = $k;
        }
        //   $newarr = array_slice($arr, $start, 2);
        $c_list['total'] = $total;
        //   $c_list['list'] = $newarr;
        $c_list['list'] = $arr;
        $this->result['data'] = $c_list;
        return response()->json($this->result);
    }

    //产品详情
    public function goodsIdInfo(Request $request){
        $id = $request->input('goods_id','');
        $godown= Godown::from('godown as g')
            ->select('g.id','g.type','ga.goods_attr_name','d.depot_name','g.godown_no','g.godown_weight','g.godown_length','g.godown_width','g.godown_height','g.godown_pic','g.godown_number','g.no_start','g.no_end')
            ->leftJoin('goods_attr as ga','g.goods_attr_id','=','ga.id')
            ->leftJoin('depots as d','d.id','=','g.depot_id')
            ->where('g.id','=',$id)
            ->first();
        $godown = json_decode(json_encode($godown),true);
        $this->result['data'] = $godown;
        return response()->json($this->result);
    }

    //产品在售公司列表
    public function sellCompanyList(Request $request){
        $go_name = $request->input('goods_name','');
        $res = GoodsAttr::from('goods_attr as ga')
            ->select('c.company_name','c.company_number','c.id','m.mobile','c.label','c.logo')
            ->leftJoin('company as c','ga.company_id','=','c.id')
            ->leftJoin('company_user as cu','c.id','=','cu.company_id')
            ->leftJoin('members as m','cu.user_id','=','m.id')
            ->where('ga.goods_attr_name', $go_name)
            ->where('cu.is_admin',1)
            ->get();
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '未找到售卖公司';
        }else{
            $res = json_decode(json_encode($res),true);
            foreach ($res as &$v){
                if ($v['logo'] == null){
                    $v['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/uploads/default/info.png';
                }else{
                    $v['logo'] = 'https://'.$_SERVER['SERVER_NAME'].'/'.$v['logo'];
                }
                if ($v['label'] == null){
                    $v['label'] = [];
                }else{
                    $v['label'] = json_decode($v['label'],1);
                }
            }
            $this->result['data'] = $res;
        }
        return response()->json($this->result);
    }


    //收藏产品
    public function collection(Request $request){
        $data['u_id'] = $request->input('mem_id','');
        $data['g_id'] = $request->input('goods_id','');
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = Collection::insertGetId($data);
        if (!$res){
            return $this->verify_parameter('收藏失败',1);
        }
        return response()->json($this->result);
    }

    //取消收藏
    public function cancelCollction(Request $request){
        $gid = $request->input('goods_id','');
        $uid = $request->input('mem_id','');
        $res = Collection::where('u_id',$uid)->where('g_id',$gid)->delete();
        if (!$res){
            return $this->verify_parameter('取消失败',1);
        }
        return response()->json($this->result);
    }

    //我的收藏
    public function myCollection(Request $request){
        $data = $request->input();
        $start = ($data['page'] - 1)*10;
        $godown = Collection::from('collection as c')
            ->select('g.id','g.godown_no','g.godown_length','g.godown_width','g.godown_weight','g.type','g.created_at','ga.goods_attr_name','cp.company_name','cp.id as company_id','g.godown_pic','cp.company_number','m.mobile')
            ->leftJoin('godown as g','c.g_id','=','g.id')
            ->leftJoin('users as u','c.u_id','=','u.id')
            ->leftJoin('goods_attr as ga','g.goods_attr_id','=','ga.id')
            ->leftJoin('company as cp','ga.company_id','=','cp.id')
            ->leftJoin('company_user as cu','cu.company_id','=','cp.id')
            ->leftJoin('members as m','m.id','=','cu.user_id')
            ->where('cu.is_admin',1)
            ->where('c.u_id','=',$data['mem_id'])
            ->where('ga.status',1)
            ->skip($start)->take(10)
            ->get();
        if (!$godown){
            $this->result['data'] = [];
        }else{
            $godown = json_decode(json_encode($godown),true);
            foreach ($godown as &$v){
                $v['godown_pic'] = explode(',',$v['godown_pic']);
            }
            $this->result['data'] = $godown;
        }
        return response()->json($this->result);
    }

    //获取openid
    public function getOpenid(Request $request){
        $code = $request->input('code','');

        $config = Configs::first();
        $url = "https://api.weixin.qq.com/sns/jscode2session?";
        $url .= "appid=".$config->mem_wechat_appid;
        $url .= "&secret=".$config->mem_wechat_secret;
        $url .= "&js_code=".$code;
        $url .= "&grant_type=authorization_code";
        $res = file_get_contents($url);                                                                                 //请求微信小程序获取用户接口
        $tmp_res = json_decode($res,true);
        $res = Users::where('openid',$tmp_res['openid'])->first();
        if (!$res){  //不存在
            $data['name'] = $request->input('name','');
            $data['avatar'] = $request->input('avatar','');
            $data['openid'] = $tmp_res['openid'];
            $data["created_at"] = Carbon::now()->toDateTimeString();
            $id = Users::insertGetId($data);
            $data['id'] = $id;
            $data['name'] = $data['name'];
            $data['avatar'] = $data['avatar'];
            $data['openid'] = $tmp_res['openid'];
        }else{
            $data['id'] = $res->id;
            $data['name'] = $res->name;
            $data['avatar'] = $res->avatar;
            $data['openid'] = $tmp_res['openid'];
        }

        $this->result['data'] = $data;
        return response()->json($this->result);
    }

    //根据openid获取用户信息
    public function getUserInfo(Request $request){
        $where['openid'] = $request->input('openid','');
        $res = Users::where($where)->first();
        $res = json_decode(json_encode($res),true);
        $this->result['data'] = $res;
        return response()->json($this->result);
    }


    //返回失败的原因
    private function verify_parameter($str,$type=1){
        $this->result['status'] = 1;
        if($type==1){
            $this->result['msg'] = "必传参数".$str."为空";
        }else{
            $this->result['msg'] = $str;
        }
        return response()->json($this->result);
    }

    //生成一个随机公司ID
    private function getCompanyNumber(){
        $str = '0123456789';
        $str = str_shuffle($str);
        $company_number = substr($str,0,8);
        $count = Company::where("company_number",'=',$company_number)->count();
        if($count>0){
            $this->getCompanyNumber();
        }else{
            return $company_number;
        }
    }

    //操作记录方法
    private function adminLog($company_id,$type,$content,$user_id,$identity) {
        $adminArr = array();
        $adminArr['company_id'] = $company_id;
        $adminArr['type'] = $type;
        $adminArr['content'] = $content;
        $adminArr['user_id'] = $user_id;
        $adminArr['identity'] = $identity;
        $adminArr['created_at'] = Carbon::now()->toDateTimeString();
        $adminArr['updated_at'] = Carbon::now()->toDateTimeString();
        AdminLog::insert($adminArr);
    }


    /**
     * 中文转拼音 (utf8版,gbk转utf8也可用)
     * @param string $str         utf8字符串
     * @param string $ret_format  返回格式 [all:全拼音|first:首字母|one:仅第一字符首字母]
     * @param string $placeholder 无法识别的字符占位符
     * @param string $allow_chars 允许的非中文字符
     * @return string             拼音字符串
     */
    function pinyin($str, $ret_format = 'first', $placeholder = '#', $allow_chars = '/[a-zA-Z\s ]/' ) {
        static $pinyins = null;

        if (null === $pinyins) {
            $data = 'yi:一,乁,乂,义,乙,亄,亦,亿,仡,以,仪,伇,伊,伿,佁,佚,佾,侇,依,俋,倚,偯,儀,億,兿,冝,刈,劓,劮,勚,勩,匇,匜,医,吚,呓,呭,呹,咦,咿,唈,噫,囈,圛,圯,坄,垼,埶,埸,墿,壱,壹,夁,夷,奕,妷,姨,媐,嫕,嫛,嬄,嬑,嬟,宐,宜,宧,寱,寲,屹,峄,峓,崺,嶧,嶬,嶷,已,巸,帟,帠,幆,庡,廙,异,弈,弋,弌,弬,彛,彜,彝,彞,役,忆,忔,怈,怡,怿,恞,悒,悘,悥,意,憶,懌,懿,扅,扆,抑,挹,揖,撎,攺,敡,敼,斁,旑,旖,易,晹,暆,曀,曎,曵,杙,杝,枍,枻,柂,栘,栧,栺,桋,棭,椅,椬,椸,榏,槸,檍,檥,檹,欭,欹,歝,殔,殪,殹,毅,毉,沂,沶,泆,洢,浂,浥,浳,湙,溢,漪,潩,澺,瀷,炈,焲,熠,熤,熪,熼,燚,燡,燱,狋,猗,獈,玴,瑿,瓵,畩,異,疑,疫,痍,痬,瘗,瘞,瘱,癔,益,眙,瞖,矣,礒,祎,禕,秇,移,稦,穓,竩,笖,簃,籎,縊,繄,繶,繹,绎,缢,羛,羠,義,羿,翊,翌,翳,翼,耴,肄,肊,胰,膉,臆,舣,艗,艤,艺,芅,苅,苡,苢,荑,萓,萟,蓺,薏,藙,藝,蘙,虉,蚁,蛜,蛡,蛦,蜴,螔,螘,螠,蟻,衣,衤,衪,衵,袘,袣,裔,裛,褹,襼,觺,訲,訳,詍,詒,詣,誃,誼,謻,譩,譯,議,讉,讛,议,译,诒,诣,谊,豙,豛,豷,貖,貤,貽,贀,贻,跇,跠,軼,輢,轙,轶,辷,迆,迤,迻,逘,逸,遗,遺,邑,郼,酏,醫,醳,醷,釔,釴,鈘,鈠,鉯,銥,鎰,鏔,鐿,钇,铱,镒,镱,阣,陭,隿,霬,靾,頉,頤,頥,顊,顗,颐,飴,饐,饴,駅,驛,驿,骮,鮧,鮨,鯣,鳦,鶂,鶃,鶍,鷁,鷊,鷖,鷧,鷾,鸃,鹝,鹢,鹥,黓,黟,黳,齮,齸,㐹,㑊,㑜,㑥,㓷,㔴,㕥,㖂,㘁,㘈,㘊,㘦,㙠,㙯,㚤,㚦,㛕,㜋,㜒,㝖,㞔,㠯,㡫,㡼,㢞,㣂,㣻,㥴,㦉,㦤,㦾,㩘,㫊,㰘,㰝,㰻,㱅,㱲,㲼,㳑,㴁,㴒,㵝,㵩,㶠,㹓,㹭,㺿,㽈,䄁,䄬,䄿,䆿,䇩,䇵,䉨,䋚,䋵,䌻,䎈,䐅,䐖,䓃,䓈,䓹,䔬,䕍,䖁,䖊,䗑,䗟,䗷,䘝,䘸,䝘,䝝,䝯,䞅,䢃,䣧,䦴,䧧,䩟,䬁,䬥,䬮,䭂,䭇,䭞,䭿,䮊,䯆,䰙,䱌,䱒,䲑,䴊,䴬|ding:丁,仃,叮,啶,奵,定,嵿,帄,忊,椗,玎,甼,疔,盯,矴,碇,碠,磸,耵,聢,聣,腚,萣,薡,訂,订,酊,釘,錠,鐤,钉,锭,靪,頂,顁,顶,飣,饤,鼎,鼑,㝎,㫀,㴿,㼗|kao:丂,尻,拷,攷,栲,洘,烤,犒,考,銬,铐,靠,髛,鮳,鯌,鲓,䐧,䯪|qi:七,乞,亓,亝,企,倛,僛,其,凄,剘,启,呇,呮,咠,唘,唭,啓,啔,啟,嘁,噐,器,圻,埼,夡,奇,契,妻,娸,婍,屺,岂,岐,岓,崎,嵜,帺,弃,忯,恓,悽,愒,愭,慼,慽,憇,憩,懠,戚,捿,掑,摖,斉,斊,旂,旗,晵,暣,朞,期,杞,柒,栔,栖,桤,桼,棄,棊,棋,棨,棲,榿,槭,檱,櫀,欫,欺,歧,气,気,氣,汔,汽,沏,泣,淇,淒,渏,湆,湇,漆,濝,炁,猉,玂,玘,琦,琪,璂,甈,畦,疧,盀,盵,矵,砌,碁,碕,碛,碶,磎,磜,磧,磩,礘,祁,祇,祈,祺,禥,竒,簯,簱,籏,粸,紪,綥,綦,綨,綮,綺,緀,緕,纃,绮,缼,罊,耆,肵,脐,臍,艩,芑,芞,芪,荠,萁,萋,葺,蕲,薺,藄,蘄,蚑,蚔,蚚,蛴,蜝,蜞,螧,蟿,蠐,裿,褀,褄,訖,諆,諬,諿,讫,豈,起,跂,踑,踦,蹊,軝,迄,迉,邔,郪,釮,錡,鏚,锜,闙,霋,頎,颀,騎,騏,騹,骐,骑,鬐,鬾,鬿,魌,魕,鯕,鰭,鲯,鳍,鵸,鶀,鶈,麒,麡,齊,齐,㒅,㓞,㜎,㞓,㞚,㟓,㟚,㟢,㣬,㥓,㩩,㩽,㫓,㮑,㯦,㼤,㾨,䀈,䀙,䁈,䁉,䄎,䄢,䄫,䅤,䅲,䉝,䉻,䋯,䌌,䎢,䏅,䏌,䏠,䏿,䐡,䑴,䒗,䒻,䓅,䔇,䙄,䚉,䚍,䛴,䞚,䟄,䟚,䡋,䡔,䢀,䧘,䧵,䩓,䫔,䬣,䭫,䭬,䭶,䭼,䰇,䰴,䱈,䲬,䳢,䶒,䶞|shang:丄,上,仩,伤,傷,商,垧,墒,尙,尚,恦,愓,慯,扄,晌,殇,殤,滳,漡,熵,緔,绱,蔏,螪,裳,觞,觴,謪,賞,赏,鑜,鬺,䬕|xia:丅,下,侠,俠,傄,匣,厦,吓,呷,嚇,圷,夏,夓,峡,峽,廈,懗,搳,敮,暇,柙,梺,溊,炠,烚,煆,狎,狭,狹,珨,瑕,疜,疨,睱,瞎,硖,硤,碬,磍,祫,笚,筪,縀,縖,罅,翈,舝,舺,蕸,虾,蝦,諕,谺,赮,轄,辖,遐,鍜,鎋,鏬,閕,閜,陜,陿,霞,颬,騢,魻,鰕,鶷,黠,㗇,㗿,㘡,㙤,㰺,㽠,䖎,䖖,䘥,䛅,䦖,䪗,䫗|mu:丆,亩,仫,凩,募,坶,墓,墲,姆,娒,峔,幕,幙,慔,慕,拇,旀,暮,木,椧,楘,樢,母,毣,毪,氁,沐,炑,牡,牧,牳,狇,獏,畆,畒,畝,畞,畮,目,睦,砪,穆,胟,艒,苜,莯,萺,蚞,踇,鉧,鉬,钼,雮,霂,鞪,㒇,㜈,㣎,㧅,㾇,䀲,䊾,䑵,䥈,䧔,䱯|wan:万,丸,乛,倇,刓,剜,卍,卐,唍,埦,塆,壪,妧,婉,婠,完,宛,岏,帵,弯,彎,忨,惋,抏,挽,捖,捥,晚,晩,晼,杤,梚,椀,汍,涴,湾,潫,灣,烷,玩,琓,琬,畹,皖,盌,睕,碗,笂,紈,綩,綰,纨,绾,翫,脘,腕,芄,莞,菀,萬,薍,蜿,豌,貦,贃,贎,踠,輓,邜,鋄,鋔,錽,鍐,鎫,頑,顽,㜶,㝴,㸘,㽜,㿸,䂺,䅋,䖤,䗕,䘼,䛷,䝹,䥑,䩊,䯈,䳃|zhang:丈,仉,仗,傽,墇,嫜,嶂,帐,帳,幛,幥,张,弡,張,彰,慞,扙,掌,暲,杖,樟,涨,涱,漲,漳,獐,璋,痮,瘬,瘴,瞕,礃,章,粀,粻,胀,脹,蔁,蟑,賬,账,遧,鄣,鏱,鐣,障,鞝,餦,騿,鱆,麞,㕩,㙣,㽴,䍤|san:三,伞,俕,傘,厁,叁,壭,弎,散,橵,毵,毶,毿,犙,糁,糂,糝,糣,糤,繖,鏒,閐,饊,馓,鬖,㤾,㧲,㪔,㪚,䀐,䉈,䊉,䫅,䫩|:|ji:丌,丮,乩,亟,亼,伋,伎,佶,偈,偮,僟,兾,冀,几,击,刉,刏,剂,剞,剤,劑,勣,卙,即,卽,及,叝,叽,吉,咭,哜,唧,喞,嗘,嘰,嚌,圾,坖,垍,基,塉,墍,墼,妀,妓,姞,姬,嫉,季,寂,寄,屐,岌,峜,嵆,嵇,嵴,嶯,己,幾,庴,廭,彐,彑,彶,徛,忌,忣,急,悸,惎,愱,憿,懻,戟,戢,技,挤,掎,揤,撃,撠,擊,擠,攲,敧,旡,既,旣,暨,暩,曁,机,极,枅,梞,棘,楖,楫,極,槉,槣,樭,機,橶,檕,檝,檵,櫅,殛,毄,汲,泲,洎,济,済,湒,漃,漈,潗,激,濈,濟,瀱,焏,犄,犱,狤,玑,璣,璾,畸,畿,疾,痵,瘠,癠,癪,皍,矶,磯,祭,禝,禨,积,稘,稩,稷,稽,穄,穊,積,穖,穧,笄,笈,筓,箕,箿,簊,籍,糭,紀,紒,級,継,緝,縘,績,繋,繫,繼,级,纪,继,绩,缉,罽,羁,羇,羈,耤,耭,肌,脊,脨,膌,臮,艥,芨,芰,芶,茍,萕,葪,蒺,蓟,蔇,蕀,蕺,薊,蘎,蘮,蘻,虀,虮,蝍,螏,蟣,裚,襀,襋,覉,覊,覬,觊,觙,觭,計,記,誋,諅,譏,譤,计,讥,记,谻,賫,賷,赍,趌,跡,跻,跽,踖,蹐,蹟,躋,躤,躸,輯,轚,辑,迹,郆,鄿,銈,銡,錤,鍓,鏶,鐖,鑇,鑙,际,際,隮,集,雞,雦,雧,霁,霵,霽,鞊,鞿,韲,飢,饑,饥,驥,骥,髻,魢,鮆,鯚,鯽,鰶,鰿,鱀,鱭,鱾,鲚,鲫,鳮,鵋,鶏,鶺,鷄,鷑,鸄,鸡,鹡,麂,齌,齎,齏,齑,㑧,㒫,㔕,㖢,㗊,㗱,㘍,㙨,㙫,㚡,㞃,㞆,㞛,㞦,㠍,㠎,㠖,㠱,㡇,㡭,㡮,㡶,㤂,㥍,㥛,㦸,㧀,㨈,㪠,㭰,㭲,㮟,㮨,㰟,㱞,㲅,㲺,㳵,㴉,㴕,㸄,㹄,㻑,㻷,㽺,㾊,㾒,㾵,䁒,䋟,䐀,䐕,䐚,䒁,䓫,䓽,䗁,䚐,䜞,䝸,䞘,䟌,䠏,䢋,䢳,䣢,䤒,䤠,䦇,䨖,䩯,䮺,䯂,䰏,䲯,䳭,䶓,䶩|bu:不,佈,勏,卟,吥,咘,哺,埗,埠,峬,布,庯,廍,怖,悑,捗,晡,步,歨,歩,獛,瓿,篰,簿,荹,蔀,补,補,誧,踄,轐,逋,部,郶,醭,鈈,鈽,钚,钸,餔,餢,鳪,鵏,鸔,㘵,㙛,㚴,㨐,㳍,㻉,㾟,䀯,䊇,䋠,䍌,䏽,䑰,䒈,䝵,䪁,䪔,䬏,䳝,䴝,䴺|yu:与,予,于,亐,伃,伛,余,俁,俞,俣,俼,偊,傴,儥,兪,匬,吁,唹,喅,喐,喩,喻,噊,噳,圄,圉,圫,域,堉,堣,堬,妤,妪,娛,娯,娱,媀,嫗,嬩,宇,寓,寙,屿,峪,峿,崳,嵎,嵛,嶎,嶼,庽,庾,彧,御,忬,悆,惐,愈,愉,愚,慾,懙,戫,扜,扵,挧,揄,敔,斔,斞,於,旟,昱,杅,栯,棛,棜,棫,楀,楡,楰,榆,櫲,欎,欝,欤,欲,歈,歟,歶,毓,浴,淢,淤,淯,渔,渝,湡,滪,漁,澞,澦,灪,焴,煜,燏,燠,爩,牏,狱,狳,獄,玉,玗,玙,琙,瑀,瑜,璵,畬,畭,瘀,瘉,瘐,癒,盂,盓,睮,矞,砡,硢,礇,礖,礜,祤,禦,禹,禺,秗,稢,稶,穥,穻,窬,窳,竽,箊,篽,籅,籞,籲,紆,緎,繘,纡,罭,羭,羽,聿,育,腴,臾,舁,舆,與,艅,芋,芌,茟,茰,荢,萭,萮,萸,蒮,蓣,蓹,蕍,蕷,薁,蘌,蘛,虞,虶,蜟,蜮,蝓,螸,衧,袬,裕,褕,覦,觎,誉,語,諛,諭,謣,譽,语,谀,谕,豫,貐,踰,軉,輍,輿,轝,迃,逳,逾,遇,遹,邘,郁,鄅,酑,醧,釪,鈺,銉,鋊,鋙,錥,鍝,鐭,钰,铻,閾,阈,陓,隃,隅,隩,雓,雨,雩,霱,預,预,飫,餘,饇,饫,馀,馭,騟,驈,驭,骬,髃,鬰,鬱,鬻,魊,魚,魣,鮽,鯲,鰅,鱊,鱼,鳿,鴥,鴧,鴪,鵒,鷠,鷸,鸆,鸒,鹆,鹬,麌,齬,龉,㑨,㒁,㒜,㔱,㙑,㚥,㝢,㠘,㠨,㡰,㣃,㤤,㥔,㥚,㥥,㦛,㪀,㬂,㬰,㲾,㳚,㳛,㶛,㷒,㺄,㺞,㺮,㼌,㼶,㽣,䁌,䁩,䂊,䂛,䃋,䄏,䄨,䆷,䈅,䉛,䋖,䍂,䍞,䏸,䐳,䔡,䖇,䗨,䘘,䘱,䛕,䜽,䢓,䢩,䣁,䥏,䨒,䨞,䩒,䬄,䮇,䮙,䰻,䱷,䲣,䴁,䵫|mian:丏,俛,偭,免,冕,勉,勔,喕,娩,婂,媔,嬵,宀,愐,棉,檰,櫋,汅,沔,湎,眄,眠,矈,矊,矏,糆,綿,緜,緬,绵,缅,腼,臱,芇,葂,蝒,面,靣,鮸,麪,麫,麵,麺,黾,㒙,㛯,㝰,㤁,㬆,㮌,㰃,㴐,㻰,䀎,䃇,䏃,䤄,䫵,䰓|gai:丐,乢,侅,匃,匄,垓,姟,峐,忋,戤,摡,改,晐,杚,概,槩,槪,溉,漑,瓂,畡,盖,祴,絠,絯,荄,葢,蓋,該,该,豥,賅,賌,赅,郂,鈣,鎅,钙,陔,隑,㕢,㧉,㮣,䏗,䪱|chou:丑,丒,仇,侴,俦,偢,儔,吜,嬦,帱,幬,惆,愁,懤,抽,搊,杽,栦,椆,殠,燽,犨,犫,畴,疇,瘳,皗,瞅,矁,稠,筹,篘,籌,紬,絒,綢,绸,臭,臰,菗,薵,裯,詶,讎,讐,踌,躊,遚,酧,酬,醜,醻,雔,雠,魗,㐜,㛶,㤽,㦞,㨶,㵞,㿧,䇺,䊭,䌧,䌷,䓓,䔏,䛬,䥒,䪮,䲖|zhuan:专,僎,叀,啭,囀,堟,嫥,孨,専,專,撰,灷,瑑,瑼,甎,砖,磚,竱,篆,籑,縳,膞,蒃,蟤,襈,諯,譔,賺,赚,転,轉,转,鄟,顓,颛,饌,馔,鱄,䉵,䡱|qie:且,倿,切,匧,厒,妾,怯,悏,惬,愜,挈,朅,洯,淁,癿,穕,窃,竊,笡,箧,篋,籡,緁,聺,苆,茄,藒,蛪,踥,鍥,鐑,锲,魥,鯜,㓶,㗫,㚗,㛍,㛙,㤲,㥦,㫸,㰰,㰼,㹤,㾀,㾜,䟙,䤿,䦧,䬊|pi:丕,仳,伓,伾,僻,劈,匹,啤,噼,噽,嚊,嚭,圮,坯,埤,壀,媲,嫓,屁,岯,崥,庀,怶,悂,憵,批,披,抷,揊,擗,旇,朇,枇,椑,榌,毗,毘,毞,淠,渒,潎,澼,炋,焷,狉,狓,琵,甓,疈,疲,痞,癖,皮,睤,睥,砒,磇,礔,礕,秛,秠,笓,篺,簲,紕,纰,罴,羆,翍,耚,肶,脴,脾,腗,膍,芘,苉,蚍,蚽,蜱,螷,蠯,諀,譬,豼,豾,貔,邳,郫,釽,鈚,鈹,鉟,銔,銢,錃,錍,铍,闢,阰,陴,隦,霹,駓,髬,魮,魾,鮍,鲏,鴄,鵧,鷿,鸊,鼙,㔥,㨽,㯅,㿙,䏘,䑀,䑄,䚰,䚹,䠘,䡟,䤏,䤨,䫌,䰦,䴙|shi:世,丗,乨,亊,事,什,仕,佦,使,侍,兘,兙,冟,势,勢,十,卋,叓,史,呞,呩,嗜,噬,埘,塒,士,失,奭,始,姼,嬕,实,実,室,宩,寔,實,尸,屍,屎,峕,崼,嵵,市,师,師,式,弑,弒,徥,忕,恀,恃,戺,拭,拾,揓,施,时,旹,是,昰,時,枾,柹,柿,栻,榁,榯,檡,氏,浉,湜,湤,湿,溡,溮,溼,澨,濕,炻,烒,煶,狮,獅,瑡,瓧,眂,眎,眡,睗,矢,石,示,礻,祏,竍,笶,笹,筮,箷,篒,簭,籂,籭,絁,舐,舓,莳,葹,蒒,蒔,蓍,虱,虲,蚀,蝕,蝨,螫,褆,褷,襫,襹,視,视,觢,試,詩,誓,諟,諡,謚,識,识,试,诗,谥,豕,貰,贳,軾,轼,辻,适,逝,遈,適,遾,邿,酾,釃,釈,释,釋,釶,鈰,鉂,鉃,鉇,鉐,鉽,銴,鍦,铈,食,飠,飾,餝,饣,饰,駛,驶,鮖,鯴,鰘,鰣,鰤,鲥,鲺,鳲,鳾,鶳,鸤,鼫,鼭,齛,㒾,㔺,㕜,㖷,㫑,㮶,㱁,㵓,㸷,㹝,㹬,㹷,䁺,䂖,䊓,䏡,䒨,䖨,䛈,䟗,䤱,䦠,䦹,䩃,䭄,䰄,䴓,䶡|qiu:丘,丠,俅,叴,唒,囚,坵,媝,寈,崷,巯,巰,恘,扏,搝,朹,梂,楸,殏,毬,求,汓,泅,浗,渞,湭,煪,犰,玌,球,璆,皳,盚,秋,秌,穐,篍,糗,紌,絿,緧,肍,莍,萩,蓲,蘒,虬,虯,蚯,蛷,蝤,蝵,蟗,蠤,裘,觓,觩,訄,訅,賕,赇,趥,逎,逑,遒,邱,酋,醔,釚,釻,銶,鞦,鞧,鮂,鯄,鰌,鰍,鰽,鱃,鳅,鵭,鶖,鹙,鼽,龝,㐀,㐤,㕤,㞗,㟈,㤹,㥢,㧨,㭝,㷕,㺫,㼒,䆋,䊵,䎿,䜪,䞭,䟬,䟵,䠗,䣇,䤛|bing:丙,並,仌,併,倂,偋,傡,兵,冫,冰,垪,寎,并,幷,庰,怲,抦,掤,摒,昞,昺,柄,栟,栤,梹,棅,檳,氷,炳,燷,病,眪,禀,秉,窉,竝,苪,蛃,誁,邴,鈵,鉼,鋲,陃,靐,鞆,餅,餠,饼,鮩,㓈,㨀,䈂,䋑,䓑,䗒,䴵|ye:业,也,亪,亱,倻,偞,僷,冶,叶,吔,嘢,噎,嚈,埜,堨,墷,壄,夜,嶪,嶫,抴,捓,捙,掖,揶,擖,擛,擨,擪,擫,晔,暍,曄,曅,曗,曳,枼,枽,椰,楪,業,歋,殗,洂,液,漜,潱,澲,烨,燁,爗,爷,爺,皣,瞱,瞸,礏,耶,腋,葉,蠮,謁,谒,邺,鄓,鄴,野,釾,鋣,鍱,鎁,鎑,铘,靥,靨,頁,页,餣,饁,馌,驜,鵺,鸈,㐖,㖡,㖶,㗼,㙒,㙪,㝣,㥷,㩎,㪑,㱉,㸣,䈎,䓉,䤳,䤶,䥟,䥡,䥺,䧨,䭟,䲜|cong:丛,从,匆,叢,囪,囱,婃,孮,従,徖,從,忩,怱,悤,悰,慒,憁,暰,枞,棇,楤,樅,樬,樷,欉,淙,漎,漗,潀,潈,潨,灇,焧,熜,爜,琮,瑽,璁,瞛,篵,緫,繱,聡,聦,聪,聰,苁,茐,葱,蓯,蔥,藂,蟌,誴,謥,賨,賩,鏦,騘,驄,骢,㼻,䉘,䕺,䧚,䳷|dong:东,侗,倲,働,冬,冻,凍,动,動,咚,垌,埬,墥,姛,娻,嬞,岽,峒,峝,崠,崬,恫,懂,戙,挏,昸,東,栋,棟,氡,氭,洞,涷,湩,硐,笗,箽,胨,胴,腖,苳,菄,董,蕫,蝀,詷,諌,迵,霘,駧,鮗,鯟,鶇,鶫,鸫,鼕,㑈,㓊,㖦,㗢,㜱,㢥,㨂,㼯,䂢,䅍,䍶,䞒,䵔|si:丝,乺,亖,伺,似,佀,価,俟,俬,儩,兕,凘,厮,厶,司,咝,嗣,嘶,噝,四,姒,娰,媤,孠,寺,巳,廝,思,恖,撕,斯,枱,柶,梩,楒,榹,死,汜,泀,泗,泤,洍,涘,澌,瀃,燍,牭,磃,祀,禗,禠,禩,私,竢,笥,糹,絲,緦,纟,缌,罒,罳,耜,肂,肆,蕬,蕼,虒,蛳,蜤,螄,螦,蟖,蟴,覗,貄,釲,鈻,鉰,銯,鋖,鍶,鐁,锶,颸,飔,飤,飼,饲,駟,騃,騦,驷,鷥,鸶,鼶,㐌,㕽,㚶,㣈,㭒,㸻,㹑,㾅,䇃,䎣,䏤,䦙|cheng:丞,乗,乘,侱,偁,呈,城,埕,堘,塍,塖,娍,宬,峸,庱,徎,悜,惩,憆,憕,懲,成,承,挰,掁,摚,撐,撑,朾,枨,柽,棖,棦,椉,橕,橙,檉,檙,泟,洆,浾,溗,澂,澄,瀓,爯,牚,珵,珹,琤,畻,睈,瞠,碀,秤,称,程,穪,窚,竀,筬,絾,緽,脀,脭,荿,虰,蛏,蟶,裎,誠,诚,赪,赬,逞,郕,酲,鋮,鏳,鏿,鐺,铖,铛,阷,靗,頳,饓,騁,騬,骋,鯎,㐼,㞼,㨃,㲂,㼩,䀕,䁎,䄇,䆑,䆵,䆸,䇸,䔲,䗊,䞓,䧕,䫆,䮪|diu:丟,丢,銩,铥,颩|liang:両,两,亮,俍,兩,凉,哴,唡,啢,喨,墚,掚,晾,梁,椋,樑,涼,湸,煷,簗,粮,粱,糧,綡,緉,脼,良,蜽,裲,諒,谅,踉,輌,輛,輬,辆,辌,量,鍄,魉,魎,㒳,㔝,㹁,䓣,䝶,䠃,䣼,䩫,䭪|you:丣,亴,优,佑,侑,偤,優,卣,又,友,右,呦,哊,唀,嚘,囿,姷,孧,宥,尢,尤,峟,峳,幼,幽,庮,忧,怞,怣,怮,悠,憂,懮,攸,斿,有,柚,梄,梎,楢,槱,櫌,櫾,沋,油,泑,浟,游,湵,滺,瀀,牖,牗,牰,犹,狖,猶,猷,由,疣,祐,禉,秞,糿,纋,羐,羑,耰,聈,肬,脜,苃,莜,莠,莤,莸,蒏,蕕,蚰,蚴,蜏,蝣,訧,誘,诱,貁,輏,輶,迂,迶,逌,逰,遊,邎,邮,郵,鄾,酉,酭,釉,鈾,銪,铀,铕,駀,魷,鮋,鱿,鲉,麀,黝,鼬,㒡,㓜,㕗,㕱,㘥,㚭,㛜,㤑,㫍,㮋,㰶,㳺,㹨,㺠,㻀,㽕,㾞,䀁,䅎,䆜,䑻,䒴,䖻,䚃,䛻,䞥,䢊,䢟,䬀,䱂,䳑|yan:严,乵,俨,偃,偐,偣,傿,儼,兖,兗,円,剦,匽,厌,厣,厭,厳,厴,咽,唁,喭,噞,嚥,嚴,堰,塩,墕,壛,壧,夵,奄,妍,妟,姲,姸,娫,娮,嫣,嬊,嬮,嬿,孍,宴,岩,崦,嵃,嵒,嵓,嶖,巌,巖,巗,巘,巚,延,弇,彥,彦,恹,愝,懕,戭,扊,抁,掞,掩,揅,揜,敥,昖,晏,暥,曕,曮,棪,椻,椼,楌,樮,檐,檿,櫩,欕,沇,沿,淹,渰,渷,湮,滟,演,漹,灎,灔,灧,灩,炎,烟,焉,焔,焰,焱,煙,熖,燄,燕,爓,牪,狿,猒,珚,琂,琰,甗,盐,眼,研,砚,硏,硯,硽,碞,礹,筵,篶,簷,綖,縯,罨,胭,腌,臙,艳,艶,艷,芫,莚,菸,萒,葕,蔅,虤,蜒,蝘,衍,裺,褗,覎,觃,觾,言,訁,訮,詽,諺,讌,讞,讠,谚,谳,豓,豔,贋,贗,贘,赝,躽,軅,遃,郔,郾,鄢,酀,酓,酽,醃,醶,醼,釅,閆,閹,閻,闫,阉,阎,隁,隒,雁,顏,顔,顩,颜,餍,饜,騐,験,騴,驗,驠,验,鬳,魇,魘,鰋,鳫,鴈,鴳,鶠,鷃,鷰,鹽,麙,麣,黡,黤,黫,黬,黭,黶,鼴,鼹,齞,齴,龑,㓧,㕣,㗴,㘖,㘙,㚧,㛪,㢂,㢛,㦔,㫃,㫟,㬫,㭺,㳂,㶄,㷔,㷳,㷼,㿕,㿼,䀋,䀽,䁙,䂩,䂴,䄋,䅧,䊙,䊻,䌪,䎦,䑍,䓂,䕾,䖗,䗡,䗺,䜩,䢥,䢭,䣍,䤷,䨄,䭘,䱲,䲓,䳛,䳺,䴏,䶮|sang:丧,喪,嗓,搡,桑,桒,槡,磉,褬,鎟,顙,颡,䡦,䫙|gun:丨,惃,棍,棞,滚,滾,璭,睔,睴,磙,緄,緷,绲,蓘,蔉,衮,袞,裷,謴,輥,辊,鮌,鯀,鲧,㙥,㫎,㯻,䃂,䎾,䜇,䵪|jiu:丩,久,乆,九,乣,倃,僦,勼,匓,匛,匶,厩,咎,啾,奺,就,廄,廏,廐,慦,揂,揪,揫,摎,救,旧,朻,杦,柩,柾,桕,樛,欍,殧,汣,灸,牞,玖,畂,疚,究,糺,糾,紤,纠,臼,舅,舊,舏,萛,赳,酒,镹,阄,韭,韮,鬏,鬮,鯦,鳩,鷲,鸠,鹫,麔,齨,㠇,㡱,㧕,㩆,㲃,㶭,㺩,㺵,䅢,䆒,䊆,䊘,䓘,䛮,䡂,䳎,䳔|ge:个,佮,個,割,匌,各,呄,哥,哿,嗝,圪,塥,彁,愅,戈,戓,戨,挌,搁,搿,擱,敋,格,槅,櫊,歌,滆,滒,牫,牱,犵,獦,疙,硌,箇,紇,纥,肐,胳,膈,臵,舸,茖,葛,虼,蛒,蛤,袼,裓,觡,諽,謌,輵,轕,鉻,鎘,鎶,铬,镉,閣,閤,阁,隔,革,鞈,鞷,韐,韚,騔,骼,鬲,鮯,鰪,鴐,鴚,鴿,鸽,㗆,㝓,㠷,㦴,㨰,㪾,㵧,㷴,䆟,䈓,䐙,䕻,䗘,䘁,䛋,䛿,䢔,䧄,䨣,䩐,䪂,䪺,䫦|ya:丫,亚,亜,亞,伢,俹,劜,厊,压,厓,呀,哑,唖,啞,圔,圠,垭,埡,堐,壓,娅,婭,孲,岈,崕,崖,庌,庘,押,挜,掗,揠,枒,桠,椏,氩,氬,涯,漄,牙,犽,猚,猰,玡,琊,瑘,疋,痖,瘂,睚,砑,稏,穵,窫,笌,聐,芽,蕥,蚜,衙,襾,訝,讶,迓,錏,鐚,铔,雅,鴉,鴨,鵶,鸦,鸭,齖,齾,㝞,㧎,㰳,㿿,䄰,䅉,䊦,䝟,䢝,䦪,䪵,䯉,䰲,䵝|zhuang:丬,壮,壯,壵,妆,妝,娤,庄,庒,戅,撞,桩,梉,樁,湷,焋,状,狀,粧,糚,荘,莊,装,裝|zhong:中,仲,伀,众,偅,冢,刣,喠,堹,塚,妐,妕,媑,尰,幒,彸,忠,柊,歱,汷,泈,炂,煄,狆,瘇,盅,眾,祌,祍,种,種,筗,籦,終,终,肿,腫,舯,茽,蔠,蚛,螤,螽,衆,衳,衶,衷,諥,踵,蹱,迚,重,鈡,鍾,鐘,钟,锺,鴤,鼨,㐺,㣫,㲴,䱰|jie:丯,介,借,倢,偼,傑,刦,刧,刼,劫,劼,卩,卪,吤,喈,嗟,堦,堺,姐,婕,媎,媘,媫,嫅,孑,尐,屆,届,岊,岕,崨,嵥,嶻,巀,幯,庎,徣,忦,悈,戒,截,拮,捷,接,掲,揭,擑,擮,擳,斺,昅,杰,桀,桔,桝,椄,楐,楬,楶,榤,檞,櫭,毑,洁,湝,滐,潔,煯,犗,玠,琾,界,畍,疌,疖,疥,痎,癤,皆,睫,砎,碣,礍,秸,稭,竭,節,結,繲,结,羯,脻,节,芥,莭,菨,蓵,藉,蚧,蛣,蛶,蜐,蝔,蠘,蠞,蠽,街,衱,衸,袺,褯,解,觧,訐,詰,誡,誱,讦,诘,诫,踕,迼,鉣,鍻,阶,階,鞂,頡,颉,飷,骱,魝,魪,鮚,鲒,鶛,㑘,㓗,㓤,㔾,㘶,㛃,㝌,㝏,㞯,㠹,㦢,㨗,㨩,㮞,㮮,㸅,㾏,㿍,䀷,䀹,䁓,䂒,䂝,䂶,䅥,䇒,䌖,䔿,䕙,䗻,䛺,䣠,䥛,䯰,䰺,䱄,䲙,䲸|feng:丰,仹,俸,偑,僼,冯,凤,凨,凬,凮,唪,堸,夆,奉,妦,寷,封,峯,峰,崶,捀,摓,枫,桻,楓,檒,沣,沨,浲,渢,湗,溄,灃,烽,焨,煈,犎,猦,琒,瓰,甮,疯,瘋,盽,砜,碸,篈,綘,縫,缝,艂,葑,蘕,蘴,蜂,覂,諷,讽,豐,賵,赗,逢,鄷,酆,鋒,鎽,鏠,锋,靊,風,飌,风,馮,鳯,鳳,鴌,麷,㡝,㦀,㵯,䏎,䙜,䟪,䩼|guan:丱,倌,关,冠,卝,官,悹,悺,惯,慣,掼,摜,棺,樌,毌,泴,涫,潅,灌,爟,琯,瓘,痯,瘝,癏,盥,矔,礶,祼,窤,筦,管,罆,罐,舘,萖,蒄,覌,観,觀,观,貫,贯,躀,輨,遦,錧,鏆,鑵,関,闗,關,雚,館,馆,鰥,鱞,鱹,鳏,鳤,鸛,鹳,㮡,㴦,䌯,䎚,䏓,䗆,䗰,䘾,䙛,䙮,䝺,䦎,䩪,䪀,䲘|chuan:串,传,傳,僢,剶,喘,圌,巛,川,暷,椽,歂,氚,汌,猭,玔,瑏,穿,篅,舛,舡,舩,船,荈,賗,輲,遄,釧,钏,镩,鶨,㯌,㱛,㼷,䁣|chan:丳,产,僝,儃,儳,冁,刬,剗,剷,劖,嚵,囅,壥,婵,嬋,孱,嵼,巉,幝,幨,廛,忏,懴,懺,掺,搀,摌,摲,摻,攙,旵,梴,棎,欃,毚,浐,湹,滻,潹,潺,瀍,瀺,灛,煘,燀,獑,產,産,硟,磛,禅,禪,簅,緾,繟,繵,纏,纒,缠,羼,艬,蒇,蕆,蝉,蝊,蟬,蟾,裧,襜,覘,觇,誗,諂,譂,讇,讒,谄,谗,躔,辿,鄽,酁,醦,鋋,鋓,鏟,鑱,铲,镡,镵,閳,闡,阐,韂,顫,颤,饞,馋,㔆,㙴,㙻,㢆,㢟,㦃,㬄,㯆,㵌,㶣,㸥,㹌,㹽,㺥,䀡,䂁,䊲,䐮,䑎,䜛,䠨,䡪,䡲,䣑,䤘,䤫,䥀,䧯,䩶,䪜,䮭,䱿,䴼,䵐|lin:临,亃,僯,凛,凜,厸,吝,啉,壣,崊,嶙,廩,廪,恡,悋,惏,懍,懔,拎,撛,斴,晽,暽,林,橉,檁,檩,淋,潾,澟,瀶,焛,燐,獜,琳,璘,甐,疄,痳,癛,癝,瞵,碄,磷,稟,箖,粦,粼,繗,翷,膦,臨,菻,蔺,藺,賃,赁,蹸,躏,躙,躪,轔,轥,辚,遴,邻,鄰,鏻,閵,隣,霖,顲,驎,鱗,鳞,麐,麟,㐭,㔂,㖁,㝝,㨆,㷠,䉮,䕲,䗲,䚬,䢯,䫐,䫰,䮼|zhuo:丵,倬,劅,卓,叕,啄,啅,圴,妰,娺,彴,拙,捉,撯,擆,擢,斀,斫,斮,斱,斲,斵,晫,桌,梲,棁,棳,椓,槕,櫡,浊,浞,涿,濁,濯,灂,灼,炪,烵,犳,琢,琸,着,硺,禚,穛,穱,窡,窧,篧,籗,籱,罬,茁,蠗,蠿,諁,諑,謶,诼,酌,鋜,鐯,鐲,镯,鵫,鷟,㑁,㣿,㪬,㭬,㺟,䅵,䕴,䶂|zhu:丶,主,伫,佇,住,侏,劚,助,劯,嘱,囑,坾,墸,壴,孎,宔,屬,嵀,拄,斸,曯,朱,杼,柱,柷,株,槠,樦,橥,櫧,櫫,欘,殶,泏,注,洙,渚,潴,濐,瀦,灟,炢,炷,烛,煑,煮,燭,爥,猪,珠,疰,瘃,眝,瞩,矚,砫,硃,祝,祩,秼,窋,竚,竹,竺,笁,笜,筑,筯,箸,築,篫,紵,紸,絑,纻,罜,羜,翥,舳,芧,苎,苧,茱,茿,莇,著,蓫,藸,蛀,蛛,蝫,蠋,蠩,蠾,袾,註,詝,誅,諸,诛,诸,豬,貯,贮,跓,跦,躅,軴,迬,逐,邾,鉒,銖,鋳,鑄,钃,铢,铸,陼,霔,飳,馵,駐,駯,驻,鮢,鯺,鱁,鴸,麆,麈,鼄,㑏,㔉,㝉,㤖,㧣,㫂,㵭,㹥,㺛,㾻,㿾,䇡,䇧,䌵,䍆,䎷,䐢,䕽,䘚,䘢,䝒,䝬,䟉,䥮,䬡,䭖,䮱,䰞|ha:丷,哈,妎,鉿,铪|dan:丹,亶,伔,但,僤,儋,刐,勯,匰,单,単,啖,啗,啿,單,嘾,噉,嚪,妉,媅,帎,弹,彈,惮,憚,憺,担,掸,撣,擔,旦,柦,殚,殫,氮,沊,泹,淡,澸,澹,狚,玬,瓭,甔,疍,疸,瘅,癉,癚,眈,砃,禫,窞,箪,簞,紞,耼,耽,聃,聸,胆,腅,膽,萏,蛋,蜑,衴,褝,襌,觛,訑,誕,诞,贉,躭,郸,鄲,酖,霮,頕,餤,饏,馾,駳,髧,鴠,黕,㔊,㕪,㗖,㡺,㫜,㱽,㲷,㵅,㺗,㽎,䃫,䄷,䉞,䉷,䨢,䨵,䩥,䭛,䮰,䱋,䳉|wei:为,亹,伟,伪,位,偉,偎,偽,僞,儰,卫,危,厃,叞,味,唯,喂,喡,喴,囗,围,圍,圩,墛,壝,委,威,娓,媁,媙,媦,寪,尉,尾,峗,峞,崣,嵔,嵬,嶶,巍,帏,帷,幃,廆,徫,微,惟,愄,愇,慰,懀,捤,揋,揻,斖,昷,暐,未,桅,梶,椲,椳,楲,沩,洈,洧,浘,涠,渨,渭,湋,溈,溦,潍,潙,潿,濊,濰,濻,瀢,炜,為,烓,煀,煒,煟,煨,熭,燰,爲,犚,犩,猥,猬,玮,琟,瑋,璏,畏,痏,痿,癐,癓,硊,硙,碨,磑,維,緭,緯,縅,纬,维,罻,胃,腲,艉,芛,苇,苿,荱,菋,萎,葦,葨,葳,蒍,蓶,蔚,蔿,薇,薳,藯,蘶,蜲,蜼,蝛,蝟,螱,衛,衞,褽,覣,覹,詴,諉,謂,讆,讏,诿,谓,踓,躗,躛,軎,轊,违,逶,違,鄬,醀,錗,鍏,鍡,鏏,闈,闱,隇,隈,隗,霨,霺,霻,韋,韑,韙,韡,韦,韪,頠,颹,餧,餵,饖,骩,骪,骫,魏,鮇,鮠,鮪,鰃,鰄,鲔,鳂,鳚,㕒,㖐,㞇,㞑,㟪,㠕,㢻,㣲,㥜,㦣,㧑,㨊,㬙,㭏,㱬,㷉,䃬,䈧,䉠,䑊,䔺,䗽,䘙,䙿,䜅,䜜,䝐,䞔,䡺,䥩,䧦,䪋,䪘,䬐,䬑,䬿,䭳,䮹,䲁,䵋,䵳|jing:丼,井,京,亰,俓,倞,傹,儆,兢,净,凈,刭,剄,坓,坕,坙,境,妌,婙,婛,婧,宑,巠,幜,弪,弳,径,徑,惊,憬,憼,敬,旌,旍,景,晶,暻,曔,桱,梷,橸,汫,汬,泾,浄,涇,淨,瀞,燝,燞,猄,獍,璄,璟,璥,痉,痙,睛,秔,稉,穽,竞,竟,竧,竫,競,竸,粳,精,経,經,经,聙,肼,胫,脛,腈,茎,荆,荊,莖,菁,蟼,誩,警,踁,迳,逕,鏡,镜,阱,靓,靖,静,靚,靜,頚,頸,颈,驚,鯨,鲸,鵛,鶁,鶄,麖,麠,鼱,㕋,㘫,㢣,㣏,㬌,㵾,㹵,䔔,䜘,䡖,䴖,䵞|li:丽,例,俐,俚,俪,傈,儮,儷,凓,刕,利,剓,剺,劙,力,励,勵,历,厉,厘,厤,厯,厲,吏,呖,哩,唎,唳,喱,嚟,嚦,囄,囇,坜,塛,壢,娌,娳,婯,嫠,孋,孷,屴,岦,峛,峲,巁,廲,悡,悧,悷,慄,戾,搮,攊,攦,攭,斄,暦,曆,曞,朸,李,枥,栃,栎,栗,栛,栵,梨,梸,棃,棙,樆,檪,櫔,櫟,櫪,欐,欚,歴,歷,氂,沥,沴,浬,涖,溧,漓,澧,濿,瀝,灕,爄,爏,犁,犂,犛,犡,狸,猁,珕,理,琍,瑮,璃,瓅,瓈,瓑,瓥,疠,疬,痢,癘,癧,皪,盠,盭,睝,砅,砺,砾,磿,礪,礫,礰,礼,禮,禲,离,秝,穲,立,竰,笠,筣,篥,篱,籬,粒,粝,粴,糎,糲,綟,縭,缡,罹,艃,苈,苙,茘,荔,荲,莅,莉,菞,蒚,蒞,蓠,蔾,藜,藶,蘺,蚸,蛎,蛠,蜊,蜧,蟍,蟸,蠇,蠡,蠣,蠫,裏,裡,褵,詈,謧,讈,豊,貍,赲,跞,躒,轢,轣,轹,逦,邌,邐,郦,酈,醨,醴,里,釐,鉝,鋫,鋰,錅,鏫,鑗,锂,隶,隷,隸,離,雳,靂,靋,驪,骊,鬁,鯉,鯏,鯬,鱧,鱱,鱺,鲡,鲤,鳢,鳨,鴗,鵹,鷅,鸝,鹂,麗,麜,黎,黧,㑦,㒧,㒿,㓯,㔏,㕸,㗚,㘑,㟳,㠟,㡂,㤡,㤦,㦒,㧰,㬏,㮚,㯤,㰀,㰚,㱹,㴝,㷰,㸚,㹈,㺡,㻎,㻺,㼖,㽁,㽝,㾐,㾖,㿛,㿨,䁻,䃯,䄜,䅄,䅻,䇐,䉫,䊍,䊪,䋥,䍠,䍦,䍽,䓞,䔁,䔆,䔉,䔣,䔧,䖥,䖽,䖿,䗍,䘈,䙰,䚕,䟏,䟐,䡃,䣓,䣫,䤙,䤚,䥶,䧉,䬅,䬆,䮋,䮥,䰛,䰜,䱘,䲞,䴄,䴡,䴻,䵓,䵩,䶘|ju:举,侷,俱,倨,倶,僪,具,冣,凥,剧,劇,勮,匊,句,咀,圧,埧,埾,壉,姖,娵,婅,婮,寠,局,居,屦,屨,岠,崌,巈,巨,弆,怇,怚,惧,愳,懅,懼,抅,拒,拘,拠,挙,挶,捄,据,掬,據,擧,昛,梮,椇,椈,椐,榉,榘,橘,檋,櫸,欅,歫,毩,毱,沮,泃,泦,洰,涺,淗,湨,澽,炬,焗,焣,爠,犋,犑,狊,狙,琚,疽,痀,眗,瞿,矩,砠,秬,窭,窶,筥,簴,粔,粷,罝,耟,聚,聥,腒,舉,艍,苣,苴,莒,菊,蒟,蓻,蘜,虡,蚷,蜛,袓,裾,襷,詎,諊,讵,豦,貗,趄,趜,跔,跙,距,跼,踘,踞,踽,蹫,躆,躹,輂,遽,邭,郹,醵,鉅,鋦,鋸,鐻,钜,锔,锯,閰,陱,雎,鞠,鞫,颶,飓,駏,駒,駶,驧,驹,鮈,鮔,鴡,鵙,鵴,鶋,鶪,鼰,鼳,齟,龃,㘌,㘲,㜘,㞐,㞫,㠪,㥌,㨿,㩀,㩴,㬬,㮂,㳥,㽤,䃊,䄔,䅓,䆽,䈮,䋰,䏱,䕮,䗇,䛯,䜯,䡞,䢹,䣰,䤎,䪕,䰬,䱟,䱡,䴗,䵕,䶙,䶥|pie:丿,嫳,撆,撇,暼,氕,瞥,苤,鐅,䥕|fu:乀,付,伏,伕,俌,俘,俯,偩,傅,冨,冹,凫,刜,副,匐,呋,呒,咈,咐,哹,坿,垘,复,夫,妇,妋,姇,娐,婏,婦,媍,孚,孵,富,尃,岪,峊,巿,帗,幅,幞,府,弗,弣,彿,復,怤,怫,懯,扶,抚,拂,拊,捬,撫,敷,斧,旉,服,枎,枹,柎,柫,栿,桴,棴,椨,椱,榑,氟,泭,洑,浮,涪,滏,澓,炥,烰,焤,父,猤,玞,玸,琈,甫,甶,畉,畐,畗,癁,盙,砆,砩,祓,祔,福,禣,秿,稃,稪,竎,符,笰,筟,箙,簠,粰,糐,紨,紱,紼,絥,綍,綒,緮,縛,绂,绋,缚,罘,罦,翇,肤,胕,脯,腐,腑,腹,膚,艀,艴,芙,芣,芾,苻,茀,茯,荂,荴,莩,菔,萯,葍,蕧,虙,蚥,蚨,蚹,蛗,蜅,蜉,蝜,蝠,蝮,衭,袝,袱,複,褔,襆,襥,覄,覆,訃,詂,諨,讣,豧,負,賦,賻,负,赋,赙,赴,趺,跗,踾,輔,輹,輻,辅,辐,邞,郙,郛,鄜,酜,釜,釡,鈇,鉘,鉜,鍑,鍢,阜,阝,附,陚,韍,韨,颫,馥,駙,驸,髴,鬴,鮄,鮒,鮲,鰒,鲋,鳆,鳧,鳬,鳺,鴔,鵩,鶝,麩,麬,麱,麸,黻,黼,㓡,㕮,㙏,㚆,㚕,㜑,㟊,㠅,㤔,㤱,㪄,㫙,㬼,㳇,㵗,㽬,㾈,䂤,䃽,䋨,䋹,䌗,䌿,䍖,䎅,䑧,䒀,䒇,䓛,䔰,䕎,䗄,䘀,䘄,䘠,䝾,䞜,䞞,䞯,䞸,䟔,䟮,䠵,䡍,䦣,䧞,䨗,䨱,䩉,䪙,䫍,䫝,䭸,䮛,䯱,䯽,䵗,䵾|nai:乃,倷,奈,奶,妳,嬭,孻,廼,摨,柰,氖,渿,熋,疓,耐,腉,艿,萘,螚,褦,迺,釢,錼,鼐,㮈,㮏,㲡,㾍,䍲,䘅,䯮|wu:乄,乌,五,仵,伆,伍,侮,俉,倵,儛,兀,剭,务,務,勿,午,吳,吴,吾,呉,呜,唔,啎,嗚,圬,坞,塢,奦,妩,娪,娬,婺,嫵,寤,屋,屼,岉,嵍,嵨,巫,庑,廡,弙,忢,忤,怃,悞,悟,悮,憮,戊,扤,捂,摀,敄,无,旿,晤,杇,杌,梧,橆,歍,武,毋,汙,汚,污,洖,洿,浯,溩,潕,烏,焐,焑,無,熃,熓,物,牾,玝,珷,珸,瑦,璑,甒,痦,矹,碔,祦,禑,窏,窹,箼,粅,舞,芜,芴,茣,莁,蕪,蘁,蜈,螐,誈,誣,誤,诬,误,趶,躌,迕,逜,邬,郚,鄔,釫,鋈,錻,鎢,钨,阢,隖,雾,霚,霧,靰,騖,骛,鯃,鰞,鴮,鵐,鵡,鶩,鷡,鹀,鹉,鹜,鼯,鼿,齀,㐅,㐳,㑄,㡔,㬳,㵲,㷻,㹳,㻍,㽾,䃖,䍢,䎸,䑁,䒉,䛩,䟼,䡧,䦍,䦜,䫓,䮏,䳇,䳱|tuo:乇,佗,侂,侻,咃,唾,坨,堶,妥,媠,嫷,岮,庹,彵,托,扡,拓,拕,拖,挩,捝,撱,杔,柁,柝,椭,楕,槖,橐,橢,毤,毻,汑,沰,沱,涶,狏,砣,砤,碢,箨,籜,紽,脫,脱,莌,萚,蘀,袉,袥,託,詑,讬,跅,跎,踻,迱,酡,陀,陁,飥,饦,馱,馲,駄,駝,駞,騨,驒,驝,驮,驼,魠,鮀,鰖,鴕,鵎,鸵,鼉,鼍,鼧,㟎,㸰,㸱,㼠,㾃,䍫,䓕,䡐,䪑,䭾,䰿,䲊,䴱|me:么,嚒,嚰,庅,濹,癦|ho:乊,乥|zhi:之,乿,侄,俧,倁,値,值,偫,傂,儨,凪,制,劕,劧,卮,厔,只,吱,咫,址,坁,坧,垁,埴,執,墆,墌,夂,姪,娡,嬂,寘,峙,崻,巵,帋,帙,帜,幟,庢,庤,廌,彘,徏,徔,徝,志,忮,恉,慹,憄,懥,懫,戠,执,扺,扻,抧,挃,指,挚,掷,搘,搱,摭,摯,擲,擿,支,旘,旨,晊,智,枝,枳,柣,栀,栉,桎,梔,梽,植,椥,榰,槜,樴,櫍,櫛,止,殖,汁,汥,汦,沚,治,洔,洷,淽,滍,滞,滯,漐,潌,潪,瀄,炙,熫,狾,猘,瓆,瓡,畤,疐,疷,疻,痔,痣,瘈,直,知,砋,礩,祉,祑,祗,祬,禃,禔,禵,秓,秖,秩,秪,秲,秷,稙,稚,稺,穉,窒,筫,紙,紩,絷,綕,緻,縶,織,纸,织,置,翐,聀,聁,职,職,肢,胑,胝,脂,膣,膱,至,致,臸,芖,芝,芷,茋,藢,蘵,蛭,蜘,螲,蟙,衹,衼,袟,袠,製,襧,覟,觗,觯,觶,訨,誌,謢,豑,豒,豸,貭,質,贄,质,贽,趾,跖,跱,踬,踯,蹠,蹢,躑,躓,軄,軹,輊,轵,轾,郅,酯,釞,銍,鋕,鑕,铚,锧,阤,阯,陟,隲,隻,雉,馶,馽,駤,騭,騺,驇,骘,鯯,鳷,鴙,鴲,鷙,鸷,黹,鼅,㕄,㗌,㗧,㘉,㙷,㛿,㜼,㝂,㣥,㧻,㨁,㨖,㮹,㲛,㴛,䄺,䅩,䆈,䇛,䇽,䉅,䉜,䌤,䎺,䏄,䏯,䐈,䐭,䑇,䓌,䕌,䚦,䛗,䝷,䞃,䟈,䡹,䥍,䦯,䫕,䬹,䭁,䱥,䱨,䳅,䵂|zha:乍,偧,劄,厏,吒,咋,咤,哳,喳,奓,宱,扎,抯,挓,揸,搩,搾,摣,札,柤,柵,栅,楂,榨,樝,渣,溠,灹,炸,煠,牐,甴,痄,皶,皻,皼,眨,砟,箚,膪,苲,蚱,蚻,觰,詐,譇,譗,诈,踷,軋,轧,迊,醡,鍘,铡,閘,闸,霅,鮓,鮺,鲊,鲝,齄,齇,㒀,㡸,㱜,㴙,㷢,䋾,䕢,䖳,䛽,䞢,䥷,䵙,䵵|hu:乎,乕,互,冱,冴,匢,匫,呼,唬,唿,喖,嘑,嘝,嚛,囫,垀,壶,壷,壺,婟,媩,嫭,嫮,寣,岵,帍,幠,弖,弧,忽,怙,恗,惚,戶,户,戸,戽,扈,抇,护,搰,摢,斛,昈,昒,曶,枑,楜,槲,槴,歑,汻,沍,沪,泘,浒,淴,湖,滬,滸,滹,瀫,烀,焀,煳,熩,狐,猢,琥,瑚,瓠,瓳,祜,笏,箎,箶,簄,粐,糊,絗,綔,縠,胡,膴,芐,苸,萀,葫,蔛,蔰,虍,虎,虖,虝,蝴,螜,衚,觳,謼,護,軤,轷,鄠,醐,錿,鍙,鍸,雐,雽,韄,頀,頶,餬,鬍,魱,鯱,鰗,鱯,鳠,鳸,鶘,鶦,鶮,鸌,鹕,鹱,㕆,㗅,㦿,㨭,㪶,㫚,㯛,㸦,㹱,㺉,㾰,㿥,䁫,䇘,䈸,䉉,䉿,䊀,䍓,䎁,䔯,䕶,䗂,䚛,䛎,䞱,䠒,䧼,䨥,䨼,䩴,䪝,䭅,䭌,䭍,䮸,䲵|fa:乏,伐,佱,傠,发,垡,姂,彂,栰,橃,沷,法,灋,珐,琺,疺,発,發,瞂,砝,筏,罚,罰,罸,茷,藅,醗,鍅,閥,阀,髪,髮,㕹,㘺,㛲,䂲,䇅,䒥,䣹|le:乐,仂,勒,叻,忇,扐,楽,樂,氻,泐,玏,砳,竻,簕,艻,阞,韷,餎,饹,鰳,鳓,㔹,㖀,㦡|yin:乑,乚,侌,冘,凐,印,吟,吲,唫,喑,噖,噾,嚚,囙,因,圁,垔,垠,垽,堙,夤,姻,婣,婬,寅,尹,峾,崟,崯,嶾,廕,廴,引,愔,慇,慭,憖,憗,懚,斦,朄,栶,檃,檭,檼,櫽,歅,殥,殷,氤,泿,洇,洕,淫,淾,湚,溵,滛,濥,濦,烎,犾,狺,猌,珢,璌,瘖,瘾,癊,癮,碒,磤,禋,秵,筃,粌,絪,緸,胤,苂,茚,茵,荫,荶,蒑,蔩,蔭,蘟,蚓,螾,蟫,裀,訔,訚,訡,誾,諲,讔,趛,鄞,酳,釿,鈏,鈝,銀,銦,铟,银,闉,阥,阴,陰,陻,隂,隐,隠,隱,霒,霠,霪,靷,鞇,音,韾,飮,飲,饮,駰,骃,鮣,鷣,齗,齦,龂,龈,㐆,㕂,㖗,㙬,㝙,㞤,㡥,㣧,㥯,㥼,㦩,㧈,㪦,㱃,㴈,㸒,㹜,㹞,㼉,㾙,䇙,䌥,䒡,䓄,䕃,䖜,䚿,䡛,䤃,䤺,䨸,䪩,䲟|ping:乒,俜,凭,凴,呯,坪,塀,娦,屏,屛,岼,帡,帲,幈,平,慿,憑,枰,檘,洴,涄,淜,焩,玶,瓶,甁,甹,砯,竮,箳,簈,缾,聠,艵,苹,荓,萍,蓱,蘋,蚲,蛢,評,评,軿,輧,郱,頩,鮃,鲆,㺸,㻂,䍈,䶄|pang:乓,厐,嗙,嫎,庞,旁,汸,沗,滂,炐,篣,耪,肨,胖,胮,膖,舽,螃,蠭,覫,逄,雱,霶,髈,鰟,鳑,龎,龐,㜊,㤶,㥬,㫄,䅭,䒍,䨦,䮾|qiao:乔,侨,俏,僑,僺,劁,喬,嘺,墝,墽,嫶,峭,嵪,巧,帩,幧,悄,愀,憔,撬,撽,敲,桥,槗,樵,橇,橋,殼,燆,犞,癄,睄,瞧,硗,硚,碻,磽,礄,窍,竅,繑,繰,缲,翘,翹,荍,荞,菬,蕎,藮,誚,譙,诮,谯,趫,趬,跷,踍,蹺,蹻,躈,郻,鄗,鄡,鄥,釥,鍫,鍬,鐈,鐰,锹,陗,鞒,鞘,鞩,鞽,韒,頝,顦,骹,髚,髜,㚁,㚽,㝯,㡑,㢗,㤍,㪣,㴥,䀉,䃝,䆻,䇌,䎗,䩌,䱁,䲾|guai:乖,叏,夬,怪,恠,拐,枴,柺,箉,㧔,㷇,㽇,䂯,䊽|mie:乜,吀,咩,哶,孭,幭,懱,搣,櫗,滅,瀎,灭,瓱,篾,蔑,薎,蠛,衊,覕,鑖,鱴,鴓,㒝,䁾,䈼,䘊,䩏|xi:习,係,俙,傒,僖,兮,凞,匸,卌,卥,厀,吸,呬,咥,唏,唽,喜,嘻,噏,嚱,塈,壐,夕,奚,娭,媳,嬆,嬉,屃,屖,屗,屣,嵠,嶍,巇,希,席,徆,徙,徚,徯,忚,忥,怬,怸,恄,息,悉,悕,惁,惜,慀,憘,憙,戏,戯,戱,戲,扸,昔,晞,晰,晳,暿,曦,杫,析,枲,桸,椞,椺,榽,槢,樨,橀,橲,檄,欯,欷,歖,歙,歚,氥,汐,洗,浠,淅,渓,溪,滊,漇,漝,潝,潟,澙,烯,焁,焈,焟,焬,煕,熂,熄,熈,熙,熹,熺,熻,燨,爔,牺,犀,犔,犠,犧,狶,玺,琋,璽,瘜,皙,盻,睎,瞦,矖,矽,硒,磶,礂,禊,禧,稀,稧,穸,窸,粞,系,細,綌,緆,縰,繥,纚,细,绤,羲,習,翕,翖,肸,肹,膝,舄,舾,莃,菥,葈,葸,蒠,蒵,蓆,蓰,蕮,薂,虩,蜥,蝷,螅,螇,蟋,蟢,蠵,衋,袭,襲,西,覀,覡,覤,觋,觹,觽,觿,誒,諰,謑,謵,譆,诶,谿,豀,豨,豯,貕,赥,赩,趇,趘,蹝,躧,郄,郋,郗,郤,鄎,酅,醯,釳,釸,鈢,銑,錫,鎴,鏭,鑴,铣,锡,闟,阋,隙,隟,隰,隵,雟,霫,霼,飁,餏,餙,餼,饩,饻,騱,騽,驨,鬩,鯑,鰼,鱚,鳛,鵗,鸂,黖,鼷,㑶,㔒,㗩,㙾,㚛,㞒,㠄,㣟,㤴,㤸,㥡,㦻,㩗,㭡,㳧,㵿,㸍,㹫,㽯,㿇,䀘,䂀,䈪,䊠,䏮,䐼,䓇,䙽,䚷,䛥,䜁,䢄,䧍,䨳,䩤,䫣,䮎,䲪|xiang:乡,享,亯,佭,像,勨,厢,向,响,啌,嚮,姠,嶑,巷,庠,廂,忀,想,晑,曏,栙,楿,橡,欀,湘,珦,瓖,瓨,相,祥,箱,絴,緗,缃,缿,翔,膷,芗,萫,葙,薌,蚃,蟓,蠁,襄,襐,詳,详,象,跭,郷,鄉,鄊,鄕,銄,鐌,鑲,镶,響,項,项,飨,餉,饗,饟,饷,香,驤,骧,鮝,鯗,鱌,鱜,鱶,鲞,麘,㐮,㗽,㟄,㟟,䊑,䐟,䔗,䖮,䜶,䢽|hai:乤,亥,咍,嗐,嗨,嚡,塰,孩,害,氦,海,烸,胲,酼,醢,餀,饚,駭,駴,骇,骸,㜾,㤥,㦟,㧡,㨟,㺔,䇋,䠽,䯐,䱺|shu:书,侸,倏,倐,儵,叔,咰,塾,墅,姝,婌,孰,尌,尗,属,庶,庻,怷,恕,戍,抒,掓,摅,攄,数,數,暏,暑,曙,書,朮,术,束,杸,枢,柕,树,梳,樞,樹,橾,殊,殳,毹,毺,沭,淑,漱,潄,潻,澍,濖,瀭,焂,熟,瑹,璹,疎,疏,癙,秫,竖,竪,籔,糬,紓,絉,綀,纾,署,腧,舒,荗,菽,蒁,蔬,薥,薯,藷,虪,蜀,蠴,術,裋,襡,襩,豎,贖,赎,跾,踈,軗,輸,输,述,鄃,鉥,錰,鏣,陎,鮛,鱪,鱰,鵨,鶐,鶑,鸀,黍,鼠,鼡,㒔,㛸,㜐,㟬,㣽,㯮,㳆,㶖,㷂,㻿,㽰,㾁,䃞,䆝,䉀,䎉,䘤,䜹,䝂,䝪,䞖,䠱,䢤,䩱,䩳,䴰|dou:乧,兜,兠,剅,吺,唗,抖,斗,斣,枓,梪,橷,毭,浢,痘,窦,竇,篼,脰,艔,荳,蔸,蚪,豆,逗,郖,酘,鈄,鋀,钭,閗,闘,阧,陡,餖,饾,鬥,鬦,鬪,鬬,鬭,㛒,㞳,㢄,㨮,㪷,㷆,䄈,䕆,䕱,䛠,䬦|nang:乪,儾,嚢,囊,囔,擃,攮,曩,欜,灢,蠰,饢,馕,鬞,齉,㒄,㶞,䂇|kai:乫,凯,凱,剀,剴,勓,嘅,垲,塏,奒,开,忾,恺,愷,愾,慨,揩,暟,楷,欬,炌,炏,烗,蒈,衉,輆,鍇,鎎,鎧,鐦,铠,锎,锴,開,闓,闿,颽,㡁,㲉,䁗,䐩,䒓,䡷|keng:乬,劥,厼,吭,唟,坈,坑,巪,怾,挳,牼,硁,硜,硻,誙,銵,鍞,鏗,铿,䡰|ting:乭,亭,侹,停,厅,厛,听,圢,娗,婷,嵉,庁,庭,廰,廳,廷,挺,桯,梃,楟,榳,汀,涏,渟,濎,烃,烴,烶,珽,町,筳,綎,耓,聤,聴,聼,聽,脡,艇,艈,艼,莛,葶,蜓,蝏,誔,諪,邒,鋌,铤,閮,霆,鞓,頲,颋,鼮,㹶,䋼,䗴,䦐,䱓,䵺|mo:乮,劘,劰,嗼,嚤,嚩,圽,塻,墨,妺,嫫,嫼,寞,尛,帓,帞,怽,懡,抹,摩,摸,摹,擵,昩,暯,末,枺,模,橅,歾,歿,殁,沫,漠,爅,瘼,皌,眜,眽,眿,瞐,瞙,砞,磨,礳,秣,粖,糢,絈,縸,纆,耱,膜,茉,莈,莫,蓦,藦,蘑,蛨,蟔,袹,謨,謩,譕,谟,貃,貊,貘,銆,鏌,镆,陌,靺,饃,饝,馍,驀,髍,魔,魩,魹,麼,麽,麿,默,黙,㱄,㱳,㷬,㷵,㹮,䁼,䁿,䃺,䉑,䏞,䒬,䘃,䜆,䩋,䬴,䮬,䯢,䱅,䳮,䴲|ou:乯,偶,吘,呕,嘔,噢,塸,夞,怄,慪,櫙,欧,歐,殴,毆,毮,沤,漚,熰,瓯,甌,筽,耦,腢,膒,蕅,藕,藲,謳,讴,鏂,鞰,鴎,鷗,鸥,齵,㒖,㛏,㼴,䌂,䌔,䚆,䯚|mai:买,佅,劢,勱,卖,嘪,埋,売,脈,脉,荬,蕒,薶,衇,買,賣,迈,邁,霡,霢,霾,鷶,麥,麦,㜥,㼮,䁲,䈿,䘑,䚑,䜕,䨪,䨫,䮮|luan:乱,亂,卵,圝,圞,奱,孌,孪,孿,峦,巒,挛,攣,曫,栾,欒,滦,灓,灤,癴,癵,羉,脔,臠,虊,釠,銮,鑾,鵉,鸞,鸾,㝈,㡩,㱍,䖂,䜌|cai:乲,倸,偲,埰,婇,寀,彩,戝,才,採,材,棌,猜,睬,綵,縩,纔,菜,蔡,裁,財,财,跴,踩,采,㒲,㥒,䌨,䌽,䐆,䣋,䰂,䴭|ru:乳,侞,儒,入,嗕,嚅,如,媷,嬬,孺,嶿,帤,扖,擩,曘,杁,桇,汝,洳,渪,溽,濡,燸,筎,縟,繻,缛,肗,茹,蒘,蓐,蕠,薷,蠕,袽,褥,襦,辱,込,邚,鄏,醹,銣,铷,顬,颥,鱬,鳰,鴑,鴽,㦺,㨎,㹘,䋈,䰰|xue:乴,吷,坹,学,學,岤,峃,嶨,怴,斈,桖,樰,泧,泶,澩,瀥,烕,燢,狘,疦,疶,穴,膤,艝,茓,蒆,薛,血,袕,觷,謔,谑,趐,踅,轌,辥,雤,雪,靴,鞾,鱈,鳕,鷽,鸴,㖸,㞽,㡜,㧒,㶅,㿱,䎀,䤕,䨮,䫻,䫼,䬂,䭥,䱑|peng:乶,倗,傰,剻,匉,喸,嘭,堋,塜,塳,巼,弸,彭,怦,恲,憉,抨,挷,捧,掽,搒,朋,梈,棚,椖,椪,槰,樥,泙,浌,淎,漨,漰,澎,烹,熢,皏,砰,硑,硼,碰,磞,稝,竼,篷,纄,胓,膨,芃,莑,蓬,蟚,蟛,踫,軯,輣,錋,鑝,閛,闏,韸,韼,駍,騯,髼,鬅,鬔,鵬,鹏,㛔,㥊,㼞,䄘,䡫,䰃,䴶|sha:乷,倽,傻,儍,刹,唦,唼,啥,喢,帹,挱,杀,榝,樧,歃,殺,沙,煞,猀,痧,砂,硰,箑,粆,紗,繌,繺,纱,翜,翣,莎,萐,蔱,裟,鎩,铩,閯,閷,霎,魦,鯊,鯋,鲨,㚫,㛼,㰱,䈉,䝊,䮜,䵘,䶎|na:乸,吶,呐,哪,嗱,妠,娜,拏,拿,挐,捺,笝,納,纳,肭,蒳,衲,袦,誽,豽,貀,軜,那,鈉,鎿,钠,镎,雫,靹,魶,㗙,㨥,㴸,䀑,䅞,䇣,䇱,䈫,䎎,䏧,䖓,䖧,䛔,䟜,䪏,䫱,䱹|qian:乹,乾,仟,仱,伣,佥,俔,倩,偂,傔,僉,儙,凵,刋,前,千,嗛,圱,圲,堑,塹,墘,壍,奷,婜,媊,嬱,孯,岍,岒,嵌,嵰,忴,悓,悭,愆,慊,慳,扦,扲,拑,拪,掔,掮,揵,搴,摼,撁,攐,攑,攓,杄,棈,椠,榩,槏,槧,橬,檶,櫏,欠,欦,歉,歬,汘,汧,浅,淺,潛,潜,濳,灊,牵,牽,皘,竏,签,箝,箞,篏,篟,簽,籖,籤,粁,綪,縴,繾,缱,羬,肷,膁,臤,芊,芡,茜,茾,荨,蒨,蔳,蕁,虔,蚈,蜸,褰,諐,謙,譴,谦,谴,谸,軡,輤,迁,遣,遷,釺,鈆,鈐,鉆,鉗,鉛,銭,錢,鎆,鏲,鐱,鑓,钎,钤,钱,钳,铅,阡,雃,韆,顅,騚,騝,騫,骞,鬜,鬝,鰬,鵮,鹐,黔,黚,㐸,㜞,㟻,㡨,㦮,㧄,㨜,㩮,㯠,㸫,䁮,䈤,䈴,䊴,䍉,䖍,䥅,䦲,䨿,䪈,䫡,䭤|er:乻,二,仒,佴,侕,儿,児,兒,刵,咡,唲,尒,尓,尔,峏,弍,弐,旕,栭,栮,樲,毦,洏,洱,爾,珥,粫,而,耏,耳,聏,胹,荋,薾,衈,袻,誀,貮,貳,贰,趰,輀,轜,迩,邇,鉺,铒,陑,隭,餌,饵,駬,髵,鮞,鲕,鴯,鸸,㒃,㖇,㚷,㛅,㜨,㢽,㧫,㮕,䋙,䋩,䌺,䎟,䎠,䎶,䏪,䣵,䮘|cui:乼,伜,倅,催,凗,啐,啛,墔,崔,嶉,忰,悴,慛,摧,榱,槯,毳,淬,漼,焠,獕,璀,疩,瘁,皠,磪,竁,粹,紣,綷,縗,缞,翆,翠,脃,脆,膬,膵,臎,萃,襊,趡,鏙,顇,㝮,㥞,㧘,㯔,㯜,㱖,㳃,㵏,㷃,㷪,䂱,䃀,䄟,䆊,䊫,䧽|ceng:乽,噌,层,層,岾,嶒,猠,硛,硳,竲,蹭,驓,㣒,㬝,䁬,䉕|gui:亀,佹,刽,刿,劊,劌,匦,匭,厬,圭,垝,妫,姽,媯,嫢,嬀,宄,嶲,巂,帰,庋,庪,归,恑,摫,撌,攰,攱,昋,晷,柜,桂,椝,椢,槶,槻,槼,櫃,櫷,歸,氿,湀,溎,炅,珪,瑰,璝,瓌,癸,皈,瞆,瞡,瞶,硅,祪,禬,窐,筀,簂,簋,胿,膭,茥,蓕,蛫,蟡,袿,襘,規,规,觤,詭,诡,貴,贵,跪,軌,轨,邽,郌,閨,闺,陒,鞼,騩,鬶,鬹,鬼,鮭,鱖,鱥,鲑,鳜,龜,龟,㔳,㙺,㧪,㨳,㩻,㪈,㲹,㸵,䁛,䇈,䌆,䍯,䍷,䐴,䖯,䙆,䝿,䞈,䞨,䠩,䣀,䤥,䯣,䰎,䳏|gan:亁,仠,倝,凎,凲,坩,尲,尴,尶,尷,干,幹,忓,感,擀,攼,敢,旰,杆,柑,桿,榦,橄,檊,汵,泔,淦,漧,澉,灨,玕,甘,疳,皯,盰,矸,秆,稈,竿,笴,筸,簳,粓,紺,绀,肝,芉,苷,衦,詌,贛,赣,赶,趕,迀,酐,骭,魐,鱤,鳡,鳱,㺂,䃭,䇞,䔈,䤗,䯎,䲺,䵟|jue:亅,倔,傕,决,刔,劂,勪,厥,噘,噱,妜,孒,孓,屩,屫,崛,嶡,嶥,彏,憠,憰,戄,抉,挗,捔,掘,撅,撧,攫,斍,桷,橛,橜,欔,欮,殌,氒,決,泬,潏,灍,焆,熦,爑,爝,爴,爵,獗,玃,玦,玨,珏,瑴,瘚,矍,矡,砄,絕,絶,绝,臄,芵,蕝,蕨,虳,蚗,蟨,蟩,覐,覚,覺,觉,觖,觼,訣,譎,诀,谲,貜,赽,趉,趹,蹶,蹷,躩,逫,鈌,鐍,鐝,钁,镢,镼,駃,鴂,鴃,鶌,鷢,龣,㓸,㔃,㔢,㟲,㤜,㩱,㭈,㭾,㰐,㵐,㷾,㸕,㹟,㻕,䀗,䁷,䆕,䆢,䇶,䋉,䍊,䏐,䏣,䐘,䖼,䘿,䙠,䝌,䞵,䞷,䟾,䠇,䡈,䦆,䦼|liao:了,僚,嘹,嫽,寥,寮,尞,尥,尦,屪,嵺,嶚,嶛,廖,廫,憀,憭,撂,撩,敹,料,暸,漻,潦,炓,燎,爎,爒,獠,璙,疗,療,瞭,窷,竂,簝,繚,缭,聊,膋,膫,蓼,蟟,豂,賿,蹘,蹽,辽,遼,鄝,釕,鐐,钌,镣,镽,飉,髎,鷯,鹩,㙩,㝋,㡻,㵳,㶫,㺒,䄦,䉼,䍡,䎆,䑠,䜍,䜮,䝀,䢧,䨅,䩍|ma:亇,傌,吗,唛,嗎,嘛,嘜,妈,媽,嫲,嬤,嬷,杩,榪,溤,犘,犸,獁,玛,瑪,痲,睰,码,碼,礣,祃,禡,罵,蔴,蚂,螞,蟆,蟇,遤,鎷,閁,馬,駡,马,骂,鬕,鰢,鷌,麻,㐷,㑻,㜫,㦄,㨸,㾺,䗫,䣕,䣖,䯦,䳸|zheng:争,佂,凧,埩,塣,姃,媜,峥,崝,崢,帧,幀,征,徰,徴,徵,怔,愸,抍,拯,挣,掙,掟,揁,撜,政,整,晸,正,氶,炡,烝,爭,狰,猙,症,癥,眐,睁,睜,筝,箏,篜,糽,聇,蒸,証,諍,證,证,诤,踭,郑,鄭,鉦,錚,钲,铮,鬇,鴊,㡠,㡧,㱏,㽀,䂻,䈣,䛫,䡕,䥌,䥭,䦛,䦶|chu:亍,俶,傗,储,儊,儲,処,出,刍,初,厨,嘼,埱,处,岀,幮,廚,怵,憷,懨,拀,搋,搐,摴,敊,斶,杵,椘,楚,楮,榋,樗,橱,橻,檚,櫉,櫥,欪,歜,滀,滁,濋,犓,珿,琡,璴,矗,础,礎,禇,竌,竐,篨,絀,绌,耡,臅,芻,蒢,蒭,蕏,處,蜍,蟵,褚,触,觸,諔,豖,豠,貙,趎,踀,蹰,躇,躕,鄐,鉏,鋤,锄,閦,除,雏,雛,鶵,黜,齣,齭,齼,㔘,㕏,㕑,㗰,㙇,㡡,㤕,㤘,㶆,㹼,㼥,䅳,䊰,䎝,䎤,䖏,䙕,䙘,䜴,䟞,䟣,䠂,䠧,䦌,䧁,䮞|kui:亏,傀,刲,匮,匱,卼,喟,喹,嘳,夔,奎,媿,嬇,尯,岿,巋,巙,悝,愦,愧,憒,戣,揆,晆,暌,楏,楑,樻,櫆,欳,殨,溃,潰,煃,盔,睽,磈,窥,窺,篑,簣,籄,聧,聩,聭,聵,葵,蒉,蒊,蕢,藈,蘬,蘷,虁,虧,蝰,謉,跬,蹞,躨,逵,鄈,鍨,鍷,鐀,鑎,闚,頄,頍,頯,顝,餽,饋,馈,馗,騤,骙,魁,㕟,㙓,㚝,㛻,㨒,䈐,䍪,䕚,䕫,䟸,䠑,䤆,䦱,䧶,䫥,䯓,䳫|yun:云,伝,傊,允,勻,匀,呍,喗,囩,夽,奫,妘,孕,恽,惲,愠,愪,慍,抎,抣,昀,晕,暈,枟,橒,殒,殞,氲,氳,沄,涢,溳,澐,煴,熅,熉,熨,狁,玧,畇,眃,磒,秐,筼,篔,紜,緼,縕,縜,繧,纭,缊,耘,耺,腪,芸,荺,蒀,蒕,蒷,蕓,蕰,蕴,薀,藴,蘊,蝹,褞,賱,贇,赟,运,運,郓,郧,鄆,鄖,酝,醖,醞,鈗,鋆,阭,陨,隕,雲,霣,韗,韞,韫,韵,韻,頵,餫,馧,馻,齫,齳,㚃,㚺,㜏,㞌,㟦,䆬,䇖,䉙,䚋,䞫,䡝,䢵,䤞,䦾,䨶,䩵,䪳,䲰,䵴|sui:亗,倠,哸,埣,夊,嬘,岁,嵗,旞,檖,歲,歳,浽,滖,澻,濉,瀡,煫,熣,燧,璲,瓍,眭,睟,睢,砕,碎,祟,禭,穂,穗,穟,粋,綏,繀,繐,繸,绥,脺,膸,芕,荽,荾,葰,虽,襚,誶,譢,谇,賥,遀,遂,邃,鐆,鐩,隋,随,隧,隨,雖,鞖,髄,髓,㒸,㞸,㴚,㵦,㻟,㻪,㻽,䅗,䉌,䍁,䔹,䜔,䠔,䡵,䢫,䥙,䭉,䯝|gen:亘,哏,揯,搄,根,艮,茛,跟,㫔,㮓,䫀|geng:亙,刯,哽,啹,喼,嗰,埂,堩,峺,庚,挭,掶,更,梗,椩,浭,焿,畊,絚,綆,緪,縆,绠,羮,羹,耕,耿,莄,菮,賡,赓,郠,骾,鯁,鲠,鶊,鹒,㾘,䋁,䌄,䱍,䱎,䱭,䱴|xie:些,亵,伳,偕,偰,僁,写,冩,劦,勰,协,協,卨,卸,嗋,噧,垥,塮,夑,奊,娎,媟,寫,屑,屓,屟,屧,屭,峫,嶰,廨,徢,恊,愶,懈,拹,挟,挾,揳,携,撷,擕,擷,攜,斜,旪,暬,械,楔,榍,榭,歇,泄,泻,洩,渫,澥,瀉,瀣,灺,炧,炨,焎,熁,燮,燲,爕,猲,獬,瑎,祄,禼,糏,紲,絏,絜,絬,綊,緤,緳,纈,绁,缬,缷,翓,胁,脅,脇,脋,膎,薢,薤,藛,蝎,蝢,蟹,蠍,蠏,衺,褉,褻,襭,諧,謝,讗,谐,谢,躞,躠,邂,邪,鐷,鞋,鞢,鞵,韰,齂,齘,齥,龤,㒠,㓔,㔎,㕐,㖑,㖿,㙝,㙰,㝍,㞕,㣯,㣰,㥟,㦪,㨙,㨝,㩉,㩦,㩪,㭨,㰔,㰡,㳦,㳿,㴬,㴮,㴽,㸉,㽊,䉏,䉣,䊝,䔑,䕈,䕵,䙊,䙎,䙝,䚳,䚸,䡡,䢡,䥱,䥾,䦏,䦑,䩧,䭎,䲒,䵦|tou:亠,偷,偸,头,妵,婾,媮,投,敨,斢,殕,紏,緰,蘣,透,鍮,頭,骰,黈,㓱,㖣,㡏,㢏,㪗,䞬,䟝,䱏,䵉|wang:亡,亾,仼,兦,妄,尣,尩,尪,尫,彺,往,徃,忘,忹,惘,旺,暀,望,朢,枉,棢,汪,瀇,焹,王,盳,網,网,罔,莣,菵,蚟,蛧,蝄,誷,輞,辋,迋,魍,㑌,㓁,㲿,㳹,㴏,䋄,䋞,䛃,䤑,䰣|kang:亢,伉,匟,囥,嫝,嵻,康,忼,慷,扛,抗,摃,槺,漮,炕,犺,砊,穅,粇,糠,躿,邟,鈧,鏮,钪,閌,闶,鱇,㰠,䡉|da:亣,剳,匒,呾,咑,哒,嗒,噠,垯,墶,大,妲,怛,打,搭,撘,橽,沓,溚,炟,燵,畣,瘩,眔,笪,答,繨,羍,耷,荅,荙,薘,蟽,褡,詚,跶,躂,达,迏,迖,逹,達,鎉,鎝,鐽,靼,鞑,韃,龖,龘,㙮,㜓,㟷,㯚,㾑,㿯,䃮,䐊,䑽,䩢,䳴,䵣|jiao:交,佼,侥,僥,僬,儌,剿,劋,勦,叫,呌,嘂,嘄,嘦,噍,噭,嚼,姣,娇,嬌,嬓,孂,峤,峧,嶕,嶠,嶣,徼,憍,挍,挢,捁,搅,摷,撟,撹,攪,敎,教,敫,敽,敿,斠,晈,暞,曒,椒,櫵,浇,湫,湬,滘,漖,潐,澆,灚,烄,焦,焳,煍,燋,狡,獥,珓,璬,皎,皦,皭,矫,矯,礁,穚,窌,窖,笅,筊,簥,絞,繳,纐,绞,缴,胶,脚,腳,膠,膲,臫,艽,芁,茭,茮,蕉,藠,虠,蛟,蟜,蟭,角,訆,譑,譥,賋,趭,跤,踋,較,轇,轎,轿,较,郊,酵,醮,釂,鉸,鐎,铰,餃,饺,驕,骄,鮫,鱎,鲛,鵁,鵤,鷦,鷮,鹪,㠐,㩰,㬭,㭂,㰾,㳅,㽱,㽲,䀊,䁶,䂃,䆗,䘨,䚩,䠛,䣤,䥞,䪒,䴔,䴛|heng:亨,哼,啈,囍,堼,姮,恆,恒,悙,桁,横,橫,涥,烆,珩,胻,脝,蘅,衡,鑅,鴴,鵆,鸻,㔰,㶇,䄓,䒛,䬖,䬝,䯒|qin:亲,侵,勤,吢,吣,唚,嗪,噙,坅,埁,媇,嫀,寑,寝,寢,寴,嵚,嶔,庈,懃,懄,抋,捦,揿,搇,撳,擒,斳,昑,梫,檎,欽,沁,溱,澿,瀙,珡,琴,琹,瘽,矝,禽,秦,笉,綅,耹,芩,芹,菣,菦,菳,藽,蚙,螓,螼,蠄,衾,親,誛,赺,赾,鈙,鋟,钦,锓,雂,靲,顉,駸,骎,鮼,鳹,㝲,㞬,㢙,㤈,㩒,㪁,㮗,㾛,䈜,䔷,䖌,䠴,䦦|bo:亳,仢,伯,侼,僠,僰,勃,博,卜,啵,嚗,壆,孛,孹,嶓,帛,愽,懪,拨,挬,捕,搏,撥,播,擘,柭,桲,檗,欂,泊,波,浡,淿,渤,湐,煿,牔,犦,犻,狛,猼,玻,瓝,瓟,癶,癷,盋,砵,碆,磻,礡,礴,秡,箔,箥,簙,簸,糪,紴,缽,胉,脖,膊,舶,艊,苩,菠,葧,蔔,蘗,蚾,袚,袯,袰,襏,襮,譒,豰,跛,踣,蹳,郣,鈸,鉑,鉢,鋍,鎛,鑮,钵,钹,铂,镈,餑,餺,饽,馎,馛,馞,駁,駮,驋,驳,髆,髉,鮊,鱍,鲌,鵓,鹁,㖕,㗘,㝿,㟑,㧳,㩧,㩭,㪍,㬍,㬧,㱟,㴾,㶿,㹀,䂍,䊿,䍨,䍸,䑈,䒄,䗚,䙏,䞳,䟛,䢌,䥬,䪇,䪬,䫊,䬪,䭦,䭯,䮀,䮂,䯋,䰊,䶈|lian:亷,僆,劆,匲,匳,嗹,噒,堜,奁,奩,娈,媡,嫾,嬚,帘,廉,怜,恋,慩,憐,戀,摙,敛,斂,梿,楝,槤,櫣,歛,殓,殮,浰,涟,湅,溓,漣,潋,澰,濂,濓,瀲,炼,煉,熑,燫,琏,瑓,璉,磏,稴,簾,籢,籨,練,縺,纞,练,羷,翴,联,聨,聫,聮,聯,脸,臁,臉,莲,萰,蓮,蔹,薕,蘝,蘞,螊,蠊,裢,裣,褳,襝,覝,謰,譧,蹥,连,連,鄻,錬,鍊,鎌,鏈,鐮,链,镰,鬑,鰊,鰱,鲢,㓎,㜃,㜕,㜻,㝺,㟀,㡘,㢘,㥕,㦁,㦑,㪘,㪝,㯬,㰈,㰸,㱨,㶌,㶑,㺦,㼑,㼓,㾾,䁠,䃛,䆂,䇜,䌞,䏈,䙺,䥥,䨬,䭑|duo:亸,仛,凙,刴,剁,剟,剫,咄,哆,哚,喥,嚉,嚲,垛,垜,埵,堕,墮,墯,多,夛,夺,奪,尮,崜,嶞,惰,憜,挅,挆,掇,敓,敚,敠,敪,朵,朶,柮,桗,椯,毲,沲,痥,綞,缍,舵,茤,裰,趓,跢,跥,跺,踱,躱,躲,軃,鈬,鐸,铎,陊,陏,飿,饳,鬌,鮵,鵽,㔍,㖼,㙐,㛆,㛊,㣞,㥩,㧷,㻔,㻧,䅜,䍴,䐾,䑨,䒳,䙃,䙟,䙤,䠤,䤪,䤻,䩔,䩣,䫂,䯬|ren:人,亻,仁,仞,仭,任,刃,刄,壬,妊,姙,屻,忈,忍,忎,恁,扨,朲,杒,栠,栣,梕,棯,牣,秂,秹,稔,紉,紝,絍,綛,纫,纴,肕,腍,芢,荏,荵,葚,衽,袵,訒,認,认,讱,躵,軔,轫,鈓,銋,靭,靱,韌,韧,飪,餁,饪,魜,鵀,㠴,㣼,㶵,㸾,䀼,䇮,䋕,䌾,䏕,䏰,䭃,䴦|ra:亽,囕,罖|ze:仄,伬,则,則,唶,啧,啫,嘖,夨,嫧,崱,帻,幘,庂,択,择,捑,擇,昃,昗,樍,歵,汄,沢,泎,泽,溭,澤,皟,瞔,矠,礋,稄,笮,箦,簀,耫,舴,蔶,蠌,襗,諎,謮,責,賾,责,赜,迮,鸅,齚,齰,㖽,㣱,㳁,㳻,䃎,䇥,䕉,䕪,䰹,䶦|jin:仅,今,伒,侭,僅,僸,儘,兓,凚,劤,劲,勁,卺,厪,噤,嚍,埐,堇,堻,墐,壗,妗,嫤,嬧,寖,尽,嶜,巹,巾,廑,惍,慬,搢,斤,晉,晋,枃,槿,歏,殣,津,浕,浸,溍,漌,濅,濜,烬,煡,燼,珒,琎,琻,瑨,瑾,璡,璶,盡,矜,砛,祲,禁,筋,紟,紧,緊,縉,缙,荕,荩,菫,蓳,藎,衿,襟,覲,觐,觔,謹,谨,賮,贐,赆,近,进,進,金,釒,錦,钅,锦,靳,饉,馑,鹶,黅,齽,㝻,㨷,㬐,㬜,㯲,㯸,㰹,㱈,㴆,㶦,㶳,㹏,䀆,䆮,䋮,䌝,䐶,䑤,䒺,䖐,䗯,䝲,䤐,䥆,䫴,䭙,䶖|pu:仆,僕,匍,噗,圃,圑,圤,埔,墣,巬,巭,扑,抪,撲,擈,攴,攵,普,暜,曝,朴,柨,樸,檏,氆,浦,溥,潽,濮,瀑,炇,烳,璞,痡,瞨,穙,纀,舖,舗,莆,菐,菩,葡,蒱,蒲,諩,譜,谱,贌,蹼,酺,鋪,鏷,鐠,铺,镤,镨,陠,駇,鯆,㒒,㬥,㯷,㲫,㹒,㺪,䈬,䈻,䑑,䔕,䗱,䧤,䲕,䴆|ba:仈,八,叐,叭,吧,哵,坝,坺,垻,墢,壩,夿,妭,岜,巴,弝,扒,把,抜,拔,捌,朳,欛,灞,炦,爸,犮,玐,疤,癹,矲,笆,粑,紦,罢,罷,羓,耙,胈,芭,茇,菝,蚆,覇,詙,豝,跁,跋,軷,釛,釟,鈀,钯,霸,靶,颰,魃,魞,鮁,鲃,鲅,鼥,㔜,㖠,㞎,㧊,㶚,䃻,䆉,䇑,䎬,䟦,䥯,䩗,䩻,䰾,䱝,䳁,䳊|reng:仍,扔,礽,芿,辸,陾,㭁,㺱,䄧,䚮|fo:仏,佛,坲,梻|tao:仐,匋,咷,啕,夲,套,嫍,幍,弢,慆,掏,搯,桃,梼,槄,檮,洮,涛,淘,滔,濤,瑫,畓,祹,絛,綯,縚,縧,绦,绹,萄,蜪,裪,討,詜,謟,讨,迯,逃,醄,鋾,錭,陶,鞀,鞉,鞱,韜,韬,飸,饀,饕,駣,騊,鼗,㚐,㹗,䚯,䚵,䬞,䵚|lun:仑,伦,侖,倫,囵,圇,埨,婨,崘,崙,惀,抡,掄,棆,沦,淪,溣,碖,磮,稐,綸,纶,耣,腀,菕,蜦,論,论,踚,輪,轮,錀,陯,鯩,㖮,㷍,䈁,䑳|cang:仓,仺,伧,倉,傖,凔,匨,嵢,欌,沧,滄,濸,獊,罉,舱,艙,苍,蒼,蔵,藏,螥,賶,鑶,鶬,鸧,㵴,㶓,䅮,䢢|zi:仔,倳,兹,剚,吇,呰,咨,唨,啙,嗞,姉,姊,姕,姿,子,孖,字,孜,孳,孶,崰,嵫,恣,杍,栥,梓,椔,榟,橴,淄,渍,湽,滋,滓,漬,澬,牸,玆,眥,眦,矷,禌,秄,秭,秶,稵,笫,籽,粢,紎,紫,緇,缁,耔,胏,胔,胾,自,芓,茊,茡,茲,葘,虸,觜,訾,訿,諮,谘,貲,資,赀,资,赼,趑,趦,輜,輺,辎,鄑,釨,鈭,錙,鍿,鎡,锱,镃,頾,頿,髭,鯔,鰦,鲻,鶅,鼒,齍,齜,龇,㜽,㧗,㰣,㰷,㱴,㺭,䅆,䐉,䔂,䘣|ta:他,侤,咜,嚃,嚺,塌,塔,墖,她,它,崉,挞,搨,撻,榙,榻,毾,涾,溻,澾,濌,牠,狧,獭,獺,祂,禢,褟,襨,誻,譶,趿,踏,蹋,蹹,躢,遝,遢,鉈,錔,铊,闒,闥,闼,阘,鞜,鞳,鮙,鰨,鳎,㒓,㗳,㛥,㣛,㣵,㧺,㭼,㯓,㳠,㳫,㹺,㺚,㿹,䂿,䈋,䈳,䌈,䍇,䍝,䎓,䑜,䓠,䜚,䵬,䶀,䶁|xian:仙,仚,伭,佡,僊,僩,僲,僴,先,冼,县,咞,咸,哯,唌,啣,嘕,垷,奾,妶,姭,娊,娨,娴,娹,婱,嫌,嫺,嫻,嬐,孅,宪,尟,尠,屳,岘,峴,崄,嶮,幰,廯,弦,忺,憪,憲,憸,挦,掀,搟,撊,撏,攇,攕,显,晛,暹,杴,枮,橌,櫶,毨,氙,涀,涎,湺,澖,瀗,灦,烍,燹,狝,猃,献,獫,獮,獻,玁,现,珗,現,甉,痫,癇,癎,県,睍,硍,礥,祆,禒,秈,筅,箲,籼,粯,糮,絃,絤,綫,線,縣,繊,纎,纖,纤,线,缐,羡,羨,胘,腺,臔,臽,舷,苋,苮,莧,莶,薟,藓,藔,藖,蘚,蚬,蚿,蛝,蜆,衔,衘,褼,襳,誢,誸,諴,譣,豏,賢,贒,贤,赻,跣,跹,蹮,躚,輱,酰,醎,銛,銜,鋧,錎,鍁,鍂,鍌,鏾,鑦,铦,锨,閑,闲,限,陥,险,陷,険,險,霰,韅,韯,韱,顕,顯,餡,馅,馦,鮮,鱻,鲜,鶱,鷳,鷴,鷼,鹇,鹹,麲,鼸,㔵,㘅,㘋,㛾,㜪,㡉,㡾,㢺,㦓,㧋,㧥,㩈,㪇,㫫,㬎,㬗,㭠,㭹,㮭,㯀,㳄,㳭,㵪,㶍,㺌,㿅,䀏,䁂,䃱,䃸,䉯,䉳,䏹,䒸,䕔,䗾,䘆,䚚,䜢,䝨,䞁,䢾,䤼,䥪,䦥,䧋,䧟,䧮,䨘,䨷,䱤,䲗,䵇,䶟,䶢|hong:仜,叿,吰,哄,嗊,嚝,垬,妅,娂,宏,宖,弘,彋,揈,撔,晎,汯,泓,洪,浤,渱,渹,潂,澋,澒,灴,烘,焢,玒,玜,硔,硡,竑,竤,篊,粠,紅,紘,紭,綋,红,纮,翃,翝,耾,苰,荭,葒,葓,蕻,薨,虹,訇,訌,讧,谹,谼,谾,軣,輷,轟,轰,鈜,鉷,銾,鋐,鍧,閎,閧,闀,闂,闳,霐,霟,鞃,鬨,魟,鴻,鸿,黉,黌,㖓,㢬,㬴,㶹,䀧,䂫,䃔,䆖,䉺,䍔,䜫,䞑,䡌,䡏,䧆,䨎,䩑,䪦,䫹,䫺,䲨|tong:仝,佟,僮,勭,同,哃,嗵,囲,峂,庝,彤,恸,慟,憅,捅,晍,曈,朣,桐,桶,樋,橦,氃,浵,潼,炵,烔,熥,犝,狪,獞,痌,痛,眮,瞳,砼,秱,穜,童,筒,筩,粡,絧,統,綂,统,膧,茼,蓪,蚒,衕,赨,通,酮,鉖,鉵,銅,铜,餇,鮦,鲖,㛚,㠉,㠽,㣚,㣠,㤏,㪌,㮔,㸗,㼧,㼿,䂈,䆚,䆹,䮵,䳋,䴀,䶱|dai:代,侢,傣,叇,呆,呔,垈,埭,岱,帒,带,帯,帶,廗,待,怠,懛,戴,曃,柋,歹,殆,汏,瀻,獃,玳,瑇,甙,簤,紿,緿,绐,艜,蚮,袋,襶,貸,贷,蹛,軑,軚,軩,轪,迨,逮,霴,靆,鮘,鴏,黛,黱,㐲,㞭,㫹,㯂,㶡,㻖,㿃,䈆,䒫,䚞,䚟|ling:令,伶,凌,刢,另,呤,囹,坽,夌,姈,婈,孁,岭,岺,崚,嶺,彾,掕,昤,朎,柃,棂,櫺,欞,泠,淩,澪,瀮,灵,炩,燯,爧,狑,玲,琌,瓴,皊,砱,祾,秢,竛,笭,紷,綾,绫,羚,翎,聆,舲,苓,菱,蓤,蔆,蕶,蘦,蛉,衑,袊,裬,詅,跉,軨,輘,酃,醽,鈴,錂,铃,閝,阾,陵,零,霊,霗,霛,霝,靈,領,领,駖,魿,鯪,鲮,鴒,鸰,鹷,麢,齡,齢,龄,龗,㖫,㡵,㥄,㦭,㪮,㬡,㯪,㱥,㲆,㸳,㻏,㾉,䄥,䈊,䉁,䉖,䉹,䌢,䍅,䔖,䕘,䖅,䙥,䚖,䠲,䡼,䡿,䧙,䨩,䯍,䰱,䴇,䴒,䴫|chao:仦,仯,吵,嘲,巐,巢,巣,弨,怊,抄,晁,朝,樔,欩,漅,潮,炒,焯,煼,牊,眧,窲,繛,罺,耖,觘,訬,謿,超,轈,鄛,鈔,钞,麨,鼂,鼌,㶤,㷅,䄻,䎐,䏚,䬤,䰫|chang:仧,伥,倀,倡,偿,僘,償,兏,厂,厰,唱,嘗,嚐,场,場,塲,娼,嫦,尝,常,廠,徜,怅,悵,惝,敞,昌,昶,晿,暢,椙,氅,淐,猖,玚,琩,瑒,瑺,瓺,甞,畅,畼,肠,腸,膓,苌,菖,萇,蟐,裮,誯,鋹,鋿,錩,鏛,锠,長,镸,长,閶,阊,韔,鬯,鯧,鱨,鲳,鲿,鼚,㙊,㦂,㫤,䕋,䗅,䠀,䠆,䩨,䯴|sa:仨,卅,摋,撒,栍,桬,櫒,洒,潵,灑,脎,萨,薩,訯,鈒,钑,隡,靸,颯,飒,馺,㒎,㪪,㳐,㽂,䊛,䘮,䙣,䬃|men:们,們,悶,懑,懣,扪,捫,暪,椚,焖,燜,玣,璊,穈,菛,虋,鍆,钔,門,閅,门,闷,㡈,㥃,㦖,㨺,㱪,㵍,䊟,䝧,䫒|fan:仮,凡,凢,凣,勫,匥,反,噃,墦,奿,嬎,嬏,嬔,帆,幡,忛,憣,払,旙,旛,杋,柉,梵,棥,樊,橎,氾,汎,泛,滼,瀪,瀿,烦,煩,燔,犯,犿,璠,畈,番,盕,矾,礬,笲,笵,範,籓,籵,緐,繁,繙,羳,翻,膰,舤,舧,范,蕃,薠,藩,蘩,蠜,襎,訉,販,贩,蹯,軓,軬,轓,辺,返,釩,鐇,钒,颿,飜,飯,飰,饭,鱕,鷭,㕨,㝃,㠶,㤆,㴀,㶗,㸋,㺕,㼝,㽹,䀀,䀟,䉊,䉒,䊩,䋣,䋦,䌓,䐪,䒠,䒦,䛀,䡊,䣲,䪛,䪤,䫶,䭵,䮳|yang:仰,佒,佯,傟,养,劷,卬,咉,坱,垟,央,奍,姎,岟,崵,崸,徉,怏,恙,慃,懩,扬,抰,揚,攁,敭,旸,昜,暘,杨,柍,样,楊,楧,様,樣,殃,氜,氧,氱,泱,洋,漾,瀁,炀,炴,烊,煬,珜,疡,痒,瘍,癢,眏,眻,礢,禓,秧,紻,羊,羏,羕,羪,胦,蛘,詇,諹,軮,輰,鉠,鍈,鍚,鐊,钖,阦,阳,陽,雵,霷,鞅,颺,飏,養,駚,鰑,鴦,鴹,鸉,鸯,㔦,㟅,㨾,㬕,㺊,㿮,䁑,䇦,䑆,䒋,䖹,䬗,䬬,䬺,䭐,䵮|wo:仴,倭,偓,卧,唩,喔,婐,婑,媉,幄,我,挝,捰,捾,握,撾,斡,楃,沃,涡,涹,渥,渦,濣,焥,猧,瓁,瞃,硪,窝,窩,肟,腛,臒,臥,莴,萵,蜗,蝸,踒,齷,龌,㠛,㦱,㧴,㱧,䁊,䠎,䰀|jian:件,侟,俭,俴,倹,健,僭,儉,兼,冿,减,剑,剣,剪,剱,劍,劎,劒,劔,囏,囝,坚,堅,墹,奸,姦,姧,寋,尖,帴,幵,建,弿,彅,徤,惤,戋,戔,戩,戬,拣,挸,捡,揀,揃,搛,撿,擶,旔,暕,枧,柬,栫,梘,检,検,椷,椾,楗,榗,槛,樫,橺,檢,檻,櫼,歼,殱,殲,毽,洊,涧,渐,減,湔,湕,溅,漸,澗,濺,瀐,瀳,瀸,瀽,煎,熞,熸,牋,牮,犍,猏,玪,珔,瑊,瑐,监,監,睑,睷,瞯,瞷,瞼,硷,碊,碱,磵,礀,礆,礛,笕,笺,筧,简,箋,箭,篯,簡,籛,糋,絸,緘,縑,繝,繭,缄,缣,翦,聻,肩,腱,臶,舰,艦,艰,艱,茧,荐,菅,菺,葌,葏,葥,蒹,蕑,蕳,薦,藆,虃,螹,蠒,袸,裥,襇,襉,襺,見,覵,覸,见,詃,諓,諫,謇,謭,譼,譾,谏,谫,豜,豣,賎,賤,贱,趝,趼,跈,践,踐,踺,蹇,轞,釼,鉴,鋻,鍳,鍵,鏩,鐗,鐧,鑑,鑒,鑬,鑯,鑳,锏,键,閒,間,间,靬,鞬,鞯,韀,韉,餞,餰,饯,馢,鬋,鰎,鰔,鰜,鰹,鲣,鳒,鳽,鵳,鶼,鹣,鹸,鹻,鹼,麉,㓺,㔋,㔓,㣤,㦗,㨴,㨵,㯺,㰄,㳨,㶕,㺝,䄯,䅐,䇟,䉍,䛳,䟅,䟰,䤔,䥜,䧖,䩆,䬻,䭈,䭕,䭠,䮿,䯛,䯡,䵖,䵛,䵡,䵤,䶠|jia:价,佳,假,傢,價,加,叚,唊,嗧,嘉,圿,埉,夹,夾,婽,嫁,宊,家,岬,幏,徦,恝,戛,戞,扴,抸,拁,斚,斝,架,枷,梜,椵,榎,榢,槚,檟,毠,泇,浃,浹,犌,猳,玾,珈,甲,痂,瘕,稼,笳,糘,耞,胛,脥,腵,荚,莢,葭,蛱,蛺,袈,袷,裌,豭,貑,賈,贾,跏,跲,迦,郏,郟,鉀,鉫,鋏,鎵,钾,铗,镓,頬,頰,颊,餄,駕,驾,鴶,鵊,麚,㕅,㪴,㮖,㼪,㿓,䀫,䁍,䑝,䕛,䛟,䩡|yao:仸,倄,偠,傜,吆,咬,喓,嗂,垚,堯,夭,妖,姚,婹,媱,宎,尧,尭,岆,峣,崾,嶢,嶤,幺,徭,徺,愮,抭,揺,搖,摇,摿,暚,曜,曣,杳,枖,柼,楆,榚,榣,殀,殽,溔,烑,熎,燿,爻,狕,猺,獟,珧,瑤,瑶,眑,矅,磘,祅,穾,窅,窈,窑,窔,窯,窰,筄,繇,纅,耀,肴,腰,舀,艞,苭,药,葯,葽,蓔,薬,藥,蘨,袎,要,覞,訞,詏,謠,謡,讑,谣,軺,轺,遙,遥,邀,銚,鎐,鑰,闄,靿,顤,颻,飖,餆,餚,騕,鰩,鳐,鴁,鴢,鷂,鷕,鹞,鼼,齩,㔽,㝔,㞁,㟱,㢓,㨱,㫏,㫐,㮁,㴭,㵸,㿑,㿢,䁏,䁘,䂚,䆙,䆞,䉰,䋂,䋤,䌊,䌛,䍃,䑬,䔄,䖴,䙅,䚺,䚻,䢣,䬙,䴠,䶧|fen:份,偾,僨,分,吩,坆,坋,坟,墳,奋,奮,妢,岎,帉,幩,弅,忿,愤,憤,昐,朆,枌,梤,棻,棼,橨,氛,汾,瀵,炃,焚,燌,燓,玢,瞓,秎,竕,粉,粪,糞,紛,纷,羒,羵,翂,肦,膹,芬,蒶,蕡,蚠,蚡,衯,訜,豮,豶,躮,轒,酚,鈖,鐼,隫,雰,餴,饙,馚,馩,魵,鱝,鲼,鳻,黂,黺,鼖,鼢,㖹,㥹,㮥,㷊,㸮,㿎,䩿,䯨,䴅|di:仾,低,俤,偙,僀,厎,呧,唙,啇,啲,嘀,嚁,地,坔,坻,埊,埞,堤,墑,墬,奃,娣,媂,嫡,嶳,帝,底,廸,弚,弟,弤,彽,怟,慸,抵,拞,掋,摕,敌,敵,旳,杕,枤,柢,梊,梑,棣,氐,涤,滌,滴,焍,牴,狄,玓,甋,眱,睇,砥,碲,磾,祶,禘,笛,第,篴,籴,糴,締,缔,羝,翟,聜,肑,腣,苐,苖,荻,菂,菧,蒂,蔋,蔐,蔕,藡,蝃,螮,袛,覿,觌,觝,詆,諦,诋,谛,豴,趆,踶,軧,迪,递,逓,遞,遰,邸,釱,鉪,鍉,鏑,镝,阺,隄,靮,鞮,頔,馰,骶,髢,魡,鯳,鸐,㡳,㢩,㣙,㦅,㪆,㭽,㰅,㹍,㼵,䀸,䀿,䂡,䊮,䍕,䏑,䑭,䑯,䞶,䟡,䢑,䣌,䧝,䨀,䨤,䩘,䩚,䮤,䯼,䱃,䱱,䴞,䵠,䶍|fang:仿,倣,匚,坊,埅,堏,妨,彷,房,放,方,旊,昉,昘,枋,淓,牥,瓬,眆,紡,纺,肪,舫,芳,蚄,訪,访,趽,邡,鈁,錺,钫,防,髣,魴,鲂,鴋,鶭,㑂,㕫,㤃,㧍,㯐,䢍,䦈,䲱|pei:伂,佩,俖,呸,培,姵,嶏,帔,怌,斾,旆,柸,毰,沛,浿,珮,笩,肧,胚,蓜,衃,裴,裵,賠,赔,轡,辔,配,醅,錇,锫,阫,陪,陫,霈,馷,㟝,㤄,㧩,㫲,㳈,䊃,䣙,䪹,䫠,䲹|diao:伄,凋,刁,刟,叼,吊,奝,屌,弔,弴,彫,扚,掉,殦,汈,琱,瘹,瞗,碉,窎,窵,竨,簓,蓧,藋,虭,蛁,訋,調,调,貂,釣,鈟,銱,鋽,鑃,钓,铞,雕,雿,鮉,鯛,鲷,鳭,鵰,鼦,㒛,㪕,㹿,䂪,䂽,䉆,䔙,䠼,䵲|dun:伅,吨,噸,囤,墩,墪,壿,庉,惇,憞,撉,撴,敦,橔,沌,潡,炖,燉,犜,獤,盹,盾,砘,碷,礅,腞,蜳,趸,踲,蹲,蹾,躉,逇,遁,遯,鈍,钝,頓,顿,驐,㬿,䤜|xin:伈,伩,信,俽,噷,噺,囟,妡,嬜,孞,廞,心,忄,忻,惞,新,昕,杺,枔,欣,歆,潃,炘,焮,盺,脪,舋,芯,莘,薪,衅,訢,訫,軐,辛,邤,釁,鈊,鋅,鐔,鑫,锌,阠,顖,馨,馫,馸,鬵,㐰,㚯,㛛,㭄,䒖,䚱,䛨,䜗,䜣,䰼|ai:伌,僾,凒,叆,哀,哎,唉,啀,嗌,嗳,嘊,噯,埃,塧,壒,娾,嫒,嬡,嵦,愛,懓,懝,挨,捱,敱,敳,昹,暧,曖,欸,毐,溰,溾,濭,爱,瑷,璦,癌,皑,皚,皧,瞹,矮,砹,硋,碍,礙,艾,蔼,薆,藹,譪,譺,賹,躷,鎄,鑀,锿,隘,霭,靄,靉,餲,馤,鱫,鴱,㑸,㕌,㗒,㗨,㘷,㝶,㢊,㤅,㱯,㿄,䀳,䅬,䑂,䔽,䝽,䠹,䨠,䬵,䶣|xiu:休,俢,修,咻,嗅,岫,庥,朽,樇,溴,滫,烋,烌,珛,琇,璓,秀,糔,綇,綉,繍,繡,绣,羞,脙,脩,臹,苬,螑,袖,裦,褎,褏,貅,銝,銹,鎀,鏅,鏥,鏽,锈,飍,饈,馐,髤,髹,鮴,鵂,鸺,齅,㗜,㱙,㾋|nu:伖,伮,傉,努,奴,孥,弩,怒,搙,砮,笯,胬,駑,驽,㚢,䢞|huo:伙,佸,俰,剨,劐,吙,咟,嗀,嚄,嚯,嚿,夥,夻,奯,惑,或,捇,掝,擭,攉,旤,曤,檴,沎,活,湱,漷,濩,瀖,火,獲,癨,眓,矆,矐,祸,禍,秮,秳,穫,耠,耯,臛,艧,获,蒦,藿,蠖,謋,豁,貨,货,邩,鈥,鍃,鑊,钬,锪,镬,閄,雘,霍,靃,騞,㗲,㘞,㦜,㦯,㨯,㯉,㸌,䁨,䂄,䄀,䄆,䄑,䉟,䋭,䣶,䦚,䯏,䰥|hui:会,佪,僡,儶,匯,卉,咴,哕,喙,嘒,噅,噕,噦,嚖,囘,回,囬,圚,婎,媈,孈,寭,屷,幑,廻,廽,彗,彙,彚,徻,徽,恚,恛,恢,恵,悔,惠,慧,憓,懳,拻,挥,揮,撝,晖,晦,暉,暳,會,桧,楎,槥,橞,檅,檓,檜,櫘,毀,毁,毇,汇,泋,洃,洄,浍,湏,滙,潓,澮,瀈,灰,灳,烠,烣,烩,烪,煇,燬,燴,獩,珲,琿,璤,璯,痐,瘣,睳,瞺,禈,秽,穢,篲,絵,繢,繪,绘,缋,翙,翚,翬,翽,芔,茴,荟,蔧,蕙,薈,薉,藱,虺,蚘,蛔,蛕,蜖,螝,蟪,袆,褘,詯,詼,誨,諱,譓,譭,譮,譿,讳,诙,诲,豗,賄,贿,輝,辉,迴,逥,鏸,鐬,闠,阓,隓,隳,靧,韢,頮,顪,餯,鮰,鰴,麾,㑰,㑹,㒑,㜇,㞧,㤬,㥣,㨤,㨹,㩓,㩨,㬩,㰥,㱱,㷄,㷐,㻅,䂕,䃣,䅏,䇻,䌇,䏨,䕇,䙌,䙡,䛛,䛼,䜋,䤧,䧥,䩈,䫭|che:伡,俥,偖,勶,唓,坼,奲,屮,彻,徹,扯,掣,撤,撦,澈,烢,烲,爡,瞮,砗,硨,硩,聅,莗,蛼,車,车,迠,頙,㔭,㥉,㨋,㬚,㯙,㱌,㵃,㵔,㾝,㿭,䁤,䋲,䑲,䒆,䚢,䛸,䜠,䞣,䧪,䨁,䰩|xun:伨,侚,偱,勋,勛,勲,勳,卂,噀,噚,嚑,坃,埙,塤,壎,壦,奞,寻,尋,峋,巡,巽,廵,徇,循,恂,愻,揗,攳,旬,曛,杊,栒,桪,樳,殉,殾,毥,汛,洵,浔,潯,灥,焄,熏,燂,燅,燖,燻,爋,狥,獯,珣,璕,矄,窨,紃,纁,臐,荀,蔒,蕈,薫,薰,蘍,蟳,襑,訊,訓,訙,詢,训,讯,询,賐,迅,迿,逊,遜,鄩,醺,鑂,顨,馴,駨,驯,鱏,鱘,鲟,㜄,㝁,㢲,㨚,㰊,㰬,㽦,䋸,䑕,䖲,䙉,䛜,䞊,䭀|gu:估,傦,僱,凅,古,咕,唂,唃,啒,嘏,固,堌,夃,姑,嫴,孤,尳,峠,崓,崮,怘,愲,扢,故,柧,梏,棝,榖,榾,橭,毂,汩,沽,泒,淈,濲,瀔,焸,牯,牿,痼,皷,盬,瞽,硲,祻,稒,穀,笟,箍,箛,篐,糓,縎,罛,罟,羖,股,脵,臌,菇,菰,蓇,薣,蛄,蛊,蛌,蠱,觚,詁,诂,谷,軱,軲,轂,轱,辜,逧,酤,鈲,鈷,錮,钴,锢,雇,頋,顧,顾,餶,馉,骨,鮕,鯝,鲴,鴣,鵠,鶻,鸪,鹄,鹘,鼓,鼔,㒴,㚉,㧽,㯏,㼋,㽽,㾶,䀇,䀜,䀦,䀰,䅽,䊺,䍍,䍛,䐨,䓢,䜼,䡩,䮩,䵻,䶜|ni:伱,伲,你,倪,儗,儞,匿,坭,埿,堄,妮,婗,嫟,嬺,孴,尼,屔,屰,怩,惄,愵,抳,拟,掜,擬,旎,昵,晲,暱,柅,棿,檷,氼,泥,淣,溺,狔,猊,痆,眤,睨,秜,籾,縌,胒,腻,膩,臡,苨,薿,蚭,蜺,觬,貎,跜,輗,迡,逆,郳,鈮,鉨,鑈,铌,隬,霓,馜,鯢,鲵,麑,齯,㞾,㠜,㣇,㥾,㦐,㪒,㲻,㵫,㹸,䁥,䕥,䘌,䘦,䘽,䛏,䝚,䦵,䧇,䭲,䰯,䵑,䵒|ban:伴,办,半,坂,姅,岅,怑,扮,扳,拌,搬,攽,斑,斒,昄,朌,板,湴,版,班,瓣,瓪,瘢,癍,秚,粄,絆,绊,舨,般,蝂,螁,螌,褩,辦,辬,鈑,鉡,钣,闆,阪,靽,頒,颁,魬,㚘,㩯,㪵,㸞,㺜,䉽,䕰,䬳|xu:伵,侐,俆,偦,冔,勖,勗,卹,叙,呴,喣,嘘,噓,垿,墟,壻,姁,婿,媭,嬃,幁,序,徐,恤,慉,戌,揟,敍,敘,旭,旴,昫,暊,朂,栩,楈,槒,欨,欰,欻,歔,歘,殈,汿,沀,洫,湑,溆,漵,潊,烅,烼,煦,獝,珝,珬,畜,疞,盢,盨,盱,瞁,瞲,砉,稰,稸,窢,糈,絮,続,緒,緖,縃,續,绪,续,聟,胥,蒣,蓄,蓿,蕦,藇,藚,虗,虚,虛,蝑,訏,許,訹,詡,諝,譃,许,诩,谞,賉,鄦,酗,醑,銊,鑐,需,須,頊,须,顼,驉,鬚,魆,魖,鱮,㐨,㑔,㑯,㕛,㖅,㗵,㘧,㚜,㜅,㜿,㞊,㞰,㤢,㥠,㦽,㰲,㵰,㷦,㺷,㾥,䂆,䅡,䋶,䍱,䔓,䘏,䙒,䛙,䜡,䢕,䣱,䣴,䦗,䦽,䬔,䱛,䳳|zhou:伷,侜,僽,冑,周,呪,咒,咮,喌,噣,嚋,妯,婤,宙,州,帚,徟,昼,晝,晭,洀,洲,淍,炿,烐,珘,甃,疛,皱,皺,盩,睭,矪,碡,箒,籀,籒,籕,粙,粥,紂,縐,纣,绉,肘,胄,舟,荮,菷,葤,詋,謅,譸,诌,诪,賙,赒,軸,輈,輖,轴,辀,週,郮,酎,銂,霌,駎,駲,騆,驟,骤,鯞,鵃,鸼,㑇,㑳,㔌,㛩,㥮,㼙,㾭,䇠,䈙,䋓,䎻,䏲,䐍,䖞,䛆,䩜,䶇|shen:伸,侁,侺,兟,呻,哂,堔,妽,姺,娠,婶,嬸,审,宷,審,屾,峷,弞,愼,慎,扟,抌,昚,曋,柛,椮,椹,榊,氠,沈,涁,深,渖,渗,滲,瀋,燊,珅,甚,甡,甧,申,瘆,瘮,眒,眘,瞫,矤,矧,砷,神,祳,穼,籶,籸,紳,绅,罙,罧,肾,胂,脤,腎,葠,蓡,蔘,薓,蜃,裑,覾,訠,訷,詵,諗,讅,诜,谂,谉,身,邥,鉮,鋠,頣,駪,魫,鯓,鯵,鰰,鰺,鲹,鵢,㔤,㜤,㥲,㰂,㰮,㵊,㵕,㾕,䆦,䰠|qu:伹,佉,佢,刞,劬,匤,匷,区,區,厺,去,取,呿,坥,娶,屈,岖,岨,岴,嶇,忂,憈,戵,抾,敺,斪,曲,朐,朑,欋,氍,浀,淭,渠,灈,璖,璩,癯,磲,祛,竘,竬,筁,籧,粬,紶,絇,翑,翵,耝,胊,胠,臞,菃,葋,蕖,蘧,蛆,蛐,蝺,螶,蟝,蠷,蠼,衐,衢,袪,覰,覷,覻,觑,詓,詘,誳,诎,趋,趣,趨,躣,躯,軀,軥,迲,郥,鑺,閴,闃,阒,阹,駆,駈,驅,驱,髷,魼,鰸,鱋,鴝,鸜,鸲,麮,麯,麴,麹,黢,鼁,鼩,齲,龋,㖆,㜹,㠊,㣄,㧁,㫢,㯫,㰦,㲘,䀠,䁦,䂂,䋧,䒧,䝣,䞤,䟊,䠐,䵶,䶚|beng:伻,嘣,埄,埲,塴,奟,崩,嵭,泵,琣,琫,甏,甭,痭,祊,絣,綳,繃,绷,菶,跰,蹦,迸,逬,鏰,镚,閍,鞛,㑟,㱶,㷯,䋽,䙀,䨻,䩬,䭰,䳞|ga:伽,嘎,嘠,噶,尕,尜,尬,旮,玍,釓,錷,钆,魀|dian:佃,傎,典,厧,唸,坫,垫,墊,壂,奌,奠,婝,婰,嵮,巅,巓,巔,店,惦,扂,掂,攧,敁,敟,椣,槙,橂,殿,淀,滇,澱,点,玷,琔,电,甸,瘨,癜,癫,癲,碘,磹,簟,蒧,蕇,蜔,踮,蹎,鈿,钿,阽,電,靛,顚,顛,颠,驔,點,齻,㓠,㚲,㝪,㞟,㥆,㵤,㶘,㸃,㼭,䍄,䓦,䟍,䧃|han:佄,傼,兯,函,凾,厈,含,咁,哻,唅,喊,圅,垾,娢,嫨,寒,屽,崡,嵅,悍,憨,憾,扞,捍,撖,撼,旱,晗,晘,晥,暵,梒,汉,汗,浛,浫,涆,涵,淊,漢,澏,瀚,焊,焓,熯,爳,猂,琀,甝,皔,睅,筨,罕,翰,肣,莟,菡,蔊,蘫,虷,蚶,蛿,蜬,蜭,螒,譀,谽,豃,邗,邯,酣,釬,銲,鋎,鋡,閈,闬,雗,韓,韩,頇,頷,顄,顸,颔,馠,馯,駻,鬫,魽,鶾,鼾,㑵,㒈,㖤,㘎,㘕,㘚,㙈,㙔,㙳,㜦,㟏,㟔,㢨,㨔,㪋,㮀,㲦,㵄,㵎,㶰,㸁,㺖,㼨,㽉,㽳,䁔,䈄,䌍,䍐,䍑,䎯,䏷,䐄,䓍,䓿,䕿,䖔,䗙,䘶,䛞,䤴,䥁,䧲,䨡,䫲,䮧,䶃|bi:佊,佖,俾,偪,匂,匕,吡,哔,啚,嗶,坒,堛,壁,夶,奰,妣,妼,婢,嬖,嬶,屄,币,幣,幤,庇,庳,廦,弊,弻,弼,彃,彼,必,怭,愊,愎,敝,斃,朼,枈,柀,柲,梐,楅,比,毕,毖,毙,毴,沘,湢,滗,滭,潷,濞,煏,熚,狴,獘,獙,珌,璧,畀,畁,畢,疕,疪,痹,痺,皀,皕,碧,禆,秕,稫,笔,筆,筚,箅,箆,篦,篳,粃,粊,綼,縪,繴,罼,聛,胇,腷,臂,舭,苾,荜,荸,萆,萞,蓖,蓽,蔽,薜,蜌,螕,袐,裨,襅,襞,襣,觱,詖,诐,豍,貏,貱,贔,赑,跸,蹕,躃,躄,辟,逼,避,邲,鄙,鄨,鄪,鉍,鎞,鏎,鐴,铋,閇,閉,閟,闭,陛,鞞,鞸,韠,飶,饆,馝,駜,驆,髀,髲,魓,鮅,鰏,鲾,鵖,鷝,鷩,鼊,鼻,㓖,㗉,㘠,㘩,㙄,㚰,㠲,㡀,㡙,㢰,㢶,㢸,㧙,㪏,㪤,㮰,㮿,㯇,㱸,㳼,㵥,㵨,㹃,㻫,㻶,㿫,䀣,䁹,䃾,䄶,䇷,䊧,䋔,䌟,䎵,䏢,䏶,䕗,䖩,䘡,䟆,䟤,䠋,䣥,䦘,䧗,䨆,䩛,䪐,䫁,䫾,䭮,䮡,䯗,䵄|zhao:佋,兆,召,啁,垗,妱,巶,找,招,旐,昭,曌,枛,棹,櫂,沼,炤,照,燳,爫,狣,瑵,盄,瞾,窼,笊,箌,罀,罩,羄,肁,肇,肈,詔,诏,赵,趙,釗,鉊,鍣,钊,駋,鮡,㕚,㡽,㨄,㷖,㺐,䃍,䈃,䈇,䍜,䍮,䝖,䮓|ci:佌,佽,偨,刺,刾,呲,嗭,垐,堲,嬨,庛,慈,朿,柌,栨,次,此,泚,濨,玼,珁,瓷,甆,疵,皉,磁,礠,祠,糍,絘,縒,茈,茦,茨,莿,薋,蛓,螆,蠀,詞,词,賜,赐,趀,跐,辝,辞,辤,辭,鈶,雌,飺,餈,骴,鴜,鶿,鷀,鹚,㓨,㘂,㘹,㞖,㠿,㡹,㢀,㤵,㩞,㹂,䂣,䆅,䈘,䓧,䖪,䗹,䛐,䦻,䧳,䨏,䭣,䯸,䰍,䲿,䳄,䳐|zuo:佐,作,侳,做,咗,唑,坐,岝,岞,左,座,怍,捽,昨,柞,椊,祚,秨,稓,筰,糳,繓,胙,莋,葃,葄,蓙,袏,鈼,阼,飵,㑅,㘀,㘴,㛗,㝾,㭮,㸲,䋏,䎰,䔘,䝫,䞰|ti:体,倜,偍,剃,剔,厗,啼,嗁,嚏,嚔,媞,屉,屜,崹,徲,悌,悐,惕,惖,惿,戻,挮,掦,提,揥,替,梯,楴,歒,殢,洟,涕,渧,漽,珶,瑅,瓋,碮,稊,籊,綈,緹,绨,缇,罤,蕛,薙,蝭,裼,褅,謕,趧,趯,踢,蹄,蹏,躰,軆,逖,逷,遆,醍,銻,鍗,鐟,锑,題,题,騠,骵,體,髰,鬀,鬄,鮷,鯷,鳀,鴺,鵜,鶗,鶙,鷈,鷉,鹈,㖒,㗣,㡗,㣢,㬱,㯩,䅠,䌡,䎮,䔶,䙗,䚣,䛱,䝰,䣡,䣽,䧅,䨑,䪆,䬾,䯜,䴘,䶏,䶑|zhan:佔,偡,占,噡,嫸,展,崭,嶃,嶄,嶘,嶦,惉,战,戦,戰,拃,搌,斩,斬,旃,旜,栈,栴,桟,棧,榐,橏,毡,氈,氊,沾,湛,澶,琖,皽,盏,盞,瞻,站,粘,綻,绽,菚,薝,蘸,虥,虦,蛅,覱,詀,詹,譫,讝,谵,趈,蹍,輚,輾,轏,辗,邅,醆,閚,霑,颭,飐,飦,饘,驏,驙,骣,魙,鱣,鳣,鸇,鹯,黵,㞡,㟞,㠭,㣶,㺘,㻵,䁴,䋎,䎒,䗃,䘺,䟋,䡀,䩅,䪌,䱠,䱼|he:何,佫,劾,合,呵,咊,和,哬,啝,喝,嗃,嗬,垎,壑,姀,寉,峆,惒,抲,敆,曷,柇,核,楁,欱,毼,河,涸,渮,湼,澕,焃,煂,熆,熇,燺,爀,狢,癋,皬,盇,盉,盍,盒,碋,礉,禾,秴,篕,籺,粭,翮,翯,荷,菏,萂,蚵,螛,蠚,袔,褐,覈,訶,訸,詥,诃,貈,貉,賀,贺,赫,郃,鉌,鑉,閡,闔,阂,阖,隺,靍,靎,靏,鞨,頜,颌,饸,魺,鲄,鶡,鶴,鸖,鹖,鹤,麧,齃,齕,龁,龢,㓭,㔠,㕡,㕰,㥺,㦦,㪉,㬞,㭘,㭱,㮝,㮫,㵑,㷎,㷤,㹇,㿣,䃒,䅂,䎋,䒩,䓼,䕣,䚂,䞦,䢗,䪚,䫘,䳚,䳽,䴳,䵱,䶅|she:佘,厍,厙,奢,射,弽,慑,慴,懾,捨,摂,摄,摵,攝,檨,欇,涉,涻,渉,滠,灄,猞,畲,社,舌,舍,舎,蔎,虵,蛇,蛥,蠂,設,设,賒,賖,赊,赦,輋,韘,騇,麝,㒤,㢵,㭙,㰒,㴇,䀅,䁋,䁯,䂠,䄕,䌰,䞌,䠶,䤮,䬷,䵥|gou:佝,冓,勾,坸,垢,够,夠,姤,媾,岣,彀,搆,撀,构,枸,構,沟,溝,煹,狗,玽,笱,篝,簼,緱,缑,耇,耈,耉,苟,茩,蚼,袧,褠,覯,觏,訽,詬,诟,豿,購,购,遘,鈎,鉤,钩,雊,鞲,韝,㗕,㜌,㝅,㝤,㨌,㳶,㺃,䃓,䝭,䞀|ning:佞,侫,儜,凝,咛,嚀,嬣,宁,寍,寕,寗,寜,寧,拧,擰,柠,橣,檸,泞,澝,濘,狞,獰,甯,矃,聍,聹,薴,鑏,鬡,鸋,㝕,㣷,㲰,㿦,䔭,䗿,䭢|yong:佣,俑,傛,傭,勇,勈,咏,喁,嗈,噰,埇,塎,墉,壅,嫞,嵱,庸,廱,彮,怺,恿,悀,惥,愑,愹,慂,慵,拥,擁,柡,栐,槦,永,泳,涌,湧,滽,澭,灉,牅,用,甬,痈,癕,癰,砽,硧,禜,臃,苚,蛹,詠,踊,踴,邕,郺,鄘,醟,銿,鏞,镛,雍,雝,顒,颙,饔,鯒,鰫,鱅,鲬,鳙,鷛,㐯,㙲,㝘,㞲,㦷,㶲,㷏,㽫,䗤,䞻|wa:佤,劸,咓,哇,啘,嗗,嗢,娃,娲,媧,屲,徍,挖,搲,攨,洼,溛,漥,瓦,瓲,畖,砙,窊,窪,聉,腽,膃,蛙,袜,襪,邷,韈,韤,鼃,㧚,㰪,㼘,䎳,䚴,䠚|ka:佧,卡,咔,咖,咯,喀,垰,胩,裃,鉲|bao:佨,保,儤,剝,剥,勹,勽,包,堡,堢,報,媬,嫑,孢,宝,寚,寳,寶,忁,怉,报,抱,暴,曓,煲,爆,珤,窇,笣,緥,胞,苞,菢,萡,葆,蕔,薄,藵,虣,袌,褒,褓,襃,豹,賲,趵,鉋,鑤,铇,闁,雹,靌,靤,飹,飽,饱,駂,骲,髱,鮑,鲍,鳵,鴇,鸨,齙,龅,㙅,㙸,㫧,㲏,㲒,㵡,㻄,㿺,䈏,䎂,䤖,䥤,䨌,䨔,䪨,䭋,䳈,䳰,䴐|lao:佬,僗,劳,労,勞,咾,哰,唠,嗠,嘮,姥,嫪,崂,嶗,恅,憥,憦,捞,撈,朥,栳,橑,橯,浶,涝,澇,烙,牢,狫,珯,痨,癆,硓,磱,窂,簩,粩,老,耂,耢,耮,荖,蛯,蟧,軂,轑,酪,醪,銠,鐒,铑,铹,顟,髝,鮱,㗦,㞠,㟉,㟙,㟹,㧯,㨓,䃕,䇭,䕩,䜎,䝁,䝤,䲏,䳓,䵏|bai:佰,兡,呗,唄,庍,拜,拝,挀,捭,掰,摆,擺,敗,柏,栢,猈,瓸,白,百,稗,竡,粨,粺,絔,薭,襬,贁,败,鞁,韛,㗑,㗗,㠔,㼟,㼣,㿟,䒔,䙓,䢙,䳆,䴽|ming:佲,冥,凕,名,命,姳,嫇,慏,掵,明,暝,朙,榠,洺,溟,猽,眀,眳,瞑,茗,蓂,螟,覭,詺,鄍,酩,銘,铭,鳴,鸣,㝠,㟰,㫥,䄙,䆨,䆩,䊅,䒌,䫤|hen:佷,很,恨,拫,狠,痕,詪,鞎,㯊,䓳|quan:佺,全,券,劝,勧,勸,啳,圈,圏,埢,姾,婘,孉,峑,巏,巻,恮,悛,惓,拳,搼,权,棬,椦,楾,権,權,汱,泉,洤,湶,烇,牶,牷,犈,犬,犭,瑔,甽,畎,痊,硂,筌,絟,綣,縓,绻,腃,荃,葲,虇,蜷,蠸,觠,詮,诠,跧,踡,輇,辁,醛,銓,鐉,铨,闎,韏,顴,颧,駩,騡,鬈,鰁,鳈,齤,㒰,㟨,㟫,䀬,䄐,䊎,䑏,䟒,䠰|tiao:佻,嬥,宨,岧,岹,庣,恌,挑,旫,晀,朓,条,條,樤,眺,祒,祧,窕,窱,笤,粜,糶,絩,聎,脁,芀,蓚,蓨,蜩,螩,覜,誂,趒,跳,迢,鋚,鎥,铫,鞗,頫,髫,鯈,鰷,鲦,齠,龆,㑿,㟘,㸠,䎄,䒒,䖺,䟭,䠷,䩦,䯾,䱔,䳂|xing:侀,倖,兴,刑,哘,型,垶,塂,姓,娙,婞,嬹,幸,形,性,悻,惺,擤,星,曐,杏,洐,涬,煋,狌,猩,瑆,皨,睲,硎,箵,篂,緈,腥,臖,興,荇,莕,蛵,行,裄,觪,觲,謃,邢,郉,醒,鈃,鉶,銒,鋞,钘,铏,陉,陘,騂,骍,鮏,鯹,㐩,㓑,㓝,㝭,㣜,㨘,㮐,㼛,㼬,䁄,䂔,䓷,䛭,䣆,䤯,䰢,䳙|kan:侃,偘,冚,刊,勘,坎,埳,堪,堿,塪,墈,崁,嵁,惂,戡,栞,欿,歁,看,瞰,矙,砍,磡,竷,莰,衎,輡,轁,轗,闞,阚,顑,龕,龛,㸝,䀍,䘓,䶫|lai:來,俫,倈,唻,婡,崃,崍,庲,徕,徠,来,梾,棶,涞,淶,濑,瀨,瀬,猍,琜,癞,癩,睐,睞,筙,箂,籁,籟,莱,萊,藾,襰,賚,賴,赉,赖,逨,郲,錸,铼,頼,顂,騋,鯠,鵣,鶆,麳,㚓,㠣,㥎,㾢,䂾,䄤,䅘,䋱,䓶,䚅,䠭,䧒,䲚|chi:侈,侙,俿,傺,勅,匙,卶,叱,叺,吃,呎,哧,啻,喫,嗤,噄,坘,垑,墀,妛,媸,尺,岻,弛,彨,彲,彳,恜,恥,慗,憏,懘,抶,拸,持,摛,攡,敕,斥,杘,欼,歭,歯,池,泜,淔,湁,漦,灻,炽,烾,熾,瓻,痓,痴,痸,瘛,癡,眵,瞝,竾,笞,筂,篪,粚,糦,絺,翄,翅,翤,翨,耛,耻,肔,胣,胵,腟,茌,荎,蚇,蚩,蚳,螭,袲,袳,裭,褫,訵,誺,謘,豉,貾,赤,赿,趍,趩,跮,踟,迟,迣,遅,遟,遫,遲,鉓,鉹,銐,雴,飭,饎,饬,馳,驰,魑,鴟,鵄,鶒,鷘,鸱,麶,黐,齒,齝,齿,㒆,㓼,㓾,㔑,㘜,㙜,㞴,㞿,㟂,㡿,㢁,㢋,㢮,㮛,㱀,㳏,㶴,㽚,䆍,䇼,䈕,䊼,䐤,䑛,䔟,䗖,䙙,䛂,䜄,䜵,䜻,䞾,䟷,䠠,䤲,䪧,䮈,䮻,䰡,䳵,䶔,䶵|kua:侉,咵,垮,夸,姱,挎,晇,胯,舿,誇,跨,銙,骻,㐄,䋀|guang:侊,俇,僙,光,咣,垙,姯,广,広,廣,撗,桄,欟,洸,灮,炗,炚,炛,烡,犷,獷,珖,硄,胱,臦,臩,茪,輄,逛,銧,黆,㫛|mi:侎,冖,冞,冪,咪,嘧,塓,孊,宓,宻,密,峚,幂,幎,幦,弥,弭,彌,戂,擟,攠,敉,榓,樒,櫁,汨,沕,沵,泌,洣,淧,渳,滵,漞,濔,濗,瀰,灖,熐,爢,猕,獼,瓕,眫,眯,瞇,祕,祢,禰,秘,簚,米,粎,糜,糸,縻,羃,羋,脒,芈,葞,蒾,蔝,蔤,藌,蘼,蜜,蝆,袮,覓,覔,覛,觅,詸,謎,謐,谜,谧,踎,迷,醚,醾,醿,釄,銤,镾,靡,鸍,麊,麋,麛,鼏,㜆,㜷,㝥,㟜,㠧,㣆,㥝,㨠,㩢,㫘,㰽,㳴,㳽,㴵,㵋,㸏,㸓,䁇,䉾,䊳,䋛,䌏,䌐,䌕,䌘,䌩,䍘,䕳,䕷,䖑,䛉,䛑,䛧,䣾,䤉,䤍,䥸,䪾,䭧,䭩,䱊,䴢|an:侒,俺,儑,唵,啽,垵,埯,堓,婩,媕,安,岸,峖,庵,按,揞,晻,暗,案,桉,氨,洝,犴,玵,痷,盦,盫,罯,胺,腤,荌,菴,萻,葊,蓭,誝,諳,谙,豻,貋,銨,錌,铵,闇,隌,雸,鞌,鞍,韽,馣,鮟,鵪,鶕,鹌,黯,㜝,㟁,㱘,㸩,㽢,䁆,䅁,䅖,䎏,䎨,䜙,䬓,䮗,䯥|lu:侓,僇,剹,勎,勠,卢,卤,噜,嚕,嚧,圥,坴,垆,塶,塷,壚,娽,峍,庐,廘,廬,彔,录,戮,挔,捛,掳,摝,撸,擄,擼,攎,枦,栌,椂,樐,樚,橹,櫓,櫨,氇,氌,泸,淕,淥,渌,滷,漉,潞,澛,濾,瀂,瀘,炉,熝,爐,獹,玈,琭,璐,璷,瓐,甪,盝,盧,睩,矑,硉,硵,碌,磠,祿,禄,稑,穋,箓,簏,簬,簵,簶,籙,籚,粶,纑,罏,胪,膔,膟,臚,舮,舻,艣,艪,艫,芦,菉,蓾,蔍,蕗,蘆,虂,虏,虜,螰,蠦,觮,觻,賂,赂,趢,路,踛,蹗,輅,轆,轤,轳,辂,辘,逯,醁,鈩,錄,録,錴,鏀,鏕,鏴,鐪,鑥,鑪,镥,陆,陸,露,顱,颅,騄,騼,髗,魯,魲,鯥,鱸,鲁,鲈,鴼,鵦,鵱,鷺,鸕,鸬,鹭,鹵,鹿,麓,黸,㓐,㔪,㖨,㛬,㜙,㟤,㠠,㢚,㢳,㦇,㪐,㪖,㪭,㫽,㭔,㯝,㯟,㯭,㱺,㼾,㿖,䃙,䌒,䎑,䎼,䐂,䕡,䘵,䚄,䟿,䡎,䡜,䩮,䬛,䮉,䰕,䱚,䲐,䴪|mou:侔,劺,哞,恈,某,桙,洠,牟,眸,瞴,蟱,謀,谋,鉾,鍪,鴾,麰,㭌,䍒,䏬,䗋,䥐,䱕|cha:侘,偛,剎,叉,嗏,垞,奼,姹,察,岔,嵖,差,扠,扱,挿,插,揷,搽,杈,查,査,槎,檫,汊,猹,疀,碴,秅,紁,肞,臿,艖,芆,茬,茶,衩,褨,訍,詧,詫,诧,蹅,釵,銟,鍤,鎈,鑔,钗,锸,镲,靫,餷,馇,㛳,㢉,㢎,㢒,㣾,㤞,㪯,㫅,䁟,䆛,䊬,䑘,䒲,䓭,䕓,䟕,䡨,䤩,䰈,䲦,䶪|gong:供,兝,兣,公,共,功,匑,匔,厷,唝,塨,宫,宮,工,巩,幊,廾,弓,恭,愩,慐,拱,拲,攻,杛,栱,汞,熕,珙,碽,篢,糼,羾,肱,蚣,觥,觵,貢,贑,贡,躬,躳,輁,鞏,髸,龏,龔,龚,㓋,㔶,㤨,㧬,㫒,㭟,㯯,㺬,㼦,䂬,䇨,䡗,䢚|lv:侣,侶,儢,勴,吕,呂,哷,垏,寽,屡,屢,履,嵂,律,慮,旅,曥,梠,榈,櫖,櫚,氀,氯,滤,焒,爈,率,祣,稆,穞,穭,箻,絽,綠,緑,縷,繂,绿,缕,膂,膐,膢,葎,藘,虑,褛,褸,郘,鋁,鑢,铝,閭,闾,馿,驢,驴,鷜,㔧,㠥,㭚,㲶,㻲,㾔,䔞,䢖,䥨|zhen:侦,侲,偵,圳,塦,姫,嫃,寊,屒,帪,弫,抮,挋,振,揕,搸,敒,敶,斟,昣,朕,枕,栕,栚,桢,桭,楨,榛,槇,樼,殝,浈,湞,潧,澵,獉,珍,珎,瑧,甄,畛,疹,眕,眞,真,眹,砧,碪,祯,禎,禛,稹,箴,籈,紖,紾,絼,縝,縥,纼,缜,聄,胗,臻,萙,葴,蒖,蓁,薽,蜄,袗,裖,覙,診,誫,诊,貞,賑,贞,赈,軫,轃,轸,辴,遉,酙,針,鉁,鋴,錱,鍖,鍼,鎭,鎮,针,镇,阵,陣,震,靕,駗,鬒,鱵,鴆,鸩,黮,黰,㐱,㓄,㣀,㪛,㮳,㯢,㴨,䂦,䂧,䊶,䏖,䑐,䝩,䟴,䨯,䪴,䫬,䲴,䳲|ce:侧,側,冊,册,厕,厠,墄,廁,恻,惻,憡,拺,敇,测,測,畟,笧,策,筞,筴,箣,簎,粣,荝,萗,萴,蓛,㥽,㨲,㩍,䇲,䈟,䊂,䔴,䜺|kuai:侩,儈,凷,哙,噲,圦,块,塊,墤,巜,廥,快,擓,旝,狯,獪,筷,糩,脍,膾,蒯,郐,鄶,鱠,鲙,㔞,㙕,㙗,㟴,㧟,㬮,㱮,䈛,䓒,䭝,䯤,䶐|chai:侪,儕,勑,喍,囆,拆,柴,犲,瘥,祡,茝,虿,蠆,袃,豺,㑪,㳗,㾹,䓱,䘍|nong:侬,儂,农,哝,噥,弄,憹,挊,挵,欁,浓,濃,癑,禯,秾,穠,繷,脓,膿,蕽,襛,農,辳,醲,齈,㶶,䁸,䢉,䵜|hou:侯,候,厚,后,吼,吽,喉,垕,堠,帿,後,洉,犼,猴,瘊,睺,矦,篌,糇,翭,葔,豞,逅,郈,鄇,銗,鍭,餱,骺,鮜,鯸,鱟,鲎,鲘,齁,㕈,㖃,㗋,㤧,㫗,㬋,㮢,㸸,㺅,䂉,䗔,䙈,䞧,䪷,䫛,䳧|jiong:侰,僒,冂,冋,冏,囧,坰,埛,扃,泂,浻,澃,炯,烱,煚,煛,熲,燑,燛,窘,絅,綗,蘏,蘔,褧,迥,逈,顈,颎,駉,駫,㑋,㓏,㖥,㢠,㤯,㷗,㷡,䌹,䐃,䢛|nan:侽,南,喃,囡,娚,婻,戁,抩,揇,暔,枏,枬,柟,楠,湳,煵,男,畘,腩,莮,萳,蝻,諵,赧,遖,难,難,㓓,㫱,㽖,䁪,䈒,䔜,䔳,䕼,䛁,䶲|xiao:侾,俲,傚,削,効,呺,咲,哓,哮,啋,啸,嘋,嘐,嘨,嘯,嘵,嚣,嚻,囂,婋,孝,宯,宵,小,崤,庨,彇,恔,恷,憢,揱,撨,效,敩,斅,斆,晓,暁,曉,枭,枵,校,梟,櫹,歊,歗,毊,洨,消,涍,淆,滧,潇,瀟,灱,灲,焇,熽,猇,獢,痚,痟,皛,皢,硝,硣,穘,窙,笑,筱,筿,箫,篠,簘,簫,綃,绡,翛,肖,膮,萧,萷,蕭,藃,虈,虓,蟂,蟏,蟰,蠨,訤,詨,誟,誵,謏,謞,踃,逍,郩,銷,销,霄,驍,骁,髇,髐,魈,鴞,鴵,鷍,鸮,㑾,㔅,㗛,㚣,㤊,㬵,㹲,䊥,䒕,䒝,䕧,䥵|bian:便,匾,卞,变,変,峅,弁,徧,忭,惼,扁,抃,拚,揙,昪,汳,汴,炞,煸,牑,猵,獱,甂,砭,碥,稨,窆,笾,箯,籩,糄,編,緶,缏,编,艑,苄,萹,藊,蝙,褊,覍,變,貶,贬,辡,辧,辨,辩,辪,辫,辮,辯,边,遍,邉,邊,釆,鍽,閞,鞭,頨,鯾,鯿,鳊,鴘,㝸,㣐,㦚,㭓,㲢,㳎,㳒,㴜,㵷,㺹,㻞,䁵,䉸,䒪,䛒,䡢,䪻|tui:俀,僓,娧,尵,推,煺,穨,脮,腿,蓷,藬,蘈,蛻,蜕,褪,蹆,蹪,退,隤,頹,頺,頽,颓,駾,骽,魋,㞂,㢈,㢑,㦌,㱣,㷟,㾯,㾼,㾽,㿉,㿗,䀃,䅪,䍾,䫋|cu:促,噈,媨,徂,憱,殂,猝,瘄,瘯,簇,粗,縬,蔟,觕,誎,趗,踧,蹙,蹴,蹵,酢,醋,顣,麁,麄,麤,鼀,㗤,㰗,䃚,䎌,䓚,䙯,䛤,䟟,䠓,䠞,䢐,䥄,䥘,䬨|e:俄,偔,僫,匎,卾,厄,吪,呃,呝,咢,咹,噁,噩,囮,垩,堊,堮,妸,妿,姶,娥,娿,婀,屙,屵,岋,峉,峨,峩,崿,廅,恶,悪,惡,愕,戹,扼,搤,搹,擜,枙,櫮,歞,歺,涐,湂,珴,琧,皒,睋,砈,砐,砨,硆,磀,腭,苊,莪,萼,蕚,蚅,蛾,蝁,覨,訛,詻,誐,諤,譌,讍,讹,谔,豟,軛,軶,轭,迗,遌,遏,遻,鄂,鈋,鋨,鍔,鑩,锇,锷,閼,阏,阨,阸,頞,頟,額,顎,颚,额,餓,餩,饿,騀,魤,鰐,鱷,鳄,鵈,鵝,鵞,鶚,鹅,鹗,齶,㓵,㔩,㕎,㖾,㗁,㟧,㠋,㡋,㦍,㧖,㩵,㮙,㱦,㷈,㼂,㼢,㼰,䄉,䆓,䑥,䑪,䓊,䔾,䕏,䖸,䙳,䛖,䝈,䞩,䣞,䩹,䫷,䱮,䳗,䳘,䳬|ku:俈,刳,哭,喾,嚳,圐,堀,崫,库,庫,扝,枯,桍,楛,焅,狜,瘔,矻,秙,窟,絝,绔,苦,袴,裤,褲,跍,郀,酷,骷,鮬,㒂,㠸,䇢|jun:俊,儁,军,君,呁,均,埈,姰,寯,峻,懏,捃,攈,晙,桾,汮,浚,濬,焌,燇,珺,畯,皲,皸,皹,碅,竣,筠,箘,箟,莙,菌,蚐,蜠,袀,覠,軍,郡,鈞,銁,銞,鍕,钧,陖,餕,馂,駿,骏,鮶,鲪,鵔,鵕,鵘,麇,麏,麕,㑺,㒞,㓴,㕙,㝦,㴫,㻒,㽙,䇹,䕑,䜭,䝍|zu:俎,傶,卆,卒,哫,崒,崪,族,爼,珇,祖,租,稡,箤,組,组,菹,葅,蒩,詛,謯,诅,足,踤,踿,鎺,鏃,镞,阻,靻,㞺,㰵,㲞,䅸,䔃,䖕,䚝,䯿,䱣|hun:俒,倱,圂,婚,忶,惛,惽,慁,掍,昏,昬,棔,殙,浑,涽,混,渾,溷,焝,睧,睯,繉,荤,葷,觨,諢,诨,轋,閽,阍,餛,馄,魂,鼲,㑮,㥵,㨡,䅙,䅱,䚠,䛰,䧰,䫟,䰟,䴷|su:俗,傃,僳,嗉,嗽,囌,塐,塑,夙,嫊,宿,愫,愬,憟,梀,榡,樎,樕,橚,櫯,殐,泝,洬,涑,溯,溸,潚,潥,玊,珟,璛,甦,碿,稣,穌,窣,簌,粛,粟,素,縤,肃,肅,膆,苏,蔌,藗,蘇,蘓,觫,訴,謖,诉,谡,趚,蹜,速,遡,遬,酥,鋉,餗,驌,骕,鯂,鱐,鷫,鹔,㑉,㑛,㓘,㔄,㕖,㜚,㝛,㨞,㩋,㪩,㬘,㯈,㴋,㴑,㴼,䃤,䅇,䌚,䎘,䏋,䑿,䔎,䘻,䛾,䥔|lia:俩,倆|pai:俳,哌,徘,拍,排,棑,派,湃,牌,犤,猅,磗,箄,簰,蒎,輫,鎃,㭛,㵺,䖰|biao:俵,儦,墂,婊,幖,彪,摽,杓,标,標,檦,淲,滮,瀌,灬,熛,爂,猋,瘭,穮,脿,膘,臕,蔈,藨,表,裱,褾,諘,謤,贆,錶,鏢,鑣,镖,镳,颮,颷,飆,飇,飈,飊,飑,飙,飚,驃,驫,骉,骠,髟,鰾,鳔,麃,㟽,㠒,㧼,㯱,㯹,䔸,䞄|fei:俷,剕,匪,厞,吠,啡,奜,妃,婓,婔,屝,废,廃,廢,悱,扉,斐,昲,暃,曊,朏,杮,棐,榧,櫠,沸,淝,渄,濷,狒,猆,疿,痱,癈,篚,緋,绯,翡,肥,肺,胐,腓,菲,萉,蕜,蕟,蜚,蜰,蟦,裶,誹,诽,費,费,鐨,镄,霏,靅,非,靟,飛,飝,飞,餥,馡,騑,騛,鯡,鲱,鼣,㔗,㥱,㩌,㭭,㵒,䆏,䈈,䉬,䑔,䕁,䕠,䚨,䛍,䠊,䤵,䨽,䨾,䰁|bei:俻,倍,偝,偹,備,僃,北,卑,喺,备,悖,悲,惫,愂,憊,揹,昁,杯,桮,梖,焙,牬,犕,狈,狽,珼,琲,盃,碑,碚,禙,糒,背,苝,蓓,藣,蛽,被,褙,誖,貝,贝,軰,輩,辈,邶,鄁,鉳,鋇,鐾,钡,陂,鞴,骳,鵯,鹎,㔨,㛝,㣁,㤳,㰆,㶔,㷶,㸢,㸬,㸽,㻗,㼎,㾱,䁅,䋳,䔒,䠙,䡶,䩀,䰽|zong:倊,倧,偬,傯,堫,宗,嵏,嵕,嵸,总,惣,惾,愡,捴,揔,搃,摠,昮,朡,棕,椶,熧,燪,猔,猣,疭,瘲,碂,磫,稯,粽,糉,綜,緃,総,緵,縂,縦,縱,總,纵,综,翪,腙,艐,葼,蓗,蝬,豵,踨,踪,蹤,錝,鍯,鏓,鑁,騌,騣,骔,鬃,鬉,鬷,鯮,鯼,㢔,㯶,㷓,㹅,䍟,䝋,䰌|tian:倎,兲,唺,塡,填,天,婖,屇,忝,恬,悿,捵,掭,搷,晪,殄,沺,淟,添,湉,琠,瑱,璳,甛,甜,田,畋,畑,畠,痶,盷,睓,睼,碵,磌,窴,緂,胋,腆,舔,舚,菾,覥,觍,賟,酟,錪,闐,阗,靔,靝,靦,餂,鴫,鷆,鷏,黇,㐁,㖭,㙉,㥏,㧂,㮇,㶺,䄼,䄽,䐌,䑚,䟧,䠄,䡒,䡘,䣯,䥖,䩄|dao:倒,刀,刂,到,叨,噵,壔,宲,导,導,屶,岛,島,嶋,嶌,嶹,忉,悼,捣,捯,搗,擣,朷,椡,槝,檤,氘,焘,燾,瓙,盗,盜,祷,禂,禱,稲,稻,纛,翢,翿,舠,菿,衜,衟,蹈,軇,道,釖,陦,隝,隯,魛,鱽,㠀,㿒,䆃,䌦,䧂,䲽|tan:倓,傝,僋,叹,啴,嗿,嘆,嘽,坍,坛,坦,埮,墰,墵,壇,壜,婒,弾,忐,怹,惔,憛,憳,憻,探,摊,撢,擹,攤,昙,暺,曇,榃,橝,檀,歎,毯,湠,滩,潬,潭,灘,炭,璮,痑,痰,瘫,癱,碳,罈,罎,舑,舕,菼,藫,袒,襢,覃,談,譚,譠,谈,谭,貚,貪,賧,贪,赕,郯,醈,醓,醰,鉭,錟,钽,锬,顃,鷤,㲜,㲭,㷋,㽑,䃪,䆱,䉡,䊤,䏙,䐺,䕊,䜖,䞡,䦔|chui:倕,吹,垂,埀,捶,搥,桘,棰,槌,炊,箠,腄,菙,錘,鎚,锤,陲,顀,龡,㓃,㝽,㥨,㩾,䄲,䍋,䞼,䳠|tang:倘,偒,傏,傥,儻,劏,唐,啺,嘡,坣,堂,塘,嵣,帑,戃,搪,摥,曭,棠,榶,樘,橖,汤,淌,湯,溏,漟,烫,煻,燙,爣,瑭,矘,磄,禟,篖,糃,糖,糛,羰,耥,膅,膛,蓎,薚,蝪,螗,螳,赯,趟,踼,蹚,躺,鄌,醣,鎕,鎲,鏜,鐋,钂,铴,镋,镗,闛,隚,鞺,餳,餹,饄,饧,鶶,鼞,㑽,㒉,㙶,㜍,㭻,㲥,㼺,㿩,䅯,䉎,䌅,䟖,䣘,䧜|kong:倥,埪,孔,崆,恐,悾,控,涳,硿,空,箜,躻,躼,錓,鞚,鵼,㤟,㸜|juan:倦,劵,勌,勬,卷,呟,埍,奆,姢,娟,帣,弮,慻,捐,捲,桊,涓,淃,狷,獧,瓹,眷,睊,睠,絭,絹,绢,罥,羂,脧,臇,菤,蔨,蠲,裐,鄄,鋑,鋗,錈,鎸,鐫,锩,镌,隽,雋,飬,餋,鵑,鹃,㢧,㢾,㪻,㯞,㷷,䄅,䌸,䖭,䚈,䡓,䳪|luo:倮,儸,剆,啰,囉,峈,捋,摞,攞,曪,椤,欏,泺,洛,洜,漯,濼,犖,猡,玀,珞,瘰,癳,砢,笿,箩,籮,絡,纙,络,罗,羅,脶,腡,臝,荦,萝,落,蓏,蘿,螺,蠃,裸,覶,覼,躶,逻,邏,鏍,鑼,锣,镙,雒,頱,饠,駱,騾,驘,骆,骡,鮥,鱳,鵅,鸁,㑩,㒩,㓢,㦬,㩡,㰁,㱻,㴖,㼈,㽋,㿚,䀩,䇔,䈷,䊨,䌱,䌴,䯁|song:倯,傱,凇,娀,宋,崧,嵩,嵷,庺,忪,怂,悚,愯,慫,憽,捒,松,枀,枩,柗,梥,檧,淞,濍,硹,竦,耸,聳,菘,蜙,訟,誦,讼,诵,送,鎹,頌,颂,餸,駷,鬆,㕬,㧐,㨦,㩳,㮸,䉥,䛦,䜬,䢠|leng:倰,冷,堎,塄,愣,棱,楞,睖,碐,稜,薐,踜,䉄,䚏,䬋,䮚|ben:倴,坌,奔,奙,捹,撪,本,桳,楍,泍,渀,犇,獖,畚,笨,苯,賁,贲,輽,逩,錛,锛,㡷,㤓,㨧,㮺,㱵,䬱|zhai:债,債,夈,宅,寨,捚,摘,斋,斎,斏,榸,瘵,砦,窄,粂,鉙,齋,㡯,㩟|qing:倾,傾,儬,凊,剠,勍,卿,圊,埥,夝,庆,庼,廎,情,慶,掅,擎,擏,晴,暒,棾,樈,檠,檾,櫦,殑,殸,氢,氫,氰,淸,清,漀,濪,甠,硘,碃,磬,箐,罄,苘,葝,蜻,請,謦,请,軽,輕,轻,郬,鑋,靑,青,靘,頃,顷,鯖,鲭,黥,㯳,㷫,䋜,䌠,䔛,䝼,䞍,䯧,䲔|ying:偀,僌,啨,営,嘤,噟,嚶,塋,婴,媖,媵,嫈,嬰,嬴,孆,孾,巆,巊,应,廮,影,応,愥,應,摬,撄,攍,攖,攚,映,暎,朠,桜,梬,楹,樱,櫻,櫿,浧,渶,溁,溋,滎,滢,潁,潆,濙,濚,濴,瀅,瀛,瀠,瀯,瀴,灐,灜,煐,熒,營,珱,瑛,瑩,璎,瓔,甇,甖,瘿,癭,盁,盈,矨,硬,碤,礯,穎,籝,籯,緓,縈,纓,绬,缨,罂,罃,罌,膡,膺,英,茔,荥,荧,莹,莺,萤,营,萦,萾,蓥,藀,蘡,蛍,蝇,蝧,蝿,螢,蠅,蠳,褮,覮,謍,譍,譻,賏,贏,赢,軈,迎,郢,鎣,鐛,鑍,锳,霙,鞕,韺,頴,颍,颕,颖,鴬,鶧,鶯,鷪,鷹,鸎,鸚,鹦,鹰,㑞,㢍,㨕,㯋,㲟,㴄,㵬,㶈,㹙,㹚,㿘,䀴,䁐,䁝,䃷,䇾,䑉,䕦,䙬,䤝,䨍,䪯,䭊,䭗|ruan:偄,堧,壖,媆,嫰,愞,撋,朊,瑌,瓀,碝,礝,緛,耎,腝,蝡,軟,輭,软,阮,㼱,㽭,䓴,䞂,䪭|chun:偆,唇,堾,媋,惷,旾,春,暙,杶,椿,槆,橁,櫄,浱,淳,湻,滣,漘,犉,瑃,睶,箺,純,纯,脣,莼,萅,萶,蒓,蓴,蝽,蠢,賰,踳,輴,醇,醕,錞,陙,鯙,鰆,鶉,鶞,鹑,㖺,㝄,㝇,㵮,㸪,㿤,䄝,䏛,䏝,䐇,䐏,䓐,䔚,䞐,䣨,䣩,䥎,䦮,䫃|ruo:偌,叒,婼,嵶,弱,挼,捼,楉,渃,焫,爇,箬,篛,若,蒻,鄀,鰙,鰯,鶸,䐞|pian:偏,囨,媥,楄,楩,片,犏,篇,翩,胼,腁,覑,諚,諞,谝,貵,賆,蹁,駢,騈,騗,騙,骈,骗,骿,魸,鶣,㓲,㛹,㸤,㼐,䏒,䮁|sheng:偗,剩,剰,勝,升,呏,圣,墭,声,嵊,憴,斘,昇,晟,晠,曻,枡,榺,橳,殅,泩,渑,渻,湦,澠,焺,牲,珄,琞,生,甥,盛,省,眚,竔,笙,縄,繩,绳,聖,聲,胜,苼,蕂,譝,貹,賸,鉎,鍟,阩,陞,陹,鱦,鵿,鼪,㗂,㼳,㾪,䁞,䎴,䚇,䞉,䪿,䱆|huang:偟,兤,凰,喤,堭,塃,墴,奛,媓,宺,崲,巟,幌,徨,怳,恍,惶,愰,慌,揘,晃,晄,曂,朚,楻,榥,櫎,湟,滉,潢,炾,煌,熀,熿,獚,瑝,璜,癀,皇,皝,皩,磺,穔,篁,簧,縨,肓,艎,荒,葟,蝗,蟥,衁,詤,諻,謊,谎,趪,遑,鍠,鎤,鐄,锽,隍,韹,餭,騜,鰉,鱑,鳇,鷬,黃,黄,㞷,㤺,㨪,㬻,㾠,㾮,䁜,䅣,䊗,䊣,䌙,䍿,䐠,䐵,䑟,䞹,䪄,䮲,䳨|duan:偳,塅,媏,断,斷,椴,段,毈,煅,瑖,短,碫,端,簖,籪,緞,缎,耑,腶,葮,褍,躖,鍛,鍴,锻,㫁,㱭,䠪|zan:偺,儧,儹,兂,咱,喒,囋,寁,撍,攒,攢,昝,暂,暫,濽,灒,瓉,瓒,瓚,禶,簪,簮,糌,襸,讃,讚,賛,贊,赞,趱,趲,蹔,鄼,酂,酇,錾,鏨,鐕,饡,㜺,㟛,㣅,㤰|lou:偻,僂,喽,嘍,塿,娄,婁,屚,嵝,嶁,廔,慺,搂,摟,楼,樓,溇,漊,漏,熡,甊,瘘,瘺,瘻,瞜,篓,簍,耧,耬,艛,蒌,蔞,蝼,螻,謱,軁,遱,鏤,镂,陋,鞻,髅,髏,㔷,㟺,㥪,㪹,㲎,㺏,䁖,䄛,䅹,䝏,䣚,䫫,䮫,䱾|sou:傁,凁,叜,叟,嗖,嗾,廀,廋,捜,搜,摗,擞,擻,櫢,溲,獀,瘶,瞍,艘,蒐,蓃,薮,藪,螋,鄋,醙,鎪,锼,颼,飕,餿,馊,騪,㖩,㛐,㵻,䈹,䉤,䏂,䮟|yuan:傆,元,冤,剈,原,厡,厵,员,員,噮,囦,园,圆,圎,園,圓,圜,垣,垸,塬,夗,妴,媛,媴,嫄,嬽,寃,弲,怨,悁,惌,愿,掾,援,杬,棩,榞,榬,橼,櫞,沅,淵,渁,渆,渊,渕,湲,源,溒,灁,爰,猨,猿,獂,瑗,盶,眢,禐,笎,箢,緣,縁,缘,羱,肙,苑,葾,蒝,蒬,薗,蚖,蜎,蜵,蝝,蝯,螈,衏,袁,裫,褑,褤,謜,貟,贠,轅,辕,远,逺,遠,邍,邧,酛,鈨,鋺,鎱,院,願,駌,騵,魭,鳶,鴛,鵷,鶢,鶰,鸢,鸳,鹓,黿,鼋,鼘,鼝,㟶,㤪,㥐,㥳,㭇,㹉,䅈,䏍,䖠,䛄,䛇,䩩,䬇,䬧,䬼,䲮,䳒,䳣|rong:傇,冗,媶,嫆,嬫,宂,容,峵,嵘,嵤,嶸,戎,搈,搑,摉,曧,栄,榕,榮,榵,毧,氄,溶,瀜,烿,熔,爃,狨,瑢,穁,穃,絨,縙,绒,羢,肜,茙,茸,荣,蓉,蝾,融,螎,蠑,褣,軵,鎔,镕,駥,髶,㘇,㝐,㣑,㭜,㲓,㲝,㲨,㺎,㼸,䇀,䇯,䈶,䘬,䠜,䡆,䡥,䢇,䤊,䩸|jiang:傋,僵,勥,匞,匠,壃,夅,奖,奨,奬,姜,将,將,嵹,弜,弶,彊,摪,摾,杢,桨,槳,橿,櫤,殭,江,洚,浆,滰,漿,犟,獎,畕,畺,疅,疆,礓,糡,糨,絳,繮,绛,缰,翞,耩,膙,茳,葁,蒋,蔣,薑,螀,螿,袶,講,謽,讲,豇,酱,醤,醬,降,韁,顜,鱂,鳉,㢡,㯍,䁰,䉃,䋌,䒂,䕭,䕯,䙹,䞪|bang:傍,垹,塝,峀,帮,幇,幚,幫,徬,捠,梆,棒,棓,榜,浜,牓,玤,硥,磅,稖,綁,縍,绑,膀,艕,蒡,蚌,蜯,謗,谤,邦,邫,鎊,镑,鞤,㔙,㭋,㮄,㯁,㾦,䂜,䎧,䖫,䟺,䧛,䰷|hao:傐,儫,兞,号,哠,嗥,嘷,噑,嚆,嚎,壕,好,恏,悎,昊,昦,晧,暤,暭,曍,椃,毫,浩,淏,滈,澔,濠,灏,灝,獆,獋,皓,皜,皞,皡,皥,秏,竓,籇,耗,聕,茠,蒿,薃,薅,薧,號,蚝,蠔,譹,豪,郝,顥,颢,鰝,㕺,㘪,㙱,㚪,㝀,㞻,㠙,㩝,㬔,㬶,㵆,䒵,䚽,䝞,䝥,䧫,䪽,䬉,䯫|shan:傓,僐,删,刪,剡,剼,善,嘇,圸,埏,墠,墡,姍,姗,嬗,山,幓,彡,扇,挻,搧,擅,敾,晱,曑,杉,杣,椫,樿,檆,汕,潸,澘,灗,炶,烻,煔,煽,熌,狦,珊,疝,痁,睒,磰,笘,縿,繕,缮,羴,羶,脠,膳,膻,舢,芟,苫,蔪,蟮,蟺,衫,覢,訕,謆,譱,讪,贍,赡,赸,跚,軕,邖,鄯,釤,銏,鐥,钐,閃,閊,闪,陕,陝,饍,騸,骟,鯅,鱓,鱔,鳝,㚒,㣌,㣣,㨛,㪎,㪨,㶒,䄠,䆄,䚲,䠾,䥇,䦂,䦅,䱇,䱉,䴮|suo:傞,唆,唢,嗦,嗩,娑,惢,所,挲,摍,暛,桫,梭,溑,溹,琐,琑,瑣,睃,簑,簔,索,縮,缩,羧,莏,蓑,蜶,趖,逤,鎍,鎖,鎻,鎼,鏁,锁,髿,鮻,㪽,䂹,䅴,䈗,䐝,䖛,䗢,䞆,䞽,䣔,䵀|zai:傤,儎,再,哉,在,宰,崽,扗,栽,洅,渽,溨,災,灾,烖,甾,睵,縡,菑,賳,載,载,酨,㞨,㱰,㴓,䏁,䣬,䮨,䵧|bin:傧,儐,宾,彬,摈,擯,斌,椕,槟,殡,殯,氞,汃,滨,濒,濱,濵,瀕,瑸,璸,砏,繽,缤,膑,臏,虨,蠙,豩,豳,賓,賔,邠,鑌,镔,霦,顮,髌,髕,髩,鬂,鬓,鬢,䐔|nuo:傩,儺,喏,懦,懧,挪,掿,搦,搻,桛,梛,榒,橠,燶,硸,稬,穤,糑,糥,糯,諾,诺,蹃,逽,郍,鍩,锘,黁,㐡,㑚,㔮,㛂,㡅,㰙,䚥|can:傪,儏,参,參,叄,叅,喰,噆,嬠,惨,惭,慘,慙,慚,憯,朁,残,殘,湌,澯,灿,燦,爘,璨,穇,粲,薒,蚕,蝅,蠶,蠺,謲,飡,餐,驂,骖,黪,黲,㘔,㛑,㜗,㣓,㥇,㦧,㨻,㱚,㺑,㻮,㽩,㿊,䅟,䍼,䏼,䑶,䗝,䗞,䘉,䙁,䛹,䝳,䣟,䫮,䬫,䳻|lei:傫,儡,儽,厽,嘞,垒,塁,壘,壨,嫘,擂,攂,樏,檑,櫐,櫑,欙,泪,洡,涙,淚,灅,瓃,畾,癗,矋,磊,磥,礌,礧,礨,禷,类,累,絫,縲,纇,纍,纝,缧,罍,羸,耒,肋,脷,蔂,蕌,蕾,藟,蘱,蘲,蘽,虆,蠝,誄,讄,诔,轠,酹,銇,錑,鐳,鑘,鑸,镭,雷,靁,頛,頪,類,颣,鱩,鸓,鼺,㑍,㒍,㒦,㔣,㙼,㡞,㭩,㲕,㴃,㵢,㶟,㹎,㼍,㿔,䉂,䉓,䉪,䍣,䍥,䐯,䒹,䛶,䢮,䣂,䣦,䨓,䮑,䴎|zao:傮,凿,唕,唣,喿,噪,慥,早,枣,栆,梍,棗,澡,灶,煰,燥,璅,璪,皁,皂,竃,竈,簉,糟,艁,薻,藻,蚤,譟,趮,蹧,躁,造,遭,醩,鑿,㲧,㿷,䜊,䥣,䲃|ao:傲,凹,厫,嗷,嗸,坳,垇,墺,奡,奥,奧,媪,媼,嫯,岙,岰,嶅,嶴,廒,慠,懊,扷,抝,拗,摮,擙,敖,柪,滶,澚,澳,熬,爊,獒,獓,璈,磝,翱,翶,翺,聱,芺,蔜,螯,袄,襖,謷,謸,軪,遨,鏊,鏖,镺,隞,驁,骜,鰲,鳌,鷔,鼇,㑃,㕭,㘬,㘭,㜜,㜩,㟼,㠂,㠗,㤇,㥿,㿰,䁱,䐿,䚫,䜒,䞝,䥝,䦋,䫨,䮯,䯠,䴈,䵅|chuang:傸,刅,创,刱,剏,剙,創,噇,幢,床,怆,愴,摐,漺,牀,牎,牕,疮,瘡,磢,窓,窗,窻,闖,闯,㡖,㵂,䃥,䆫,䇬,䎫,䚒,䡴,䭚|piao:僄,剽,勡,嘌,嫖,彯,徱,慓,旚,殍,漂,犥,瓢,皫,瞟,磦,票,篻,縹,缥,翲,薸,螵,醥,闝,顠,飃,飄,飘,魒,㩠,㬓,㵱,㹾,㺓,㼼,䏇,䴩|man:僈,墁,姏,嫚,屘,幔,悗,慢,慲,摱,曼,槾,樠,満,满,滿,漫,澫,澷,熳,獌,睌,瞒,瞞,矕,縵,缦,蔄,蔓,蘰,蛮,螨,蟃,蟎,蠻,襔,謾,谩,鄤,鏋,鏝,镘,鞔,顢,颟,饅,馒,鬗,鬘,鰻,鳗,㒼,㗄,㗈,㙢,㛧,㡢,㬅,㵘,䅼,䊡,䐽,䑱,䕕,䛲,䜱,䝡,䝢,䟂,䡬,䯶,䰋|zun:僔,噂,尊,嶟,捘,撙,樽,繜,罇,譐,遵,銌,鐏,鱒,鳟,鶎,鷷|deng:僜,凳,噔,墱,嬁,嶝,戥,櫈,灯,燈,璒,登,瞪,磴,竳,等,簦,艠,覴,豋,蹬,邓,鄧,鐙,镫,隥,䃶,䒭,䠬,䮴|tie:僣,呫,帖,怗,聑,萜,蛈,貼,贴,跕,鉄,銕,鐡,鐢,鐵,铁,飻,餮,驖,鴩,䥫,䴴,䵿|seng:僧|min:僶,冧,冺,刡,勄,垊,姄,岷,崏,忞,怋,悯,愍,慜,憫,抿,捪,敃,敏,敯,旻,旼,暋,民,泯,湣,潣,玟,珉,琘,琝,瑉,痻,皿,盿,碈,笢,笽,簢,緍,緡,缗,罠,苠,蠠,賯,鈱,錉,鍲,閔,閩,闵,闽,鰵,鳘,鴖,黽,㞶,㟩,㟭,㢯,㥸,㨉,䁕,䂥,䃉,䋋,䟨,䡅,䡑,䡻,䪸,䲄|sai:僿,嗮,嘥,噻,塞,愢,揌,毢,毸,簺,腮,虄,賽,赛,顋,鰓,鳃,㗷,䈢|dang:儅,党,凼,噹,圵,垱,壋,婸,宕,当,挡,擋,攩,档,檔,欓,氹,潒,澢,灙,珰,璗,璫,瓽,當,盪,瞊,砀,碭,礑,筜,簜,簹,艡,荡,菪,蕩,蘯,蟷,裆,襠,譡,讜,谠,趤,逿,闣,雼,黨,䑗,䣊,䣣,䦒|xuan:儇,吅,咺,喧,塇,媗,嫙,嬛,宣,怰,悬,愃,愋,懸,揎,旋,昍,昡,晅,暄,暅,暶,梋,楥,楦,檈,泫,渲,漩,炫,烜,煊,玄,玹,琁,琄,瑄,璇,璿,痃,癣,癬,眩,眴,睻,矎,碹,禤,箮,絢,縼,繏,绚,翧,翾,萱,萲,蓒,蔙,蕿,藼,蘐,蜁,蝖,蠉,衒,袨,諠,諼,譞,讂,谖,贙,軒,轩,选,選,鉉,鍹,鏇,铉,镟,鞙,颴,駽,鰚,㘣,㧦,㳙,㳬,㹡,㾌,䁢,䍗,䍻,䗠,䘩,䝮,䠣,䧎,䩙,䩰,䮄,䲂,䲻,䴉,䴋|tai:儓,冭,台,囼,坮,太,夳,嬯,孡,忲,态,態,抬,擡,旲,檯,汰,泰,溙,炱,炲,燤,珆,箈,籉,粏,肽,胎,臺,舦,苔,菭,薹,跆,邰,酞,鈦,钛,颱,駘,骀,鮐,鲐,㑷,㒗,㘆,㙵,㣍,㥭,㬃,㷘,㸀,䈚,䑓,䢰,䣭|lan:儖,兰,厱,嚂,囒,壈,壏,婪,嬾,孄,孏,岚,嵐,幱,懒,懢,懶,拦,揽,擥,攔,攬,斓,斕,栏,榄,欄,欖,欗,浨,滥,漤,澜,濫,瀾,灆,灠,灡,烂,燗,燣,爁,爛,爤,爦,璼,瓓,礷,篮,籃,籣,糷,繿,纜,缆,罱,葻,蓝,蓞,藍,蘭,褴,襕,襤,襴,襽,覧,覽,览,譋,讕,谰,躝,醂,鑭,钄,镧,闌,阑,韊,㑣,㘓,㛦,㜮,㞩,㦨,㨫,㩜,㰖,㱫,㳕,䃹,䆾,䊖,䌫,䍀,䑌,䦨,䪍,䰐,䳿|meng:儚,冡,勐,夢,夣,孟,幪,庬,懜,懞,懵,掹,擝,曚,朦,梦,橗,檬,氋,溕,濛,猛,獴,瓾,甍,甿,盟,瞢,矇,矒,礞,罞,艋,艨,莔,萌,萠,蒙,蕄,虻,蜢,蝱,蠓,鄳,鄸,錳,锰,雺,霥,霿,靀,顭,饛,鯍,鯭,鸏,鹲,鼆,㙹,㚞,㜴,㝱,㠓,㩚,䀄,䇇,䉚,䏵,䑃,䑅,䒐,䓝,䗈,䙦,䙩,䠢,䤓,䥂,䥰,䰒,䲛,䴌,䴿,䵆|qiong:儝,卭,宆,惸,憌,桏,橩,焪,焭,煢,熍,琼,璚,瓊,瓗,睘,瞏,穷,穹,窮,竆,笻,筇,舼,茕,藑,藭,蛩,蛬,赹,跫,邛,銎,㒌,㧭,㮪,㷀,㼇,䅃,䆳,䊄,䓖,䛪,䠻|lie:儠,冽,列,劣,劽,咧,埒,埓,姴,峢,巤,挒,挘,捩,擸,毟,洌,浖,烈,烮,煭,犣,猎,猟,獵,睙,聗,脟,茢,蛚,裂,趔,躐,迾,颲,鬛,鬣,鮤,鱲,鴷,㤠,㧜,㬯,㭞,㯿,㲱,㸹,㼲,㽟,䁽,䅀,䉭,䓟,䜲,䟩,䟹,䢪,䴕|kuang:儣,况,劻,匡,匩,哐,圹,壙,夼,岲,恇,懬,懭,抂,旷,昿,曠,框,況,洭,爌,狂,狅,眖,眶,矌,矿,砿,礦,穬,筐,筺,絋,絖,纊,纩,誆,誑,诓,诳,貺,贶,軖,軠,軦,軭,邝,邼,鄺,鉱,鋛,鑛,鵟,黋,㤮,䊯,䵃|chen:儭,嗔,嚫,塵,墋,夦,宸,尘,忱,愖,抻,揨,敐,晨,曟,棽,榇,樄,櫬,沉,烥,煁,琛,疢,瘎,瞋,硶,碜,磣,稱,綝,臣,茞,莀,莐,蔯,薼,螴,衬,襯,訦,諃,諶,謓,讖,谌,谶,賝,贂,趁,趂,趻,踸,軙,辰,迧,郴,鈂,陈,陳,霃,鷐,麎,齓,齔,龀,㕴,㧱,㫳,㲀,㴴,㽸,䆣,䒞,䚘,䜟,䞋,䟢,䢅,䢈,䢻,䣅,䤟,䫖|teng:儯,唞,幐,朰,滕,漛,疼,痋,籐,籘,縢,腾,膯,藤,虅,螣,誊,謄,邆,霯,駦,騰,驣,鰧,鼟,䒅,䕨,䠮,䲍,䲢|long:儱,咙,哢,嚨,垄,垅,壟,壠,屸,嶐,巃,巄,徿,拢,攏,昽,曨,朧,栊,梇,槞,櫳,泷,湰,漋,瀧,爖,珑,瓏,癃,眬,矓,砻,硦,礱,礲,窿,竉,竜,笼,篭,籠,聋,聾,胧,茏,蕯,蘢,蠪,蠬,衖,襱,豅,贚,躘,鏧,鑨,陇,隆,隴,霳,靇,驡,鸗,龍,龒,龓,龙,㑝,㙙,㚅,㛞,㝫,㟖,㡣,㢅,㦕,㰍,㴳,䃧,䏊,䙪,䡁,䥢,䪊|rang:儴,勷,嚷,壌,壤,懹,攘,瀼,爙,獽,瓤,禳,穣,穰,纕,蘘,譲,讓,让,躟,鬤,㚂,䉴|xiong:兄,兇,凶,匈,哅,夐,忷,恟,敻,汹,洶,熊,胷,胸,芎,訩,詗,詾,讻,诇,雄,㐫,䧺|chong:充,冲,嘃,埫,宠,寵,崇,崈,徸,忡,憃,憧,揰,摏,沖,浺,漴,爞,珫,緟,罿,翀,舂,艟,茺,虫,蝩,蟲,衝,褈,蹖,銃,铳,隀,㓽,㧤,㹐,䌬,䖝,䳯|dui:兊,兌,兑,叾,垖,堆,塠,对,対,對,嵟,怼,憝,懟,濧,瀩,痽,碓,磓,祋,綐,薱,譈,譵,鐓,鐜,镦,队,陮,隊,頧,鴭,㙂,㟋,㠚,㬣,㳔,㵽,䇏,䇤,䔪,䨴,䨺,䬈,䬽,䯟|ke:克,刻,剋,勀,勊,匼,可,咳,嗑,坷,堁,壳,娔,客,尅,岢,峇,嵑,嵙,嶱,恪,愙,揢,搕,敤,柯,棵,榼,樖,殻,氪,渇,渴,溘,炣,牁,犐,珂,疴,痾,瞌,碦,磕,礊,礚,科,稞,窠,緙,缂,翗,胢,苛,萪,薖,蝌,課,课,趷,軻,轲,醘,鈳,錁,钶,锞,頦,顆,颏,颗,騍,骒,髁,㕉,㞹,㤩,㪃,㪙,㪡,㪼,㰤,㵣,㾧,䙐,䶗|tu:兎,兔,凃,凸,吐,唋,図,图,圖,圗,土,圡,堍,堗,塗,屠,峹,嵞,嶀,庩,廜,徒,怢,悇,捈,捸,揬,梌,汢,涂,涋,湥,潳,痜,瘏,禿,秃,稌,突,筡,腯,荼,莵,菟,葖,蒤,跿,迌,途,酴,釷,鈯,鋵,鍎,钍,馟,駼,鵌,鵚,鵵,鶟,鷋,鷵,鼵,㭸,㻌,㻠,㻬,㻯,䅷,䖘,䠈,䣄,䣝,䤅,䳜|qiang:兛,呛,唴,嗆,嗴,墏,墙,墻,嫱,嬙,嶈,廧,強,强,戕,戗,戧,抢,搶,摤,斨,枪,椌,槍,樯,檣,溬,漒,炝,熗,牄,牆,猐,獇,玱,琷,瑲,瓩,篬,繈,繦,羌,羗,羟,羥,羫,羻,腔,艢,蔃,蔷,薔,蘠,蜣,襁,謒,跄,蹌,蹡,錆,鎗,鏘,鏹,锖,锵,镪,㛨,㩖,䅚,䵁|nei:內,内,娞,氝,焾,腇,餒,馁,鮾,鯘,㕯,㖏,㘨,㨅,㼏,䡾,䲎,䳖|liu:六,刘,劉,嚠,囖,塯,媹,嬼,嵧,廇,懰,旈,旒,柳,栁,桞,桺,榴,橊,橮,沠,流,浏,溜,澑,瀏,熘,熮,珋,琉,瑠,瑬,璢,瓼,甅,畄,留,畱,疁,瘤,癅,硫,磂,磟,綹,绺,罶,羀,翏,蒥,蓅,藰,蟉,裗,蹓,遛,鋶,鎏,鎦,鏐,鐂,锍,镏,镠,雡,霤,飀,飂,飅,飗,餾,馏,駠,駵,騮,驑,骝,鬸,鰡,鶹,鷚,鹠,鹨,麍,㐬,㙀,㨨,㶯,㽌,㽞,䄂,䉧,䋷,䗜,䚧,䬟,䭷,䰘,䱖,䱞,䶉|pou:兺,咅,哛,哣,堷,婄,抔,抙,捊,掊,犃,箁,裒,颒,㕻,㧵|shou:兽,収,受,售,垨,壽,夀,守,寿,手,扌,授,收,涭,狩,獣,獸,痩,瘦,綬,绶,膄,艏,鏉,首,㖟,㝊,㥅,䛵,䭭|mao:冃,冇,冐,冒,卯,唜,堥,夘,媢,峁,帽,愗,懋,戼,旄,昴,暓,枆,楙,毛,毜,毝,毷,泖,渵,牦,猫,瑁,皃,眊,瞀,矛,笷,緢,耄,芼,茂,茅,茆,蓩,蛑,蝐,蝥,蟊,袤,覒,貌,貓,貿,贸,軞,鄚,鄮,酕,鉚,錨,铆,锚,髦,髳,鶜,㒵,㒻,㚹,㝟,㡌,㧇,㧌,㪞,㫯,㮘,㲠,㴘,㺺,㿞,䀤,䅦,䋃,䓮,䡚,䫉|ran:冄,冉,呥,嘫,姌,媣,染,橪,然,燃,珃,繎,肰,苒,蒅,蚦,蚺,衻,袇,袡,髥,髯,㚩,㜣,㯗,㲯,㸐,㾆,㿵,䎃,䑙,䒣,䖄,䡮,䣸,䤡,䫇|gang:冈,冮,刚,剛,堈,堽,岗,岡,崗,戆,戇,掆,杠,棡,槓,港,焵,牨,犅,疘,矼,筻,綱,纲,缸,罁,罓,罡,肛,釭,鋼,鎠,钢,阬,㟠,㟵,㽘,䴚|gua:冎,刮,剐,剮,劀,卦,叧,呱,啩,坬,寡,挂,掛,歄,焻,煱,瓜,絓,緺,罣,罫,胍,苽,褂,詿,诖,趏,銽,颪,颳,騧,鴰,鸹,㒷,䈑|kou:冦,剾,劶,口,叩,宼,寇,廤,彄,怐,扣,抠,摳,敂,滱,眍,瞉,瞘,窛,筘,簆,芤,蔲,蔻,釦,鷇,㓂,㔚,㰯,㲄,㽛,䳟,䳹|pan:冸,判,叛,坢,媻,幋,搫,攀,柈,槃,沜,泮,溿,潘,瀊,炍,爿,牉,畔,畨,盘,盤,盼,眅,磐,縏,蒰,蟠,袢,襻,詊,跘,蹒,蹣,鋬,鎜,鑻,鞶,頖,鵥,㐴,㳪,䃑,䃲,䈲,䰉,䰔|qia:冾,圶,帢,恰,愘,拤,掐,殎,洽,硈,葜,跒,酠,鞐,髂,㓣,㡊,㤉,䜑,䠍,䨐,䯊,䶝|mei:凂,呅,嚜,堳,塺,妹,媄,媒,媚,媺,嬍,寐,嵄,嵋,徾,抺,挴,攗,攟,昧,枚,栂,梅,楣,楳,槑,毎,每,沒,没,沬,浼,渼,湄,湈,煝,煤,燘,猸,玫,珻,瑂,痗,眉,眛,睂,睸,矀,祙,禖,篃,美,脄,脢,腜,苺,莓,葿,蘪,蝞,袂,跊,躾,郿,酶,鋂,鎂,鎇,镁,镅,霉,韎,鬽,魅,鶥,鹛,黣,黴,㭑,㶬,㺳,䀛,䆀,䉋,䊈,䊊,䍙,䒽,䓺,䜸,䤂,䰨,䰪,䵢|zhun:准,凖,埻,宒,準,稕,窀,綧,肫,衠,訰,諄,谆,迍|cou:凑,楱,湊,腠,輳,辏,㫶|du:凟,剢,匵,厾,嘟,堵,妒,妬,嬻,帾,度,杜,椟,櫝,殬,殰,毒,涜,渎,渡,瀆,牍,牘,犊,犢,独,獨,琽,瓄,皾,督,睹,秺,笃,篤,肚,芏,荰,蝳,螙,蠧,蠹,裻,覩,読,讀,讟,读,豄,賭,贕,赌,都,醏,錖,鍍,鑟,镀,闍,阇,靯,韇,韣,韥,騳,髑,黩,黷,㱩,㸿,㾄,䀾,䄍,䅊,䈞,䐗,䓯,䙱,䟻,䢱,䪅,䫳,䮷,䲧|cun:刌,吋,墫,存,寸,忖,拵,村,澊,皴,竴,籿,踆,邨,䍎|wen:刎,吻,呚,呡,問,塭,妏,彣,忟,抆,揾,搵,文,桽,榅,榲,殟,汶,渂,温,溫,炆,珳,瑥,璺,瘒,瘟,砇,稳,穏,穩,紊,紋,絻,纹,聞,肳,脕,脗,芠,莬,蚉,蚊,螡,蟁,豱,輼,轀,辒,鈫,鎾,閺,閿,闅,闦,闧,问,闻,阌,雯,顐,饂,馼,魰,鰛,鰮,鳁,鳼,鴍,鼤,㒚,㖧,㗃,㝧,㳷,䎹,䎽,䘇,䰚|hua:划,劃,化,华,哗,嘩,埖,姡,婲,婳,嫿,嬅,崋,摦,撶,杹,桦,椛,槬,樺,滑,澅,猾,璍,画,畫,畵,硴,磆,糀,繣,舙,花,芲,華,蕐,蘤,蘳,螖,觟,話,誮,諙,諣,譁,话,鋘,錵,鏵,铧,驊,骅,鷨,黊,㓰,㕦,㕲,㕷,㚌,㟆,㠏,㠢,㦊,㦎,㩇,㭉,㮯,䅿,䏦,䔢,䛡,䠉,䱻,䶤|yue:刖,嬳,岄,岳,嶽,彟,彠,恱,悅,悦,戉,抈,捳,曰,曱,月,枂,樾,汋,瀹,爚,玥,矱,礿,禴,箹,篗,籆,籥,籰,粤,粵,約,约,蘥,蚎,蚏,越,跀,跃,躍,軏,鈅,鉞,钥,钺,閱,閲,阅,鸑,鸙,黦,龠,龥,㜧,㜰,㬦,㰛,㹊,䋐,䖃,䟠,䠯,䡇,䢁,䢲,䤦,䥃,䶳|bie:別,别,咇,彆,徶,憋,瘪,癟,莂,虌,蛂,蟞,襒,蹩,鱉,鳖,鼈,龞,㢼,㿜,䉲,䋢,䏟,䠥,䭱|pao:刨,匏,咆,垉,奅,庖,抛,拋,泡,炮,炰,爮,狍,疱,皰,砲,礟,礮,脬,萢,蚫,袍,褜,跑,軳,鞄,麅,麭,㘐,㚿,㯡,䛌,䩝,䶌|shua:刷,唰,耍,誜|cuo:剉,剒,厝,夎,嵯,嵳,挫,措,搓,撮,棤,瑳,痤,睉,矬,磋,脞,莝,莡,蒫,蓌,蔖,虘,蹉,逪,遳,醝,銼,錯,锉,错,髊,鹺,鹾,齹,㟇,㽨,䂳,䐣,䟶,䠡,䣜,䱜,䴾|la:剌,啦,喇,嚹,垃,拉,揦,揧,搚,攋,旯,柆,楋,櫴,溂,爉,瓎,瘌,砬,磖,翋,腊,臈,臘,菈,藞,蜡,蝋,蝲,蠟,辢,辣,邋,鑞,镴,鞡,鬎,鯻,㕇,㸊,㻋,㻝,䂰,䃳,䏀,䓥,䗶,䝓,䟑,䪉,䱫,䶛|po:剖,叵,哱,嘙,坡,奤,娝,婆,尀,岥,岶,廹,敀,昢,櫇,泼,洦,溌,潑,烞,珀,皤,破,砶,笸,粕,蒪,蔢,謈,迫,鄱,酦,醱,釙,鉕,鏺,钋,钷,頗,颇,駊,魄,㛘,㨇,㰴,䄸,䎊,䞟,䣪,䣮,䨰,䪖,䯙|tuan:剬,剸,团,団,圕,團,塼,彖,慱,抟,摶,槫,檲,湍,湪,漙,煓,猯,疃,篿,糰,褖,貒,鏄,鷒,鷻,㩛,䊜,䜝,䵯|zuan:劗,揝,攥,籫,繤,纂,纉,纘,缵,躜,躦,鑚,鑽,钻,䂎,䌣,䎱,䤸|shao:劭,勺,卲,哨,娋,少,弰,捎,旓,柖,梢,潲,烧,焼,焽,燒,玿,稍,筲,紹,綤,绍,艄,芍,苕,莦,萔,蕱,蛸,袑,輎,邵,韶,颵,髾,鮹,㪢,㲈,㷹,㸛,䏴,䒚,䔠,䙼,䬰|gao:勂,吿,告,夰,峼,搞,暠,杲,槀,槁,槔,槹,橰,檺,櫜,滜,獔,皋,皐,睪,睾,祮,祰,禞,稁,稾,稿,筶,篙,糕,縞,缟,羔,羙,膏,臯,菒,藁,藳,誥,诰,郜,鋯,鎬,锆,镐,韟,餻,高,髙,鷎,鷱,鼛,㚏,㚖,㾸,䗣|lang:勆,唥,啷,埌,塱,嫏,崀,廊,悢,朖,朗,朤,桹,榔,樃,欴,浪,烺,狼,琅,瑯,硠,稂,筤,艆,莨,蒗,蓈,蓢,蜋,螂,誏,躴,郎,郒,郞,鋃,鎯,锒,閬,阆,駺,㓪,㙟,㝗,㟍,㢃,㫰,㮾,㱢,㾗,㾿,䀶,䁁,䆡,䍚,䕞,䡙,䯖,䱶|weng:勜,嗡,塕,奣,嵡,暡,滃,瓮,甕,瞈,罋,翁,聬,蓊,蕹,螉,鎓,鶲,鹟,齆,㘢,㜲,䐥,䤰|mang:匁,厖,吂,哤,壾,娏,尨,忙,恾,杗,杧,氓,汒,浝,漭,牤,牻,狵,痝,盲,硭,笀,芒,茫,茻,莽,莾,蘉,蛖,蟒,蠎,邙,釯,鋩,铓,駹,㙁,㝑,㟌,㟐,㟿,㡛,㬒,㻊,䀮,䁳,䅒,䈍,䒎,䖟,䟥,䵨|nao:匘,呶,垴,堖,夒,婥,嫐,孬,峱,嶩,巎,怓,恼,悩,惱,挠,撓,檂,淖,猱,獶,獿,瑙,硇,碙,碯,脑,脳,腦,臑,蛲,蟯,詉,譊,鐃,铙,閙,闹,鬧,㑎,㛴,㞪,㺀,㺁,䃩,䄩,䑋,䛝,䜀,䜧,䫸,䴃|za:匝,咂,囐,帀,拶,杂,桚,沞,沯,砸,磼,紥,紮,臜,臢,襍,鉔,雑,雜,雥,韴,魳,䕹,䞙,䪞|suan:匴,狻,痠,祘,笇,筭,算,蒜,酸,㔯|nian:卄,哖,埝,姩,年,廿,念,拈,捻,撚,撵,攆,涊,淰,碾,秊,秥,簐,艌,蔫,蹨,躎,輦,辇,鮎,鯰,鲇,鲶,鵇,黏,㘝,㞋,㲽,䄭,䄹,䚓,䩞,䬯|shuai:卛,帅,帥,摔,甩,蟀,衰,䢦|que:却,卻,埆,塙,墧,崅,悫,愨,慤,搉,榷,毃,炔,燩,瘸,皵,硞,确,碏,確,礐,礭,缺,舃,蒛,趞,闋,闕,阕,阙,雀,鵲,鹊,㕁,㩁,㰌,㱋,㱿,㴶,㾡,䇎,䦬,䧿|zhe:厇,哲,啠,喆,嗻,嚞,埑,嫬,悊,折,摺,晢,晣,柘,棏,樀,樜,歽,浙,淛,矺,砓,磔,籷,粍,者,蔗,虴,蛰,蜇,蟄,蟅,袩,褶,襵,詟,謫,謺,讁,讋,谪,赭,輒,輙,轍,辄,辙,这,這,遮,銸,鍺,锗,鮿,鷓,鹧,㞏,㪿,㯰,䂞,䊞,䎲,䏳,䐑,䐲,䓆,䗪,䝃,䝕,䠦,䩾,䵭|a:厑,吖,啊,嗄,錒,锕,阿|zui:厜,嗺,嘴,噿,嶊,嶵,晬,最,朘,枠,栬,樶,檇,檌,欈,濢,璻,祽,穝,絊,纗,罪,蕞,蟕,辠,酔,酻,醉,鋷,錊,㝡,㠑,㰎,䘹,䮔|rou:厹,媃,宍,揉,柔,楺,渘,煣,瑈,瓇,禸,粈,糅,肉,腬,葇,蝚,蹂,輮,鍒,鞣,韖,騥,鰇,鶔,㖻,㽥,䄾,䐓,䧷,䰆|shuang:双,塽,孀,孇,慡,樉,欆,滝,灀,爽,礵,縔,艭,鏯,雙,霜,騻,驦,骦,鷞,鸘,鹴,㦼,㼽,䗮,䡯,䫪|die:叠,哋,啑,喋,嚸,垤,堞,峌,嵽,幉,恎,惵,戜,挕,揲,昳,曡,曢,殜,氎,爹,牃,牒,瓞,畳,疂,疉,疊,眣,眰,碟,絰,绖,耊,耋,胅,臷,艓,苵,蜨,蝶,褋,褺,詄,諜,谍,趃,跌,蹀,迭,镻,鰈,鲽,㑙,㥈,㦶,㩸,㩹,㫼,㬪,㭯,㲲,㲳,㷸,㻡,䘭,䞇,䞕,䠟,䪥,䮢,䲀,䳀,䴑|rui:叡,壡,枘,桵,橤,汭,瑞,甤,睿,緌,繠,芮,蕊,蕋,蕤,蘂,蘃,蚋,蜹,銳,鋭,锐,㓹,㛱,㪫,㮃,㲊,䅑,䌼,䓲|tun:吞,呑,啍,坉,屯,忳,旽,暾,朜,氽,涒,焞,畽,臀,臋,芚,豘,豚,軘,霕,飩,饨,魨,鲀,黗,㖔,㞘,㩔,㹠,㼊|fou:否,垺,妚,紑,缶,缹,缻,雬,鴀,䳕|shun:吮,瞬,舜,顺,㥧,䀢,䀵,䑞|guo:呙,咼,啯,嘓,囯,囶,囻,国,圀,國,埚,堝,墎,崞,帼,幗,彉,彍,惈,慖,掴,摑,果,椁,楇,槨,淉,漍,濄,猓,瘑,粿,綶,聒,聝,腂,腘,膕,菓,蔮,虢,蜾,蝈,蟈,裹,褁,輠,过,過,郭,鈛,鍋,鐹,锅,餜,馃,馘,㕵,㖪,㚍,㞅,㳀,㶁,䂸,䆐,䐸,䙨,䤋,䬎,䴹|pen:呠,喯,喷,噴,歕,湓,濆,瓫,盆,翉,翸,葐|ne:呢,抐,疒,眲,訥,讷,䎪,䭆|m:呣,嘸|huai:咶,坏,壊,壞,徊,怀,懐,懷,槐,櫰,淮,瀤,耲,蘹,蘾,褢,褱,踝,㜳,䈭,䴜|pin:品,嚬,姘,娉,嫔,嬪,拼,朩,榀,汖,牝,玭,琕,矉,礗,穦,聘,薲,貧,贫,頻,顰,频,颦,馪,驞,㰋,䀻|yo:哟,唷,喲|o:哦|shui:哾,帨,楯,橓,水,氵,氺,涗,涚,睡,瞚,瞤,祱,稅,税,脽,蕣,裞,說,説,誰,谁,閖,順,鬊,㽷,䭨|huan:唤,喚,喛,嚾,堚,奂,奐,宦,寏,寰,峘,嵈,幻,患,愌,懁,懽,换,換,擐,攌,桓,梙,槵,欢,欥,歓,歡,洹,浣,涣,渙,漶,澣,澴,烉,焕,煥,狟,獾,环,瑍,環,瓛,痪,瘓,睆,瞣,糫,絙,綄,緩,繯,缓,缳,羦,肒,荁,萈,萑,藧,讙,豢,豲,貆,貛,輐,轘,还,逭,還,郇,酄,鍰,鐶,锾,镮,闤,阛,雈,驩,鬟,鯇,鯶,鰀,鲩,鴅,鵍,鹮,㓉,㕕,㡲,㣪,㦥,㪱,㬇,㬊,㵹,㶎,㹖,㼫,㿪,䀓,䀨,䆠,䈠,䍺,䝠,䥧,䦡,䭴,䮝,䯘,䴟|nou:啂,槈,檽,獳,羺,耨,譨,譳,鎒,鐞,㝹,䅶,䘫,䨲,䰭|ken:啃,垦,墾,恳,懇,掯,肎,肯,肻,裉,褃,豤,貇,錹,㸧|chuai:啜,嘬,揣,膗,踹,㪓,㪜,䦟,䦤,䦷|pa:啪,妑,帊,帕,怕,掱,杷,潖,爬,琶,皅,筢,舥,葩,袙,趴,䯲,䶕|se:啬,嗇,懎,擌,栜,槮,歮,歰,洓,涩,渋,澀,澁,濇,濏,瀒,琗,瑟,璱,瘷,穑,穡,穯,篸,縇,繬,聓,色,裇,襂,譅,轖,銫,鏼,铯,閪,雭,飋,鬙,㒊,㥶,㮦,㱇,㴔,㻭,䉢,䔼,䨛|nie:啮,喦,嗫,噛,嚙,囁,囓,圼,孼,孽,嵲,嶭,巕,帇,惗,捏,揑,摰,敜,枿,槷,櫱,涅,篞,籋,糱,糵,聂,聶,肀,臬,臲,苶,菍,蘖,蠥,讘,踂,踗,踙,蹑,躡,鉩,錜,鎳,鑷,钀,镊,镍,闑,陧,隉,顳,颞,齧,㖖,㘿,㙞,㚔,㜸,㡪,㩶,㮆,㴪,㸎,䂼,䄒,䌜,䜓,䯀,䯅,䯵|n:啱,嗯,莻,鈪,銰,㐻|wai:喎,外,崴,歪,竵,顡,㖞,䠿|miao:喵,妙,媌,嫹,庙,庿,廟,描,杪,淼,渺,玅,眇,瞄,秒,竗,篎,緲,缈,苗,藐,邈,鱙,鶓,鹋,㑤,㠺,㦝,䁧,䅺,䖢|shuo:嗍,妁,搠,朔,槊,欶,烁,爍,獡,矟,硕,碩,箾,蒴,说,鎙,鑠,铄,䀥,䈾,䌃|dia:嗲|cao:嘈,嶆,愺,懆,撡,操,曹,曺,槽,漕,糙,肏,艚,艸,艹,草,蓸,螬,褿,襙,鄵,鏪,騲,鼜,㜖,㯥,䄚,䏆,䐬,䒃,䒑|de:嘚,得,徳,德,恴,悳,惪,淂,的,鍀,锝,㝵,㤫,㥀,㥁,㯖,䙷,䙸|hei:嘿,嬒,潶,黑,黒|kuo:噋,廓,懖,扩,拡,括,挄,擴,栝,桰,濶,穒,筈,萿,葀,蛞,闊,阔,霩,鞟,鞹,韕,頢,髺,鬠,㗥,䟯,䦢,䯺|ca:嚓,囃,擦,攃,礤,礸,遪,䟃,䵽|chuo:嚽,娕,娖,惙,戳,擉,歠,涰,磭,綽,绰,腏,趠,踔,輟,辍,辵,辶,逴,酫,鑡,齪,齱,龊,㚟,㲋,䂐,䃗,䄪,䆯,䇍,䋘,䍳,䓎,䮕|zen:囎,怎,譖,譛,谮,䫈|nin:囜,您,拰,脌,㤛,䋻,䚾,䛘|kun:困,坤,堃,堒,壸,壼,婫,尡,崐,崑,悃,捆,昆,晜,梱,涃,潉,焜,熴,猑,琨,瑻,睏,硱,祵,稇,稛,綑,臗,菎,蜫,裈,裍,裩,褌,醌,錕,锟,閫,閸,阃,騉,髠,髡,髨,鯤,鲲,鵾,鶤,鹍,㩲,㫻,䠅|qun:囷,夋,宭,峮,帬,羣,群,裙,裠,輑,逡,㪊,㿏,䭽|ri:囸,日,釰,鈤,馹,驲,䒤|lve:圙,擽,畧,稤,稥,鋝,鋢,锊,㑼,㔀,㨼,䂮,䌎,䛚,䤣|zhui:坠,墜,娷,惴,椎,沝,甀,畷,硾,礈,笍,綴,縋,缀,缒,膇,諈,贅,赘,轛,追,醊,錐,錣,鑆,锥,隹,餟,騅,骓,鵻,䄌|hang:垳,夯,妔,斻,杭,沆,笐,筕,絎,绗,航,苀,蚢,貥,迒,頏,颃,魧,㤚,䀪,䘕,䟘,䣈,䦳,䲳,䴂|sao:埽,嫂,慅,扫,掃,掻,搔,氉,溞,瘙,矂,繅,缫,臊,颾,騒,騷,骚,髞,鰠,鱢,鳋,㛮,㿋,䐹,䕅,䖣|zang:塟,奘,弉,牂,羘,脏,臓,臟,臧,葬,賍,賘,贓,贜,赃,銺,駔,驵,髒,㘸|zeng:増,增,憎,曽,曾,橧,熷,璔,甑,矰,磳,繒,缯,罾,譄,贈,赠,鄫,鋥,锃,鱛,㽪,䙢,䰝|en:奀,峎,恩,摁,煾,蒽,䅰,䊐,䬶,䭓,䭡|zou:奏,媰,掫,揍,棷,棸,箃,緅,菆,諏,诹,走,赱,邹,郰,鄒,鄹,陬,騶,驺,鯐,鯫,鲰,黀,齺,㔿,㵵,䠫|nv:女,恧,朒,籹,衂,衄,釹,钕,㵖,䖡,䘐,䚼,䶊|nuan:奻,暖,渜,煖,煗,餪,㬉,䎡,䙇|niu:妞,忸,扭,杻,汼,沑,炄,牛,牜,狃,紐,纽,莥,鈕,钮,靵,㺲,䀔,䋴,䏔,䒜|rao:娆,嬈,扰,擾,桡,橈,犪,繞,绕,荛,蕘,襓,遶,隢,饒,饶,㑱,㹛,䫞|niang:娘,嬢,孃,酿,醸,釀,䖆|niao:嫋,嬝,嬲,尿,脲,茑,茒,蔦,袅,裊,褭,鳥,鸟,㒟,㜵,㞙,㠡,㭤,㳮,䃵,䐁,䙚,䦊,䮍|nen:嫩,㜛,㯎,㶧|sun:孙,孫,巺,损,損,搎,榫,槂,潠,狲,猻,畃,笋,筍,箰,簨,荪,蓀,蕵,薞,鎨,隼,飧,飱,鶽,㔼,㡄,㦏,䁚|kuan:宽,寛,寬,梡,欵,款,歀,窽,窾,鑧,髋,髖,㯘,䕀,䤭,䥗,䲌|yen:岃,膶|ang:岇,昂,昻,枊,盎,肮,醠,骯,㦹,㭿,㼜,䀚,䍩,䒢,䩕,䭹,䭺|cen:岑,嵾,梣,涔,笒,膥,㞥,㻸,䃡,䅾,䤁,䨙,䯔,䲋|cuan:巑,撺,攅,攛,櫕,欑,殩,汆,熶,爨,穳,窜,竄,篡,篹,簒,蹿,躥,鑹,㠝,㭫,㵀,㸑,䆘,䰖|te:忑,忒,慝,特,犆,脦,蟘,貣,鋱,铽,㥂,㧹|re:惹,热,熱|den:扥,扽,揼|zhua:抓,檛,爪,簻,膼,髽|shuan:拴,栓,涮,腨,閂,闩,䧠|zhuai:拽,跩|lue:掠,略|shai:晒,曬,筛,篩,簁,簛,㩄,㬠|sen:森|run:橍,润,潤,閏,閠,闰,㠈,䦞|nue:疟,虐|nve:瘧,䖈,䖋,䨋|gei:給,给|miu:繆,缪,謬,谬|neng:能,㲌,㴰,䏻|zei:蠈,賊,贼,鰂,鱡,鲗|fiao:覅|eng:鞥|ng:㕶|chua:䫄';

            $rows = explode('|', $data);

            $pinyins = array();
            foreach($rows as $v) {
                list($py, $vals) = explode(':', $v);
                $chars = explode(',', $vals);

                foreach ($chars as $char) {
                    $pinyins[$char] = $py;
                }
            }
        }

        $str = trim($str);
        if ($str === ''){
            $str = 1;
        }
        $len = mb_strlen($str, 'UTF-8');
        $rs = '';
        for ($i = 0; $i < $len; $i++) {
            $chr = mb_substr($str, $i, 1, 'UTF-8');

            if(preg_match('/^[A-Z]+$/', $str)){
                $chr = strtolower($chr);
            }

            $asc = ord($chr);
            if ($asc < 0x80) { // 0-127
                if (preg_match($allow_chars, $chr)) { // 用参数控制正则
                    $rs .= $chr; // 0-9 a-z A-Z 空格
                } else { // 其他字符用填充符代替
                    $rs .= $placeholder;
                }

            } else { // 128-255
                if (isset($pinyins[$chr])) {
                    $rs .= 'first' === $ret_format ? $pinyins[$chr][0] : ($pinyins[$chr] . ' ');
                } else {
                    $rs .= $placeholder;
                }
            }

            if ('one' === $ret_format && '' !== $rs) {
                return $rs[0];
            }
        }

        return rtrim($rs, ' ');
    }

    //判断一个元素是否在二维数组里面
    public function array_multi_search( $p_needle, $p_haystack ){
        foreach ($p_haystack as $v){
            if ($v['region'] == $p_needle){
                return true;
            }
        }
        return false;
    }

    //图片制作缩略图
    public function imageThumbnail($old_src){
        ini_set('memory_limit','2048M');
        ini_set("gd.jpeg_ignore_warning", 1);
        //成功返回1，格式不符合返回2，生成图片失败返回3
        $info = pathinfo($old_src);
        $data = getimagesize($old_src);
        if (!$info || !$data){
            $old_src = substr($old_src,1);
            return $old_src;
        }
        $width = $data[0]/2;
        $height = $data[1]/2;
        $file_name = $info['basename'];
        $new_path = './uploads/thumbnail/'.date('Ymd',time());
        if (!is_dir($new_path)){
            mkdir($new_path,0777,true);
        }
        $new_src = $new_path.'/'.$file_name;
        $new_width = $width;
        $new_height= $height;
        $rate=100;

        $old_info = getimagesize($old_src);
        switch($old_info[2]){
            case 1:$im = imagecreatefromgif($old_src);break;
            case 2:$im = imagecreatefromjpeg($old_src);break;
            case 3:$im = imagecreatefrompng($old_src);break;
            case 4:$im = imagecreatefromjpeg("/img/swf.jpg");break;
            case 6:return false;
        }
        if(!$im) return 2;
        $old_width = imagesx($im);
        $old_height = imagesy($im);
        if($old_width<$new_width && $old_height<$new_height){
            imagejpeg($im,$new_src,$rate);
            imagedestroy($im);
            return 1;
        }

        $x_rate = $old_width/$new_width;
        $y_rate = $old_height/$new_height;
        if($x_rate<$y_rate){
            $dst_x = ceil($old_width/$y_rate);
            $dst_y = $new_height-1;
            $new_start_x = 0;
            $new_start_y = 0;
        }else {
            $dst_x = $new_width;
            $y_rate = $old_height / $new_height;
            if ($x_rate < $y_rate) {
                $dst_x = ceil($old_width / $y_rate);
                $dst_y = $new_height - 1;
                $new_start_x = 0;
                $new_start_y = 0;
            } else {
                $dst_x = $new_width;
                $dst_y = ceil($old_height / $x_rate);
                $new_start_x = 0;
                $new_start_y = 0;
            }
            $newim = imagecreatetruecolor($dst_x, $dst_y);//先压缩
            $bg = imagecolorallocate($newim, 255, 255, 255);
            imagefilledrectangle($newim, 0, 0, $dst_x, $dst_y, $bg); //画个大小一致矩形充当背景

            imagecopyresampled($newim, $im, 0, 0, 0, 0, $dst_x, $dst_y, $old_width, $old_height);

            $cutim = imagecreatetruecolor($dst_x, $dst_y);//对图像进行截图
            imagecopyresampled($cutim, $newim, 0, 0, $new_start_x, $new_start_y, $new_width, $new_height, $new_width, $new_height);
            imagejpeg($cutim, $new_src, $rate);//对图像进行截图

            imagedestroy($im);
            imagedestroy($newim);
            $a = imagedestroy($cutim);
            if ($a) {
                $img_size = ceil(filesize($new_src) / 1000); //获取文件大小
                if ($img_size > 500){
                    $this->imageThumbnail($new_src);
                }
                return $new_src;
            } else {
                return false;
            }
        }
    }

}
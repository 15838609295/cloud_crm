<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Admin\Modular;
use App\Models\Admin\PlugInUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlugInController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    //获取插件列表
    public function index(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        if ($type == 1){
            $cid = 174;
            //模块表
            $modularModel = new Modular();
            $list = $modularModel->getList();
        }else{
            $list = [];
            $cid = 183;
        }
        $data = DB::table('permissions')->where('cid',$cid)->select('id')->get();
        $data = json_decode(json_encode($data),true);
        $ids = [];
        foreach ($data as $v){
            $ids[] = $v['id'];
        }
        $res = DB::table('permissions')->whereIn('id',$ids)->select('id','label','display','icon','is_limit','sort','show_mode','new_name')->get();
        $res = json_decode(json_encode($res),true);
        if (!$res){
            $this->returnData['code'] = 0;
            $this->returnData['msg'] = '无插件';
            $this->returnData['data'] = [];
        }else{
            $res = array_merge($res,$list);
            foreach ($res as &$v){
                if (isset($v['icon'])){
                    $v['icon'] = $this->processingPictures($v['icon']);
                }
            }
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //修改插件信息
    public function updateInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['type_id'] = $request->input('type_id','');
        if (isset($data['type_id']) && $data['type_id'] != ''){
            $id = $request->input('id','');
            $data['new_name'] = $request->input('new_name','');
            $data['icon'] = $request->input('icon','');
            $data['is_limit'] = $request->input('is_limit','');
            $data['display'] = $request->input('display','');
            $data['type_id'] = $request->input('type_id','');
            $data['updated_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table('modular')->where('id',$id)->update($data);
            if (!$res){
                $this->returnData = ErrorCode::$admin_enum['modifyfail'];
            }
        }else{
            unset($data['type_id']);
            $id = $request->input('id','');
            $data['new_name'] = $request->input('new_name','');
            $data['icon'] = $request->input('icon','');
            $data['is_limit'] = $request->input('is_limit','');
            $data['display'] = $request->input('display','');
            $data['updated_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table('permissions')->where('id',$id)->update($data);
            if (!$res){
                $this->returnData = ErrorCode::$admin_enum['modifyfail'];
            }
        }
        return $this->return_result($this->returnData);
    }

    //首页模块列表
    public function modularList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getHomeList($type);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //修改首页模块信息
    public function updateModular(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['status'] = $request->input('status','');
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->updateHomeData($id,$data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }
        return $this->return_result($this->returnData);
    }

    //获取插件根据排序和状态
    private function getPlugUnitOrder($type){
        if ($type == 1){
            $ids = ['164','165','166','167','168','175','176','177','178','179','213','214','215','221'];
        }else{
            $ids = ['184','185'];
        }
        $res = DB::table('permissions')->whereIn('id',$ids)->get();
        if (!$res){
            return array();
        }else{
            //模块表
            if ($type == 1){
                $modularModel = new Modular();
                $list = $modularModel->getList();
                $res = json_decode(json_encode($res),true);
                if ($list){
                    $res = array_merge($res,$list);
                }
                $last_names = array_column($res,'sort');
                array_multisort($last_names,SORT_ASC,$res);
            }
            return $res;
        }
    }

    //获取底部导航栏根据排序和状态
    private function getNavigationList($type){
        $plugUnitModel = new PlugInUnit();
        $data['type'] = $type;
        $res = $plugUnitModel->getNavigationList($data,0);
        foreach ($res as &$v){
            if ($v['iconPath']){
                $v['iconPath'] = $this->processingPictures($v['iconPath']);
            }
            if ($v['selectedIconPath']){
                $v['selectedIconPath'] = $this->processingPictures($v['selectedIconPath']);
            }
            if ($v['selectedIconSvg']){
                $v['selectedIconSvg'] = $this->processingPictures($v['selectedIconSvg']);
            }
        }
        return $res;
    }

    //获取模块排序
    public function getModularOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['type'] = $request->post('type',1);
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getHomeOrder($data);
        $plugUnitModel = new PlugInUnit();
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            if ($data['type'] == 1){
                foreach ($res as &$v){
                    if ($v['id'] == 1){  //轮播图
                        $bannerList = $plugUnitModel->showBanner($data['type']);
                        if ($bannerList){
                            foreach ($bannerList as &$b_v){
                                $b_v['url'] = $this->processingPictures($b_v['url']);
                            }
                        }
                        $v['bannerList'] = $bannerList;
//                        $v['bannerList'] = $plugUnitModel->showBanner($data['type']);
                    }
                    if ($v['id'] == 3){   //插件列表插进去
                         $plugUnitList= $this->getPlugUnitOrder($data['type']);
                        foreach ($plugUnitList as &$p_v){
                            if ($p_v['icon']){
                                $p_v['icon'] = $this->processingPictures($p_v['icon']);
                            }
                        }
                        $v['plugUnitList'] = $plugUnitList;
                    }
                    if($v['id'] == 11){   //底部导航栏
                        $v['navigationList'] = $this->getNavigationList($data['type']);
                    }
                }
            }else{
                foreach ($res as &$v){
                    if ($v['id'] == 6){  //轮播图
                        $v['bannerList'] = $plugUnitModel->showBanner($data['type']);
                    }
                    if ($v['id'] == 8){   //插件列表插进去
                        $v['plugUnitList'] = $this->getPlugUnitOrder($data['type']);
                    }
                    if ($v['id'] == 12){
                        $v['navigationList'] = $this->getNavigationList($data['type']);
                    }
                }
            }
            $this->returnData['data'] = array_values($res);
        }
        return $this->return_result($this->returnData);
    }

    //小程序名称和色调
    public function getWxapplet(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        if ($type == 1){
            $res = DB::table('configs')->select('wxapplet_name as wxappletName','wxapplet_color as wxappletColor','member_format as memberFormat')->where('id',1)->first();
        }else{
            $res = DB::table('configs')->select('wechat_name as wxappletName','wechat_color as wxappletColor')->where('id',1)->first();
        }
        $res = json_decode(json_encode($res),true);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //修改小程序名称和色调
    public function updateWxapplet(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $type = $request->post('type',1);
        if ($type == 1){
            $data['wxapplet_name'] = $request->input('wxappletName','');
            $data['wxapplet_color'] = $request->input('wxappletColor','');
            $data['member_format'] = $request->input('memberFormat','');
        }else{
            $data['wechat_name'] = $request->input('wxappletName','');
            $data['wechat_color'] = $request->input('wxappletColor','');
        }
        if ($type == 1){
            if ($data['wxapplet_name'] == '' || $data['wxapplet_color']== ''){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '参数缺失';
                return $this->return_result($this->returnData);
            }
        }else{
            if ($data['wechat_name'] == '' || $data['wechat_color']== ''){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '参数缺失';
                return $this->return_result($this->returnData);
            }
        }
        $res = DB::table('configs')->where('id',1)->update($data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }
        return $this->return_result($this->returnData);
    }

    //移动模块位置
    public function updateModularOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $new_position = $request->input('modelList','');
        $position = json_decode($new_position,true);
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->updatehomeOrder($position);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '移动失败';
        }
        return $this->return_result($this->returnData);
    }

    //移动插件位置
    public function updatePlugUnitOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $new_sort = $request->input('plugunitList','');
        $sort = json_decode($new_sort,true);
        foreach ($sort as $v){
            if (isset($v['type_id'])){
                $where['sort'] = $v['sort'];
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                $res = DB::table('modular')->where('id',$v['id'])->update($where);
                if (!$res){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '移动失败';
                    return $this->return_result($this->returnData);
                }
            }else{
                $where['sort'] = $v['sort'];
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                $res = DB::table('permissions')->where('id',$v['id'])->update($where);
                if (!$res){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '移动失败';
                    return $this->return_result($this->returnData);
                }
            }
        }
        return $this->return_result($this->returnData);
    }

    //导航栏移动位置
    public function updateNavigationOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $navigation = $request->post('navigation','');
        $type = $request->post('type','');
        $new_list = json_decode($navigation,true);
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->navigationMove($type,$new_list);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '移动失败';
        }
        return $this->return_result($this->returnData);
    }

    //轮播图管理
    public function getBannerList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        $pageNo = $request->post('pageNo',1);
        $data['pageSize'] = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1) * $data['pageSize'];
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getBannerList($type,$data);
        if (!$res){
            $res['total'] = 0;
            $res['rows'] = [];
        }else{
            foreach ($res['rows'] as &$v){
                $v['url'] = $this->processingPictures($v['url']);
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //添加轮播图
    public function addBanner(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['title'] = $request->input('title','');
        $data['url'] = $request->input('url','');
        $data['type'] = $request->input('type',1);
        $data['status'] = $request->input('status','');
        $con = Configs::first();
        if ($con->env != "CLOUD"){
            $data['size'] = '0*0';
//            $size = getimagesize('https://'.$_SERVER['HTTP_HOST'].$data['url']);
//            $data['size'] = $size[0].'*'.$size[1];
        }else{
            $data['size'] = '0*0';
        }
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->addBanner($data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['addfail'];
        }
        return $this->return_result($this->returnData);
    }

    //轮播详情
    public function getBannerInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getBannerId($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '记录不存在';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //修改轮播信息
    public function updateBanner(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id');
        $status = $request->input('status');
        $url = $request->input('url');
        $title = $request->input('title');
        $data = [];
        if (isset($status)){
            $data['status'] = $status;
        }
        if ($url){
            $data['size'] = '';
            $data['url'] = $url;
        }
        if ($title){
            $data['title'] = $title;
        }
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->updateBannerStatus($id,$data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }elseif ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '至少保留一张轮播图来显示';
        }else if ($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '最多显示5张轮播图';
        }
        return $this->return_result($this->returnData);
    }

    //轮播图删除
    public function delBannerId($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->delBanner($id);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['delfail'];
        }
        return $this->return_result($this->returnData);
    }

    //底部导航栏列表
    public function navigationList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['type'] = $request->post('type','');
        $pageNo = $request->post('pageNo',1);
        $data['pageSize'] = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getNavigationList($data,1);
        if (isset($res['rows'])){
            foreach ($res['rows'] as &$v){
                if (isset($v['iconPath'])){
                    $v['iconPath'] = $this->processingPictures($v['iconPath']);
                }
                if (isset($v['selectedIconPath'])){
                    $v['selectedIconPath'] = $this->processingPictures($v['selectedIconPath']);
                }
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //添加底部导航栏
    public function addNavigationInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        $pageId = $request->post('pageId','');
        $list['text'] = $request->post('text','');
        $list['iconPath'] = $request->post('iconPath','');
        $list['selectedIconSvg'] = $request->post('selectedIconSvg','');
        $list['selectedIconPath'] = $request->post('selectedIconPath','');
        $list['display'] = $request->post('display',0);
        $list['sort'] = $request->post('sort',0);
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->addNavigationInfo($type,$pageId,$list);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['addfail'];
        }
        return $this->return_result($this->returnData);
    }

    //修改导航栏信息
    public function updateNavigationInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        $id = $request->post('id','');
        $pageId = $request->post('pageId','');
        $list['pagePath'] = $request->post('pagePath','');
        $list['text'] = $request->post('text','');
        $list['iconPath'] = $request->post('iconPath','');
        $list['selectedIconSvg'] = $request->post('selectedIconSvg','');
        $list['selectedIconPath'] = $request->post('selectedIconPath','');
        $list['display'] = $request->post('display','');
        $list['sort'] = $request->post('sort','');
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->updateNavigationInfo($type,$id,$pageId,$list);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }else if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '导航栏最多显示5个';
        }else if($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '导航栏必须要显示一个';
        }
        return $this->return_result($this->returnData);
    }

    //修改导航栏状态
    public function updateNavigationStatus(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type',1);
        $id = $request->post('id','');
        $display = (int)$request->post('display','');
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->updateNavigationStatus($type,$id,$display);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }else if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '导航栏最多显示5个';
        }else if($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '导航栏必须要显示一个';
        }
        return $this->return_result($this->returnData);
    }

    //删除底部导航栏
    public function delNavigationId(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type','');
        $id = $request->post('id','');
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->delNavigationId($type,$id);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['delfail'];
        }else if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '不能删除唯一显示的导航栏';
        }
        return $this->return_result($this->returnData);
    }

    //获取路径
    public function getWeAppPagePath(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->post('type','');
        $plugUnitModel = new PlugInUnit();
        $res = $plugUnitModel->getWxAppPageList($type);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //用户小程序配置
    public function getAgentWechatConfigs(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $configsModel = new Configs();
        $res = $configsModel->getValue('agent_wechat_configs');
        $res = json_decode($res,true);
        if (!$res){
            $data= [
                'album' => [
                    'status' => 0,
                    'bgImage' => ''
                ],
                'workOrder' => [
                    'status' => 0,
                    'bgImage' => ''
                ]
            ];
        }else{
            if (isset($res['album'])){
                $res['album']['bgImageUrl'] = $this->processingPictures($res['album']['bgImage']);
            }else{
                $res['album'] = ['status' => 0,'bgImage' => ''];
            }
            if (isset($res['workOrder'])){
                $res['workOrder']['bgImageUrl'] = $this->processingPictures($res['workOrder']['bgImage']);
            }else{
                $res['workOrder'] = ['status' => 0,'bgImage' => ''];
            }
            $data= $res;
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //修改用户小程序配置
    public function updateAgentWechatConfigs(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $list['album'] = $request->post('album','');
        $list['workOrder'] = $request->post('workOrder','');
        $configsModel = new Configs();
        $data['agent_wechat_configs'] = json_encode($list);
        $res = $configsModel->toUpdate($data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }
        return $this->return_result($this->returnData);
    }






}
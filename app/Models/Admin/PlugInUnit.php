<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlugInUnit extends Model
{
    protected $home ='home_page';
    protected $con ='configs';

    //获取首页配置列表
    public function getHomeList($type){
        $res = DB::table($this->home)->where('type',$type)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //修改首页配置
    public function updateHomeData($id,$data){
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->home)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取首页模块
    public function getHomeOrder($data){
        $res = DB::table($this->home)->where($data)->where('status',1)->orderBy('position','asc')->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $activity_id = DB::table('permissions')->where('id',166)->where('display',1)->first();
        if (!$activity_id){
            foreach ($res as $k=>$v){
                if ($v['id'] == 5){
                    unset($res[$k]);
                }
            }
        }
        foreach ($res as &$v){
            if ($v['id'] == 11 || $v['id'] == 12){
                $v['status'] = 0;
            }
        }
        return $res;
    }

    //修改模块位置
    public function updatehomeOrder($data){
        if (!is_array($data)){
            return false;
        }
        foreach ($data as $v){
            $where['position'] = $v['position'];
            $where['updated_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->home)->where('id',$v['id'])->update($where);
            if (!$res){
                return false;
            }
        }
        return true;
    }

    //修改插件位置
    public function updatePlugUnitOrder($data){
        if (!is_array($data)){
            return false;
        }
        foreach ($data as $v){
            $where['sort'] = $v['sort'];
            DB::table($this->table)->where('id',$v['id'])->update($where);
        }
        return true;
    }

    //轮播图列表
    public function getBannerList($type,$fields){
        $res = DB::table('banner')->where('type',$type);
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->get();
        if (!$result['rows']){
            return false;
        }
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        return $result;
    }

    //添加轮播图
    public function addBanner($data){
        //查询轮播图数量 少于5个默认显示多余5个默认不显示
        $count = DB::table('banner')->where('status',1)->count();
        if ($count >= 5){
            $data['status'] = 0;
        }else{
            $data['status'] = 1;
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('banner')->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取轮播详情
    public function getBannerId($id){
        $res = DB::table('banner')->where('id',$id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //修改轮播状态
    public function updateBannerStatus($id,$data){
//        $close = DB::table('banner')->where('status',1)->count();
//        if (isset($data['status'])){
//            if ($data['status'] == 0){
//                if ($close <= 1){
//                    $a = -1;
//                    return $a;
//                }
//            }elseif ($data['status'] == 1){
//                if ($close >= 5){
//                    $a = -2;
//                    return $a;
//                }
//            }
//        }
        $res = DB::table('banner')->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //轮播图删除
    public function delBanner($id){
        $res = DB::table('banner')->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取显示的轮播图
    public function showBanner($type){
        $res = DB::table('banner')->where('type',$type)->where('status',1)->get();
        if (!$res){
            $data = [];
            return $data;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //获取小程序底部导航栏
    public function getNavigationList($fields,$type){
        if ($fields['type'] == 1){  //客户小程序导航
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_tarbar_list'],true);
            if ($type == 1){  //有分页查询
                if (is_array($data)){
                    foreach ($data as $k=>&$v){
                        $v['id'] = $k;
                    }
                    $pagedata['total'] = count($data);
                    $pagedata['rows'] = array_slice($data,$fields['start'],$fields['pageSize']);
                    return $pagedata;
                }else{
                    return [];
                }
            }else{   //无分页获取显示的导航
                if (is_array($data)){
                    $list = [];
                    foreach ($data as $k=>&$v){
                        $v['id'] = $k;
                        if ((int)$v['display'] == 1){
                            $list[] = $v;
                        }
                    }
                    $last_names = array_column($list,'sort');
                    array_multisort($last_names,SORT_ASC,$list);
                    return $list;
                }else{
                    return [];
                }
            }
        }else{            //员工小程序导航
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            if ($type == 1){  //有分页查询
                if (is_array($data)){
                    foreach ($data as $k=>&$v){
                        $v['id'] = $k;
                    }
                    $pagedata['total'] = count($data);
                    $pagedata['rows'] = array_slice($data,$fields['start'],$fields['pageSize']);
                    return $pagedata;
                }else{
                    return [];
                }
            }else{   //无分页获取显示的导航
                if (is_array($data)){
                    $list = [];
                    foreach ($data as $k=>&$v){
                        $v['id'] = $k;
                        if ((int)$v['display'] == 1){
                            $list[] = $v;
                        }
                    }
                    $last_names = array_column($list,'sort');
                    array_multisort($last_names,SORT_ASC,$list);
                    return $list;
                }else{
                    return [];
                }
            }
        }
    }

    //添加底部小程序
    public function addNavigationInfo($type,$pageId,$fields){
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $path_res = DB::table('wxapp_page')->where('id',$pageId)->first();
        $path_res = json_decode(json_encode($path_res),true);
        if ($type == 1){   //客户小程序添加
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);

            $data = json_decode($res['agent_tarbar_list'],true);
            if (!is_array($data)){
                $fields['display'] = 1;
                $fields['pagePath'] = $path_res['path'];
                $fields['pagePathName'] = $path_res['name'];
                $list[] = $fields;
                $where['agent_tarbar_list'] = json_encode($list);
                $add_res = DB::table($this->con)->where('id',1)->update($where);
                if (!$add_res){
                    return false;
                }
                return true;
            }else{
                $display = 0;
                foreach ($data as $v){
                    //显示导航栏数量
                    if ($v['display'] == 1){
                        $display += 1;
                    }
                }
                if ($display >= 5){
                    $fields['display'] = 0;
                }
                $fields['pagePath'] = $path_res['path'];
                $fields['pagePathName'] = $path_res['name'];
                array_push($data,$fields);
                $where['agent_tarbar_list'] = json_encode($data);
                $add_res = DB::table($this->con)->where('id',1)->update($where);
                if (!$add_res){
                    return false;
                }
                return true;
            }
        }else{           //员工小程序添加
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            if (!is_array($data)){
                $fields['display'] = 1;
                $fields['pagePath'] = $path_res['path'];
                $fields['pagePathName'] = $path_res['name'];
                $list[] = $fields;
                $where['admin_tarbar_list'] = json_encode($list);
                $add_res = DB::table($this->con)->where('id',1)->update($where);
                if (!$add_res){
                    return false;
                }
                return true;
            }else{
                $display = 0;
                foreach ($data as $v){
                    //显示导航栏数量
                    if ($v['display'] == 1){
                        $display += 1;
                    }
                }
                if ($display >= 5){
                    $fields['display'] = 0;
                }
                $fields['pagePath'] = $path_res['path'];
                $fields['pagePathName'] = $path_res['name'];
                array_push($data,$fields);
                $where['admin_tarbar_list'] = json_encode($data);
                $add_res = DB::table($this->con)->where('id',1)->update($where);
                if (!$add_res){
                    return false;
                }
                return true;
            }
        }
    }

    //修改导航栏
    public function updateNavigationInfo($type,$id,$pageId,$fields){
        $path_res = DB::table('wxapp_page')->where('id',$pageId)->first();
        $path_res = json_decode(json_encode($path_res),true);
        if ($type == 1){  //客户小程序
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_tarbar_list'],true);
            $number = 0;
            $display = 0;
            foreach ($data as $k=>&$v){
                if ($v['display'] == 1){
                    $number +=1;
                }
                if ($k == $id){
                    $display = $v['display'];
                    $v['pageId'] = $pageId;
                    $v['pagePath'] = $path_res['path'];
                    $v['pagePathName'] = $path_res['name'];
                    $v['text'] = $fields['text'];
                    $v['iconPath'] = $fields['iconPath'];
                    $v['selectedIconSvg'] = $fields['selectedIconSvg'];
                    $v['selectedIconPath'] = $fields['selectedIconPath'];
                    $v['display'] = $fields['display'];
                    $v['sort'] = $fields['sort'];
                    $v['updated_at'] = Carbon::now()->toDateTimeString();
                }
            }
            if ($number <= 1 && $display == 0){
                return -2;
            }
            if ($number >= 5 && $display == 0 && $fields['display'] == 1){
                return -1;
            }
            $where['agent_tarbar_list'] = json_encode($data);
            $update_res = DB::table($this->con)->where('id',1)->update($where);
            if (!$update_res){
                return false;
            }
            return true;
        }else{
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            $number = 0;
            $display = 0;
            foreach ($data as $k=>&$v){
                if ($v['display'] == 1){
                    $number +=1;
                }
                if ($k == $id){
                    $display = $v['display'];
                    $v['pageId'] = $pageId;
                    $v['pagePath'] = $path_res['path'];
                    $v['pagePathName'] = $path_res['name'];
                    $v['text'] = $fields['text'];
                    $v['iconPath'] = $fields['iconPath'];
                    $v['selectedIconSvg'] = $fields['selectedIconSvg'];
                    $v['selectedIconPath'] = $fields['selectedIconPath'];
                    $v['display'] = $fields['display'];
                    $v['sort'] = $fields['sort'];
                    $v['updated_at'] = Carbon::now()->toDateTimeString();
                }
            }
            if ($number <= 1 && $display == 0){
                return -2;
            }
            if ($number >= 5 && $display == 0 && $fields['display'] == 1){
                return -1;
            }
            $where['admin_tarbar_list'] = json_encode($data);
            $update_res = DB::table($this->con)->where('id',1)->update($where);
            if (!$update_res){
                return false;
            }
            return true;
        }
    }

    //删除导航栏
    public function delNavigationId($type,$id){
        if ($type == 1){    //客户小程序
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_tarbar_list'],true);
            $number = 0;
            $display = 0;
            foreach ($data as $k=>$v){
                if ($v['display'] == 1){
                    $number +=1;
                }
                if ($k==$id){
                    $display = $v['display'];
                    unset($data[$k]);
                }
            }
            if ($number <= 1 && $display == 1){
                return -1;
            }
            $where['agent_tarbar_list'] = json_encode($data);
            $add_res = DB::table($this->con)->where('id',1)->update($where);
            if ($add_res){
                return true;
            }
            return false;
        }else{
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            $number = 0;
            $display = 0;
            foreach ($data as $k=>$v){
                if ($v['display'] == 1){
                    $number +=1;
                }
                if ($k==$id){
                    $display = $v['display'];
                    unset($data[$k]);
                }
            }
            if ($number <= 1 && $display == 1){
                return -1;
            }
            $where['admin_tarbar_list'] = json_encode($data);
            $add_res = DB::table($this->con)->where('id',1)->update($where);
            if ($add_res){
                return true;
            }
            return false;
        }
    }

    //修改导航栏状态
    public function updateNavigationStatus($type,$id,$display){
        if ($type == 1){   //客户小程序
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_tarbar_list'],true);
            $number = 0;
            foreach ($data as $k=>&$v){
                if ($v['display'] == 1){
                    $number += 1;
                }
                if ($k==$id){
                    $v['display'] = $display;
                }
            }
            if ($display == 1 && $number >= 5){
                return -1;
            }
            if ($display == 0 && $number <= 1){
                return -2;
            }
            $where['agent_tarbar_list'] = json_encode($data);
            $add_res = DB::table($this->con)->where('id',1)->update($where);
            if ($add_res){
                return true;
            }
            return false;
        }else{
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            $number = 0;
            foreach ($data as $k=>&$v){
                if ($v['display'] == 1){
                    $number += 1;
                }
                if ($k==$id){
                    $v['display'] = $display;
                }
            }
            if ($display == 1 && $number >= 5){
                return -1;
            }
            $where['admin_tarbar_list'] = json_encode($data);
            $add_res = DB::table($this->con)->where('id',1)->update($where);
            if ($add_res){
                return true;
            }
            return false;
        }
    }

    //导航栏排序
    public function navigationMove($type,$list){
        if ($type == 1){
            $res = DB::table($this->con)->select('agent_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_tarbar_list'],true);
            foreach ($data as $k=>&$v){
                foreach ($list as $l_v){
                    if ($l_v['id'] == $k){
                        $v['sort'] = $l_v['sort'];
                    }
                }
            }
            $where['agent_tarbar_list'] = json_encode($data);
            $update_res = DB::table($this->con)->where('id',1)->update($where);
            if ($update_res){
                return true;
            }
            return false;
        }else{
            $res = DB::table($this->con)->select('admin_tarbar_list')->where('id',1)->first();
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['admin_tarbar_list'],true);
            foreach ($data as $k=>&$v){
                foreach ($list as $l_v){
                    if ($l_v['id'] == $k){
                        $v['sort'] = $l_v['sort'];
                    }
                }
            }
            $where['admin_tarbar_list'] = json_encode($data);
            $update_res = DB::table($this->con)->where('id',1)->update($where);
            if ($update_res){
                return true;
            }
            return false;
        }
    }

    //获取小程序页面路径
    public function getWxAppPageList($type){
        $res = DB::table('wxapp_page')
            ->where('type',$type)
            ->where('is_home',1)
            ->get();
        if (!$res){
            return [];
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //获取客户小程序配置信息
    public function getAgentWechatConfigs(){
        $res = DB::table($this->con)->where('id',1)->select('agent_wechat_configs')->first();
        $res = json_decode(json_encode($res),true);
        if (!$res['agent_wechat_configs']){
            return false;
        }
        $data = json_decode($res['agent_wechat_configs'],true);
        return $data;
    }

    //修改客户小程序配置
    public function updateAgentWechatConfigs($fields){
        $res = DB::table($this->con)->where('id',1)->select('agent_wechat_configs')->first();
        if ($res){
            $res = json_decode(json_encode($res),true);
            $data = json_decode($res['agent_wechat_configs'],true);
            foreach ($fields as $k=>$v){
                $data[$k] = $v;
            }
            $list['agent_wechat_configs'] = json_encode($data);
        }else{
            $list['agent_wechat_configs'] = json_encode($fields);
        }
        $update_res = DB::table($this->con)->where('id',1)->update($list);
        if (!$update_res){
            return false;
        }
        return true;
    }

}

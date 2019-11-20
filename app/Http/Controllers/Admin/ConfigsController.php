<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Library\Tools;
use App\Models\Admin\Configs;
use App\Models\Auth\AuthBase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigsController extends BaseController
{

	public function __construct(Request $request){
        parent::__construct($request);
    }

    //站点信息
    public function index(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
    	$laravel = app();
    	$configModel = new Configs();
    	$con = $configModel->getConfigByID();
//        $data = Tools::curl("https://crmweb.netbcloud.com/admin/configs/other", "");
        $data = Tools::curl("https://crm.netbcloud.com/admin/configs/other", "");
        $data = json_decode($data,1);
        $pach = public_path().'/version.txt';
        $edition = file_get_contents($pach);  //获取版本信息
        $data['data'][0]['con_value'] = 'V'.$edition;
    	$arr[] = array('con_name'=>'程序版本','con_value'=>'云运维管理系统 '.$con['version']);
    	$arr[] = $data["data"][0];unset($data["data"][0]);
    	if ($con['env'] != 'CLOUD'){
            $arr[] = array('con_name'=>'服务器IP地址','con_value'=>$_SERVER['SERVER_ADDR']);
            $arr[] = array('con_name'=>'服务器域名','con_value'=>$_SERVER['SERVER_NAME']);
            $arr[] = array('con_name'=>'服务器端口','con_value'=>$_SERVER['SERVER_PORT']);
        }
    	$arr[] = array('con_name'=>'服务器版本','con_value'=>php_uname('s').php_uname('r')); 
    	$arr[] = array('con_name'=>'服务器操作系统','con_value'=>php_uname()); 
    	$arr[] = array('con_name'=>'PHP版本','con_value'=>phpversion()); 
    	$arr[] = array('con_name'=>'Laravel版本','con_value'=>$laravel::VERSION); 
    	$arr[] = array('con_name'=>'服务器当前时间 ','con_value'=>date("Y-m-d H:i:s")); 
    	$arr[] = array('con_name'=>'最大上传限制 ','con_value' => get_cfg_var ('upload_max_filesize') ? get_cfg_var ('upload_max_filesize'):'不允许');
    	$arr[] = array('con_name'=>'最大执行时间','con_value'=>get_cfg_var("max_execution_time")."秒 "); 
    	$arr[] = array('con_name'=>'数据库版本','con_value'=>'Mysql 5.7');
        $arr = array_merge($arr, $data["data"]);
        $this->returnData['data'] = $arr;
        return response()->json($this->returnData);
    }

    /* 用户菜单 */
    public function UserMenu(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($this->is_su){
            $res = AuthBase::getSuMenu();
        }else{
            $res = AuthBase::getUserPermission($this->AU['id']);
        }
        if(!$res){
            $res = [];
//            $this->returnData = ErrorCode::$admin_enum['fail'];
//            $this->returnData['data'] = ['menu'=>[],'function_men'=>[]];
//            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //获取功能插件
    public function get_function_menut(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if ($this->is_su){
            $result  = DB::table('admin_plug_in_unit')->get();
            $result = json_decode(json_encode($result),true);
            $this->returnData['data'] = $result;
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '访问错误';
        }
        return response()->json($this->returnData);
    }

    public function update_function_menut($id,Request$request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['display'] = $request->post('display');
        if ($data['display'] == 1){  //添加
            $res = DB::table('admin_plug_in_unit')->where('id',$id)->update($data);
            $res = DB::table('admin_plug_in_unit')->where('id',$id)->first();
            $res = json_decode(json_encode($res),true);
            $result = DB::table('plug_in_unit')->insert($res);
        }elseif ($data['display'] == 0){   //删除
            $res = DB::table('admin_plug_in_unit')->where('id',$id)->update($data);
            $result = DB::table('plug_in_unit')->delete($id);
        }
        if (!$res && !$result){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //客户功能配置列表
    public function cusFunction(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',30);
        $start = ($page_no - 1)*$page_size;
        $id = $request->input('cid','');
        $search = $request->input('search','');

        $res = DB::table('admin_permissions')->select('id','name','label','cid','display','description');
        if ($id != ''){
            $res->where('cid',(int)$id);
        }
        if($search !=''){
            $res->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('label', 'like', '%' . $search . '%');
            });
        }
        $data['total'] = $res->count();
        $result = $res->skip($start)->take($page_size)->orderBy('id', 'asc')->get();
        $result = json_decode(json_encode($result),true);
        $data['rows'] = $result;
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //权限详情
    public function cusFunctionInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $res = DB::table('admin_permissions')->where('id',$id)->first();
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '信息不存在';
        }else{
            $res = json_decode(json_encode($res),true);
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //编辑
    public function updateFunction(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['name'] = $request->input('name','');
        $data['label'] = $request->input('label','');
        $data['description'] = $request->input('description','');
        $data['cid'] = $request->input('cid','');
        $data['icon'] = $request->input('icon','');
        $data['display'] = $request->input('display','');
        $data['sort'] = $request->input('sort','');
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('admin_permissions')->where('id',$id)->update($data);
        //修改客户端的权限信息
        $cus_res = DB::table('permissions')->where('id',$id)->first();
        if ($cus_res){  //存在就修改
            DB::table('permissions')->where('id',$id)->update($data);
        }
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //删除
    public function delFunction($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $res = DB::table('admin_permissions')->delete($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }else{  //删除客户权限
            DB::table('permissions')->delete($id);
        }
        return response()->json($this->returnData);
    }

    //添加
    public function addFunction(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        $data['label'] = $request->input('label','');
        $data['description'] = $request->input('description','');
        $data['cid'] = $request->input('cid','');
        $data['icon'] = $request->input('icon','');
        $data['display'] = $request->input('display',1);
        $data['sort'] = $request->input('sort','');
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('admin_permissions')->insert($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //客户开启/关闭
    public function switchFunction(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $status = $request->input('status','');
        $target_info = DB::table('admin_permissions')->where('id',$id)->first();
        $target_info = json_decode(json_encode($target_info),true);
        if ($status == 1){  //开启
            if ($target_info['cid'] != 0){  //添加的如果是子 查询父
                $father_info = DB::table('permissions')->where('id',$target_info['cid'])->first();
                if (!$father_info){  //客户权限表没有父级就添加
                    $father_info = DB::table('admin_permissions')->where('id',$target_info['cid'])->first();
                    $father_info = json_decode(json_encode($father_info),true);
                    $father_info['display'] = 0;
                    DB::table('permissions')->insert($father_info);
                }
                //添加子
                $target_info['display'] = 0;
                DB::table('permissions')->insert($target_info);
                //修改 父级和子级显示为开启状态
                $where['display'] = 0;
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('admin_permissions')->where('id',$id)->update($where);
                DB::table('admin_permissions')->where('id',$target_info['cid'])->update($where);
            }else{  //添加父级 查询所有子集 批量添加
                $target_info['display'] = 0;
                DB::table('permissions')->insert($target_info);
                $son_info = DB::table('admin_permissions')->where('cid',$target_info['id'])->get();
                $son_info = json_decode(json_encode($son_info),true);
                //修改 父级和子级状态为显示
                $where['display'] = 0;
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('admin_permissions')->where('id',$id)->update($where);
                foreach ($son_info as $v){
                    $v['display'] = 0;
                    DB::table('permissions')->insert($v);
                    DB::table('admin_permissions')->where('id',$v['id'])->update($where);
                }
            }
        }elseif ($status == 2){  //关闭
            if ($target_info['cid'] != 0){  //关闭子
                DB::table('permissions')->delete($id);
                $where['display'] = 1;
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('admin_permissions')->where('id',$id)->update($where);
            }else{  //关闭父集
                $where['display'] = 1;
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                $res_list = DB::table('admin_permissions')->where('cid',$id)->get();
                $res_list = json_decode(json_encode($res_list),true);
                //删除父级修改父级状态
                DB::table('permissions')->delete($id);
                DB::table('admin_permissions')->where('id',$id)->update($where);
                //删除子级修改子级状态
                foreach ($res_list as $v){
                    DB::table('admin_permissions')->where('id',$v['id'])->update($where);  //修改关闭状态
                    DB::table('permissions')->delete($v['id']);                             //删除功能
                }
            }
        }
        return response()->json($this->returnData);
    }

    //修改体统版本
    public function updateSever(Request $request){
        $env = $request->input('env','');
        if (!$env){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '参数缺失';
            return response()->json($this->returnData);
        }
        $data['env'] = $env;
        $res = DB::table('configs')->where('id',1)->update($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }
}

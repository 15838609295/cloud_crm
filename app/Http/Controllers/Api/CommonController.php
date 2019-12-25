<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Activity;
use App\Models\Admin\Branch;
use App\Models\Admin\AdminUser;
use App\Models\Admin\Articles;
use App\Models\Admin\Configs;
use App\Models\Admin\FormId;
use App\Models\Admin\Picture;
use App\Models\Admin\PlugInUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Library\UploadFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommonController extends BaseController
{
    public function __construct()
    {
        $this->noCheckOpenidAction = ['getNews', 'getAbout', 'UploadPicture','getWxconfig','homePage']; //不校验openid
        parent::__construct();
    }

    /* 图片上传 */
    public function UploadPicture(Request $request)
    {
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('picture',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            //匹配出图片的格式
            if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '文件错误';
                return response()->json($this->returnData);
            }

            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            file_put_contents($temp_file, base64_decode(str_replace($result[1], '', $base64_img)));  //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/uploads/picture/'.$time;
            $img_name = $cloud_file.'/'.time().rand(11111,99999).'.'.$result[2];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传图片失败';
                return response()->json($this->returnData);
            }
            $result['status'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['url'] = $url['ObjectURL'];
        } else{
            $file = $request->file('picture');
            if (!$file){
                $this->returnData = ErrorCode::$api_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $res = (new UploadFile([
                'upload_dir' => './uploads/picture/',
                'type'       => ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp']
            ]))->upload($file);
            if($res['code'] > 0) {
                return response()->json($res);
            }
            $result['status'] = 0;
            $result['msg'] = '上传成功';
            $result['data'] = ['url' => $res['url'],'id' => $res['url']];
        }
        return response()->json($result);
    }

	//获取部门信息
	public function getBranchs(){
		$res = Branch::select('id','branch_name')->get()->toArray();
		$this->returnData['data'] = $res;
        return response()->json($this->returnData);
	}

    //解除绑定
    public function unbind(){
        $params = request()->input();
        $admin_res = DB::table('admin_users')->where('openid',$params['openid'])->first();
        if (!$admin_res){
            $data['status'] = 1;
            $data['msg'] = '解除绑定失败，未找到账号';
            return response()->json($data);
        }
        $where['openid'] = '';
        $res = DB::table('admin_users')->where('openid',$params['openid'])->update($where);
        if (!$res){
            $data['status'] = 1;
            $data['msg'] = '解除绑定失败，未找到账号';
            return response()->json($data);
        }else{
            $data['status'] = 0;
            $data['msg'] = '解除绑定成功';
            return response()->json($data);
        }
    }
	//业绩排行榜
	public function getRankingList()
    {
        $params = request()->post();
		$date = Carbon::now()->toDateString();
        $fields = array(
            'date' => substr($date,0,7)
        );
        if(isset($params['admin_id']) && trim($params['admin_id'])!=''){
            $fields['admin_id'] = trim($params['admin_id']);
        }
		$adminModel = new AdminUser();
        $admin_res = $adminModel->getRankingList($fields);
        if(!$admin_res){
            $admin_res = '';
        }else{
            foreach ($admin_res as &$v){
                if (isset($v['wechat_pic'])){
                    $v['wechat_pic'] = $this->processingPictures($v['wechat_pic']);
                }
            }
        }
		$this->returnData['data'] = $admin_res;
        return response()->json($this->returnData);
	}	

	//获取签到时间
	public function getWorkStatus()
    {
		$date = Carbon::now()->toDateString();
        $params = AdminUser::from('admin_users as au')
            ->select('au.*','c.name as company_name')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->where('au.status','=',0)
            ->orderBy('au.work_time','desc')
            ->get()->toArray();
    	//冒泡排序把接待的和签到早的挪到前面
    	$len = count($params);
    	for($k=0;$k <= $len; $k++){
		    for($j=$len-1;$j > $k; $j--){
		        if($params[$j]['work_status'] < $params[$j-1]['work_status']){
			        if(substr($params[$j]['work_time'],0,10)==substr($date,0,10)){
			        	if(substr($params[$j-1]['work_time'],0,10)==substr($date,0,10)){
		        			$temp = $params[$j];
				            $params[$j] = $params[$j-1];
				            $params[$j-1] = $temp;
				        }
			        }
		        }else if($params[$j]['work_status'] == $params[$j-1]['work_status']){
		        	if(substr($params[$j]['work_time'],0,10)==substr($date,0,10)){
			        	if(substr($params[$j-1]['work_time'],0,10)==substr($date,0,10)){
			        		if($params[$j]['work_time'] < $params[$j-1]['work_time']){
			        			$temp = $params[$j];
					            $params[$j] = $params[$j-1];
					            $params[$j-1] = $temp;
			        		}
				        }
			        }
		        }
		    }
		}
    	foreach($params as $k=>$v){
            $params[$k]['customer_number'] = 0;
    		//获取今天获得指派的客户数
    		if(substr($v['work_time'],0,10)==substr($date,0,10)){
    			$params[$k]['customer_number'] = DB::table('assign_log')->where('assign_touid',$v['id'])->where('created_at', 'LIKE', '%'.substr($date,0,10).'%')->count();
    		}
    		//work_status 因为后台是1代表接待，但是小程序是0代表接待，为了跟后台状态保持一致，需要数据颠倒下
            $params[$k]["work_status"] = $v["work_status"] == '1' ? 0 : 1;
    	}
    	$this->returnData['data'] = $params;
        return response()->json($this->returnData);
	}

	//修改工作状态
	public function updateWorkStatus()
    {
        $params = request()->post();
		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
		    $this->returnData['status'] = 1;
		    $this->returnData['msg'] = 'admin_id不能为空';
		    return response()->json($this->returnData);
		}
		if(!isset($params["work_status"])||trim($params["work_status"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'work_status不能为空';
            return response()->json($this->returnData);
		}
		if($params["work_status"]!=0 && $params["work_status"]!=1){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'work_status传值有误';
            return response()->json($this->returnData);
		}
		$date = Carbon::now()->toDateTimeString();
        //work_status 因为后台是1代表接待，但是小程序是0代表接待，为了跟后台状态保持一致，需要数据颠倒下
		$data['work_status'] = $params["work_status"] == '1' ? 0 : 1;
		$res = AdminUser::where('id','=',$params["admin_id"])->where('work_time','like','%'.substr($date,0,10).'%')->first();
		if(!$res && $date >= substr($date,0,10).' 08:00:00'){
			$data['work_time'] = $date;
		}
		
		$bool = AdminUser::where('id','=',$params["admin_id"])->update($data);
		if(!$bool){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '更改工作状态失败';
            return response()->json($this->returnData);
		}
        return response()->json($this->returnData);
	}

	//获取首页排版
    public function homePage(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $plugUnitModel = new PlugInUnit();
        $articleModel = new Articles();
        $activityModel = new Activity();
        $data['type'] = 2;
        $res = $plugUnitModel->getHomeOrder($data);
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            foreach ($res as &$v){
                if ($v['id'] == 6){  //轮播图
                    $v['bannerList'] = $plugUnitModel->showBanner($data['type']);
                }
                if ($v['id'] == 7){  //公告
                    $v['newsList'] = $articleModel->getNotice();
                }
                if ($v['id'] == 8){  //插件列表插进去
                    $v['plugUnitList'] = $this->getPlugUnitOrder();
                }
                if ($v['id'] == 9){  //新闻
                    $v['newsList'] = $articleModel->getNews();
                }
                if ($v['id'] == 10){  //推荐
                    $v['activityList'] = $activityModel->getNewActivity();
                }
            }
            $this->returnData['data'] = array_values($res);
        }
        return response()->json($this->returnData);
    }

    //获取插件根据排序和状态
    public function getPlugUnitOrder(){
        $ids = ['184','185'];
        $res = DB::table('permissions')->whereIn('id',$ids)->orderBy('sort','asc')->get();
        if (!$res){
            return array();
        }else{
            $res = json_decode(json_encode($res),1);
            return $res;
        }
    }

    //获取小程序名称和排版信息
    public function getWxconfig(){
        $data = DB::table('configs')->where('id',1)->select('wechat_name as wxappletName','wechat_color as wxappletColor')->first();
        $data = json_decode(json_encode($data),true);
        $navigationList = $this->getNavigationList(0);
        foreach ($navigationList as &$v){
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
        $data['tabbarList'] = $navigationList;
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    //获取底部导航栏根据排序和状态
    private function getNavigationList($type){
        $plugUnitModel = new PlugInUnit();
        $data['type'] = $type;
        $res = $plugUnitModel->getNavigationList($data,0);
        return $res;
    }

	//获取最新新闻
	public function getNews()
    {
        $articleModel = new Articles();
        $res = $articleModel->getSpecialApiArticle(1);
		if(!$res){
		    $res = '';
        }else{
		    if (isset($res['video_cover'])){
                $res['video_cover'] = $this->processingPictures($res['video_cover']);
            }
            if (isset($res['thumb'])){
		        $res['thumb'] = $this->processingPictures($res['thumb']);
            }
        }
		$this->returnData['data']= $res;
        return response()->json($this->returnData);
	}

	//获取关于我们
	public function getAbout()
    {
        $articleModel = new Articles();
        $res = $articleModel->getApiArticle(4);
        if(!$res){
            $res = '';
        }
		$this->returnData['data']= $res;
        return response()->json($this->returnData);
	}

    //获取form_id
    public function get_form_id(){
        $data['form_id'] = request()->input('formId','');
        $data['channel'] = 'other';
        $id = request()->input('id','');
        $data['form_user'] = 'admin_'.$id;
        $data['is_used'] = 0;
        $formIdModle = new FormId();
        $res = $formIdModle->addData($data);
        if (!$res){
            Log::info($id.'form_id error',array('info'=>$data));
        }
        return response()->json($this->returnData);
    }
}
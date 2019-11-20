<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\User\UserBase;
use App\Models\Admin\Achievement;
use Carbon\Carbon;
use DB;

class AchievementController extends BaseController
{

	//获取全局数据
	public function getGlobalData(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'admin_id不能为空';
            return response()->json($this->returnData);
		};
        $adminUserModel = new UserBase();
        $admin = $adminUserModel->getAdminByID($params['admin_id']);
        if($admin['openid'] != $this->openid){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = 'admin_id与当前用户不一致';
            return response()->json($this->returnData);
        }
    	$date = Carbon::now()->toDateTimeString();
        $last_month = Carbon::now()->subMonth()->toDateString();
        $last_date = Carbon::parse('yesterday')->toDateTimeString();
        $achievementModel = new Achievement();
        //当月业绩
        $data['totalbalance'] = $achievementModel->getAchievementByDate($date,$admin['id'],'month');
        //上月业绩
        $data['recharge'] = $achievementModel->getAchievementByDate($last_month,$admin['id'],'month');
        //第一业绩
        $data['achievement'] = $achievementModel->getTopAchievement($date,'month');
      	//今日业绩
      	$data['curtodaybalance'] = $achievementModel->getAchievementByDate($date,$admin['id'],'day');
      	//昨日业绩
      	$data['lasttodaybalance'] = $achievementModel->getAchievementByDate($last_date,$admin['id'],'day');
      	$this->returnData['data'] = $data;
        return response()->json($this->returnData);
	}
}
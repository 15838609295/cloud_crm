<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Achievement;
use App\Models\Admin\Configs;
use App\Models\Article\ArticleBase;
use App\Models\Customer\CustomerBase;
use App\Models\User\UserBase;
use App\Models\User\UserBranch;
use App\Models\User\UserMember;
use Carbon\Carbon;
use Illuminate\Http\Request;


class IndexController extends BaseController
{
    
    public function __construct(Request $request){
        parent::__construct($request);
    }
	
	/* 后台首页 */
    public function index(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $adminUserModel = new UserBase();
        $admin = $adminUserModel->getAdminByID($this->AU['id']);
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
		//更新的日志
        $articleModel = new ArticleBase();
		$data['articles'] = $articleModel->getIndexArticles();
		//用户团队列表
        $branchModel = new UserBranch();
        $data['user_branch'] = $branchModel->getUserBranchList($this->AU['id']);
        $data['index_branch_id'] = 0;
        if(is_array($data['user_branch']) && count($data['user_branch'])>1){
            $data['index_branch_id'] = $data['user_branch'][0]['branch_id'];
        }
        //我的客户数
        $customerModel = new CustomerBase();
        $data['total_customer'] = $customerModel->getCustomNumber($this->AU['id']);
        //逾期客户
        $data['customer_over'] = $customerModel->getCustomerOverCount($this->AU['id']);
        //总业绩
        $data['achievement_money'] = $achievementModel->getAchievementMoney($this->AU['id']);
        $con = Configs::first();
        $data['member_wechat_qr'] = $this->processingPictures($con->member_wechat_qr);
        $data['wxapplet_name'] = $con->wxapplet_name;
        $data['admin_wechat_qr'] = $this->processingPictures($con->admin_wechat_qr);
        $data['wechat_name'] = $con->wechat_name;
		$this->returnData['data'] = $data;
        return response()->json($this->returnData);
	}

	/* 首页待沟通客户列表 */
	public function contactList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'admin_id' => $this->AU['id']
        );
        $memberModel = new UserMember();
        $member_list = $memberModel->getIndexMemberList($searchFilter);
        $data = $member_list;
        $data['tabletile'] = '今天需要联系客户';
        if($this->AU['power']!=''){
            $data['tabletile'] = '七天内未联系客户';
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 获取用户团队列表 */
    public function userBranchData(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($request->post('branch_id')===NULL){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $page_no = $request->post('page_no') ? $request->post('page_no') : 1;
        $page_size = $request->post('page_size') ? $request->post('page_size') : 20;
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'branch_id' => trim($request->post('branch_id',''))
        );
        $branchModel = new UserBranch();
        $verify = $branchModel->isBranchUser($searchFilter['branch_id'],$this->AU['id']);
        if(!$verify){
            $this->returnData = ErrorCode::$admin_enum['not_branch_user'];
            return response()->json($this->returnData);
        }
        $columns = ['au.id','au.name','au.wechat_pic'];
        $member_list = $branchModel->getBranchUserList($searchFilter,$columns);
        $this->returnData['data'] = $member_list;
        return response()->json($this->returnData);
    }

    public function userBranchList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $branchModel = new UserBranch();
        $data = $branchModel->getUserBranchList($this->AU['id']);//echo "<pre>";print_r($data);
        if($data) {
            $branchId = $dataList = [];
            foreach ($data as $item) {
                $branchId[] = $item["branch_id"];
                $dataList[$item["branch_id"]] = $item;
            }
            $columns = ['au.id', 'au.name', 'au.wechat_pic', "ub.branch_id"];
            $branchData = $branchModel->getBranchUserLists($branchId, $columns);//echo "<pre>dddd";print_r($branchData);die("2");
            foreach ($branchData as $b){
                $dataList[$b["branch_id"]]["userList"][] = $b;
            }
            $this->returnData['data'] = array_values($dataList);
        }else{
            $this->returnData['data'] = [];
        }
        return response()->json($this->returnData);
    }
}

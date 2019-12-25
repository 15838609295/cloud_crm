<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Library\Tools;
use App\Models\Admin\Achievement;
use App\Models\Admin\AssignLog;
use App\Models\Member\MemberBase;
use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 签到列表 */
    public function attendanceList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $now_time = date('Y-m-d');
        $userModel = new UserBase();
        $res = $userModel->getAdminList();
        $data = array(
            'on_work' => array(),
            'out_of_work' => array()
        );
        $tmp_id_arr = [];
        foreach ($res as $key=>$value){
            if(strpos($value['work_time'],$now_time)===false){
                if (isset($value['wechat_pic'])){
                    $value['wechat_pic'] = $this->processingPictures($value['wechat_pic']);
                }
                $data['out_of_work'][] = $value;
                continue;
            }
            $tmp_id_arr[] = $value['id'];
            if (isset($value['wechat_pic'])){
                $value['wechat_pic'] = $this->processingPictures($value['wechat_pic']);
            }
            $data['on_work'][] = $value;
        }
        $tmp_arr = $data['on_work'];
        if(!empty($tmp_arr)) {
            $assign_log = $this->_getAssignByAdminID($tmp_id_arr, $now_time);
            $member = $this->_getMemberByAdminID($tmp_id_arr, $now_time);
            $achievement = $this->_getAchievementByAdminID($tmp_id_arr, $now_time);
            foreach ($tmp_arr as $key => $value) {
                //获取今天指派的客户数
                $tmp_arr[$key]['customer_number'] = isset($assign_log['day'][$value['id']]) ? $assign_log['day'][$value['id']] : 0;
                //获取当月指派的客户数
                $tmp_arr[$key]['month_customer_number'] = isset($assign_log['month'][$value['id']]) ? $assign_log['month'][$value['id']] : 0;
                //获取今天激活的客户数
                $tmp_arr[$key]['deal_number'] = isset($member['day'][$value['id']]) ? $member['day'][$value['id']] : 0;
                //获取当月激活的客户数
                $tmp_arr[$key]['month_deal_number'] = isset($member['month'][$value['id']]) ? $member['month'][$value['id']] : 0;
                //获取今天的业绩
                $tmp_arr[$key]['bonus_number'] = isset($achievement['day'][$value['id']]) ? $achievement['day'][$value['id']] : 0;
                //获取当月的业绩
                $tmp_arr[$key]['month_bonus_number'] = isset($achievement['month'][$value['id']]) ? $achievement['month'][$value['id']] : 0;
            }
            $data['on_work'] = $tmp_arr;
        }
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 业绩排行榜 */
    public function rankList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $searchFilter = array(
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'branch_id' => trim($request->post('branch_id','')),
            'company_id' => trim($request->post('company_id','')),
            'admin_id' => trim($request->post('admin_id','')),                                              //销售ID
            'start_time' => trim($request->post('start_time','')),
            'end_time' => trim($request->post('end_time','')),
        );
        $adminUserModel = new UserBase();
        $data = $adminUserModel->getAdminListToRank($searchFilter);
        if(!is_array($data)){
            $data['list'] = [];
            $data['total_money'] = 0.00;
            $data['today_total'] = 0.00;
            $data['yesterday_total'] = 0.00;
            $this->returnData['data'] = $data;
//            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return $this->return_result($this->returnData);
        }
        $tmp_list = [];
        foreach ($data as $item){
            $tmp_list[] = $item['id'];
        }
        $searchFilter['user_list'] = $tmp_list;
        if(!$searchFilter["start_time"]){
            $searchFilter["start_time"] = date('Y-m-d H:i:s', mktime(0,0,0,date('m'),1,date('Y'))); //当月开始时间
        }
        if(!$searchFilter["end_time"]){
            $searchFilter["end_time"] = date('Y-m-d H:i:s',mktime(23,59,59,date('m'),date('t'),date('Y'))); //当月结束时间
        }
        $achievementModel = new Achievement();
        $ach_data = $achievementModel->getAchievementDataToRank($searchFilter);
        $tmp = [];
        foreach ($ach_data as $key=>$value)
        {
            $tmp[$value['admin_users_id']] = $value['total_money'];
        }
        foreach ($data as $key=>$value){
            $data[$key]['total_money'] = 0;
            if(isset($tmp[$value['id']])){
                $data[$key]['total_money'] = $tmp[$value['id']];
            }
        }
        $tool = new Tools();
        $list = $tool->array_sort($data,'total_money','SORT_DESC');
        foreach ($list as &$v){
            if (isset($v['wechat_pic'])){
                $v['wechat_pic'] = $this->processingPictures($v['wechat_pic']);
            }
        }
        $data = array(
            'list' => array_values($list),
            'total_money' => 0,
            'today_total' => 0,
            'yesterday_total' => 0
        );
        $total_money = 0;
        foreach ($list as $key=>$value){
            $total_money = $total_money + floatval($value['total_money']);
        }
        $data['total_money'] = sprintf('%.2f',$total_money);
        $data['today_total'] = $achievementModel->getTotalMoneyBydate(['user_list' => $tmp_list,'date' => date('Y-m-d')]);
        $data['yesterday_total'] = $achievementModel->getTotalMoneyBydate(['user_list' => $tmp_list,'date' => Carbon::now()->yesterday()->toDateString()]);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //团队，部门业绩排行
    public function teamList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data = array(
            'start_time' => trim($request->post('start_time','')),
            'end_time' => trim($request->post('end_time','')),
        );
        $adminUserModel = new UserBase();
        $res = $adminUserModel->getAdminByCompanyId();
        if(!$data["start_time"]){
            $data["start_time"] = date('Y-m-d H:i:s', mktime(0,0,0,date('m'),1,date('Y'))); //当月开始时间
        }
        if(!$data["end_time"]){
            $data["end_time"] = date('Y-m-d H:i:s',mktime(23,59,59,date('m'),date('t'),date('Y'))); //当月结束时间
        }
        //获取业绩
        $achievementModel = new Achievement();
        $ach_data = $achievementModel->getAchievementByCompanyId($res,$data);
        $res1 = $this->arraySort($ach_data['company'],'total_money');
        $res2 = $this->arraySort($ach_data['branchs'],'total_money');
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['company'] = $res1;
        $data['data']['branchs'] = $res2;
        return $this->return_result($data);
    }

    public function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    /* 拼接接受分配客户的字段信息 */
    private function _getAssignByAdminID($list,$time)
    {
        $assignModel = new AssignLog();
        $day_in_list = $assignModel->getAssignLogByDateWithUserID($list,'in',$time);
        $day_out_list = $assignModel->getAssignLogByDateWithUserID($list,'out',$time);
        $month_in_list = $assignModel->getAssignLogByDateWithUserID($list,'in',substr($time,0,7));
        $month_out_list = $assignModel->getAssignLogByDateWithUserID($list,'out',substr($time,0,7));
        $tmp = [];
        foreach ($day_in_list as $key=>$value){
            $tmp[$value['uid']] = $value['total_assign'];
        }
        foreach ($day_out_list as $key=>$value){
            if(!isset($tmp[$value['uid']])){
                $tmp[$value['uid']] = 0;
            }
            $tmp[$value['uid']] = $tmp[$value['uid']] - $value['total_assign'];
        }
        $month_tmp = [];
        foreach ($month_in_list as $key=>$value){
            $month_tmp[$value['uid']] = $value['total_assign'];
        }
        foreach ($month_out_list as $key=>$value){
            if(!isset($month_tmp[$value['uid']])){
                $month_tmp[$value['uid']] = 0;
            }
            $month_tmp[$value['uid']] = $month_tmp[$value['uid']] - $value['total_assign'];
        }
        foreach ($list as $key=>$value){
            if(!isset($tmp[$value])){
                $tmp[$value] = 0;
            }
            if(!isset($month_tmp[$value])){
                $month_tmp[$value] = 0;
            }
        }
        $data = array(
            'day' => $tmp,
            'month' => $month_tmp
        );
        return $data;
    }

    /* 拼接已激活客户字段信息 */
    private function _getMemberByAdminID($list,$time)
    {
        $data = array(
            'day' => array(),
            'month' => array()
        );
        $customerModel = new MemberBase();
        $tmp = [];
        $res = $customerModel->getMemberByAdminID($list,$time);
        foreach ($res as $key=>$value){
            $tmp[$value['recommend']] = $value['total_member'];
        }
        $data['day'] = $tmp;
        $tmp = [];
        $res = $customerModel->getMemberByAdminID($list,substr($time,0,7));
        foreach ($res as $key=>$value){
            $tmp[$value['recommend']] = $value['total_member'];
        }
        $data['month'] = $tmp;
        return $data;
    }

    /* 拼接完成业绩金额字段信息 */
    private function _getAchievementByAdminID($list,$time)
    {
        $data = array(
            'day' => array(),
            'month' => array()
        );
        $achievementModel = new Achievement();
        $tmp = [];
        $res = $achievementModel->getAchievementByAdminID($list,$time);
        foreach ($res as $key=>$value){
            $tmp[$value['admin_users_id']] = $value['total_money'];
        }
        $data['day'] = $tmp;
        $tmp = [];
        $res = $achievementModel->getAchievementByAdminID($list,substr($time,0,7));
        foreach ($res as $key=>$value){
            $tmp[$value['admin_users_id']] = $value['total_money'];
        }
        $data['month'] = $tmp;
        return $data;
    }

    /* 运营数据 */
    public function getBusinessInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if(empty($id)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $arr = [];
        $date = date('Y-m-d');
        $assign_log = $this->_getAssignByAdminID([$id],$date);
        $member = $this->_getMemberByAdminID([$id],$date);
        $achievement = $this->_getAchievementByAdminID([$id],$date);
        $arr['day_customer_number'] = isset($assign_log['day'][$id]) ? $assign_log['day'][$id] : 0; //今日客户量   今天指派的客户数
        $arr['month_customer_number'] = isset($assign_log['month'][$id]) ? $assign_log['month'][$id] : 0; //本月客户量   当月指派的客户数
        $arr['deal_number'] = isset($member['day'][$id]) ? $member['day'][$id] : 0; //今天激活的客户数
        $arr['month_deal_number'] = isset($member['month'][$id]) ? $member['month'][$id] : 0;//获取当月激活的客户数
        $arr['day_bonus_number'] = isset($achievement['day'][$id]) ? $achievement['day'][$id] : 0;   //获取今天的业绩
        $arr['month_bonus_number'] =isset($achievement['month'][$id]) ? $achievement['month'][$id] : 0;//获取当月的业绩
        if($arr['month_customer_number']!=0){
            $arr['month_conversion'] = sprintf('%.2f',$arr['month_deal_number']/$arr['month_customer_number'])*100; //当月成单率
            $arr['month_no_conversion'] = 100 - $arr['month_conversion']; //本月丢单率
        }else{
            $arr['month_conversion'] = 0; //当月成单率
            $arr['month_no_conversion'] = 100; //本月丢单率
        }
        $yesterday = date('Y-m-d',strtotime("-0 year -1 month -0 day"));
        $assign_log = $this->_getAssignByAdminID([$id],$yesterday);
        $achievement = $this->_getAchievementByAdminID([$id],$yesterday);
        $arr['yesterday_customer_number'] = isset($assign_log['day'][$id]) ? $assign_log['day'][$id] : 0; //昨日客户量  昨日指派的客户数
        $arr['yesterday_month_customer_number'] = isset($assign_log['month'][$id]) ? $assign_log['month'][$id] : 0; //上月客户量   上月指派的客户数
        $arr['yesterday_bonus_number'] = isset($achievement['day'][$id]) ? $achievement['day'][$id] : 0;   //获取昨天的业绩
        $arr['last_month_bonus_number'] =isset($achievement['month'][$id]) ? $achievement['month'][$id] : 0;//获取上月的业绩
        $assignModel = new AssignLog();
        $all_in_list = $assignModel->getAssignLogByDateWithUserID([$id],'in');
        $all_out_list = $assignModel->getAssignLogByDateWithUserID([$id],'out');
        $in_total = $out_total = 0;
        if(isset($all_in_list[0])){
            $in_total = $all_in_list[0]["total_assign"];
        }
        if(isset($all_out_list[0])){
            $out_total = $all_out_list[0]["total_assign"];
        }
        $all_customer_number = $in_total - $out_total;//总客户量
        $customerModel = new MemberBase();
        $res = $customerModel->getMemberByAdminID($id,"");
        $all_deal_number = isset($res['total_member']) ? $res['total_member'] : 0; //总激活量
        $all_customer_number = $all_customer_number < $all_deal_number ? $all_deal_number : $all_customer_number; //暂时处理的
        if($all_customer_number>0){
            $arr['all_conversion'] = sprintf('%.2f',$all_deal_number/$all_customer_number)*100;  //总成单率
            $arr['all_no_conversion'] = 100 - $arr['all_conversion'];  //总丢单率
        }else{
            $arr['all_conversion'] = 0;
            $arr['all_no_conversion'] = 100;
        }
        $this->returnData['data'] = $arr;
        return $this->return_result($this->returnData);
    }
}

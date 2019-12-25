<?php
namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Member\MemberBase;
use App\Models\NotifyBase;
use App\Models\User\UserBase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Partners\V20180321\Models\AuditApplyClientRequest;
use TencentCloud\Partners\V20180321\Models\DescribeAgentClientsRequest;
use TencentCloud\Partners\V20180321\Models\DescribeAgentPayDealsRequest;
use TencentCloud\Partners\V20180321\PartnersClient;

class CommonController extends Controller
{

    public $result = array("status"=>0,'msg'=>'请求成功','data'=>"");
    //更新售后订单信息
    public function updateAfterSale(){
        $after_arr = DB::table('after_sale as af')
            ->select('af.*','s.rule_type','s.rule_type','s.type','s.after_first_bonus')
            ->leftJoin('salebonusrule as s','af.sbr_id','=','s.id')
            ->where('af.after_status','=',0)
            ->where('af.sbr_id','>',0)
            ->where('s.rule_type',0)
            ->get();
        $after_arr = json_decode(json_encode($after_arr),true);

        $ins_data = [];
        foreach ($after_arr as $v) {
            //如果是奖金记录 增加一个月份 跳过
            if ($v['type'] == 0){
                $data_upd['after_time'] = $v['after_time'] + 1;
                $data_upd['updated_at'] = Carbon::now()->toDateTimeString();
                $this->updateAfterSaleInfo($data_upd,$v['id']);
                continue;
            }
            $data_upd = [];
            $admin_upd = [];
            $data = [];
            $addBonusMoney = 0;    //要增加的提成

            //获取售后人员信息
            $adminUser = DB::table('admin_users')->where('id',$v['after_sale_id'])->select('bonus','name')->first();
            $adminUser = json_decode(json_encode($adminUser),true);
            //计算时间
            $cur_time = Carbon::parse('-' . ($v['after_time'] + 1) . ' months')->toDateTimeString();

            if ($v['after_time'] == 0) {                                                                   //计算首次提成
                if(($v['after_time'] + 1) <= $v['buy_length']){                                           //服务时间在购买时间内
                    if ($cur_time >= $v['buy_time']){                                                     //到购买时间 发提成
                        $data_upd['after_time'] = $v['after_time'] + 1;

                        if ($v['after_first_bonus'] == 0) {           //当售后首月为0时   总金额除以月数加钱
                            $addBonusMoney = intval($v['after_money'] / $v['buy_length']);
                        } else {
                            $addBonusMoney = (intval($v['after_money'] * $v['after_first_bonus'])) / 100;
                        }
                        $admin_upd['bonus'] =  $adminUser['bonus'] + $addBonusMoney;
                        $data['admin_users_id'] = $v['after_sale_id'];
                        $data['type'] = 1;
                        $data['money'] = $addBonusMoney;
                        $data['cur_bonus'] = $admin_upd['bonus'];
                        $data['member_name'] = $v['member_name'];
                        $data['member_phone'] = $v['member_phone'];
                        $data['goods_name'] = $v['goods_name'];
                        $data['goods_money'] = $v['goods_money'];
                        $data['order_number'] = $v['order_number'];
                        $data['remarks'] = $v['remarks'];
                        $data['created_at'] = Carbon::now()->toDateTimeString();
                        //增加提交人名称和审核人名称
                        $data['submitter'] = $adminUser['name'];
                        $data['auditor'] = 'root';
                    }
                }
            }else if (($v['after_time'] + 1) <= $v['buy_length']) {                                     //服务时间在购买时间内 计算非首次提成
                if ($cur_time >= $v['buy_time']){                                                         //到购买时间 发提成
                    $data_upd['after_time'] = $v['after_time'] + 1;

                    if ($v['after_first_bonus'] !== 0) {       //如果售后首月提成为0时
                        $one = (intval($v['after_money'] * $v['after_first_bonus'])) / 100;
                    } else {
                        $one = 0;
                    }
                    if ($one == 0) {
                        $addBonusMoney = round($v['after_money'] / $v['buy_length']);
                    } else {
                        $addBonusMoney = round(($v['after_money'] - $one) * 100 / ($v['buy_length'] - 1)) / 100;
                    }
                    $admin_upd['bonus'] = $adminUser['bonus'] + $addBonusMoney;
                    $data['admin_users_id'] = $v['after_sale_id'];
                    $data['type'] = 1;
                    $data['money'] = $addBonusMoney;
                    $data['cur_bonus'] = $admin_upd['bonus'];
                    $data['member_name'] = $v['member_name'];
                    $data['member_phone'] = $v['member_phone'];
                    $data['goods_name'] = $v['goods_name'];
                    $data['goods_money'] = $v['goods_money'];
                    $data['order_number'] = $v['order_number'];
                    $data['remarks'] = $v['remarks'];
                    $data['created_at'] = Carbon::now()->toDateTimeString();
                    //增加提交人名称和审核人名称
                    $data['submitter'] = $adminUser['name'];
                    $data['auditor'] = 'root';
                }
            }else {
                $data = [];
                $admin_upd = [];
                $data_upd['after_status'] = 1;
            }
            //售后表更新
            if (count($data_upd) > 0){
                $data_upd['updated_at'] = Carbon::now()->toDateTimeString();
                $this->updateAfterSaleInfo($data_upd,$v['id']);
            }
            //用户表提成更新
            if (count($admin_upd) > 0){
                $admin_upd['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('admin_users')->where('id', $v['after_sale_id'])->update($admin_upd);
            }
            //增加资金记录
            if (count($data) > 0){
                array_push($ins_data,$data);
            }
        }
        //批量插入
        DB::table('admin_bonus_log')->insert($ins_data);
        return response()->json($this->result);
    }

    //提成表修改
    private function updateAfterSaleInfo($fields,$id){
        $res = DB::table('after_sale')->where('id',$id)->update($fields);
        if (!$res){
            Log::info("admin_after_sale_update_error",array("id"=>$id,"update_info"=>$fields));
        }
        return true;
    }

    //更新售后奖金信息
    public function updateAfterSaleBonus(){
        $after_arr = DB::table('bonus_sale as bs')
            ->select('bs.*','s.rule_type','s.type','s.first_bonus')
            ->leftJoin('salebonusrule as s','bs.sbr_id','=','s.id')
            ->where('bs.after_status', '=', 0)
            ->where('bs.sbr_id','>',0)
            ->where('s.rule_type',0)
            ->where('s.type',0)
            ->get();
        $after_arr = json_decode(json_encode($after_arr),true);

        $ins_data = [];
        foreach ($after_arr as $v) {
            $data_upd = [];
            $admin_upd = [];
            $data = [];

            //管理员表更新
            $adminUser = DB::table('admin_users')->where('id',$v['after_sale_id'])->select('sale_bonus','name')->first();
            $adminUser = json_decode(json_encode($adminUser),true);
            //计算时间
            $cur_time = Carbon::parse('-' . ($v['after_time'] + 1) . ' months')->toDateTimeString();
            $addMoney = 0;    //要增加的金额

            if ($v['after_time'] == 0) {   //计算首次提成
                if ( ($v['after_time'] + 1) <= $v['buy_length']){
                    if ($cur_time >= $v['buy_time']){
                        $data_upd['after_time'] = $v['after_time'] + 1;

                        if ($v['first_bonus'] == 0) {           //当售后首月为0时   总金额除以月数加钱
                            $addMoney = intval($v['after_money'] / $v['buy_length']);
                        } else {
                            $addMoney = (intval($v['after_money'] * $v['first_bonus'])) / 100;
                        }
                        $admin_upd['sale_bonus'] = $adminUser['sale_bonus'] + $addMoney;
                        $data['admin_users_id'] = $v['after_sale_id'];
                        $data['type'] = 4;
                        $data['money'] = $addMoney;
                        $data['cur_bonus'] = $admin_upd['sale_bonus'];
                        $data['member_name'] = $v['member_name'];
                        $data['member_phone'] = $v['member_phone'];
                        $data['goods_name'] = $v['goods_name'];
                        $data['goods_money'] = $v['goods_money'];
                        $data['order_number'] = $v['order_number'];
                        $data['remarks'] = $v['remarks'];
                        $data['created_at'] = Carbon::now()->toDateTimeString();
                        //增加提交人名称和审核人名称
                        $data['submitter'] = $adminUser['name'];
                        $data['auditor'] = 'root';
                    }
                }
            }else if (($v['after_time'] + 1) <= $v['buy_length']) {
                if ($cur_time >= $v['buy_time']){
                    $data_upd['after_time'] = $v['after_time'] + 1;
                    if ($v['first_bonus'] !== 0) {       //如果售后首月提成为0时
                        $one = (intval($v['after_money'] * $v['first_bonus'])) / 100;
                    } else {
                        $one = 0;
                    }
                    if ($one == 0) {
                        $addMoney = round($v['after_money'] / $v['buy_length']);
                    } else {
                        $addMoney = round(($v['after_money'] - $one) * 100 / ($v['buy_length'] - 1)) / 100;
                    }
                    $admin_upd['sale_bonus'] = $adminUser['sale_bonus'] + $addMoney;
                    $data['admin_users_id'] = $v['after_sale_id'];
                    $data['type'] = 4;
                    $data['money'] = $addMoney;
                    $data['cur_bonus'] = $admin_upd['sale_bonus'];
                    $data['member_name'] = $v['member_name'];
                    $data['member_phone'] = $v['member_phone'];
                    $data['goods_name'] = $v['goods_name'];
                    $data['goods_money'] = $v['goods_money'];
                    $data['order_number'] = $v['order_number'];
                    $data['remarks'] = $v['remarks'];
                    $data['created_at'] = Carbon::now()->toDateTimeString();
                    //增加提交人名称和审核人名称
                    $data['submitter'] = $adminUser['name'];
                    $data['auditor'] = 'root';
                }
            } else {
                //售后表更新
                $data = [];
                $data_upd['after_status'] = 1;
                $admin_upd = [];
            }
            if (count($admin_upd) > 0){    //有计算奖金
                $admin_upd['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('admin_users')->where('id',$v['after_sale_id'])->update($admin_upd);
            }
            if(count($data_upd) > 0){
                DB::table('bonus_sale')->where("id",$v['id'])->update($data_upd);
                $data_upd['updated_at'] = Carbon::now()->toDateTimeString();
            }
            if (count($data) > 0){
                array_push($ins_data,$data);
            }

        }
        //批量插入
        DB::table('admin_bonus_log')->insert($ins_data);
        return response()->json($this->result);
    }

    public function getTitle()
    {
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*--- end 跨域测试用---*/
        $returnData = ErrorCode::$admin_enum["success"];
        $configModel = new Configs();
        $data = $configModel->getFields(["title", "qy_redirect", "shortcut", "shortcut_name", "shortcut_url", "qywxLogin", "agent_url","seo_title","env",'avatar_status','plugin_open_type as pluginOpenType']);
        $agreementData = DB::table('articles')->whereIn('typeid',[6,7])->select('typeid','title','content')->get();
        if (!$agreementData){
            $data['agreementTitle'] = '';
            $data['agreementContent'] ='';
            $data['privacyTitle'] = '';
            $data['privacyContent'] = '';
        }
        $agreementData = json_decode(json_encode($agreementData),true);
        foreach ($agreementData as $v){
            if ($v['typeid'] == 6){
                $data['agreementTitle'] = $v['title'];
                $data['agreementContent'] =$v['content'];
            }else{
                $data['privacyTitle'] = $v['title'];
                $data['privacyContent'] =$v['content'];
            }
        }
        $returnData['data'] = $data;
        return response()->json($returnData);
    }

    //站点信息
    public function other()
    {
        $arr[] = array('con_name' => '软件版本', 'con_value' => 'V3.4');
        $arr[] = array('con_name' => '联系我们', 'con_value' => '手机/微信：13670093216 QQ：1018608475');
        $arr[] = array('con_name' => '版权所有', 'con_value' => '深圳市网商天下科技开发有限公司');
        $arr[] = array('con_name' => '总策划兼项目经理', 'con_value' => '车贯 （Michael）');
        $arr[] = array('con_name' => '研发团队', 'con_value' => '陈元  许堉颖  陈镇松  李迪  杜新科  刘丹');
        $arr[] = array('con_name' => '设计团队', 'con_value' => '龙芳 林鸿耿');
        $arr[] = array('con_name' => '特别支持', 'con_value' => '刘远航  袁程远');
        $returnData['data'] = $arr;
        return response()->json($returnData);
    }

    //缓存腾讯云当天的客户订单
    public function cache_tencent_order(){
        //清空表数据
        DB::table("tencent_order")->truncate();
        DB::table("tencent_order_price")->truncate();
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $start_time = date('Y-m-d 00:00:00',time());
        $end_time = date('Y-m-d 23:59:59',time());
        $params = [
            "CreatTimeRangeStart" => $start_time,
            "CreatTimeRangeEnd" => $end_time,
            "Offset" => 0,
            "Limit" => 200,
        ];
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new DescribeAgentPayDealsRequest();

        $req->fromJsonString(json_encode($params));

        $resp = $client->DescribeAgentPayDeals($req);
        $res = $resp->toJsonString();
        $res = json_decode($res,true);
        $orders = [];
        $orders_price = [];
        if (isset($res['AgentPayDealSet'])){
            foreach ($res['AgentPayDealSet'] as $k=>$v){
                $orders[$k]['dealId'] = $v['DealId'];
                $orders[$k]['dealName'] = $v['DealName'];
                $orders[$k]['goodsCategoryId'] = $v['GoodsCategoryId'];
                $orders[$k]['ownerUin'] = $v['OwnerUin'];
                $orders[$k]['appId'] = $v['AppId'];
                $orders[$k]['goodsNum'] = $v['GoodsNum'];
                $orders[$k]['creater'] = $v['Creater'];
                $orders[$k]['creatTime'] = $v['CreatTime'];
                $orders[$k]['payer'] =  $v['Payer'];
                if (isset($v['BillId'])){
                    $orders[$k]['billId'] = $v['BillId'];
                }else{
                    $orders[$k]['billId'] = '';
                }
                if (isset($v['VoucherDecline'])){
                    $orders[$k]['voucherDecline'] = $v['VoucherDecline'];
                }else{
                    $orders[$k]['voucherDecline'] = '';
                }
                $orders[$k]['payEndTime'] = $v['PayEndTime'];
                $orders[$k]['status'] = $v['Status'];
                $orders[$k]['goodsName'] = $v['GoodsName'];
                $orders[$k]['actionType'] = $v['ActionType'];
                $orders[$k]['payerMode'] = $v['PayerMode'];
                $orders[$k]['clientRemark'] = $v['ClientRemark'];
                $orders[$k]['clientType'] = $v['ClientType'];
                $orders[$k]['projectType'] = $v['ProjectType'];
                $orders[$k]['dealStatus'] = $v['DealStatus'];
                $orders[$k]['bigDealId'] = $v['BigDealId'];
                $orders[$k]['voucherDecline']    = '';
                $orders[$k]['flag']  = '';
                //价格详情
                $orders_price[$k]['realTotalCost'] = $v['GoodsPrice']['RealTotalCost'];
            }
        }else{
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            return response()->json($result);
        }
        $orders_res = DB::table('tencent_order')->insert($orders);
        $orders_price_res = DB::table('tencent_order_price')->insert($orders_price);
        if ($orders_res && $orders_price_res) {
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            return response()->json($result);
        } else {
            $result['code'] = 1;
            $result['msg'] = '添加失败';
            return response()->json($result);
        }
    }

    //自动审核腾讯云客户
    public function verifyQCloudCustomerList(){
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);
        $req = new DescribeAgentClientsRequest();
        $req->ClientFlag = 'a';
        $resp = $client->DescribeAgentClients($req);
        $res = $resp->toJsonString();
        $res = json_decode($res, true);
        if ($res['TotalCount'] > 0) {
            $data = $res['AgentClientSet'];
            if (!is_array($data) || count($data) < 1) {
                return response()->json(['status' => 99, 'msg' => '接口报错，返回失败']);
            }
            $verify_res = $this->_verifyQCloudCustomer($data);
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    public function _verifyQCloudCustomer($data){
        require_once  base_path().'/vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
        $con = Configs::first();
        $cred = new Credential($con->tencent_secretid, $con->tencent_secrekey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("partners.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new PartnersClient($cred, "", $clientProfile);

        $req = new AuditApplyClientRequest();
        foreach ($data as $v) {
            $req = new AuditApplyClientRequest();
            $req->ClientUin = $v['ClientUin'];
            $req->AuditResult = 'accept';   //同意
            $req->Note = '1.客户情况：个人  2.预计每月订单与消耗：1K-3K  3.为客户提供何种服务：售前售后售中技术咨询服务'; //申请理由      B类客户审核时必须填写申请理由 有腾讯云审核

            $resp = $client->AuditApplyClient($req);
            $res = $resp->toJsonString();
            $res = json_decode($res, true);
            if (isset($res['Response']['Error'])) {
                Log::info($v['ClientUin'] . ' verify fail. ', array('result' => $res));
            } else {
                Log::info($v['ClientUin'] . ' verify success. ', array('result' => $res));
            }
        }
    }

    public function uploadzipfile()
    {
        $data['code'] = 0;
        $data['msg'] = '该用户可以上传文件';
        return response()->json($data);
    }

    //修改活动状态定时任务接口
    public function updateActivityStatus()
    {
        $activity_arr = DB::table('activity')
            ->select('id','activity_status','start_time','end_time','stop_time')
            ->whereIn('activity_status',[1,2,3])
            ->get();
        $activity_arr = json_decode(json_encode($activity_arr), true);
        if (!$activity_arr){
            return response()->json($this->result);
        }
        $now_time = Carbon::now()->toDateTimeString();
        foreach ($activity_arr as $v) {
            $where['activity_status'] = '';
            switch ($v['activity_status']){
                case 1;                  //报名中
                    if ($v['stop_time'] < $now_time){
                        $where['activity_status'] = 2;
                    }
                    break;
                case 2;                 //报名截止
                    if ($v['start_time'] < $now_time){
                        $where['activity_status'] = 3;
                    }
                    break;
                case 3;                 //活动进行中
                    if ($v['end_time'] < $now_time){
                        $where['activity_status'] = 4;
                    }
                    break;
                default;
                    $where['activity_status'] = '';
                   break;
            }
            if ($where['activity_status'] != ''){
                DB::table('activity')->where('id', $v['id'])->update($where);
            }
        }
        return response()->json($this->result);
    }

    //丢单/下次联系小于当前时间，客户重新分配
    public function customerUpdateInfo(){
        $res =  DB::table('customer')
            ->where(function ($query) {
                $query ->where('contact_next_time','<',date('Y-m-d',time()).' 00:00:00')
                    ->Orwhere('progress','丢单');
            })
            ->where('cust_state','!=',1)
            ->where('recommend','!=',1)
           ->skip(0)
            ->take(100)
            ->get();
        $res = json_decode(json_encode($res),true);
        $where['recommend'] = 1;
        foreach ($res as $v){
            DB::table('customer')->where('id',$v['id'])->update($where);
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    //删除过期form_id
    public function timingUpdateFormId(){
        $exceedTime = date("Y-m-d 00:00:00",strtotime("-5 day"));
        $res =  DB::table('form_id')
            ->where('create_time','<',$exceedTime)
            ->select('id')
            ->get();

        $res = json_decode(json_encode($res),true);
        if (!$res){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            return response()->json($data);
        }
        $ids = [];
        foreach ($res as $v){
            $ids[] = $v['id'];
        }
        DB::table('form_id')->whereIn('id',$ids)->delete();
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    //下载文件
    public function download(Request $request){
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $url = $request->post('url');
            $info = pathinfo($url);
            $data['code'] = 3;
            $data['name'] = $info['basename'];
            $data['data'] = $url;
            return $data;
        }else{
            $file_name = './'.$request->input('url');
            $size = filesize ($file_name);
            $file_name = "https://".$_SERVER['SERVER_NAME'].'/'.$file_name;
            $mime = 'application/force-download';
            header('Pragma: public'); // required
            header('Expires: 0'); // no cache
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private',false);
            header('Content-Type: '.$mime);
            header("Accept-ranges:bytes");
            header("Content-Length:".$size);
            header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
            header('Content-Transfer-Encoding: binary');
            header('Connection: close');
            readfile($file_name); // push it out
            exit();
        }
    }

    //根据设置提醒时间通知运维和客户经理
    public function updateRemindTime(){
        $res = DB::table('achievement_extend')->whereNotNull('remind_time')->get();
        $con = Configs::first();
        $notifyModel = new NotifyBase();
        $adminBaseModel = new UserBase();
        $memberBaseModel = new MemberBase();
        if (!$res){
            $data['code'] = 0;
            $data['msg'] = '请求成功';
            return response()->json($data);
        }
        $res = json_decode(json_encode($res),true);
        //日期计算
        foreach ($res as $v){
            $set_time = strtotime($v['remind_time']);
            $now_time = strtotime(date('Y-m-d 00:00:00',time()));
            $surplus_time = ($set_time - $now_time)/86400;
            if ($surplus_time == 0){  //到期 发送通知
                if ($con->qywxLogin == 1){
                    $member_info = $memberBaseModel->getMemberByID($v['member_id']);
                    $manager_info = $adminBaseModel->getAdminByID($v['manager_id']);
                    if ($manager_info){
                        $params = array(
                            'type' => 'ach_success',
                            'content' => '您设置的'.$member_info['name'].'客户的提醒时间为今天，请您及时处理，并更新提醒时间',
                            'receive_wechatid' => $manager_info['email'],
                        );
                        $notifyModel->sendQYWechat($params);
                    }
                    $maintain_info = $adminBaseModel->getAdminByID($v['maintain_id']);
                    if ($maintain_info){
                        $params = array(
                            'type' => 'ach_success',
                            'content' => '您设置的'.$member_info['name'].'客户的提醒时间为今天，请您及时处理，并更新提醒时间',
                            'receive_wechatid' => $maintain_info['email'],
                        );
                        $notifyModel->sendQYWechat($params);
                    }

                }
            }
            $where['expire'] = $surplus_time;
            $update_res = DB::table('achievement_extend')->where('id',$v['id'])->update($where);
            if (!$update_res){
                Log::info('update achievement_extend fail id is' .$v['id']);
                $data['code'] = 0;
                $data['msg'] = '请求成功';
                return response()->json($data);
            }
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    //记录平台运营数据
    public function recordOperateData(){
        //月份
        $month = date('Y-m');
        $data['month'] = $month;
        $start_time = date('Y-m-01 00:00:00');
        $end_time = date('Y-m-t 23:59:59');
        //计算总客户量
        $total_customer = DB::table('customer')->whereBetween('created_at',[$start_time,$end_time])->select('source')->get();
        $total_customer = json_decode(json_encode($total_customer),true);
        $data['customer_number'] = count($total_customer);
        //总激活量
        $total_member = DB::table('member as m')->leftJoin('member_extend as me','m.id','=','me.member_id')->whereBetween('create_time',[$start_time,$end_time])->select('source')->get();
        $total_member = json_decode(json_encode($total_member),true);
        $data['member_number'] = count($total_member);
        //本月业绩
        $ach_res = DB::table('achievement')->select(DB::raw('sum(goods_money) AS total_money'))
            ->whereBetween('created_at',[$start_time,$end_time])
            ->where('status',1)
            ->where('ach_state',0)
            ->get();
        $ach_res = json_decode(json_encode($ach_res),true);
        $money = 0;
        foreach ($ach_res as $v){
            $money = $money + $v['total_money'];
        }
        $data['bonus_number'] = $money;
        //计算来源
        $sou_res = DB::table('member_source')->select('source_name')->get();
        if (!$sou_res){
            $source = [];
        }
        $sou_res = json_decode(json_encode($sou_res),true);
        foreach ($sou_res as &$v){
            $v['month_customer'] = 0;
            $v['month_member'] = 0;
            foreach ($total_customer as $c_v){
                if ($c_v['source'] == $v['source_name']){
                    $v['month_customer'] += 1;
                }
            }
            foreach ($total_member as $m_v){
                if ($m_v['source'] == $v['source_name']){
                    $v['month_member'] += 1;
                }
            }
        }
        $data['source'] = json_encode($sou_res);
        //成单
        if ($data['member_number'] == 0 && $data['customer_number'] == 0){
            $data['order_form'] = 0;
        }elseif ($data['member_number'] != 0 && $data['customer_number'] == 0){
            $data['order_form'] = $total_member;
        }else{
            $data['order_form'] = (int)(sprintf('%.2f',$data['member_number']/$data['customer_number'])*100);
        }
        //丢单
        $data['lose_order'] = 100 - $data['order_form'];
        $data['id'] = 0;
        $data['type'] = 1;
        $res = DB::table('operate_log')->where('month',$month)->where('type',1)->where('id',0)->first();
        if (!$res){
            DB::table('operate_log')->insert($data);
        }else{
            DB::table('operate_log')->where('log_id',$res->log_id)->update($data);
        }
        return response()->json($this->result);
    }

    //记录平台部门运营数据
    public function CompanyOperateData(){
        $company_res = DB::table('company')->get();
        $company_res = json_decode(json_encode($company_res),true);
        $userModel = new UserBase();
        $month = date('Y-m');
        $start_time = date('Y-m-01 00:00:00');
        $end_time = date('Y-m-t 23:59:59');
        foreach ($company_res as $v){
            $ids = $userModel->getCompanyIdList($v['id']);
            $data['month'] = $month;
            $data['id'] = $v['id'];
            $data['type'] = 2;
            //计算本月客户量
            $total_customer = DB::table('customer')->whereBetween('created_at',[$start_time,$end_time])->select('source')->whereIn('recommend',$ids)->get();
            $total_customer = json_decode(json_encode($total_customer),true);
            $data['customer_number'] = count($total_customer);
            //计算激活量
            $total_member = DB::table('member as m')->leftJoin('member_extend as me','m.id','=','me.member_id')->whereBetween('m.create_time',[$start_time,$end_time])->select('source')->whereIn('me.recommend',$ids)->get();
            $total_member = json_decode(json_encode($total_member),true);
            $data['member_number'] = count($total_member);
            //本月业绩
            $ach_res = DB::table('achievement')->select(DB::raw('sum(goods_money) AS total_money'))
                ->whereBetween('created_at',[$start_time,$end_time])
                ->whereIn('admin_users_id',$ids)
                ->where('status',1)
                ->where('ach_state',0)
                ->get();
            $ach_res = json_decode(json_encode($ach_res),true);
            $money = 0;
            foreach ($ach_res as $a_v){
                $money = $money + $a_v['total_money'];
            }
            $data['bonus_number'] = $money;
            //计算来源
            $sou_res = DB::table('member_source')->select('source_name')->get();
            if (!$sou_res){
                $data['source'] = json_encode([]);
            }else{
                $sou_res = json_decode(json_encode($sou_res),true);
                foreach ($sou_res as &$s_v){
                    $s_v['month_customer'] = 0;
                    $s_v['month_member'] = 0;
                    foreach ($total_customer as $c_v){
                        if ($c_v['source'] == $s_v['source_name']){
                            $s_v['month_customer'] += 1;
                        }
                    }
                    foreach ($total_member as $m_v){
                        if ($m_v['source'] == $s_v['source_name']){
                            $s_v['month_member'] += 1;
                        }
                    }
                }
                $data['source'] = json_encode($sou_res);
            }
            //成单
            if ($data['member_number'] == 0 && $data['customer_number'] == 0){
                $data['order_form'] = 0;
            }elseif ($data['member_number'] != 0 && $data['customer_number'] == 0){
                $data['order_form'] = $total_member;
            }else{
                $data['order_form'] = (int)(sprintf('%.2f',$data['member_number']/$data['customer_number'])*100);
            }
            //丢单
            $data['lose_order'] = 100 - $data['order_form'];
            $res = DB::table('operate_log')->where('month',$month)->where('type',2)->where('id',$v['id'])->first();
            if (!$res){
                DB::table('operate_log')->insert($data);
            }else{
                DB::table('operate_log')->where('log_id',$res->log_id)->update($data);
            }
        }
        return response()->json($this->result);
    }

    //记录平台个人运营数据
    public function personalOperateData(){
        $users_res = DB::table('admin_users as au')
            ->select('au.id')
            ->leftJoin('admin_users_extend as aue','au.id','=','aue.admin_id')
            ->where('au.status',0)
            ->where('aue.job_status',1)
            ->get();
        $users_res = json_decode(json_encode($users_res),true);
        $userModel = new UserBase();
        $month = date('Y-m');
        $start_time = date('Y-m-01 00:00:00');
        $end_time = date('Y-m-t 23:59:59');
        foreach ($users_res as $v){
            $data['month'] = $month;
            $data['id'] = $v['id'];
            $data['type'] = 3;
            //计算本月客户量
            $total_customer = DB::table('customer')->whereBetween('created_at',[$start_time,$end_time])->select('source')->where('recommend',$v['id'])->get();
            $total_customer = json_decode(json_encode($total_customer),true);
            $data['customer_number'] = count($total_customer);
            //计算激活量 正式客户
            $total_member = DB::table('member as m')
                ->select('me.source')
                ->leftJoin('member_extend as me','m.id','=','me.member_id')
                ->where('me.recommend',$v['id'])
                ->whereBetween('m.create_time',[$start_time,$end_time])
                ->get();
            $total_member = json_decode(json_encode($total_member),true);
            $data['member_number'] = count($total_member);
            //本月业绩
            $ach_res = DB::table('achievement')->select(DB::raw('sum(goods_money) AS total_money'))
                ->whereBetween('created_at',[$start_time,$end_time])
                ->where('admin_users_id',$v['id'])
                ->where('status',1)
                ->where('ach_state',0)
                ->get();
            $ach_res = json_decode(json_encode($ach_res),true);
            $money = 0;
            foreach ($ach_res as $a_v){
                $money = $money + $a_v['total_money'];
            }
            $data['bonus_number'] = $money;
            //计算来源
            $sou_res = DB::table('member_source')->select('source_name')->get();
            if (!$sou_res){
                $data['source'] = json_encode([]);
            }else{
                $sou_res = json_decode(json_encode($sou_res),true);
                foreach ($sou_res as &$s_v){
                    $s_v['month_customer'] = 0;
                    $s_v['month_member'] = 0;
                    foreach ($total_customer as $c_v){
                        if ($c_v['source'] == $s_v['source_name']){
                            $s_v['month_customer'] += 1;
                        }
                    }
                    foreach ($total_member as $m_v){
                        if ($m_v['source'] == $s_v['source_name']){
                            $s_v['month_member'] += 1;
                        }
                    }
                }
                $data['source'] = json_encode($sou_res);
            }
            //成单
            if ($data['member_number'] == 0 && $data['customer_number'] == 0){
                $data['order_form'] = 0;
            }elseif ($data['member_number'] != 0 && $data['customer_number'] == 0){
                $data['order_form'] = $total_member;
            }else{
                $data['order_form'] = (int)(sprintf('%.2f',$data['member_number']/$data['customer_number'])*100);
            }
            //丢单
            $data['lose_order'] = 100 - $data['order_form'];
            $res = DB::table('operate_log')->where('month',$month)->where('type',3)->where('id',$v['id'])->first();
            if (!$res){
                DB::table('operate_log')->insert($data);
            }else{
                DB::table('operate_log')->where('log_id',$res->log_id)->update($data);
            }
        }
        return response()->json($this->result);
    }

    /* 添加客户 */
    public function create(Request $request){
        $customer['qq'] = $request->post('qq','');
        $customer['remarks'] = $request->post('remarks','');
        $customer['name'] = $request->post('name','');
        if (!$customer['qq'] || !$customer['remarks'] || !$customer['name']){
            $data['code'] = ErrorCode::$admin_enum['fail'];
            $data['msg'] = '添加失败';
            return response()->json($data);
        }
        $res = DB::table('customer')->where('qq',$customer['qq'])->first();
        if ($res){
            $customer['name'] = $customer['name'].'(已有订单)';
        }
        $customer['status'] = 1;
        $customer['source'] = '腾讯云市场';
        $customer['created_at'] = Carbon::now()->toDateTimeString();
        $id = DB::table('customer')->insertGetId($customer);
        $assign['member_id'] = $id;
        $assign['assign_name'] = 'root';
        $assign['assign_admin'] = 'root';
        $assign['assign_admin'] = 'root';
        $assign['created_at'] = Carbon::now()->toDateTimeString();
        $assign['assign_uid'] = 1;
        $assign['assign_touid'] = 1;
        $assign['operation_uid'] = 1;
        $assign_res = DB::table('assign_log')->insert($assign);
        if(!$id || !$assign_res){
            $data['code'] = ErrorCode::$admin_enum['fail'];
            $data['msg'] = '添加失败';
            return response()->json($data);
        }
        $data['code'] = 0;
        $data['msg'] = '添加成功';
        return response()->json($data);
    }

    //云函数创建数据库
    public function createDatabase(){
        global $scf_data;
        $host = $scf_data['system']['database']['hostname'];
        $port = $scf_data['system']['database']['hostPort'];
        $database = $scf_data['system']['database']['database'];
        $username = $scf_data['system']['database']['username'];
        $password = $scf_data['system']['database']['password'];
        $target_folder = dirname(public_path());
        $target_file = $target_folder . '/vue.sql';
        if (file_exists($target_file)){
            $_sql = file_get_contents($target_file);
            $_arr = explode(';', $_sql);
            $conn = new \mysqli($host.":".$port, $username, $password);
            // 检测连接
            if ($conn->connect_error) {
                $data['code'] = 1;
                $data['msg'] = '数据库配置无效';
                $data['data'] = '';
                return response()->json($data);
            }
            if (!mysqli_select_db($conn,$database)){
                $sql = "CREATE DATABASE ".$database;
                if ($conn->query($sql) != TRUE) {
                    $data['code'] = 1;
                    $data['msg'] = '创建数据库失败';
                    $data['data'] = '';
                    return response()->json($data);
                }
            }
            //执行sql语句
            foreach ($_arr as $k=>$_value) {
                $conn->query($_value . ';');
            }
            $conn->close();
            $data['code'] = 1;
            $data['msg'] = '初始化成功';
            return response()->json($data);
        }
    }
}
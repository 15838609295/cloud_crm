<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\BonusSale;
use App\Models\Admin\Configs;
use App\Models\Member\MemberBase;
use App\Models\User\UserBase;
use Illuminate\Http\Request;
use App\Models\Admin\Achievement;
use App\Models\Admin\Salebonusrule;
use App\Models\Admin\AfterSale;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementController extends BaseController
{
    protected $fields = array(
        'goods_name' => '',
        'goods_money' => 0,
        'order_number' => '',
        'buy_time' => '',
        'buy_length' => 0,
        'after_sale_id' => 0,
        'remarks' => '',
        'sale_proof' => ''
    );

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 业绩订单列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time','')),                                          //订单创建时间(开始)
            'end_time' => trim($request->post('end_time','')),                                              //订单创建时间(结束)
            'branch_id' => trim($request->post('branch_id','')),                                            //团队ID
            'user_id' => trim($request->post('user_id','')),                                                //销售ID
            'status' => trim($request->post('status','')),                                                  //业绩订单状态
            'type' => trim($request->post('type','')),                                                      //商品类型筛选
            'admin_id' => $this->AU['id'],
            'is_del' => 0
        );
        $achievementModel = new Achievement();
        $res = $achievementModel->getAchievementList($searchFilter);
//        $res['total_money'] = $achievementModel->getAchievementTotalMoney($res['rows']);
        $res['total_month'] = $achievementModel->getTotalMoneyBydate(['user_list' => [$this->AU['id']],'date' => date('Y-m')]); //个人当月业绩
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    /* 个人业绩订单列表 */
    public function getList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $start_time = date('Y-m-d H:i:s', mktime(0,0,0,date('m'),1,date('Y'))); //当月开始时间
        $end_time = date('Y-m-d H:i:s',mktime(23,59,59,date('m'),date('t'),date('Y'))); //当月结束时间
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time',$start_time)),                                          //订单创建时间(开始)
            'end_time' => trim($request->post('end_time',$end_time)),                                              //订单创建时间(结束)
            'user_id' => trim($request->post('user_id','')),                                                //销售ID
            'status' => trim($request->post('status','')),                                                  //业绩订单状态
//            'admin_id' => $this->AU['id'],
            'is_del' => 0
        );
        $achievementModel = new Achievement();
        $res = $achievementModel->getAchievements($searchFilter);
        $res['total_money'] = $achievementModel->getAchievementTotalMoney($res['rows']);
        $res['total_month'] = $achievementModel->getTotalMoneyBydate(['user_list' => [$this->AU['id']],'date' => date('Y-m')]); //个人当月业绩
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    /* 业绩订单详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $adminUserModel = new UserBase();
        $user_list = $adminUserModel->getAdminSubuser($this->AU['id']);
        $achievementModel = new Achievement();
        $data = $achievementModel->getAchievementById($id);
        if(!is_array($data) || count($data)<1){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            $this->returnData['msg'] = '数据不存在';
            return $this->return_result($this->returnData);
        }
        if(!in_array($data['admin_users_id'],$user_list)){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            return $this->return_result($this->returnData);
        }
        $data['sale_proof'] = trim($data['sale_proof'],',');
        $data['sale_proof'] = array_values(explode(',',$data['sale_proof']));
        $res = DB::table("admin_users")->where("id", $data["verify_user_id"])->select("name")->first();
        $res = json_decode(json_encode($res),true);
        $data['verify_user_name'] = $res['name'];
        $member_info = DB::table('member as m')
            ->select('me.realname','me.company','me.qq','me.wechat','me.type','me.position','me.source')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->where('m.mobile','=',$data['member_phone'])
            ->first();
        $member_info = json_decode(json_encode($member_info),true);
        $data['company'] = $member_info['company'];
        $data['qq'] = $member_info['qq'];
        $data['wechat'] = $member_info['wechat'];
        if ($member_info['type'] == 1){
            $data['type'] = '企业';
        }else{
            $data['type'] = '个人';
        }
        if ($data['sale_proof']){
            foreach ($data['sale_proof'] as &$v){
                $v = $this->processingPictures($v);
            }
        }
        $data['position'] = $member_info['position'];
        $data['source'] = $member_info['source'];
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 业绩修改 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $achievement = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===NULL){
                continue;
            }
            $achievement[$field] = $request->post($field,$this->fields[$field]);
        }
        $achievementModel = new Achievement();
        $data = $achievementModel->getAchievementById($id);
        if(!is_array($data)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return $this->return_result($this->returnData);
        }
        if($this->AU['id']>1 && $this->AU['id'] != $data['admin_users_id']){
            $this->returnData = ErrorCode::$admin_enum['auth_fail'];
            $this->returnData['msg'] = '无权限编辑该订单';
            return $this->return_result($this->returnData);
        }
        $res = $achievementModel->achievementUpdate($id,$achievement);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return $this->return_result($this->returnData);
    }

    /* 更新数据 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['status','refuse','refund'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $id = $request->id;
        switch ($request->action){
            case 'status':
                $achievementModel = new Achievement();
                $data = $achievementModel->achievementUpdate($id,['status' => $request->status]);
                if(is_array($data)){
                    $this->returnData = ErrorCode::$admin_enum['fail'];
                    $this->returnData['msg'] = '处理失败';
                }
                return $this->return_result($this->returnData);
            case 'refuse':
                $data = $this->_refuseAchievement($request);
                return $this->return_result($data);
            case 'refund':
                $data = $this->_refundAchievement($id);
                return $this->return_result($data);
            default:
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '未知操作';
                return $this->return_result($this->returnData);
        }
    }

    /* 审核订单 */
    public function verifyRecord(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $achievementModel = new Achievement();
        $a_res = $achievementModel->getAchievementById($id);
        $a_res['verify'] = $this->AU['name'];
        if(!is_array($a_res)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return $this->return_result($this->returnData);
        }
        if(!in_array($request->after_type,[0,1,2])){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        $rule_type = 0;   //提成规则
        $order_bonus = 0;  //订单提成
        $after_money = 0;  //提成
        $bonus_money = 0;  //奖金
        $data_upd['ach_state'] = $request->ach_state;
        $data_upd['status'] = 1;
        $data_upd['sbr_id'] = $request->sbr_id;
        $data_upd['after_sale_id'] = $request->after_sale_id;
        $data_upd['order_bonus'] = $order_bonus;
        $saleRuleModel = new Salebonusrule();
        $saleRuleDetail = $saleRuleModel->getSaleRuleDetail($request->sbr_id);
        if($request->after_type==0){
            if(!is_array($saleRuleDetail)){
                $this->returnData = ErrorCode::$admin_enum['not_exist'];
                $this->returnData['msg'] = '提成规则不存在';
                return $this->return_result($this->returnData);
            }
        }
        if($request->after_type==0 && $saleRuleDetail['rule_type']==0){
            if($saleRuleDetail['cost'] > 100){
                $this->returnData = ErrorCode::$admin_enum['error'];
                $this->returnData['msg'] = '提成规则错误';
                return $this->return_result($this->returnData);
            }
            //售前提成
            $data_upd['order_bonus'] = $saleRuleModel->calculatSaleRuleBouns($request->sbr_id,$a_res['goods_money'])['pre_bonus'];
        }
        if($request->after_type==0 && $saleRuleDetail['rule_type']!=0){
            $order_bonus = $saleRuleDetail['pre_bonus'];     //售前提成
            $data_upd['order_bonus'] = $order_bonus;        //售前提成
            $userbaseModel = new UserBase();
            if($request->post('after') == 0) {   //创建售后订单则发固定提成或奖金
                if ($saleRuleDetail['type'] == 1){  //提成
                    $after_money = $saleRuleDetail['after_bonus'];  //售后提成
                    //固定金额直接增加售后提成
                    if ($after_money > 0){
                        $res = $userbaseModel->addBonus($request->after_sale_id,$after_money,$a_res);
                    }
                }else{  //奖金
                    $bonus_money = $saleRuleDetail['bonus'];         //售后奖金
                    //固定金额直接增加奖金
                    if ($bonus_money){
                        $res = $userbaseModel->addSaleBonus($request->after_sale_id,$bonus_money,$a_res);
                    }
                }
            }
        }
        if($request->after_type==2){
            //手写固定金额
            $order_bonus = $request->order_bonus;       //售前提成
            $after_money = $request->after_money;       //售后提成
            $bonus_money = $request->bonus_money;       //售后奖金
            $data_upd['order_bonus'] = $order_bonus;    //售前提成
            $rule_type = 1;
            //直接增加到售后
            $userbaseModel = new UserBase();
            if ($after_money > 0){
                $res = $userbaseModel->addBonus($request->after_sale_id,$after_money,$a_res);
            }
            if ($bonus_money > 0){
                $res = $userbaseModel->addSaleBonus($request->after_sale_id,$bonus_money,$a_res);
            }
        }
        $data_upd['verify_time'] = Carbon::now();
        $data_upd['verify_user_id'] = $this->AU['id'];
//        Log::info('update achievement :',array('id'=>$id,'data'=>$data_upd));
        $result = $achievementModel->achievementUpdate($id,$data_upd);
        if(!$result){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '同意失败';
            return $this->return_result($this->returnData);
        }
        $a_res['bonus_money'] = $bonus_money;
        $a_res['after_money'] = $after_money;
        $a_res['rule_type'] = $rule_type;
        $r_data = $this->_afterVerifyRecord($a_res,$request);
        return $this->return_result($r_data);
    }

    private function _afterVerifyRecord($data,$request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $r_data = $this->returnData;
        $achievementModel = new Achievement();
        $saleRuleModel = new Salebonusrule();
        $comm = new \App\Library\Common();
        $fields = ['au.name as admin_name','a.refuse_remarks','au.email','a.order_number','au.wechat_id'];
        $achData = $achievementModel->getAchievementWithAdminData($request->id,$fields);
        //var_dump($achData);exit();
        //创建售后订单
        if($request->post('after') == 0) {
            $data_ins['after_sale_id'] = $request->after_sale_id;
            $data_ins['member_name'] = $data['member_name'];
            $data_ins['member_phone'] = $data['member_phone'];
            $data_ins['goods_money'] = $data['goods_money'];
            $data_ins['sbr_id'] = $request->sbr_id;
            $data_ins['goods_name'] = $data['goods_name'];
            $data_ins['order_number'] = $data['order_number'];
            $data_ins['buy_time'] = $data['buy_time'];
            $data_ins['buy_length'] = $data['buy_length'];
            $data_ins['after_type'] = $request->post('after_type');
            $data_ins['after_money'] = $data['after_money'];
            $afterSaleModel = new AfterSale();
            $bonusSaleModel = new BonusSale();
            if ($request->after_type == 0 ){  //选择规则
                $saleRuleDetail = $saleRuleModel->getSaleRuleDetail($request->sbr_id);
                if ($saleRuleDetail['type'] == 1){  //提成规则
                    $data_ins['bonus_royalty'] = 0;
                    if ($data['rule_type'] == 0) {
                        $data_ins['after_money'] = $saleRuleModel->calculatSaleRuleBouns($request->sbr_id, $data['goods_money'])['after_bonus'];
                    }
                    //增加售后记录
                    $asmid = $afterSaleModel->afterSaleInsert($data_ins);
                    if (!$asmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加售后记录失败';
                        return $r_data;
                    }
                }else{    //奖金规则
                    //添加奖金记录
                    $data_ins['after_money'] = $data['bonus_money'];
                    if ($data['rule_type'] == 0) {
                        $data_ins['after_money'] = $saleRuleModel->calculatSaleRuleBouns($request->sbr_id, $data['goods_money'])['sale_bonus'];
                    }
                    $bsmid = $bonusSaleModel->afterSaleInsert($data_ins);
                    if (!$bsmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加奖金失败';
                        return $r_data;
                    }
                    //添加售后记录
                    $data_ins['after_money'] = $data['after_money'];
                    $data_ins['bonus_royalty'] = $data['bonus_money'];
                    if ($data['rule_type'] == 0) {
                        $data_ins['bonus_royalty'] = $saleRuleModel->calculatSaleRuleBouns($request->sbr_id, $data['goods_money'])['sale_bonus'];
                    }
                    $asmid = $afterSaleModel->afterSaleInsert($data_ins);
                    if (!$asmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加售后记录失败';
                        return $r_data;
                    }
                }
            }else{        //未选择规则
                if ($data['after_money'] > 0){  //有提成
                    //添加售后记录
                    $data_ins['after_money'] = $data['after_money'];
                    $data_ins['bonus_royalty'] = $data['bonus_money'];
                    $asmid = $afterSaleModel->afterSaleInsert($data_ins);
                    if (!$asmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加售后记录失败';
                        return $r_data;
                    }
                }
                if ($data['bonus_money'] > 0){  //有奖金
                    //添加奖金记录
                    $data_ins['after_money'] = $data['bonus_money'];
                    unset($data_ins['bonus_royalty']);
                    $bsmid = $bonusSaleModel->afterSaleInsert($data_ins);
                    if (!$bsmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加奖金失败';
                        return $r_data;
                    }
                }
                if (!$data['bonus_money'] && !$data['bonus_money']){
                    //增加售后记录
                    $asmid = $afterSaleModel->afterSaleInsert($data_ins);
                    if (!$asmid) {
                        $r_data = ErrorCode::$admin_enum['fail'];
                        $r_data['msg'] = '同意失败，添加售后记录失败';
                        return $r_data;
                    }
                }
            }
        }
        $configModel = new Configs();
        $config = $configModel->getConfigByID(1);
        if ($config['qywxLogin'] == 1){
            //发邮件加自动推送
            $comm->QyWechatPush($achData['wechat_id'],'订单号为'.$achData['order_number'].'的业绩订单审核成功');
        }
        $r_data['msg'] = '同意成功';
        if($request->post('after') != 0) $r_data['msg'] = '同意成功,但不创建售后订单';
        return $r_data;
    }

    /* 拒绝业绩订单 */
    private function _refuseAchievement($request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $data['status'] = 2;
        $data['refuse_remarks'] = $request->post('refuse_remarks','');
        $data['verify_time'] = Carbon::now();
        $data['verify_user_id'] = $this->AU['id'];
        $achievementModel = new Achievement();
        $res = $achievementModel->achievementUpdate($id,$data);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '处理失败';
            return $r_data;
        }
        $fields = ['au.name as admin_name','a.refuse_remarks','au.email','a.order_number','au.wechat_id'];
        $achData = $achievementModel->getAchievementWithAdminData($id,$fields);
        $configModel = new Configs();
        $config = $configModel->getConfigByID(1);
        if ($config['qywxLogin'] == 1) {
            //发邮件加自动推送
            $comm = new \App\Library\Common();
            $comm->QyWechatPush($achData['wechat_id'], '订单号为' . $achData['order_number'] . '的业绩订单审核失败，拒绝备注：' . $achData['refuse_remarks']);
        }
        return $this->returnData;
    }

    /* 退还业绩订单 */
    private function _refundAchievement($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $achievementModel = new Achievement();
        $res = $achievementModel->getAchievementById($id);
        if(!is_array($res)){
            $r_data = ErrorCode::$admin_enum['not_exist'];
            $r_data['msg'] = "订单不存在";
            return $r_data;
        }
        $res = $achievementModel->achievementUpdate($id,['status'=>3]);
        if(!$res){
            $r_data = ErrorCode::$admin_enum['fail'];
            $r_data['msg'] = '退款失败';
            return $r_data;
        }
        return $this->returnData;
    }

    /* 业绩导出 */
    public function export(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['data'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        if($request->action=='data'){
            if(trim($request->get('list',''))==''){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '勾选列表不能为空';
                return $this->return_result($this->returnData);
            }
            $list = explode(',',trim($request->get('list','')));
            $achievementModel = new Achievement();
            $maxID = $achievementModel->getMaxAchievementID();
            $page_no = $request->get('page_no', 1);
            $page_size = $request->get('page_size', 10);
            $searchFilter = array(
                'sortName' => $request->get('sortName','id'),                                               //排序列名
                'sortOrder' => $request->get('sortOrder','desc'),                                           //排序（desc，asc）
                'pageNumber' => $page_no,                                                                               //当前页码
                'pageSize' => $maxID,                                                                               //一页显示的条数
                'start' => ($page_no-1) * $page_size,                                                                   //开始位置
                'searchKey' => trim($request->get('search','')),                                            //搜索关键词
                'start_time' => trim($request->get('start_time','')),                                       //订单创建时间(开始)
                'end_time' => trim($request->get('end_time','')),                                           //订单创建时间(结束)
                'branch_id' => trim($request->get('branch_id','')),                                         //团队ID
                'user_id' => trim($request->get('user_id','')),                                             //销售ID
                'status' => trim($request->get('status','')),                                               //业绩订单状态
                'admin_id' => $this->AU['id'],
                'list' => $list,
                'type' => '',
                'is_del' => 0
            );
            $res = $achievementModel->getAchievementList($searchFilter);
            $obj = $res['rows'];
            $arr=[['ID','客户名称','手机','提交人','商品名称','商品金额','订单号','订单提成','备注(拒绝备注)','购买时间','订单状态']];
            foreach($obj as $key => $val){
                if($val['refuse_remarks']){
                    $remarks = $val['remarks'].'('.$val['refuse_remarks'].')';
                }else{
                    $remarks = $val['remarks'];
                }
                if($val['status']==0){
                    $status = '审核中';
                }else if($val['status']==1){
                    $status = '审核成功';
                }else{
                    $status = '拒绝';
                }
                $arr[] = array(
                    $val['id'],
                    $val['member_name'],
                    $val['member_phone'],
                    $val['name'],
                    $val['goods_name'],
                    $val['goods_money'],
                    $val['order_number'],
                    $val['order_bonus'],
                    $remarks,
                    $val['buy_time'],
                    $status
                );
            }
            $con = Configs::first();
            $env = $con->env;
            if ($env == 'CLOUD'){
                $temp_file = tempnam(sys_get_temp_dir(),"1xlsx");  //临时文件
                $a = Excel::create('业绩订单',function($excel) use ($arr){
                    $excel->sheet('业绩订单', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->string('xlsx');
                file_put_contents($temp_file,$a);
                $data['code'] = 3;
                $data['name'] = '业绩订单.xlsx';
                $data['data'] = $temp_file;
                return $data;
            }else{
                Excel::create('业绩订单',function($excel) use ($arr){
                    $excel->sheet('业绩订单', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->export('xlsx');
            }
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '操作失败';
        return $this->return_result($this->returnData);
    }

    /* 业绩录入 */
    public function achievementAdd(Request $request, $id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberBase();
        $info = $memberModel->getMemberList(["me.realname", "m.mobile"], [["m.id", "=", $id]]);
        $data['member_name'] = $info[0]['realname'] ?: $request->post("name", '');
        $data['member_phone'] = $info[0]['mobile'];
        $data['member_id'] = $id;
        $data['admin_users_id'] = $this->AU['id'];
        $data['goods_money'] = $request->post("goods_money", 0);
        $data['goods_name'] = $request->post("goods_name", "");
        $data['order_number'] = $request->post("order_number", 0);
        $data['sbr_id'] = 0;
        $data['after_sale_id'] = $request->post("after_sale_id", 0);
        $data['sale_proof'] = $request->post("sale_proof", 0);
        $data['remarks'] = $request->post("remarks", "");
        $data['buy_time'] = $request->post("buy_time", "");
        $data['buy_length'] = $request->post("buy_length", "");
        $data['status'] = 0;
        $achievementModel = new Achievement();
        $res = $achievementModel->achievementInsert($data);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "录入失败";
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = "录入成功";
        return $this->return_result($this->returnData);
    }

    /* 业绩转移 */
    public function exchangeOrder(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $touid = $request->input("exchange_user");
        if($touid==null){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '转移管理员不能为空';
            return $this->return_result($this->returnData);
        }
        $list = $request->input("exchange_list");
        if($list==null || $list=="0"){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '转移订单不能为空';
            return $this->return_result($this->returnData);
        }
        $list = explode(',',$list);
        foreach ($list as $k=>$v){
            if($v=="0"){
                unset($list[$k]);
            }
        }

        $achievementModel = new Achievement();
        $result = $achievementModel->getBaseAchievementList(['id'],[['id','in',$list]]);
        if(!is_array($result)){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '无权转移';
            return $this->return_result($this->returnData);
        }
        $data_list = [];
        foreach ($result as $key=>$value){
            $data_list[] = $value['id'];
        }
        $bool = $achievementModel->achievementUpdate($data_list,['admin_users_id'=>$touid]);
        if(!$bool){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return $this->return_result($this->returnData);
    }
    
    /* 业绩订单删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $achievementModel = new Achievement();
        $res = $achievementModel->achievementDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return $this->return_result($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return $this->return_result($this->returnData);
    }
}
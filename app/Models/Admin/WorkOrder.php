<?php

namespace App\Models\Admin;

use App\Library\Common;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Qcloud\Sms\SmsSingleSender;

class WorkOrder extends Model
{
    protected $table_name='work_order';
    private $work_order_info = ['wo.id','wo.description','wo.pic_list','wo.address','wo.status','wo.type_id','wo.accept_time','wo.created_at','wo.end_time'];
    private $work_order = ['wo.id','wo.description','wo.status','wo.accept_time','wo.created_at','wo.end_time'];

    /* 带筛选条件获取问题列表 */
    public function getWorkOrderListWithFilter($fields){
        $res = DB::table($this->table_name.' as wo')
            ->select('wo.id','wo.status','wo.type_id','wo.accept_time','wo.created_at','wo.end_time','m.name as member_name','m.mobile as member_mobile',
                'wot.label as type_name','wo.created_at','au.name as admin_name','au.mobile as admin_mobile','wo.description','s.name as street_name',
                'aus.name as former_admin_name','aus.mobile as former_admin_mobile')
            ->leftJoin('member as m','wo.member_id','=','m.id')
            ->leftJoin('admin_users as au','wo.admin_id','=','au.id')
            ->leftJoin('admin_users as aus','wo.former_admin_id','=','aus.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->whereNull('wo.deleted_at');
        if(isset($fields['status']) && $fields['status']!= ''){
            if ($fields['status'] == 0){
                $res->where('wo.status',0);
            }elseif ($fields['status'] == 1){
                $res->whereIn('wo.status',[1,2]);
            }elseif ($fields['status'] == 2){
                $res->where('wo.status',3);
            }
        }
        if ($fields['admin_id'] != 1){
            //查询此管理所负责的街道
            $streetModel = new Street();
            $ids = $streetModel->getAdminStreetId($fields['admin_id']);
            $res->whereIn('wo.admin_id',$ids);
        }
        if($fields['street_id']!=''){
            $res->where('wol.street_id',$fields['street_id']);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('m.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('m.mobile', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('wol.description', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        if($data['total']<1){
            $data['rows'] = [];
            return $data;
        }
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    //工单详情
    public function getWorkOrderInfo($id,$type){
        $res = DB::table($this->table_name.' as wo')
            ->select('wo.id','wo.status','wot.label as type_name','wo.created_at','wo.accept_time','wo.end_time','wo.description','wo.pic_list','wo.address',
                'st.name as son_name','s.name as father_name','m.name as member_name','m.mobile as member_mobile','au.name as admin_name','au.mobile as admin_mobile')
            ->leftJoin('member as m','wo.member_id','=','m.id')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('street as st','wo.c_street_id','=','st.id')
            ->leftJoin('admin_users as au','wo.admin_id','=','au.id')
            ->where('wo.id',$id)
            ->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if ($type != 1){
            switch ($res['status']){
                case 0;
                    $res['status_txt'] = '未签收';
                    break;
                case 3:
                    $res['status_txt'] = '已解决';
                    break;
                default:
                    $res['status_txt'] = '处理中';
            }
        }else{
            switch($res['status']) {
                case 0:
                    $res['status_txt'] = '待处理';
                    break;
                case 1;
                    $res['status_txt'] = '待处理';
                    break;
                case 2;
                    $res['status_txt'] = '已处理';
                    break;
                case 3;
                    $res['status_txt'] = '已解决';
                    break;
            }
        }
        if ($res['pic_list']){
            $res['pic_list'] = json_decode($res['pic_list'],true);
        }
        $res['work_order_log'] = $this->getWorkOrderLog($res['id'],$type);
        //查询有没有转单记录
        $transferWorkOrderModel = new TransferWorkOrder();
        $work_order_log = $transferWorkOrderModel->getWorkOrderIdLog($res['id']);
        if ($work_order_log){
            $res['change_status'] = $work_order_log['status'];
            $res['change_remarks'] = $work_order_log['change_remarks'];
            switch($res['change_status']) {
                case 1;
                    $res['change_status_txt'] = '转单，待审核';
                    break;
                case 2;
                    $res['change_status_txt'] = '转单驳回';
                    break;
                case 3;
                    $res['change_status_txt'] = '转单通过';
                    break;
            }
        }else{
            $res['change_status'] = 0;
            $res['change_remarks'] = '';
            $res['change_status_txt'] = '未转单';
        }
        return $res;
    }

    //删除工单 软删除
    public function delWorkOrderInfo($id){
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if (!$res){
            return false;
        }
        DB::beginTransaction();
        try{
            $where['deleted_at'] = Carbon::now()->toDateTimeString();
            $order_del_res = DB::table($this->table_name)->where('id',$id)->update($where);
            if (!$order_del_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollback();
            return false;
        }

    }

    //统计工单数据
    public function workOrderStatistics(){
        $data['total_order'] = DB::table($this->table_name)->whereNull('deleted_at')->count();   //总数
        $data['accept_order'] = DB::table($this->table_name)->whereNull('deleted_at')->where('status',2)->count();   //已处理
        $data['newly_added_order'] = DB::table($this->table_name)->whereNull('deleted_at')->where('created_at','>',date('Y-m-d 00:00:00',time()))->count(); //新增
        $data['complete_order'] = DB::table($this->table_name)->whereNull('deleted_at')->where('status',3)->count();  //已解决
        $all['title'] = '全部数据';
        $all['data'] = [
            [
                'name' => '已解决',
                'value' => $data['complete_order']
            ],
            [
                'name' => '处理中',
                'value' => $data['accept_order']
            ],
            [
                'name' => '待处理',
                'value' => $data['total_order'] - $data['complete_order'] - $data['accept_order']
            ]
        ];
        if ($data['total_order'] == 0 && $data['complete_order'] == 0){
            $completion_rate = 0;
        }elseif ($data['total_order'] == 0 && $data['complete_order'] != 0){
            $completion_rate = $data['complete_order'] *100;
        }else{
            $completion_rate = (int)(sprintf('%.2f',$data['complete_order']/$data['total_order'])*100).'%';
        }
        $all['list'] = [
            'member_feedback' =>$data['total_order'],     //总数
            //'admin_feedback' => $data['accept_order'],  //已解决
            //'sign_for' => $data['accept_order'],           //已处理
            'completion_rate' => $completion_rate,          //完成率
        ];
        $seven['start_time'] = date('Y-m-d 00:00:00',strtotime('-7 day'));
        $seven['end_time'] = date("Y-m-d 23:59:59",time());
        $fifteen['start_time'] = date('Y-m-d 00:00:00',strtotime('-15 day'));
        $fifteen['end_time'] = date("Y-m-d 23:59:59",time());
        $thirty['start_time'] = date('Y-m-d 00:00:00',strtotime('-30 day'));
        $thirty['end_time'] = date("Y-m-d 23:59:59",time());
        $data['chartData'][0] = $all;
        $seven_data = $this->calculation($seven);
        $fifteen_data = $this->calculation($fifteen);
        $thirty_data = $this->calculation($thirty);
        $data['chartData'][1] = $seven_data;
        $data['chartData'][1]['title'] = '七天数据';
        $data['chartData'][2] = $fifteen_data;
        $data['chartData'][2]['title'] = '十五天数据';
        $data['chartData'][3] = $thirty_data;
        $data['chartData'][3]['title'] = '三十天数据';
        return $data;
     }

    public function calculation($data){
         $total_order = DB::table($this->table_name)->whereNull('deleted_at')->whereBetween('created_at',[$data['start_time'],$data['end_time']])->count();  //总数
         $complete_order = DB::table($this->table_name)->whereNull('deleted_at')->whereBetween('created_at',[$data['start_time'],$data['end_time']])->where('status',3)->count();  //完成
         $accept_order = DB::table($this->table_name)->whereNull('deleted_at')->whereBetween('created_at',[$data['start_time'],$data['end_time']])->where('status',2)->count();  //已处理
         $list['data'] = [
             [
                 'name' => '已解决',
                 'value' => $complete_order
             ],
             [
                 'name' => '处理中',
                 'value' => $accept_order
             ],
             [
                 'name' => '待处理',
                 'value' => $total_order - $complete_order - $accept_order
             ]
         ];
         if ($complete_order != 0 && $total_order != 0){
             $completion_rate = (int)(sprintf('%.2f',$complete_order/$total_order)*100).'%';
         }elseif ($complete_order != 0 && $total_order == 0){
             $completion_rate = ($complete_order*100).'%';
         }else{
             $completion_rate = '0%';
         }
         $list['list'] = [
             'member_feedback' =>$total_order,
             //'admin_feedback' => $accept_order,
             //'sign_for' => $accept_order,
             'completion_rate' => $completion_rate
         ];
         return $list;
     }

     //反馈问题标签
    public function getFeedbackList($fields){
         if (!$fields){
             $res = DB::table('work_order_type')->whereNull('deleted_at')->get();
             if(!$res){
                 return false;
             }
             $res = json_decode(json_encode($res),true);
             return $res;
         }else{
             $res = DB::table('work_order_type')->whereNull('deleted_at');
             $data['total'] = $res->count();
             $result = $res->skip($fields['start'])->take($fields['pageSize'])->get();
             if(!$result){
                 return false;
             }
             $data['rows'] = json_decode(json_encode($result),true);
             return $data;
         }
    }

    //添加问题
    public function addFeedback($name){
        $data['label'] = $name;
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('work_order_type')->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改反馈类型
    public function updateFeedback($fields,$id){
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('work_order_type')->where('id',$id)->update($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除反馈标签
    public function delFeedbackId($id){
        $where['deleted_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('work_order_type')->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    /* 添加工单 */
    public function workOrderInsert($data,$user){
        $streetModel = new Street();
        $data['member_name'] = DB::table('admin_users')->where('id',$user['id'])->value('name');
        $data['status'] = 0;
//        $data['status'] = 1;
        $data['member_phone'] = $user['mobile'];
        $data['member_id'] = $user['id'];
        $data['admin_id'] = $streetModel->getadminId($data['c_street_id']);
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $work_id = DB::table('work_order')->insertGetId($data);
        if(!$work_id){
            return false;
        }
        //发送通知
//        $member_type = 1;
        $admin_type = 4;
//        $this->sendSns($member_type,$work_id);
//        $this->sendSns($admin_type,$work_id);
//        $this->sendWechatPush($member_type,$work_id);
//        $this->sendWechatPush($admin_type,$work_id);
        return true;
    }

    //根据客户id获取反馈
    public function getMyFeedbackList($fields){
        $res = DB::table($this->table_name.' as wo')
            ->select('wo.id','wot.label as type_name','wo.created_at','wo.description','wo.status','wo.address')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('street as st','wo.c_street_id','=','st.id')
            ->whereNull('wo.deleted_at')
            ->where('wo.member_id',$fields['id']);
        $data['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy('id','desc')->get();
        if (!$result){
            return false;
        }
        $data['rows'] = json_decode(json_encode($result),true);
        foreach ($data['rows'] as &$v){
            if ($v['status'] == 0){
                $v['status_txt'] = '待签收';
            }else if($v['status'] == 1){
                $v['status_txt'] = '已签收，待处理';
            }elseif ($v['status'] == 3){
                $v['status_txt'] = '已解决';
            }else{
                $v['status_txt'] = '已处理，待确认';
            }
        }
        return $data;
    }

    //管理员 工单列表
    public function getAdminFeedbackList($fields){
        //查询管理负责的街道
        $streetModel = new Street();
        $ids = $streetModel->getAdminStreetId($fields['admin_id']);
        array_push($ids,$fields['admin_id']);
        $res = DB::table($this->table_name)
            ->select('id','description','created_at','address','status')
            ->whereIn('admin_id',$ids)
            ->whereNull('deleted_at')
            ->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $list = [];
        foreach ($res as $v){
            if (isset($fields['type']) && $fields['type'] != ''){
                $log_res = DB::table('transfer_work_order')->where('work_order_id',$v['id'])->orderBy('created_at','desc')->first();
                $log_res = json_decode(json_encode($log_res),true);
                switch ((int)$fields['type']){
                    case 0;
                        if ($log_res){
                            if ($log_res['status'] == 2 || $log_res['status'] == 3){
                                if ($v['status'] == 0){
                                    $list[] = $v;
                                }
                            }
                        }else{
                            if ($v['status'] == 0){
                                $list[] = $v;
                            }
                        }
                        break;
                    case 1;
                        if ($log_res){
                            if ($log_res['status'] == 2 || $log_res['status'] == 3){
                                if ($v['status'] ==  1 || $v['status'] ==  2){
                                    $list[] = $v;
                                }
                            }
                        }else if ($v['status'] == 1 || $v['status'] ==  2){
                            $list[] = $v;
                        }
                        break;
                    case 2;
                        if ($log_res){
                            if ($log_res['status'] == 2 || $log_res['status'] == 3 || $log_res['status']== 4){
                                if ($v['status'] ==  3){
                                    $list[] = $v;
                                }
                            }
                        }else if ($v['status'] == 3){
                            $list[] = $v;
                        }
                        break;
                }
            }
        }

        $data['total'] = count($list);
        $data['rows'] = $pagedata=array_slice($list,$fields['start'],$fields['pageSize']);
        $last_names = array_column($data['rows'],'id');
        array_multisort($last_names,SORT_DESC,$data['rows']);
        if (!$data['rows']){
            return false;
        }
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        foreach ($data['rows'] as &$v){
            switch($v['status']) {
                case 0:
                    $v['status_txt'] = '待签收';
                    break;
                case 1;
                    $v['status_txt'] = '待处理';
                    break;
                case 2;
                    $v['status_txt'] = '处理中';
                    break;
                case 3;
                    $v['status_txt'] = '已解决';
                    break;
            }
        }
        return $data;
    }

    //签收工单
    public function acceptWorkOrder($id,$admin_id){
        //判断此工单是否属于此管理员
        $order_data = DB::table($this->table_name)
            ->select('id','admin_id')
            ->where('id',$id)
            ->first();
        if (!$order_data){
            return false;
        }
        $order_data = json_decode(json_encode($order_data),true);
        if ($order_data['admin_id'] != $admin_id){
            return -1;
        }
        $data['status'] = 1;
        $data['accept_time'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

//    //管理提处理工单
//    public function completeWorkOrder($fields,$id,$admin_id){
//        //判断此工单是否属于此管理员
//        $order_data = DB::table($this->table_name.' as wo')
//            ->select('wol.c_street_id')
//            ->leftJoin('work_order_log as wol','wo.log_id','=','wol.id')
//            ->where('wo.id',$id)
//            ->first();
//        if (!$order_data){
//            return false;
//        }
//        $order_data = json_decode(json_encode($order_data),true);
//        $streetModel = new Street();
//        $this_admin_id = $streetModel->getadminId($order_data['c_street_id']);
//        if ($this_admin_id != $admin_id){
//            return -1;
//        }
//        $fields['end_time'] = Carbon::now()->toDateTimeString();
//        $fields['status'] = 2;
//        $res = DB::table($this->table_name)->where('id',$id)->update($fields);
//        if (!$res){
//            return false;
//        }
//        return true;
//    }

    //发送通知
    public function sendSns($type,$id){
        $con = Configs::first();
        require_once  base_path().'/vendor/qcloudsms_php-master/src/index.php';
        $workOrder_info = DB::table($this->table_name)->where('id',$id)->select('street_id','member_id','admin_id')->first();
        if (!$workOrder_info){
            return false;
        }
        $workOrder_info = json_decode(json_encode($workOrder_info),true);
        $member_info = DB::table('member')->where('id',$workOrder_info['member_id'])->select('mobile')->first();
        $member_info = json_decode(json_encode($member_info),true);
        $admin_users = DB::table('admin_users')->where('id',$workOrder_info['admin_id'])->select('mobile')->first();
        $admin_users = json_decode(json_encode($admin_users),true);

        //获取工单负责人的信息
        $streetModel = new Street();
        $duty_id = $streetModel->getadminId($workOrder_info['street_id']);
        $duty_mobile = [];
        if ($duty_id){
            $ids = explode(',',$duty_id);
            foreach ($ids as $v){
                $duty_admin_user = DB::table('admin_users')->where('id',$v)->select('mobile')->first();
                $duty_admin_user = json_decode(json_encode($duty_admin_user),true);
                $duty_mobile[] = $duty_admin_user['mobile'];
            }
        }

        // 短信应用SDK AppID
        $appid = $con->sms_appid; // 1400开头
        // 短信应用SDK AppKey
        $appkey = $con->sms_appkey;
        switch ($type) {
            case 1:                            //反馈成功
                // 指定模板ID单发短信
                $templateId = '424231';
                // 需要发送短信的手机号码
                $phoneNumbers = [$member_info['mobile']];
                break;
            case 2:                             //签收成功
                // 指定模板ID单发短信
                $templateId = '424234';
                // 需要发送短信的手机号码
                $phoneNumbers = [$member_info['mobile']];
                break;
            case 3:                               //已处理
                // 指定模板ID单发短信
                $templateId = '424237';
                // 需要发送短信的手机号码
                $phoneNumbers = [$member_info['mobile']];
                break;
            case 4:                             //待签收
                // 指定模板ID单发短信
                $templateId = '424243';
                // 需要发送短信的手机号码
                $phoneNumbers = [$admin_users['mobile']];
                break;
            case 5:                               //签收成功
                // 指定模板ID单发短信
                $templateId = '424247';
                // 需要发送短信的手机号码
                $phoneNumbers = [$admin_users['mobile']];
                break;
            case 6:                                //处理成功
                // 指定模板ID单发短信
                $templateId = '424248';
                // 需要发送短信的手机号码
                $phoneNumbers = [$admin_users['mobile']];
                break;
            case 7:                                //处理成功,给负责人发送短信
                // 指定模板ID单发短信
                $templateId = '424248';
                // 需要发送短信的手机号码
                $phoneNumbers = $duty_mobile;
                break;
            default:
                $templateId = '';
                $phoneNumbers = '';
                break;
        }

        // 签名
        $smsSign = "梅城街长制";
        $ssender = new SmsSingleSender($appid, $appkey);
        $code_info = [date('Y-m-d H:i:s',time())];
        if ($type == 7){
            if ($phoneNumbers){
                foreach ($phoneNumbers as $mobile){
                    $result = $ssender->sendWithParam("86", $mobile, $templateId,$code_info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
                }
            }else{
                $result = true;
            }
        }else{
            $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,$code_info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        }
//        $result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,$code_info, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        $rsp = json_decode($result,true);
        if ($rsp['result'] == 0){
            return true;
        }else{
            return false;
        }
    }

    /* 添加问题记录 */
    public function workOrderLogInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $res_id = DB::table('work_order_log')->insertGetId($data);
        if(!$res_id){
            return false;
        }
        return $res_id;
    }

    /* 更新问题 */
    public function workOrderUpdate($id,$data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /*
     * $type 推送类型
     * $id   工单id
     * */
    public function sendWechatPush($type,$id){
        $content['type'] = $type;
        $con = Configs::first();
        $workOrder_info = DB::table($this->table_name)->where('id',$id)->select('member_id','accept_time','end_time','type_id','admin_id')->first();
        if (!$workOrder_info){
            return false;
        }
        $workOrder_info = json_decode(json_encode($workOrder_info),true);

        $type_name = DB::table('work_order_type')->where('id',$workOrder_info['type_id'])->value('label');
        $work_log = DB::table('work_order_log')->where('log_id',$workOrder_info['log_id'])->orderBy('id','desc')->first();
        $work_log = json_decode(json_encode($work_log),true);
        $admin_users = DB::table('admin_users')->where('id',$workOrder_info['admin_id'])->select('mobile','openid')->first();
        $admin_users = json_decode(json_encode($admin_users),true);
        $member_users = DB::table('member')->where('id',$workOrder_info['member_id'])->first();
        $member_users = json_decode(json_encode($member_users),true);
        $common = new Common();
        $openid = '';
        switch ($type) {
            case 1:   //客户 - 反馈成功
                //获取fromid
                $openid = $member_users['openid'];
                $content['content']     = $workOrder_info['description'];
                $content['time']        = $workOrder_info['created_at'];
                $content['member_name'] = $member_users['member_name'];
                $content['form_id']     = $this->getFormId($openid);
                break;
            case 2:  //客户 - 受理通知
                $openid = $member_users['openid'];
                $content['status']      = '已受理';
                $content['type_name']   = $workOrder_info['accept_time'];
                $content['admin_name']  = $admin_users['name'];
                $content['form_id']     = $this->getFormId($openid);
                break;
            case 3:   //客户 - 反馈结果
                $openid = $member_users['openid'];
                $content['time']     = $workOrder_info['created_at'];
                $content['content']  = $workOrder_info['description'];
                $content['status']   = '已处理';
                $content['end_time'] = $workOrder_info['end_time'];
                $content['remarks']  = $work_log['remarks'];
                $content['form_id']  = $this->getFormId($openid);
                break;
            case 4:   //管理 - 工单待处理
                $openid = $admin_users['openid'];
                $content['content']    = $workOrder_info['description'];
                $content['time']       = $workOrder_info['created_at'];
                $content['type_name']  = $type_name;
                $content['form_id']    = $this->getFormId($openid);
                break;
            case 5:   //管理 - 受理通知
                $openid = $admin_users['openid'];
                $content['member_name'] = $workOrder_info['accept_time'];
                $content['created_at']  = $workOrder_info['description'];
                $content['status']      = '已签收';
                $content['form_id']     = $this->getFormId($openid);
                break;
            case 6:   //管理 - 反馈结果
                $openid = $admin_users['openid'];
                $content['member_name'] = $workOrder_info['end_time'];;
                $content['content']     = $workOrder_info['accept_time'];
                $content['status']      = '已处理';
                $content['result']      = $workOrder_info['remarks'];;
                $content['type_name']   = $type_name;
                $content['form_id']     = $this->getFormId($openid);
                break;
        }
        $common->WechatPush($openid,$content);
        return true;
    }

    //获取form_id
    public function getFormId($openid){
        $member_info = DB::table('member')->where('openid',$openid)->select('id')->first();
        $member_info = json_decode(json_encode($member_info),true);
        $total = DB::table('form_id')->where('form_user','member_'.$member_info['id'])->where('is_used',0)->get();
        $total = json_decode(json_encode($total),true);
        if (count($total) < 1){
            return false;
        }
        $formId = DB::table('form_id')->where('form_user','member_'.$member_info['id'])->where('is_used',0)->first();
        $formId = json_decode(json_encode($formId),true);
        if ($formId['form_id'] == ''){
            $where['is_used'] = 1;
            DB::table('form_id')->where('id',$formId['id'])->update($where);
            $this->getFormId($openid);
        } elseif ($formId['form_id'] == 'the formId is a mock one'){
            $where['is_used'] = 1;
            DB::table('form_id')->where('id',$formId['id'])->update($where);
            $this->getFormId($openid);
        }else{
            $where['is_used'] = 1;
            DB::table('form_id')->where('id',$formId['id'])->update($where);
            return $formId['form_id'];
        }
    }

    //工单导出信息
    public function getWorkOrderListInfo($ids,$type = null){
        $res = DB::table($this->table_name.' as wo')
            ->select('wo.id','wo.status','wot.label as type_name','wo.created_at','wo.accept_time','wo.end_time','wo.description','wo.address',
                'st.name as street_son_name','s.name as street_father_name','m.name as member_name',
                'm.mobile as member_mobile','au.name as admin_name', 'au.mobile as admin_mobile')
            ->leftJoin('member as m','wo.member_id','=','m.id')
            ->leftJoin('admin_users as au','wo.admin_id','=','au.id')
            ->leftJoin('work_order_type as wot','wo.type_id','=','wot.id')
            ->leftJoin('street as s','wo.street_id','=','s.id')
            ->leftJoin('street as st','wo.c_street_id','=','st.id')
            ->whereIn('wo.id',$ids)
            ->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        foreach ($res as &$v){
            switch ($v['status']){
                case 0:
                    $v['status'] = '待签收';
                    break;
                case 1:
                    $v['status'] = '待处理';
                    break;
                case 2:
                    $v['status'] = '处理中';
                    break;
                case 3:
                    $v['status'] = '已解决';
                    break;
            }
        }
        if ($type == 1){
            foreach ($res as &$v){
                $v['work_log'] = $this->getWorkOrderLog($v['id'],0);
            }
        }
        return $res;
    }

    //工单统计查询 $type 有无分页，无分页为导出信息
    public function selectTime($fields,$type){
        $end_time = strtotime($fields['end_time']);
        $start_time = (strtotime($fields['start_time']));
        //总条数
        $data['total'] = (($end_time +1) - $start_time)/86400;
        if ($type == 1){
            $new_end_time = date('Y-m-d 23:59:59',($end_time - ($fields['start'] * 86400)));
            $list_len = (strtotime($fields['end_time']) +1 - strtotime($fields['start_time']))/86400;
            if ($list_len < $fields['pageSize']){
                $new_start_time = date('Y-m-d 00:00:00',(strtotime($new_end_time) - ($list_len * 86400) +1));
            }else{
                $new_start_time = date('Y-m-d 00:00:00',(strtotime($new_end_time) - ($fields['pageSize'] * 86400) +1));
            }
        }else{
            $new_end_time = $fields['end_time'];
            $new_start_time = $fields['start_time'];
        }

        $list_len = (strtotime($new_end_time) +1 - strtotime($new_start_time))/86400;
        $time = strtotime($new_start_time);
        //以天为键名创建数组
        $list = [];
        for ($i = 0;$i < $list_len;$i++){
            $today = date('Ymd',($time + ($i * 86400)));
            $list[$today]['time'] = date('Y-m-d',($time + ($i * 86400)));
            $list[$today]['total'] = 0;         //总条数
            $list[$today]['accept'] = 0;        //已处理
            $list[$today]['solve'] = 0;         //已解决
            $list[$today]['untreated'] = 0;    //待处理
        }
        $res = DB::table($this->table_name)->whereNull('deleted_at')->whereBetween('created_at',[$new_start_time,$new_end_time])->get();
        $res = json_decode(json_encode($res),true);
        //向数组补充数据
        foreach ($res as $k=>$v){
            $created_at = str_replace('-','',substr($v['created_at'], 0, 10));
            if (isset($list[$created_at])){
                $list[$created_at]['total'] = $list[$created_at]['total'] + 1;
                if ($v['status'] == 1){
                    $list[$created_at]['untreated'] = $list[$created_at]['untreated'] + 1;
                }else if ($v['status'] == 2){
                    $list[$created_at]['accept'] = $list[$created_at]['accept'] + 1;
                }else if ($v['status'] == 3){
                    $list[$created_at]['solve'] = $list[$created_at]['solve'] + 1;
                }
            }
        }
        //计算完成率
        foreach ($list as &$v){
            if ($v['total'] != 0 && $v['solve'] != 0){
                $v['solve_rate'] = sprintf('%.2f',(($v['solve']/$v['total'])*100));
            }elseif ($v['total'] == 0 && $v['solve'] != 0){
                $v['solve_rate'] = $v['solve']*100;
            }else{
                $v['solve_rate'] =0;
            }
        }
        $new_list = array_values($list);
        $data['rows'] = array_reverse($new_list);
        return $data;
    }

    //根据工单id获取处理记录
    public function getWorkOrderLog($id,$type){
        $work_order_log = DB::table('work_order_log')->where('log_id',$id)->orderBy('created_at','asc')->get();
        if ($work_order_log){
            $work_order_log = json_decode(json_encode($work_order_log),true);
            foreach ($work_order_log as &$v){
                if (!is_array($v['annex'])){
                    $v['annex'] = json_decode($v['annex'],true);
                }
                if ($v['type'] == 1){  //客户信息
                    $name = DB::table('member')->select('name','mobile')->where('id',$v['u_id'])->first();
                    $name = json_decode(json_encode($name),true);
                    $v['name'] = $name['name'];
                    if ($type == 1){
                        $v['type_txt'] = '用户补充';
                    }else{
                        $v['type_txt'] = '补充';
                    }
                }else{
                    $name = DB::table('admin_users')->select('name','mobile')->where('id',$v['u_id'])->first();
                    $name = json_decode(json_encode($name),true);
                    $v['name'] = $name['name'];
                    if ($type == 1){
                        $v['type_txt'] = '管理反馈';
                    }else{
                        $v['type_txt'] = '答复';
                    }
                }
            }
            return $work_order_log;
        }else{
            return [];
        }
    }

    //添加工单记录信息
    public function addWorkOrderLog($fields){
        DB::beginTransaction();
        try{
            $fields['created_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table('work_order_log')->insert($fields);
            if (!$res){
                DB::rollback();
                return false;
            }
//            $work_res = DB::table($this->table_name)->where('id',$fields['log_id'])->first();
//            $work_res = json_decode(json_encode($work_res),true);
//            if ($work_res['status'] != 2){  //管理提交反馈 修改工单状态
                $where['status'] = 3;  //管理提交反馈 工单即结束
//                $where['accept_time'] = Carbon::now()->toDateTimeString();
                $where['end_time'] = Carbon::now()->toDateTimeString();
                $work_order_res = DB::table($this->table_name)->where('id',$fields['log_id'])->update($where);
                if (!$work_order_res){
                    DB::rollback();
                    return false;
                }
//            }
            DB::commit();
            return true;
        }
        catch (\ErrorException $e){
            DB::rollback();
            return false;
        }
    }

    //结束工单
    public function endWorkOrder($id){
        DB::beginTransaction();
        try{
            $where['status'] = 3;
            $where['end_time'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->table_name)->where('id',$id)->update($where);
            if (!$res){
                DB::rollback();
                return false;
            }
            //查询有没有转单待审核的记录
            $change_log = DB::table('transfer_work_order')->where('work_order_id',$id)->where('status',1)->first();
            if ($change_log){
                $change_status['status'] = 4;
                $change_res = DB::table('transfer_work_order')->where('work_order_id',$id)->update($change_status);
                if (!$change_res){
                    DB::rollback();
                    return false;
                }
            }
            DB::commit();
            return true;
        }
        catch (\ErrorException $e){
            DB::rollback();
            return false;
        }
    }





























}

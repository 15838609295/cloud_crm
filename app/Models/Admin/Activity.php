<?php

namespace App\Models\Admin;

use App\Library\Common;
use App\Library\WxpayService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Activity extends Model
{
    protected $table='activity';   //活动表
    protected $table_name='activity_type';   //活动类型表

    //获取活动类型列表
    public function getActivityType(){
        $type_list = DB::table($this->table_name)->get();
        $type_list = json_decode(json_encode($type_list),true);
        if ($type_list){
            return $type_list;
        }else{
            return [];
        }
    }

    //获取活动名称列表
    public function getActivityTypeName($id){
        $name = DB::table($this->table_name)->where('id',$id)->select('name')->first();
        $name = json_decode(json_encode($name),true);
        if ($name){
            return $name['name'];
        }else{
            return [];
        }
    }

    //修改活动类型
    public function updateActivityType($data){
        $res = '';
        if ($data['type'] == 1){  //增
            $count = DB::table($this->table_name)->count();
            if ($count >= 20){
                $a = -1;
                return $a;
            }
            $where['name'] = $data['name'];
            $where['created_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->table_name)->insert($where);
        }elseif ($data['type'] == 2){  //修改
            $where['name'] = $data['name'];
            $where['updated_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->table_name)->where('id',$data['id'])->update($where);
        }elseif ($data['type'] == 3){   //删除
            $res = DB::table($this->table_name)->delete($data['id']);
            //当存在有活动的活动类型删除时修改为默认第一个活动类型
            $result = DB::table($this->table)->where('activity_type_id',$data['id'])->get();
            $result = json_decode(json_encode($result),true);
            if ($result){
                $type_id = DB::table($this->table_name)->select('id')->first();
                $type_id = json_decode(json_encode($type_id),true);
                foreach ($result as $v){
                    $v['activity_type_id'] = $type_id['id'];
                    DB::table($this->table)->where('id',$v['id'])->update($v);
                }
            }
        }
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //添加活动
    public function addActivity($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['activity_status'] = 1;
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

//    //复制活动
//    public function copyActivity($data){
//        $data['updated_at'] = Carbon::now()->toDateTimeString();
//        $res = DB::table($this->table)->where('id',$id)->update($data);
//        if ($res){
//            return true;
//        }else{
//            return false;
//        }
//    }

    //取消活动
    public function cancelActivity($id,$data){
        $res = DB::table($this->table)->where('id',$id)->first();
        //活动在进行中 发送通知
        $res = json_decode(json_encode($res),true);
        if ($res['end_time'] > substr(Carbon::now()->toDateTimeString(),0,10).' 00:00:00'){
            $attend_ids = DB::table('attend as a')
                ->leftJoin('member as m','a.member_id','=','m.id')
                ->select('a.id','a.created_at','m.mobile','a.openid','a.member_id')
                ->where('a.activity_id',$id)->whereIn('a.status',[1,2])->get();
            $attend_ids = json_decode(json_encode($attend_ids),true);
            if ($attend_ids){  //如果存在已报名和报名成功的
                $sendWX = new Common();
                //发送微信通知
                $send_info_sns = [
                    'start_time'   => $res['start_time'],
                    'name'          => $res['name'],
                    'cause'           => $data['cause'],
                    'host_contact' => $res['host_contact'],
                    'type'          => 1
                ];
                $send_info_We = [
                    'name'          => $res['name'],
                    'place'         => $res['place'],
                    'start_time'   => $res['start_time'],
                    'cause'         => $data['cause'],
                    'host_party'   => $res['host_party'],
                    'host_contact' => $res['host_contact'],
                    'type'          => 1
                ];
                if ($res['notice'] == 1){
                    foreach ($attend_ids as $v){
                        $send_info_We['form_id'] = $this->getFormId($v['member_id']);
                        $sendWX->WechatPush($v['openid'],$send_info_We);
                    }
                }elseif ($res['notice'] == 2){  //短信通知
                    foreach ($attend_ids as $v){
                       $sendWX->sendSNS($v['mobile'],$send_info_sns);
                    }
                }elseif ($res['notice'] == 3){   //微信和短信通知
                    foreach ($attend_ids as $v){
                        //发送短信通知
                        $sendWX->sendSNS($v['mobile'],$send_info_sns);
                        //发送微信通知
                        $send_info_We['form_id'] = $this->getFormId($v['member_id']);
                        $sendWX->WechatPush($v['openid'],$send_info_We);
                    }
                }
                //退款
                if ($res['cost'] > 0){
                    $mchid = 'wx0fa2777491d1f633';                    //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
                    $appid = '1445622102';                             //微信支付申请对应的公众号的APPID
                    $apiKey = '523136f7c52a452748cac685a29164f6';    //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
                    $wxPay = new WxpayService($mchid,$appid,$apiKey);
//            $orderNo = '';                                        //商户订单号（商户订单号与微信订单号二选一，至少填一个）
                    $refundNo = rand(11111,99999);                      //退款订单号(可随机生成)
                    foreach ($attend_ids as $v){
                        $wxOrderNo = $v['out_trade_no'];            //微信订单号（商户订单号与微信订单号二选一，至少填一个）
                        $totalFee = $v['money'];                       //订单金额，单位:元
                        $refundFee = $v['money'];                      //退款金额，单位:元
                        $result = $wxPay->doRefund($totalFee, $refundFee, $refundNo, $wxOrderNo);
                        if($result === true){
                            $menber_attend['status'] = 4;
                            $menber_attend['updated_at'] = Carbon::now()->toDateTimeString();
                            DB::table('attend')->where('id',$v['id'])->update($menber_attend);
                        }
                    }
                }
                $update_data['activity_status'] = 0;
                $update_data['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table($this->table)->where('id',$id)->update($update_data);
                return true;
            }
        }
        if ($res){
            $where['activity_status'] = 0;
            $where['updated_at'] = Carbon::now()->toDateTimeString();
            DB::table($this->table)->where('id',$id)->update($where);
            return true;
        }else{
            return false;
        }
    }

    //删除活动
    public function delActivity($id){
        $where['deleted_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //活动列表
    public function getActivityList($data){
        $res = DB::table($this->table)->whereNull('deleted_at')->select('id','name','start_time','end_time','host_party','picture','explain','place','updated_at','activity_status');
        if ($data['activity_type'] != ''){
            $res->where('activity_type_id',$data['activity_type']);
        }
        if ($data['status'] != ''){
            $res->where('activity_status',$data['status']);
        }
        if ($data['start_time'] !='' && $data['end_time'] != ''){
            $res->whereBetween('start_time',[$data['start_time'],$data['end_time']]);
        }elseif ($data['start_time'] != '' && $data['end_time'] == ''){
            $res->where('start_time','>=',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] != ''){
            $res->where('start_time','=<',$data['end_time']);
        }
        if ($data['searchKey'] != ''){
            $res->where('name', 'LIKE', '%' . $data['searchKey'] . '%');
        }
        $list['total'] = $res->count();
        $list['rows'] = [];
        $result = $res->skip($data['start'])->take($data['pageSize'])->orderBy($data['sortName'], $data['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $list;
        }
        $list['rows'] = $result;
        return $list;
    }

    //活动详情
    public function getActivityInfo($id){
        $res = DB::table($this->table.' as a')
            ->leftJoin('activity_type as at','a.activity_type_id','=','at.id')
            ->select('a.*','at.name as activity_type_name')
            ->where('a.id',$id)
            ->first();
        if ($res){
            $res = json_decode(json_encode($res),true);
            return $res;
        }
        return false;
    }

    //评论列表
    public function getCommentList($data){
        $res = DB::table('comment')->select('*')->where('activity_id',$data['activityId']);
        if ($data['status'] !=''){
            if ($data['status'] == 1){
                $res->whereNotNull('picture');
            }else{
                $res->whereNull('picture');
            }
        }
        if ($data['startTime'] !== '' && $data['endTime'] != ''){
            $res->whereBetween('created_at',[$data['startTime'],$data['endTime']]);
        }elseif ($data['startTime'] != '' && $data['endTime'] == ''){
            $res->where('created_at','>=',$data['startTime']);
        }elseif ($data['startTime'] == '' && $data['endTime'] != ''){
            $res->where('created_at','=<',$data['endTime']);
        }

        if ($data['searchKey'] !=''){
            $searchKey = $data['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('c.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.content', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $list['total'] = $res->count();
        $result = $res->skip($data['start'])->take($data['pageSize'])->orderBy($data['sortName'], $data['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $list['rows'] = $result;
        return $list;
    }

    //审核评论
    public function checkComment($data){
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('comment')->where('id',$data['id'])->first();
        if ($res){
            DB::table('comment')->where('id',$data['id'])->update($data);
            return true;
        }
        return false;
    }

    //客户与活动的关系  报名 点赞 收藏
    public function relation($id,$activity_id){
        $data = [];
        //报名状态
        $attend_1 = DB::table('attend')->where('member_id',$id)->where('status',1)->where('activity_id',$activity_id)->first();
        if ($attend_1){
            $data['sign_status'] = 1;  //待审核
        }else{
            $attend_2 = DB::table('attend')->where('member_id',$id)->where('status',2)->where('activity_id',$activity_id)->first();
            if ($attend_2){
                $data['sign_status'] = 2;  //报名成功
            }else{
                $data['sign_status'] = 0;  //没有报名
            }
        }
        //点赞状态
        $fabulous = DB::table('fabulous')->where('member_id',$id)->where('activity_id',$activity_id)->first();
        if ($fabulous){
            $data['fabulous_status'] = 2; //已点赞
        }else{
            $data['fabulous_status'] = 1; //未点赞
        }
        //收藏状态
        $collect = DB::table('collect')->where('member_id',$id)->where('activity_id',$activity_id)->first();
        if ($collect){
            $data['collect_status'] = 2;  //已收藏
        }else{
            $data['collect_status'] = 1; //未收藏
        }
        return $data;
    }

    //获取活动状态
    public function getActivityStatus($activity_id){
        $res = DB::table($this->table)->where('id',$activity_id)->select('activity_status')->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //取消活动
    public function cancelSignUp($id,$activity_id){
        $member_info = DB::table('attend')->where('activity_id',$activity_id)->where('member_id',$id)->first();
        $member_info = json_decode(json_encode($member_info),true);
        if ($member_info['status'] == 2){
            //客户审核通过退款流程
            $res = DB::table($this->table)->where('id',$activity_id)->first();
            $res = json_decode(json_encode($res),true);
            $now_time = Carbon::now()->toDateTimeString();
            $surplus = strtotime($now_time) - strtotime($res['created_at'])/3600;
            if ($surplus <= 24){
                //活动开始时间小于24小时 不可取消报名
                return -1;
            }
        }
        if ($member_info['money'] > 0){
            //如果活动收费 需退回活动费用
            $mchid = 'wx0fa2777491d1f633';                    //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
            $appid = '1445622102';                             //微信支付申请对应的公众号的APPID
            $apiKey = '523136f7c52a452748cac685a29164f6';    //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
            $wxPay = new WxpayService($mchid,$appid,$apiKey);
//           $orderNo = '';                                        //商户订单号（商户订单号与微信订单号二选一，至少填一个）
            $refundNo = rand(11111,99999);                      //退款订单号(可随机生成)
            $wxOrderNo = $member_info['out_trade_no'];            //微信订单号（商户订单号与微信订单号二选一，至少填一个）
            $totalFee = $member_info['money'];                       //订单金额，单位:元
            $refundFee = $member_info['money'];                      //退款金额，单位:元
            $result = $wxPay->doRefund($totalFee, $refundFee, $refundNo, $wxOrderNo);
            if($result === true){
                $menber_attend['status'] = 5;
                $menber_attend['updated_at'] = Carbon::now()->toDateTimeString();
                $res = DB::table('attend')->where('id',$member_info['id'])->update($menber_attend);
                if ($res){
                    //退款成功 取消成功
                    return 1;
                }else{
                    //取消失败
                    return -3;
                }
            }else{
                //退款失败
                return -2;
            }
        }
        //取消报名状态
        $where['status'] = 5;
        $res = DB::table('attend')->where('id',$member_info['id'])->update($where);
        if ($res){
            //取消报名成功
            return 1;
        }else{
            //取消失败
            return -3;
        }
    }

    //用户点赞
    public function spotFabulous($id,$activity_id){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['member_id'] = $id;
        $data['activity_id'] = $activity_id;
        $res = DB::table('fabulous')->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //用户取消点赞
    public function cancelFabulous($id,$activity_id){
        $data['member_id'] = $id;
        $data['activity_id'] = $activity_id;
        $res = DB::table('fabulous')->where($data)->delete();
        if (!$res){
            return false;
        }
        return true;
    }

    //点赞数量
    public function fabulousNumber($id){
        $number = 0;
        $res = DB::table('fabulous')->where('activity_id',$id)->get();
        if ($res){
            $res = json_decode(json_encode($res),true);
            $number = count($res);
        }
        return $number;
    }

    //用户收藏
    public function memebrCollect($id,$activity_id){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['member_id'] = $id;
        $data['activity_id'] = $activity_id;
        $res = DB::table('collect')->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //用户取消点赞
    public function cancelCollect($id,$activity_id){
        $data['member_id'] = $id;
        $data['activity_id'] = $activity_id;
        $res = DB::table('collect')->where($data)->delete();
        if (!$res){
            return false;
        }
        return true;
    }

    //收藏数量
    public function collectNumber($id){
        $number = 0;
        $res = DB::table('collect')->where('activity_id',$id)->get();
        if ($res){
            $res = json_decode(json_encode($res),true);
            $number = count($res);
        }
        return $number;
    }

    //用户收藏列表
    public function getMemberCollect($id){
        $res = DB::table('collect as c')
            ->leftJoin('activity as a','c.activity_id','=','a.id')
            ->select('a.picture','a.name','c.created_at','a.id','a.place')
            ->where('c.member_id',$id)
            ->orderBy('c.id','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //用户点赞列表
    public function getMemberFabulous($id){
        $res = DB::table('fabulous as f')
            ->leftJoin('activity as a','f.activity_id','=','a.id')
            ->select('a.picture','a.name','f.created_at','a.id','a.place')
            ->where('f.member_id',$id)
            ->orderBy('f.id','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //客户已参加的活动列表
    public function myActivityList($id,$data){
        $res = DB::table('attend as a')
            ->select('a.created_at','ac.name','ac.place','a.status','ac.id','ac.picture')
            ->leftJoin($this->table.' as ac','a.activity_id','=','ac.id')
            ->where('a.member_id',$id);
        if ($data['status'] != ''){
            if ($data['status'] == 3){
                $res->where('a.status',5);
            }elseif ($data['status'] == 5){
                $res->where('a.status',3);
            }else{
                $res->where('a.status',$data['status']);
            }

        }
        if ($data['time_status'] != ''){
            if ($data['time_status'] == 1){  //本月
                //php获取本月起始时间戳和结束时间戳
                $beginThismonth=date('Y-m-d 00:00:00',mktime(0,0,0,date('m'),1,date('Y')));
                $endThismonth=date('Y-m-d 23:59:59',mktime(23,59,59,date('m'),date('t'),date('Y')));
                $res->whereBetween('a.created_at',[$beginThismonth,$endThismonth]);
            }elseif ($data['time_status'] == 2){   //本周
                //获取本周开始日期结束日期
                $sdefaultDate = date("Y-m-d");
                $first=1;
                $w=date('w',strtotime($sdefaultDate));
                $week_start=date('Y-m-d 00:00:00',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
                $week_end=date('Y-m-d 23:59:59',strtotime("$week_start +6 days"));
                $res->whereBetween('a.created_at',[$week_start,$week_end]);
            }elseif ($data['time_status'] == 3){   //半年
                //半年开始结束时间
                $sdefaultDate = date("Y-m-d");
                $half_year_end = date("Y-m-d");
                $half_year_start = date('Y-m-d 23:59:59',strtotime("$sdefaultDate - 5 month"));
                $res->whereBetween('a.created_at',[$half_year_end,$half_year_start]);
            }elseif ($data['time_status'] == 4){   //全年
                //本年开始结束时间
                $year_start = date('Y-m-d 00:00:00',mktime(23,59,59,01,01,date('Y')));
                $year_end = date('Y-m-d 23:59:59',mktime(23,59,59,12,31,date('Y')));
                $res->whereBetween('a.created_at',[$year_start,$year_end]);
            }
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        return $result;
    }

    //参加活动详情
    public function myActivityInfo($id,$activity_id){
        //活动信息
        $activity_info = DB::table($this->table)->where('id',$activity_id)->select('name','start_time','place','cost')->first();
        $activity_info = json_decode(json_encode($activity_info),true);
        //参加信息
        $attend = DB::table('attend')->where('member_id',$id)->where('activity_id',$activity_id)->orderBy('id','desc')->first();
        $attend = json_decode(json_encode($attend),true);
        $order_info['name'] = $attend['member_name'];
        $order_info['mobile'] = $attend['mobile'];
        $order_info['number'] = 1;
        $order_info['cost'] = $activity_info['cost'];
        if ($attend['status'] == 1){
            $order_info['statusTxt'] = '待审核';
        }elseif ($attend['status'] == 2){
            $order_info['statusTxt'] = '已参加';
        }elseif ($attend['status'] == 3){
            $order_info['statusTxt'] = '审核未通过，待退款';
        }elseif ($attend['status'] == 4){
            $order_info['statusTxt'] = '审核未通过，已退款';
        }elseif ($attend['status'] == 5){
            $order_info['statusTxt'] = '已取消报名';
        }elseif ($attend['status'] == 0){
            $order_info['statusTxt'] = '待付款';
        }
        $order_info['status'] = $attend['status'];
        //支付信息
        $pay_info['out_trade_no'] = $attend['out_trade_no'];
        $pay_info['time'] = $attend['updated_at'];
        $pay_info['type'] = '微信';
        $data['activity_info'] = $activity_info;
        $data['order_info'] = $order_info;
        $data['pay_info'] = $pay_info;
        return $data;
    }

    //最新活动列表3个
    public function getNewActivity(){
        $res = DB::table($this->table)
            ->select('id','name','picture','start_time')
            ->whereNull('deleted_at')
            ->where('activity_status',1)
            ->where('stop_time','>',Carbon::now()->toDateTimeString())
            ->orderBy('id','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        foreach ($res as &$v){
            $v['start_time'] = date('m月d日',strtotime($v['start_time']));
        }
        return $res;
    }

    //全部活动列表
    public function getAllList(){
        $res = DB::table($this->table)->whereNull('deleted_at')->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //获取form_id
    public function getFormId($member_id){
        $formId = DB::table('form_id')->where('form_user','member_'.$member_id)->where('is_used',0)->first();
        $formId = json_decode(json_encode($formId),true);
        if ($formId['form_id'] == ''){
            $where['is_used'] = 1;
            DB::table('form_id')->where('id',$formId['id'])->update($where);
            $this->getFormId($member_id);
        }else{
            $where['is_used'] = 1;
            DB::table('form_id')->where('id',$formId['id'])->update($where);
            return $formId['form_id'];
        }

    }













































}

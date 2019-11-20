<?php

namespace App\Models\Admin;

use App\Library\Common;
use App\Library\WxpayService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Attend extends Model
{
    protected $table='attend';

    public function addAttend($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    public function getActivityNumber($id){
        $number = 0;
        $res = DB::table($this->table)->where('activity_id',$id)->where('status',2)->get();
        if ($res){
            $number = count($res);
        }
        return $number;
    }

    //报名列表
    public function getActivityList($data){
        $res = DB::table($this->table.' as a')
            ->select('a.id','a.activity_id','a.member_id','a.member_name','m.mobile','a.status','a.created_at','a.money')
            ->leftJoin('member as m','a.member_id','=','m.id')
            ->where('a.activity_id',$data['activity_id']);
        $list['total'] = $res->count();
        $result = $res->skip($data['start'])->take($data['pageSize'])->orderBy('a.'.$data['sortName'], $data['sortOrder'])->get();
        $list['rows'] = [];
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $list;
        }
        $list['rows'] = $result;
        return $list;
    }

    //审核报名人员
    public function agreeAttend($data){
        $res = DB::table($this->table.' as a')
            ->leftJoin('member as m','a.member_id','=','m.id')
            ->select('a.id','a.activity_id','m.mobile','a.member_name','a.openid','a.member_id','a.money')
            ->where('a.id',$data['id'])
            ->first();
        $res = json_decode(json_encode($res),true);
        if ($res){
            $activity_data = DB::table('activity')->where('id',$res['activity_id'])->select('name','place','start_time','host_party','host_contact','notice','activity_type_id','activity_status')->first();
            $activity_data = json_decode(json_encode($activity_data),true);
            if ($activity_data['activity_status'] != 1){
                $a = -1;
                return $a;
            }
            $type_name = DB::table('activity_type')->where('id',$activity_data['activity_type_id'])->select('name')->first();
            $type_name = json_decode(json_encode($type_name),true);
            $sendWX = new Common();
            //是否通过审核
            if ($data['status'] == 1){     //通过审核
                $send_info_sns = [
                    'name' => $activity_data['name'],
                    'start_time' => $activity_data['start_time'],
                    'place' => $activity_data['place'],
                    'host_contact' => $activity_data['host_contact'],
                    'type' => 2
                ];
                $send_info_We = [
                    'name' => $activity_data['name'],
                    'type_name' => $type_name['name'],
                    'place' => $activity_data['place'],
                    'start_time' => $activity_data['start_time'],
                    'host_party' => $activity_data['host_party'],
                    'host_contact' => $activity_data['host_contact'],
                    'type' => 2
                ];
                if ($activity_data['notice'] == 1){  //微信通知
                    $send_info_We['form_id'] = $this->getFormId($res['member_id']);
                    $sendWX->WechatPush($res['openid'],$send_info_We);
                }elseif ($activity_data['notice'] == 2){  //短信通知
                    $sendWX->sendSNS($res['mobile'],$send_info_sns);
                }elseif ($activity_data['notice'] == 3){  //微信短信通知
                    $send_info_We['form_id'] = $this->getFormId($res['member_id']);
                    $sendWX->WechatPush($res['openid'],$send_info_We);
                    $sendWX->sendSNS($res['mobile'],$send_info_sns);
                }
                $menber_attend['status'] = 2;
                $menber_attend['updated_at'] = Carbon::now()->toDateTimeString();
                $res = DB::table($this->table)->where('id',$data['id'])->update($menber_attend);
                if ($res){
                    return true;
                }else{
                    return false;
                }

            }elseif ($data['status'] == 2){     //拒绝通过
                if ($res['money'] > 0){          //判断是否需要退款
                    $mchid = 'wx0fa2777491d1f633';                    //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
                    $appid = '1445622102';                             //微信支付申请对应的公众号的APPID
                    $apiKey = '523136f7c52a452748cac685a29164f6';    //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
                    $wxPay = new WxpayService($mchid,$appid,$apiKey);
//                    $orderNo = '';                                     //商户订单号（商户订单号与微信订单号二选一，至少填一个）
                    $refundNo = rand(11111,99999);                       //退款订单号(可随机生成)
                    $wxOrderNo = $res['transaction_id'];               //微信订单号（商户订单号与微信订单号二选一，至少填一个）
                    $totalFee = $res['money'];                          //订单金额，单位:元
                    $refundFee = $res['money'];                         //退款金额，单位:元
                    $result = $wxPay->doRefund($totalFee, $refundFee, $refundNo, $wxOrderNo);
                    if($result === true){
                        //发送通知
                        $send_info_sns = [
                            'start_time' => $activity_data['start_time'],
                            'name' => $activity_data['name'],
                            'msg' => '参与人数已满',
                            'host_contact' => $activity_data['host_contact'],
                            'type' => 3
                        ];
                        $send_info_We = [
                            'member_name' => $res['member_name'],
                            'created_at' => $res['created_at'],
                            'name' => $activity_data['name'],
                            'result' => '报名申请未通过',
                            'msg' => '参与人数已满',
                            'type' => 3
                        ];
                        if ($activity_data['notice'] == 1){  //微信通知
                            $send_info_We['form_id'] = $this->getFormId($res['member_id']);
                            $sendWX->WechatPush($res['openid'],$send_info_We);
                        }elseif ($activity_data['notice'] == 2){  //短信通知
                            $sendWX->sendSNS($res['mobile'],$send_info_sns);
                        }elseif ($activity_data['notice'] == 3){  //微信短信通知
                            $send_info_We['form_id'] = $this->getFormId($res['member_id']);
                            $sendWX->WechatPush($res['openid'],$send_info_We);
                            $sendWX->sendSNS($res['mobile'],$send_info_sns);
                        }
                        $menber_attend['status'] = 4;
                        $menber_attend['updated_at'] = Carbon::now()->toDateTimeString();
                        DB::table('attend')->where('id',$res['id'])->update($menber_attend);
                        return true;
                    }else{
                        return false;
                    }
                }
                $menber_attend['status'] = 4;
                $menber_attend['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('attend')->where('id',$res['id'])->update($menber_attend);
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    //获取活动全部人员
    public function getAttendAll($data){
        $res = DB::table($this->table)->where('activity_id',$data['id'])->get();
        if ($res){
            $res = json_decode(json_encode($res),true);
            return $res;
        }
        return false;
    }

    //获取活动人数
    public function getAttendNumber($id){
        $res = DB::table($this->table)->where('activity_id',$id)->where('status',2)->count();
        return $res;
    }

    //获取用户与报名的状态
    public function getMemberActivity($id,$activity_id){
        $info = DB::table($this->table)->where('member_id',$id)->where('activity_id',$activity_id)->first();
        if ($info){
            $info = json_decode(json_encode($info),true);
            if($info['status'] == 0){
                DB::table($this->table)->delete($info['id']);
            }elseif ($info['status'] == 4){
                DB::table($this->table)->delete($info['id']);
            }elseif ($info['status'] == 5){
                DB::table($this->table)->delete($info['id']);
            }
            $new_info = DB::table($this->table)->where('member_id',$id)->where('activity_id',$activity_id)->first();
            $new_info = json_decode(json_encode($new_info),true);
            if ($new_info){
                return false;
            }
        }
        //获取报名人数
        $count = DB::table($this->table)->where('activity_id',$activity_id)->where('status',2)->count();
        $limit_number = DB::table('activity')->where('id',$activity_id)->select('limit_number')->first();
        $limit_number = json_decode(json_encode($limit_number),true);
        if ($count >= $limit_number['limit_number']){
            $a = -1;
            return $a;
        }
        return true;
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

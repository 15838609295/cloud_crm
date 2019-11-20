<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Configs extends Model
{
    protected $table_name = 'configs';

    public function getConfigByID($id=1)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function configUpdate($id,$data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res && $res !== 0){
            return false;
        }
        return true;
    }
    
    public function getWxPayConfig($id=1){
        $res = DB::table($this->table_name)->where('id',$id)->select('wx_pay_merchant_id','wx_pay_secret_key')->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function checkTakeMoney($uid,$money,$type){
        $user_money = DB::table('admin_users')->where('id',$uid)->select('bonus','sale_bonus')->first();
        $user_money = json_decode(json_encode($user_money),true);
        $rule = $this->getConfigByID();
        if ($type == 1){    //提成提现
//            if ($rule['royalty_small'] && $money < $rule['royalty_small']){
//                return 1;
//            }
//            if( $rule['royalty_alone'] && $money > $rule['royalty_alone']){
//                return 2;
//            }
//            if ($rule['royalty_proportion'] && $money > ($user_money['bonus']*$rule['royalty_proportion'])/100){
//                return 3;
//            }
//            //  当天次数  $res->('c.contact_next_time',[$fields['next_start_time'],$fields['next_end_time']]);
//            $beginToday=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
//            $endToday=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1) ;
//            $today_second = DB::table('take_bonus')->where('type',1)->where('admin_users_id',$uid)->whereBetween('created_at',[$beginToday,$endToday])->get();
//            $today_second = count(json_decode(json_encode($today_second),true));
//            if ($rule['royalty_today_second'] && $today_second >= $rule['royalty_today_second']){
//                return 4;
//            }
//            //  当月次数
//            //php获取本月起始时间戳和结束时间戳
//            $beginThismonth=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),1,date('Y')));
//            $endThismonth=date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('t'),date('Y')));
//            $month_second = DB::table('take_bonus')->where('type',1)->where('admin_users_id',$uid)->whereBetween('created_at',[$beginThismonth,$endThismonth])->get();
//            $month_second = count(json_decode(json_encode($month_second),true));
//            if ($rule['royalty_month_second'] && $month_second >= $rule['royalty_month_second']){
//                return 5;
//            }
            return 6;
        }elseif ($type == 2){   //奖金提现
            if ($rule['bonus_small'] && $money < $rule['bonus_small']){
                return 1;
            }
            if($rule['bonus_alone'] && $money > $rule['bonus_alone']){
                return 2;
            }
            if ($rule['bonus_proportion'] && $money > ($user_money['sale_bonus']*$rule['bonus_proportion'])/100){
                return 3;
            }
            //  当天次数  $res->('c.contact_next_time',[$fields['next_start_time'],$fields['next_end_time']]);
            $beginToday=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
            $endToday=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1) ;
            $today_second = DB::table('take_bonus')->where('type',2)->where('admin_users_id',$uid)->whereBetween('created_at',[$beginToday,$endToday])->get();
            $today_second = count(json_decode(json_encode($today_second),true));
            if ($rule['bonus_today_second'] && $today_second >= $rule['bonus_today_second']){
                return 4;
            }
            //  当月次数
            //php获取本月起始时间戳和结束时间戳
            $beginThismonth=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),1,date('Y')));
            $endThismonth=date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('t'),date('Y')));
            $month_second = DB::table('take_bonus')->where('type',2)->where('admin_users_id',$uid)->whereBetween('created_at',[$beginThismonth,$endThismonth])->get();
            $month_second = count(json_decode(json_encode($month_second),true));
            if ($rule['bonus_month_second'] && $month_second >= $rule['bonus_month_second']){
                return 5;
            }
            return 6;
        }
    }
}

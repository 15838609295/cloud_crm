<?php

namespace App\Models\Admin;

use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Achievement extends Model
{
    protected $table_name='achievement';

    protected $fillable = [
        'id', 'member_name', 'member_phone', 'goods_money','order_number', 'admin_users_id', 'goods_name', 'remarks', 'created_at', 'updated_at'
    ];

    /* 获取基础的售后订单 */
    public function getBaseAchievementList($fields = ['*'],$filter_options = null)
    {
        $res = DB::table($this->table_name)->select($fields);
        if(is_array($filter_options)){
            foreach ($filter_options as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        $result = $res->get();
        $res = json_decode(json_encode($result),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过ID获取业绩订单 */
    public function getAchievementById($id)
    {
        $res = DB::table($this->table_name.' as a')
            ->select('a.*','au.name as admin_name')
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->where('a.id',$id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function getAchievementByAdminID($id,$date)
    {
        $res = DB::table($this->table_name)
            ->select('admin_users_id',DB::raw('SUM(goods_money) AS total_money'))
            ->where('status',1)
            ->where('ach_state',0)
            ->where('buy_time','LIKE','%'.$date.'%')
            ->groupBy('admin_users_id');
        if(is_array($id)){
            $res->whereIn('admin_users_id',$id);
            $result = $res->get();
        }else{
            $res->where('admin_users_id',$id);
            $result = $res->first();
        }
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    /* 通过order_no获取业绩订单 */
    public function getAchievementByOrderNo($order_no)
    {
        $res = DB::table($this->table_name)->where('order_number',$order_no)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取带售前销售信息的业绩订单
    public function getAchievementWithAdminData($id,$fields = ['a.*','au.*'])
    {
        $res = DB::table($this->table_name.' as a')
            ->select($fields)
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->where('a.id','=',$id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取带售前和售后销售信息的业绩订单详情
    public function getAchievementWithAllAdminData($id)
    {
        $res = DB::table($this->table_name.' as a')
            ->select('a.*','au.name','autwo.name as after_sale_name')
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->leftJoin('admin_users as autwo','autwo.id','=','a.after_sale_id')
            ->where('a.id','=',$id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取某时间内用户业绩总金额
    public function getUserAchievementTotalMoney($user_id,$date)
    {
        $res = DB::table($this->table_name)
            ->where('status','=',1)
            ->where('buy_time','like','%'.$date.'%')
            ->where('admin_users_id',$user_id)
            ->where('ach_state','=',0)
            ->sum('goods_money');
        return $res;
    }

    //获取业绩订单列表(权限+筛选条件)
    public function getAchievementList($fields)
    {
        $res = DB::table($this->table_name.' as a')
            ->select('a.*','au.name','g.goods_type')
            ->leftJoin('admin_users as au','a.admin_users_id','=','au.id')
            ->leftJoin('user_branch as ub','a.admin_users_id','=','ub.user_id')
            ->leftJoin('goods as g','a.sbr_id','=','g.id')
            ->groupBy('a.id');
        //根据商品类型筛选商品
        if ($fields['type'] != ''){
            $goodsTypeModel = new GoodsType();
            $goods_ids = $goodsTypeModel->getGoodsNames($fields['type']);
            $res->whereIn('a.goods_name',$goods_ids);
        }
        if(isset($fields['user_id']) && $fields['user_id']!=''){
            $res->where('au.id',$fields['user_id']);
        }
        if(isset($fields['list']) && count($fields['list'])>0){
            $res->whereIn('a.id',$fields['list']);
        }
        if(isset($fields['admin_id']) && $fields['admin_id']!=''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('a.admin_users_id',$user_list);
        }
        if(isset($fields['status']) && $fields['status']!=''){
            if($fields['status']!='expire'){
                $res->where('a.status',$fields['status']);
            }else{
                $start_time = date('Y-m-d H:i:s',time());
                $tmp = explode(' ',$start_time)[1];
                $end_time = strtotime("+1 month",strtotime($start_time));
                $end_time = date("Y-m-d",$end_time)." ".$tmp;
                $res->whereBetween('a.end_time',[$start_time,$end_time]);
            }
        }
        if(isset($fields['branch_id']) && $fields['branch_id']!=''){
            $res->where('ub.branch_id',$fields['branch_id']);
        }
        if($fields['start_time']!='' && $fields['end_time']!=''){
            $res->whereBetween('a.buy_time',[$fields['start_time'],$fields['end_time']]);
        }else if($fields['start_time']!='' && $fields['end_time']==''){
            $res->where('a.buy_time','>=',$fields['start_time']);
        }else if($fields['start_time']=='' && $fields['end_time']!=''){
            $res->where('a.buy_time','=<',$fields['end_time']);
        }
        if(isset($fields['searchKey']) && $fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('a.member_name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('a.member_phone', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('a.goods_name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('a.order_number', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res->get();
        $total = json_decode(json_encode($total),true);
        $data['total'] = count($total);
        $data['total_money'] = $this->getAchievementTotalMoney($total);
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    //获取业绩订单列表(权限+筛选条件)
    public function getAchievements($fields)
    {
        $res = DB::table($this->table_name.' as a')
            ->select('a.*','au.name')
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->leftJoin('user_branch as ub','ub.user_id','=','a.admin_users_id')
            ->groupBy('a.id');
        if(isset($fields['user_id']) && $fields['user_id']!=''){
            $res->where('au.id',$fields['user_id']);
        }
        if ($fields['start_time'] != '' && $fields['end_time'] != ''){
            $res->whereBetween('a.created_at',[$fields['start_time'],$fields['end_time']]);
        }
//        $res->whereBetween('a.created_at',[date('Y-m-d H:i:s', mktime(0,0,0,date('m'),1,date('Y'))),date('Y-m-d H:i:s',mktime(23,59,59,date('m'),date('t'),date('Y')))]);
        $data['total'] = count(json_decode(json_encode($res->get()),true));
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 筛选业绩排行榜数据 */
    public function getAchievementDataToRank($fields)
    {
        $res = DB::table($this->table_name)
            ->select('admin_users_id',DB::raw('sum(goods_money) as total_money'))
            ->whereIn('admin_users_id',$fields['user_list']);
        if($fields['start_time']!='' && $fields['end_time']!=''){
            $res->whereBetween('buy_time',[$fields['start_time'],$fields['end_time']]);
        }else if($fields['start_time']!='' && $fields['end_time']==''){
            $res->where('buy_time','>=',$fields['start_time']);
        }else if($fields['start_time']=='' && $fields['end_time']!=''){
            $res->where('buy_time','=<',$fields['end_time']);
        }
        $res->where('status','=',1)->where('ach_state','=',0);
        $result = $res->groupBy('admin_users_id')->get();
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    /*筛选团队排行榜数据*/
    public function getAchievementByCompanyId($data,$where){
        $requset = [];
        foreach ($data as $key=>$values){
            foreach ($values as $k=>$v){
                if (isset($v['uid'])){

                    $res = DB::table($this->table_name)
                        ->select(DB::raw('sum(goods_money) as total_money'))
                        ->whereIn('admin_users_id',$v['uid']);
                    if($where['start_time']!='' && $where['end_time']!=''){
                        $res->whereBetween('buy_time',[$where['start_time'],$where['end_time']]);
                    }else if($where['start_time']!='' && $where['end_time']==''){
                        $res->where('buy_time','>=',$where['start_time']);
                    }else if($where['start_time']=='' && $where['end_time']!=''){
                        $res->where('buy_time','=<',$where['end_time']);
                    }
                    $result = $res->where('status',1)->where('ach_state',0)->get();
                    $result = json_decode(json_encode($result),true);
                    $money = 0;
                    foreach ($result as $info){
                        $money += $info['total_money'];
                    }
                    $money = sprintf("%.2f",$money);
                    $requset[$key][$k]['id'] = $v['id'];
                    $requset[$key][$k]['name'] = $v['name'];
                    $requset[$key][$k]['total_money'] = $money;
                }else{
                    $requset[$key][$k]['id'] = $v['id'];
                    $requset[$key][$k]['name'] = $v['name'];
                    $money = 0;
                    $money = sprintf("%.2f",$money);
                    $requset[$key][$k]['total_money'] = $money;
                }
            }
        }
        return $requset;
    }


    /* 通过日期获取总业绩 */
    public function getTotalMoneyBydate($fields)
    {
        $res = DB::table($this->table_name)
            ->whereIn('admin_users_id',$fields['user_list'])
            ->where('buy_time','LIKE',"%".$fields['date']."%")
            ->where('status','=',1)
            ->where('ach_state','=',0)
            ->sum('goods_money');
        return $res;
    }

    //获得业绩总金额
    public function getAchievementTotalMoney($data)
    {
        $total = 0;
        if(!is_array($data) || count($data)<1){
            return $total;
        }
        foreach ($data as $key=>$value){
            if($value['status']==1){
                $total += $value['goods_money'];
            }
        }
        return sprintf("%.2f",$total);
    }

    //获得最新业绩的ID
    public function getMaxAchievementID()
    {
        $id = DB::table($this->table_name)->max('id');
        return $id;
    }

    //获得时间内业绩订单
    public function getAchievementByDate($date,$user_id,$type='month')
    {
        $datemonth = $date;
        if($type=='day'){
            $datemonth = substr($date,0,10);
        }
        if($type=='month'){
            $datemonth = substr($date,0,7);
        }
        $res = DB::table($this->table_name.' as a')
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->where('a.status','=',1)
            ->where('a.ach_state','=',0)
            ->where('au.ach_status','=',0)
            ->where('a.buy_time','LIKE','%'.$datemonth.'%')
            ->where('au.id','=',$user_id)
            ->sum('a.goods_money');
        return $res;
    }

    //获取某时间内业绩第一数据
    public function getTopAchievement($date,$type='month')
    {
        $datemonth = $date;
        if($type=='day'){
            $datemonth = substr($date,0,10);
        }
        if($type=='month'){
            $datemonth = substr($date,0,7);
        }
        $res = DB::table($this->table_name.' as a')
            ->select('au.name',DB::raw('sum(a.goods_money) as total_money'))
            ->leftJoin('admin_users as au','au.id','=','a.admin_users_id')
            ->where('a.status','=',1)
            ->where('a.ach_state','=',0)
            ->where('au.ach_status','=',0)
            ->where('a.buy_time','LIKE','%'.$datemonth.'%')
            ->orderBy('total_money','desc')
            ->groupBy('au.id')
            ->first('a.goods_money');
        if(!$res){
            $res = array('name' => '','total_money' => '');
            return $res;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 添加业绩 */
    public function achievementInsert($data)
    {
        $data['created_at']=Carbon::now();
        $data['updated_at']=Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 修改业绩 */
    public function achievementUpdate($id,$fields)
    {
        $fields['updated_at'] = Carbon::now();
        if(!is_array($fields) || count($fields)<1){
            return false;
        }
        $res = DB::table($this->table_name);
        if(is_array($id)){
            $res->whereIn('id',$id);
        }else{
            $res->where('id',$id);
        }
        $result = $res->update($fields);
        if(!$result){
            return false;
        }
        //判断是否有售后订单 扣除提成或奖金
        $ach_data = DB::table($this->table_name)->first();
        $ach_data = json_decode(json_encode($ach_data),true);
        //提成 奖金扣除
        $afterSaleModel = new AfterSale();
        $afterSaleModel->deductionAftreSale($ach_data);
        return true;
    }

    /* 通过指定字段更新achievement */
    public function achievementUpdateByColumn($key,$value,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name);
        if(is_array($value)){
            $res->whereIn($key,$value);
        }else{
            $res->where($key,$value);
        }
        $res = $res->update($data);
        if($res || $res === 0){
            return true;
        }
        return false;
    }

    /* 删除业绩 */
    public function achievementDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    //获取某时间内用户业绩总金额
    public function getAchievementMoney($user_id)
    {
        $res = DB::table($this->table_name)
            ->where('status','=',1)
            ->where('admin_users_id',$user_id)
            ->where('ach_state','=',0)
            ->sum('goods_money');
        return $res;
    }

    //售后服务列表
    public function serviceList($fields){
        $res = DB::table($this->table_name.' as a')
            ->select('m.name as member_name','a.member_phone','a.order_number','a.created_at','m.id as member_id','a.remarks','au.name as admin_name','a.buy_length',
                'a.buy_time','ae.remind_time','ae.expire','ae.star_class')
            ->leftJoin('member as m',function ($join){
                $join->on('a.member_name','=','m.name')->on('a.member_phone','=','m.mobile');
            })
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('admin_users as au','me.recommend','=','au.id')
            ->leftJoin('achievement_extend as ae','m.id','=','ae.member_id')
            ->where('a.status',1)
            ->where('a.after_sale_id','!=',0);
        if ($fields['type'] == 1 && $fields['search'] != ''){
            $res->where('a.member_name','LIKE', '%' . $fields['search'] . '%');
        }elseif ($fields['type'] == 2 && $fields['search'] != ''){
            $res->where('a.member_phone',$fields['search']);
        }elseif ($fields['type'] == 3 && $fields['search'] != ''){
            $res->where('au.name','LIKE', '%' . $fields['search'] . '%');
        }elseif ($fields['type'] == 4 && $fields['search'] != ''){
            $res->where('m.id',$fields['search']);
        }

        if($fields['admin_id'] != ''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $where['admin_id'] = $fields['admin_id'];
            $where['user_list'] = $user_list;
            $res->where(function ($query) use ($where) {
                    $query->whereIn('me.recommend',$where['user_list'])
                        ->orwhere('ae.maintain_id','=',$where['admin_id'])
                        ->orwhere('ae.duty_id',$where['admin_id']);
                });
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $now_time = time();
        $list = [];
        foreach ($result as &$v){
            //获取拓展表信息
            //计算剩余天数
            $buy_time = strtotime($v['buy_time']);
            $buy_length = $v['buy_length'] * 30 * 86400;
            $expire_time = $buy_time + $buy_length;
            if (!$v['star_class']){
                $v['star_class'] = 1;
            }
//            if ($expire_time < $now_time){  //小于现在的时间 已过期 不记录
//                continue;
//            }
            $v['surplus_time'] = $expire_time-$now_time;
            if (!array_key_exists($v['member_id'],$list)){
                $v['surplus_day'] = floor(($v['surplus_time'])/86400);
                if ($fields['star_class'] != ''){
                    if ($v['star_class'] == $fields['star_class']){
                        $list[$v['member_id']] = $v;
                    }
                }else{
                    $list[$v['member_id']] = $v;
                }
            }else{
                if ($list[$v['member_id']]['surplus_time'] < $v['surplus_time']){
                    $v['surplus_day'] = floor(($v['surplus_time'])/86400);
                    if ($fields['star_class'] != ''){
                        if ($v['star_class'] == $fields['star_class']){
                            $list[$v['member_id']] = $v;
                        }
                    }else{
                        $list[$v['member_id']] = $v;
                    }
                }
            }
        }
        $new_list = array_values($list);
        $data['total'] = count($new_list);
        if ($fields['sortName'] != ''){
            $last_names = array_column($new_list,$fields['sortName']);
            if ($fields['sortOrder'] == 'desc'){
                array_multisort($last_names,SORT_DESC,$new_list);
            }else{
                array_multisort($last_names,SORT_ASC,$new_list);
            }
        }
        $page_info = array_slice($new_list,$fields['start'],$fields['pageSize']);
        $data['rows'] = array_values($page_info);
        return $data;
    }

    public function getMemberOrdersList($member_id,$fields){
        $res = DB::table($this->table_name.' as a')
            ->select('a.*')
            ->leftJoin('member as m',function ($join){
                $join->on('a.member_name','=','m.name')->on('a.member_phone','=','m.mobile')->oron('a.member_id','=','m.id');
            })
            ->where('m.id',$member_id)
            ->orderBy('a.id','desc');
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->get();
        if (!$res){
            return false;
        }
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        return $result;
    }

    //获取总业绩
    public function statisticsAchievent($fields){
        $res = DB::table($this->table_name)->select(DB::raw('sum(goods_money) AS total_money'))
            ->whereBetween('created_at',[$fields['start_time'],$fields['end_time']])
            ->where('status',1)
            ->where('ach_state',0);
        if ($fields['ids'] != ''){
            $res->whereIn('admin_users_id',$fields['ids']);
        }
        $money_res = $res->get();
        $money_res = json_decode(json_encode($money_res),true);
        $money = 0;
        foreach ($money_res as $v){
            $money = $money + $v['total_money'];
        }
        return $money;
    }

    //计算曲线图数据
    public function curveData($fields){
        $list['label'] = '销售业绩';
        $list['dataList'] = [];
        $month = (int)date('m');
        for ($i = 1;$i <= $month;$i++){
            if ($i < 10){
                $m = '0'.$i;
                $time = mktime(00,00,00,$m,01,date('Y'));
                $start_time = date('Y-m-d 00:00:00',$time);
                $end_time = date('Y-m-t 23:59:59',$time);
            }else{
                $time = mktime(00,00,00,$i,01,date('Y'));
                $start_time = date('Y-m-d 00:00:00',$time);
                $end_time = date('Y-m-t 23:59:59',$time);
            }
            $res = DB::table($this->table_name)
                ->select('id',DB::raw('sum(goods_money) as money'))
                ->whereBetween('created_at',[$start_time,$end_time])
                ->where('status',1)
                ->where('ach_state',0);
            if ($fields['ids'] != ''){
                $res->whereIn('admin_users_id',$fields['ids']);
            }
            $number = $res->get();
            $number = json_decode(json_encode($number),true);

            if (!$number[0]['money']){
                $list['dataList'][] = 0;
            }else{
                $list['dataList'][] = $number[0]['money'];
            }
        }
        return $list;
    }
}

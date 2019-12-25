<?php

namespace App\Models\Admin;

use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AfterSale extends Model
{
    protected $table_name='after_sale';

    //获取基础的售后订单
    public function getBaseAfterSaleList($fields = ['*'],$filter_options = null)
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

    //通过ID获取售后订单
    public function getAfterSaleOrderByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $result = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    //通过ID获取售后订单详情(带跟进人信息)
    public function getASOrderWitdRecommendByID($id)
    {
        $res = DB::table($this->table_name.' as as')
            ->select('as.*','au.name as after_sale_name')
            ->leftJoin('admin_users as au','as.after_sale_id','=','au.id')
            ->where('as.id',$id)
            ->first();
        if(!$res){
            return false;
        }
        $result = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    //通过ID获取售后订单详情(带跟进人信息以及销售规则信息)
    public function getAfterSaleOrderDetailByID($id)
    {
        $res = DB::table($this->table_name.' as as')
            ->select('as.*','au.name as after_name','sb.after_first_bonus','sb.after_first_bonus')
            ->leftJoin('admin_users as au','as.after_sale_id','=','au.id')
            ->leftJoin('salebonusrule as sb','as.sbr_id','=','sb.id')
            ->where('as.id',$id)
            ->first();
        if(!$res){
            return false;
        }
        $result = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    //获取售后订单列表(权限+筛选条件)
    public function getAfterSaleList($fields=['*'])
    {
        $res = DB::table($this->table_name.' as as')
            ->select('as.*','au.name as after_name')
            ->leftJoin('admin_users as au','as.after_sale_id','=','au.id')
            ->leftJoin('user_branch as ub','ub.user_id','=','as.after_sale_id')
            ->groupBy('as.id');
        if($fields['user_id']!=''){
            $res->where('au.id',$fields['user_id']);
        }
        if(isset($fields['admin_id']) && $fields['admin_id']!=''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('as.after_sale_id',$user_list);
        }
        if($fields['after_status']!=''){
            $res->where('as.after_status',$fields['after_status']);
        }
        if($fields['branch_id']!=''){
            $res->where('ub.branch_id',$fields['branch_id']);
        }
        if($fields['start_time']!='' && $fields['end_time']!=''){
            $res->whereBetween('as.buy_time',[$fields['start_time'],$fields['end_time']]);
        }else if($fields['start_time']!='' && $fields['end_time']==''){
            $res->where('as.buy_time','>=',$fields['start_time']);
        }else if($fields['start_time']=='' && $fields['end_time']!=''){
            $res->where('as.buy_time','=<',$fields['end_time']);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('as.member_name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('as.member_phone', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('as.goods_name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('as.order_number', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $data['total'] = count(json_decode(json_encode($res->get()),true));
        //总列表
        $list = json_decode(json_encode($res->get()),true);
        $data['percentage_money'] = $this->getPercentageMoney($list);
        //计算应得奖金
        $data['deserved'] = $this->deservedBonus($list);

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

    //处理售后订单列表数据
    public function buildAfterSaleListFields($data,$surplus_time = null)
    {
        if(!is_array($data) || count($data)<1){
            return false;
        }
        $saleRule = [];
        $tmp_res = DB::table('salebonusrule')->get();
        $tmp_res = json_decode(json_encode($tmp_res),true);
        foreach ($tmp_res as $key=>$value){
            $saleRule['id_'.$value['id']] = $value;
        }
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                $data[$key]['obtain_price'] = 0;
                $data[$key]['over_price'] = 0;
                $data[$key]['obtain_bonus_price'] = 0;
                $data[$key]['over_bonus_price'] = 0;
                if (isset($saleRule['id_' . $value['sbr_id']])) {
                    if ($saleRule['id_' . $value['sbr_id']]['rule_type'] == 1){
                        $data[$key]['obtain_price'] = $value['after_money'];//已经获得的提成
                        $data[$key]['obtain_bonus_price'] = $value['bonus_royalty'];//已经获得的奖金提成
                        $data[$key]['over_price'] = 0;//还剩下的提成
                        $data[$key]['over_bonus_price'] = 0;//还剩下的奖金提成
                    }else{
                        if ($value['buy_length'] != 0) {
                            if ($value['after_time'] == 0 || $value['after_type'] !== 0) {
                                $data[$key]['obtain_price'] = 0;
                                $data[$key]['obtain_bonus_price'] = 0;
                                $data[$key]['over_price'] = $value['after_money'];
                                $data[$key]['over_bonus_price'] = $value['bonus_royalty'];
                            } else if ($value['after_time'] > 0 && $value['after_type'] !== 1) {

                                if ($saleRule['id_' . $value['sbr_id']]['type'] == 1){  //提成
                                    if ($saleRule['id_' . $value['sbr_id']]['after_first_bonus'] == 0){
                                        $one = (intval($value['after_money'] / $value['buy_length']));
                                    }else{
                                        $one = (intval($value['after_money'] * $saleRule['id_' . $value['sbr_id']]['after_first_bonus'])) / 100;//第一次获得的提成
                                    }
                                    $buy_length = $value['buy_length'] - 1;
                                    if ($value['buy_length'] === 1) {
                                        $buy_length = $value['buy_length'];
                                    }
                                    $price = round(($value['after_money'] - $one) * 100 / $buy_length) / 100;//非第一次以后每次获得的提成
                                    $data[$key]['obtain_price'] = $one + ($value['after_time'] - 1) * $price;//已经获得的提成
                                    $data[$key]['over_price'] = sprintf('%.2f', $value['after_money'] - $data[$key]['obtain_price']);//还剩下的提成
                                    $data[$key]['over_bonus_price'] = 0;
                                }
                                if ($saleRule['id_' . $value['sbr_id']]['type'] == 0){  //奖金
                                    if ($saleRule['id_' . $value['sbr_id']]['first_bonus'] == 0){
                                        $bonus_one = (intval($value['bonus_royalty'] / $value['buy_length']));
                                    }else{
                                        $bonus_one = (intval($value['bonus_royalty'] * $saleRule['id_' . $value['sbr_id']]['first_bonus'])) / 100;//第一次获得的奖金
                                    }
                                    $buy_length = $value['buy_length'] - 1;
                                    if ($value['buy_length'] === 1) {
                                        $buy_length = $value['buy_length'];
                                    }
                                    $bonus_price = round(($value['bonus_royalty'] - $bonus_one) * 100 / $buy_length) / 100;//非第一次以后每次获得的奖金
                                    $data[$key]['obtain_bonus_price'] = $bonus_one + ($value['after_time'] - 1) * $bonus_price;//已经获得的奖金
                                    $data[$key]['over_bonus_price'] = sprintf('%.2f', $value['bonus_royalty'] - $data[$key]['obtain_bonus_price']);//还剩下的奖金
                                    $data[$key]['obtain_price'] = 0;
                                }

//                                if ($saleRule['id_' . $value['sbr_id']]['after_first_bonus'] == 0){
//                                    $one = (intval($value['after_money'] / $value['buy_length']));
//                                    $bonus_one = (intval($value['bonus_royalty'] / $value['buy_length']));
//                                }else{
//                                    $one = (intval($value['after_money'] * $saleRule['id_' . $value['sbr_id']]['after_first_bonus'])) / 100;//第一次获得的提成
//                                    $bonus_one = (intval($value['bonus_royalty'] * $saleRule['id_' . $value['sbr_id']]['first_bonus'])) / 100;//第一次获得的奖金
//                                }

//                                $buy_length = $value['buy_length'] - 1;
//                                if ($value['buy_length'] === 1) {
//                                    $buy_length = $value['buy_length'];
//                                }
//                                $price = round(($value['after_money'] - $one) * 100 / $buy_length) / 100;//非第一次以后每次获得的提成
//                                $bonus_price = round(($value['bonus_royalty'] - $bonus_one) * 100 / $buy_length) / 100;//非第一次以后每次获得的奖金
//                                $data[$key]['obtain_price'] = $one + ($value['after_time'] - 1) * $price;//已经获得的提成
//                                $data[$key]['obtain_bonus_price'] = $bonus_one + ($value['after_time'] - 1) * $bonus_price;//已经获得的奖金
//                                $data[$key]['over_price'] = sprintf('%.2f', $value['after_money'] - $data[$key]['obtain_price']);//还剩下的提成
//                                $data[$key]['over_bonus_price'] = sprintf('%.2f', $value['bonus_royalty'] - $data[$key]['obtain_bonus_price']);//还剩下的奖金
                            }
                        }
                    }
                }else{  //没有规则id为固定金额发放
                    $data[$key]['obtain_price'] = $value['after_money'];//已经获得的提成
                    $data[$key]['obtain_bonus_price'] = $value['bonus_royalty'];//已经获得的提成
                    $data[$key]['over_price'] = 0;//还剩下的提成
                    $data[$key]['over_bonus_price'] = 0;//还剩下的奖金提成

                }
                if (trim($surplus_time) != "") {
                    $surplus_time = (int)$surplus_time;
                    if ($surplus_time <= 24 && (($value['buy_length'] - $value['after_time']) != $surplus_time)) {
                        unset($data[$key]);
                    }
                    if ($surplus_time > 24 && (($value['buy_length'] - $value['after_time']) < $surplus_time)) {
                        unset($data[$key]);
                    }
                }
            }
        }
        return array_values($data);
    }

    //累计所有售后提成
    public function getPercentageMoney($list){
        $percentage_money = 0;
        if(!empty($list)) {
            foreach ($list as $value) {
                $percentage_money += $value['after_money'];
            }
        }
        return sprintf("%.2f",$percentage_money);
    }

    //获取售后订单总提成金额
    public function getAfterSaleTotalMoney()
    {
        $total_money = 0;
        $saleRule = [];
        $tmp_res = DB::table('salebonusrule')->get();
        $tmp_res = json_decode(json_encode($tmp_res),true);
        foreach ($tmp_res as $key=>$value){
            $saleRule['id_'.$value['id']] = $value;
        }
        $list = DB::table('after_sale')->get();
        $list = json_decode(json_encode($list),true);
        foreach($list as $key=>$value){
            $total_money += 0;
            if(isset($saleRule['id_'.$value['sbr_id']])){
                if($value['buy_length'] != 0){
                    if($value['after_time']==0){
                        $total_money += $value['after_money'];
                    }else if($value['after_time'] > 0 && $value['buy_length']>1){
                        $one = (intval($value['after_money'] * $saleRule['id_'.$value['sbr_id']]['after_first_bonus']))/100;//第一次获得的提成
                        $price = round(($value['after_money'] - $one)*100/($value['buy_length']-1))/100;//非第一次以后每次获得的提成
                        $obtain_price = $one + ($value['after_time'] - 1) * $price;//已经获得的提成
                        $total_money += sprintf("%.2f",$value['after_money'] - $obtain_price);//还剩下的提成
                    }
                }
            }
        }
        return $total_money;
    }

    //插入售后订单 待完善 改成有什么添加什么数据
    public function afterSaleInsert($data)
    {
        $fields = $data;
        $fields['created_at'] = Carbon::now();
        $fields['updated_at'] = Carbon::now();
        $fields['after_money'] = 0;
        if($data['after_type']==0 && (int)$data['sbr_id']>0){
            $saleRuleModel = new Salebonusrule();
            $saleb = $saleRuleModel->getSaleRuleDetail($data['sbr_id']);
            if(!$saleb){
                return redirect('/admin/aftersale/index')->withErrors("该商品未建立提成规则");
            }
            if ($saleb['type'] == 1){
                if($saleb['rule_type']==0){
                    $fields['after_money'] =  ($saleb['after_bonus'] * ($fields['goods_money']-($fields['goods_money']*($saleb['cost'])/100)))/100;
                }else{
                    $fields['after_money'] =  $saleb['after_bonus'];
                }
            }else{
                $fields['after_money'] =  $data['after_money'];
            }

        }else if($data['after_type']==2){
            $fields['after_money'] = $data['after_money'];
        }
        if (!$fields['sbr_id']){
            $fields['sbr_id'] = 0;
        }
        $res = DB::table($this->table_name)->insert($fields);
        if(!$res){
            return false;
        }
        return true;
    }

    //更新售后订单 待完善 改成有什么更新什么 不全部更新
    public function afterSaleUpdate($id,$data)
    {
        $fields = $data;
        $fields['updated_at'] = Carbon::now();
        $fields['after_money'] = 0;
        if($data['after_type']==0 && (int)$data['sbr_id']>0){
            $saleRuleModel = new Salebonusrule();
            $saleb = $saleRuleModel->getSaleRuleDetail($data['sbr_id']);
            if(!$saleb){
                return redirect('/admin/aftersale/index')->withErrors("该商品未建立提成规则");
            }
            if($saleb['rule_type']==0){
                $fields['after_money'] =  ($saleb['after_bonus'] * ($fields['goods_money']-($fields['goods_money']*($saleb['cost'])/100)))/100;
            }else{
                $fields['after_money'] =  $saleb['after_bonus'];
            }
        }else if($data['after_type']==2){
            $fields['after_money'] = $data['after_money'];
        }
        $res = DB::table($this->table_name)->where('id',$id)->update($fields);
        if(!$res){
            return false;
        }
        return true;
    }

    //更新字段
    public function afterSaleUpdateByFields($id,$fields)
    {
        if(!is_array($fields) || count($fields)<1){
            return false;
        }
        $res = DB::table($this->table_name);
        if(is_array($id)){
            $res->whereIn('id',$id);
        }else{
            $res->where('id',$id);
        }
        $fields['updated_at'] = Carbon::now();
        $result = $res->update($fields);
        if(!$result){
            return false;
        }
        return true;
    }

    //获取售后订单订单号
    public function checkOrderNo($order_no)
    {
        $res = DB::table($this->table_name)->where('order_number',$order_no)->first();
        if(!$res){
            return false;
        }
        $result = json_decode(json_encode($res),true);
        if(!isset($result['order_number'])){
            return false;
        }
        return $result['order_number'];
    }

    //删除售后订单
    public function aftersaleDelete($id){
        DB::beginTransaction();
        try {
            $res = DB::table($this->table_name)->where('id',$id)->delete();
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return $ex->getMessage();
        }
    }

    //计算赢得奖金
    public function deservedBonus($list){
        $bonus = 0;
        foreach ($list as $v){
            if ($v['after_time'] != 0 && $v['after_money'] != 0 && $v['buy_length'] != 0){
                $bonus = $bonus + ($v['after_money']/$v['buy_length']*$v['after_time']);
                $bonus = $bonus + ($v['bonus_royalty']/$v['buy_length']*$v['after_time']);
            }
        }
        return sprintf('%.2f',$bonus);
    }

    //停止售后订单 扣除用户提成
    public function deductionAftreSale($fields){
        $where['member_name'] = $fields['member_name'];
        $where['member_phone'] = $fields['member_phone'];
        $where['goods_money'] = $fields['goods_money'];
        $where['goods_name'] = $fields['goods_name'];
        $where['order_number'] = $fields['order_number'];
        $where['buy_time'] = $fields['buy_time'];
        $where['buy_length'] = $fields['buy_length'];
        $where['after_sale_id'] = $fields['after_sale_id'];
        $res = DB::table($this->table_name)->where($where)->first();
        if (!$res){  //无售后
            return true;
        }
        $res = json_decode(json_encode($res),true);
        if ($res['after_money'] == 0){   //无提成
            return true;
        }
        $admin_user_info = DB::table('amin_users')->where('id',$res['after_sale_id'])->select('bonus')->first();
        $admin_user_info = json_decode(json_encode($admin_user_info),true);
        if ($res['sbr_id'] == 0){  //无提成规则 扣除全部
            $bonus['bonus'] = $admin_user_info['bonus'] - $res['after_money'];
            DB::table('amin_users')->where('id',$res['after_sale_id'])->update($bonus);
        }else{  //有提成规则
            $salebonusrule_info = DB::table('salebonusrule')->where('id',$res['sbr_id'])->first();
            if ($salebonusrule_info['rule_type'] == 1){  //固定提成规则 扣除全部
                $bonus['bonus'] = $admin_user_info['bonus'] - $res['after_money'];
                DB::table('amin_users')->where('id',$res['after_sale_id'])->update($bonus);
            }else{  //按比例 提成规则
                if ($res['after_time'] == 0){  //未开始发提成
                    return true;
                }
                $deduct_bonus = 0;
                if ($salebonusrule_info['after_first_bonus'] == 0){  //月平均
                    $deduct_bonus = $res['after_money']/$res['buy_length']*$res['after_time'];
                }else{  //首月自定义
                    $deduct_bonus = $res['after_money']*$salebonusrule_info['after_first_bonus']; //减去首月占比
                    $surplus_bonus = $res['after_money'] - $deduct_bonus;
                    $res['after_time'] = $res['after_time'] -1;
                    $res['buy_length'] = $res['buy_length'] -1;
                    if ($res['after_time'] != 0){
                        $deduct_bonus = $deduct_bonus + ($surplus_bonus/$res['buy_length']*$res['after_time']);
                    }
                }
                $deduct_bonus = sprintf('%.2f',$deduct_bonus);
                $bonus['bonus'] = $admin_user_info['bonus'] - $deduct_bonus;
                DB::table('amin_users')->where('id',$res['after_sale_id'])->update($bonus);
            }
        }
    }
}

<?php

namespace App\Models\Admin;

use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BonusSale extends Model
{
    protected $table_name='bonus_sale';

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

    //获取列表
    public function getBonusSaleList($fields){
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=""){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('branch_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $data['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($result),true);
        return $data;
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

    //获取售后奖金列表(权限+筛选条件)
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
        //计算全部奖金总和
        $total_rows = $res->get();
        $total_rows = json_decode(json_encode($total_rows),true);
        $data['total'] = count($total_rows);
        $rows =  $this->buildAfterSaleListFields($total_rows,trim($fields['surplus_time']));
        if ($rows){
            $expect_bonus = $this->getAfterSaleTotalMoney($rows);
            $not_bonus = sprintf("%.2f",$expect_bonus['not_bonus']);
            $already_bonus = sprintf("%.2f",$expect_bonus['already_bonus']);
        }else{
            $not_bonus = 0.00;
            $already_bonus = 0.00;
        }

        $data['rows'] = [];
        $data['not_bonus'] = $not_bonus;
        $data['already_bonus'] = $already_bonus;
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
                if (isset($saleRule['id_' . $value['sbr_id']])) {
                    if ($saleRule['id_' . $value['sbr_id']]['rule_type'] == 1){
                        $data[$key]['obtain_price'] = $value['after_time'];
                        $data[$key]['over_price'] = 0;
                    }else{
                        if ($value['buy_length'] != 0) {
                            if ($value['after_time'] == 0 || $value['after_type'] !== 0) {
                                $data[$key]['obtain_price'] = 0;
                                $data[$key]['over_price'] = $value['after_money'];
                            } else if ($value['after_time'] > 0 && $value['after_type'] !== 1) {
                                if ($saleRule['id_' . $value['sbr_id']]['first_bonus'] == 0){
                                    $one = (intval($value['after_money'] / $value['buy_length']));
                                }else{
                                    $one = (intval($value['after_money'] * $saleRule['id_' . $value['sbr_id']]['first_bonus'])) / 100;//第一次获得的提成
                                }
                                $buy_length = $value['buy_length'] - 1;
                                if ($value['buy_length'] === 1) {
                                    $buy_length = $value['buy_length'];
                                }
                                $price = round(($value['after_money'] - $one) * 100 / $buy_length) / 100;//非第一次以后每次获得的提成
                                $data[$key]['obtain_price'] = $one + ($value['after_time'] - 1) * $price;//已经获得的提成
                                $data[$key]['over_price'] = sprintf('%.2f', $value['after_money'] - $data[$key]['obtain_price']);//还剩下的提成
                            }
                        }
                    }
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
    public function getAfterSaleTotalMoney($list)
    {
        $not_bonus = 0;            //未获取的奖金
        $already_bonus = 0;        //已获取的奖金
        $saleRule = [];
        $tmp_res = DB::table('salebonusrule')->get();
        $tmp_res = json_decode(json_encode($tmp_res),true);
        foreach ($tmp_res as $key=>$value){
            $saleRule['id_'.$value['id']] = $value;
        }
        foreach($list as $key=>$value){
            if(isset($saleRule['id_'.$value['sbr_id']])){
                if ($saleRule['id_'.$value['sbr_id']]['rule_type'] != 1){
                    if($value['buy_length'] != 0){
                        if($value['after_time']==0){
                            $not_bonus += $value['after_money'];
                        }else if($value['after_time'] > 0 && $value['buy_length']>1){
                            $one = (intval($value['after_money'] * $saleRule['id_'.$value['sbr_id']]['first_bonus']))/100;//第一次获得的提成
                            $price = round(($value['after_money'] - $one)*100/($value['buy_length']-1))/100;//非第一次以后每次获得的提成
                            $obtain_price = $one + ($value['after_time'] - 1) * $price;//已经获得的提成
                            $not_bonus += sprintf("%.2f",$value['after_money'] - $obtain_price);//还剩下的提成
                            $already_bonus +=$obtain_price;
                        }
                    }
                }else{ //规则固定
                    $already_bonus += $value['after_money'];
                }
            }else{  //无规则固定
                $already_bonus += $value['after_money'];
            }
        }
        $data['not_bonus'] = $not_bonus;
        $data['already_bonus'] = $already_bonus;
        return $data;
    }

    //插入售后订单 待完善 改成有什么添加什么数据
    public function afterSaleInsert($data)
    {
        $fields = $data;
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $fields['updated_at'] = Carbon::now()->toDateTimeString();
        $fields['after_money'] = 0;
        if($data['after_type']==0 && (int)$data['sbr_id']>0){
            $saleRuleModel = new Salebonusrule();
            $saleb = $saleRuleModel->getSaleRuleDetail($data['sbr_id']);
            if(!$saleb){
                return redirect('/admin/aftersale/index')->withErrors("该商品未建立提成规则");
            }
            if($saleb['rule_type']==0){
                $fields['after_money'] =  ($saleb['bonus'] * ($fields['goods_money']-($fields['goods_money']*($saleb['cost'])/100)))/100;
            }else{
                $fields['after_money'] =  $saleb['bonus'];
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
}

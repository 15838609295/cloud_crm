<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Salebonusrule extends Model
{
    protected $table_name='salebonusrule';

    public function getRuleList($fields = ['*'])
    {
        $res = DB::table($this->table_name)->select($fields)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 获取指定字段全部规则列表 */
    public function getSaleRuleList($type,$fields = ['*'])
    {
        if(!is_array($fields)){
            return array();
        }
        if ($type != ''){
            if ($type == 1){
                $res = DB::table($this->table_name)->select($fields)->where('status',1)->where('rule_type',0)->get();
            }else{
                $res = DB::table($this->table_name)->select($fields)->where('status',1)->where('rule_type',1)->get();
            }
        }else{
            $res = DB::table($this->table_name)->select($fields)->where('status',1)->get();
        }
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 带筛选条件选取提成规则 */
    public function getSaleBonusRuleListWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('rule_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
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

    public function getSaleRuleDetail($rule_id)
    {
        $res = DB::table($this->table_name)->where('id',$rule_id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //计算销售利润
    public function calculatSaleRuleBouns($rule_id,$buy_money)
    {
        $res = $this->getSaleRuleDetail($rule_id);
        if(!$res){
            return 0;
        }
        //利润 = 售前利润(销售所得) + 售后利润(销售所得) + 余下利润
        // step 1 成本 = 售价 * 成本百分比
        $cost = $buy_money * ($res['cost'])/100;
        // step 2 利润 = 售价 - 成本
        $profit = $buy_money - $cost;
        if ($res['rule_type'] == 0){  //按比例
            //售前利润
            $pre_bonus = $profit/100 * $res['pre_bonus'];
            //售后利润
            $after_bonus = $profit/100 * $res['after_bonus'];
            //售后奖金
            $sale_bonus = $profit/100 * $res['bonus'];
        }else{    //按固定
            //售前利润
            $pre_bonus = $res['pre_bonus'];
            //售后利润
            $after_bonus = $res['after_bonus'];
            //售后奖金
            $sale_bonus = $res['bonus'];
        }
        return array('pre_bonus'=>$pre_bonus,'after_bonus'=>$after_bonus,'sale_bonus'=>$sale_bonus);
    }

    public function saleRuleInsert($data)
    {
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function saleRuleUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    public function saleRuleDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    public function updateStatus($data){
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $where['status'] = $data['status'];
        $res = DB::table($this->table_name)->where('id',$data['id'])->update($where);
        if (!$res){
            return false;
        }
        return true;
    }
}

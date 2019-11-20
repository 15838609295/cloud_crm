<?php

namespace App\Models;

use App\Models\User\UserBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
	protected $table_name = 'orders';

	protected $fillable = [
        'id', 'order_sn', 'title', 'type', 'uid', 'uname', 'price', 'amount', 'total_price', 'pay_type', 'pay_time', 'pay_status', 'status' ,'is_del', 'created_at', 'updated_at'
    ];

	/* 通过id获取基础订单信息 */
	public function getOrderByID($id)
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

    /* 带筛选条件获取订单列表 */
    public function getOrderListWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as o')
            ->select('o.*','au.name','gt.name as typeTxt')
            ->leftJoin('member_extend as me','me.member_id','=','o.uid')
            ->leftJoin('admin_users as au','me.recommend','=','au.id')
            ->leftJoin('goods_type as gt','gt.id','=','o.type');
        $adminUserModel = new UserBase();
        $power = $adminUserModel->getAdminPower($fields['admin_id']);
        if($power!=''){
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('me.recommend',$user_list);
        }else{
            $res->where('au.id','=',$fields['admin_id']);
        }
        if(isset($fields['is_del']) && $fields['is_del']!=''){
            $res->where('o.is_del',$fields['is_del']);
        }
        if($fields['start_time']!='' && $fields['end_time']!=''){
            $res->whereBetween('o.created_at',[$fields['start_time'],$fields['end_time']]);
        }else if($fields['start_time']!='' && $fields['end_time']==''){
            $res->where('o.created_at','>=',$fields['start_time']);
        }else if($fields['start_time']=='' && $fields['end_time']!=''){
            $res->where('o.created_at','=<',$fields['end_time']);
        }
        if($fields['pay_start_time']!='' && $fields['pay_end_time']!=''){
            $res->whereBetween('o.pay_time',[strtotime($fields['pay_start_time']),strtotime($fields['pay_end_time'])]);
        }else if($fields['pay_start_time']!='' && $fields['pay_end_time']==''){
            $res->where('o.pay_time','>=',strtotime($fields['pay_start_time']));
        }else if($fields['pay_start_time']=='' && $fields['pay_end_time']!=''){
            $res->where('o.pay_time','=<',strtotime($fields['pay_end_time']));
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('o.title', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if ($fields['type'] == 1){
            $res->whereNotNull('o.owner_uin');
        }else{
            $res->whereNull('o.owner_uin');
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

    /* 修改订单 */
    public function orderUpdate($id,$data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }
}
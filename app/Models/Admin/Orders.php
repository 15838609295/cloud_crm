<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Orders extends Model
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
    public function getOrderListWithFilter($user_id,$fields)
    {
        $res = DB::table($this->table_name.' as o')
            ->select('o.*','au.name')
            ->leftJoin('member as m','m.id','=','o.uid')
            ->leftJoin('admin_users as au','m.recommend','=','au.id');
        $adminUserModel = new AdminUser();
        $power = $adminUserModel->getAdminPower($user_id);
        if($power!=''){
            $user_list = $adminUserModel->getAdminSubuser($user_id);
            $res->whereIn('m.recommend',$user_list);
        }else{
            $res->where('au.id','=',$user_id);
        }

        if($fields['is_del']){
            $res->where('o.is_del',$fields['is_del']);
        }
        if(isset($fields['nexttime']) && $fields['nexttime']!=''){
            $nexttime_start = substr(trim($fields['nexttime']),0,10).' 00:00:00';
            $nexttime_end = substr(trim($fields['nexttime']),13,10).' 23:59:59';
            $res->whereBetween('o.pay_time',[$nexttime_start,$nexttime_end]);
        }
        if(isset($fields['create_at']) && $fields['create_at']!=''){
            $nexttime_start = substr(trim($fields['created_at']),0,10).' 00:00:00';
            $nexttime_end = substr(trim($fields['created_at']),13,10).' 23:59:59';
            $res->whereBetween('o.created_at',[$nexttime_start,$nexttime_end]);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('o.title', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
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
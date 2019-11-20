<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Monolog\Handler\IFTTTHandler;

class AdminBonusLog extends Model
{
    protected $table_name='admin_bonus_log';


    public function getBonusLogWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as abl')
            ->select('au.name as admin_name','au.mobile as admin_mobile','abl.*')
            ->leftjoin('admin_users as au','admin_users_id','=','au.id');

        if ($fields['type'] != ''){
            if ($fields['type'] == 1){  //入账
                $types = ['2','5'];
                $res->whereNotIn('abl.type',$types);
            }else{   //提现明细
                $types = ['2','5'];
                $res->whereIn('abl.type',$types);
            }
        }
        if ($fields['start_time'] !='' && $fields['end_time'] != ''){
            $res->whereBetween('abl.created_at',[$fields['start_time'],$fields['end_time']]);
        }elseif ($fields['start_time'] != '' && $fields['end_time'] == ''){
            $res->where('abl.created_at','<',$fields['start_time']);
        }elseif ($fields['start_time'] == '' && $fields['end_time'] != ''){
            $res->where('abl.created_at','>',$fields['end_time']);
        }

        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('abl.member_name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('abl.goods_name', 'LIKE', '%' .$searchKey . '%')
                    ->orwhere('au.name', 'LIKE', '%' .$searchKey . '%')
                    ->orwhere('abl.order_number', 'LIKE', '%' .$searchKey . '%')
                    ->orwhere('abl.member_phone', 'LIKE', '%' .$searchKey . '%');
            });
        }
        if ($fields['user_id'] != 1){
            $res->where('abl.admin_users_id',$fields['user_id']);
        }elseif ($fields['user_id'] == 1 && $fields['id'] != ''){
            $res->where('abl.admin_users_id',$fields['id']);
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

    public function getApiBonusLogList($admin_id,$start)
    {
        $res = DB::table('admin_bonus_log as abl')
            ->select('au.name as admin_name','abl.*')
            ->leftjoin('admin_users as au','admin_users_id','=','au.id')
            ->where('abl.admin_users_id','=',$admin_id)
            ->orderBy('abl.id','desc')
            ->skip($start)->take(20)
            ->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function adminBonusLogInsert($data)
    {
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }
}

<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserMember extends Model
{
    protected $table_name='admin_users';

    public function getIndexMemberList($fields)
    {
        $date = Carbon::now()->toDateTimeString();
        $res = DB::table('customer as c')
            ->select('c.*','au.name as admin_name','c.source as source_name')
            ->leftJoin($this->table_name.' as au','au.id','=','c.recommend')
            ->where('c.cust_state','=',0);
        //权限判断
        if(isset($fields['admin_id']) && $fields['admin_id']!=''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('c.recommend',$user_list);
            if(count($user_list)>1){
                $res->whereBetween('c.contact_next_time',[Carbon::now()->subDays(7),Carbon::now()]);
            }else{
                $res->where('c.contact_next_time','LIKE',substr($date,0,10));
            }
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->get();
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
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Communicationlog extends Model
{
    protected $table_name = 'communicationlog';

    protected $fillable = [
        'id','member_id', 'admin_user_id', 'comm_time','contentlog','created_at','updated_at'
    ];

    public function getFollowUpRecordByUid($id)
    {
        $res = DB::table($this->table_name.' as cl')
            ->select('cl.*','au.name as adminname')
            ->leftJoin('admin_users as au', 'au.id', '=', 'cl.admin_user_id')
            ->where('cl.member_id', '=', $id)
            ->orderBy('cl.id','desc')
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

    public function communicationLogInsert($data)
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

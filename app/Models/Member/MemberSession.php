<?php

namespace App\Models\Member;

use App\Library\Tools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberSession extends Model
{
    const EXPIRE_TIME = 172800;         //2 * 24 * 3600

    protected $table_name = 'member_session';

    public function getSession($session_id)
    {
        $res = DB::table($this->table_name)
            ->where('session_id',$session_id)
            ->where('expire_time','>',time())
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

    /* 设置session */
    public function setSession($session_data)
    {
        if(!is_array($session_data)){
            return false;
        }
        if(!isset($session_data['member_id'])){
            return false;
        }
        if (!isset($session_data['session_id'])) {
            $session_id = $this->createSessionId();
        }else{
            $session_id = $session_data['session_id'];
        }
        //$this->sessionDelete($session_id);
        $fields = array(
            'session_id' => $session_id,
            'member_id' => $session_data['member_id'],
            'login_ip' => Tools::get_client_ip(),
            'expire_time' => (time() + self::EXPIRE_TIME),
            'create_time' => time()
        );
        if (isset($session_data['msg'])){
            $fields['code'] = $session_data['msg'];
        }
        $res = DB::table($this->table_name)->insert($fields);
        if(!$res){
            return null;
        }
        return $session_id;
    }

    public function sessionDelete($session_id)
    {
        $res = DB::table($this->table_name)->where('session_id',$session_id)->update(['expire_time'=>time()]);
        if(!$res){
            return false;
        }
        return true;
    }

    public function createSessionId()
    {
        return Tools::createGUID();
    }
}

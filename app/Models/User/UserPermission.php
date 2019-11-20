<?php

namespace App\Models\User;

use App\Library\Tools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserPermission extends Model
{
    const EXPIRE_TIME = 172800;         //2 * 24 * 3600

    protected $table_name = 'admin_session';

    public function setSession($session_data)
    {
        if(!is_array($session_data)){
            return false;
        }
        if(!isset($session_data['admin_id'])){
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
            'admin_id' => $session_data['admin_id'],
            'login_ip' => Tools::get_client_ip(),
            'expire_time' => (time() + self::EXPIRE_TIME),
            'create_time' => time()
        );
        $res = DB::table($this->table_name)->insert($fields);
        if(!$res){
            return null;
        }
        return $session_id;
    }

    public function sessionDelete($session_id)
    {
//        $res = DB::table($this->table_name)->where('session_id',$session_id)->delete();
//        if(!$res){
//            return false;
//        }
//        return true;
    }

    public function createSessionId()
    {
        return Tools::createGUID();
    }
}

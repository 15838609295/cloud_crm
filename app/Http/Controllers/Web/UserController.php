<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController{

    public function __construct(Request $request){
        parent::__construct($request);
    }

	/**
	 * 前端修改密码
	 * 
	 */
	public function passupdate(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
		$user = DB::table('member')->where('id','=',$this->AU)->first();
        $user = json_decode(json_encode($user),true);
		$pass = $request->pass;
     	if (isset($pass) && $pass != '') {
            $user_data['password'] = bcrypt($pass);
            $user_data['update_time']  = Carbon::now();
        }
        $bool=DB::table('member')->where('id','=',$this->AU)->update($user_data);
        if($bool){
        	return response()->json($this->result);
        }else{
        	$this->returnData['code'] = 1;
        	$this->returnData['msg'] = '修改失败';
        	return response()->json($this->returnData);
        }
	}
	
	/**
	 * 前端修改头像
	 * 
	 */
	public function picurl(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
		$data['avatar'] = $request->avatar;
		$bool = DB::table('member_extend')->where('member_id','=',$this->AU)->update($data);
		if($bool){
			return response()->json($this->returnData);
		}else{
			$this->returnData['code'] = 1;
			$this->returnData['msg'] = '修改失败';
			return response()->json($this->returnData);
		}
	}
}

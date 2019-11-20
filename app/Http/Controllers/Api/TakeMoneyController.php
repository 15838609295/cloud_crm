<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AdminUser;
use App\Models\Admin\AdminBonusLog;
use App\Models\Admin\Configs;
use App\Models\Admin\Withdrawal;
use Illuminate\Support\Facades\DB;

class TakeMoneyController extends BaseController {

	//提现申请
	public function takeMoney(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
		    $this->returnData['status'] =1;
		    $this->returnData['msg'] = 'admin_id不能为空';
		    return response()->json($this->returnData);
		}
		if(!isset($params["bonus_money"])||trim($params["bonus_money"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'bonus_money不能为空';
            return response()->json($this->returnData);
		}
        if(!isset($params["type"])||trim($params["type"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'type不能为空';
            return response()->json($this->returnData);
        }
        //提现规则验证
        $configsModel = new Configs();
        $res = $configsModel->checkTakeMoney($params["admin_id"],$params["bonus_money"],$params["type"]);
        if ($res == 1){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '提现金额小于规定金额';
            return response()->json($this->returnData);
        }elseif ($res == 2 || $res == 3){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '提现金额大于规定金额';
            return response()->json($this->returnData);
        }elseif ($res == 4 ){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '今天提现次数已用尽';
            return response()->json($this->returnData);
        }elseif ($res == 5){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '当月提现次数已用尽';
            return response()->json($this->returnData);
        }

		//判断可选参数
		if(!isset($params["remarks"])||trim($params["remarks"])==''){
			$params["remarks"] = '';
		}
		
		$admin = AdminUser::where('id','=',$params["admin_id"])->first();
        if($this->openid != $admin['openid']){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '用户信息错误';
            return response()->json($this->returnData);
        }
        if ($params["type"] == 1){
            if($params["bonus_money"] > $admin->bonus){
                $this->returnData['status'] =1;
                $this->returnData['msg'] = '提现金额不能大于提成总额';
                return response()->json($this->returnData);
            }
        }elseif ($params["type"] == 2){
            if($params["bonus_money"] > $admin->sale_bonus){
                $this->returnData['status'] =1;
                $this->returnData['msg'] = '提现金额不能大于奖金总额';
                return response()->json($this->returnData);
            }
        }
		
		$data_ins['admin_users_id'] = $admin->id;
		$data_ins['order_number'] = $this->getOrderSn();
		$data_ins['bonus_money'] = $params["bonus_money"];
        if ($params["type"] == 1){
            $data_ins['cur_bonus'] = $admin->bonus - $params["bonus_money"];
        }elseif ($params["type"] == 2){
            $data_ins['cur_bonus'] = $admin->sale_bonus - $params["bonus_money"];
        }
		$data_ins['remarks'] = $params["remarks"];
		$data_ins['status'] = 0;
		$data_ins['handle_id'] = 0;
        $data_ins['type'] = $params["type"];
		//开启事务
	    DB::beginTransaction();
        try {
        	//提现表更新
            $withDrawalModel = new Withdrawal();
            $take_ins_res = $withDrawalModel->takeMoneyInsert($data_ins);
			//管理员表更新
            if ($params["type"] == 1){
                $data_upd['bonus'] = $admin->bonus - $params["bonus_money"];
            }elseif ($params["type"] == 2){
                $data_upd['sale_bonus'] = $admin->sale_bonus - $params["bonus_money"];
            }
			$admin_upt_res = AdminUser::where('id','=',$admin->id)->update($data_upd);
			if($take_ins_res && $admin_upt_res){
                DB::commit();
            }else{
			    DB::rollback();
                $this->returnData['status'] =1;
                $this->returnData['msg'] = '提现申请失败';
                return response()->json($this->returnData);
            }
		} catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '提现申请失败';
            return response()->json($this->returnData);
        }
        return response()->json($this->returnData);
	}

    //获取提成记录
    public function getBonusList(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'admin_id不能为空';
            return response()->json($this->returnData);
		}
        if(!AdminUser::where('openid','=',$this->openid)->where('id','=',$params["admin_id"])->count()){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '用户信息错误';
            return response()->json($this->returnData);
        }
		//判断有没有可选参数
		if(!isset($params['page']) || trim($params['page']) == ''){
            $params['page'] = 1;
    	}
    	$start = ((int)$params['page']-1)*20;  //截取部分数据
		$adminBonusModel = new AdminBonusLog();
        $abl = $adminBonusModel->getApiBonusLogList($params["admin_id"],$start);
      	if(!$abl){
            $abl = [];
        }
        foreach($abl as $k => $v){
            $abl[$k]['created_at'] = substr($v['created_at'],0,10);
        }

		$this->returnData['data'] = $abl;
        return response()->json($this->returnData);
	}	

	//获取提现记录
	public function getTakeMoneyList()
    {
        $params = request()->post();
		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'admin_id不能为空';
            return response()->json($this->returnData);
		}
        if(!AdminUser::where('openid','=',$this->openid)->where('id','=',$params["admin_id"])->count()){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = '用户信息错误';
            return response()->json($this->returnData);
        }
		//判断有没有可选参数
		if(!isset($params['page']) || trim($params['page']) == ''){
            $params['page'] = 1;
    	}
    	$start = ((int)$params['page']-1)*20;  //截取部分数据

        $takeMoneyModel = new Withdrawal();
        $res = $takeMoneyModel->getApiTakeMoneyList($params["admin_id"],$start);
        if(!$res){
            $res = [];
        }
        foreach($res as $k => $v){
            $res[$k]['created_at'] = substr($v['created_at'],0,10);
        }
		$this->returnData['data'] = $res;
        return response()->json($this->returnData);
	}
  	
  	//修改用户信息
    public function updateUsers(){
        if ($this->returnData['status'] > 0){
            return response()->json($this->returnData);
        }
        $params = request()->post();
  		//判断必传参数
		if(!isset($params["admin_id"])||trim($params["admin_id"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'admin_id不能为空';
            return response()->json($this->returnData);
		}
		if(!isset($params["sex"])||trim($params["sex"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'sex不能为空';
            return response()->json($this->returnData);
		}
		if($params["sex"]!='男' && $params["sex"]!='女'){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'sex传值有误';
            return response()->json($this->returnData);
		}
		if(!isset($params["password"])||trim($params["password"])==''){
            $this->returnData['status'] =1;
            $this->returnData['msg'] = 'password不能为空';
            return response()->json($this->returnData);
		}
		
		$data_upd['sex'] = $params["sex"];
		$data_upd['password'] = bcrypt($params["password"]);
		
		$bool = AdminUser::where('id','=',$params["admin_id"])->update($data_upd);
		if(!$bool){
			$this->returnData['status'] = 1;
			$this->returnData['msg'] = '修改失败！！！';
		}
        return response()->json($this->returnData);
  	}
	
	//获取订单号
    private function getOrderSn()
    {
        $order_sn = date("ymdHis").rand(1000,9999);
        $take_money_model = new Withdrawal();
        $res = $take_money_model->getTakeMoneyByOrderNo($order_sn);
        if(is_array($res)){
            $this->getOrderSn();
        }else{
            return $order_sn;
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AdminBonusLog;
use App\Models\Admin\WalletLogs;
use App\Models\Admin\Withdrawal;
use App\Models\Member\MemberBase;
use App\Models\Member\MemberMoney;
use Illuminate\Http\Request;

class FinanceController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 余额统计 */
    public function balance(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberMoney();
        $data['total_money'] = $memberModel->getMemberTotalMoney('balance');
        $walletModel = new WalletLogs();
        $res = $walletModel->getMonthMoney('balance');
        $data = array_merge($data,$res);
        //有时返回浮点时错乱
        $data['month_money'] = sprintf("%.2f",$data['month_money']);
        $data['month_use_money'] = sprintf("%.2f",$data['month_use_money']);
        $data['total_money'] = sprintf("%.2f",$data['total_money']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 余额记录 */
    public function balanceRecord(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('page_no', 1);
        $page_size = $request->input('page_size', 10);
        $status_type = $request->input('type', '');
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'admin_id' => $this->AU['id'],
            'type' => 'balance',
            'status_type' => $status_type,
        );
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberRichListWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 赠送金统计 */
    public function giftMoney(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberModel = new MemberMoney();
        $data['total_money'] = $memberModel->getMemberTotalMoney('cash_coupon');
        $walletModel = new WalletLogs();
        $res = $walletModel->getMonthMoney('cash_coupon');
        $data = array_merge($data,$res);
        $data['month_money'] = sprintf("%.2f",$data['month_money']);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 赠送金记录 */
    public function giftMoneyRecord(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('page_no', 1);
        $page_size = $request->input('page_size', 10);
        $status_type = $request->input('type', '');
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'admin_id' => $this->AU['id'],
            'type' => 'cash_coupon',
            'status_type' => $status_type,
        );
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberRichListWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 提成记录 */
    public function commissionRecord(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'admin_id' => $this->AU['id']
        );
        $bonusLogModel = new AdminBonusLog();
        $res = $bonusLogModel->getBonusLogWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 提现记录 */
    public function withdrawalsRecord(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
        );
        $withdrawalModel = new Withdrawal();
        $data = $withdrawalModel->getWithdrawalListWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if ($this->AU['id'] != 1){
            $data['code']  =1;
            $data['msg']  = '无权限';
            $data['data']  ='';
            return response()->json($data);
        }
        if (!isset($request->action) || !in_array(strval($request->action),['withdrawal'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        if($request->action == 'withdrawal'){
            if($request->post('check_res')===NULL || !in_array(strval($request->post('check_res')),['pass','refuse'],true)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                return response()->json($this->returnData);
            }
            $data = array(
                'id' => $request->id,
                'check_res' => $request->post('check_res'),
                'admin_id' => $this->AU['id'],
                'admin_name' => $this->AU['name'],
            );
            $withdrawalModel = new Withdrawal();
            $res = $withdrawalModel->dealWithdrawal($data);
            return response()->json($res);
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '未知操作';
        return response()->json($this->returnData);
    }
}
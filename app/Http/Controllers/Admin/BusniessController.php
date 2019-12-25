<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\WalletLogs;
use App\Models\Admin\Withdrawal;
use App\Models\Member\MemberBase;
use App\Models\Member\MemberMoney;
use Illuminate\Http\Request;


class BusniessController extends BaseController
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
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 余额记录 */
    public function balanceDataList(Request $request){
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
            'admin_id' => $this->AU['id'],
            'type' => 'balance'
        );
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberRichListWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
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
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    /* 赠送金记录 */
    public function giftMoneyRecord(Request $request){
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
            'admin_id' => $this->AU['id'],
            'type' => 'cash_coupon'
        );
        $memberModel = new MemberBase();
        $res = $memberModel->getMemberRichListWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    /* 提现记录 */
    public function withdrawalsDataList(Request $request){
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
        return $this->return_result($this->returnData);
    }

    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['withdrawal'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return $this->return_result($this->returnData);
        }
        if($request->action == 'withdrawal'){
            if($request->post('check_res')===NULL || !in_array(strval($request->post('check_res')),['pass','refuse'],true)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                return $this->return_result($this->returnData);
            }
            $data = array(
                'id' => $request->id,
                'check_res' => $request->post('check_res'),
                'admin_id' => $this->AU['id'],
                'admin_name' => $this->AU['name'],
            );
            $withdrawalModel = new Withdrawal();
            $res = $withdrawalModel->dealWithdrawal($data);
            return $this->return_result($res);
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '未知操作';
        return $this->return_result($this->returnData);
    }
}

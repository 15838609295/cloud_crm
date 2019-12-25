<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\AdminBonusLog;

class BonusLogController extends Controller
{
    public $returnData = array(
        'status'=>0,
        'msg'=>'请求成功'
    );

	//获取数据
    public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageno = $request->post("pageNumber") ? $request->post("pageNumber") : 1;
        $pagesize = $request->post("pageSize") ? $request->post("pageSize") : 10;
        $searchFilter = array(
            'sortName' => $request->post("sortName"), //排序列名
            'sortOrder' => $request->post("sortOrder"), //排序（desc，asc）
            'pageNumber' => $pageno, //当前页码
            'pageSize' => $pagesize, //一页显示的条数
            'start' => ($pageno-1) * $pagesize, //开始位置
            'searchKey' => trim($request->post("search",'')) //搜索条件
        );
        $bonusLogModel = new AdminBonusLog();
        $res = $bonusLogModel->getBonusLogWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }
}
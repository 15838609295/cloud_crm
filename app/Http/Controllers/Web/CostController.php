<?php

namespace App\Http\Controllers\Web;

use App\Models\WalletLogs;
use Illuminate\Http\Request;

class CostController extends BaseController
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
    /**
     * 资金明细
     */
    public function wallet(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $sortName = $request->post("sortName",'id');    //排序列名
        $sortOrder = $request->post("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->post("pageNumber");  //当前页码
        $pageSize = $request->post("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $search = $request->post("search",'');  //搜索条件

        $total = WalletLogs::where("uid","=",$this->AU)
            ->where(function ($query) {
                $query->where("type","=","0")
                    ->orwhere("type","=","9")
                    ->orwhere("type","=","5");
            });
        $rows =  WalletLogs::where("uid","=",$this->AU)
            ->where(function ($query) {
                $query->where("type","=","0")
                    ->orwhere("type","=","9")
                    ->orwhere("type","=","5");
            });

        if(trim($search)){
            $total->where(function ($query) use ($search) {
                $query->where('remarks', 'LIKE', '%' . $search . '%');
            });
            $rows->where(function ($query) use ($search) {
                $query->where('remarks', 'LIKE', '%' . $search . '%');
            });
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['total'] = $total->count();
        $data['data']['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();

        return response()->json($data);
    }


    /**
     * 赠送金明细
     */
    public function donation(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $sortName = $request->post("sortName",'id');    //排序列名
        $sortOrder = $request->post("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->post("pageNumber");  //当前页码
        $pageSize = $request->post("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $search = $request->post("search",'');  //搜索条件

        $rows =  WalletLogs::where("uid","=",$this->AU)
            ->where("type","!=","0")
            ->where("type","!=","9")
            ->where("type","!=","5");

        if(trim($search)){
            $rows->where(function ($query) use ($search) {
                $query->where('remarks', 'LIKE', '%' . $search . '%');
            });
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['total'] = $rows->count();
        $data['data']['rows'] = $rows->skip($start)->take($pageSize)
            ->orderBy($sortName, $sortOrder)
            ->get();
        return response()->json($data);
    }

}

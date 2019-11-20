<?php

namespace App\Http\Controllers\Web;

use App\Models\Articles;
use App\Models\MemberExtend;
use Illuminate\Http\Request;

class IndexController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request){
        parent::__construct($request);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data['data']['info'] = MemberExtend::from('member_extend as m')
            ->select('m.realname','m.avatar','m.cash_coupon','m.balance','m.position','m.company','m.qq','m.wechat','ml.name as levelName','ml.discount','me.mobile','me.create_time','me.name')
            ->leftJoin('member_level as ml','ml.id','=','m.level')
            ->leftJoin('member as me','me.id','=','m.member_id')
            ->where('m.member_id','=' ,$this->AU)
            ->first();
        
        $news = Articles::where("is_display","=","0")->where("typeid","=",1);
		        
		$news->where(function ($query) {
            $query->where('read_power', '=', 0)
            ->orwhere('read_power', '=', 2);
        });
        $data['data']["news"] = $news->orderBy("id","desc")->Limit(5)->get();
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        return response()->json($data);
    }

    //获取用户基本信息接口
    public function GetUserInfo(){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data'] = MemberExtend::from('member_extend as m')
            ->select('me.name','ml.name as levelName','ml.discount','me.update_time','m.*','me.tencent_status','me.tencent_discount')
            ->leftJoin('member_level as ml','ml.id','=','m.level')
            ->leftJoin('member as me','me.id','=','m.member_id')
            ->where('m.member_id','=' ,$this->AU)
            ->first();
        $data['data'] = json_decode(json_encode($data['data']),true);
        return response()->json($data);
    }
}

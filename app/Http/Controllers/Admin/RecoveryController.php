<?php
//
//namespace App\Http\Controllers\Admin;
//
//use App\Models\Admin\Goods;
//use Illuminate\Http\Request;
//use App\Http\Controllers\Controller;
//use DB;
//
//class RecoveryController extends Controller
//{
//    private $returnData = array(
//        "status"=>0,
//        'msg'=>'请求成功',
//        'data'=>'',
//    );
//
//
//    /* 获取删除商品列表 */
//    public function getDataList(Request $request)
//    {
//        if (!$request->ajax()) {
//            $this->returnData['status'] = 99;
//            $this->returnData['msg'] = '请求参数不正确';
//            return response()->json($this->returnData);
//        }
//        $pageno = $request->post("pageNumber") ? $request->post("pageNumber") : 1;
//        $pagesize = $request->post("pageSize") ? $request->post("pageSize") : 10;
//        $searchFilter = array(
//            'sortName' => $request->post("sortName"), //排序列名
//            'sortOrder' => $request->post("sortOrder"), //排序（desc，asc）
//            'pageNumber' => $pageno, //当前页码
//            'pageSize' => $pagesize, //一页显示的条数
//            'start' => ($pageno-1) * $pagesize, //开始位置
//            'searchKey' => trim($request->post("search",'')), //搜索条件
//            'is_del' => 1
//        );
//        $goodsModel = new Goods();
//        $data = $goodsModel->getGoodsWithFilter($searchFilter);
//        $this->returnData['data'] = $data;
//        return response()->json($this->returnData);
//    }
//
//    //商品恢复
//    public function destroy($id)
//    {
//        $data = array('is_del' => 0);
//        $goodsModel = new Goods();
//        $res = $goodsModel->goodsUpdate($id,$data);
//        if(!$res){
//            return redirect('/admin/goods')->withErrors('恢复失败');
//        }
//        return redirect('/admin/goods')->withSuccess('恢复成功');
//    }
//}

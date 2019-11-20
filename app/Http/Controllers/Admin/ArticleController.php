<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Articles;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    protected $fields = array(
        'title' => '',
        'thumb' => '',
        'description' => '',
        'content' => '',
        'read_power' => 0,
        'is_display' => 0,
        'file_url' => '',
        'typeid' => 1,
        'picture_type' => 0,
        'video_cover' => '',
        'articles_type_id' => ''
    );

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    //获取系统默认新闻
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($request->post('typeid')===NULL || !in_array(strval($request->post('typeid')),array('1','2','3','4'))){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                              //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                          //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                  //当前页码
            'pageSize' => $page_size,                                                                                  //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                            //搜索关键词
            'typeid' => trim($request->post('typeid','')),                                               //文章类型
            'articles_type_id' => trim($request->post('articles_type_id','')),                         //文章类型
        );
        $articleModel = new Articles();
        $res = $articleModel->getArticlesWithFilter($searchFilter);
        foreach ($res['rows'] as &$v){
            $v['thumb'] = $this->processingPictures($v['thumb']);
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

//    //获取插件新闻
//    public function partDataList(Request $request){
//        if ($this->returnData['code'] > 0){
//            return $this->returnData;
//        }
//        $page_no = $request->post('page_no', 1);
//        $page_size = $request->post('page_size', 10);
//        $searchFilter = array(
//            'sortName' => $request->post('sortName','id'),                                              //排序列名
//            'sortOrder' => $request->post('sortOrder','desc'),                                          //排序（desc，asc）
//            'pageNumber' => $page_no,                                                                                  //当前页码
//            'pageSize' => $page_size,                                                                                  //一页显示的条数
//            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
//            'searchKey' => trim($request->post('search','')),                                            //搜索关键词
//            'typeid' => 1,
//            'articles_type_id' => trim($request->post('articles_type_id','')),                         //文章类型
//            'type' => 2                                                                                                //系统新闻 类别
//        );
//        $articleModel = new Articles();
//        $res = $articleModel->getArticlesWithFilter($searchFilter);
//        foreach ($res['rows'] as &$v){
//            $v['thumb'] = $this->processingPictures($v['thumb']);
//        }
//        $this->returnData['data'] = $res;
//        return response()->json($this->returnData);
//    }

    /* 文章详情 */
    public function detail($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $articleModel = new Articles();
        $data = $articleModel->getArticlesByID($id);
        if (!is_array($data)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /* 文章增加 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if($request->post('typeid')===NULL || !in_array(strval($request->post('typeid')),array('1','3'))){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $article = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $article[$field] = $request->post($field,$this->fields[$field]);
        }
        $articleModel = new Articles();
        $res = $articleModel->articleInsert($article);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 文章修改 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $articleModel = new Articles();
        $data = $articleModel->getArticlesByID($id);
        if (!is_array($data)){
            $this->returnData = ErrorCode::$admin_enum['not_exist'];
            return response()->json($this->returnData);
        }
        $article = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            if($request->post($field)===NULL){
                continue;
            }
            $article[$field] = $request->post($field,$this->fields[$field]);
        }
        $articleModel = new Articles();
        $res = $articleModel->articleUpdate($id,$article);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }


    /* 文章操作 */
    public function ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!isset($request->action) || !in_array(strval($request->action),['status'],true)){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            return response()->json($this->returnData);
        }
        $id = $request->id;
        if($request->action=='status'){
            if($request->post('is_display')===NULL || !in_array(strval($request->post('is_display')),['0','1'])){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                return response()->json($this->returnData);
            }
            $articleModel = new Articles();
            $res = $articleModel->articleUpdate($id,['is_display'=>$request->post('is_display')]);
            if(!$res){
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '操作失败';
                return response()->json($this->returnData);
            }
            $this->returnData['msg'] = '操作成功';
            return response()->json($this->returnData);
        }
        $this->returnData = ErrorCode::$admin_enum['fail'];
        $this->returnData['msg'] = '未知操作';
        return response()->json($this->returnData);
    }

    /* 文章删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
    	$articleModel = new Articles();
    	$res = $articleModel->articleDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }

    /* 获取更新日志3 所有内容 */
    public function listContent(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $searchFilter = array(
            'sortName' => "created_at",                                                  //排序列名
            'sortOrder' => "desc",                                              //排序（desc，asc）
            'typeid' => 3                                                   //文章类型
        );
        $articleModel = new Articles();
        $res = $articleModel->getArticlesContentWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }
}

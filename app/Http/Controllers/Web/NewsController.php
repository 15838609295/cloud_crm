<?php

namespace App\Http\Controllers\Web;

use App\Models\Admin\Articles;
use Illuminate\Http\Request;
use Db;

class NewsController extends BaseController
{

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /**
     * 列表
     * @return \Illuminate\Http\Response
     */
    public function news(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $sortName = $request->post("sortName",'id');    //排序列名
        $sortOrder = $request->post("sortOrder",'desc');   //排序（desc，asc）
        $pageNumber = $request->post("pageNumber");  //当前页码
        $pageSize = $request->post("pageSize");   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $search = $request->post("search",'');  //搜索条件
        $rows = Articles::where(function ($query){
            $query->where('read_power','=',0)
                ->orwhere('read_power','=',2);
        })
            ->where('typeid','=','1')
            ->where('is_display','=','0');

        if(trim($search)){
            $rows->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
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

    /* 文章详情 */
    public function newInfo($id){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $articleModel = new Articles();
        $data = $articleModel->getArticlesByID($id);
        if (!is_array($data)){
            $res['code'] = 1;
            $res['msg'] = '新闻不存在';
            return response()->json($res);
        }else{
            $res['code'] = 0;
            $res['msg'] = '请求成功';
            $res['data'] = $data;
        }
        return response()->json($res);
    }

    /**
     * 日志
     *
     */
    public function log(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $page_no = $request->post('page_no',1);
        $page_size = $request->post('page_size',10);
        $searchFilter = array(
            'sortName' => "created_at",                                                  //排序列名
            'sortOrder' => "desc",                                               //排序（desc，asc）
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'typeid' => 3,
            'searchKey' => '',
            'is_display' => 0,
            'read_power' => [0,2],
            'articles_type_id' => ''
        );
        $articleModel = new Articles();
        $res = $articleModel->getCustomArticlesWithFilter($searchFilter);
        $data = [];
        foreach ($res["rows"] as $v){
            $month = date("Y-m", strtotime($v['created_at']));
            $data[$month][] = $v;
        }
        $res["rows"] = array_sort($data);
        $this->returnData['data'] = $data;
	    return response()->json($this->returnData);
    }

    /**
    * 加载跟多日志
    *
    * @return \Illuminate\Http\Response
    */	
    public function loadLog(){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data = Articles::where(function ($query){
                    $query->where('read_power','=',0)
                    ->orwhere('read_power','=',2);
     			})
     			->where('typeid','=',3)
     			->where('is_display','=',0)
     			->orderBy("created_at", "desc")
     			->limit(10)
     			->get();
        
	    if($data!=''){
        	$this->returnData["data"] = $data;
	        return response()->json($this->returnData);
	    }else{
	    	$this->returnData['code']=1;
	    	$this->returnData['msg']="请求失败";
	    	return response()->json($this->returnData);
	    }
	}

    /**
     * 使用帮助
     *
     */
    public function help(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data = Articles::find((int)1);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    /**
     * 关于我们
     *
     */
    public function about(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $data = Articles::find((int)2);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }
}

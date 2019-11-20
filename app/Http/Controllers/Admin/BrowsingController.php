<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Articles;
use App\Models\Admin\ArticlesType;
use App\Models\Admin\ServiceHotline;
use Illuminate\Http\Request;


class BrowsingController extends BaseController {

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 使用帮助 */
	public function help(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
	    $articleModel = new Articles();
	    $params = array(
            array('read_power','<',2),
            array('id','=',1)
        );
	    $data = $articleModel->getArticleByCustome($params,'single');
        $this->returnData['data'] = $data ?: [];
        return response()->json($this->returnData);
	}

	/* 关于我们 */
	public function about(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $articleModel = new Articles();
        $params = array(
            array('read_power','<',2),
            array('id','=',2)
        );
        $data = $articleModel->getArticleByCustome($params,'single');
        $this->returnData['data'] = $data ?: [];
        return response()->json($this->returnData);
	}
	
	/* 日志 */
    public function log(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => "created_at",                                                  //排序列名
            'sortOrder' => "desc",                                               //排序（desc，asc）
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'typeid' => 3,
            'searchKey' => '',
            'is_display' => 0,
            'read_power' => [0,1],
            'articles_type_id' => ''
        );
        $articleModel = new Articles();
        $res = $articleModel->getCustomArticlesWithFilter($searchFilter);
        $data = [];
        foreach ($res["rows"] as $v){
            $v['thumb'] = $this->processingPictures($v['thumb']);
            $month = date("Y-m", strtotime($v['created_at']));
            $data[$month][] = $v;
        }
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

	/* 加载外部新闻列表 */
//    public function getDataList(Request $request){
//        $this->checUserState();
//
//        $page_no = $request->post('page_no', 1);
//        $page_size = $request->post('page_size', 10);
//        $searchFilter = array(
//            'sortName' => $request->post('sortName','created_at'),                                      //排序列名
//            'sortOrder' => $request->post('sortOrder','desc'),                                          //排序（desc，asc）
//            'pageSize' => $page_size,                                                                                  //一页显示的条数
//            'start' => ($page_no-1) * $page_size,                                                                      //开始位置
//            'searchKey' => trim($request->post('search','')),                                            //搜索条件
//            'typeid' => 1,
//            'is_display' => 0,
//            'read_power' => [0,2],
//            'articles_type_id' => $request->post('articles_type_id','')
//        );
//        $articleModel = new Articles();
//        $res = $articleModel->getCustomArticlesWithFilter($searchFilter);
//        $this->returnData['data'] = $res;
//        return response()->json($this->returnData);
//    }

    /* 查看新闻详情 */
    public function view($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $articleModel = new Articles();
        $data = $articleModel->getArticlesByID((int)$id);
        $this->returnData['data'] = $data ?: [];
        return response()->json($this->returnData);
    }

    //新闻类型列表
    public function type_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',20);
        $fields = [
            'start' => ($page_no - 1) * $page_size,
            'pageSize' => $page_size,
            'sortName' => $request->post("sortName",'id'),
            'sortOrder' => $request->post("sortOrder",'desc'),
            'type' => $request->post('type','')
        ];
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->getList($fields);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //添加新闻类型
    public function add_type(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        $data['type'] = $request->input('type','');
        $data['status'] = $request->input('status','');
        $data['sort'] = $request->input('sort','');
        $data['cid'] = $request->input('cid','');
        $data['icon'] = $request->input('icon','');
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->addType($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //修改新闻类型
    public function update_type(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        $id = $request->input('id','');
        $data['status'] = $request->input('status','');
        $data['icon'] = $request->input('icon','');
        $data['sort'] = $request->input('sort','');
        $data['type'] = $request->input('type','');
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->updateType($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //删除新闻类型
    public function del_type($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->delType($id);
        if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请删除此分类下的子分类';
        }elseif (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return response()->json($this->returnData);
    }

    //修改类型权重
    public function update_sort(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['sort'] = $request->input('sort','');
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->updateSort($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //修改类型显示状态
    public function update_status(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->input('id','');
        $data['status'] = $request->input('status','');
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->updateStatus($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //系统默认类型列表
    public function typeList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $fields = [
            'type' => $request->post('type','')
        ];
        $articlesTypeModel = new ArticlesType();
        $res = $articlesTypeModel->getTypeList($fields);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //服务热线列表
    public function hotlineList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',20);
        $data =[
            'pageSize' => $pageSize,
            'start' => ($pageNo -1)*$pageSize,
            'sortName' => $request->post('sortName','id'),
            'sortOrder'=> $request->post('sortOrder','desc'),
            "search" => $request->post('search',''),
            "status" => $request->post('status',''),
        ];
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->getList($data);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //新增服务热线
    public function addHotline(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->post('name','');
        $data['user_name'] = $request->post('user_name','');
        $data['mobile'] = $request->post('mobile','');
        $data['status'] = $request->post('status',1);
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->addData($data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['addfail'];
        }
        return response()->json($this->returnData);
    }

    //修改服务热线
    public function updateHotline(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $data['name'] = $request->post('name','');
        $data['user_name'] = $request->post('user_name','');
        $data['mobile'] = $request->post('mobile','');
        $data['status'] = $request->post('status','');
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->updateInfo($id,$data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }
        return response()->json($this->returnData);
    }

    //删除服务热线
    public function delHotline(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->delInfo($id);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['delfail'];
        }
        return response()->json($this->returnData);
    }

    //修改状态
    public function updateHotlineStatus(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $data['status'] = $request->post('status','');
        $serviceHotlineModel = new ServiceHotline();
        $res = $serviceHotlineModel->updateInfo($id,$data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }
        return response()->json($this->returnData);
    }
}
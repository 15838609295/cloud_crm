<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use Illuminate\Http\Request;
use App\Models\Cases;

class CaseController extends BaseController
{
    protected $fields = [
        'case_name' => '',
        'case_pic' => '',
        'case_version' => '',
        'type' => '',
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

	public function typeList(){
        $data = array(
            ['id' => 1,'type_name' => '餐饮'],
            ['id' => 2,'type_name' => '酒店'],
            ['id' => 3,'type_name' => '教育'],
            ['id' => 4,'type_name' => '零售'],
            ['id' => 5,'type_name' => '电商'],
            ['id' => 6,'type_name' => '外卖'],
            ['id' => 7,'type_name' => '婚庆'],
            ['id' => 8,'type_name' => '房产'],
            ['id' => 9,'type_name' => '鲜花'],
            ['id' => 10,'type_name' => 'KTV'],
            ['id' => 11,'type_name' => '超市'],
            ['id' => 12,'type_name' => '多商家'],
            ['id' => 13,'type_name' => '珠宝'],
            ['id' => 14,'type_name' => '旅游'],
            ['id' => 15,'type_name' => '运动'],
            ['id' => 16,'type_name' => '美容'],
            ['id' => 17,'type_name' => '家居'],
            ['id' => 18,'type_name' => '农业'],
            ['id' => 19,'type_name' => '医药'],
            ['id' => 20,'type_name' => '母婴'],
            ['id' => 21,'type_name' => '摄影'],
            ['id' => 22,'type_name' => '社区'],
            ['id' => 23,'type_name' => '汽车'],
            ['id' => 24,'type_name' => '资讯'],
            ['id' => 25,'type_name' => '金融'],
            ['id' => 26,'type_name' => '家政'],
            ['id' => 27,'type_name' => '票务'],
            ['id' => 28,'type_name' => '洗浴'],
            ['id' => 29,'type_name' => '保险']
        );
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

	/* 案例列表 */
	public function dataList(Request $request){
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','asc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索条件
            'type' => trim($request->post('type','')),                                                      //分类
            'is_del' => 0
        );
        $caseModel = new Cases();
        $data = $caseModel->getCasesWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }
	
	/* 添加案例 */
    public function create(Request $request){
        $case = [];
        foreach ($this->fields as $key=>$value) {
            /* 验证参数未做 */
            $case[$key] = $request->post($key);
        }
        $case['is_del'] = 0;
        $caseModel = new Cases();
        $res = $caseModel->caseInsert($case);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 修改案例 */
    public function edit(Request $request){
        $id = $request->id;
        $case = [];
        foreach ($this->fields as $key=>$value) {
            /* 验证参数未做 */
            $case[$key] = $request->post($key);
        }
        $caseModel = new Cases();
        $res = $caseModel->caseUpdate($id,$case);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }
    
    /* 删除案例 */
    public function delete($id){
        $caseModel = new Cases();
        $res = $caseModel->caseDelete((int)$id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
}
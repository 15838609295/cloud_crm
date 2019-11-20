<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Company;
use Illuminate\Http\Request;


class CompanyController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    /* 获取数据列表 */
    public function dataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','asc'),                                               //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search',''))                                                //搜索条件
        );
        $companyModel = new Company();
        $res = $companyModel->getCompanyWithFilter($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 获取公司列表 */
    public function companyList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $columns = ['id','name'];
        $branchModel = new Company();
        $res = $branchModel->getCompanyList($columns);
        if(count($res)<1){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '暂无数据';
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    /* 添加公司 */
    public function create(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $company_name = trim($request->post('name',''));
        if($company_name==''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '公司名称不能为空';
            return response()->json($this->returnData);
        }

        if (!preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $company_name)) {
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '公司名称不能包含特殊字符';
            return response()->json($this->returnData);
        }

        $companyModel = new Company();
        $res = $companyModel->getCompanyByName($company_name);
        if(is_array($res)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '公司名称已使用,请重新输入';
            return response()->json($this->returnData);
        }
        $wechat_channel_id = trim($request->post('wechat_channel_id',1));
        if($wechat_channel_id=='' || !is_numeric($request->post('wechat_channel_id'))){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '企业微信部门ID格式错误';
            return response()->json($this->returnData);
        }
        $res = $companyModel->companyInsert(['name'=>$company_name,'wechat_channel_id'=>$wechat_channel_id]);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '添加失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '添加成功';
        return response()->json($this->returnData);
    }

    /* 修改公司 */
    public function edit(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->id;
        $company_name = trim($request->post('name',''));
        if($company_name==''){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '公司名称不能为空';
            return response()->json($this->returnData);
        }
        $companyModel = new Company();
        $res = $companyModel->getCompanyByName($company_name,$id);
        if(is_array($res)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '团队名称已使用,请重新输入';
            return response()->json($this->returnData);
        }
        $wechat_channel_id = trim($request->post('wechat_channel_id'));
        if($wechat_channel_id=='' || !is_numeric($request->post('wechat_channel_id'))){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '企业微信部门ID格式错误';
            return response()->json($this->returnData);
        }
        $res = $companyModel->companyUpdate($id,['name' => $company_name,'wechat_channel_id' => $wechat_channel_id]);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '修改失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '修改成功';
        return response()->json($this->returnData);
    }

    /* 公司删除 */
    public function delete($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $companyModel = new Company();
        $res = $companyModel->companyDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '删除失败';
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = '删除成功';
        return response()->json($this->returnData);
    }
}

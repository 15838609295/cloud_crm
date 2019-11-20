<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AdminUser;
use App\Models\Company;
use Illuminate\Http\Request;

class HrController extends BaseController
{
    protected $admin_user_fields = [
        'name'  => '',
        'company_id' => '',
        'position'   => '',
        'sex' => '男',
        'email' => '',
        'formal_time' => '',
        'hiredate' => '',
    ];

    protected $admin_user_extend_fields = [
        'birth_date'=> '',
        'nation' => '',
        'age' => 0,
        'idcard_no'   => '',
        'highest_edu'   => '',
        'degree'   => '',
        'marital_status'   => '',
        'stature'   => '',
        'political_affiliation' => '',
        'native_place' => '',
        'reg_permanent_place' => '',
        'reg_permanent_type' => '',
        'technical_title' => '',
        'social_security_no' => '',
        'public_reserve_funds' => '',
        'current_address' => '',
        'home_address' => '',
        'mobile_phone' => '',
        'tel_phone' => '',
        'binduser_name' => '',
        'binduser_work_address' => '',
        'binduser_phone' => '',
        'education_history' => '',
        'work_history' => '',
        'foreigner_language' => '',
        'foreigner_language_status' => '',
        'computer_science_level' => '',
        'skill_title' => '',
        'cantonese_skill_status' => '',
        'certificate_info' => '',
        'lastest_employer_name' => '',
        'lastest_employer_job' => '',
        'lastest_employer_phone' => '',
        'family_info' => '',
        'acquaintance_name' => '',
        'acquaintance_department' => '',
        'acquaintance_job' => '',
        'acquaintance_relation' => '',
        'job_status' => '',
        'form_pic' => '',
        'real_avatar' => '',
        'identity_card_pic' => '',
        'certificate_pic' => '',
        'examination_pic' => '',
        'other_pic' => '',
        'goods_collection' => ''
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

    public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageno = $request->post("page_no", 1);
        $pagesize = $request->post("page_size", 10);
        $searchFilter = array(
            'sortName' => $request->post("sortName"), //排序列名
            'sortOrder' => $request->post("sortOrder", "desc"), //排序（desc，asc）
            'pageNumber' => $pageno, //当前页码
            'pageSize' => $pagesize, //一页显示的条数
            'start' => ($pageno-1) * $pagesize, //开始位置
            'status' => $request->post("status",''),
            'hireDate' => $request->post("hireDate",''),
            'job_status' => $request->post("job_status",''),
            'searchKey' => trim($request->post("search",'')), //搜索条件
            'company_id' => $request->post("company_id",'')
        );
        $adminUserModel = new AdminUser();
        $res = $adminUserModel->getAdminUserListFromHr($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    public function getInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if(!$id){
            return response()->json(ErrorCode::$admin_enum['params_error']);
        }
        $adminUserModel = new AdminUser();
        $res = $adminUserModel->getAdminUserDetail($id);
        if (!$res) {
            return response()->json(ErrorCode::$admin_enum['not_exist']);
        }
        $res["wechat_pic"] = $res["real_avatar"];
        $data['uid'] = $id;
        $companyModel = new Company();
        $company = $companyModel->getCompanyByID($res['company_id']);
        $res['companys_name'] = $company['name'];
        if ($res['real_avatar']){
            $res['real_avatar'] = $this->processingPictures($res['real_avatar']);
        }
        if ($res['wechat_pic']){
            $res['wechat_pic'] = $this->processingPictures($res['wechat_pic']);
        }
        if ($res['identity_card_pic']){
            $res['identity_card_pic'] = json_decode($res['identity_card_pic'],true);
            foreach ($res['identity_card_pic'] as &$v){
                $v = $this->processingPictures($v);
            }
        }else{
            $res['identity_card_pic'] = [];
        }
        if ($res['form_pic']){
            $res['form_pic'] = json_decode($res['form_pic'],true);
            foreach ($res['form_pic'] as &$v){
                $v = $this->processingPictures($v);
            }
        }else{
            $res['form_pic'] = array();
        }
        $data['user_data'] = $res;
        $this->returnData['data'] = $data;
        return response()->json($this->returnData);
    }

    public function update(Request $request,$id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if((int)$id===1){
            $this->returnData = 0;
            $this->returnData['msg'] = "无权编辑该用户";
            return response()->json($this->returnData);
        }
        $res = $request->input();
        foreach ($res as $key=>$value){
            if(isset($this->admin_user_fields[$key])){
                $data[$key] = $value;
            }
            if(isset($this->admin_user_extend_fields[$key])){
                $extend_data[$key] = $value;
            }
        }
        if(!empty($res["wechat_pic"]))
            $extend_data["real_avatar"] =  $res["wechat_pic"];
        $adminUserModel = new AdminUser();
        $res = $adminUserModel->adminUserUpdate((int)$id,$data);
        $extend_res = $adminUserModel->adminUserExtendUpdate((int)$id,$extend_data);
        if(!$res && !$extend_res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "更新资料失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "更新资料成功";
        return response()->json($this->returnData);
    }

    /* 暂不用，后面再处理 */
    public function insert(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $res = $request->input();
        foreach ($res as $key=>$value){
            if(isset($this->admin_user_fields[$key])){
                $data[$key] = $value;
            }
            if(isset($this->admin_user_extend_fields[$key])){
                $extend_data[$key] = $value;
            }
        }
        $data['password'] = bcrypt($res['password'] ?: "123456");
        $adminUserModel = new AdminUser();
        $id = $adminUserModel->adminUserInsert($data);
        $extend_res = $adminUserModel->adminUserExtendUpdate((int)$id,$extend_data);
        if(!$id && !$extend_res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "插入资料失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "插入资料成功";
        return response()->json($this->returnData);
    }

    public function del($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $adminUserModel = new AdminUser();
        $res = $adminUserModel->adminUserDelete($id);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = "删除资料失败";
            return response()->json($this->returnData);
        }
        $this->returnData['msg'] = "删除资料成功";
        return response()->json($this->returnData);
    }
}

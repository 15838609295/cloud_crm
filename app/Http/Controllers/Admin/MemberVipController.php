<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\China;
use App\Models\Admin\Configs;
use App\Models\Admin\IndustryType;
use App\Models\Admin\Picture;
use App\Models\Member\MemberBase;
use App\Models\Member\MemberVip;
use Carbon\Carbon;
use foo\bar;
use Illuminate\Http\Request;
use Excel;
use Symfony\Component\Translation\Dumper\IniFileDumper;

class MemberVipController extends BaseController
{

    protected $member = array(
        'name' => '真实姓名',
        'mobile' => '电话',
        'email' => '邮箱',
        'is_vip' => '会员状态',
        'industry_type_id' => '会员类型',
    );

    protected $member_extend = array(
        'avatar' => '头像',
        'realname' => '真实姓名',
        'position' => '职位',
        'wechat' => '微信',
    );
    protected $member_vip = array(
        'birthday' => '',
        'native_place' => '',
        'nation' => '',
        'political_outlook' => '',
        'education' => '',
        'id_number' => '',
        'school' => '',
        'major' => '',
        'recommender' => '',
        'enterprise_name' => '',
        'position' => '',
        'nature' => '',
        'province' => '',
        'city' => '',
        'area' => '',
        'address' => '',
        'new_high' => '',
        'industry' => '',
        'zip_code' => '',
        'patent' => '',
        'office_phone' => '',
        'website' => '',
        'fax' => '',
        'registered_capital' => '',
        'staff_number' => '',
        'tax_amount' => '',
        'turnover' => '',
        'job_brief' => '',
        'company_profile' => '',
        'sex' => '',
        'main_business' => '',
    );

    public function __construct(Request $request){
        parent::__construct($request);
    }

    //添加会员类型
    public function addIndustryType(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称不能为空或包含特殊字符';
            return response()->json($this->returnData);
        }
        if (mb_strlen($data['name']) > 20){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称长度不能超出20个字';
            return response()->json($this->returnData);
        }
        $industryTypeModel = new IndustryType();
        $res = $industryTypeModel->addInfo($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        if ((int)$res == -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '类型添加数量已达到上限';
        }
        return response()->json($this->returnData);
    }

    //删除会员行业类型
    public function delInidustryType($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        if (!$id){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '参数缺失';
            return response()->json($this->returnData);
        }
        $industryTypeModel = new IndustryType();
        $res = $industryTypeModel->delInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return response()->json($this->returnData);
    }

    //会员类型修改
    public function updateInidustry(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['name'] = $request->input('name','');
        $industryTypeModel = new IndustryType();
        $res = $industryTypeModel->updateInfoId($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //会员类型列表
    public function getInidustryList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $industryTypeModel = new IndustryType();
        $res = $industryTypeModel->getList();
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //获取公司列表
    public function enterpriseList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->getEnterprise();
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //会员列表
    public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1) -1;
        $page_size = $request->input('pageSize',30);
        $searchFilter = [
            'sortName' => $request->input('sotrName','id'),
            'sortOrder' => $request->input('sortOrder','desc'),
            'enterprise_name' => $request->input('enterprise',''),
            'start' => $page_size * $page_no,
            'pageSize' => $page_size,
            'is_vip' => $request->input('status',''),
            'industry_type_id' => $request->input('typeId',''),
            'searchKey' => $request->input('search','')
        ];
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->getList($searchFilter);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //会员详情
    public function getVipInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->getIdInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '用户不存在';
        }else{
            $chinaModel = new China();
            $res['provinceTxt'] = $chinaModel->getName($res['province']);
            $res['cityTxt'] = $chinaModel->getName($res['city']);
            $res['areaTxt'] = $chinaModel->getName($res['area']);
            if ($res['avatar']){
                $res['avatar'] = $this->processingPictures($res['avatar']);
            }
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //添加会员
    public function addMemberVip(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $achievement = $request->post();
        //获取客户表电话
        $memberBaseModel = new MemberBase();
        $mobiles = $memberBaseModel->getMemberList(['m.mobile']);
        foreach ($mobiles as &$v){
            $v = $v['mobile'];
        }
        //邮箱
        $emails = $memberBaseModel->getMemberList(['m.email']);
        foreach ($emails as &$v){
            $v = $v['email'];
        }
        //微信
        $wechats = $memberBaseModel->getMemberList(['me.wechat']);
        foreach ($wechats as &$v){
            $v = $v['wechat'];
        }
        //获取身份证号
        $memberVipModel = new MemberVip();
        $id_numbers = $memberVipModel->getFields(['id_number']);
        foreach ($id_numbers as &$v){
            $v = $v['id_number'];
        }
        if (in_array($achievement['mobile'],$mobiles)){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '手机号已存在';
            return response()->json($this->returnData);
        }
        if (in_array($achievement['email'],$emails)){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '邮箱已存在';
            return response()->json($this->returnData);
        }
        if (in_array($achievement['wechat'],$wechats)){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '微信号已存在';
            return response()->json($this->returnData);
        }
        if (in_array($achievement['id_number'],$id_numbers)){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '身份证号已存在';
            return response()->json($this->returnData);
        }
        $member = [];
        foreach ($this->member as $k=>$v){
            if ($k != 'is_vip'){
                if ($achievement[$k] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = $v.'不能为空';
                    return response()->json($this->returnData);
                }
                $member[$k] = $achievement[$k];
            }
            if ($achievement['status'] == ''){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '会员状态不能为空';
                return response()->json($this->returnData);
            }
            $member['is_vip'] = $achievement['status'];
        }
        $member_extend = [];
        foreach ($this->member_extend as $k=>$v){
            if ($k != 'realname'){
                if ($achievement[$k] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = $v.'不能为空';
                    return response()->json($this->returnData);
                }
                $member_extend[$k] = $achievement[$k];
            }
            if ($achievement['name'] == ''){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '用户名不能为空';
                return response()->json($this->returnData);
            }
            $member_extend['realname'] = $achievement['name'];
        }
        $member_vip = [];
        foreach ($this->member_vip as $k=>$v){
            $member_vip[$k] = $achievement[$k];
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->addMemberVip($member,$member_extend,$member_vip);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return response()->json($this->returnData);
    }

    //会员信息修改
    public function updateMemberVip($id,Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $achievement = $request->post();
        //个人信息
        $member = [];
        foreach ($this->member as $k=>$v){
            if ($k != 'is_vip'){
                if ($achievement[$k] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = $v.'不能为空';
                    return response()->json($this->returnData);
                }
                $member[$k] = $achievement[$k];
            }else{
                if ($achievement['status'] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '会员状态不能为空';
                    return response()->json($this->returnData);
                }else{
                    $member['is_vip'] = $achievement['status'];
                }
            }

        }
        //个人详情
        $member_extend = [];
        foreach ($this->member_extend as $k=>$v){
            if ($k != 'realname'){
                if ($achievement[$k] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = $v.'不能为空';
                    return response()->json($this->returnData);
                }
                $member_extend[$k] = $achievement[$k];
            }else{
                if ($achievement['name'] == ''){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '用户名不能为空';
                    return response()->json($this->returnData);
                }else{
                    $member_extend['realname'] = $achievement['name'];
                }
            }
        }
        $member_extend['company'] = request()->input('enterprise_name','');
        //会员信息
        $member_vip = [];
        foreach ($this->member_vip as $k=>$v){
            $member_vip[$k] = $achievement[$k];
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->updayeMemberVip($id,$member,$member_extend,$member_vip);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    //会员删除
    public function delMemberVip($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->delMemberId($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return response()->json($this->returnData);
    }

    //地区列表
    public function regionList(){
        $chinaModel = new China();
        $res = $chinaModel->getRegionList();
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //导出模板
    public function toExcel(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){
            $fileName = '会员模板表';
            $data['code'] = 3;
            $data['name'] = '会员模板表.xlsx';
            $data['data'] = realpath(base_path('public/download')).'/'.$fileName.'.xlsx';
            return $data;
        }else{
            $fileName = '会员模板表';
            if(is_file(realpath(base_path('public/download')).'/'.$fileName.'.xlsx')){
                return response()->download(realpath(base_path('public/download')).'/'.$fileName.'.xlsx',$fileName.'.xlsx');
            }
        }
        $this->returnData = ErrorCode::$admin_enum['not_exist'];
        $this->returnData['msg'] = '文件不存在';
        return response()->json($this->returnData);
    }

    //批量导入
    public function excleInsert(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $base64_excel = trim($request->post('file',''));
            if (!$base64_excel){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $files = json_decode($base64_excel,true);
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            $img_name = time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传Excel失败';
                return response()->json($this->returnData);
            }
            //下载
            $path = urldecode($url['ObjectURL']);
            $path = substr($path,strripos($path,"/")+1);
            $body = $pictureModel->getObgect($path);
            if (empty($body)){
                $data['code'] = 1;
                $data['msg'] = '导入失败';
                return $data;
            }
            $file_path = tempnam(sys_get_temp_dir(),time());
            file_put_contents($file_path, $body->__toString());
        }else{
            if($request->file('file')===NULL){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入文件不能为空';
                return $r_data;
            }
            $file = $request->file('file')->store('temporary');
            $file_path = 'storage/app/'.iconv('UTF-8', 'GBK',$file);
        }
        $tmp_arr = [];
        //获取客户表电话
        $memberBaseModel = new MemberBase();
        $mobiles = $memberBaseModel->getMemberList(['m.mobile']);
        foreach ($mobiles as &$v){
            $v = $v['mobile'];
        }
        //邮箱
        $emails = $memberBaseModel->getMemberList(['m.email']);
        foreach ($emails as &$v){
            $v = $v['email'];
        }
        //获取身份证号
        $memberVipModel = new MemberVip();
        $id_numbers = $memberVipModel->getFields(['id_number']);
        foreach ($id_numbers as &$v){
            $v = $v['id_number'];
        }
        //获取会员类型
        $industryTypeModel = new IndustryType();
        $types = $industryTypeModel->getList();
        Excel::load($file_path, function($reader) use(&$tmp_arr) {
            $reader = $reader->getSheet(0);
            $tmp_arr = $reader->toArray();
        });
        unset($tmp_arr[0]); //去除头部标题
        if (count($tmp_arr) <= 0){
            $r_data = ErrorCode::$admin_enum['params_error'];
            $r_data['msg'] = '未填写人员信息';
            return $r_data;
        }
        $txt = '';
        foreach ($tmp_arr as $k=>$v){
//            if (in_array(null,$v)){
//                $txt[] = '第'.$k.'行信息缺失，请补充完整';
//                continue;
//            }
            if (in_array($v[17],$mobiles)){
                $txt .= '第'.$k.'行客户电话已存在，请重新确认信息';
                continue;
            }
            if (in_array($v[7],$id_numbers)){
                $txt .= '第'.$k.'行客户身份证号码已存在，请重新确认信息';
                continue;
            }
            if (in_array($v[19],$emails)){
                $txt .= '第'.$k.'行客户邮箱已存在，请重新确认信息';
                continue;
            }
            $member = [
                'name' => $v[0],
                'mobile' => $v[17],
                'password' => bcrypt(123456),
                'email' => $v[19],
                'is_vip' => 2,
                'status' => 1,
                'industry_type_id' => 0
            ];
            foreach ($types as $values){
                if ($v[23] == $values['name']){
                    $member['industry_type_id'] = $values['id'];
                }
            }
            if (!$member['industry_type_id']){
                $txt .= '第'.$k.'行客户会员类型不存在，请重新确认信息';
                continue;
            }
            $member_extend = [
//                'avatar' => $v['avatar'],
                'realname' => $v[0],
                'position' => $v[12],
                'company' => $v[11],
                'wechat' => $v[18]
            ];
            if ($v[2]){
                $entry_date=explode("/",$v[2]);
                $birthday =date("Y-m-d",mktime(0,0,0,$entry_date[0],$entry_date[1],$entry_date[2]));
            }else{
                $birthday = '';
            }
            $member_vip = [
                'birthday' => $birthday,
                'native_place' => $v[3],
                'nation' => $v[4],
                'political_outlook' => $v[5],
                'education' => $v[6],
                'id_number' => $v[7],
                'school' => $v[8],
                'major' => $v[9],
                'recommender' => $v[10],
                'enterprise_name' => $v[11],
                'position' => $v[12],
                'nature' => $v[13],
                'address' => $v[14],
                'new_high' => $v[15],
                'industry' => $v[16],
                'office_phone' => $v[20],
                'registered_capital' => $v[21],
                'staff_number' => $v[22],
                'turnover' => $v[24],
                'tax_amount' => $v[25],
                'company_profile' => $v[26],
                'main_business' => $v[27]
            ];
            $res = $memberVipModel->addMemberVip($member,$member_extend,$member_vip);
            if (!$res){
                $txt .= '第'.$k.'行导入时出错';
            }
        }
        if ($txt != ''){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = $txt;
        }
        return response()->json($this->returnData);
    }

    //会员认证列表
    public function authenticationList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['status'] = $request->input('status',1);
        if ($data['status'] == ''){
            $data['status'] = 1;
        }
        $data['searchKey'] = $request->input('search','');
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',20);
        $data['start'] = ($page_no - 1)*$page_size;
        $data['pageSize'] = $page_size;
        $data['orderName'] = $request->input('orderName','id');
        $data['orderSort'] = $request->input('orderSort','desc');
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->notMemberVip($data);
        $this->returnData['data'] = $res;
        return response()->json($this->returnData);
    }

    //会员详情
    public function memberVipInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->getIdInfo($id);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '请求失败';
        }else{
            $chinaModel = new China();
            if ($res['province']){
                $res['provinceTxt'] = $chinaModel->getName($res['province']);
            }else{
                $res['province'] = '';
            }
            if ($res['city']){
                $res['cityTxt'] = $chinaModel->getName($res['city']);
            }else{
                $res['city'] = '';
            }
            if ($res['area']){
                $res['areaTxt'] = $chinaModel->getName($res['area']);
            }else{
                $res['area'] = '';
            }
            $res['industry_type_id'] = (int)$res['industry_type_id'];
            $this->returnData['data'] = $res;
        }
        return response()->json($this->returnData);
    }

    //认证审核
    public function examineMemberVip(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['is_vip'] = $request->input('is_vip','');
        $data['industry_type_id'] = $request->input('industry_type_id','');
        if ($data['industry_type_id'] == ''){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请选择会员类型';
            return response()->json($this->returnData);
        }
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->examineMemberVip($id,$data);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '审核失败';
        }
        return response()->json($this->returnData);
    }

    //删除认证信息
    public function delInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('member_id');
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->delMemberVipInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData = '删除失败';
        }
        return response()->json($this->returnData);
    }



































}
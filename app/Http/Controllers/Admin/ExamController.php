<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Admin\Exam;
use App\Models\Admin\ExamineeGroup;
use App\Models\Admin\ExamType;
use App\Models\Admin\ItemBank;
use App\Models\Admin\Picture;
use App\Models\Admin\TestPaper;
use App\Models\Member\MemberBase;
use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Excel;
use Monolog\Handler\IFTTTHandler;

class ExamController extends BaseController
{
    protected $exam_fields = [
        'name' => '考试名称未填写',
        'startTime' => '开始考试时间未选择',
        'endTime' => '结束考试时间未选择',
        'qualifiedScore' => '合格分数未填写',
        'totalScore' => '总分',
        'cover' => '考试封面图未上传',
        'explain' => '考试说明未填写',
        'limitTime' => '考试限制时长未填写',
        'number' => '重考次数未填写',
        'examineeId' => '考生组未选择',
        'isRanking' => '是否显示排名未选择',
        'isCopy' => '是否限制粘贴未选择',
        'isSort' => '是否打乱顺序未选择',
        'notice' => '通知方式未选择',
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

    //题目参数判断
    private function paramHandle($data){
        if ($data['name'] == '' || strlen($data['name']) <= 0){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '题目标题不能为空';
            return $this->returnData;
        }
        if ($data['type'] != 3){
            $option = json_decode($data['option'],true);
           if (count($option) <= 0){
               $this->returnData['code'] = 1;
               $this->returnData['msg'] = '请添加选项';
               return $this->returnData;
           }
            foreach ($option as $v){

                if ($v['value'] == '' || !isset($v['value'])){
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '选项不能为空';
                    return $this->returnData;
                }
            }
        }
        if ($data['answer'] == ''){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '答案不能为空';
            return $this->returnData;
        }
        if ((int)$data['fraction'] <= 0 || !$data['fraction']){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '分数必须大于0';
            return $this->returnData;
        }
        return true;
    }

    //试卷类型列表
    public function examTypeList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examTypeModel = new ExamType();
        $res = $examTypeModel->getList();
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //添加试卷类型
    public function addExamType(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        if ($data['name'] == ''){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '参数缺失';
        }
        $examTypeModel = new ExamType();
        $res = $examTypeModel->addData($data);
        if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '类型数量已达上限';
        }
        return $this->return_result($this->returnData);
    }

    //修改试卷类型名称
    public function updateExamType(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['name'] = $request->input('name','');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $examTypeModel = new ExamType();
        $res = $examTypeModel->updateData($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //删除试卷类型
    public function delExamType($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examTypeModel = new ExamType();
        $res = $examTypeModel->delData($id);
        if (!$res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '分类下已有试卷，请先把试卷转移其他分类';
        }elseif (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //试卷列表
    public function testPaperList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',1);
        $data = [
            'start' => ($page_no - 1)*$page_size,
            'pageSize' => $page_size,
            'type_id' => $request->input('typeId',''),
            'search' => $request->input('search','')
        ];
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->testPaperList($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //试卷列表无分页
    public function testPaperNoPage(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->testPaperNoPage();
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //试卷题目列表
    public function testPaperSubjectList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',10);
        $start = ($pageNo -1)*$pageSize;
        $data['type'] = $request->post('type','');
        $data['search'] = $request->post('search','');
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->getTestPaperSubjectList($data);
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            $res['rows']=array_slice($res['rows'],$start,$pageSize);
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //添加试卷
    public function addTestPaper(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '试卷名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $data['type_id'] = $request->input('typeId','');
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->addTestPaper($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return $this->return_result($this->returnData);
    }

    //修改试卷
    public function updateTestPaper(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['name'] = $request->input('name','');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '试卷不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $data['type_id'] = $request->input('typeId','');
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->updateTestPaper($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //删除试卷
    public function delTestPaper($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->delTestPaper($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //添加试卷题目
    public function testAddSubject(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $data['name'] = $request->input('name','');
        $data['annex'] = $request->input('annex','');
        $data['type'] = $request->input('type','');
        $data['must'] = $request->input('must','');
        $data['option'] = $request->input('option','');
        $data['answer'] = $request->input('answer','');
        $data['fraction'] = $request->input('fraction','');
        $data['remarks'] = $request->input('remarks','');
        //处理用户提交造成的空格问题
        $data['answer'] = preg_replace('# #','',$data['answer']);
        $param = $this->paramHandle($data);
        if (isset($param['code'])){
            return $this->return_result($param);
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->addTestSubject($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加题目失败';
        }
        return $this->return_result($this->returnData);
    }

    //试卷从题库批量添加题目
    public function itemAddTest(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $item_ids = $request->input('itemIds','');
        $id = $request->input('id','');
        if (!$item_ids){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请选择题目';
            return $this->return_result($this->returnData);
        }
        $item_ids = explode(',',$item_ids);
        foreach ($item_ids as &$v){
            $v = (int)$v;
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->itemAddTest($id,$item_ids);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加题目失败';
        }
        return $this->return_result($this->returnData);
    }

    //修改试卷题目
    public function updateTestItem(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $item_id = $request->input('itemId','');
        $data['name'] = $request->input('name','');
        $data['annex'] = $request->input('annex','');
        $data['type'] = $request->input('type','');
        $data['must'] = $request->input('must','');
        $data['option'] = $request->input('option','');
        $data['answer'] = $request->input('answer','');
        $data['fraction'] = $request->input('fraction','');
        $data['remarks'] = $request->input('remarks','');
        //处理用户提交造成的空格问题
        $data['answer'] = preg_replace('# #','',$data['answer']);
        $param = $this->paramHandle($data);
        if (isset($param['code'])){
            return $this->return_result($param);
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->updateTestItem($item_id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加题目失败';
        }
        return $this->return_result($this->returnData);
    }

    //删除试卷题目
    public function delTestItem(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $item_id = $request->input('itemId','');
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->delTestIten($id,$item_id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //试卷导出
    public function testPaperToExcel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $ids = $request->input('ids','');
        if (!$ids){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请选择试卷';
            return $this->return_result($this->returnData);
        }
        $ids = explode(',',$ids);
        foreach ($ids as &$v){
            $v = (int)$v;
        }
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->getTestPaperInfo($ids);

        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a =  Excel::create('试卷信息',function($excel) use ($res){
                foreach ($res as $k=>$v) {
                    if (!$v){
                        $arr = [];
                        $number = 0;
                        $dan = 0;
                        $duo = 0;
                        $tian = 0;
                        $name = $v['name'].'-'.$v['type_name'];
                    }else{
                        $arr = [];
                        $name = $v['name'].'-'.$v['type_name'];
                        unset($v['name']);
                        unset($v['type_name']);
                        $arr[] = ['题号','类型','题目','选项','答案','分数','题目解析'];
                        $number = 0;
                        $type = '';
                        $dan = 0;
                        $duo = 0;
                        $tian = 0;
                        foreach ($v['subject'] as $s){
                            if ($s['type'] == 1){
                                $type  = '单选题';
                                $dan +=1;
                            }elseif ($s['type'] == 2){
                                $type  = '多选题';
                                $duo +=1;
                            }elseif ($s['type'] == 3){
                                $type  = '填空题';
                                $tian +=1;
                            }
                            $txt = '';
                            if (is_array($s['option'])){
                                foreach ($s['option'] as $k=>$o){
                                    $txt .= $o['label'].$o['value'].'      ';
                                }
                            }
                            $new_remarks = strip_tags($s['remarks']);
                            $arr[]  = [
                                $s['id'],
                                $type,
                                $s['name'],
                                $txt,
                                $s['answer'],
                                $s['fraction'],
                                $new_remarks
                            ];
                            $number +=$s['fraction'];
                        }
                    }
                    $arr[] = [];
                    $arr[] = array('总分 : '.$number,'单选题 : '.$dan,'多选题 : '.$duo,'填空题 : '.$tian,);
                    $excel->sheet($name, function ($sheet) use ($arr) {
                        $sheet->rows($arr);

                    });
                }
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '试卷信息.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('试卷信息',function($excel) use ($res){
                foreach ($res as $k=>$v) {
                    if (!$v){
                        $arr = [];
                        $number = 0;
                        $dan = 0;
                        $duo = 0;
                        $tian = 0;
                        $name = $v['name'].'-'.$v['type_name'];
                    }else{
                        $arr = [];
                        $name = $v['name'].'-'.$v['type_name'];
                        unset($v['name']);
                        unset($v['type_name']);
                        $arr[] = ['题号','类型','题目','选项','答案','分数','题目解析'];
                        $number = 0;
                        $type = '';
                        $dan = 0;
                        $duo = 0;
                        $tian = 0;
                        foreach ($v['subject'] as $s){
                            if ($s['type'] == 1){
                                $type  = '单选题';
                                $dan +=1;
                            }elseif ($s['type'] == 2){
                                $type  = '多选题';
                                $duo +=1;
                            }elseif ($s['type'] == 3){
                                $type  = '填空题';
                                $tian +=1;
                            }
                            $txt = '';
                            if (is_array($s['option'])){
                                foreach ($s['option'] as $k=>$o){
                                    $txt .= $o['label'].$o['value'].'      ';
                                }
                            }
                            $new_remarks = strip_tags($s['remarks']);
                            $arr[]  = [
                                $s['id'],
                                $type,
                                $s['name'],
                                $txt,
                                $s['answer'],
                                $s['fraction'],
                                $new_remarks
                            ];
                            $number +=$s['fraction'];
                        }
                    }
                    $arr[] = [];
                    $arr[] = array('总分 : '.$number,'单选题 : '.$dan,'多选题 : '.$duo,'填空题 : '.$tian,);
                    $excel->sheet($name, function ($sheet) use ($arr) {
                        $sheet->rows($arr);

                    });
                }
            })->export('xlsx');
        }
    }

    //试卷批量删除题目
    public function batchDelId(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $list = $request->input('itemIds','');
        $id = $request->input('id','');
        if (!$list){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请选要删除的试题';
            return $this->return_result($this->returnData);
        }
        $list = explode(',',$list);
        $testPaperModel = new TestPaper();
        $res = $testPaperModel->batchDel($id,$list);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //下载导入题目模板
    public function downloadExcelDemo(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $fileName = '题目模板导入表';
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){
            $data['code'] = 3;
            $data['name'] = '题目模板导入表.xlsx';
            $data['data'] = realpath(base_path('public/download')).'/'.$fileName.'.xlsx';
            return $data;
        }else{
            if(is_file(realpath(base_path('public/download')).'/'.$fileName.'.xlsx')){
                return response()->download(realpath(base_path('public/download')).'/'.$fileName.'.xlsx',$fileName.'.xlsx');
            }
        }
        $this->returnData = ErrorCode::$admin_enum['not_exist'];
        $this->returnData['msg'] = '文件不存在';
        return $this->return_result($this->returnData);
    }

    //试卷批量上传题目
    public function batchUploadItem(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        $id = $request->post('id','');
        if ($con->env == 'CLOUD'){
            $base64_excel = trim($request->post('file',''));
            if (!$base64_excel){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return $this->return_result($this->returnData);
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
                $this->returnData['msg'] = 'Excel上传失败';
                return $this->return_result($this->returnData);
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
        Excel::load($file_path, function($reader) use(&$tmp_arr) {
            $reader = $reader->getSheet(0);
            $tmp_arr = $reader->toArray();
        });
        array_shift($tmp_arr);
        $txt = '';
        $testPaperModel = new TestPaper();
        foreach ($tmp_arr as $k=>$v){
            if ($v[0] === null){
                continue;
            }
            $item['type'] = $v[0];
            if (!in_array($item['type'],[1,2,3])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入题目类型格式不正确，请仔细查看文件头括号备注信息';
                return $r_data;
            }
            $item['fraction'] = $v[1];
            $item['must'] = $v[2];
            if (!in_array($item['must'],[0,1])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '是否必填格式不正确，请仔细查看文件头括号备注信息';
                return $r_data;
            }
            $item['name'] = $v[3];
            if (!$item['name']){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入题目不能为空';
                return $r_data;
            }
            $item['remarks'] = $v[4];
            $item['answer'] = $v[5];
            if (!isset($item['answer'])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '题目正确答案不能为空';
                return $r_data;
            }
            $list = array();
            $total = 10;
            for ($i = 6;$i < $total;$i++){
                if ($v[$i] != ''){
                    if ($i == 6){
                        $list[0]['label'] = 'A';
                        $list[0]['value'] = $v[$i];
                    }elseif ($i == 7){
                        $list[1]['label'] = 'B';
                        $list[1]['value'] = $v[$i];
                    }elseif ($i == 8){
                        $list[2]['label'] = 'C';
                        $list[2]['value'] = $v[$i];
                    }elseif ($i == 9){
                        $list[3]['label'] = 'D';
                        $list[3]['value'] = $v[$i];
                    }
                }
            }
            if (count($list) > 0){
                $item['option'] = json_encode($list);
            }else{
                $item['option'] = '';
            }
            if($item['type'] != 3){
                if ($item['option'] == ''){
                    $r_data = ErrorCode::$admin_enum['params_error'];
                    $r_data['msg'] = '题目选项不能为空';
                    return $r_data;
                }
            }
            $res = $testPaperModel->addTestSubject($id,$item);
            if (!$res){
                $txt .= '第'.$k.'题添加错误';
            }
        }
        if ($txt){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = $txt;
        }
        return $this->return_result($this->returnData);
    }

    //试题详情
    public function itemInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->itemInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '试题不存在';
        }
        if(isset($res['annex'])){
            $res['annex'] = $this->processingPictures($res['annex']);
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //题库试题删除
    public function itmeDel($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->delItem($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //题库批量删除
    public function itemBatchDel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $list_ids = $request->input('ids','');
        if (!$list_ids){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请选择要删除的题目';
        }
        $list_ids = explode(',',$list_ids);
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->batchDelItem($list_ids);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //题库添加题目
    public function addItemBatch(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->input('name','');
        $data['annex'] = $request->input('annex','');
        $data['type'] = $request->input('type','');
        $data['option'] = $request->input('option','');
        $data['answer'] = $request->input('answer','');
        $data['fraction'] = $request->input('fraction','');
        $data['remarks'] = $request->input('remarks','');
        $data['must'] = $request->input('must','');
        //处理用户提交造成的空格问题
        $data['answer'] = preg_replace('# #','',$data['answer']);
        $param = $this->paramHandle($data);
        if (isset($param['code'])){
            return $this->return_result($param);
        }
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->addItem($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //题库修改题目
    public function updateItemBatch(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('itemId','');
        $data['name'] = $request->input('name','');
        $data['annex'] = $request->input('annex','');
        $data['type'] = $request->input('type','');
        $data['must'] = $request->input('must','');
        $data['option'] = $request->input('option','');
        $data['answer'] = $request->input('answer','');
        $data['fraction'] = $request->input('fraction','');
        $data['remarks'] = $request->input('remarks','');
        //处理用户提交造成的空格问题
        $data['answer'] = preg_replace('# #','',$data['answer']);
        $param = $this->paramHandle($data);
        if (isset($param['code'])){
            return $this->return_result($param);
        }
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->updateItem($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //题库批量上传
    public function itemBatchUpload(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $base64_excel = trim($request->post('file',''));
            if (!$base64_excel){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return $this->return_result($this->returnData);
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
                $this->returnData['msg'] = 'Excel上传失败';
                return $this->return_result($this->returnData);
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
        Excel::load($file_path, function($reader) use(&$tmp_arr) {
            $reader = $reader->getSheet(0);
            $tmp_arr = $reader->toArray();
        });
        array_shift($tmp_arr);
        $txt = '';
        $itemBankModel = new ItemBank();
        foreach ($tmp_arr as $k=>$v){
            if ($v[0] === null ){
                continue;
            }
            $item['type'] = $v[0];
            if (!in_array($item['type'],[1,2,3])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入题目类型格式不正确，请仔细查看文件头括号备注信息';
                return $r_data;
            }
            $item['fraction'] = $v[1];
            $item['must'] = $v[2];
            if (!in_array($item['must'],[0,1])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '是否必填格式不正确，请仔细查看文件头括号备注信息';
                return $r_data;
            }
            $item['name'] = $v[3];
            if (!$item['name']){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '导入题目不能为空';
                return $r_data;
            }
            $item['remarks'] = $v[4];
            $item['answer'] = $v[5];
            if (!isset($item['answer'])){
                $r_data = ErrorCode::$admin_enum['params_error'];
                $r_data['msg'] = '正确答案不能为空';
                return $r_data;
            }
            $list = array();
            $total = 10;
            for ($i = 6;$i < $total;$i++){
                if ($v[$i] != ''){
                    if ($i == 6){
                        $list[0]['label'] = 'A';
                        $list[0]['value'] = $v[$i];
                    }elseif ($i == 7){
                        $list[1]['label'] = 'B';
                        $list[1]['value'] = $v[$i];
                    }elseif ($i == 8){
                        $list[2]['label'] = 'C';
                        $list[2]['value'] = $v[$i];
                    }elseif ($i == 9){
                        $list[3]['label'] = 'D';
                        $list[3]['value'] = $v[$i];
                    }
                }
            }
            if (count($list) > 0){
                $item['option'] = json_encode($list);
            }else{
                $item['option'] = '';
            }
            if($item['type'] != 3){
                if ($item['option'] == ''){
                    $r_data = ErrorCode::$admin_enum['params_error'];
                    $r_data['msg'] = '题目选项不能为空';
                    return $r_data;
                }
            }
            $res = $itemBankModel->additem($item);
            if (!$res){
                $txt .= '第'.$k.'题添加错误';
            }
        }
        if ($txt){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = $txt;
        }
        return $this->return_result($this->returnData);
    }

    //题库列表
    public function itemBankList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$pageSize;
        $data['search'] = $request->post('search','');
        $data['type'] = $request->post('type','');
        $data['classify'] = $request->post('classify','item');
        $data['id'] = $request->post('id','');
        $data['pageSize'] = $pageSize;
        $itemBankModel = new ItemBank();
        $res = $itemBankModel->itemList($data);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //考试管理列表
    public function examList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',20 );
        $data = [
            'start' => ($page_no - 1) * $page_size,
            'page_size' => $page_size,
            'type_id' => $request->input('typeId',''),
            'status' => $request->input('status',''),
            'start_time' =>$request->input('startTime',''),
            'end_time' => $request->input('endTime',''),
            'search' => $request->input('search',''),
            'sort_name' => $request->input('sortName','id'),
            'sort_order' => $request->input('sortOrder','desc'),
            'id' => '',
        ];
        $examModel = new Exam();
        $examGropuModel = new ExamineeGroup();
        $res = $examModel->examList($data);
        $now_time = Carbon::now()->toDateTimeString();
        foreach ($res['rows'] as &$v){
            if ($v['start_time'] > $now_time && $v['end_time'] > $now_time){
                $v['status_txt'] = '未开始';
                $v['status'] = 1;
            }elseif ($v['start_time'] < $now_time && $v['end_time'] > $now_time){
                $v['status_txt'] = '考试中';
                $v['status'] = 2;
            }elseif ($v['end_time'] < $now_time){
                $v['status_txt'] = '已结束';
                $v['status'] = 3;
            }
            $examinee_id = json_decode($v['examinee_id'],1);
            $v['group_name'] = '';
            $name = $examGropuModel->getInArray('name',['id',$examinee_id]);
            foreach ($name as $n_v){
                $v['group_name'] .= $n_v['name'].'/';
            }
            $v['group_name'] = substr($v['group_name'],0,strlen($v['group_name'])-1);
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //停止考试
    public function stopTheExam($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examModel = new Exam();
        $res = $examModel->stopTheExam($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改考试状态失败';
        }
        return $this->return_result($this->returnData);
    }

    //考试列表无分页
    public function itemListNoPage(){
        $examModel = new Exam();
        $res = $examModel->itenListNoPage();
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //创建考试
    public function addExamData(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $exam_data = [];
        foreach ($this->exam_fields as $key => $value) {
            if (!$request->post($key) && $key !='isRanking' && $key !='isCopy' && $key !='isSort' && $key != 'examineeId') {
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = $value;
                return $this->return_result($this->returnData);
            }
            $exam_data[$key] = $request->post($key);
        }
        $exam_data['examineeId'] = $request->post('examineeId','');
        if ($exam_data['examineeId'] == ''){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '未选择考试分组';
            return $this->return_result($this->returnData);
        }
        if (!$exam_data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $exam_data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '考试名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $exam_data['cuttingScreenNumber'] = $request->post('cuttingScreenNumber',0);
        $test_paper_id = $request->post('testId');
        $examModel = new Exam();
        $admin_name = $this->AU['name'];
        $res = $examModel->addExamData($exam_data, $test_paper_id, $admin_name);
        if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '此试卷下无题目，请重新选择';
        }elseif ($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '此试卷下存在有题目没有选项，请补充完整';
        }elseif ($res === -3){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '此试卷下存在有题目的分数小于等于0，请补充完整';
        }elseif ($res === -4){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '此试卷下存在有题目为空，请补充完整';
        }elseif ($res === -5){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '未创建考生分组';
        }elseif (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '创建失败';
        }
        return $this->return_result($this->returnData);
    }

    //考试详情
    public function examInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examModel = new Exam();
        $res = $examModel->examInfoId($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '考试不存在';
        }else{
            if (isset($res['cover'])){
                $res['cover'] = $this->processingPictures($res['cover']);
            }
            $res['subject_list'] = json_decode($res['subject_list'],1);
            foreach ($res['subject_list'] as &$v){
                $v['option'] = json_decode($v['option'],1);
                $v['answer'] = json_decode($v['answer'],1);
            }
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //考试结果统计分析
    public function examResultAnalyse($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examModel = new Exam();
        $res = $examModel->examResultAnalyse($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '考试不存在';
        }elseif ($res === -1){
            $this->returnData['code'] = 0;
            $this->returnData['msg'] = '未有考生参加该考试';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //考试答卷列表
    public function examResultList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $pageNo = $request->input('pageNo',1);
        $pageSzie = $request->input('pageSize',20);
        $data = [
            'pageSize' => $pageSzie,
            'start' => ($pageNo -1)*$pageSzie,
            'sortName' => 'id',
            'sortOrder' => 'desc',
            'status' => $request->input('status',''),
            'search' => $request->input('search',''),
        ];
        $examModel = new Exam();
        $res = $examModel->examResultList($id,$data);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //考试答卷导出
    public function userExamToExcel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $examModel = new Exam();
        $res = $examModel->examResultToExcelData($id);
        $res['list']['name'] = $examModel->getSelResult($id,'name');
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),"1xlsx");  //临时文件
            $a = Excel::create('试卷信息',function($excel) use ($res){
                foreach ($res['rows'] as $k=>&$v) {
                    if ($v['status'] == 1){
                        $v['status_txt'] = '及格';
                    }else{
                        $v['status_txt'] = '不及格';
                    }
                    $arr[] = ['姓名','答题时间','最高成绩','考试次数','状态'];
                    $arr[] = [
                        $v['name'],
                        $v['start_time'],
                        $v['branch'],
                        $v['number'],
                        $v['status_txt']
                    ];
                }
                $arr[] = [];
                $arr[] = ['总分 : '.$res['list']['total_score'],
                    '考试次数: '.$res['list']['exam_number'],
                    '平均分 : '.$res['list']['avg'],
                    '最高分 : '.$res['list']['max'],
                    '最低分 : '.$res['list']['min'],
                    '及格分 : '.$res['list']['qualified_score'],
                    '及格率 : '.$res['list']['pass_rate'],
                    '及格次数 : '.$res['list']['pass']
                ];
                $excel->sheet($res['list']['name'], function ($sheet) use ($arr) {
                    $sheet->rows($arr);
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '试卷信息.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('试卷信息',function($excel) use ($res){
                foreach ($res['rows'] as $k=>&$v) {
                    if ($v['status'] == 1){
                        $v['status_txt'] = '及格';
                    }else{
                        $v['status_txt'] = '不及格';
                    }
                    $arr[] = ['姓名','答题时间','最高成绩','考试次数','状态'];
                    $arr[] = [
                        $v['name'],
                        $v['start_time'],
                        $v['branch'],
                        $v['number'],
                        $v['status_txt']
                    ];
                }
                $arr[] = [];
                $arr[] = ['总分 : '.$res['list']['total_score'],
                    '考试次数: '.$res['list']['exam_number'],
                    '平均分 : '.$res['list']['avg'],
                    '最高分 : '.$res['list']['max'],
                    '最低分 : '.$res['list']['min'],
                    '及格分 : '.$res['list']['qualified_score'],
                    '及格率 : '.$res['list']['pass_rate'],
                    '及格次数 : '.$res['list']['pass']
                ];
                $excel->sheet($res['list']['name'], function ($sheet) use ($arr) {
                    $sheet->rows($arr);
                });
            })->export('xlsx');
        }
    }


    //考生详情
    public function examineeInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->input('type','');
        $uid = $request->input('uid','');
        $id = $request->input('id','');
        //查询用户组
        $examModel = new Exam();
        $name = $examModel->getExamineerName($id);
        if ($type == 1){  //1为客户
            $memberBaseModel = new MemberBase();
            $res = $memberBaseModel->getMemberByID($uid);
        }else if ($type == 2){ //为员工
            $userBaseModel = new UserBase();
            $res = $userBaseModel->getAdminDetailByID($uid);
        }
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '用户信息不存在';
        }else{
            $res['examinee_group_name'] = $name;
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //答卷详情
    public function answerInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',20);
        $start = ($pageNo - 1)*$pageSize;
        $id = $request->post('id','');
        $examModel = new Exam();
        $res = $examModel->answerDetails($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '考试结果不存在';
        }else{
            $pagedata = array_slice($res,$start,$pageSize);
            $this->returnData['data'] = $pagedata;
        }
        return $this->return_result($this->returnData);
    }

    //考生管理
    public function examineeList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',30);
        $data['start'] = ($pageNo - 1)*$pageSize;
        $data['pageSize'] = $pageSize;
        $type = $request->post('type','member');
        $data['typeId'] = $request->post('typeId','');
        $data['search'] = $request->post('search','');
        $examModel = new Exam();
        $res = $examModel->getExamineersList($data,$type);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //考生分组
    public function examineeGroup(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examineeIds = $request->post('examineeIds','');
        $groupIds = $request->post('groupIds','');
        $examineeIds = explode(',',$examineeIds);
        $groupIds = explode(',',$groupIds);
        $examModel = new Exam();
        $res = $examModel->Grouping($examineeIds,$groupIds);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加到分组失败';
        }
        return $this->return_result($this->returnData);
    }

    //批量删除分组考生
    public function batchDelExaminee(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['ids'] = $request->post('ids','');
        $data['group_id'] = $request->post('groupId','');
        $data['ids'] = explode(',',$data['ids']);
        $examModel = new Exam();
        $res = $examModel->batchDelExaminee($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //创建考生组
    public function addExamineeGroup(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->post('name','');
        $data['group_type'] = $request->post('groupType','');
        if ($data['name'] == '' || !isset($data['group_type'])){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '参数缺失';
            return $this->return_result($this->returnData);
        }
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '分组名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $examineeGroupModel = new ExamineeGroup();
        $res = $examineeGroupModel->addExamineeGroupData($data);
        if ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '分组已达上限';
        }elseif(!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return $this->return_result($this->returnData);
    }

    //考生组列表
    public function examineeGroupList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $type = $request->input('type','');
        $examineeGroupModel = new ExamineeGroup();
        $res = $examineeGroupModel->examineeGroupList($type);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //删除考生分组
    public function delExamineeGroup($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examineeGroupModel = new ExamineeGroup();
        $res = $examineeGroupModel->delExamineeGroup($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //分组编辑
    public function updateExamineeGroup(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['name'] = $request->post('name','');
        if (!$data['name'] || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $data['name'])){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '分组名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $examineeGroupModel = new ExamineeGroup();
        $res = $examineeGroupModel->updateExamineeGroup($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //考试统计
    public function examCompute(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $time_type = $request->post('timeType','');
        $examModel = new Exam();
        $res = $examModel->examCompute($time_type);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //试题分析
    public function examAnalyse(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['type'] = $request->post('type','');
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',10);
        $start = ($pageNo -1)*$pageSize;
        $examModel = new Exam();
        $res = $examModel->examAnalyse($data);
        if ($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '有试题不存在选项';
            return $this->return_result($this->returnData);
        }
        $res['rows'] = array_slice($res['rows'],$start,$pageSize);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //试题分析导出
    public function examAnalyseToExcel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->input('id','');
        $data['type'] = 1;
        $examModel = new Exam();
        $res = $examModel->examAnalyse($data);
        if ($res === -2){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '有试题不存在选项';
            return $this->return_result($this->returnData);
        }
        $list = $examModel->examList(['id'=>$data['id'],'status' => '','type_id' => '','start_time' => '','end_time' => '','search' => '','start'=>0,'page_size'=>50,'sort_name'=>'id','sort_order'=>'desc']);
        $res['info'] = $list['rows'][0];
        $name = $examModel->getSelResult($data['id'],'name');
        $arr[] = ['试卷名称','试卷分类','总分'];
        $arr[] = [
            $res['info']['name'],
            $res['info']['type_name'],
            $res['info']['total_score'],
        ];
        $arr[] = [];
        $arr[] = ['题目','题目类型','可选答案','备注','用户选择结果','相同人数'];
        foreach ($res['rows'] as &$v) {
            $type_txt = '';
            //类型
            if ($v['type'] == 1){
                $type_txt = '单选题';
            }elseif($v['type'] == 2){
                $type_txt = '多选题';
            }elseif ($v['type'] == 3){
                $type_txt = '填空题';
            }
            //备注处理
            $v['remarks'] = strip_tags($v['remarks']);
            if ($v['type'] != 3){
                $arr[] = [
                    $v['name'],
                    $type_txt,
                    $v['option'][0]['label'].'.'.$v['option'][0]['value'],
                    $v['remarks'],
                    $v['option'][0]['label'].'.'.$v['option'][0]['value'],
                    $v['option'][0]['number'],
                ];
                for ($i = 1;$i < count($v['option']);$i++){
                    $arr[] = [
                        '',
                        '',
                        $v['option'][$i]['label'].'.'.$v['option'][$i]['value'],
                        '',
                        $v['option'][$i]['label'].'.'.$v['option'][$i]['value'],
                        $v['option'][$i]['number'],
                    ];
                }
            }else{
                $arr[] = [
                    $v['name'],
                    $type_txt,
                    '',
                    $v['remarks'],
                    $v['option'][0]['value'],
                    $v['option'][0]['number'],
                ];
                for ($i = 1;$i < count($v['option']);$i++){
                    $arr[] = [
                        '',
                        '',
                        '',
                        '',
                        $v['option'][$i]['value'],
                        $v['option'][$i]['number'],
                    ];
                }
            }
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a =  Excel::create($name,function($excel) use ($arr){
                $excel->sheet('试题分析', function ($sheet) use ($arr) {
                    $sheet->rows($arr);
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = $name.'.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create($name,function($excel) use ($arr){
                $excel->sheet('试题分析', function ($sheet) use ($arr) {
                    $sheet->rows($arr);
                });
            })->export('xlsx');
        }
    }

    //考试删除
    public function examDel($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $examModel = new Exam();
        $res = $examModel->examDel($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }


































}

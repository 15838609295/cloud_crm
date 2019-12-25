<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Configs;
use App\Models\Admin\Findings;
use App\Models\Admin\Picture;
use App\Models\Admin\Questionnaire;
use App\Models\Member\MemberBase;
use Illuminate\Http\Request;
use Excel;

class QuestionnaireController extends BaseController
{
    protected $fields = [
        'cover' => '',
        'title' => '',
        'remarks' => '',
        'ending' => '',
        'status' => 0,
        'total' => 0,
    ];

    public function __construct(Request $request){
        parent::__construct($request);
    }

    //添加问卷
    public function add_info(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $case = [];
        foreach ($this->fields as $key=>$value) {
            /* 验证参数未做 */
            $case[$key] = $request->post($key);
        }
        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->addQuestionnaire($case);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        return $this->return_result($this->returnData);
    }

    //问卷列表
    public function get_questionnaire_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no',1);
        $page_size = $request->post('page_size',10);
        $searchFilter = [
            'sortName' => $request->post("sortName", "id"), //排序列名
            'sortOrder' => $request->post("sortOrder", "desc"), //排序（desc，asc）
            'pageSize' => $page_size,
            'start' => $page_no -1 * $page_size,
            'status' => $request->post('status'),
            'searchKey' => $request->post('search',''),
            'start_time' => $request->post('start_time',''),
            'end_time' => $request->post('end_time','')
        ];
        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->getList($searchFilter);

        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //删除用户提交的问卷答卷
    public function del_data($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->delQuset($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //答卷列表
    public function get_findings_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no',1);
        $page_size = $request->post('page_size',10);
        $searchFilter = [
            'questionnaire_id' => $request->post('questionnaire_id',''),
            'pageSize' => $page_size,
            'start' => $page_no -1 * $page_size,
            'start_time' => $request->post('start_time',''),
            'end_time' => $request->post('end_time',''),
            'sortName' => $request->post('sort_name','id'),
            'sortOrder' => $request->post('sort_order','desc'),
        ];
        $findingsModel = new Findings();
        $res = $findingsModel->getDateList($searchFilter);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //用户提交的答卷详情
    public function get_findings_info($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $findingsModel = new Findings();
        $res = $findingsModel->getInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //问卷详情导出
    public function findings_to_excel($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $findingsModel = new Findings();
        $res = $findingsModel->getInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '无数据可下载';
            return $this->return_result($this->returnData);
        }
        $txt = $res['que_name'].'-'.$res['member_name'].'答卷';
        unset($res['que_name']);
        unset($res['member_name']);
        $list = [];
        foreach ($res as $k=>$v){
            $list[$k]['title'] = $v['title'];
            if (is_array($v['answer'])){
                foreach ($v['answer'] as $val){
                    if ($val['number'] > 0){
                        $list[$k]['result'] = $val['res'];
                    }
                }
            }else{
                $list[$k]['result'] = $v['answer'];
            }

        }
        $arr = [['问题','填写回答']];
        foreach ($list as $v){
            $arr[] = [
                $v['title'],
                $v['result']
            ];
        }

        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),"1xlsx");  //临时文件
            $a = Excel::create($txt,function($excel) use ($arr,$txt){
                $excel->sheet($txt, function($sheet) use ($arr){
                    $sheet->rows($arr);
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = $txt.'.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create($txt,function($excel) use ($arr,$txt){
                $excel->sheet($txt, function($sheet) use ($arr){
                    $sheet->rows($arr);
                });
            })->export('xlsx');
        }
    }

    //统计数据
    public function statistical_data(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $searchFilter = [
            'questionnaire_id' => $request->post('questionnaire_id',''),
            'start_time' => $request->post('start_time',''),
            'end_time' => $request->post('end_time',''),
            'title_number' => $request->post('title_number',''),
            'page_no' => $request->post('page_no',1),
            'page_size' => $request->post('page_size',5),
            'type' => 2
        ];
        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->get_statistical($searchFilter);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //问答分页
    public function answer_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $searchFilter = [
            'questionnaire_id' => $request->post('questionnaire_id',''),
            'title_number' => $request->post('title_number'),
            'page_no' => $request->post('page_no',1),
            'page_size' => $request->post('page_size',5),
            'start_time' => $request->post('start_time',''),
            'end_time' => $request->post('end_time',''),
            'sortName' => $request->post('sort_name','id'),
            'sortOrder' => $request->post('sort_order','desc')
        ];
        $questionnaireModel = new Findings();
        $res = $questionnaireModel->getAnswerList($searchFilter);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //统计导出信息
    public function compare_to_excel($id,Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $questionnaireModel = new Questionnaire();
        $data['questionnaire_id'] = $id;
        $data['type'] = 1;
        $data['title_number'] = '';
        $data['page_no'] = '';
        $data['page_size'] = '';
        $data['start_time'] = $request->post('start_time','');
        $data['end_time'] = $request->post('start_time','');
        $res = $questionnaireModel->get_statistical($data);
        if ($res['total'] < 1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '无数据可下载';
            return $this->return_result($this->returnData);
        }
        $arr = [['问题','选择结果/回答','此选择人数','回答总人数','百分比']];
        foreach ($res['rows'] as $v){
            if (is_array($v['answer'])){
                $arr[] = [
                    $v['title'],
                ];
                foreach ($v['answer'] as $key=>$val){
                    if (isset($val['number'])){
                        $arr[] = [
                            '',
                            $val['res'],
                            $val['number'],
                            $res['total'],
                            (round($val['number']/$res['total'], 2)*100).'%'
                        ];
                    }else{
                        $arr[] = [
                            '',
                            $val
                        ];
                    }
                }
            }
        }

        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),"1xlsx");  //临时文件
            $a = Excel::create('问卷调查结果统计',function($excel) use ($arr){
                $excel->sheet('问卷调查结果统计', function($sheet) use ($arr){
                    $sheet->rows($arr);
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '问卷调查结果统计.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('问卷调查结果统计',function($excel) use ($arr){
                $excel->sheet('问卷调查结果统计', function($sheet) use ($arr){
                    $sheet->rows($arr);
                });
            })->export('xlsx');
        }
    }

    //上传excel表
    public function get_excel(Request $request){
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
                $this->returnData['msg'] = '上传文件失败';
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
        $list = [];
        foreach ($tmp_arr as $k=>$v){
            $arr['title'] = $v[0];
            $arr['is_fill'] = $v[1];
            $arr['type'] = $v[2];
            $len = count($v);
            $arr['answer'] = [];
            $j = 1;
            for($i = 3;$i< $len;$i++){
                if ($v[$i]){
                    $arr['answer'][$j]['res'] = $v[$i];
                    $arr['answer'][$j]['number'] = 0;
                    $j++;
                }
            }
            $list[] = $arr;
        }
        $this->returnData['data'] = $list;
        if (file_exists($file_path)){
            unlink($file_path);
        }
        return $this->return_result($this->returnData);
    }

    //导出excel模板
    public function excel_demo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){
            $fileName = 'questionnaire';
            $data['code'] = 3;
            $data['name'] = 'questionnaire.xlsx';
            $data['data'] = realpath(base_path('public/download')).'/'.$fileName.'.xlsx';
            return $data;
        }else{
            $fileName = 'questionnaire';
            if(is_file(realpath(base_path('public/download')).'/'.$fileName.'.xlsx')){
                return response()->download(realpath(base_path('public/download')).'/'.$fileName.'.xlsx',$fileName.'.xlsx');
            }
        }
        $this->returnData = ErrorCode::$admin_enum['not_exist'];
        $this->returnData['msg'] = '文件不存在';
        return $this->return_result($this->returnData);
    }




























































}
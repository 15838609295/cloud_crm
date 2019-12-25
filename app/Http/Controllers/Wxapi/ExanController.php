<?php
namespace App\Http\Controllers\Wxapi;

use App\Http\Requests\Request;
use App\Models\Admin\Exam;
use App\Models\Admin\ExamineeGroup;

class ExanController extends BaseController{

    public function __construct(){
        parent::__construct();
    }
    //考试列表
    public function examList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $pageNo = $params['pageNo'] ? $params['pageNo'] : 1;
        $pageSize = $params['pageSize'] ? $params['pageSize'] : 20;
        $start = ($pageNo -1)*$pageSize;
        //查询客户分组
        $type = 'member';
        $examineeGroupModel = new ExamineeGroup();
        $type_res = $examineeGroupModel->examineeGroupList($type);
        if (!$type_res){
            $this->result['data'] = ['rows'=>[],'total'=>0];
            return $this->return_result($this->result);
        }
        //查询每个分组下的人
        foreach ($type_res as &$v){
            $exam_papel = $examineeGroupModel->getGroupPeople($v['id']);
            if (!$exam_papel){
                $v['group_uids'] = [];
            }else{
                foreach ($exam_papel as $e_v){
                    $v['group_uids'][] = $e_v['u_id'];
                }
            }
        }
        $group_ids[] = 1;
        //查询用户所在组
        foreach ($type_res as &$v){
            if (in_array($this->user['id'],$v['group_uids'])){
                $group_ids[] = $v['id']; //获取用户所在组id
            }
        }
        if (count($group_ids)< 1){ //没有分组，没有考试
            $this->result['data'] = ['rows'=>[],'total'=>0];
            return $this->return_result($this->result);
        }
        //去查询进行中的考试
        $examModel = new Exam();
        $exam_res = $examModel->getConductExam();
        if (!$exam_res){  //没有在进行中的考试
            $this->result['data'] = ['rows'=>[],'total'=>0];
            return $this->return_result($this->result);
        }
        $data['rows'] = [];
        foreach ($group_ids as &$g_v){
            foreach ($exam_res as $e_v){
                $e_v['cover'] = $this->processingPictures($e_v['cover']);
                $exam_group_ids = json_decode($e_v['examinee_id'],1);
                if (in_array($g_v,$exam_group_ids)){
                    $data['rows'][] = $e_v;
                }
            }
        }
        $total = count($data['rows']);
        if ($total){
            $data['total'] = $total;
            $pagedata=array_slice($data['rows'],$start,$pageSize);
            $data['rows'] = $pagedata;
        }else{
            $data['rows'] = [];
            $data['total'] = 0;
        }
        $this->result['data'] = $data;
        unset($data);
        return $this->return_result($this->result);
    }

    //考试详情
    public function getExamInfo(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $examModel = new Exam();
        $user_id = $this->user['id'];
        $info_res = $examModel->getExamInfo($params['id'],$user_id);
        if (!$info_res){
            $this->result['status'] = 0;
            $this->result['msg'] = '考试已结束';
        }else{
            $this->result['data'] = $info_res;
        }
        return $this->return_result($this->result);
    }

    //获取试题
    public function getQuestionsList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $id = $params['id'] ? $params['id'] : '';
        $pageNo = $params['pageNo'] ? $params['pageNo']:1;
        $pageSize = $params['pageSize'] ? $params['pageSize'] :20;
        $start = ($pageNo -1)*$pageSize;
        $examModel = new Exam();
        $res = $examModel->getQuestionsList($id);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '考试不存在';
        }else{
            $data['taotal'] = $res['total'];
            $pagedata=array_slice($res['rows'],$start,$pageSize);
            if ($res['is_sort'] == 1){
                $data['list'] = array_values($this->randomArray($pagedata));
            }else{
                $data['list'] = array_values($pagedata);
            }
            $this->result['data'] = $data;
        }
        //创建答卷
        if ($pageNo -1 == 0){
            $card = $this->examStartResult($id);
            if (!$card){
                $this->result['status'] = 1;
                $this->result['msg'] = '创建题卡失败';
            }
        }
        return $this->return_result($this->result);
    }

    //考试前获取用户信息创建答卷
    public function examStartResult($examId){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $data['exam_id'] = $examId;
        $data['uid'] = $this->user['id'];
        $data['name'] = $this->user['name'];
        $data['type'] = 1; //1客户答卷 2员工答卷
        $examModel = new Exam();
        $res = $examModel->addStartExamResult($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }

    //考试结束提交
    public function examEndResult(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $data['answer'] = $params['answer'];
        $data['id'] = $params['id'];
        $data['uid'] = $this->user['id'];
        $examModel = new Exam();
        $res = $examModel->examSettlement($data);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '提交失败';
        }else{
            $this->result['data'] = $res;
        }
        return $this->return_result($this->result);
    }

    //提交前调用的预提交
    public function preSubmit(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $data['id'] = $params['id'];
        $data['uid'] = $this->user['id'];
        $data['answer'] = $params['answer'];
        $examModel = new Exam();
        $res = $examModel->examPreSubmit($data);
        $this->result['data'] = $res;
        return $this->return_result($this->result);
    }

    //考试排名
    public function rankingList(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $id = $params['id'];
        $examModel = new Exam();
        $res = $examModel->rankingList($id);
        $this->result['data'] = array_values($res);
        return $this->return_result($this->result);
    }

    //用户查看注解
    public function examExplain(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $data['id'] = $params['id'];
        $data['uid'] = $this->user['id'];
        $data['examNumber'] = $params['number'];
        $pageNo = $params['pageNo']?$params['pageNo'] : 1;
        $pageSize = $params['pageSize']?$params['pageSize'] : 20;
        $start = ($pageNo - 1)*$pageSize;
        $data['type'] = $params['type'];
        $examModel = new Exam();
        $res = $examModel->getExamExplain($data);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '考试不存在';
        }else{
            $res['list']=array_slice($res['list'],$start,$pageSize);
            $this->result['data'] = $res;
        }
        return $this->return_result($this->result);
    }

    //考试结果接口
    public function examResult(){
        if ($this->result['status'] > 0){
            return $this->return_result($this->result);
        }
        $params = request()->post();
        $data['id'] = $params['id'];
        $data['uid'] = $this->user['id'];
        $examModel = new Exam();
        $res = $examModel->examResult($data);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '结果不存在';
        }else{
            $this->result['data'] = $res;
        }
        return $this->return_result($this->result);
    }

    //打乱题目顺序
    private function randomArray($arr){
        if (!empty($arr)) {
            $key = array_keys($arr);
            shuffle($key);
            foreach ($key as $value) {
                $arr2[$value] = $arr[$value];
            }
            $arr = $arr2;
        }
        return $arr;
    }

}






?>
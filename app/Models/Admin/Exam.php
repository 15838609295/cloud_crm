<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use foo\bar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exam extends Model
{
    protected $table='exam';

    //考试列表有分页
    public function examList($data){
        $res = DB::table($this->table.' as e')
            ->leftJoin('exam_type as et','e.type_id','=','et.id')
            ->select('e.id','e.name','e.examinee_id','e.total_score','e.cover','e.limit_time','e.updated_at','e.start_time','e.end_time','e.subject_number','et.name as type_name');
        $now_time = Carbon::now()->toDateTimeString();
        if ($data['status'] != '' && $data['status'] == 1){ //未开始
            $res->where('e.start_time','>',$now_time);
        }elseif ($data['status'] != '' && $data['status'] == 2){  //考试中
            $res->where('e.start_time','<',$now_time)->where('e.end_time','>',$now_time);
        }elseif ($data['status'] != '' && $data['status'] == 3) {  //已考完
            $res->where('e.end_time','<',$now_time);
        }

        if ($data['id'] != ''){
            $res->where('e.id',$data['id']);
        }

        if ($data['type_id'] != ''){
            $res->where('e.type_id',$data['type_id']);
        }
        if ($data['start_time'] != '' && $data['end_time'] != ''){
            $res->whereBetween('e.created_at',[$data['start_time'],$data['end_time']]);
        }elseif ($data['start_time'] != '' && $data['end_time'] == ''){
            $res->where('e.created_at','>',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] != ''){
            $res->where('e.created_at','<',$data['end_time']);
        }
        if($data['search'] != ''){
            $searchKey = $data['search'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('e.name', 'LIKE', '%' . $searchKey . '%');
            });
        }

        $result['total'] = $res->count();
        $result['rows'] = $res->skip($data['start'])->take($data['page_size'])->orderBy('e.'.$data['sort_name'], $data['sort_order'])->get();
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        return $result;
    }

    //考试列表无分页
    public function itenListNoPage(){
        $res = DB::table($this->table)->select('id','name')->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //根据条件获取字段
    public function getFields($field, $filter, $one = true){
        if (!$filter){
            return false;
        }
        $db = DB::table($this->table)->where($filter)->select($field);
        if ($one){
            $data = $db->first();
        }else{
            $data = $db->get();
        }
        if (!$data){
            return [];
        }else{
            $data = json_decode(json_encode($data),true);
        }
        return $data;
    }

    //获取某字段的值
    public function getValue($field, $filter){
        $data = DB::table($this->table)->where($filter)->value($field);
        if (!$data){
            return false;
        }
        return $data;
    }

    //创建考试
    public function addExamData($data,$test_paper_id,$amdin_name){
        $testPaperModel = new TestPaper();
        $item_data = $testPaperModel->getFields(['name','item_bank_list','type_id'],['id'=>$test_paper_id]);
        if (!$item_data){
            return -1;
        }
        $ids = json_decode($item_data['item_bank_list'],true);
        $item_list = DB::table('item_bank')->whereIn('id',$ids)->select('id','name','annex','type','must','option','answer','fraction','remarks')->get();
        $item_list = json_decode(json_encode($item_list),true);
        $total_score = 0;
        $subject_number = count($item_list);
        foreach ($item_list as $v){
            if ($v['type'] != 3){
                $v['option'] = json_decode($v['option'],true);
                if (!is_array($v['option']) || count($v['option']) <= 0){
                    return -2;
                }
                if ($v['fraction'] <= 0){
                    return -3;
                }
                if (strlen($v['answer']) <= 0){
                    return -4;
                }
            }
            $total_score += $v['fraction'];
        }
        if ($data['examineeId'] == 0){  //全部分组
            $examineeGrpouModel = new ExamineeGroup();
            $examinee_id = $examineeGrpouModel->getAllGrpouIds();
        }else{
            $examinee_id = json_encode(explode(',',$data['examineeId']));
        }
        $exam_data['name'] = $data['name'];
        $exam_data['start_time'] = $data['startTime'];
        $exam_data['end_time'] = $data['endTime'];
        $exam_data['qualified_score'] = $data['qualifiedScore'];
        $exam_data['total_score'] = $total_score;
        $exam_data['cover'] = $data['cover'];
        $exam_data['explain'] = $data['explain'];
        $exam_data['limit_time'] = $data['limitTime'];
        $exam_data['number'] = $data['number'];
        $exam_data['examinee_id'] = $examinee_id;
        $exam_data['is_ranking'] = $data['isRanking'];
        $exam_data['is_copy'] = $data['isCopy'];
        $exam_data['is_sort'] = $data['isSort'];
        $exam_data['notice'] = $data['notice'];
        $exam_data['subject_list'] = json_encode($item_list);
        $exam_data['type_id'] = $item_data['type_id'];
        $exam_data['found'] = $amdin_name;
        $exam_data['created_at'] = Carbon::now()->toDateTimeString();
        $exam_data['subject_number'] = $subject_number;
        $exam_data['test_paper_name'] = $item_data['name'];
        $res = DB::table($this->table)->insert($exam_data);
        if (!$res){
            return false;
        }
        return true;
    }

    //考试详情
    public function examInfoId($id){
        $res = DB::table($this->table.' as e')
            ->leftJoin('exam_type as et','e.type_id','=','et.id')
            ->select('e.*','et.name as type_name')
            ->where('e.id',$id)
            ->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $res['examinee_group_name'] = '';
        $examinee_id = json_decode($res['examinee_id'],1);
        $examineeGroupModel = new ExamineeGroup();
        foreach ($examinee_id as $v){
            $name = $examineeGroupModel->getGroupName($v);
            $res['examinee_group_name'] .= $name.'/';
        }
        $res['group_name'] = substr($res['examinee_group_name'],0,strlen($res['examinee_group_name'])-1);
        $now_time = Carbon::now()->toDateTimeString();
        if ($res['start_time'] > $now_time){
            $res['status_txt'] = '未开始';
            $res['status'] = 1;
        }elseif ($res['start_time'] < $now_time && $res['end_time'] > $now_time){
            $res['status_txt'] = '进行中';
            $res['status'] = 2;
        }elseif ($res['end_time'] < $now_time){
            $res['status_txt'] = '已结束';
            $res['status'] = 3;
        }
        return $res;
    }

    //考试结果统计分析
    public function examResultAnalyse($id){
        $exam_info = [];
        //总分和及格分
        $total_number = DB::table($this->table)->select('total_score','qualified_score','number as exam_number')->where('id',$id)->first();
        if (!$total_number){
            return false;
        }
        $total_number = json_decode(json_encode($total_number),true);
        //考试次数
        $data['frequency'] = DB::table('exam_results')->where('exam_id',$id)->count();
        if (!$data['frequency']){
            return -1;
        }
        //最高分
        $data['max'] = DB::table('exam_results')->where('exam_id',$id)->max('branch');
        //最低分
        $data['min'] = DB::table('exam_results')->where('exam_id',$id)->min('branch');
        //平均分
        $data['avg'] = DB::table('exam_results')->where('exam_id',$id)->avg('branch');
        //及格人数
        $data['pass'] = DB::table('exam_results')->where('exam_id',$id)->where('branch','>=',$total_number['qualified_score'])->count();
        //及格率
        $data['pass_rate'] = ((sprintf("%.2f",$data['pass']/$data['frequency']))*100).'%';
        $data['avg'] = sprintf("%.2f",$data['avg']);
        $data['total_score'] = $total_number['total_score'];
        $data['qualified_score'] = $total_number['qualified_score'];
        $data['exam_number'] = $total_number['exam_number'];
        return $data;
    }

    //考生答卷列表
    public function examResultList($id,$data){
        $res = DB::table('exam_results')->where('exam_id',$id)->select('id','uid','name','branch','number','status','start_time','end_time','type');
        if ($data['status'] != ''){
            if ($data['status'] == 1){
                $res->where('status',$data['status']);
            }elseif ($data['status'] == 2){
                $res->where('status',0);
            }
        }
        if ($data['search'] != ''){
            $res->where('name','LIKE', '%' . $data['search'] . '%');
        }
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($data['start'])->take($data['pageSize'])->orderBy($data['sortName'], $data['sortOrder'])->get();
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        return $result;
    }

    //根据做答卷id处理做答卷详情
    public function answerDetails($examResultsId){
        $res = DB::table('exam_results')->where('id',$examResultsId)->first();
        if (!$res){
            return $res;
        }
        $res = json_decode(json_encode($res),true);
        $answer = json_decode($res['answer'],true);
        $subject_list = $this->getValue('subject_list',['id'=>$res['exam_id']]);
        $subject_list = json_decode($subject_list,true);
        foreach ($subject_list as &$v){
            if ($v['type'] != 3){
                $v['option'] = json_decode($v['option'],1);
                $v['correct_answer'] = implode(',',json_decode($v['answer'],1));
                $v['answer'] = json_decode($v['answer'],1);
            }else{
                $v['correct_answer'] = $v['answer'];
            }
            if (!$answer){
                $v['status'] = 0;
                if ($v['type'] != 3){
                    $v['user_answer'] = [];
                }else{
                    $v['user_answer'] = '';
                }

            }else{
                foreach ($answer as &$a_v){
                    if ($v['id'] == (int)$a_v['key']){
                        if  ($v['correct_answer'] == $a_v['answer']) {  //正确
                            $v['status'] = 1;
                            $v['user_answer'] = $v['answer'];
                        }else{                                            //错误
                            $v['status'] = 0;
                            if ($v['type'] != 3){
                                $a_v['answer'] = explode(',',$a_v['answer']);
                                foreach ($a_v['answer'] as &$a_v_a){
                                    $a_v_a = (int)$a_v_a;
                                }
                            }
                            $v['user_answer'] = $a_v['answer'];
                        }
                    }
                }
                if (!isset($v['user_answer'])){               //没有回答
                    $v['status'] = 0;
                    if ($v['type'] != 3){
                        $v['user_answer'] = [];
                    }else{
                        $v['user_answer'] = '';
                    }
                }
            }
        }
        return $subject_list;
    }

    //考生列表
    public function getExamineersList($data,$type){
        //查询分组 分组id存在 查询单组的成员
        if ($data['typeId'] != ''){
            $group_user_ids = DB::table('examinee_group_role')->where('examinee_group_id',$data['typeId'])->get();
            if (!$group_user_ids){
                return [];
            }
            $group_user_ids = json_decode(json_encode($group_user_ids),1);
            $users_ids = [];
            foreach ($group_user_ids as $v){
                $users_ids[] = $v['u_id'];
            }
            if ($type == 'admin'){
                $res = DB::table('admin_users')->select('id','name','email','mobile','position','sex')->whereIn('id',$users_ids);

            }else{
                $res = DB::table('member as m')
                    ->select('m.id','m.name','m.mobile','m.email','m.is_vip','me.company','me.wechat')
                    ->leftJoin('member_extend as me','m.id','=','me.member_id')
                    ->whereIn('m.id',$users_ids);
            }
            if ($data['search'] != ''){
                $res->where('name','LIKE', '%' . $data['search'] . '%');
            }
            $list['total'] = $res->count();
            $examinee_list = $res->skip($data['start'])->take($data['pageSize'])->orderBy('id','desc')->get();
            $examinee_list = json_decode(json_encode($examinee_list),true);
            $list['rows'] = $examinee_list;
            $examineeGroupModel = new ExamineeGroup();
            $name = $examineeGroupModel->getGroupName($data['typeId']);
            foreach ($list['rows'] as &$v){
                $v['group_name'] = $name;
            }
            //返回单组考生
            return $list;
        }else{
            //查询全部考生
            if ($type == 'admin') {  //员工
                $res = DB::table('admin_users')->select('id','name','email','mobile','position','sex');
            }else {           //客户
                $res = DB::table('member as m')
                    ->select('m.id','m.name','m.mobile','m.email','m.is_vip','me.company','me.wechat')
                    ->leftJoin('member_extend as me','m.id','=','me.member_id');
            }
            if ($data['search'] != ''){
                $res->where('name','LIKE', '%' . $data['search'] . '%');
            }
            $total = $res->count();
            $examinee_list = $res->skip($data['start'])->take($data['pageSize'])->orderBy('id','desc')->get();
            $examinee_list = json_decode(json_encode($examinee_list),1);
            //查询分组
            $type_res = DB::table('examinee_group');
            if ($data['typeId'] != ''){
                $examinee_type = $type_res->where('id',$data['typeId'])->first();
            }else{
                if ($type == 'admin') { //员工
                    $examinee_type = $type_res->where('group_type', 1)->get();
                }else{           //客户
                    $examinee_type = $type_res->where('group_type',0)->get();
                }
            }
            $examinee_type = json_decode(json_encode($examinee_type),1);
            foreach ($examinee_type as &$v){
                $group_user_ids = DB::table('examinee_group_role')->where('examinee_group_id',$v['id'])->get();
                $group_user_ids = json_decode(json_encode($group_user_ids),1);
                if (!$group_user_ids){
                    $v['group_user_ids'] = [];
                }else{
                    $group_user_ids = json_decode(json_encode($group_user_ids),1);
                    $group_ids = [];
                    foreach ($group_user_ids as $g_v){
                        $group_ids[] = $g_v['u_id'];
                    }
                    $v['group_user_ids'] = $group_ids;
                }
            }
            foreach ($examinee_list as &$e_ids){
                $e_ids['group_name'] = '';
                foreach ($examinee_type as $e_type){
                    if (in_array($e_ids['id'],$e_type['group_user_ids'])){
                        $e_ids['group_name'] .= $e_type['name'].'/';
                    }
                }
                if ($e_ids['group_name'] == ''){
                    $e_ids['group_name'] = '-';
                }else{
                    $e_ids['group_name'] = substr($e_ids['group_name'],0,strlen($e_ids['group_name'])-1);
                }
            }
            //反回全部考生
            $result['total'] = $total;
            $result['rows'] = $examinee_list;
            return $result;
        }
    }

    //考生分组
    public function Grouping($examineerIds,$groupIds){
        foreach ($groupIds as $gid){
            foreach ($examineerIds as $e_id){
                $data['u_id'] = $e_id;
                $data['examinee_group_id'] = $gid;
                $res = DB::table('examinee_group_role')->insert($data);
                if (!$res){
                    return false;
                }
            }
        }
        return true;
    }

    //批量删除考生
    public function batchDelExaminee($data){
        foreach ($data['ids'] as $v){
            $where['examinee_group_id'] = $data['group_id'];
            $where['u_id'] = $v;
            $res = DB::table('examinee_group_role')->where($where)->delete();
            if (!$res){
                return false;
            }
        }
        return true;
    }

    //获取进行中的考试列表
    public function getConductExam(){
        $res = DB::table($this->table)
            ->select('id','cover','name','start_time','end_time','examinee_id')
            ->where('end_time','>',Carbon::now()->toDateTimeString())
            ->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),1);
        foreach ($res as &$v){
            if ($v['start_time'] > Carbon::now()->toDateTimeString() && $v['end_time'] > Carbon::now()->toDateTimeString()){
                $v['status'] = 1;
                $v['status_txt'] = '未开始';
            }elseif ($v['start_time'] < Carbon::now()->toDateTimeString() && $v['end_time'] > Carbon::now()->toDateTimeString()){
                $v['status'] = 2;
                $v['status_txt'] = '进行中';
            }elseif ($v['start_time'] < Carbon::now()->toDateTimeString() && $v['end_time'] < Carbon::now()->toDateTimeString()){
                $v['status'] = 3;
                $v['status_txt'] = '已结束';
            }
        }
        return $res;
    }

    //获取试卷信息
    public function getExamInfo($id,$user_id){
        //判断用户是否考过这场考试
        $user_result = DB::table('exam_results')->where('uid',$user_id)->where('exam_id',$id)->first();
        $res = DB::table($this->table)
            ->select('id','name','cover','limit_time','number','qualified_score','is_copy','end_time')
            ->where('id',$id)
            ->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if ($res['end_time'] < date('Y-m-d H:i:s')){
            return false;
        }
        if ($user_result){
            $user_result = json_decode(json_encode($user_result),true);
            $user_number = $user_result['number'];
            $exam_number = $res['number'];
            $res['exam_number'] = $exam_number - $user_number;
        }else{ //用户没有考过
            $res['exam_number'] = $res['number'];
        }
        return $res;
    }

    //获取考试题目
    public function getQuestionsList($id){
        $res = DB::table($this->table)->where('id',$id)->select('is_sort','subject_list')->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),1);
        $subject_list = json_decode($res['subject_list'],1);
        foreach ($subject_list as &$v){
            $v['option'] = json_decode($v['option'],1);
        }
        $data['total'] = count($subject_list);
        $data['rows'] = $subject_list;
        $data['is_sort'] = $res['is_sort'];
        return $data;
    }

    //考前创建答卷
    public function addStartExamResult($data){
        //查询用户是否已考过
        $id = DB::table('exam_results')->where('uid',$data['uid'])->where('exam_id',$data['exam_id'])->value('id');
        if (!$id){ //没有考试过
            $data['start_time'] = Carbon::now()->toDateTimeString();
            $data['created_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table('exam_results')->insert($data);
            if (!$res){
                return false;
            }
            return true;
        }else{
            //已经考试过，修改开始考试时间
            $where['start_time'] = Carbon::now()->toDateTimeString();
            $res = DB::table('exam_results')->where('id',$id)->update($where);
            if (!$res){
                return false;
            }
            return true;
        }
    }

    //用户提交答案计算结果
    public function examSettlement($data){
        //处理用户提交的答案
        $exam_result['end_time'] = Carbon::now()->toDateTimeString();
        $answer = json_decode($data['answer'],1);
        //获取题目计算得分
        $exam_res = $this->getFields(['subject_list','qualified_score','number','total_score','is_ranking'],['id'=>$data['id']]);
        $subject_list = json_decode($exam_res['subject_list'],1);
        $fraction = 0;                            //本次分数
        foreach ($subject_list as $k=>$v){
            if ($v['type'] != 3){
                $v['answer'] = implode(',',json_decode($v['answer'],1));
            }
            foreach ($answer as $a_v){
                if ($a_v['key'] == $v['id']){ //计算相同的题
                    //处理用户提交的答案
                    $a_v_answer = preg_replace('# #','',$a_v['answer']);
                    if ($v['answer'] == $a_v_answer){
                        $fraction = $fraction + $v['fraction'];
                    }
                }
            }
        }
        if ($fraction > $exam_res['qualified_score']){
            $exam_result['status'] = 1;    //及格
        }else{
            $exam_result['status'] = 0;   //不及格
        }
        //找到用户的答卷
        $user_res = DB::table('exam_results')->where('exam_id',$data['id'])->where('uid',$data['uid'])->first();
        $user_res = json_decode(json_encode($user_res),1);
        //计算用时
        $end_time = strtotime($exam_result['end_time']);
        $start_time = strtotime($user_res['start_time']);
        $use_time = $end_time -$start_time;
        $use_time = date('i.s',$use_time);

        $exam_result['use_time'] = $use_time;                 //考试用时
        $exam_result['start_time'] = $user_res['start_time'];//考试开始时间
        $exam_result['lately_results'] = $fraction;           //最新考试分数
        $exam_result['answer'] = $data['answer'];             //用户答案
        if ($user_res['number'] == 0){
            //考试次数  没考过
            $exam_result['number'] = 1;
            $exam_result['branch'] = $fraction;
            $result = DB::table('exam_results')->where('id',$user_res['id'])->update($exam_result);
            if (!$result){
                return false;
            }
            $exam_result['highest'] = $fraction;
        }else{
            $exam_result['number'] = $user_res['number']+1;
            //用户考过
            if ($fraction > $user_res['branch']){  //本次考试分数大于上次考试分数 则修改分数
                $exam_result['branch'] = $fraction;
                $result = DB::table('exam_results')->where('id',$user_res['id'])->update($exam_result);
                if (!$result){
                    return false;
                }
                $exam_result['highest'] = $exam_result['branch'];
            }else{                                  //本次考试小于记录分数 显示记录分数
                $update['number'] = $user_res['number']+1;
                $update['lately_results'] = $fraction;
                DB::table('exam_results')->where('id',$user_res['id'])->update($update);
                $exam_result['highest'] = $user_res['branch'];
            }
        }
        unset($exam_result['answer']);
        $exam_result['total_branch'] = $exam_res['total_score'];
        //是否需要获取排名信息
        if ($exam_res['is_ranking'] == 1){
            $users_result = DB::table('exam_results')->where('exam_id',$data['id'])->select('id','uid','name','branch','use_time')->orderBy('branch','desc')->get();
            $users_result = json_decode(json_encode($users_result),1);
            $exam_result['total_number'] = count($users_result);
            //根据分数分组
            $ranking_number = [];
            foreach ($users_result as $k=>$v){
                $ranking_number[$v['branch']][] = $v;
            }
            //根据分数
            $ranking = [];
            foreach ($ranking_number as $v){
                if (count($v) > 1){
                    //如果分数相同 根据所用时间来排序
                    $list = array_column($v,'use_time');
                    array_multisort($list,SORT_ASC,$v);
                    foreach ($v as $vv){
                        $ranking[] = $vv;
                    }
                }else{
                    $ranking[] = $v[0];
                }
            }
            foreach ($ranking as $k=>$v){
                if ($v['uid'] == $data['uid']){
                    $exam_result['ranking_number'] = $k+1;
                }
            }
        }else{
            //不显示排名
            $exam_result['ranking_number'] = 0;
            $exam_result['total_number'] = 0;
        }
        //剩余考试次数
        $exam_result['again'] = $exam_res['number'] - $exam_result['number'];
        $new_time = explode('.',$exam_result['use_time']);
        if (isset($new_time[0]) && isset($new_time[1])){
            $exam_result['use_time'] = $new_time[0].'分'.$new_time[1].'秒';
        }else{
            $exam_result['use_time'] = $new_time[0].'分0秒';
        }
        return $exam_result;
    }

    //用户预提交判断
    public function examPreSubmit($data){
        $time = time();
        //获取全部的题
        $problem_list = DB::table($this->table)->where('id',$data['id'])->select('subject_number','limit_time')->first();
        $problem_list = json_decode(json_encode($problem_list),1);
        $answer = json_decode($data['answer'],1);
        $count = count($answer);
        //判断是否全部填写
        if ($count < $problem_list['subject_number']){
            $res['number'] = $problem_list['subject_number'] - $count;
        }else{
            $res['number'] = 0;
        }
        //计算剩余时间
        $start_time = DB::table('exam_results')->where('uid',$data['uid'])->where('exam_id',$data['id'])->value('start_time');
        $start_time = strtotime($start_time);
        $end_time = $start_time + ($problem_list['limit_time'] * 60);
        $surplus_time = $end_time - $time;
        if ($surplus_time > 0){
            $res['surplus_time'] = date('i分s秒',$surplus_time);
        }
        return $res;
    }

    //停止考试
    public function stopTheExam($id){
        $where['end_time'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //考试排名
    public function rankingList($id){
        $users_result = DB::table('exam_results as er')
            ->select('er.id','er.uid','er.name','er.branch','er.use_time','me.avatar')
            ->leftJoin('member_extend as me','er.uid','=','me.member_id')
            ->where('er.exam_id',$id)
            ->orderBy('branch','desc')->get();
        $users_result = json_decode(json_encode($users_result),1);
        $ranking_number = [];
        foreach ($users_result as $k=>$v){
            $ranking_number[$v['branch']][] = $v;
        }
        $data = [];
        foreach ($ranking_number as $v){
            if (count($v) > 1){
                //如果分数相同 根据所用时间来排序
                $list = array_column($v,'use_time');
                array_multisort($list,SORT_ASC,$v);
                foreach ($v as $vv){
                    $data[] = $vv;
                }
            }else{
               $data[] = $v[0];
            }
        }
        foreach ($data as &$v){
            $new_time = explode('.',$v['use_time']);
            if (isset($new_time[0]) && isset($new_time[1])){
                $v['use_time'] = $new_time[0].'分'.$new_time[1].'秒';
            }else{
                $v['use_time'] = $new_time[0].'分0秒';
            }
        }
        return $data;
    }

    //获取题目注解
    public function getExamExplain($data){
        //获取试卷的考题和用户的答卷
        $user_result = DB::table('exam_results')->where('exam_id',$data['id'])->where('uid',$data['uid'])->select('answer','number')->first();
        $user_result = json_decode(json_encode($user_result),1);
        $answer = json_decode($user_result['answer'],1);
        $subject_list = DB::table($this->table)->where('id',$data['id'])->select('subject_list','number')->first();
        if (!$subject_list){
            return false;
        }
        $subject_list = json_decode(json_encode($subject_list),1);
        $exam_subject_list = json_decode($subject_list['subject_list'],1);
        //查看试卷解析，修改考试次数
        $update_data['number'] = $subject_list['number'];
        if ($user_result['number'] != $subject_list['number']){
            DB::table('exam_results')->where('exam_id',$data['id'])->where('uid',$data['uid'])->update($update_data);
        }

        $result['yes'] = 0;
        $result['no'] = 0;
        $result['not_filled'] = 0;
        $result['list'] = [];
        $subject_list_ids = [];
        $user_subject_ids = [];
        foreach ($exam_subject_list as &$v){
            $subject_list_ids[] = $v['id'];
            $v['option'] = json_decode($v['option'],1);
            if ($v['type'] != 3){
                $v['answer'] = implode(',',json_decode($v['answer'],1));
            }
            if (!$answer){  //没有答案
                if ($data['type'] == 'not_filled'){
                    $v['answer'] = explode(',',$v['answer']);
                    foreach ($v['answer'] as &$v_a){
                        $a_v_a = (int)$v_a;
                    }
                    $result['list'][] = $v;
                }
            }else{  //有答案
                foreach ($answer as &$a_v){
                    $user_subject_ids[] = (int)$a_v['key'];
                    if ($v['id'] == $a_v['key']){
                        if ($v['answer'] == $a_v['answer']){
                            $result['yes'] += 1;
                            if ($data['type'] == 'yes'){
                                if ($v['type'] != 3){
                                    $yes_answer = explode(',',$v['answer']);
                                    foreach ($yes_answer as &$y_a){
                                        $y_a = (int)$y_a;
                                    }
                                    $v['answer'] = $yes_answer;
                                    $v['user_answer'] = $yes_answer;
                                }else{
                                    $v['user_answer'] = $a_v['answer'];
                                }
                                $result['list'][] = $v;
                            }
                        }elseif ($v['answer'] != $a_v['answer']){
                            $result['no'] += 1;
                            if ($data['type'] == 'no'){
                                if ($v['type'] != 3){
                                    $yes_answer = explode(',',$v['answer']);
                                    foreach ($yes_answer as &$y_a){
                                        $y_a = (int)$y_a;
                                    }
                                    $v['answer'] = $yes_answer;
                                    $v['user_answer'] = $a_v['answer'];
                                }else{
                                    $v['user_answer'] = $a_v['answer'];
                                }
                                $result['list'][] = $v;
                            }
                        }
                    }
                }
            }
        }
        //统计未答的题
        $user_subject_ids = array_unique($user_subject_ids);
        $not_filled =  array_diff($subject_list_ids,$user_subject_ids);
        $result['not_filled'] = count($not_filled);
        if ($data['type'] == 'not_filled'){
            foreach ($exam_subject_list as &$v){
                if (in_array($v['id'],$not_filled)){
//                    if ($v['type'] != 3 && $v['answer']){
//                        $v['option'] = json_decode($v['option'],1);
//                    }
                    $v['user_answer'] = '';
                    $result['list'][] = $v;
                }
            }
        }
        return $result;
    }

    //考试统计
    public function examCompute($time_type){
        //考试已结束
        $data['exam_end_number'] = DB::table($this->table)->where('end_time','<',Carbon::now()->toDateTimeString())->count();
        //试卷总数
        $data['test_paper_number'] = DB::table('test_paper')->count();
        //新增考生
        $data['new_member'] = DB::table('member')->where('create_time','>',date('Y-m-d 00:00:00',time()))->count();
        //近7天
        $seven_start = date("Y-m-d 00:00:00", strtotime("-7 day"));
        $seven_end = date("Y-m-d 23:59:59", time());
       //近15天
        $fifteen_start = date("Y-m-d 00:00:00", strtotime("-15 day"));
        $fifteen_end = date("Y-m-d 23:59:59", time());
        //近30天
        $thirty_start = date("Y-m-d 00:00:00", strtotime("-30 day"));
        $thirty_end = date("Y-m-d 23:59:59", time());
        $list[0]['title'] = '7日考试数据';
        $seven_res = $this->statistics($seven_start,$seven_end);
        $list[0]['data'] = $seven_res['data'];
        $list[0]['list'] = $seven_res['list'];
        $list[1]['title'] = '15日考试数据';
        $fifteen_res = $this->statistics($fifteen_start,$fifteen_end);
        $list[1]['data'] = $fifteen_res['data'];
        $list[1]['list'] = $fifteen_res['list'];
        $list[2]['title'] = '30日考试数据';
        $thirty_res = $this->statistics($thirty_start,$thirty_end);
        $list[2]['data'] = $thirty_res['data'];
        $list[2]['list'] = $thirty_res['list'];
        $data['chartData'] = $list;
        return $data;
    }

    //考试统计
    public function statistics($start_time,$end_time){
        //查询考试 统计数据
        $exam_res = DB::table('exam')->whereBetween('end_time',[$start_time,$end_time])->get();
        if (!$exam_res){
            $data = [
                ['value'=>0,'name' => '及格率'],
                ['value'=>0,'name' => '不及格率'],
            ];
            $list['exam_number'] = 0;
            $list['exam_result_number'] = 0;
            $list['average_time'] = 0;
            $list['pass_rate'] = 0;
            $list['pass_number'] = 0;
            $list['fail'] = 0;
            $res = ['data'=>$data,'list'=>$list];
            return $res;
        }
        $exam_res = json_decode(json_encode($exam_res),true);
        //试卷id集合
        $exam_ids = [];
        foreach ($exam_res as $v){
            array_push($exam_ids,$v['id']);
        }
        //考试数量
        $exam_number = count($exam_res);
        //考试次数
        $exam_result = DB::table('exam_results')->whereIn('exam_id',$exam_ids)->whereBetween('created_at',[$start_time,$end_time])->get();
        $exam_result = json_decode(json_encode($exam_result),true);
        $exam_result_number = count($exam_result);
        //平均考试时间
        if ($exam_result_number){  //有考生答題
            $time = 0;
            foreach ($exam_result as $v){
                $time += $v['use_time'];
            }

            $average_time =  sprintf("%.2f",$time/$exam_result_number);
            $new_time = explode('.',$average_time);
            $use_time = '';
            if ($new_time[0] && $new_time[1]){
                $use_time = $new_time[0].'分'.$new_time[1].'秒';
            }else{
                $use_time = $new_time[0].'分0秒';
            }
            //$use_time = $new_time[0].'分'.$new_time[1].'秒';
        }else{
            $use_time = '未有考生答题';
        }

        //获取及格人数 不及格人数 及格率
        $pass_number = 0;
        $fail = 0;
        foreach ($exam_result as $v){
            if ($v['status'] == 0){ //不及格
                $fail +=1;
            }else{
                $pass_number +=1;
            }
        }
        //及格率
        if ($pass_number != 0 && $fail != 0){
            $pass_rate = ((sprintf("%.2f",$pass_number/$pass_number+$fail))*100).'%';
        }else{
            $pass_rate = '0%';
        }
        $data = [
            ['value'=>$pass_number,'name' => '及格率'],
            ['value'=>$fail,'name' => '不及格率'],
        ];
        $list['exam_number'] = $exam_number;
        $list['exam_result_number'] = $exam_result_number;
        $list['average_time'] = $use_time;
        $list['pass_rate'] = $pass_rate;
        $list['pass_number'] = $pass_number;
        $list['fail'] = $fail;
        $res = ['data'=>$data,'list'=>$list];
        return $res;
    }

    //试题分析
    public function examAnalyse($data){
        //获取考试题目信息
        $exam_res = DB::table($this->table)->where('id',$data['id'])->select('subject_list')->first();
        $exam_res = json_decode(json_encode($exam_res),1);
        $subject_list = json_decode($exam_res['subject_list'],1);
        foreach ($subject_list as &$v){
            if ($v['type'] != 3){
                $v['option'] = json_decode($v['option'],true);
                if ($data['type'] == 1){  //列表
                    if (is_array($v['option'])){
                        foreach ($v['option'] as $k=>$v_o_v){
                            $v['option'][$k]['number'] = 0;
                        }
                    }else{
                        return -2;
                        $v['option'][0]['label'] = 'A';
                        $v['option'][0]['value'] = '';
                        $v['option'][0]['number'] = 0;
                    }

                }else if ($data['type'] == 2){ //饼状图
                    $exam_data = [];
                    if(is_array($v['option'])){
                        foreach ($v['option'] as $v_o_v){
                            $exam_data[] = [
                                'value' => 0,
                                'name' => $v_o_v['value'],
                            ];
                        }
                    }else{
                        $exam_data[] = [
                            'value' => 0,
                            'name' => '',
                        ];
                    }

                    $v['chartData'] = [
                        'title' => $v['name'],
                        'data' => $exam_data
                    ];
                }
            }else if($v['type'] == 3){
                $v['option'] = [];
                $v['option'][0]['value'] = '';
                $v['option'][0]['number'] = 0;
                if ($data['type'] == 1){  //列表
                    if (!$v['option']){
                        $v['option']['value'] = '';
                        $v['option']['number'] = 0;
                    }else{
                        foreach ($v['option'] as &$v_o_n){
                            $v_o_n['number'] = 0;
                        }
                    }
                }
            }
        }
        //获取用户答卷信息
        $user_res = DB::table('exam_results')->where('exam_id',$data['id'])->select('answer')->get();
        $user_res = json_decode(json_encode($user_res),1);
        $total_result = count($user_res);
        //题目
        foreach ($subject_list as $k=>&$s_v){
            //统计每个用户答案
            foreach ($user_res as $u_v){
                $each_user_res = json_decode($u_v['answer'],1);
                if (!$each_user_res){
                    continue;
                }
                //计算单个用户的答案
                foreach ($each_user_res as $e_u_r_v){
                    if ($s_v['id'] == $e_u_r_v['key']){
                        $user_this_res = explode(',',$e_u_r_v['answer']);
                        if ($s_v['type'] != 3){  //单 多选 加
                            if ($data['type'] == 1){   //列表数据
                                foreach ($s_v['option'] as $k=>&$s_o_v){
                                    foreach ($user_this_res as $u_t_r){
                                        if ($k == (int)$u_t_r){
                                            $s_o_v['number'] = $s_o_v['number'] + 1;
                                        }
                                    }
                                }
                            }else if($data['type'] == 2){   //饼状图计算
                                foreach ($user_this_res as $u_t_r){
                                    $s_v['chartData']['data'][$u_t_r]['value'] = $s_v['chartData']['data'][$u_t_r]['value'] +1;
                                }
                            }
                        }else{  //填空题处理  $user_this_res[0] 用户的值
                            $t_res = $this->deep_in_array($user_this_res[0],$s_v['option']);
                            if ($t_res){   //存在
                                foreach ($s_v['option'] as &$s_v_o){
                                    if ($s_v_o['value'] == $user_this_res[0]){
                                        $s_v_o['number'] = $s_v_o['number'] +1;
                                        continue;
                                    }
                                }
                            }else{  //不存在
                                if ($s_v['option'][0]['value'] == ''){
                                    $s_v['option'][0]['value'] = $user_this_res[0];
                                    $s_v['option'][0]['number'] = 1;
                                }else{
                                    $arr_res = ['label' => '','value' => $user_this_res[0],'number' => 1];
                                    array_push($s_v['option'],$arr_res);
                                }
                            }

                        }
                    }
                }
            }
        }
        if ($data['type'] == 1){  //计算选择率
            foreach ($subject_list as &$s_v){
                if ($s_v['type'] != 3){
                    foreach ($s_v['option'] as &$s_v_o){
                        if ($total_result != 0){
                            $s_v_o['choice'] = sprintf('%.2f',($s_v_o['number']/$total_result)*100).'%';
                        }else{
                            $s_v_o['choice'] = '0%';
                        }
                    }
                }
            }
        }
        $resulr['total'] = count($subject_list);
        $resulr['rows'] = array_values($subject_list);
        return $resulr;
    }


    public function deep_in_array($value, $array) {
        foreach($array as $item) {
            if(!is_array($item)) {
                if ($item === $value) {
                    return true;
                } else {
                    continue;
                }
            }

            if(in_array($value, $item)) {
                return true;
            } else if($this->deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }

    //删除考试
    public function examDel($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        //删除用户答卷
        $count = DB::table('exam_results')->where('exam_id',$id)->count();
        if ($count){
            DB::table('exam_results')->where('exam_id',$id)->delete();
        }
        return true;
    }

    //考试结果
    public function examResult($data){
        $where[] = ['exam_id','=',$data['id']];
        $where[] = ['uid','=',$data['uid']];
        $res = DB::table('exam_results')->where($where)->select('branch','use_time','number','start_time','end_time','status','lately_results')->first();
        $res = json_decode(json_encode($res),true);
        if (!$res){
            return false;
        }
        //获取考试信息
        $exam_res = DB::table($this->table)->where('id',$data['id'])->select('number','total_score','is_ranking')->first();
        $exam_res = json_decode(json_encode($exam_res),true);
        $new_time = explode('.',$res['use_time']);
        $res['use_time'] = '';
        if (isset($new_time[0]) && isset($new_time[1])){
            $res['use_time'] = $new_time[0].'分'.$new_time[1].'秒';
        }else{
            $res['use_time'] = $new_time[0].'分0秒';
        }
        //$res['use_time'] = $new_time[0].'分'.$new_time[1].'秒';
        $res['again'] = $exam_res['number'] - $res['number'];
        $res['total_score'] = $exam_res['total_score'];
        //判断是否需要显示排名
        if ($exam_res['is_ranking'] == 1){  //显示
            $list = $this->rankingList($data['id']);
            $res['total_number'] = count($list);
            foreach ($list as $k=>$l_v){
                if ($l_v['uid'] == $data['uid']){
                    $res['is_ranking'] = $k+1;
                }
            }
        }else{
            $res['total_number'] = 0;
            $res['is_ranking'] = 0;
        }
        return $res;
    }

    //考试结果导出
    public function examResultToExcelData($id){
        $res = DB::table('exam_results')->where('exam_id',$id)->get();
        $res = json_decode(json_encode($res),true);
        $list = $this->examResultAnalyse($id);
        $data['list'] = $list;
        $data['rows'] = $res;
        return $data;
    }

    //根据id获取字段值
    public function getSelResult($id,$field){
        $res = DB::table($this->table)->where('id',$id)->select($field)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res[$field];
    }

    //根据答卷id获取分组名
    public function getExamineerName($results_id){
        $exam_id = DB::table('exam_results')->where('id',$results_id)->select('exam_id')->first();
        $exam_id = json_decode(json_encode($exam_id),1);
        $id = $exam_id['exam_id'];
        $examinee = DB::table($this->table)->where('id',$id)->select('examinee_id')->first();
        $examinee = json_decode(json_encode($examinee),true);
        $examinee_ids = json_decode($examinee['examinee_id'],true);
        $txt = '';
        foreach ($examinee_ids as $id){
            $examineeGroupModel = new ExamineeGroup();
            $name = $examineeGroupModel->getGroupName($id);
            $txt .= $name.'/';
        }
        $txt = substr($txt,0,strlen($txt)-1);
        return $txt;
    }




























}

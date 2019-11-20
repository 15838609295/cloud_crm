<?php

namespace App\Models\Admin;

use App\Library\Common;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Questionnaire extends Model
{
    protected $table='questionnaire';  //问卷信息表
    protected $table_name='subject';   //问卷题目答案记录表

    public function addQuestionnaire($data){
        $que_info['cover'] = $data['cover'];
        $que_info['title'] = $data['title'];
        $que_info['remarks'] = $data['remarks'];
        $que_info['ending'] = $data['ending'];
        $que_info['status'] = $data['status'];
        $que_info['total'] = 0;
        $que_info['created_at'] = Carbon::now()->toDateTimeString();
        $que_id = DB::table($this->table)->insertGetId($que_info);

        $subject_datas = [];
        if (is_array($data['question'])){
            foreach ($data['question'] as $v){
                $subject_datas['questionnaire_id'] = $que_id;
                $subject_datas['topic'] = $v['title'];
                $subject_datas['answer'] = serialize($v['options']);
                $subject_datas['result'] = '';
                $subject_datas['is_fill'] = $v['is_fill'];
                $subject_datas['type'] = $v['type'];
            }
        }
        $res = DB::table($this->table_name)->insertAll($subject_datas);
        if ($que_id && $res){
            return true;
        }
        return false;
    }

    public function getList($data){
        $res = DB::table($this->table)->select('*');
        if ($data['status'] != ''){
            $res->where('status',$data['status']);
        }
        if($data['searchKey']!=''){
            $searchKey = $data['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('title', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('remarks', 'LIKE', '%' .$searchKey . '%')
                    ->orwhere('ending', 'LIKE', '%' .$searchKey . '%');
            });
        }
        if ($data['start_time'] != '' && $data['end_time'] != ''){
            $res->whereBetween('created_at',[$data['start_time'],$data['end_time']]);
        }elseif ($data['start_time'] != '' && $data['end_time'] == ''){
            $res->where('created_at','>=',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] != ''){
            $res->where('created_at','=<',$data['end_time']);
        }
        $list['total'] = $res->count();
        $result = $res->skip($data['start'])->take($data['pageSize'])->orderBy($data['sortName'], $data['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $list['rows'] = $result;
        return $list;
    }

    public function delQuset($id){
        $res = DB::table($this->table_name)->where('id',$id);
        if ($res){
            DB::table($this->table_name)->delete($id);
            return true;
        }
        return false;
    }

    public function get_statistical($data){
        $title_number = $data['title_number'];
        if ($data['page_size'] != '' && $data['page_no'] != ''){
            $start = ($data['page_no'] - 1) *$data['page_size'];
            $res = DB::table($this->table_name)
                ->where('questionnaire_id',$data['questionnaire_id'])
                ->skip($start)
                ->take($data['page_size'])
                ->get();
        }else{
            $res = DB::table($this->table_name)
                ->where('questionnaire_id',$data['questionnaire_id'])
                ->get();
        }
        if ($res){
            $res = json_decode(json_encode($res),true);
            $member_res = DB::table('findings')->where('questionnaire_id',$data['questionnaire_id'])->select('*');
            if ($data['start_time'] !='' && $data['end_time'] != ''){
                $member_res->whereBetween('created_at',[$data['start_time'],$data['end_time']]);
            }elseif ($data['start_time'] !='' && $data['end_time'] == ''){
                $member_res->where('created_at','>=',$data['start_time']);
            }elseif ($data['start_time'] == '' && $data['end_time'] !=''){
                $member_res->where('created_at','=<',$data['end_time']);
            }
            $result = $member_res->orderBy('id', 'desc')->get();
            $common = new Common();
            if ($result){
                $result = json_decode(json_encode($result),true);
                $list['rows'] = $common->statistics_data($title_number,$res,$result,$data['type']);
                $list['total'] = count($result);
                return $list;
            }
        }
        return false;
    }

    public function addSubject($data){
        foreach ($data as $k=>$v){
            $list['questionnaire_id'] = 1;
            $list['title'] = $v['title'];
            $list['title_number'] = $k+1;
            $list['answer'] = $v['answer'];
            $list['created_at'] = Carbon::now()->toDateTimeString();
            $list['result'] = 0;
            $list['is_fill'] = $v['is_fill'];
            $list['type'] = $v['type'];
            DB::table($this->table_name)->insert($list);
        }
        return true;
    }

    public function getQuestInfo($id){
        $res = DB::table($this->table)->where('id',$id)->first();
        $res = json_decode(json_encode($res),true);
        if ($res){
            $subjects = DB::table($this->table_name)->where('questionnaire_id',$id)->get();
            $subjects = json_decode(json_encode($subjects),true);
            foreach ($subjects as &$v){
                if ($v['type'] != 3){
                    $v['answer'] = unserialize($v['answer']);
                }
            }
            $res['problem'] = $subjects;
            return $res;
        }
        return false;
    }





















}

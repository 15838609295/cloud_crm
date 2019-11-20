<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Findings extends Model
{
    protected $table='findings';

    public function getDateList($data){
        $res = DB::table($this->table.' as f')
            ->select('f.*','m.name as name')
            ->leftJoin('member as m','f.member_id','=','m.id');
        if ($data['start_time'] != '' && $data['end_time'] != ''){
            $res->whereBetween('f.created_at',[$data['start_time'],$data['end_time']]);
        }elseif($data['start_time'] != '' && $data['end_time'] == ''){
            $res->where('f.created_at','>=',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] !=''){
            $res->where('f.created_at','=<',$data['end_time']);
        }
        $res->where('f.questionnaire_id',$data['questionnaire_id']);
        $list['total'] = $res->count();
        $result = $res->skip($data['start'])->take($data['pageSize'])->orderBy('f.'.$data['sortName'], $data['sortOrder'])->get();
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

    public function getInfo($id){
        $res = DB::table($this->table.' as f')
            ->select('f.*','m.name as member_name','q.title as que_name')
            ->leftJoin('member as m','f.member_id','=','m.id')
            ->leftJoin('questionnaire as q','f.questionnaire_id','=','q.id')
            ->where('f.id',$id)
            ->first();
        if ($res){
            $res = json_decode(json_encode($res),true);
            $result = unserialize($res['result']);
            $subject = DB::table('subject')->where('questionnaire_id',$res['questionnaire_id'])->get();
            $subject = json_decode(json_encode($subject),true);
            foreach ($subject as $k=>&$v){
                $answer = unserialize($v['answer']);
                if ($v['type'] != 3){
                    $answer[$result[$k][0]]['number'] +=1;
                    $v['answer'] = $answer;
                }else{
                    $v['answer'] = $result[$k][0];
                }
            }
            $subject['que_name'] = $res['que_name'];
            $subject['member_name'] = $res['member_name'];
            return $subject;
        }
        return false;
    }

    public function getAnswerList($data){
        $res = DB::table($this->table) ->where('questionnaire_id',$data['questionnaire_id']);
        if ($data['start_time'] != '' && $data['end_time'] != ''){
            $res->whereBetween('created_at',[$data['start_time'],$data['end_time']]);
        }elseif ($data['start_time'] != '' && $data['end_time'] == ''){
            $res->where('created_at','>=',$data['start_time']);
        }elseif ($data['start_time'] == '' && $data['end_time'] != ''){
            $res->where('created_at','=<',$data['end_time']);
        }
        $start = ($data['page_no'] -1) * $data['page_size'];
        $list['total'] = $res->count();
        $result = $res->skip($start)->take($data['page_size'])->orderBy($data['sortName'], $data['sortOrder'])->get();
        $rows = [];
        $result = json_decode(json_encode($result),true);
        foreach ($result as $k=>$v){
            $member_result = unserialize($v['result']);
            $rows[$k]['id'] = $v['id'];
            $rows[$k]['result'] = $member_result[$data['title_number'] - 1];
        }
        $list['rows'] = $rows;
        return $list;
    }

    public function addFindings($data){
        $data['end_time'] = $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if ($res){
            return true;
        }else{
            return false;
        }
    }
}

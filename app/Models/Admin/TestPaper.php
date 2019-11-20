<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TestPaper extends Model
{
    protected $table='test_paper';

    //添加试卷
    public function addTestPaper($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改试卷
    public function updateTestPaper($id,$data){
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除试卷
    public function delTestPaper($id){
       $res = DB::table($this->table)->delete($id);
       if (!$res){
           return false;
       }
//       //删除这张试卷的所有答卷
//        $answer_res = DB::table('answer_sheet')->whereIn('test_paper_id',$id)->count();
//        if ($answer_res){
//            DB::table('answer_sheet')->whereIn('test_paper_id',$id)->delete();
//        }
       return true;
    }

    //手动添加题目
    public function addTestSubject($id,$data){
        if ($data['type'] != 3){
            $answer = explode(',',$data['answer']);
            foreach ($answer as &$v){
                $v = (int)$v;
            }
            $data['answer'] = json_encode($answer);
        }
        $item_bank = DB::table($this->table)->where('id',$id)->select('item_bank_list')->first();
        $item_bank = json_decode(json_encode($item_bank),true);
        $list = json_decode($item_bank['item_bank_list'],1);   //题目id集合
        //添加到题库
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $item_bannk_id = DB::table('item_bank')->insertGetId($data);
        $k = count($list);
        $list[$k] = $item_bannk_id;
        $where['item_bank_list'] = json_encode($list);
        $where['updated_at'] =  Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //题库添加题目
    public function itemAddTest($id,$item_ids){
        $res = DB::table($this->table)->where('id',$id)->first();
        $res = json_decode(json_encode($res),true);
        $item_list = json_decode($res['item_bank_list'],1);
        if (!is_array($item_list)){
            $new_list = $item_ids;
        }else{
            $new_list = array_merge($item_list,$item_ids);
        }
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $where['item_bank_list'] = json_encode($new_list);
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改题目
    public function updateTestItem($item_id,$data){
        if ($data['type'] != 3){
            $answer = explode(',',$data['answer']);
            foreach ($answer as &$v){
                $v = (int)$v;
            }
            $data['answer'] = json_encode($answer);
        }
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table('item_bank')->where('id',$item_id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除试卷题目
    public function delTestIten($id,$item_id){
        $res = DB::table($this->table)->where('id',$id)->select('id','item_bank_list')->first();
        $res = json_decode(json_encode($res),true);
        $itme_list = json_decode($res['item_bank_list']);
        foreach ($itme_list as $k=>$v){
            if ($v == $item_id){
                unset($itme_list[$k]);
            }
        }
        //数组重新排序
        $new_arr = array_values($itme_list);
        $where['item_bank_list'] = json_encode($new_arr);
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $test_res = DB::table($this->table)->where('id',$res['id'])->update($where);
        if (!$test_res){
            return false;
        }
        return true;
    }

    //试卷列表
    public function testPaperList($data){
        $res = DB::table($this->table.' as t')
            ->select('t.id','t.name','t.type_id as typeId','et.name as type_name','t.item_bank_list')
            ->leftJoin('exam_type as et','t.type_id','=','et.id');
        if ($data['type_id'] != ''){
            $res->where('t.type_id',$data['type_id']);
        }
        if ($data['search'] != ''){
            $res->where('t.name', 'LIKE', '%' . $data['search'] . '%');
        }
        $result['total'] = $res->count();
        $result['rows'] = $res->skip($data['start'])->take($data['pageSize'])->orderBy('id', 'desc')->get();
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        foreach ($result['rows'] as &$v){
            $v['single'] = 0;   //单选题数
            $v['many'] = 0;     //多选题数
            $v['fill'] = 0;     //填空题数
            $v['raction'] = 0;  //试卷总分
            $item_bank_list = json_decode($v['item_bank_list'],1);
            if (is_array($item_bank_list)){
                foreach ($item_bank_list as $t){
                    $item = DB::table('item_bank')->where('id',$t)->select('type','fraction')->first();
                    $item = json_decode(json_encode($item),true);
                    if ($item['type'] == 1){
                        $v['single'] =$v['single'] + 1;
                    }elseif ($item['type'] == 2){
                        $v['many'] = $v['many'] + 1;
                    }elseif ($item['type'] == 3){
                        $v['fill'] = $v['fill'] + 1;
                    }
                    $v['raction'] = $v['raction'] + $item['fraction'];
                }
            }
        }
        return $result;
    }

    //试卷题目列表
    public function getTestPaperSubjectList($data){
        $res = DB::table($this->table)->where('id',$data['id'])->select('item_bank_list')->first();
        $res = json_decode(json_encode($res),true);
        $item_list = json_decode($res['item_bank_list'],1);
        if (!$item_list){
            return false;
        }
        $result = [];
        foreach ($item_list as $v){
            $res = DB::table('item_bank')->where('id',$v)->select('id','name','type','fraction')->first();
            $res = json_decode(json_encode($res),true);
            if ($data['type'] != ''){
                if ($data['type'] == $res['type']){
                    if ($data['search'] != ''){
                        if (strstr($res['name'],$data['search'])){
                            if ($res['type'] == 1){
                                $res['type_name'] = '单选题';
                            }elseif($res['type'] == 2){
                                $res['type_name'] = '多选题';
                            }elseif($res['type'] == 3){
                                $res['type_name'] = '填空题';
                            }
                            $result[] = $res;
                        }
                    }else{
                        if ($res['type'] == 1){
                            $res['type_name'] = '单选题';
                        }elseif($res['type'] == 2){
                            $res['type_name'] = '多选题';
                        }elseif($res['type'] == 3){
                            $res['type_name'] = '填空题';
                        }
                        $result[] = $res;
                    }
                }
            }else{
                if ($data['search'] != ''){
                    if (strstr($res['name'],$data['search'])){
                        if ($res['type'] == 1){
                            $res['type_name'] = '单选题';
                        }elseif($res['type'] == 2){
                            $res['type_name'] = '多选题';
                        }elseif($res['type'] == 3){
                            $res['type_name'] = '填空题';
                        }
                        $result[] = $res;
                    }
                }else{
                    if ($res['type'] == 1){
                        $res['type_name'] = '单选题';
                    }elseif($res['type'] == 2){
                        $res['type_name'] = '多选题';
                    }elseif($res['type'] == 3){
                        $res['type_name'] = '填空题';
                    }
                    $result[] = $res;
                }
            }
        }
        $total = count($result);
        $list_res['total'] = $total;
        $list_res['rows'] = $result;
        return $list_res;
    }

    //Excel导出试卷 获取所有试卷详情
    public function getTestPaperInfo($ids){
        if (is_array($ids)){  //多张试卷
            $list = [];
            foreach ($ids as $v){
                $res = DB::table($this->table.' as t')
                    ->select('t.*','et.name as type_name')
                    ->leftJoin('exam_type as et','t.type_id','=','et.id')
                    ->where('t.id',$v)
                    ->first();
                $res = json_decode(json_encode($res),true);
                $res['subject'] = [];
                //是否有题目
                $a = isset($res['item_bank_list']);
                if ($a){
                    $item_list = json_decode($res['item_bank_list']);
                    foreach ($item_list as $l){
                        $item = DB::table('item_bank')->where('id',$l)->select('id','name','type','fraction','option','answer','remarks')->first();
                        $item = json_decode(json_encode($item),true);
                        $item['option'] = json_decode($item['option'],1);
                        $res['subject'][] = $item;
                    }
                }
                $list[] = $res;
            }
        }else{
            return false;
        }
        return $list;
    }

    //批量删除试题
    public function batchDel($id,$list){
        $res = DB::table($this->table)->where('id',$id)->first();
        $res = json_decode(json_encode($res),1);
        $item_list = json_decode($res['item_bank_list'],1);
        $new_list = array_diff($item_list,$list);
        if (count($new_list) > 0){
            $where['item_bank_list'] = json_encode($new_list);
        }else{
            $where['item_bank_list'] = '';
        }
        $where['updated_at'] = Carbon::now()->toDateTimeString();

        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //试卷列表无分页
    public function testPaperNoPage(){
        $res = DB::table($this->table)->whereNotNull('item_bank_list')->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $data = [];
        foreach ($res as $k=>$r_v){
            $data[$k]['name'] = $r_v['name'];
            $data[$k]['id'] = $r_v['id'];
            $data[$k]['type_id'] = $r_v['type_id'];
            $data[$k]['fraction'] = 0;
            $item_bank_list = json_decode($r_v['item_bank_list'],true);
            foreach ($item_bank_list as $i_v){
                $i_res = DB::table('item_bank')->where('id',$i_v)->select('fraction')->first();
                $i_res = json_decode(json_encode($i_res),true);
                $fraction = $i_res['fraction'];
                $data[$k]['fraction'] += $fraction;
            }
        }
        return $data;
    }
}

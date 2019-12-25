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

    //根据条件获取信息
    public function getFields($field, $filter = [], $one = true){
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
    public function getValues($field, $filter){
        $data = DB::table($this->table)->where($filter)->value($field);
        if (!$data){
            return false;
        }
        return $data;
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
        $item_bank = DB::table($this->table)->where('id',$id)->value('item_bank_list');
        $list = json_decode($item_bank,1);   //题目id集合
        if (!$list){
            $k = 0;
        }else{
            $k = count($list);
        }
        //添加到题库
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $item_bannk_id = DB::table('item_bank')->insertGetId($data);
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
        if (!$result['rows']){
            return [];
        }
        $result['rows'] = json_decode(json_encode($result['rows']),true);
        foreach ($result['rows'] as &$v){
            $v['single'] = 0;   //单选题数
            $v['many'] = 0;     //多选题数
            $v['fill'] = 0;     //填空题数
            $v['raction'] = 0;  //试卷总分
            $ids = json_decode($v['item_bank_list'],1);
            if (is_array($ids)){
                $items = DB::table('item_bank')->whereIn('id',$ids)->select('type','fraction')->get();
                $items = json_decode(json_encode($items),true);
                foreach ($items as $t){
                    if ($t['type'] == 1){
                        $v['single'] =$v['single'] + 1;
                    }elseif ($t['type'] == 2){
                        $v['many'] = $v['many'] + 1;
                    }elseif ($t['type'] == 3){
                        $v['fill'] = $v['fill'] + 1;
                    }
                    $v['raction'] = $v['raction'] + $t['fraction'];
                }
            }
        }
        return $result;
    }

    //试卷题目列表
    public function getTestPaperSubjectList($data){
        $res = $this->getValues('item_bank_list',['id'=>$data['id']]);
        if (!$res){
            return false;
        }
        $ids = json_decode($res,1);
        $list = DB::table('item_bank')->whereIn('id',$ids)->select('id','name','type','fraction')->get();
        $list = json_decode(json_encode($list),true);
        foreach ($list as &$v){
            switch ($v['type']){
                case 1;
                    $v['type_name'] = '单选题';
                    break;
                case 2:
                    $v['type_name'] = '多选题';
                    break;
                case 3;
                    $v['type_name'] = '填空题';
                    break;
            }
        }
        $total = count($list);
        $list_res['total'] = $total;
        $list_res['rows'] = $list;
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
        $res = DB::table($this->table)->select('id','name','item_bank_list')->whereNotNull('item_bank_list')->get();
        if (!$res){
            return [];
        }
        $res = json_decode(json_encode($res),true);
        foreach ($res as $k=>&$v){
            $ids = json_decode($v['item_bank_list'],true);
            $list = DB::table('item_bank')->whereIn('id',$ids)->select('fraction')->get();
            $list = json_decode(json_encode($list),true);
            $v['fraction'] = 0;
            foreach ($list as $l_v){
                $v['fraction'] += $l_v['fraction'];
            }
            unset($res[$k]['item_bank_list']);
        }
        return $res;
    }
}

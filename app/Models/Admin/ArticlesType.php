<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArticlesType extends Model
{
    protected $table='articles_type';

    //列表
    public function getList($fields){
        $res = DB::table($this->table)->where('cid',0)->whereNull('deleted_at');
        $data['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $result = json_decode(json_encode($result),true);
        if (!$result){
            return [];
        }
        foreach ($result as &$v){
            $v['grade'] = 1;
            $res = $this->arrangement($v['id'],$v['grade'],1);
            if ($res){
                $v['children'] = $res;
            }
        }
        $data['rows'] = $result;
        return $data;
    }

    //处理分类子集处理
    private function arrangement($id,$grade,$type){
        if ($type == 1){
            $res = DB::table($this->table)
                ->select('id','name','cid','status','created_at','sort','type','icon')
                ->where('cid',$id)
                ->whereNull('deleted_at')
                ->get();
        }elseif ($type == 2){
            $res = DB::table($this->table)
                ->select('id as value','name as label','cid','icon')
                ->whereNull('deleted_at')
                ->where('status',1)
                ->where('cid',$id)
                ->get();
        }
        $res = json_decode(json_encode($res),true);
        if ($res){
            foreach ($res as &$v){
                $v['grade'] = $grade+1;
                if ($type == 1){
                    $v_res = $this->arrangement($v['id'],$v['grade'],$type);
                }else{
                    $v_res = $this->arrangement($v['value'],$v['grade'],$type);
                }
                if ($v_res){
                    $v['children'] = $v_res;
                }
            }
            return $res;
        }
    }

    //类型列表
    public function getTypeList(){
        $res = DB::table($this->table)
            ->select('id as value','name as label','cid','icon')
            ->where('cid',0)
            //->where('type',1)
            ->whereNull('deleted_at')
            ->get();
        $res = json_decode(json_encode($res),true);
        foreach ($res as &$v){
            $v['grde'] = 1;
            $v_res = $this->arrangement($v['value'],$v['grde'],2);
            if ($v_res){
                $v['children'] = $v_res;
            }
        }
        return $res;
    }

    //添加类型
    public function addType($data){
        $data['created_at'] = Carbon::now()->toDateTimeString();
        //如果添加的是专题
        if ($data['type'] == 2){
            $id = DB::table($this->table)->insertGetId($data);
            if (!$id){
                return false;
            }
            //去插件表添加
            $m_data['type_id'] = $id;
            $m_data['display'] = $data['status'];
            $m_data['created_at'] = Carbon::now()->toDateTimeString();
            DB::table('modular')->insert($m_data);
            return true;
        }
        //判断添加的类型和状态
        if ($data['cid'] != 0 && $data['status'] != 0){
            //修改父级状态
            $f_res = $this->ParentLevel($data['cid'],1);
            if (!$f_res){
                return false;
            }
        }
        $res = DB::table($this->table)->insert($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改类型
    public function updateType($id,$data){
        $old_res = DB::table($this->table)->where('id',$id)->first();
        $old_res = json_decode(json_encode($old_res),true);
        if ($old_res['type'] != $data['type']){  //改变类型值
            if ($data['type'] == 1){  //删除
                $where['type_id'] = $id;
                DB::table('modular')->where($where)->delete();
            }else{                    //创建
                $m_data['icon'] = $data['icon'];
                $m_data['type_id'] = $id;
                $m_data['display'] = $data['status'];
                $m_data['created_at'] = Carbon::now()->toDateTimeString();
                DB::table('modular')->insert($m_data);
            }
        }else if($old_res['type'] == 2){   //修改 专题
            $m_data['icon'] = $data['icon'];
            $m_data['display'] = $data['status'];
            $m_data['updated_at'] = Carbon::now()->toDateTimeString();
            DB::table('modular')->where('type_id',$id)->update($m_data);
        }
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //删除类型
    public function delType($id){
        //判断是否有子集
        $sun_id = DB::table($this->table)->where('cid',$id)->whereNull('deleted_at')->get();
        $sun_id = json_decode(json_encode($sun_id),true);
        if ($sun_id){
            return -1;
        }
        $where['deleted_at'] = Carbon::now()->toDateTimeString();
        $old_res = DB::table($this->table)->where('id',$id)->first();
        $old_res = json_decode(json_encode($old_res),true);
        if ($old_res['type'] == 2){  //专题 删除
            DB::table('modular')->where('type_id',$id)->delete();
        }
        $res = DB::table($this->table)->where('id',$id)->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改权重
    public function updateSort($id,$data){
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改显示状态
    public function updateStatus($data){
        //判断是不是专题
        $this_res = DB::table($this->table)->where('id',$data['id'])->first();
        $this_res = json_decode(json_encode($this_res),true);
        if ($this_res['type'] == 2){  //专题 同步插件表状态
            $fields['display'] = $data['status'];
            DB::table('modular')->where('type_id',$data['id'])->update($fields);
        }
        $where['status'] = $data['status'];
        //显示改父级 隐藏改子级
        if ($data['status'] == 1){  //显示    修改父级状态
            $f_result = $this->ParentLevel($data['id'],1);
            if (!$f_result){
                return false;
            }
            //显示也修改子集
            $c_result = $this->SubLevel($data['id'],1);
            if (!$c_result){
                return false;
            }
        }else{                      //隐藏     修改子集状态
            $c_result = $this->SubLevel($data['id'],0);
            if (!$c_result){
                return false;
            }
            $res = DB::table($this->table)->where('id',$data['id'])->update($where);
            if (!$res){
                return false;
            }
        }
        return true;
    }

    //修改父级状态
    private function ParentLevel($id,$status){
        $res = DB::table($this->table)->where('id',$id)->first();
        $res = json_decode(json_encode($res),true);
        if ($res && $res['status'] != 1){
            $where['status'] = $status;
            $u_res = DB::table($this->table)->where('id',$res['id'])->update($where);
            if (!$u_res){
                return false;
            }
            $this->ParentLevel($res['cid'],1);
        }
        return true;
    }

    //修改子集状态
    private function SubLevel($id,$status){
        $res = DB::table($this->table)->where('cid',$id)->get();
        $res = json_decode(json_encode($res),true);
        if ($res){
            foreach ($res as $v){
                if ($v['status'] != $status){
                    $where['status'] = $status;
                    $u_res = DB::table($this->table)->where('id',$v['id'])->update($where);
                    if (!$u_res){
                        return false;
                    }
                    $this->SubLevel($v['id'],$status);
                }
            }
        }
        return true;
    }

    //根据状态获取类型列表
    public function getArticlesList(){
        $res = DB::table($this->table)->where('status',1)->orderBy('sort','desc')->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //根据type_id返回此类型下所有id集合
    public function getTypeIdList($type){
        $res = DB::table($this->table)->where('type',$type)->select('id')->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        $ids = [];
        foreach ($res as $v){
            $ids[] = $v['id'];
        }
        return $ids;
    }
}

<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use function GuzzleHttp\Psr7\uri_for;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Street extends Model
{
    protected $table='street';

    //街道父级无分页
    public function getNoPageStreet(){
        $res = DB::table($this->table)->select('id','name')->where('cid',0)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //街道列表
    public function getStreetList($fields){
        $res = DB::table($this->table.' as s')
            ->where('cid',$fields['cid']);
        $data['total'] = $res->count();
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->get();
        if (!$res){
            return false;
        }
        $data['rows'] = json_decode(json_encode($result),true);
        $admin = DB::table('admin_users')->select('id','name')->get();
        $admin = json_decode(json_encode($admin),true);
        foreach ($data['rows'] as &$v){
            $v['admin_name'] = '';
            $admin_id = explode(',',$v['admin_id']);
            foreach ($admin_id as $a_v){
                foreach ($admin as $admin_v){
                    if ($admin_v['id'] == $a_v){
                        $v['admin_name'] .= $admin_v['name'].'/';
                    }
                }
            }
            $v['admin_name'] = substr($v['admin_name'],0,strlen($v['admin_name'])-1);
        }
        return $data;
    }

    //添加街道
    public function adsDate($fields){
        $fields['created_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->insert($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //修改街道信息
    public function updateStreetInfo($fields,$id){
        $fields['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($fields);
        if (!$res){
            return false;
        }
        return true;
    }

    //刪除街道
    public function delStreetId($id){
        $res = DB::table($this->table)->where('id',$id)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if ($res['cid'] == 0){  //查询他子集
            $son = DB::table($this->table)->where('cid',$res['id'])->get();
            $son = json_decode(json_encode($son),true);
            if ($son){
                return -1;
            }
        }
        $del_res = DB::table($this->table)->delete($id);
        if (!$del_res){
            return false;
        }
        return true;
    }

    //管理员绑定街道
    public function adminBindingStreet($fields){
        $where['admin_id'] = $fields['admin_id'];
        $where['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$fields['street_id'])->update($where);
        if (!$res){
            return false;
        }
        return true;
    }

    //获取服务电话
    public function gethotlineList($fields){
        $res = DB::table($this->table)
            ->select('id','name','tel')
            ->where('wx_status',1)
            ->where('cid','!=',1);
        $data['total'] = $res->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        if (!$data['rows']){
            return false;
        }
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    //获取街道二级联动列表
    public function getStreetListNoPage(){
        $res = DB::table($this->table)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $list = [];
        foreach ($res as $v){
            if ($v['cid'] == 0){
                $list[] = $v;
            }else{
                foreach ($list as &$l){
                    if ($l['id'] == $v['cid']){
                        $l['chartData'][] = $v;
                    }
                }
            }
        }
        foreach ($list as $k=>$v){
            if (empty($v['chartData'])){
                unset($list[$k]);
            }
        }
        return $list;
    }

    //根据街道id获取管路员id
    public function getadminId($id){
        $res = DB::table($this->table)->where('id',$id)->first();
        $res = json_decode(json_encode($res),true);
        return $res['admin_id'];
    }

    //根据管理获取负责街道id
    public function getAdminStreetId($admin_id){
        $res = DB::table($this->table)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $ids = [];
        foreach ($res as $v){
            if ($v['cid'] == 0){  //区域存两位以上
                $admin_ids = explode(',',$v['admin_id']);
                if (in_array($admin_id,$admin_ids)){
                    $street_list = DB::table($this->table)->select('id','admin_id')->where('cid',$v['id'])->get();
                    if ($street_list){
                        $street_list = json_decode(json_encode($street_list),true);
                        foreach ($street_list as $s_v){
                            $ids[] = $s_v['admin_id'];
                        }
                    }
                }
            }else{  //街道 属于一人
                if ($v['admin_id'] == $admin_id){
                    $ids[] = $v['admin_id'];
                }
            }
        }
        $ids = array_unique($ids);
        return $ids;
    }

    //街道信息导出
    public function getAllStreetList(){
        $res = DB::table($this->table)
            ->select('id','name','cid','tel as mobile','admin_id')
            ->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        $admin_res = DB::table('admin_users')->select('id','name')->get();
        $admin_res = json_decode(json_encode($admin_res),true);
        foreach ($res as &$v){
            if ($v['admin_id']){
                $admin_ids = explode(',',$v['admin_id']);
                $name = '';
                foreach ($admin_ids as $a_v){
                    foreach ($admin_res as $r_v){
                        if ($a_v == $r_v['id']){
                            $name .= $r_v['name'].'/';
                        }
                    }
                }
                $v['admin_name'] = substr($name,0,strlen($name)-1);
            }
        }
        $list = [];
        foreach ($res as $v){
            if ($v['cid'] == 0){
                $list[$v['id']] = $v;
            }else{
                $list[$v['cid']]['datalist'][] = $v['name'];
            }
        }
        return $list;
    }

    //管理员信息导出附带所管理的街道信息
    public function getAdminStreetInfo(){
        $res = DB::table('admin_users')->select('id','name as admin_name','mobile','email','hiredate')->get();
        $res = json_decode(json_encode($res),true);
        if (count($res) < 1){
            return false;
        }
        $street_data = DB::table($this->table)->select('name','admin_id')->where('cid','!=',0)->get();
        $street_data = json_decode(json_encode($street_data),true);
        foreach ($res as &$v){
            $v['street_data'] = [];
            if (count($street_data) > 0){
                foreach ($street_data as $s_v){
                    if ($s_v['admin_id'] == $v['id']){
                        $v['street_data'][] = $s_v['name'];
                    }
                }
            }
        }
        return $res;
    }

    //修改区域信息状态
    public function updateStreetStatus($id,$fields){
        $fields['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table)->where('id',$id)->update($fields);
        if (!$res){
            return false;
        }
        return true;
    }


























}

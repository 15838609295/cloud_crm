<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberSource extends Model
{
    protected $table_name='member_source';

    protected $fillable = [
        'source_id', 'source_name','created_at', 'updated_at'
    ];

    public function getSourceListWithFields()
    {
        $res = DB::table($this->table_name)
            ->select('source_name')
            ->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过ID获用户来源 */
    public function getMemberSourceByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过来源名称获用户来源 */
    public function getMemberSourceByName($name,$id = null)
    {
        $res = DB::table($this->table_name)
            ->where('source_name',$name);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $res = $res->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 获取用户来源列表 */
    public function getMemberSourceList()
    {
        $res = DB::table($this->table_name)->orderBy("order", "desc")->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 获取用户来源列表(带筛选条件) */
    public function getMemberSourceWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=""){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('source_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 添加用户来源 */
    public function memberSourceInsert($data)
    {
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 修改用户来源 */
    public function memberSourceUpdate($id, $data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除用户来源 */
    public function memberSourceDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

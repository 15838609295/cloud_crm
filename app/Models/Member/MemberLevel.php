<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberLevel extends Model
{
    protected $table_name='member_level';

    protected $fillable = [
        'id', 'name', 'discount','created_at', 'updated_at'
    ];

    /* 通过id获取等级信息 */
    public function getMemberLevelByID($id)
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

    //根据条件获取信息
    public function getFields($fields, $filter, $one = true){
        if (!$filter){
            return false;
        }
        $db = DB::table($this->table_name)->where($filter)->select($fields);
        if ($one){
            $data = $db->first();
        }else{
            $data = $db->get();
        }
        $data = json_decode(json_encode($data),true);
        return $data;
    }

    /* 等级名称是否存在 */
    public function getMemberLevelByName($name)
    {
        $res = DB::table($this->table_name)->where('name',$name)->count();
        return $res;
    }

    /* 获取用户等级列表 */
    public function getMemberLevelList()
    {
        $res = DB::table($this->table_name)->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 获取客户等级列表(带筛选条件) */
    public function getMemberLevelWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=""){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 检测名称重复 */
    public function checkNameRepeat($id,$name)
    {
        $res = DB::table($this->table_name)
            ->where('id','!=',$id)
            ->where('name',$name)
            ->count();
        return $res;
    }

    /* 添加用户等级规则 */
    public function memberLevelInsert($data)
    {
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 修改用户等级规则 */
    public function memberLevelUpdate($id, $data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除用户等级规则 */
    public function memberLevelDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

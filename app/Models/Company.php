<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $table_name = 'company';
    
	protected $fillable = [
        'id', 'name', 'wechat_channel_id', 'created_at', 'updated_at'
    ];

	/* 通过ID获取部门 */
	public function getCompanyByID($id)
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

    /* 通过ID获取部门 */
    public function getCompanyByName($name,$id=null)
    {
        $res = DB::table($this->table_name)
            ->where('name',$name);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $result = $res->first();
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    /* 验证部门名称是否存在 */
    public function checkCompanyName($id,$name)
    {
        $res = DB::table($this->table_name)
            ->where('name',$name)
            ->where('id','!=',$id)
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

    /* 获取部门列表 */
	public function getCompanyList($fields=['*'])
    {
        if(!is_array($fields)){
            return array();
        }
        $res = DB::table($this->table_name)->select($fields)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function getCompanyWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('name', 'LIKE', '%' . $searchKey . '%');
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

    /* 添加部门 */
    public function companyInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 部门信息更新 */
    public function companyUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 部门信息删除 */
    public function companyDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    //根据部门获取管理员
    public function getCompanyAdminList(){
        $res =DB::table($this->table_name)->select('id','name as label')->get();
        $res = json_decode(json_encode($res),true);
        foreach ($res as &$v){
            $personnel = DB::table('admin_users')->select('id','name')->where('company_id',$v['id'])->get();
            if ($personnel){
                $personnel = json_decode(json_encode($personnel),true);
                $v['personnel'] = $personnel;
            }else{
                $v['personnel'] = [];
            }

        }
        return $res;
    }
}

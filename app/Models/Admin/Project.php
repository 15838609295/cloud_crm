<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $table_name = 'project';
    
	protected $fillable = [
        'id', 'name', 'created_at', 'updated_at'
    ];

    public function getProjectListWithFields()
    {
        $res = DB::table($this->table_name)
            ->select('name')
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

    /* 通过id获取项目 */
    public function getProjectByID($id)
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

    /* 通过名称获取项目 */
    public function getProjectByName($name,$id=null)
    {
        $res = DB::table($this->table_name)->where('name',$name);
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

	public function getProjectList()
    {
        $res = DB::table($this->table_name)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 带筛选条件获取项目列表 */
    public function getProjectListWithFilter($fields)
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
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 添加项目 */
    public function projectInsert($data)
    {
        $data['created_at']=Carbon::now();
        $data['updated_at']=Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 更新项目 */
    public function projectUpdate($id,$data)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除项目 */
    public function projectDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cases extends Model
{
    protected $table_name='cases';

    protected $fillable = [
        'id', 'case_name', 'case_url', 'case_edition', 'industry', 'is_del', 'created_at', 'updated_at'
    ];

    /* 通过ID获取case */
    public function getCaseByID($id)
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

    /* 带筛选条件获取case列表 */
    public function getCasesWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('case_name', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if(isset($fields['type']) && $fields['type']!=''){
            $res->where('type',$fields['type']);
        }
        if(isset($fields['is_del'])){
            $res->where('is_del',$fields['is_del']);
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

    /* 插入case记录 */
    public function caseInsert($data)
    {
        $data['updated_at']=Carbon::now();
        $data['created_at']=Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 更新case记录 */
    public function caseUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 删除case记录 */
    public function caseDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }
}

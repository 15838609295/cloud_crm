<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cases extends Model
{
    protected $table_name='cases';

    protected $fillable = [
        'id', 'case_name', 'case_url', 'case_edition', 'industry', 'is_del', 'created_at', 'updated_at'
    ];

    //通过ID获取case
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
    public function getCasesWithFilter($filter_options = null)
    {
        $res = DB::table($this->table_name);
        if(is_array($filter_options)){
            foreach ($filter_options as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }elseif ($value[1]=='LIKE'){
                    $res->whereIn($value[0],'LIKE','%'.$value[2].'%');
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        $res = $res->get();
        if(!$res){
            return false;
        }
        $result = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
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

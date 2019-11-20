<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Problem extends Model
{
    protected $table_name='problem';

    protected $fillable = [
        'id', 'admin_user_id', 'problem_doc', 'state', 'remarks'
    ];

    /* 带筛选条件获取问题列表 */
    public function getProblemListWithFilter($user_id,$fields)
    {
        $res = DB::table($this->table_name.' as p')
            ->select('p.*','au.name')
            ->leftJoin('admin_users as au','au.id','=','p.admin_user_id');
        if($user_id>1){
            $res->where('p.admin_user_id',$user_id);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('p.problem_doc', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 添加问题 */
    public function problemInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 更新问题 */
    public function problemUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }
}

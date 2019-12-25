<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Articles extends Model
{
    protected $table_name = 'articles';

    protected $fillable = [
        'id', 'title', 'thumb', 'typeid', 'description', 'content', 'is_display','articles_type_id'
    ];

    //通过ID获取文章
    public function getArticlesByID($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $res['type_name'] = '';
        if(is_array(json_decode($res['articles_type_id'],true))){
            $type = json_decode($res['articles_type_id'],true);
            foreach ($type as $v){
                $type_id = array_pop($v);
                $names = DB::table('articles_type')->where('id',$type_id)->select('name')->first();
                $names = json_decode(json_encode($names),true);
                $res['type_name'] .= $names['name'].'/';
            }
            $res['type_name'] = substr($res['type_name'],0,strlen($res['type_name'])-1);
        }else{
            $names = DB::table('articles_type')->where('id',$res['articles_type_id'])->select('name')->first();
            $names = json_decode(json_encode($names),true);
            $res['type_name'] .= $names['name'];
        }
        return $res;
    }

    //通过文章类型获取文章
    public function getArticlesByType($typeid)
    {
        $res = DB::table($this->table_name)->where('typeid',$typeid)->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function getApiArticle($typeid)
    {
        $res = DB::table($this->table_name)->where('typeid',$typeid)->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function getSpecialApiArticle($typeid)
    {
        $res = DB::table($this->table_name)->where('typeid',$typeid)
            ->where('is_display',0)
            ->where(function ($query){
                $query->where('read_power', 0)
                    ->orwhere('read_power', 1);
            })
            ->orderBy('id','desc')
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //带筛选条件获取article
    public function getArticlesWithFilter($fields){
        if ($fields['articles_type_id'] != ''){  //查询此类型下的新闻
            $id_res = DB::table($this->table_name)
                ->select('id','articles_type_id');
            if ($fields['searchKey'] != ''){
                $searchKey = $fields['searchKey'];
                $id_res->where(function ($query) use ($searchKey) {
                    $query->where('title', 'LIKE', '%' . $searchKey . '%');
                });
            }
            if($fields['typeid']!=''){
                $id_res->where('typeid',$fields['typeid']);
            }

            $id_result = $id_res->orderBy($fields['sortName'], $fields['sortOrder'])->get();
            $id_result = json_decode(json_encode($id_result),true);
            if (!$id_result){
                $data['total'] = 0;
                $data['rows'] = [];
                return $data;
            }
            $list = [];
            foreach ($id_result as $v){
                if(is_array(json_decode($v['articles_type_id'],true))){
                    $arr = json_decode($v['articles_type_id'],true);
                    $ids = [];
                    foreach ($arr as $a_v){
                        $ids = array_merge($ids,$a_v);
//                        $ids = array_pop($a_v);
                    }
                    if (in_array($fields['articles_type_id'],$ids)){
                        $list[] = $v['id'];
                    }
                }else{
                    if ($v['articles_type_id'] == $fields['articles_type_id']){
                        $list[] = $v['id'];
                    }
                }
            }
            $ids = array_slice($list,$fields['start'],$fields['pageSize']);
            $result = DB::table($this->table_name)->whereIn('id',$ids)->get();
            $result = json_decode(json_encode($result),true);

        }else{                                     //查询全部新闻
            $res = DB::table($this->table_name)
                ->select('id','title','typeid','is_display','created_at','read_power','thumb','articles_type_id');
            if($fields['searchKey']!=''){
                $searchKey = $fields['searchKey'];
                $res->where(function ($query) use ($searchKey) {
                    $query->where('title', 'LIKE', '%' . $searchKey . '%');
                });
            }
            if($fields['typeid']!=''){
                $res->where('typeid',$fields['typeid']);
            }
            $data['total'] = $res->count();
            $data['rows'] = [];
            $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
            if(!$result){
                return $data;
            }
            $result = json_decode(json_encode($result),true);
            if(!is_array($result) || count($result)<1){
                return $data;
            }
        }
        //处理分类名称
        foreach ($result as &$v){
            if(!is_array(json_decode($v['articles_type_id'],true))){
                $names = DB::table('articles_type')->where('id',$v['articles_type_id'])->select('name')->first();
                $names = json_decode(json_encode($names),true);
                $v['type_name'] = $names['name'];
            } else {
                $arr = json_decode($v['articles_type_id'],true);
                $v['type_name'] = '';
                foreach ($arr as $a_v){
                    $type_id = array_pop($a_v);
                    $names = DB::table('articles_type')->where('id',$type_id)->select('name')->first();
                    $names = json_decode(json_encode($names),true);
                    $v['type_name'] .= $names['name'].'/';
                }
                $v['type_name'] = substr($v['type_name'],0,strlen($v['type_name'])-1);
            }
            if ($v['thumb']){
                $thumb = explode(',',$v['thumb']);
                $v['thumb'] = $thumb[0];
            }
        }
        $data['rows'] = $result;
        return $data;
    }

    public function getArticlesContentWithFilter($fields){
        $res = DB::table($this->table_name)
            ->select('title','created_at','content');
        if($fields['typeid']!=''){
            $res->where('typeid',$fields['typeid']);
        }
        $res->where("is_display", 0);
        $result = $res->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return [];
        }
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $k => $v){
            $month = date("Y-m", strtotime($v['created_at']));
            $data[$month] = $v;
        }
        $data = array_sort($data);
        if(!is_array($data) || count($data)<1){
            return [];
        }
        return $data;
    }

    //带自定义筛选条件获取article
    public function getCustomArticlesWithFilter($fields)
    {
        $res = DB::table($this->table_name);
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('title', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if($fields['read_power']!=''){ $res->whereIn('read_power',$fields['read_power']);}
        if($fields['typeid']!=''){ $res->where('typeid',$fields['typeid']);}
        if($fields['is_display']!=''){ $res->where('is_display',$fields['is_display']);}
        if($fields['articles_type_id']!='') {
            $res->where('articles_type_id',$fields['articles_type_id']);
        }
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    //保存article
    public function articleInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //修改article
    public function articleUpdate($id,$data)
    {
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    //删除article
    public function articleDelete($id)
    {
        $res = DB::table($this->table_name)->where('id',$id)->delete();
        if(!$res){
            return false;
        }
        return true;
    }

    public function getArticleByCustome($filter_options,$type,$orderBy = null,$limit = null)
    {
        $res = DB::table($this->table_name);
        if(is_array($filter_options)){
            foreach ($filter_options as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        if(is_array($orderBy)){
            $res->orderBy($value[0],$value[1]);
        }
        if($limit!=null){
            $res->limit($limit);
        }
        if($type=='single'){
            $result = $res->first();
        }else{
            $result = $res->get();
        }
        if(!$result){
            return false;
        }
        $res = json_decode(json_encode($result),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取最新3条资讯
    public function getNews(){
        $res = DB::table($this->table_name)->where('typeid',1);
        $res ->where(function ($query){
            $query->where('read_power', 0)
                ->orwhere('read_power', 2);
        });
        $result = $res->where('is_display',0)->skip(0)->take(3)->orderBy('id','desc')->get();
        $result = json_decode(json_encode($result),true);
        foreach ($result as &$v){
            if ($v['thumb']){
                $thumb = explode(',',$v['thumb']);
                $v['thumb'] = $thumb[0];
            }
        }
        return $result;
    }

    //获取最新公告
    public function getNotice(){
        $res = DB::table($this->table_name)->where('typeid',3)->select('id','title');
        $result = $res->where('is_display',0)->skip(0)->take(3)->orderBy('id','desc')->get();
        $result = json_decode(json_encode($result),true);
        return $result;
    }

    //根据类型id获取类型下所有新闻
    public function getTypeNewsList($type_id,$data){
        $id_res = DB::table($this->table_name)
            ->whereNotNull('articles_type_id')
            ->where('is_display',0)
            ->select('id','articles_type_id');
        $id_result = $id_res->get();
        $id_result = json_decode(json_encode($id_result),true);
        if (!$id_result){
            $data = [];
            return $data;
        }
        $list = [];
        foreach ($id_result as $v){
            $arr = [];
            if(!is_array(json_decode($v['articles_type_id'],true))){
                if ((int)$type_id == $v['articles_type_id']){
                    $arr[] = $v['articles_type_id'];
                }
            } else {
                $arr_ids = json_decode($v['articles_type_id'],true);
                foreach ($arr_ids as $a_v){
                    $arr[] = array_pop($a_v);
                }
            }
            if (in_array((int)$type_id,$arr)){
                $list[] = $v['id'];
            }
        }
        $ids = array_slice($list,$data['start'],$data['pageSize']);
        $result = DB::table($this->table_name)->whereIn('id',$ids)->get();
        $result = json_decode(json_encode($result),true);
        return $result;
    }

    //获取公司简介
    public function getEnterprise(){
        $res = DB::table($this->table_name)->where('typeid',5)->first();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $data = json_decode($res['content'],true);
        return $data;
    }

    //修改type类型为5的公司简介
    public function updateEnterprise($fields){
        $res = DB::table($this->table_name)->where('typeid',5)->first();
        if (!$res){ //添加
            $data['title'] = '公司简介';
            $data['typeid'] = 5;
            $data['content'] = json_encode($fields);
            $data['created_at'] = Carbon::now()->toDateTimeString();
            $result = DB::table($this->table_name)->insert($data);
            if (!$result){
                return false;
            }
            return true;
        }
        //修改
        $data['content'] = json_encode($fields);
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $result = DB::table($this->table_name)->where('typeid',5)->update($data);
        if (!$result){
            return false;
        }
        return true;
    }

    //获取有视频的新闻
    public function getVideoList($fields){
        $res = DB::table($this->table_name)->whereNotNull('file_url')->skip($fields['start'])->take($fields['pageSize'])->get();
        $res = json_decode(json_encode($res),true);
        if (!$res){
            return [];
        }else{
            foreach ($res as $k=>$v){
                if ($v['file_url'] == ''){
                    unset($res[$k]);
                }
            }
        }
        return $res;
    }
}

<?php
namespace App\Http\Controllers\Wxapi;

use App\Models\Admin\Articles;
use foo\bar;
use Illuminate\Support\Facades\DB;

class NewsController extends BaseController{

    public function __construct()
    {
        $this->noCheckOpenidAction = ['getTypeList','getTypeNewsList','TypeNewsList','newsInfo']; //不校验openid
        parent::__construct();
    }

    //分类列表
    public function getTypeList(){
        $type_id = request()->input('type_id','');
        if ($type_id != ''){
            $children = $this->Selectlevel($type_id);
            if($children) {
                $types = $children;
            }else{
                $types = [];
            }
        }else{
            $types = DB::table('articles_type')
                ->select('id','name','cid','icon')
                ->where('type',1)
                ->whereNull('deleted_at')
                ->where('status',1)
                ->where('cid',0)
                ->orderBy('sort','desc')
                ->get();
            $types = json_decode(json_encode($types),true);
            foreach ($types as &$v){
                $children = $this->Selectlevel($v['id']);
                if ($v['icon']){
                    $v['icon'] = $this->processingPictures($v['icon']);
                }else{
                    $icon = 'uploads/default/type.png';
                    $v['icon'] = $this->processingPictures($icon);
                }
                if($children){
                    $v['children'] = $children;
                }
            }
        }
        $this->result['data'] = $types;
        return response()->json($this->result);
    }

    //分类父子级处理
    private function Selectlevel($id){
        $res = DB::table('articles_type')
            ->select('id','name','cid','icon')
            ->where('status',1)
            ->whereNull('deleted_at')
            ->where('cid',$id)
            ->orderBy('sort','desc')
            ->get();
        $res = json_decode(json_encode($res),true);
        if ($res){
            foreach ($res as &$v){
                if ($v['icon']){
                    $v['icon'] = $this->processingPictures($v['icon']);
                }else{
                    $icon = 'uploads/default/type.png';
                    $v['icon'] = $this->processingPictures($icon);
                }
                $v_res = $this->Selectlevel($v['id']);
                if ($v_res){
                    $v['children'] = $v_res;
                }
            }
        }
        return $res;
    }

    //获取分类下新闻
    public function getTypeNewsList(){
        $type_id = request()->input('id','');
        $data['pageNo'] = request()->input('page',1);
        $data['pageSize'] = 10;
        $data['start'] = ($data['pageNo'] -1 ) * $data['pageSize'];
        $articlesModel = new Articles();
        $res = $articlesModel->getTypeNewsList($type_id,$data);
        foreach ($res as &$v){
            if ($v['thumb']){
                $thumb = explode(',',$v['thumb']);
                foreach ($thumb as &$t_v){
                    $t_v = $this->processingPictures($t_v);
                }
                $v['thumb'] = $thumb;
            }
        }
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //新闻
    public function TypeNewsList(){
        $a = new Articles();
        $res = $a->getTypeNews();
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //新闻详情
    public function newsInfo(){
        $id = request()->input('newsId','');
        $res = DB::table('articles')->where('id',$id)->first();
        $res = json_decode(json_encode($res),true);
        if ($res['thumb']){
            $thumb = explode(',',$res['thumb']);
            foreach ($thumb as &$v){
                $v = $this->processingPictures($v);
            }
            $res['thumb'] = $thumb;
        }
        if ($res['file_url']){
            $res['file_url'] = $this->processingPictures($res['file_url']);
            if ($res['video_cover']){
                $res['video_cover'] = $this->processingPictures($res['video_cover']);
            }else{
                $video_cover = 'uploads/default/video.jpg';
                $res['video_cover'] = $this->processingPictures($video_cover);
            }
        }
        if ($res['created_at']){
            $res['created_at'] = (substr($res['created_at'],0,10));
        }
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //视频中心
    public function videoCenter(){
        $pageNo = request()->input('pageNo',1);
        $data['pageSize'] = request()->input('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $articlesModel = new Articles();
        $res = $articlesModel->getVideoList($data);
        foreach ($res as &$v){
            if($v['video_cover']){
                $v['video_cover'] = $this->processingPictures($v['video_cover']);
            }else{
                $video_cover = 'uploads/default/video.jpg';
                $v['video_cover'] = $this->processingPictures($video_cover);
            }
        }
        $this->result['data'] = array_values($res);
        return response()->json($this->result);
    }

}






?>
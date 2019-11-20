<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Album extends Model
{
    protected $table='album';
    //相册
    public function addPhoto($id,$data){
        $time = Carbon::now()->toDateTimeString();
        foreach ($data['chart'] as $v){
            $info['member_id'] = $id;
            $info['created_at'] = $time;
            $info['chart'] = $v;
            $res = DB::table($this->table)->insert($info);
            if (!$res){
                return false;
            }
        }
        return true;
    }

    //获取相册列表
    public function getAlbumList($id){
        $res = DB::table($this->table)->where('member_id',$id)->orderBy('id','desc')->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //获取最近4张图片
    public function getFour($id){
        $res = DB::table($this->table)->where('member_id',$id)->orderBy('id','desc')->skip(0)->take(4)->get();
        $res = json_decode(json_encode($res),true);
        return $res;
    }

    //删除图片
    public function delAlbum($id){
        $res = DB::table($this->table)->delete($id);
        if (!$res){
            return false;
        }
        return true;
    }

}

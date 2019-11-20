<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class China extends Model
{
    protected $table='china';

    public function getRegionList(){
        $res = DB::table($this->table)->select('id as code','name','pid')->get();
        $res = json_decode(json_encode($res),true);
        $data = [];
        foreach ($res as $k=>$v){
            if ($v['pid'] == '0'){
                $data[] = $v;
            }else{
                foreach ($data as &$d){
                    if ($v['pid'] == $d['code']){
                        $d['children'][] = $v;
                    }else{
                        foreach ($d['children'] as &$c){
                            if ($v['pid'] == $c['code']){
                                $c['children'][] = $v;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    //根据id获取名称
    public function getName($id){
        if ($id != ''){
            $res = DB::table($this->table)->where('id',$id)->select('name')->first();
            $res = json_decode(json_encode($res),true);
            return $res['name'];
        }else{
            return '';
        }

    }

    //根据名称获取id
    public function getId($name){
        $res = DB::table($this->table)->where('name',$name)->first();
        $res = json_decode(json_encode($res),true);
        return $res['id'];
    }
}

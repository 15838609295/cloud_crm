<?php

namespace App\Models\Article;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArticleBase extends Model
{
    protected $table_name='articles';

    public function getIndexArticles()
    {
        $res = DB::table($this->table_name)
            ->where('typeid','=',3)
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }
}

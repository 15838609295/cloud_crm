<?php

namespace App\Models\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletLogs extends Model
{
    protected $table_name='wallet_logs';

	public function getWalletLogs($type)
    {
        $date = Carbon::now()->toDateString();
        if($type=='balance'){
            $symbol = '=';
        }else{
            $symbol = '<>';
        }
        $res = DB::table($this->table_name)
            ->where(function ($query) use($symbol) {
                $query->where('type', $symbol, '0')
                    ->orwhere('type', $symbol, '9')
                    ->orwhere('type', $symbol, '5');
            })
            ->where('created_at','LIKE','%'.substr($date,0,7).'%')
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

    public function getMonthMoney($type)
    {
        $data = array(
            'month_money' => 0,
            'month_use_money' => 0
        );
        $list = $this->getWalletLogs($type);
        if(!is_array($list)){
            return $data;
        }
        foreach($list as $k=>$v){
            if($v['type']==0){
                if($v['money']>0){
                    $data['month_money'] += $v['money'];
                }else{
                    $data['month_use_money'] += $v['money'];
                }
            }else{
                $data['month_use_money'] += $v['money'];
            }
        }
        return $data;
    }

	public function walletLogInsert($data)
    {
        $data['created_at'] = Carbon::now();
        $data['updated_at'] = Carbon::now();
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }
}
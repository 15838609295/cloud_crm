<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MemberMoney extends Model
{

    /* 计算 赠送金/余额 总金额 */
    public function getMemberTotalMoney($type)
    {
        $res = DB::table('member_extend')->sum($type);
        return $res;
    }
}

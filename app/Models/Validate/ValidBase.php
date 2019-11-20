<?php

namespace App\Models\Validate;

use App\Http\Config\ErrorCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class ValidBase extends Model
{
    private static $instance;

    public $returnData = array(
        'code' => 0,
        'msg' => '验证成功'
    );

    public static function factory($type)
    {
        $modelPath = '\App\Models\Validate\\'.$type;
        self::$instance = new $modelPath();
        return self::$instance;
    }

    public function validate($data,$fields)
    {
        $validator = Validator::make($data,$fields['rule'],$fields['message']);//验证参数
        if ($validator->fails()) {
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = $validator->errors()->all();
        }
        return $this->returnData;
    }
}

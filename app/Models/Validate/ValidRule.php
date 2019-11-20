<?php

namespace App\Models\Validate;

use App\Http\Config\ErrorCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ValidRule extends ValidBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function c_token($data)
    {
        $params =  [
            'rule' => [
                'token' => 'required|regex:/^[a-zA-z0-9]{32}$/'
            ],
            'message' =>  [
                'token.required' => '用户凭证不能为空',
                'token.regex' => '用户凭证格式不正确'
            ]
        ];
        return $this->validate($data,$params);
    }

    public function c_idCard($data)
    {
        $res = $this->_verifyIdCard($data['id_card']);
        if(!$res){
            $this->returnData = ErrorCode::$admin_enum['id_card_error'];
        }
        return $this->returnData;
    }

    private function _verifyIdCard($cardNo)
    {
        $city_no = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $cardNo)) return false;
        if (!in_array(substr($cardNo, 0, 2), $city_no)) return false;
        $cardNo = preg_replace('/[xX]$/i', 'a', $cardNo);
        $no_length = strlen($cardNo);
        if ($no_length == 18) {
            $birthday = substr($cardNo, 6, 4) . '-' . substr($cardNo, 10, 2) . '-' . substr($cardNo, 12, 2);
        } else {
            $birthday = '19' . substr($cardNo, 6, 2) . '-' . substr($cardNo, 8, 2) . '-' . substr($cardNo, 10, 2);
        }
        if (date('Y-m-d', strtotime($birthday)) != $birthday) return false;
        if ($no_length == 18) {
            $sum = 0;
            for ($i = 17 ; $i >= 0 ; $i--) {
                $subStr = substr($cardNo, 17 - $i, 1);
                $sum += (pow(2, $i) % 11) * (($subStr == 'a') ? 10 : intval($subStr , 11));
            }
            if($sum % 11 != 1) return false;
        }
        return true;
    }
}

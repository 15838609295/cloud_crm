<?php

namespace App\Models\User;


use App\Http\Config\ErrorCode;
use App\Models\Company;
use App\Models\Wechat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserWechat extends Model
{
    private $returnData = array('code' => 0, 'msg' => 'success');

    public function buildQYUser($user_id)
    {
        $userModel = new UserBase();
        $userData = $userModel->getAdminByID($user_id);
        if(count($userData)<1){
            $this->returnData['code'] = 99;
            $this->returnData['msg'] = '用户不存在';
            return $this->returnData;
        }
        $companyModel = new Company();
        $companyData = $companyModel->getCompanyByID($userData['company_id']);
        $wechat = new Wechat();
        $valid_res = $wechat->buildQYUserWechat(['userid'=>$userData['wechat_id']],'get');
        if(isset($valid_res['userid'])){
            $res = $this->updateQYUser($userData,$companyData);
            Log::info('qywechat_user_update_res:',array('result'=>$res));
            return $this->returnData;
        }
        $res = $this->createQYUser($userData,$companyData);
        Log::info('qywechat_user_update_res:',array('result'=>$res));
        return $this->returnData;
    }

    public function createQYUser($userData,$companyData)
    {
        $data = array(
            'userid' => $userData['wechat_id'],
            'name' => $userData['name'],
            'mobile' => $userData['mobile'],
            'department' => $companyData['wechat_channel_id'],
            'position' => $userData['position'],
            'gender' => $userData['sex']=='女'? 2 : 1,
        );
        $qyWechatData = $data;
        $qyWechatData['email'] = $userData['email'];
        $emailData = $data;
        $emailData['password'] = 123456;
        $emailData['cpwd_login'] = 1;
        $inviteData = ['user'=>$userData['wechat_id']];
        $wechat = new Wechat();
        return array(
            'add_qyuser_res' => $wechat->buildQYUserWechat($qyWechatData,'create'),
            'add_qyemail_res' => $wechat->buildQYEmail($emailData,'create'),
            'invite_res' => $wechat->inviteQYUser($inviteData),
        );
    }

    public function updateQYUser($userData,$companyData)
    {
        $data = array(
            'userid' => $userData['wechat_id'],
            'name' => $userData['name'],
            'mobile' => $userData['mobile'],
            'department' => $companyData['wechat_channel_id'],
            'position' => $userData['position'],
            'gender' => $userData['sex']=='女'? 2 : 1,
            'enable' => $userData['status'] == 1 ? 0 : 1                                                                // 启用 1/禁用 0
        );
        $qyWechatData = $data;
        $qyWechatData['email'] = $userData['email'];
        $emailData = $data;
        $wechat = new Wechat();
        return array(
            'update_qyuser_res' => $wechat->buildQYUserWechat($qyWechatData,'update'),
            'update_qyemail_res' => $wechat->buildQYEmail($emailData,'update')
        );
    }

    public function deleteQYUser($wechat_id)
    {
        $data = ['userid'=>$wechat_id];
        $wechat = new Wechat();
        return array(
            'delete_qyuser_res' => $wechat->buildQYUserWechat($data,'delete'),
            'delete_qyemail_res' => $wechat->buildQYEmail($data,'delete')
        );
    }

}

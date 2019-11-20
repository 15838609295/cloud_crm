<?php
namespace App\Http\Controllers\Wxapi;

use App\Models\Admin\Album;
use App\Models\Admin\China;
use App\Models\Admin\ServiceHotline;
use App\Models\Member\MemberVip;
use Illuminate\Support\Facades\DB;

class MailListController extends BaseController{

    public function __construct()
    {
        parent::__construct();
        //权限验证
        $activity_is_limit = DB::table('permissions')->where('id',167)->first();
        $activity_is_limit = json_decode(json_encode($activity_is_limit),true);
        if ($activity_is_limit['is_limit'] == 1){   //需要验证 判断用户是否验证
            $member_info = DB::table('member')->where('id',$this->user['id'])->first();
            $member_info = json_decode(json_encode($member_info),true);
            if ($member_info['is_vip'] == 0){
                $this->result['status'] = 203;
                $this->result['msg'] = '账号尚未认证';
                return response()->json($this->result);
            }elseif ($member_info['is_vip'] == 1){
                $this->result['status'] = 204;
                $this->result['msg'] = '认证信息待审核';
                return response()->json($this->result);
            }
            elseif ($member_info['is_vip'] == 3){
                $this->result['status'] = 205;
                $this->result['msg'] = '认证未通过，请重新提交认证';
                return response()->json($this->result);
            }
        }
    }

    //通讯录列表
    public function getMemberVipList(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $search = request()->input('search','');
        $memberVipModel = new MemberVip();
        $res = $memberVipModel->getVipGroup($search);
        foreach ($res as &$v){
            if (count($v['list']) > 0){
                foreach ($v['list'] as &$l) {
                    $l['avatar'] = $this->processingPictures($l['avatar']);
                }
            }
        }
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    //会员详情
    public function getVipInfo(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $id = request()->input('id');
        $memberVipModel = new MemberVip();
        $member_info = $memberVipModel->getIdInfo($id);
        $chinaModel = new China();
        $member_info['province'] = $chinaModel->getName($member_info['province']);
        $member_info['city'] = $chinaModel->getName($member_info['city']);
        $member_info['area'] = $chinaModel->getName($member_info['area']);
        if ($member_info['avatar']){
            $member_info['avatar'] = $this->processingPictures($member_info['avatar']);
        }
        //获取用户最近4张相册
        $albumModel = new Album();
        $album = $albumModel->getFour($id);
        $data['member_info'] = $member_info;
        $data['album'] = $album;
        $this->result['data'] = $data;
        return response()->json($this->result);
    }

    //会员相册
    public function vipAlbum(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $id = request()->input('id');
        $albumModel = new Album();
        $res = $albumModel->getAlbumList($id);
        if ($res){
            $data = [];
            $cover = $res[0];
            foreach ($res as $v){
                $month = date("Y-m-d", strtotime($v['created_at']));
                $data[$month][] = $v;
            }
            $array = [];
            foreach ($data as $k=>$v){
                $time['time'] = $k;
                $time['list'] = $v;
                $array[] = $time;
            }
            $result['info'] = $array;
            $result['cover'] = $cover;
            $this->result['data'] = $result;
        }else{
            $data['cover'] = '';
            $data['info'] = '';
            $this->result['data'] = $data;
        }
        return response()->json($this->result);
    }

}
?>
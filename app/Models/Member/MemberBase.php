<?php

namespace App\Models\Member;

use App\Http\Config\ErrorCode;
use App\Library\Tools;
use App\Models\Admin\AssignLog;
use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberBase extends Model
{
    protected $table_name='member';

    //验证token有效性
    public function getUserBasicBySessionID($session_id)
    {
        $membersessionModel = new MemberSession();
        $member_info = $membersessionModel->getSession($session_id);
        if(!$member_info){
            return false;
        }
        if ($member_info['login_ip'] != Tools::get_client_ip()){
            return false;
        }
        $res = DB::table($this->table_name.' as m')
            ->select('m.id','m.name','ml.discount','me.level','ml.name as level_name','me.balance','me.cash_coupon')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','me.level')
            ->where('m.id','=',$member_info['member_id'])
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过openid获取用户 */
    public function getMemberByOpenID($open_id)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.*','ml.name as level_name', "ml.discount","mx.balance",'mx.cash_coupon')
            ->leftJoin('member_extend as mx','mx.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','mx.level')
            ->where('m.openid',$open_id)
            ->first();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /*腾讯云小程序通过openid获取用户信息*/
    public function getTencentByOpenID($tencent_openid){
        $res = DB::table($this->table_name.' as m')
            ->select('m.*','ml.name as level_name', "ml.discount","mx.balance")
            ->leftJoin('member_extend as mx','mx.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','mx.level')
            ->where('m.tencent_openid',$tencent_openid)
            ->first();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 获取带筛选条件的客户列表和资金明细记录 */
    public function getMemberRichListByMemberID($user_id,$fields,$type)
    {
        $res = DB::table('wallet_logs')->where('uid',$user_id);

        if($type=='wallet'){
            $res->where(function ($query) {
                $query->where("type","=","0")
                    ->orwhere("type","=","9");
            });
        }else{
            $res->where("type","!=","0")->where("type","!=","9");
        }
        $total = $res;
        $data['total'] = $total->count();
        if($data['total']<1){
            $data['rows'] = [];
            return $data;
        }
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    /* 通过ID获取客户基础数据 */
    public function getMemberByID($id)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.*','me.tencent_id','me.wechat','me.telephone','me.spare_mobile','me.remarks')
            ->leftJoin('member_extend as me','m.id','=','me.member_id');
        if(is_array($id)){
            $res->whereIn('id',$id);
            $res = $res->get();
        }else{
            $res->where('id',$id);
            $res = $res->first();
        }
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过ID获取客户基础数据 */
    public function getMemberExtendByID($id)
    {
        $res = DB::table("member_extend");
        if(is_array($id)){
            $res->whereIn('member_id',$id);
            $res = $res->get();
        }else{
            $res->where('member_id',$id);
            $res = $res->first();
        }
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过管理员ID获取客户数据 */
    public function getMemberByAdminID($id,$date)
    {
        $res = DB::table('member as m')
            ->select('me.recommend',DB::raw('SUM(1) AS total_member'))
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('admin_users as au','au.id','=','me.recommend')
            ->groupBy('me.recommend');
        if(!empty($date)){
            $res->where('m.create_time','LIKE','%'.$date.'%');
        }
        if(is_array($id)){
            $res->whereIn('me.recommend',$id);
            $result = $res->get();
        }else{
            $res->where('me.recommend',$id);
            $result = $res->first();
        }
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    /* 通过ID获取客户详细数据 */
    public function getMemberDetailByID($id)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.id','m.status','m.create_time','m.name','m.mobile','m.email','m.tencent_status','m.tencent_discount','me.*','ml.name as level_name','ml.discount','is_vip')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','me.level')
            ->leftJoin('admin_users as au','au.id','=','me.recommend')
            ->where('m.id',$id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $tmp = json_decode($res['certify_pic'],true);
        $res['idcard_front_side'] = isset($tmp['idcard_front_side']) ? $tmp['idcard_front_side'] : '';
        $res['idcard_back_side'] = isset($tmp['idcard_back_side']) ? $tmp['idcard_back_side'] : '';
        $res['enterprise_pic'] = isset($tmp['enterprise_pic']) ? $tmp['enterprise_pic'] : '';
        unset($res['certify_pic']);
        return $res;
    }

    /* 通过ID获取客户详细数据 */
    public function getMemberDetailByOpenid($open_id)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.id','m.status','m.create_time','m.name','m.mobile','m.email','me.*','ml.name as level_name')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','me.level')
            ->leftJoin('admin_users as au','au.id','=','me.recommend')
            ->where('m.openid',$open_id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $tmp = json_decode($res['certify_pic'],true);
        $res['idcard_front_side'] = isset($tmp['idcard_front_side']) ? $tmp['idcard_front_side'] : '';
        $res['idcard_back_side'] = isset($tmp['idcard_back_side']) ? $tmp['idcard_back_side'] : '';
        $res['enterprise_pic'] = isset($tmp['enterprise_pic']) ? $tmp['enterprise_pic'] : '';
        unset($res['certify_pic']);
        return $res;
    }

    /* 带筛选条件获取客户详情 */
    public function getMemberDetailWithFilter($filter_options)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.id','m.mobile','m.email','m.openid','me.*','au.name as auname','me.source as source_name','au.id as admin_user_id')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('admin_users as au','me.recommend','=','au.id');
        if(isset($filter_options['id']) && $filter_options['id']!=''){
            $res->orwhere('au.id','=',$filter_options['id']);
        }
        if(isset($filter_options['mobile']) && $filter_options['mobile']!=''){
            $res->orwhere('m.mobile','=',$filter_options['mobile']);
        }
        if(isset($filter_options['qq']) && $filter_options['qq']!=''){
            $res->orwhere('m.qq','=',$filter_options['qq']);
        }
        if(isset($filter_options['wechat']) && $filter_options['wechat']!=''){
            $res->orwhere('m.wechat','=',$filter_options['wechat']);
        }
        if(isset($filter_options['email']) && $filter_options['email']!=''){
            $res->orwhere('m.email','=',$filter_options['email']);
        }
        $res = $res->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过手机号获取客户 */
    public function getMemberByMobile($mobile,$id=null)
    {
        $res = DB::table($this->table_name)->where('mobile',$mobile);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $res = $res->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过邮箱获取客户 */
    public function getMemberByEmail($email,$id=null)
    {
        $res = DB::table($this->table_name)->where('email',$email);
        if($id!=null){
            $res->where('id','<>',$id);
        }
        $res = $res->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function validMemberRepeat($fields,$id=null)
    {
        $return_data = [
            'is_repeat' => 0,
            'returnData' => '',
            'data' => ''
        ];
        foreach ($fields as $key=>$value){
            if($key=='mobile'){
                $res = $this->getMemberByMobile($value,$id);
                if(is_array($res)){
                    $return_data['is_repeat'] = 1;
                    $return_data['returnData'] = ErrorCode::$admin_enum['mobile_exist'];
                }
                $return_data['data'] = $res;
                return $return_data;
            }
            if($key=='email'){
                $res = $this->getMemberByEmail($value,$id);
                if(is_array($res)){
                    $return_data['is_repeat'] = 1;
                    $return_data['returnData'] = ErrorCode::$admin_enum['email_exist'];
                }
                $return_data['data'] = $res;
                return $return_data;
            }
        }
        return $return_data;
    }

    public function validateMemberAccount($column, $password)
    {
        $res = DB::table($this->table_name)
            ->select('id','password','openid','status');
        $res->where(function ($query) use ($column) {
            $query->where('email', '=', $column)
                ->orWhere('mobile', '=', $column)
                ->orWhere('name', '=', $column);
        });
        $result = $res->first();
        if(!$result){
            return false;
        }
        $result_id = 0;
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        if(!Hash::check($password,$result['password'])){
            return false;
        }
        if ($result['status'] == 0){
            $result_id = -1;
            return $result_id;
        }
        $result_id = $result['id'];
        return $result_id;
    }

    public function getMemberList($fields = ['*'],$filter_options = null)
    {
        $res = DB::table($this->table_name.' as m')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->select($fields);
        if(is_array($filter_options)){
            foreach ($filter_options as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        $result = $res->get();
        if(!$result){
            return array();
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return array();
        }
        return $result;
    }

    public function getMemberListWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('m.*','me.avatar','me.realname','me.company','ml.name as level_name','me.balance','me.cash_coupon','au.name as admin_name')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('member_level as ml','ml.id','=','me.level')
            ->leftJoin('admin_users as au','au.id','=','me.recommend');

        if($fields['admin_id'] != ''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('me.recommend',$user_list);
        }
        if (isset($fields['tencent_status'])){
            if ($fields['tencent_status'] == 1){
                $res->where('m.tencent_status',1);
            }else if ($fields['tencent_status'] == 0){
                $res->where('m.tencent_status',0);
            }
        }

        if($fields['level'] != ''){
            $res->where('me.level','=',$fields['level']);
        }
        if($fields['status'] != ''){
            $res->where('m.status',$fields['status']);
        }
        if($fields['min_balance']!='' && $fields['max_balance']!=''){
            $res->whereBetween('me.balance',[$fields['min_balance'],$fields['max_balance']]);
        }else if($fields['min_balance']!='' && $fields['max_balance']==''){
            $res->where('me.balance','>=',$fields['min_balance']);
        }else if($fields['min_balance']=='' && $fields['max_balance']!=''){
            $res->where('me.balance','=<',$fields['max_balance']);
        }
        if($fields['start_time']!='' && $fields['end_time']!=''){
            $res->whereBetween('m.create_time',[$fields['start_time'],$fields['end_time']]);
        }else if($fields['start_time']!='' && $fields['end_time']==''){
            $res->where('m.create_time','>=',$fields['start_time']);
        }else if($fields['start_time']=='' && $fields['end_time']!=''){
            $res->where('m.create_time','=<',$fields['end_time']);
        }

        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('me.realname', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('me.company', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('m.mobile', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if(in_array($fields['sortName'],['id','create_time','update_time'])){ $fields['sortName'] = 'm.'.$fields['sortName'];}
        if(in_array($fields['sortName'],['cash_coupon','cash_coupon'])){ $fields['sortName'] = 'me.'.$fields['sortName'];}
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 获取带筛选条件的客户列表和资金明细记录 */
    public function getMemberRichListWithFilter($fields)
    {
        $res = DB::table($this->table_name.' as m')
            ->select('wl.*','m.name','m.mobile')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->leftJoin('wallet_logs as wl','m.id','=','wl.uid')
            ->leftJoin('admin_users as au','me.recommend','=','au.id');
        if($fields['admin_id']!=''){
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('me.recommend',$user_list);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('m.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('m.mobile', 'LIKE', '%' .$searchKey . '%');
            });
        }
        if($fields['type']=='balance'){
            if ($fields['status_type'] == 1){
                $res->where('wl.type','=','0');
            }elseif($fields['status_type'] == 2){
                $res->where('wl.type','=','9');
            }else{
                $res->where(function ($query) {
                    $query->where('wl.type','=','0')
                        ->orwhere('wl.type','=','9');
                });
            }
        }else{
            if ($fields['status_type'] == 1){
                $res->where('wl.type','=','1');
            }elseif($fields['status_type'] == 2){
                $res->where('wl.type','=','3');
            }else{
                $res->where('wl.type','!=','0')->where('wl.type','!=','9');
            }
        }
//        if ($fields['status_type'] && $fields['status_type'] != 0){
//            $res->where('wl.type','=',$fields['status_type']);
//        }elseif ($fields['status_type'] && $fields['status_type'] == 0){
//            $res->where('wl.type','=',0);
//        }

        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 添加客户 */
    public function memberInsert($data,$extend_data)
    {
        DB::beginTransaction();
        try {
            $res_id = DB::table($this->table_name)->insertGetId($data);
            if(!$res_id){
                DB::rollback();
                return false;
            }
            $extend_data['member_id'] = $res_id;
            $res = $this->_afterMemberInsert($data,$extend_data);
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    /* 修改客户 */
    public function memberUpdate($id,$data,$extend_data=null)
    {
        $memberData = $this->getMemberByID($id);
        if (isset($data['name']) || isset($data['mobile'])){
            //同步修改业绩订单
            $achievement_res = DB::table('achievement')->where('member_name',$memberData['name'])->where('member_phone',$memberData['mobile'])->first();
        }
        if(!is_array($memberData)){
            return false;
        }
        DB::beginTransaction();
        try {
            if (isset($achievement_res)){ //修改业绩客户信息
                $where['member_name'] = $data['name'];
                $where['member_phone'] = $data['mobile'];
                $where['updated_at'] = Carbon::now()->toDateTimeString();
                DB::table('achievement')->where('id',$achievement_res->id)->update($where);
            }
            $data['update_time'] = Carbon::now();
            $res = DB::table($this->table_name)->where('id',$id)->update($data);
            if(!$res){
                DB::rollback();
                return false;
            }
            if($extend_data==null){
                DB::commit();
                return true;
            }
            $extend_data['update_time'] = Carbon::now();
            $upt_extend_res = DB::table('member_extend')->where('member_id',$id)->update($extend_data);
            if(!$upt_extend_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    /* 修改客户 */
    public function memberExtendUpdate($id,$data)
    {
        $data['update_time'] = Carbon::now();
        $upt_extend_res = DB::table('member_extend')->where('member_id',$id)->update($data);
        if(!$upt_extend_res){
            return false;
        }
        return true;
    }

    /* 删除客户 */
    public function memberDelete($id)
    {
        DB::beginTransaction();
        try {
            $data = DB::table($this->table_name)->where('id',$id)->select("mobile")->first();
            $data = json_decode(json_encode($data),true);
            $res = DB::table($this->table_name)->where('id',$id)->delete();
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::table("customer")->where("mobile", $data["mobile"])->delete();
            $upt_extend_res = DB::table('member_extend')->where('member_id',$id)->delete();
            if(!$upt_extend_res){
                DB::rollback();
                return false;
            }
            $vip_res = DB::table("member_vip")->where("member_id",$id)->first();
            if ($vip_res){
                DB::table("member_vip")->where("member_id",$id)->delete();
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    /* 添加客户后续处理 同步到customer */
    private function _afterMemberInsert($data,$extend_data)
    {
        $res = DB::table('member_extend')->insert($extend_data);
        if(!$res){
            return false;
        }
        $fields = [
            'name' => $data['name'],
            'realname' => $extend_data['realname'],
            'type' => $extend_data['type'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'recommend' => $extend_data['recommend'],
            'addperson' => $extend_data['addperson'],
            'position' => $extend_data['position'],
            'company' => $extend_data['company'],
            'wechat' => $extend_data['wechat'],
            'qq' => $extend_data['qq'],
            'contact_next_time' => '',
            'source' => $extend_data['source'],
            'project' => $extend_data['project'],
            'progress' => '初步接触',
            'status' => $data['status'],
            'remarks' => $extend_data['remarks'],
            'cust_state' => 1
        ];
        $fields['created_at'] = Carbon::now();
        $fields['updated_at'] = Carbon::now();
        $res_id = DB::table('customer')->insertGetId($fields);
        if(!$res_id){
            return false;
        }
        $tmp_data = array(
            'member_id' => $res_id,
            'assign_uid' => $extend_data['recommend'],
            'assign_touid' => $extend_data['recommend'],
            'assign_name' => $extend_data['addperson'],
            'assign_admin' => $extend_data['addperson'],
            'operation_uid' => $extend_data['recommend']
        );
        $assignLogModel = new AssignLog();
        $res = $assignLogModel->assignLogInsert($tmp_data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 激活客户 */
    public function activeCustomer($data,$extend_data)
    {
        DB::beginTransaction();
        try {
            $data['update_time'] = Carbon::now();
            $res_id = DB::table($this->table_name)->insertGetId($data);
            if(!$res_id){
                DB::rollback();
                return false;
            }
            $extend_data['update_time'] = Carbon::now();
            $extend_data['member_id'] = $res_id;
            $res = DB::table('member_extend')->insert($extend_data);
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return false;
        }
    }

    //修改客户指派人
    public function memberExtendUpdaterecommend($list,$id){
        $where['recommend'] = $id;
        $customer_ids = DB::table('customer')->whereIn('id',$list)->get(['mobile']);
        $customer_ids = json_decode(json_encode($customer_ids),true);
        $mobiles = array();
        foreach ($customer_ids as $v){
            $mobiles[] = $v['mobile'];
        }
        $member_ids = DB::table('member')->whereIn('mobile',$mobiles)->get(['id']);
        $member_ids = json_decode(json_encode($member_ids),true);
        $id_all = array();
        foreach ($member_ids as $v){
            $id_all[] = $v['id'];
        }
        DB::table('member_extend')->whereIn('member_id',$id_all)->update($where);

        return true;
    }

    //获取全部人员列表
    public function getAll(){
        $list = DB::table('member_extend')->where('realname','!=','')->select('realname','position','member_id')->get();
//        $list = DB::table('member_extend')->where('realname','!=','')->select('realname','position','member_id')->skip(0)->take(5000)->get();
        $list = json_decode(json_encode($list),true);
        foreach ($list as $k=>&$v){
            if(strstr($v['realname'],'·')){
                $count=strpos($v['realname'],"·");
                $v['realname']=substr_replace($v['realname'],"",$count,2);
            }
            $name = str_replace(" ",'',$v['realname']);
//            $v['realname'] = preg_replace('# #', '', $v['realname']);
            $v['realname'] = preg_replace('/\p{Thai}/u','',$name);
        }
        return $list;
    }

    //验证后台管理跳转到代理商端
    public static function SetTokengetUserInfo($data){
        $userSessionModel = new MemberSession();
        $res = $userSessionModel->setSession($data);
        return $res;
    }

    //获取客户人数
    public function statisticsMemberNumbers($fields){
        $res = DB::table($this->table_name.' as m')
            ->select('me.recommend','m.id','me.source')
            ->leftJoin('member_extend as me','m.id','=','me.member_id');
        if ($fields['start_time'] != '' && $fields['end_time'] != ''){
            $res->whereBetween('create_time',[$fields['start_time'],$fields['end_time']]);
        }
        if ($fields['ids'] != ''){
            $res->whereIn('me.recommend',$fields['ids']);
        }
        $tesult = $res->get();
        $tesult = json_decode(json_encode($tesult),true);
        return $tesult;
    }

    //获取全部客户数
    public function getMemberTotal($type = ''){
        $res = DB::table($this->table_name.' as m')
            ->select('me.source',DB::raw('SUM(1) AS total_number'))
            ->leftJoin('member_extend as me','m.id','=','me.member_id');
        if ($type != ''){
            $res->groupBy('me.source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($type != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }

    //获取部门下的全部客户数
    public function getCompanyMemberTotal($fields){
        $res = DB::table($this->table_name.' as m')
            ->select('me.source',DB::raw('SUM(1) AS total_number'))
            ->leftJoin('member_extend as me','m.id','=','me.member_id')
            ->whereIn('me.recommend',$fields['ids']);
        if ($fields['type'] != ''){
            $res->groupBy('me.source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($fields['type'] != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }

    //根据时间段获取客户数量
    public function getMemberActivationTimeTotal($fields){
        $res = DB::table($this->table_name.' as m')
            ->leftJoin('member_extend as me','m.id','=','me.member_id')
            ->select('me.source',DB::raw('SUM(1) AS total_number'))
            //->where('cust_state',1)
            ->whereBetween('m.create_time',[$fields['start_time'],$fields['end_time']])
            ->whereIn('me.recommend',$fields['ids']);
        if ($fields['type'] != ''){
            $res->groupBy('me.source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($fields['type'] != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }
}

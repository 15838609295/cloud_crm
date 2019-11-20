<?php

namespace App\Models\User;

use App\Http\Config\ErrorCode;
use App\Library\Tools;
use App\Models\Admin\AdminBonusLog;
use App\Models\Admin\Configs;
use App\Models\Auth\AuthRole;
use App\Models\Member\MemberBase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserBase extends Model
{
    protected $table_name='admin_users';

    private $admin_detail_column = ['au.id','au.name','au.ach_status','au.sex','au.mobile','au.email','au.wechat_id','au.position','au.hiredate','au.status','au.power','aru.role_id','au.company_id','formal_time'];

    private $admin_list_column = ['au.id','au.name','au.sex','au.work_status','au.mobile','au.email','au.bonus','au.sale_bonus','au.hiredate','au.status','c.name as company_name'];

    public function getUserBasicBySessionID($session_id)
    {
        $userSessionModel = new UserSession();
        $admin_info = $userSessionModel->getSession($session_id);
        if(!$admin_info){
            return false;
        }
        if ($admin_info['login_ip'] == Tools::get_client_ip()){
            $res = DB::table($this->table_name.' as au')
                ->select('au.*','c.name as company_name')
                ->leftJoin('company as c','au.company_id','=','c.id')
                ->where('au.id',$admin_info['admin_id'])
                ->first();
            if(!$res){
                return false;
            }
            $res = json_decode(json_encode($res),true);
            if(!is_array($res) || count($res)<1){
                return false;
            }
            return $res;
        }else{
            return false;
        }

    }

    public function validateAdminAccount($column,$password)
    {
        $res = DB::table($this->table_name)->select('id','password', 'status');
        $res->where(function ($query) use ($column) {
            $query->where('email', '=', $column)
                ->orWhere('mobile', '=', $column)
                ->orWhere('name', '=', $column);
        });
        $result = $res->get();
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        $admin_id = 0;
        foreach ($result as $key=>$value){
            if(!Hash::check($password,$value['password'])){
                continue;
            }else{
                if($value["status"] != '0'){
                    return -1;
                }
            }
            $admin_id = $value['id'];
        }
        return $admin_id;
    }

    public function getAdminBasic($id)
    {//  ->select('au.id','au.name','au.work_time','au.hiredate','au.work_status','au.wechat_pic','c.name as company_name')
        $res = DB::table($this->table_name.' as au')
            ->select(['au.id','au.name','au.wechat_pic','au.sex','au.work_time','au.mobile','au.bonus','au.position','au.work_status','au.last_login_time','au.email','au.created_at','au.wechat_id','au.openid','c.name as company_name'])
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->where('au.id',$id)
            ->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        $now_time = date('Y-m-d');
        if(strpos($res['work_time'],$now_time)===false){
            if($res["work_status"] == '1'){
                DB::table($this->table_name)->where("id", $res["id"])->update(["work_status" => 0]);
                $res["work_status"] = 0;
            }
        }
        return $res;
    }

    public function getAdminByCompanyId(){
        $company_ids = DB::table('company')->select('id','name')->get();
        $company_ids = json_decode(json_encode($company_ids),true);
        $admin_ids = DB::table($this->table_name)->select('id','company_id')->where('status',0)->where('ach_status',0)->get();
        $admin_ids = json_decode(json_encode($admin_ids),true);
        foreach ($company_ids as &$v){
            foreach ($admin_ids as $values){
                if ($values['company_id'] == $v['id']){
                    unset($values['company_id']);
                    $v['uid'][] = $values['id'];
                }
            }
        }
        $branchs_ids = DB::table('branchs')->select('id','branch_name as name')->get();
        $branchs_ids = json_decode(json_encode($branchs_ids),true);
        $admin_userids = DB::table('user_branch as ub')
            ->select('ub.user_id as id','ub.branch_id')
            ->leftjoin($this->table_name.' as au','ub.user_id','=','au.id')
            ->where('au.status',0)
            ->where('au.ach_status',0)
            ->get();
        $admin_userids = json_decode(json_encode($admin_userids),true);
        foreach ($branchs_ids as &$v){
            foreach ($admin_userids as $info){
                if ($v['id'] == $info['branch_id']){
                    unset($values['branch_id']);
                    $v['uid'][] = $info['id'];
                }
            }
        }
        $res['company'] = $company_ids;
        $res['branchs'] = $branchs_ids;
        return $res;

    }

    public function getAdminByEmail($email,$id=null)
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

    public function getAdminByPhone($phone,$id=null)
    {
        $res = DB::table($this->table_name)->where('mobile',$phone);
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

    public function getAdminByID($id)
    {
        $res = DB::table($this->table_name);
        if(is_array($id)){
            $result = $res->whereIn('id',$id)->get();
        }else{
            $result = $res->where('id',$id)->first();
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

    public function getAdminDetailByID($id)
    {
        $res = DB::table($this->table_name.' as au')
            ->select($this->admin_detail_column)
            ->leftJoin('admin_role_user as aru','aru.user_id','=','au.id')
            ->where('au.id',$id)
            ->first();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        $userBranchModel = new UserBranch();
        $branch = $userBranchModel->getUserBranchList($id);
        $res['user_branch'] = [];
        foreach ($branch as $key=>$value){
            $res['user_branch'][] = $value['branch_id'];
        }
        if($res['power']==''){
            $res['power'] = [];
        }else{
            $res['power'] = explode(',',$res['power']);
        }
        foreach ($res['power'] as $key=>$value){
            $res['power'][$key] = (int)$value;
        }
        $res['power'] = array_values($res['power']);
        return $res;
    }

    public function getAdminUserList($fields = ['*'],$filter_options = null)
    {
        $res = DB::table('admin_users as au')
            ->select($fields)
            ->leftJoin('admin_users_extend as aue','aue.admin_id','=','au.id')
            ->where('au.status','=',0)
            ->where('aue.job_status','=',1);
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
        $res = json_decode(json_encode($result),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    /* 通过筛选条件获取管理员列表 */
    public function getAdminUserListWithFilter($fields)
    {
        $branch_id = $fields['branch_id'];
        $company_id = $fields['company_id'];
        $status = $fields['status'];
        $searchKey = $fields['searchKey'];
        $sub = DB::table('user_branch as ub')
            ->select('ub.branch_id','b.branch_name','ub.user_id')
            ->leftJoin('branchs as b','ub.branch_id','=','b.id');
        $res = DB::table('admin_users as au')
            ->select('au.id','au.name','au.sex','au.work_status','au.mobile','au.email','au.bonus','au.sale_bonus',
                'au.hiredate','au.status','c.name as company_name',DB::raw(' max(ads.create_time) as create_time'))
            ->leftJoin(DB::raw("({$sub->toSql()}) as cb"),'au.id','=','cb.user_id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->leftJoin('admin_session as ads','ads.admin_id','=','au.id')
            ->when($branch_id,function ($query) use ($branch_id){
                return $query->where('cb.branch_id',$branch_id);
            })
            ->when($company_id,function ($query) use ($company_id){
                return $query->where('au.company_id',$company_id);
            })
            ->when($searchKey,function ($query) use ($searchKey){
                return $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('au.mobile', 'LIKE', '%' . $searchKey . '%');
            })->groupBy('au.id');
        $params = [];
        if($branch_id!=''){ $params['branch_id'] = $branch_id; }
        if($company_id!=''){ $params['company_id'] = $company_id; }
        if($status!=''){ $params['status'] = $status; }
        if($searchKey!=''){
            $params['searchKey'] = array(
                'name' => $searchKey,
                'mobile' => $searchKey
            );
        }
        $data['total'] = Tools::total($res,$params);
        if (strlen($status)){
            $res->where('au.status',$status);
        }
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = [];
        if(!$result){
            return $data;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return $data;
        }
        $membersModel = new MemberBase();
        $fields = ['me.recommend'];
        $memberData = $membersModel->getMemberList($fields);
        foreach($result as $k=>&$v){
            $count = 0;
            foreach ($memberData as $key=>$value){
                if((int)$value['recommend']==(int)$v['id']){
                    $count++;
                }
            }
            if (!$v['create_time']){
                $v['create_time']= '无记录';
            }else{
                $v['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
            $result[$k]['total_customer'] = $count;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 业绩排行榜管理员筛选 */
    public function getAdminListToRank($fields)
    {
        $branch_id = $fields['branch_id'];
        $company_id = $fields['company_id'];
        $admin_id = $fields['admin_id'];
        $searchKey = $fields['searchKey'];
        $sub = DB::table('user_branch as ub')
            ->select('ub.branch_id','b.branch_name','ub.user_id')
            ->leftJoin('branchs as b','ub.branch_id','=','b.id');
        $res = DB::table('admin_users as au')
            ->select('au.id','au.name','au.wechat_pic','c.name as company_name')
            ->leftJoin(DB::raw("({$sub->toSql()}) as cb"),'au.id','=','cb.user_id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->where('au.status',0)
            ->where('au.ach_status','=',0)
            ->when($branch_id,function ($query) use ($branch_id){
                return $query->where('cb.branch_id',$branch_id);
            })
            ->when($company_id,function ($query) use ($company_id){
                return $query->where('au.company_id',$company_id);
            })
            ->when($admin_id,function ($query) use ($admin_id){
                return $query->where('au.id',$admin_id);
            })
            ->when($searchKey,function ($query) use ($searchKey){
                return $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('au.mobile', 'LIKE', '%' . $searchKey . '%');
            })->groupBy('au.id')->get();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    public function getAdminUserListWithPower($admin_id, $fields = ['*'], $filter_options = null)
    {
        $res = DB::table('admin_users')->select($fields);
        if(is_array($filter_options)){
            foreach ($filter_options as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        $sub_user_list = $this->getAdminSubuser($admin_id);
        $res->whereIn('id',$sub_user_list);
        $result = $res->get();
        $res = json_decode(json_encode($result),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //获取用户权限
    public function getAdminPower($admin_id)
    {
        $data = DB::table('admin_users')->select('power')->where('id',$admin_id)->first();
        $data = json_decode(json_encode($data),true);
        if(trim($data['power'])==''){
            return $data['power'];
        }
        $power_list = explode(',',$data['power']);
        return $power_list;
    }

    //获取用户权限下的子用户
    public function getAdminSubuser($admin_id)
    {
        $power = $this->getAdminPower($admin_id);
        if(!is_array($power)){
            return array($admin_id);
        }
        //判断有没有全部的权限
        if (in_array(1,$power)){
            $res = DB::table('admin_users')->select('id')->get();
            $user_id_list = [];
            $res = json_decode(json_encode($res),true);
            foreach ($res as $v){
                $user_id_list[] = $v['id'];
            }
            return $user_id_list;
        }
        //power权限下的所有用户
        $user_from_power = DB::table('user_branch')->whereIn('branch_id',$power)->get();
        //用户团队下的用户
        $user_from_branch = DB::table('user_branch as a')
            ->select('b.user_id','b.branch_id')
            ->leftJoin('user_branch as b','a.branch_id','=','b.branch_id')
            ->where('a.user_id',$admin_id)
            ->whereIn('b.branch_id',$power)
            ->get();

        $user_from_power = json_decode(json_encode($user_from_power),true);
        $user_from_branch = json_decode(json_encode($user_from_branch),true);
        $user_list = array_merge($user_from_power,$user_from_branch);
        array_unique($user_list, SORT_REGULAR);
        $tmp[]=$admin_id;
        foreach ($user_list as $key => $value){
            if(in_array($value['user_id'],$tmp)){
                continue;
            }
            $tmp[] = $value['user_id'];
        }
        if ($admin_id != 1){
           foreach ($tmp as $k=>$v){
               if ($v == 1){
                   unset($tmp[$k]);
               }
           }
        }
        return $tmp;
    }

    public function getAdminUserListFromHr($fields=['*'])
    {
        $res = DB::table($this->table_name.' as au')
            ->select('au.*','c.name as company_name')
            //->leftJoin('admin_users_extend as aue','au.id','=','aue.admin_id')
            ->leftJoin('company as c','au.company_id','=','c.id');
        if($fields['company_id']!=''){$res->where('au.company_id',$fields['company_id']);}
        if($fields['status']!=''){$res->where('au.status',$fields['status']);}
        if($fields['nexttime']!=''){
            $start_time = substr(trim($fields['nexttime']),0,10).' 00:00:00';
            $end_time = substr(trim($fields['nexttime']),13,10).' 23:59:59';
            $res->whereBetween('au.hiredate',[$start_time,$end_time]);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('au.email', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $tmp_res = $res;
        $data = array(
            'total' => $tmp_res->count(),
            'rows' => $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get()
        );
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        return $data;
    }

    public function getRankingList($fields)
    {
        $query = DB::table($this->table_name.' as au')
            ->select('au.id','au.wechat_pic','au.name',DB::raw('sum(a.goods_money) as total_money'),'c.name as company_name')
            ->leftJoin('achievement as a','a.admin_users_id','=','au.id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->where('a.status','=',1)
            ->where('a.ach_state','=',0)
            ->where('au.ach_status','=',0)
            ->where('au.status','=',0)
            ->where('a.buy_time','LIKE','%'.$fields['date'].'%');
        if(isset($fields['company_id'])&&trim($fields['company_id'])!=''){

        }
        if(isset($fields['admin_id'])){
            $query->where('au.id','=',$fields['admin_id']);
        }
        $res = $query->orderBy('total_money','desc')
            ->groupBy('au.id')
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

    public function getAdminList()
    {
        $res = DB::table($this->table_name.' as au')
            ->select('au.id','au.name','au.work_time','au.hiredate','au.work_status','au.wechat_pic','c.name as company_name')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->orderBy('au.work_time','desc')
            ->orderBy('au.work_status','asc')
            ->where('au.status','=',0)
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

    /* 获取管理员详情 */
    public function getAdminUserDetail($id)
    {
        $res = DB::table($this->table_name.' as au')
            ->leftJoin('admin_users_extend as aue','au.id','=','aue.admin_id')
            ->where('au.id',$id)
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

    public function validAdminUserRepeat($fields,$id=null)
    {
        $return_data = [
            'is_repeat' => 0,
            'returnData' => '',
            'data' => ''
        ];
        foreach ($fields as $key=>$value){
            if($key=='mobile'){
                $res = $this->getAdminByPhone($value,$id);
                if(is_array($res)){
                    $return_data['is_repeat'] = 1;
                    $return_data['returnData'] = ErrorCode::$admin_enum['mobile_exist'];
                }
                $return_data['data'] = $res;
//                return $return_data;
            }
            if($key=='email'){
                $res = $this->getAdminByEmail($value,$id);
                if(is_array($res)){
                    $return_data['is_repeat'] = 1;
                    $return_data['returnData'] = ErrorCode::$admin_enum['email_exist'];
                }
                $return_data['data'] = $res;
//                return $return_data;
            }
        }
        return $return_data;
    }

    public function adminUserInsert($data,$extend_data=null)
    {
        DB::beginTransaction();
        try {
            $res_id = DB::table($this->table_name)->insertGetId($data);
            if(!$res_id){
                DB::rollback();
                return false;
            }
            $res = DB::table('admin_users_extend')->insert(['admin_id'=>$res_id]);
            if(!$res){
                DB::rollback();
                return false;
            }
            DB::commit();
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            return false;
        }
        if(!is_array($extend_data)){
            return $res_id;
        }
        if($extend_data['role_id']!=''){
            $roleModel = new AuthRole();
            $roleModel->adminRoleInsert($extend_data['role_id'],$res_id);
        }
        if(count($extend_data['branch'])>0){
            $roleModel = new UserBranch();
            $data = [];
            if ($extend_data['branch'] != ''){
                foreach ($extend_data['branch'] as $value){
                    $data[] = ['branch_id'=>$value,'user_id'=>$res_id];
                }
                $roleModel->userBranchInsert($data);
            }
        }
        return $res_id;
    }

    public function adminUserUpdate($id,$data,$extend_data=null)
    {
        $res = DB::table($this->table_name)->where('id',$id)->update($data);
        if(!$res && $res !== 0){
            return false;
        }
        if(!is_array($extend_data)){
            return true;
        }
        if(isset($extend_data['role_id']) && $extend_data['role_id']!=''){
            $roleModel = new AuthRole();
            $roleModel->adminRoleUpdate($extend_data['role_id'],$id);
        }
        if($extend_data['branch'] != ''){
//        if(isset($extend_data['branch']) && count($extend_data['branch'])>0){
            $roleModel = new UserBranch();
            $data = [];
            foreach ($extend_data['branch'] as $value){
                $data[] = ['branch_id'=>$value,'user_id'=>$id];
            }
            $roleModel->userBranchUpdate($id,$data);
        }
        return true;
    }

    /* 员工资料修改 */
    public function adminUserExtendUpdate($id,$data)
    {
        $res = DB::table('admin_users_extend')->where('admin_id',$id)->update($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 管理员删除 */
    public function adminUserDelete($id)
    {
        DB::beginTransaction();
        try {
            $del_res = DB::table('admin_users')->where('id',$id)->delete();
            $del_extend_res = DB::table('admin_users_extend')->where('admin_id',$id)->delete();
            if(!$del_res || !$del_extend_res){
                DB::rollback();
                return false;
            }
            DB::commit();
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback(); //回滚事务
            return false;
        }
        DB::table('admin_role_user')->where('user_id',$id)->delete();
        DB::table('user_branch')->where('user_id',$id)->delete();
        DB::table('member_extend')->where('recommend',$id)->update(['recommend'=>1]);
        return true;
    }

    //获取用户密码
    public function getAdminUserPassword($id){
        $res = DB::table($this->table_name)->where('id',$id)->select('password')->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res;
    }

    //增加员工提成
    public function addBonus($id,$money,$data){
        $res = DB::table($this->table_name)->where('id',$id)->select('bonus')->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $where['bonus'] = $res['bonus'] + $money;
        $result = DB::table($this->table_name)->where('id',$id)->update($where);
        //业绩流水表
        $data_ins['admin_users_id'] = $id;
        $data_ins['type'] = 1;
        $data_ins['money'] = $money;
        $data_ins['cur_bonus'] = $where['bonus'];
        $data_ins['member_name'] = $data['member_name'];
        $data_ins['member_phone'] = $data['member_phone'];
        $data_ins['goods_name'] = $data['goods_name'];
        $data_ins['goods_money'] = $data['goods_money'];
        $data_ins['order_number'] = $data['order_number'];
        $data_ins['remarks'] = $data['remarks'];
        $data_ins['submitter'] = $data['admin_name'];
        $data_ins['auditor'] = $data['verify'];
        $adminBonusModel = new AdminBonusLog();
        $adminBonusModel->adminBonusLogInsert($data_ins);
        if(!$result){
            return false;
        }
        return true;
    }

    //增加奖金
    public function addSaleBonus($id,$money,$data){
        $res = DB::table($this->table_name)->where('id',$id)->select('sale_bonus')->first();
        if(!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $where['sale_bonus'] = $res['sale_bonus'] + $money;
        $result = DB::table($this->table_name)->where('id',$id)->update($where);
        //业绩流水表
        $data_ins['admin_users_id'] = $id;
        $data_ins['type'] = 4;
        $data_ins['money'] = $money;
        $data_ins['cur_bonus'] = $where['sale_bonus'];
        $data_ins['member_name'] = $data['member_name'];
        $data_ins['member_phone'] = $data['member_phone'];
        $data_ins['goods_name'] = $data['goods_name'];
        $data_ins['goods_money'] = $data['goods_money'];
        $data_ins['order_number'] = $data['order_number'];
        $data_ins['remarks'] = $data['remarks'];
        $data_ins['submitter'] = $data['admin_name'];
        $data_ins['auditor'] = $data['verify'];
        $adminBonusModel = new AdminBonusLog();
        $adminBonusModel->adminBonusLogInsert($data_ins);
        if(!$result){
            return false;
        }
        return true;
    }

    //员工资金操作
    public function capitalOperation($data){
        $user_res = DB::table('admin_users')->where('id',$data['uid'])->select('bonus','sale_bonus')->first();
        $user_res = json_decode(json_encode($user_res),true);
        $admin_log['admin_users_id'] = $data['uid'];
        $admin_log['remarks'] = $data['remarks'];
        $admin_log['created_at'] = Carbon::now()->toDateTimeString();
        if ($data['type'] == 1){   //奖金操作
            $admin_log['cur_bonus'] = $user_res['sale_bonus'];
            if ($data['dotype'] == 1){   //增加
                $admin_log['money'] = $data['money'];
                $user_res['sale_bonus'] = $user_res['sale_bonus'] + $data['money'];
                $admin_log['type'] = 8;
            }elseif($data['dotype'] == 0){  //减少
                if ($data['money'] > $user_res['sale_bonus']){  //操作金额大于余额
                    return -1;
                }
                $user_res['sale_bonus'] = $user_res['sale_bonus'] - $data['money'];
                $admin_log['money'] = '-'.$data['money'];
                $admin_log['type'] = 9;
            }
        }elseif($data['type'] == 0){  //提成操作
            $admin_log['cur_bonus'] = $user_res['bonus'];
            if ($data['dotype'] == 1){   //增加
                $admin_log['money'] = $data['money'];
                $user_res['bonus'] = $user_res['bonus'] + $data['money'];
                $admin_log['type'] = 6;
            }elseif($data['dotype'] == 0){  //减少
                if ($data['money'] > $user_res['bonus']){  //操作金额大于余额
                    return -1;
                }
                $user_res['bonus'] = $user_res['bonus'] - $data['money'];
                $admin_log['money'] = '-'.$data['money'];
                $admin_log['type'] = 7;
            }
        }
        $res = DB::table('admin_users')->where('id',$data['uid'])->update($user_res);
        if (!$res){
            return false;
        }
        $admin_log['submitter'] = $data['admin_name'];
        $adminBonusModel = new AdminBonusLog();
        $adminBonusModel->adminBonusLogInsert($admin_log);
        return true;

    }

    //根据部门id获取用户id
    public function getCompanyIdList($id){
        $res = DB::table($this->table_name.' as au')
            ->leftJoin('admin_users_extend as aue','au.id','=','aue.admin_id')
            ->where('company_id',$id)
            ->where('au.status',0)
            ->where('au.ach_status',0)
            ->where('aue.job_status',1)
            ->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $ids = [];
        foreach ($res as $v){
            $ids[] = $v['id'];
        }
        return $ids;
    }























}

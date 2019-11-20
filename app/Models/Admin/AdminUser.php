<?php

namespace App\Models\Admin;
use App\Library\Tools;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUser extends Authenticatable
{
    use Notifiable;
    protected $table='admin_users';
    protected $table_name='admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    //用户角色
    public function roles()
    {
        return $this->belongsToMany(Role::class,'admin_role_user','user_id','role_id');
    }

    // 判断用户是否具有某个角色
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return !!$role->intersect($this->roles)->count();
    }

    // 判断用户是否具有某权限
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name',$permission)->first();
            if (!$permission) return false;
        }

        return $this->hasRole($permission->roles);
    }

    // 给用户分配角色
    public function assignRole($role)
    {
        return $this->roles()->save($role);
    }

    //角色整体添加与修改
    public function giveRoleTo(array $RoleId){
        $this->roles()->detach();
        $roles=Role::whereIn('id',$RoleId)->get();
        foreach ($roles as $v){
            $this->assignRole($v);
        }
        return true;
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
        $res = DB::table($this->table);
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

    public function getAdminUserList($fields = ['*'],$filter_options = null)
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
        $total = DB::table($this->table.' as au')
            ->select(DB::raw('count(1) as total'))
            ->leftJoin('user_branch as ub','ub.user_id','=','au.id')
            ->leftJoin('branchs as b','b.id','=','ub.branch_id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->groupBy('au.id');
        $res = DB::table($this->table.' as au')
            ->select('au.*','ub.branch_id','b.branch_name','c.name as company_name')
            ->leftJoin('user_branch as ub','ub.user_id','=','au.id')
            ->leftJoin('branchs as b','b.id','=','ub.branch_id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->groupBy('au.id');
        if($fields['branch_id'] != ""){
            $total->where('ub.branch_id','=',$fields['branch_id']);
            $res->where('ub.branch_id','=',$fields['branch_id']);
        }
        if($fields['company_id'] != ""){
            $total->where('au.company_id','=',$fields['company_id']);
            $res->where('au.company_id','=',$fields['company_id']);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('au.mobile', 'like', '%' . $searchKey . '%');
            });
            $res->where(function ($query) use ($searchKey) {
                $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('au.mobile', 'like', '%' . $searchKey . '%');
            });
        }
        $data['total'] = count(json_decode(json_encode($total->get()),true));
        $data['rows'] = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        $data['rows'] = json_decode(json_encode($data['rows']),true);
        $membersModel = new Members();
        $fields = ['recommend'];
        $memberData = $membersModel->getMemberList($fields);
        foreach($data['rows'] as $k=>$v){
            $count = 0;
            foreach ($memberData as $key=>$value){
                if((int)$value['recommend']==(int)$v['id']){
                    $count++;
                }
            }
            $data['rows'][$k]['total_customer'] = $count;
        }
        return $data;
    }

    public function validateAdminAccount($column,$password)
    {
        $res = DB::table($this->table_name)->select('id','password');
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
            }
            $admin_id = $value['id'];
        }
        return $admin_id;
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
        return $tmp;
    }

    public function getAdminUserListFromHr($fields=['*'])
    {
        $res = DB::table($this->table.' as au')
            ->select('au.id', 'au.name','au.email','au.mobile','au.sex','au.hiredate','au.last_login_time','c.name as company_name', "aue.job_status",'au.formal_time')
            ->leftJoin('admin_users_extend as aue','au.id','=','aue.admin_id')
            ->leftJoin('company as c','au.company_id','=','c.id');
        if($fields['company_id']!=''){$res->where('au.company_id',$fields['company_id']);}
        if($fields['status']!=''){$res->where('au.status',$fields['status']);}
        if($fields['job_status']!=''){
            $res->where('aue.job_status',$fields['job_status']);
        }
        if($fields['hireDate']!=''){
            $start_time = substr(trim($fields['hireDate']),0,10).' 00:00:00';
            $end_time = substr(trim($fields['hireDate']),13,10).' 23:59:59';
            $res->whereBetween('au.hiredate',[$start_time,$end_time]);
        }
        if($fields['searchKey']!=''){
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('au.name', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('au.email', 'LIKE', '%' . $searchKey . '%');
            });
        }
        $fields['sortName'] = $fields['sortName'] ? $fields['sortName'] : "au.created_at";
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
        $sub = DB::table('user_branch as ub')
            ->select('ub.branch_id','b.branch_name','ub.user_id')
            ->leftJoin('branchs as b','ub.branch_id','=','b.id');
        $res = DB::table('admin_users as au')
            ->select('au.id','au.name','au.wechat_pic','c.name as company_name')
            ->leftJoin(DB::raw("({$sub->toSql()}) as cb"),'au.id','=','cb.user_id')
            ->leftJoin('company as c','c.id','=','au.company_id')
            ->where('au.status',0)
            ->where('au.ach_status','=',0);
         if(isset($fields['admin_id'])){
             $res->where('au.id',$fields['admin_id']);
         }
         if(isset($fields['admin_id'])){
             $res->where('au.id',$fields['admin_id']);
         }
        $data = $res->groupBy('au.id')->get();

        if(!$data){
            return false;
        }
        $data = json_decode(json_encode($data),true);
        if(!is_array($data) || count($data)<1){
            return false;
        }
        $tmp_list = [];
        foreach ($data as $item){
            $tmp_list[] = $item['id'];
        }
        $ach_data = DB::table("achievement")
            ->select('admin_users_id',DB::raw('sum(goods_money) as total_money'))
            ->whereIn('admin_users_id',$tmp_list)
            ->where('buy_time','LIKE','%'.$fields['date'].'%')
            ->where('status','=',1)
            ->where('ach_state','=',0)
            ->groupBy('admin_users_id')
            ->get();
        if(!$ach_data){
            return array();
        }
        $ach_data = json_decode(json_encode($ach_data),true);
        if(!is_array($ach_data) || count($ach_data)<1){
            return array();
        }
        $tmp = [];
        foreach ($ach_data as $key=>$value)
        {
            $tmp[$value['admin_users_id']] = $value['total_money'];
        }
        foreach ($data as $key=>$value){
            $data[$key]['total_money'] = 0;
            if(isset($tmp[$value['id']])){
                $data[$key]['total_money'] = $tmp[$value['id']];
            }
        }
        $tool = new Tools();
        $data = $tool->array_sort($data,'total_money','SORT_DESC');

        return array_values($data);
    }

    public function getAdminUserDetail($id)
    {//0 未婚/1已婚/2 离异/3 丧偶
        $res = DB::table($this->table.' as au')
            ->select("au.id","au.name","au.mobile","au.sex","au.company_id","au.position","au.wechat_pic",
                "aue.birth_date","aue.nation","aue.age","aue.idcard_no","aue.highest_edu","aue.degree","aue.marital_status","aue.stature","aue.political_affiliation",
                "aue.binduser_name","aue.binduser_work_address","aue.binduser_phone","aue.education_history","aue.work_history","aue.foreigner_language","aue.foreigner_language_status","aue.computer_science_level","aue.skill_title","aue.form_pic",
                "aue.cantonese_skill_status","aue.certificate_info","aue.lastest_employer_name","aue.lastest_employer_job","aue.lastest_employer_phone","aue.family_info","aue.acquaintance_name","aue.acquaintance_department","aue.acquaintance_job",
                "aue.native_place","aue.reg_permanent_place","aue.reg_permanent_type","aue.technical_title","aue.social_security_no","aue.public_reserve_funds","aue.current_address","aue.home_address","aue.tel_phone","aue.acquaintance_relation",
                "aue.job_status", "aue.real_avatar", "aue.identity_card_pic", "aue.certificate_pic", "aue.examination_pic", "aue.other_pic","aue.goods_collection",'au.formal_time','au.hiredate')
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

    public function adminUserInsert($data)
    {
        $res_id = DB::table($this->table)->insertGetId($data);
        if(!$res_id){
            return false;
        }
        $res = DB::table('admin_users_extend')->insert(['admin_id'=>$res_id]);
        if(!$res){
            return false;
        }
        return $res_id;
    }

    public function adminUserUpdate($id,$data)
    {
        $res = DB::table($this->table)->where('id',$id)->update($data);
        if(!$res){
            return false;
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


    public function adminUserDelete($id)
    {
        $res = DB::table('admin_users')->where('id',$id)->delete();
        $res = DB::table('admin_users_extend')->where('admin_id',$id)->delete();
        $roleModel = new Role();
        $res = $roleModel->deleteAdminRole($id);
        if(!$res){
            return false;
        }
        return true;
    }

    //查询总余额
    public function getTotalBonus($id=null){
        $res = DB::table($this->table_name)->select(DB::raw('sum(bonus) as total_bonus'));
        $res1 = DB::table($this->table_name)->select(DB::raw('sum(sale_bonus) as total_sale_bonus'));
        if (isset($id)){
            $res->where('id',$id);
            $res1->where('id',$id);
        }
        $res = $res->get();
        $res1 = $res1->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        $res1 = json_decode(json_encode($res1),true);
        $data = $res[0]['total_bonus'] + $res1[0]['total_sale_bonus'];
        return $data;
    }
}

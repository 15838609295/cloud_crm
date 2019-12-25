<?php

namespace App\Models\Customer;

use App\Http\Config\ErrorCode;
use App\Models\Admin\AssignLog;
use App\Models\User\UserBase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerBase extends Model
{
    protected $table_name='customer';

    /* 通过ID获取客户基础数据 */
    public function getCustomerByID($id)
    {
        $res = DB::table($this->table_name);
        if(is_array($id)){
            $result = $res->whereIn('id',$id)->get();
        }else{
            $result = $res->where('id',$id)->first();
        }
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        return $result;
    }

    /* 通过ID获取客户详细数据 */
    public function getCustomerDetail($id)
    {
        $result = DB::table($this->table_name)->where('id',$id)->first();
        if(!$result){
            return false;
        }
        $result = json_decode(json_encode($result),true);
        $res = DB::table('member as m')
            ->leftJoin('member_extend as me','me.member_id','=','m.id')
            ->select('me.avatar', "me.realname", "me.addperson", "me.recommend", "me.cash_coupon", "me.balance",
                "m.name", "m.mobile", "m.active_time")
            ->where("m.id", $id)
            ->first();
        $res = json_decode(json_encode($res),true);
        if(!is_array($result) || count($result)<1){
            return false;
        }
        if(is_array($res))
            $result = array_merge($result, $res);
        else{
            $result["avatar"] = $result["cash_coupon"] = $result["balance"] = $result["active_time"] = "";
        }
        return $result;
    }

    public function getOverCustomerCount($id, $date){
        $res = DB::table($this->table_name);
        $res->where("contact_next_time", "<", $date);
        if(is_array($id)){
            $result = $res->whereIn('recommend',$id)->count();
        }else{
            $result = $res->where('recommend',$id)->count();
        }
        return $result;
    }

    /* 通过ID获取客户详细数据 */
    public function getCustomerDetailByID($id)
    {
        $res = DB::table($this->table_name.' as c')
            ->select('c.*','au.name as admin_name')
            ->leftJoin('admin_users as au','au.id','=','c.recommend')
            ->where('c.id',$id)
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

    /* 通过手机号获取客户 */
    public function getCustomerByMobile($mobile,$id=null)
    {
        $res = DB::table($this->table_name.' as c')
            ->leftJoin('admin_users as a','c.recommend','=','a.id')
            ->select('c.mobile','a.name');
        if ($id != ''){
            $res->where('c.id','<>',$id);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $v){
            if ($v['mobile'] == $mobile){
                $data['name'] = $v['name'];
            }
        }
        if ($data){
            return $data;
        }else{
            return false;
        }
    }

    /* 通过QQ号获取客户 */
    public function getCustomerByQq($qq,$id=null)
    {
        $res = DB::table($this->table_name.' as c')
            ->leftJoin('admin_users as a','c.recommend','=','a.id')
            ->select('c.qq','a.name');
        if ($id != ''){
            $res->where('c.id','<>',$id);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $v){
            $m = explode(',',$v['qq']);
            foreach ($m as $k=>$vs){
                $data[$vs] = $v['name'];
            }
        }
       foreach ($qq as $v){
           $check = [$v=>''];
           $c_info = array_intersect_key($data,$check);
           if ($c_info){
               return $c_info;
           }
       }
        return false;
    }

    //通过公司名获取客户信息
    public function getCustomerByCompany($company,$id=null)
    {
        $res = DB::table($this->table_name.' as c')
            ->leftJoin('admin_users as a','c.recommend','=','a.id')
            ->select('c.company','a.name');
        if ($id != ''){
            $res->where('c.id','<>',$id);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $v){
            $m = explode(',',$v['company']);
            foreach ($m as $k=>$vs){
                $data[$vs] = $v['name'];
            }
        }
        foreach ($company as $v){
            $check = [$v=>''];
            $c_info = array_intersect_key($data,$check);
            if ($c_info){
                return $c_info;
            }
        }
        return false;
    }

    //通过微信获取客户信息
    public function getCustomerByWechat($wechat,$id=null){
        $res = DB::table($this->table_name.' as c')
            ->leftJoin('admin_users as a','c.recommend','=','a.id')
            ->select('c.wechat','a.name');
        if ($id != ''){
            $res->where('c.id','<>',$id);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $v){
            $m = explode(',',$v['wechat']);
            foreach ($m as $k=>$vs){
                $data[$vs] = $v['name'];
            }
        }
        foreach ($wechat as $v){
            $check = [$v=>''];
            $c_info = array_intersect_key($data,$check);
            if ($c_info){
                return $c_info;
            }
        }
        return false;
    }

    /* 通过邮箱获取客户 */
    public function getCustomerByEmail($email,$id=null){
        $res = DB::table($this->table_name.' as c')
            ->leftJoin('admin_users as a','c.recommend','=','a.id')
            ->select('c.email','a.name');
        if ($id != ''){
            $res->where('c.id','<>',$id);
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        $data = [];
        foreach ($result as $v){
            $m = explode(',',$v['email']);
            foreach ($m as $k=>$vs){
                $data[$vs] = $v['name'];
            }
        }
        foreach ($email as $v){
            $check = [$v => ''];
            $c_info = array_intersect_key($data,$check);
            if ($c_info){
                return $c_info;
            }
        }
        return false;
    }

    public function validCustomerRepeat($fields,$id=null)
    {
        $return_data = [
            'is_repeat' => 0,
            'returnData' => '',
            'data' => ''
        ];
        foreach ($fields as $key=>$value){
            switch($key){
                case 'mobile':
                    if ($value){
                        $mobiles = $value;
                        $res = $this->getCustomerByMobile($mobiles,$id);
                        if ($res){
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"手机号已存在，跟进人员：".$res['name']];
                            return $return_data;
                        }
                    }
                    break; // 跳出循环
                case 'email':
                    if ($value){
                        $email = explode(',',$value);
                        if (count($email) != count(array_unique($email))){ //检查邮箱有无重复
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"所填邮箱重复"];
                            return $return_data;
                        }
                        $res = $this->getCustomerByEmail($email,$id);
                        if ($res){
                            $name = array_shift($res);
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"邮箱已存在" . "，跟进人员：$name"];
                            return $return_data;
                        }
                    }
                    break;
                case 'qq':
                    if ($value){
                        $qq = explode(',',$value);
                        if (count($qq) != count(array_unique($qq))){ //检查腾讯云id有无重复
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"所填腾讯云id重复"];
                            return $return_data;
                        }
                        $res = $this->getCustomerByQq($qq,$id);
                        if ($res){
                            $name = array_shift($res);
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"腾讯云ID已存在" . "，跟进人员：$name"];
                            return $return_data;
                        }
                    }
                    break;
                case 'wechat';
                    if ($value){
                        $wechat = explode(',',$value);
                        if (count($wechat) != count(array_unique($wechat))){ //检查微信号有无重复
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"所填微信号重复"];
                            return $return_data;
                        }
                        $res = $this->getCustomerByWechat($wechat,$id);
                        if ($res){
                            $name = array_shift($res);
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"微信号已存在" . "，跟进人员：$name"];
                            return $return_data;
                        }
                    }
                    break;
                case 'company';
                    if ($value){
                        $company = explode(',',$value);
                        if (count($company) != count(array_unique($company))){ //检查公司名有无重复
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"所填公司名重复"];
                            return $return_data;
                        }
                        $res = $this->getCustomerByCompany($company,$id);
                        if ($res){
                            $name = array_shift($res);
                            $return_data['is_repeat'] = 1;
                            $return_data['returnData'] = ['code'=>313,"msg"=>"公司名已存在" . "，跟进人员：$name"];
                            return $return_data;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        return $return_data;
    }

    public function validateMemberAccount($column)
    {
        $res = DB::table($this->table_name)
            ->select('id','password');
        $res->where(function ($query) use ($column) {
            $query->where('email', '=', $column)
                ->orWhere('mobile', '=', $column)
                ->orWhere('name', '=', $column);
        });
        $result = $res->first();
        if(!$result){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return false;
        }
        return $res['id'];
    }

    public function getCustomerListWithFilter($fields)
    {
        $res = DB::table($this->table_name . ' as c')
            ->select('c.*','au.name as admin_name')
            ->leftJoin('admin_users as au', 'au.id', '=', 'c.recommend');
        if ($fields['admin_id'] != '') {
            $adminUserModel = new UserBase();
            $user_list = $adminUserModel->getAdminSubuser($fields['admin_id']);
            $res->whereIn('c.recommend', $user_list);
        }
        if ($fields['source'] != '') {
            $res->where('c.source', '=', $fields['source']);
        }
        if ($fields['cust_state'] != '') {
            $res->where('c.cust_state', $fields['cust_state']);
        }
        if ($fields['recommend'] != '') {
            $res->where('c.recommend', $fields['recommend']);
        }
        if ($fields['progress'] != '') {
            $res->where('c.progress', $fields['progress']);
        }
        if ($fields['contact_status'] != '') {
            if ($fields['contact_status'] == '未联系') {
                $res->where('c.cust_state', '!=', "1");
                $res->where(function ($query) {
                    $query->whereNull('c.contact_next_time')
                        ->orwhere('c.contact_next_time', '=', "0000-00-00 00:00:00");
                });
            }
            if ($fields['contact_status'] == '逾期') {
                $res->where('c.contact_next_time', '<', substr(Carbon::now()->toDateTimeString(), 0, 10) . ' 00:00:00');
                $res->where('c.contact_next_time', '!=', "0000-00-00 00:00:00");
                $res->where('c.cust_state', '!=', "1");
            }
            if ($fields['contact_status'] == '即将逾期') {
                $res->whereBetween('c.contact_next_time',[date('Y-m-d',time()) . ' 00:00:00',date('Y-m-d',(time()+3*86400)).' 23:59:59']);
                $res->where('c.contact_next_time', '!=', "0000-00-00 00:00:00");
                $res->where('c.cust_state', '!=', "1");
            }
        }
        if (isset($fields['list']) && count($fields['list']) > 0) {
            $res->whereIn('c.id', $fields['list']);
        }
        if ($fields['next_start_time'] != '' && $fields['next_end_time'] != '') {
            $res->whereBetween('c.contact_next_time', [$fields['next_start_time'], $fields['next_end_time']]);
        } else if ($fields['next_start_time'] != '' && $fields['next_end_time'] == '') {
            $res->where('c.contact_next_time', '>=', $fields['next_start_time']);
        } else if ($fields['next_start_time'] == '' && $fields['next_end_time'] != '') {
            $res->where('c.contact_next_time', '=<', $fields['next_end_time']);
        }
        if ($fields['start_time'] != '' && $fields['end_time'] != '') {
            $res->whereBetween('c.created_at', [$fields['start_time'], $fields['end_time']]);
        } else if ($fields['start_time'] != '' && $fields['end_time'] == '') {
            $res->where('c.created_at', '>=', $fields['start_time']);
        } else if ($fields['start_time'] == '' && $fields['end_time'] != '') {
            $res->where('c.created_at', '=<', $fields['end_time']);
        }
        if ($fields['searchKey'] != '') {
            $searchKey = $fields['searchKey'];
            $res->where(function ($query) use ($searchKey) {
                $query->where('c.realname', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.company', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.mobile', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.qq', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.wechat', 'LIKE', '%' . $searchKey . '%')
                    ->orwhere('c.project', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if ($fields['sortName'] == 'contact_next_time') {
            $res->whereNotNull('c.contact_next_time');
        }
        $fields['sortName'] = 'c.' . $fields['sortName'];
        $total = $res;
        $data['total'] = $total->count();
        $data['rows'] = [];
        $result = $res->skip($fields['start'])->take($fields['pageSize'])->orderBy($fields['sortName'], $fields['sortOrder'])->get();
        if (!$result) {
            return $data;
        }
        $result = json_decode(json_encode($result), true);
        if (!is_array($result) || count($result) < 1) {
            return $data;
        }
        $data['rows'] = $result;
        return $data;
    }

    /* 筛选客户字段 */
    public function getCustomerFields($fields = ['*'])
    {
        $res = DB::table($this->table_name)->select($fields)->get();
        if(!$res){
            return array();
        }
        $res = json_decode(json_encode($res),true);
        if(!is_array($res) || count($res)<1){
            return array();
        }
        return $res;
    }

    /* 添加客户 */
    public function customerInsert($data)
    {
        DB::beginTransaction();
        try {
            $data['created_at'] = Carbon::now()->toDateTimeString();
            $data['updated_at'] = Carbon::now()->toDateTimeString();
            $res_id = DB::table($this->table_name)->insertGetId($data);
            if(!$res_id){
                DB::rollback();
                return false;
            }
            $data['member_id'] = $res_id;
            $res = $this->_afterCustomerInsert($data);
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

    /* 客户批量添加 */
    public function customerBatchInsert($data)
    {
        $res = DB::table($this->table_name)->insert($data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 修改客户 */
    public function customerUpdate($id,$data)
    {
        $customerData = $this->getCustomerByID($id);
        if(!is_array($customerData)){
            return false;
        }
        DB::beginTransaction();
        try {
            $data['updated_at'] = Carbon::now()->toDateTimeString();
            $res = DB::table($this->table_name)->where('id',$id)->update($data);
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

    /* 客户批量修改 */
    public function customerBatchUpdate($field,$data)
    {
        $res = DB::table($this->table_name);
        if(is_array($field)){
            foreach ($field as $key=>$value){
                if($value[1]=='in'){
                    $res->whereIn($value[0],$value[2]);
                }else{
                    $res->where($value[0],$value[1],$value[2]);
                }
            }
        }
        $result = $res->update($data);
        if(!$result){
            return false;
        }
        return true;
    }

    /* 删除客户 */
    public function customerDelete($id){
        DB::beginTransaction();
        try {
            $res = DB::table($this->table_name)->where('id',$id)->delete();
            if(!$res){
                DB::rollback();
                return false;
            }
            $log = DB::table('assign_log')->where('member_id',$id)->first();
            if ($log){
                $del_assign_log_res = DB::table('assign_log')->where('member_id',$id)->delete();
                if(!$del_assign_log_res){
                    DB::rollback();
                    return false;
                }
            }
            DB::table('communicationlog')->where('member_id',$id)->delete();
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return $ex->getMessage();
        }
    }

    //批量删除
    public function customerDeleteAll($arr){
        DB::beginTransaction();
        try {
            $res = DB::table($this->table_name)->whereIn('id',$arr)->delete();
            if(!$res){
                DB::rollback();
                return false;
            }
            $del_assign_log_res =DB::table('assign_log')->whereIn('member_id',$arr)->delete();
            DB::table('communicationlog')->whereIn('member_id',$arr)->delete();
            if(!$del_assign_log_res){
                DB::rollback();
                return false;
            }
            DB::commit();
            return true;
        } catch(\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return $ex->getMessage();
        }
    }

    /* 添加客户后续处理 */
    private function _afterCustomerInsert($data)
    {
        $tmp_data = array(
            'member_id' => $data['member_id'],
            'assign_uid' => $data['recommend'],
            'assign_touid' => $data['recommend'],
            'assign_name' => $data['addperson'],
            'assign_admin' => $data['addperson'],
            'operation_uid' => $data['recommend']
        );
        $assignLogModel = new AssignLog();
        $res = $assignLogModel->assignLogInsert($tmp_data);
        if(!$res){
            return false;
        }
        return true;
    }

    /* 获取最新客户ID */
    public function getMaxCustomerID()
    {
        $id = DB::table($this->table_name)->max('id');
        return $id;
    }
    /* 获取用户客户数 */
    public function getCustomNumber($id, $date = ''){
        if(!empty($date))
            return DB::table($this->table_name)->where('created_at','LIKE','%'.$date.'%')->where('recommend','=',$id)->count();
        else
            return DB::table($this->table_name)->where('recommend','=',$id)->count();
    }

    public function getCustomerOverCount($id)
    {
        $data = DB::table($this->table_name)
            ->where('recommend','=',$id)
            ->where('contact_next_time','<',date('Y-m-d 00:00:00',time()))
            ->where('contact_next_time', '!=', "0000-00-00 00:00:00")
            ->where('cust_state', '!=', "1")
            ->count();
        return $data;
    }

    public function getExchangeRecommend($id, $data){
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $res = DB::table($this->table_name);
        $res_ext = DB::table('member_extend');
        if(is_array($id)){
            $res->whereIn("recommend",$id);
            $res_ext->whereIn("recommend",$id);
        }else{
            $res->where("recommend",$id);
            $res_ext->where("recommend",$id);
        }
        $res = $res->update($data);
        $data['update_time'] =$data['updated_at'];
        unset($data["updated_at"]);
        $res_ext->update($data);
        if($res || $res === 0){
            return true;
        }
        return false;
    }

    //修改客户信息记录旧信息
    public function updateCustomerLog($admin_id,$admin_name,$c_id){
        $res = DB::table($this->table_name)->where('id',$c_id)->first();
        $res = json_decode(json_encode($res),true);
        $data['c_id'] = $c_id;
        $data['u_id'] = $admin_id;
        $data['u_name'] = $admin_name;
        $data['c_info'] = json_encode($res);
        $data['created_at'] = Carbon::now()->toDateTimeString();
        DB::table('customer_log')->insert($data);
    }

    //获取修改客户记录
    public function customerLog($id){
        $res = DB::table('customer_log')->where('c_id',$id)->get();
        if (!$res){
            return false;
        }
        $res = json_decode(json_encode($res),true);
        foreach ($res as &$v){
            $v['c_info'] = json_decode($v['c_info'],true);
        }
        return $res;
    }

    //统计全部人
    public function statisticsCustomers($fields){
        $res = DB::table($this->table_name)
            ->select('created_at','source','recommend');
        if ($fields['start_time'] != '' && $fields['end_time'] != ''){
            $res->whereBetween('created_at',[$fields['start_time'],$fields['end_time']]);
        }
//        if ($fields['ids'] != ''){
//            $res->whereIn('recommend',$fields['ids']);
//        }
        $total = $res->get();
        $total = json_decode(json_encode($total),true);
        return $total;
    }

    //获取总人数
    public function getCustomerTotal($type = ''){
        $res = DB::table($this->table_name)->select('source',DB::raw('SUM(1) AS total_number'));
        if ($type != ''){
            $res->groupBy('source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($type != ''){
           return $result;
        }
        return $result[0]['total_number'];
    }

    //获取部门的总客户数
    public function getCompanyCustomerTotal($fields){
        $res = DB::table($this->table_name)
            ->select('source',DB::raw('SUM(1) AS total_number'))->whereIn('recommend',$fields['ids']);
        if ($fields['type'] != ''){
            $res->groupBy('source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($fields['type'] != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }

    //获取部门下的总激活人数
    public function getCustomerActivationTotal($fields){
        $res = DB::table($this->table_name)
            ->select('source',DB::raw('SUM(1) AS total_number'))
            ->where('cust_state',1)
            ->whereIn('recommend',$fields['ids']);
        if ($fields['type'] != ''){
            $res->groupBy('source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($fields['type'] != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }

    //根据时间段获取激活的人数
    public function getCustomerActivationTimeTotal($fields){
        $res = DB::table($this->table_name)
            ->select('source',DB::raw('SUM(1) AS total_number'))
            ->where('cust_state',1)
            ->whereBetween('activation_time',[$fields['start_time'],$fields['end_time']])
            ->whereIn('recommend',$fields['ids']);
        if ($fields['type'] != ''){
            $res->groupBy('source');
        }
        $result = $res->get();
        $result = json_decode(json_encode($result),true);
        if ($fields['type'] != ''){
            return $result;
        }
        return $result[0]['total_number'];
    }

}

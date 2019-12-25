<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Activity;
use App\Models\Admin\Attend;
use App\Models\Admin\Comment;
use App\Models\Admin\Configs;
use App\Models\Admin\Proposal;
use Carbon\Carbon;
use function GuzzleHttp\Promise\is_fulfilled;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\DB;

class ActivityController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    protected $fields = array(
        'name' => '',
        'explain' => '',
        'place' => '',
        'start_time' => '',
        'end_time' => '',
        'stop_time' => '',
        'host_party' => '',
        'host_contact' => '',
        'cost' => 0,
        'picture' => '',
        'regulations' => '',
        'details' => '',
        'limit_number' => '',
        'activity_type_id' => '',
        'audit_mode' => '',
        'notice' => ''
    );

    //活动类型列表
    public function get_activity_type_list(){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $activityModel = new Activity();
        $list = $activityModel->getActivityType();
        $this->returnData['data'] = $list;
        return $this->return_result($this->returnData);
    }

    //(增/修/删)活动类型
    public function activity_type_ajax(Request $request){
        if ($this->returnData['code'] > 0){
            return  $this->return_result($this->returnData）;
        }
        $data['id'] = $request->input('id','');
        $data['type'] = $request->input('type','');
        $data['name'] = $request->input('name','');
        if($data['type'] == 1){
            $name = $this->checkName($data['name']);
            if (isset($name['code'])){
                return $this->return_result($name);
            }
            if (mb_strlen($data['name']) >= 20){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '类型名称长度不能超过20个字';
                return $this->return_result($this->returnData);
            }
        }elseif ($data['type'] == 2){
            $name = $this->checkName($data['name']);
            if (isset($name['code'])){
                return $this->return_result($name);
            }
            if (!$data['id'] || !$data['name']){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '参数缺失';
                return $this->return_result($this->returnData);
            }
            if (mb_strlen($data['name']) >= 20){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '类型名称长度不能超过20个字';
                return $this->return_result($this->returnData);
            }
        }elseif ($data['type'] == 3){
            if (!$data['id']){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '参数缺失';
                return $this->return_result($this->returnData);
            }
        }else{
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败,参数类型未知';
            return $this->return_result($this->returnData);
        }
        $activityModel = new Activity();
        $res = $activityModel->updateActivityType($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }elseif ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '类型数量已达上限';
        }
        return $this->return_result($this->returnData);
    }

    //活动列表
    public function get_activity_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $page_no = $request->post('page_no', 1);
        $page_size = $request->post('page_size', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                                   //当前页码
            'pageSize' => $page_size,                                                                                   //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                                       //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'start_time' => trim($request->post('start_time','')),                                          //活动开始时间
            'end_time' => trim($request->post('end_time','')),                                              //活动结束时间
            'activity_type' => trim($request->post('activity_type','')),
            'status' => trim($request->post('status','')),
        );
        $activityModel = new Activity();
        $res = $activityModel->getActivityList($searchFilter);
        $attendModel = new Attend();
        foreach ($res['rows'] as &$v){
            $where['id'] = $v['id'];
            $number = $attendModel->getAttendNumber($where);
            $v['total_number'] = $number;
            if (isset($v['picture'])){
                $v['picture'] = $this->processingPictures($v['picture']);
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //添加活动
    public function add_activity(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->return_result($this->returnData);
        }
        $after_sale = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $after_sale[$field] = $request->input($field,$this->fields[$field]);
        }
        $name = $this->checkName($after_sale['name']);
        if (isset($name['code'])){
            return $this->return_result($name);
        }
        if ($after_sale['stop_time'] < (date('Y-m-d H:i:s',time()))){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '报名截止时间不能小于现在的时间';
            return $this->return_result($this->returnData);
        }
        if ($after_sale['start_time'] < $after_sale['stop_time']){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '活动开始时间不能小于报名截止时间';
            return $this->return_result($this->returnData);
        }
        $activityModel = new Activity();
        $res = $activityModel->toAdd($after_sale);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        return $this->return_result($this->returnData);
    }

    //取消活动
    public function cancel_activity($id,Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $activityModel = new Activity();
        $data['cause'] = $request->post['cause'];
        $res = $activityModel->cancelActivity($id,$data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '取消失败';
        }
        return $this->return_result($this->returnData);
    }

    //活动详情
    public function get_activity_info($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $activityModel = new Activity();
        $res = $activityModel->getActivityInfo($id);
        if ($res) {
            $res['activity_type_name'] = $activityModel->getActivityTypeName($res['activity_type_id']);
            $res['picture'] = $this->processingPictures($res['picture']);
            if ($res['end_time'] > Carbon::now()->toDateTimeString()){
                $res['expire'] = 1;
            }else{
                $res['expire'] = 2;
            }
            $attendModel = new Attend();
            $res['number'] = $attendModel->getActivityNumber($id);          //报名人数
            $res['fabulousNumber'] = $activityModel->fabulousNumber($id);  //点赞人数
            $res['collectNumber'] = $activityModel->collectNumber($id);    //收藏人数
            $this->returnData['data'] = $res;
            return $this->return_result($this->returnData);
        }
        $this->returnData['code'] = 1;
        $this->returnData['msg'] = '请求失败,活动不存在';
        return $this->return_result($this->returnData);
    }

    //报名列表
    public function entry_list($id,Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('page_no',1);
        $page_size = $request->post('page_size',10);
        $page_no = $page_no - 1;
        $data['pageNo'] = $page_no;
        $data['pageSize'] = $page_size;
        $data['start'] = $page_no * $page_no;
        $data['activity_id'] = $id;
        $data['sortOrder'] = 'desc';
        $data['sortName'] = 'id';
        $attendModel = new Attend();
        $res = $attendModel->getActivityList($data);
        if (isset($res['rows'])){
            foreach ($res['rows'] as &$v){
                $v['avatar'] = $this->processingPictures($v['avatar']);
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //审核 同意 拒接
    public function agree_refuse(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['status'] = $request->post('status','');
        $attendModel = new Attend();
        $res = $attendModel->agreeAttend($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }elseif ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '活动不在进行状态，审核失败';
        }
        return $this->return_result($this->returnData);
    }

    //评价列表
    public function comment_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->post('pageNo', 1);
        $page_size = $request->post('pageSize', 10);
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                                  //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                              //排序（desc，asc）
            'pageNumber' => $page_no,                                                                       //当前页码
            'pageSize' => $page_size,                                                                       //一页显示的条数
            'start' => ($page_no-1) * $page_size,                                                           //开始位置
            'searchKey' => trim($request->post('search','')),                                               //搜索关键词
            'status' => trim($request->post('isPicture','')),                                               //是否有图
            'startTime' => $request->input('startTime',''),
            'endTime' => $request->input('endTime',''),
            'activityId' =>  $request->input('activityId','')
        );
        $activityModel = new Activity();
        $res = $activityModel->getCommentList($searchFilter);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //审核评论
    public function check_comment(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['id'] = $request->post('id','');
        $data['status'] = $request->post('status','');
        $activityModel = new Comment();
        $res = $activityModel->updateStatus($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '审核失败';
        }
        return $this->return_result($this->returnData);
    }

    //删除评论
    public function del_comment($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $commentModel = new Comment();
        $res = $commentModel->delComment($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //报名人员导出
    public function to_excel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $activityModel = new Activity();
        $activity_name = $activityModel->getActivityInfo($id);
        $attendModel = new Attend();
        $res = $attendModel->getAttendAll($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '无人员报名参与';
            return $this->return_result($this->returnData);
        }
        if ($res){
            $arr=[['姓名','手机','报名时间','审核状态']];
            foreach($res as $key => $val){
                switch ($val['status']){
                    case '0':
                        $status = '未付款';
                        break;
                    case '1':
                        $status = '付款成功，待审核';
                        break;
                    case '2':
                        $status = '审核成功';
                        break;
                    case '3':
                        $status = '审核失败，待退款';
                        break;
                    case '4':
                        $status = '退款成功';
                        break;
                    default:
                        $status = '未知状态';
                        break;
                };
                $arr[] = [
                    $val['member_name'],
                    $val['mobile'],
                    $val['created_at'],
                    $status
                ];
            }
            $con = Configs::first();
            if ($con->env == 'CLOUD'){
                $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
                $a = Excel::create($activity_name['name'],function($excel) use ($arr){
                    $excel->sheet('客户报名信息', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->string('xlsx');
                file_put_contents($temp_file,$a);
                $data['code'] = 3;
                $data['name'] = '客户报名信息.xlsx';
                $data['data'] = $temp_file;
                return $data;
            }else{
                Excel::create($activity_name['name'],function($excel) use ($arr){
                    $excel->sheet('客户报名信息', function($sheet) use ($arr){
                        $sheet->rows($arr);
                    });
                })->export('xlsx');
            }
        }
    }

    //活动修改
    public function update_activity(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        if (!$id || $id == ''){
            return $this->return_result(ErrorCode::$admin_enum['customized'],'id不能为空');
        }
        $after_sale = [];
        foreach (array_keys($this->fields) as $field) {
            /* 验证参数未做 */
            $after_sale[$field] = $request->input($field,$this->fields[$field]);
        }
        if ($after_sale['stop_time'] < (date('Y-m-d H:i:s',time()))){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '报名截止时间不能小于现在的时间';
            return $this->return_result($this->returnData);
        }
        if ($after_sale['start_time'] < $after_sale['stop_time']){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '活动开始时间不能小于报名截止时间';
            return $this->return_result($this->returnData);
        }
        $activityModel = new Activity();
        $res = $activityModel->toUpdatead($after_sale,['id'=>$id]);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求失败';
        }
        return $this->return_result($this->returnData);
    }

    //留言列表
    public function proposal_list(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $page_no = $request->input('pageNo',1);
        $page_size = $request->input('pageSize',30);
        $pasma = [
            'page_size' => $page_size,
            'start' => ($page_no - 1) * $page_size,
            'type'  => $request->input('type',''),
            'start_time' => $request->input('startTime',''),
            'end_time' => $request->input('endTime',''),
            'searchKey' => trim($request->post('search','')),
        ];
        $proposalModel = new Proposal();
        $res = $proposalModel->getList($pasma);
        foreach ($res['rows'] as &$v){  //处理图片问题
            if ($v['picture_list']){
                $v['picture_list'] = json_decode($v['picture_list'],true);
                foreach ($v['picture_list'] as &$p_v){
                    $p_v = $this->processingPictures($p_v);
                }
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }

    //留言详情
    public function proposal_info($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $proposalModel = new Proposal();
        $res = $proposalModel->getInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请求信息不存在';
        }else{
            if ($res['picture_list']){
                $res['picture_list'] = json_decode($res['picture_list'],true);
                foreach ($res['picture_list'] as &$v){
                    $v = $this->processingPictures($v);
                }
            }
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //留言删除
    public function proposal_del($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $proposalModel = new Proposal();
        $res = $proposalModel->delProposal($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '';
        }
        return $this->return_result($this->returnData);
    }

    //活动删除
    public function activity_del($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $activityModel = new Activity();
        $res = $activityModel->delActivity($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //活动列表
    public function activity_all_list(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $activityModel = new Activity();
        $res = $activityModel->getAllList();
        if ($res){
            foreach ($res as &$v){
                $v['picture'] = $this->processingPictures($v['picture']);
            }
        }
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);
    }






































































}
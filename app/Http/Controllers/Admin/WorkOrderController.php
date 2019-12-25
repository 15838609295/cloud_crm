<?php

namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Models\Admin\Articles;
use App\Models\Admin\Configs;
use App\Models\Admin\Street;
use App\Models\Admin\TransferWorkOrder;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Admin\WorkOrder;
use Excel;

class WorkOrderController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    //公司简介
    public function enterprise(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $acticlesModel = new Articles();
        $res = $acticlesModel->getEnterprise();
        if (!$res){
            $data['name'] = '';
            $data['introduce'] = '';
            $data['picture'] = '';
            $data['tel'] = '';
            $data['address'] = '';
            $this->returnData['data'] = $data;
        }else{
            $res['picture'] = $this->processingPictures($res['picture']);
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //修改企业简介
    public function UpdateEnterprise(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        global $scf_data;
        $data['name'] = $request->post('name','');
        $data['introduce'] = $request->post('introduce','');
        $data['picture'] = $request->post('picture','');
        $data['tel'] = $request->post('tel','');
        $data['address'] = $request->post('address','');
        if (isset($scf_data['apiKey'])){
            $key = $scf_data['apiKey'];
        }else{
            $key = '';
        }
        if ($data['address'] != '' && $key != '' ){
            $url = 'https://apis.map.qq.com/ws/geocoder/v1/?address='.$data['address'].'&key='.$key;
            $result = file_get_contents($url);
            $result = json_decode($result,true);
            if ($result['status'] != 0){
                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '未获取到坐标，请重新输入地址（省、市、区格式）';
                return $this->return_result($this->returnData);
            }
            $position['lng'] = $result['result']['location']['lng'];
            $position['lat'] = $result['result']['location']['lat'];
            $data['description'] = json_encode($position);
        }
        $articlesModel = new Articles();
        $res = $articlesModel->updateEnterprise($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //街道无分页
    public function getNoPageStreet(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $streetModel = new Street();
        $res = $streetModel->getNoPageStreet();
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //街道列表
    public function streetList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('psgeNo',1);
        $data['pageSize'] = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $data['cid'] = $request->post('cid',0);
        $workOrderModel = new Street();
        $res = $workOrderModel->getStreetList($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '信息不存在';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //修改街道信息
    public function updateStreet(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $data['name'] =$request->post('name','');
        $data['admin_id'] = $request->post('admin_id','');
//        $data['tel'] = $request->post('tel','');
        $data['cid'] = $request->post('cid','');
//        $data['wx_status'] = $request->post('wx_status','');
        $streetModel = new Street();
        $res = $streetModel->updateStreetInfo($data,$id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //添加街道信息
    public function addStreetInfo(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['name'] = $request->post('name','');
        $data['admin_id'] = $request->post('admin_id','');
        $data['cid'] = $request->post('cid','');
//        $data['wx_status'] = $request->post('wx_status','');
//        $data['tel'] = $request->post('tel','');
        $streetModel = new Street();
        $res = $streetModel->adsDate($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return $this->return_result($this->returnData);
    }

//    //修改街道信息状态
//    public function updateStreetStatus(Request $request){
//        if ($this->returnData['code'] > 0){
//            return $this->returnData;
//        }
//        $id = $request->post('id','');
//        $data['wx_status'] = $request->post('wx_status','');
//        $streetModel = new Street();
//        $res = $streetModel->updateStreetStatus($id,$data);
//        if (!$res){
//            $this->returnData['code'] = 1;
//            $this->returnData['msg'] = '修改失败';
//        }
//        return $this->return_result($this->returnData);
//    }

    //街道删除
    public function delStreetId($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $streetModel = new Street();
        $res = $streetModel->delStreetId($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }elseif ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '请删除街道下的范围信息';
        }
        return $this->return_result($this->returnData);
    }

    //管理员绑定街道
    public function adminBindingStreet(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $data['admin_id'] = $request->post('admin_id','');
        $data['stret_id'] = $request->post('street_id','');
        $streetModel = new Street();
        $res = $streetModel->adminBindingStreet($data);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '绑定失败';
        }
        return $this->return_result($this->returnData);
    }

    //问题类型列表
    public function feedbackTypeList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $data['pageSize'] = $request->post('pageSize',20);
        $data['start'] = ($pageNo -1)*$data['pageSize'];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getFeedbackList($data);
        if (!$res){
            $this->returnData['data'] = [];
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //添加反馈类型
    public function addFeedback(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $name = $request->post('label','');
        if (!$name || !preg_match("/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u", $name)){
            $this->returnData = ErrorCode::$admin_enum['error'];
            $this->returnData['msg'] = '类型名称不能为空或包含特殊字符';
            return $this->return_result($this->returnData);
        }
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->addFeedback($name);
        if (!$res){
            $this->returnData['status'] = 1;
            $this->returnData['msg'] = '添加失败';
        }
        return $this->return_result($this->returnData);
    }

    //修改反馈类型
    public function updateFeedback(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $data['label'] = $request->post('label','');
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->updateFeedback($data,$id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return $this->return_result($this->returnData);
    }

    //删除问题标签
    public function delFeedback($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->delFeedbackId($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //工单列表
	public function getDataList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageno = $request->post('pageNo') ? $request->post('pageNo') : 1;
        $pagesize = $request->post('pageSize') ? $request->post('pageSize') : 10;
        $searchFilter = array(
            'sortName' => $request->post('sortName','id'),                                              //排序列名
            'sortOrder' => $request->post('sortOrder','desc'),                                          //排序（desc，asc）
            'street_id' => $request->post('street_id',''),
            'pageSize' => $pagesize,                                                                                   //一页显示的条数
            'start' => ($pageno-1) * $pagesize,                                                                        //开始位置
            'status' => trim($request->post('status','')),                                                    //搜索类型
            'searchKey' => trim($request->post('search','')),                                           //搜索条件
            'admin_id' => $this->AU['id'],
            'startTime' => $request->post('startTime',''),
            'endTime' => $request->post('endTime',''),
        );
        $workOrderModel = new WorkOrder();
        $data = $workOrderModel->getWorkOrderListWithFilter($searchFilter);
        $this->returnData['data'] = $data;
        return $this->return_result($this->returnData);
    }

    //工单详情
    public function orderInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getWorkOrderInfo($id,1);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '工单不存在';
        }else{
            if ($res['son_name'] || $res['father_name']){
                $res['street_son_name'] = $res['son_name'];
                $res['street_father_name'] = $res['father_name'];
            }
            if ($res['pic_list']){
                foreach ($res['pic_list'] as &$v){
                    $v = $this->processingPictures($v);
                }
            }
            if (is_array($res['work_order_log'])){
                foreach ($res['work_order_log'] as &$w_v){
                    if (is_array($w_v['annex'])){
                        foreach ($w_v['annex'] as &$a_v){
                            $data['id'] = $a_v;
                            $data['url'] = $this->processingPictures($a_v);
                            $a_v = (object)$data;
                        }
                    }
                }
            }
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //转单列表
    public function changeOrderList(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $psgeSize = $request->post('pageSize',1);
        $searchFilter = [
            'pageSize' => $psgeSize,
            'start' => ($pageNo -1) * $psgeSize,
            'status' => $request->post('status',''),
            'sortName' => $request->post('sortName','id'),
            'sortOrder' => $request->post('sortOrder','desc'),
            'startTime' => $request->post('startTime',''),
            'endTime' => $request->post('endTime',''),
            'searchKey' => $request->post('searchKey',''),
        ];
        $transferWorkOrder = new TransferWorkOrder();
        $res = $transferWorkOrder->getWorkOrderChangeOrder($searchFilter);
        $this->returnData['data'] = $res;
        return $this->return_result($this->returnData);

    }

    //转单详情
    public function changeOrderInfo($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $transferWorkOrder = new TransferWorkOrder();
        $res = $transferWorkOrder->getChangeOrderInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '工单不存在';
        }else{
            if ($res['pic_list']){
                $res['pic_list'] = json_decode($res['pic_list'],true);
                foreach ($res['pic_list'] as &$v){
                    $v = $this->processingPictures($v);
                }
            }
            if (is_array($res['work_order_log'])){
                foreach ($res['work_order_log'] as &$w_v){
                    if (is_array($w_v['annex'])){
                        foreach ($w_v['annex'] as &$a_v){
                            $data['id'] = $a_v;
                            $data['url'] = $this->processingPictures($a_v);
                            $a_v = (object)$data;
                        }
                    }
                }
            }
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //转单人员列表
    public function changeOrderAdminList(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $companyModel = new Company();
        $this->returnData['data'] = $companyModel->getCompanyAdminList();
        return $this->return_result($this->returnData);
    }

    //转单审核
    public function changeOrderVerify(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->post('id','');
        $data = [
            'admin_id' => $request->post('admin_id'),
            'status' => $request->post('status','')
        ];
        if ($data['admin_id'] == '' && $data['status'] == 1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '未选择管理员';
            return $this->return_result($this->returnData);
        }
        $transferWorkOrder = new TransferWorkOrder();
        $res = $transferWorkOrder->changeOrderAdmin($id,$data);
        if (!$res){
            $this->returnData = ErrorCode::$admin_enum['modifyfail'];
        }elseif ($res === -1){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '审核失败，此单已解决';
        }
        return $this->return_result($this->returnData);
    }

    //工单导出
    public function orderToExcel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $id = $request->input('id','');
        $type = $request->input('type','');
        $ids = explode(',',$id);
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->getWorkOrderListInfo($ids,$type);
        $arr[] = ['反馈人','反馈人电话','反馈信息','街道','区域','反馈类型','详细地址','网格员','网格员电话','状态','反馈时间','处理时间','确认解决时间'];
        if ($type == 1){
            foreach ($res as $k=>&$v) {
                $arr[] = [
                    $v['member_name'],
                    $v['member_mobile'],
                    $v['description'],
                    $v['street_father_name'],
                    $v['street_son_name'],
                    $v['type_name'],
                    $v['address'],
                    $v['admin_name'],
                    $v['admin_mobile'],
                    $v['status'],
                    $v['created_at'],
                    $v['accept_time'],
                    $v['end_time'],
                ];
                $arr[] = [];
                if ($v['work_log']){
                    foreach ($v['work_log'] as $w_v){
                        $arr[] = [
                            $w_v['type_txt'],$w_v['remarks'],'反馈时间',$w_v['created_at']
                        ];
                    }
                }
            }
        }else{
            foreach ($res as $k=>&$v) {
                $arr[] = [
                    $v['member_name'],
                    $v['member_mobile'],
                    $v['description'],
                    $v['street_father_name'],
                    $v['street_son_name'],
                    $v['type_name'],
                    $v['address'],
                    $v['admin_name'],
                    $v['admin_mobile'],
                    $v['status'],
                    $v['created_at'],
                    $v['accept_time'],
                    $v['end_time'],
                ];
            }
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a = Excel::create('工单反馈信息',function($excel) use ($arr){
                $excel->sheet('工单反馈信息', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                            'F' => '25%',
                            'G' => '15%',
                            'H' => '15%',
                            'I' => '15%',
                            'J' => '20%',
                            'K' => '20%',
                            'L' => '20%',
                            'M' => '30%',
                        ));
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '工单反馈信息.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('工单反馈信息',function($excel) use ($arr){
                $excel->sheet('工单反馈信息', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                            'F' => '25%',
                            'G' => '15%',
                            'H' => '15%',
                            'I' => '15%',
                            'J' => '20%',
                            'K' => '20%',
                            'L' => '20%',
                            'M' => '30%',
                        ));
                });
            })->export('xlsx');
        }
    }

    //删除工单
    public function delWorkOrder($id){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->delWorkOrderInfo($id);
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '删除失败';
        }
        return $this->return_result($this->returnData);
    }

    //工单数据统计
    public function workOrderStatistics(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->workOrderStatistics();
        if (!$res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '统计数据失败';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //工单统计查询
    public function workOrderSelect(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',10);
        $fields = [
            'start' => ($pageNo -1)*$pageSize,
            'pageSize' => $pageSize,
            'start_time' => $request->post('startTime',date('Y-m-01 00:00:00',time())),
            'end_time' => $request->post('endTime',date('Y-m-d 23:59:59',time()))
        ];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->selectTime($fields,1);
        if (!$res){
            $this->returnData['data']['total'] = 0;
            $this->returnData['data']['list'] = '';
        }else{
            $this->returnData['data'] = $res;
        }
        return $this->return_result($this->returnData);
    }

    //工单统计导出
    public function workOrderSelectToExcel(Request $request){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
        $pageNo = $request->post('pageNo',1);
        $pageSize = $request->post('pageSize',10);
        $fields = [
            'start' => ($pageNo -1)*$pageSize,
            'pageSize' => $pageSize,
            'start_time' => $request->post('startTime',date('Y-m-01 00:00:00',time())),
            'end_time' => $request->post('endTime',date('Y-m-d 23:59:59',time()))
        ];
        $workOrderModel = new WorkOrder();
        $res = $workOrderModel->selectTime($fields,2);
        $arr[] = ['日期','总反馈量','签收量','处理量','完成率'];
        foreach ($res['rows'] as $k=>&$v) {
            $arr[] = [
                $v['time'],
                $v['total'],
                $v['accept'],
                $v['solve'],
                $v['solve_rate'].'%',
            ];
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a = Excel::create('工单反馈统计',function($excel) use ($arr){
                $excel->sheet('工单反馈统计', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '工单反馈统计.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('工单反馈统计',function($excel) use ($arr){
                $excel->sheet('工单反馈统计', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->export('xlsx');
        }
    }

	//街道导出
    public function streetToExcel(){
        if ($this->returnData['code'] > 0){
            return $this->returnData;
        }
	    $streetModel = new Street();
	    $res = $streetModel->getAllStreetList();
        if (!$res){
            return $this->return_result(ErrorCode::$admin_enum['customized'],'无数据');
        }
        $arr[] = ['记录id','街道名称','组长','电话','下属区域'];
        foreach ($res as $k=>&$v) {
            $arr[$k +1] = [
                $v['id'],
                $v['name'],
                $v['admin_name'],
                $v['mobile'],
            ];
            if(isset($v['datalist'])){
                foreach ($v['datalist'] as $d){
                    array_push($arr[$k+1], $d);
                }
            }
        }

	    $con = Configs::first();
	    if ($con->env == 'CLOUD'){
            $temp_file = tempnam(sys_get_temp_dir(),time().".xlsx");  //临时文件
            $a = Excel::create('街道信息',function($excel) use ($arr){
                $excel->sheet('街道信息', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->string('xlsx');
            file_put_contents($temp_file,$a);
            $data['code'] = 3;
            $data['name'] = '街道信息.xlsx';
            $data['data'] = $temp_file;
            return $data;
        }else{
            Excel::create('街道信息',function($excel) use ($arr){
                $excel->sheet('街道信息', function ($sheet) use ($arr) {
                    $sheet->rows($arr)->setWidth(
                        array(//调整导出表格单元格宽度
                            'A' => '15%',
                            'B' => '13%',
                            'C' => '25%',
                            'D' => '18%',
                            'E' => '20%',
                        ));
                });
            })->export('xlsx');
        }
    }
}
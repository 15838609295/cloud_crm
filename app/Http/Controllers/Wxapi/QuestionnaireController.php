<?php
namespace App\Http\Controllers\Wxapi;

use App\Models\Admin\Findings;
use App\Models\Admin\Questionnaire;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    public function get_data_list(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $page_no = request()->post('page_no',1);
        $page_size = request()->post('page_size',10);
        $params = [
            'start' => ($page_no -1 )*$page_size,
            'pageSize' => $page_size,
            'status' => request()->post('status',''),
            'searchKey' => request()->post('searchKey',''),
            'start_time' => request()->post('start_time',''),
            'end_time' => request()->post('end_time',''),
            'sortName' => request()->post("sortName", "id"),
            'sortOrder' => request()->post("sortOrder", "desc"),
        ];

        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->getList($params);
        $this->result['data'] = $res;
        return response()->json($this->result);
    }

    public function get_info($id){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $questionnaireModel = new Questionnaire();
        $res = $questionnaireModel->getQuestInfo($id);
        if ($res){
            $this->result['data'] = $res;
        }else{
            $this->result['status'] = 1;
            $this->result['msg'] = '活动不存在';
        }
        return response()->json($this->result);
    }

    public function get_submit(){
        if ($this->result['status'] > 0){
            return response()->json($this->result);
        }
        $parmas = [
            'questionnaire_id' => request()->post('id',''),
            'result' => serialize(request()->post('result','')),
            'start_time' => request()->post('status_time',''),
        ];
        $que_number = DB::table('questionnaire')->where('id',$parmas['questionnaire_id'])->select('total')->firet();
        $que_number = json_decode(json_encode($que_number),true);
        $where['total'] = $que_number['total'] + 1;
        DB::table('questionnaire')->where('id',$parmas['questionnaire_id'])->update($where);
        $findingsModel = new Findings();
        $res = $findingsModel->addFindings($parmas);
        if (!$res){
            $this->result['status'] = 1;
            $this->result['msg'] = '提交失败';
        }
        return response()->json($this->result);
    }
}
?>
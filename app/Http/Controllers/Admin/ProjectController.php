<?php
//
//namespace App\Http\Controllers\Admin;
//
//use App\Models\Admin\Members;
//use App\Models\Admin\Project;
//use Illuminate\Http\Request;
//
//use App\Http\Controllers\Controller;
//
//class ProjectController extends Controller
//{
//    private $returnData = array(
//        "status"=>0,
//        'msg'=>'请求成功',
//        'data'=>'',
//    );
//
//    //项目列表
////    public function index()
////    {
////        return view('admin.project.index');
////    }
//
//
//    public function getDataList(Request $request)
//    {
//        if (!$request->ajax()) {
//            $this->returnData['status'] = 99;
//            $this->returnData['msg'] = '请求参数不正确';
//            return response()->json($this->returnData);
//        }
//        $pageno = $request->post("pageNumber") ? $request->post("pageNumber") : 1;
//        $pagesize = $request->post("pageSize") ? $request->post("pageSize") : 10;
//        $searchFilter = array(
//            'sortName' => $request->post("sortName"), //排序列名
//            'sortOrder' => $request->post("sortOrder"), //排序（desc，asc）
//            'pageNumber' => $pageno, //当前页码
//            'pageSize' => $pagesize, //一页显示的条数
//            'start' => ($pageno-1) * $pagesize, //开始位置
//            'searchKey' => trim($request->post("search",'')) //搜索条件
//        );
//        $projectModel = new Project();
//        $data = $projectModel->getProjectListWithFilter($searchFilter);
//        $this->returnData['data'] = $data;
//        return response()->json($this->returnData);
//    }
//
//    //项目添加
//    public function create()
//    {
//        $data["name"] = "";
//        return view('admin.project.create',$data);
//    }
//
//    //项目增加
//    public function store(Request $request)
//    {
//        $data['name']   = $request->input("name");
//        $projectModel = new Project();
//        $res = $projectModel->getProjectByName($data['name']);
//        if(is_array($res)){
//            return redirect('/admin/project')->withErrors("名称已存在");
//        }
//        $res = $projectModel->projectInsert($data);
//        if(!$res){
//            return redirect('/admin/project')->withErrors("添加失败");
//        }
//        return redirect('/admin/project')->withSuccess("添加成功");
//    }
//
//    //项目修改
//    public function edit($id)
//    {
//        $projectModel = new Project();
//        $data = $projectModel->getProjectByID((int)$id);
//        if (!is_array($data)) return redirect('/admin/project')->withErrors("找不到数据!");
//        return view('admin.project.edit', $data);
//    }
//
//    //项目更新
//    public function update(Request $request, $id)
//    {
//        $projectModel = new Project();
//        $info = $projectModel->getProjectByID((int)$id);
//        if(!is_array($info)){
//            return redirect('/admin/project')->withErrors("数据不存在");
//        }
//        $data['name'] = trim($request->input("name"));
//        $res = $projectModel->getProjectByName($data['name'],(int)$id);
//        if(is_array($res)){
//            return redirect('/admin/project')->withErrors("名称已存在");
//        }
//        $res = $projectModel->projectUpdate((int)$id,$data);
//        if(!$res){
//            return redirect('/admin/project')->withErrors("更新失败");
//        }
//        $memberModel = new Members();
//        $memberModel->memberProjectUpdate($info['name'],$data);
//        return redirect('/admin/project')->withSuccess("更新成功");
//    }
//
//    //项目删除
//    public function destroy($id)
//    {
//        $projectModel = new Project();
//        $res = $projectModel->projectDelete((int)$id);
//        if(!$res){
//            return redirect('/admin/project')->withErrors("删除失败");
//        }
//        return redirect('/admin/project')->withSuccess("删除成功");
//    }
//}

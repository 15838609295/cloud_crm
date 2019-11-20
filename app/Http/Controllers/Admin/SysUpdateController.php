<?php
//namespace App\Http\Controllers\Admin;
//
//use App\Models\Scripts\Sysupdate;
//use Illuminate\Http\Request;
//
//class SysUpdateController extends BaseController
//{
//    public function __construct(Request $request)
//    {
//        parent::__construct($request);
//    }
//
//    public function index()
//    {
//        $data['versionList'] = Sysupdate::getSysVersionList();
//        return view('admin.sysupdate.index',$data);
//    }
//
//    public function getDataList()
//    {
//        $this->returnData['data'] = Sysupdate::getSysVersionList();
//        return response()->json($this->returnData);
//    }
//}

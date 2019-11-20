<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\AdminUser;
use App\Models\Admin\Configs;
use App\Models\Admin\Picture;
use Carbon\Carbon;
use App\Http\Config\ErrorCode;
use App\Library\UEditorUpload;
use Illuminate\Http\Request;
use App\Library\UploadFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class FilesController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    protected function UEditorConfig()
    {
        $config = array(
            'imageActionName' => 'up_image',
            'imageFieldName' => 'up_img',
            'imageMaxSize' => 2048000,
            'imageAllowFiles' => [".png", ".jpg", ".jpeg", ".bmp"],
            'imageCompressEnable' => true,
            'imageCompressBorder' => 1600,
            'imageInsertAlign' => 'none',
            'imageUrlPrefix' => '',
            'imagePathFormat' => '/uploads/picture/{yyyy}{mm}{dd}/{time}{rand:6}'
        );
        return $config;
    }

    /* 获取UEditor配置 */
    public function getUEditorConfig()
    {
        return [
            /* 上传图片配置项 */
            "imageActionName" => "uploadimage", /* 执行上传图片的action名称 */
            "imageFieldName" => "file", /* 提交的图片表单名称 */
            "imageMaxSize" => 2048000, /* 上传大小限制，单位B */
            "imageAllowFiles" => [".png", ".jpg", ".jpeg", ".gif"], /* 上传图片格式显示 */ /* 用于ueditor编辑器校验 */
            "imageCompressEnable" => true, /* 是否压缩图片,默认是true */
            "imageCompressBorder" => 1600, /* 图片压缩最长边限制 */
            "imageInsertAlign" => "none", /* 插入的图片浮动方式 */
            "imageUrlPrefix" => "", /* 图片访问路径前缀 */
            "imagePathFormat" => "/uploads/picture/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */

            /* 抓取远程图片配置 */
            "catcherLocalDomain" => ["127.0.0.1", "localhost", "img.baidu.com"],
            "catcherActionName" => "catchimage", /* 执行抓取远程图片的action名称 */
            "catcherFieldName" => "source", /* 提交的图片列表表单名称 */
            "catcherPathFormat" => "/uploads/picture/{yyyy}{mm}{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "catcherUrlPrefix" => "", /* 图片访问路径前缀 */
            "catcherMaxSize" => 2048000, /* 上传大小限制，单位B */
            "catcherAllowFiles" => [".png", ".jpg", ".jpeg", ".gif"], /* 抓取图片格式显示 */
            "catcherAllowFilesTP" => ["png", "jpg", "jpeg", "gif"], /* 上传图片格式显示 */ /* 用于thinkphp校验 */

            /* 列出指定目录下的图片 */
            "imageManagerActionName" => "listimage", /* 执行图片管理的action名称 */
            "imageManagerListPath" => "/ueditor/php/upload/image/", /* 指定要列出图片的目录 */
            "imageManagerListSize" => 20, /* 每次列出文件数量 */
            "imageManagerUrlPrefix" => "", /* 图片访问路径前缀 */
            "imageManagerInsertAlign" => "none", /* 插入的图片浮动方式 */
            "imageManagerAllowFiles" => [".png", ".jpg", ".jpeg", ".gif"], /* 列出的文件类型 */
        ];
    }

    /* UEditor上传图片 */
    public function uploadByUEditor(Request $request)
    {
        $action = $action = $request->input('action','config');
        $CONFIG2 = $this->getUEditorConfig();
        switch ($action) {
            case 'config':
                return response()->json($CONFIG2);
                break;
            /* 上传图片 */
            case 'uploadimage':
                $config = array(
                    "allowFiles" => $CONFIG2['imageAllowFiles'],
                    "maxSize" => $CONFIG2['imageMaxSize'],
                    'pathFormat' => $CONFIG2['imagePathFormat'],
                );
                $fieldName = $CONFIG2['imageFieldName'];
                $uploader = new UEditorUpload($fieldName, $config, 'upload');
                $upload_res = $uploader->getFileInfo();
                if(!isset($upload_res['state']) || $upload_res['state']!='SUCCESS'){
                    $this->returnData = ErrorCode::$admin_enum['fail'];
                    $this->returnData['msg'] = '上传失败';
                    return response()->json($this->returnData);
                }
                $upload_res['id'] = $upload_res['url'];
                $upload_res['url'] = $request->server("REQUEST_SCHEME") . "://" . $request->server("HTTP_HOST") . $upload_res['url'];
                $this->returnData['data'] = $upload_res;
                return response()->json($this->returnData);
                break;
            default:
                $this->returnData = ErrorCode::$admin_enum['fail'];
                $this->returnData['msg'] = '不支持';
                return response()->json($this->returnData);
                break;
        }
    }

    /* UEditor上传图片 */
    public function uploadPicByUEditor()
    {
        $config = $this->UEditorConfig();
        $conf_param = array(
            'pathFormat' => $config['imagePathFormat'],
            'maxSize' => $config['imageMaxSize'],
            'allowFiles' => $config['imageAllowFiles']
        );
        $fieldName = $config['imageFieldName'];
        $uploader = new UEditorUpload($fieldName, $conf_param, 'upload');
        $upload_res = $uploader->getFileInfo();
        if(!isset($upload_res['state']) || $upload_res['state']!='SUCCESS'){
            $this->returnData = ErrorCode::$admin_enum['fail'];
            $this->returnData['msg'] = '上传失败';
            return response()->json($this->returnData);
        }
        $this->returnData['data'] = ['url' => $upload_res['url']];
        return response()->json($this->returnData);
    }

    /* 头像上传 */
    public function UploadAvatar(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $con = Configs::first();
        if ($con->env == 'CLOUD'){
            $base64_img = trim($request->post('avatar',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $type = ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp',''];
            $files = json_decode($base64_img,true);
            if (!in_array($files['type'],$type)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '文件类型错误';
                return response()->json($this->returnData);
            }
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/avatar/'.$time;
            $img_name = $cloud_file.'/'.time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传图片失败';
                return response()->json($this->returnData);
            }
            $res['url'] = $url['ObjectURL'];
        }else{
            $file = $request->file('avatar');
            if (!$file){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $res = (new UploadFile([
                'upload_dir' => './uploads/avatar/',
                'type'       => ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp']
            ]))->upload($file);
            if($res['code'] > 0) {
                return response()->json($res);
            }
        }
        $adminuserModel = new AdminUser();
        $fields['wechat_pic'] = $res['url'];
        $update_res = $adminuserModel->adminUserUpdate($this->AU['id'],$fields);
        if (!$update_res){
            $this->returnData['code'] = 1;
            $this->returnData['msg'] = '修改失败';
        }
        return response()->json($this->returnData);
    }

    /* 图片上传 */
    public function UploadPicture(Request $request)
    {
        $file = $request->file('picture');
        if (!$file){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '缺少文件';
            return response()->json($this->returnData);
        }
        $res = (new UploadFile([
            'upload_dir' => './uploads/picture/',
            'type'       => ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp']
        ]))->upload($file);
        if($res['code'] > 0) {
            return response()->json($res);
        }
        $this->returnData['data'] = ['url' => $res['url']];
        return response()->json($this->returnData);
    }

    /* 文件上传 */
    public function uploadFiles(Request $request)
    {
    	$file = $request->file('file');
        if (!$file){
            $this->returnData = ErrorCode::$admin_enum['params_error'];
            $this->returnData['msg'] = '缺少文件';
            return response()->json($this->returnData);
		}
        $inputFileName = $request->file('file')->store('article_file');
        $filePath = 'storage/app/'.iconv('UTF-8', 'GBK',$inputFileName);
        $this->returnData['data'] = ['url' => $filePath];;
        return response()->json($this->returnData);
    }

    /* 附件上传 */
    public function uploadAnnex(Request $request)
    {
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('file',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $files = json_decode($base64_img,true);
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/annex/'.$time;
            $img_name = $cloud_file.'/'.time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传文件失败';
                return response()->json($this->returnData);
            }
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['url'] = $url['ObjectURL'];
            $result['data']['id'] = $url['ObjectURL'];
        } else{
            $file = $request->file('file');
            if (!$file){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $dir_name = date('Ymd',time());
            $inputFileName = $request->file('file');
            //文件扩展名
            $ext = $inputFileName->getClientOriginalExtension();
            //获取文件的绝对路径
            $path = $inputFileName->getRealPath();
            //定义文件名
            $filename = time().'.'.$ext;
            $new_path = public_path().'/uploads/annex/'.$dir_name;
            if (!is_dir($new_path)){
                mkdir($new_path,0777,true);
            }
            //移动保存文件
            if (move_uploaded_file($path,$new_path.'/'.$filename) && file_exists($new_path.'/'.$filename)){
                $path = 'uploads/annex/'.$dir_name.'/'.$filename;
                $result['code'] = 0;
                $result['msg'] = '上传成功';
                $result['data'] = ['url' => $path,'id'=>$path];
            }else{
                $result['code'] = 1;
                $result['msg'] = '上传失败';
            }
        }
        return response()->json($result);
    }

    /* 视频上传 */
    public function uploadVideo(Request $request){
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('file',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $files = json_decode($base64_img,true);
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/video/'.$time;
            $img_name = $cloud_file.'/'.time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传文件失败';
                return response()->json($this->returnData);
            }
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['url'] = $url['ObjectURL'];
            $result['data']['id'] = $url['ObjectURL'];
        } else{
            $file = $request->file('file');
            if (!$file){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $dir_name = date('Ymd',time());
            $inputFileName = $request->file('file');
            //文件扩展名
            $ext = $inputFileName->getClientOriginalExtension();
            //获取文件的绝对路径
            $path = $inputFileName->getRealPath();
            //定义文件名
            $filename = time().'.'.$ext;
            $new_path = public_path().'/uploads/video/'.$dir_name;
            if (!is_dir($new_path)){
                mkdir($new_path,0777,true);
            }
            //移动保存文件
            if (move_uploaded_file($path,$new_path.'/'.$filename) && file_exists($new_path.'/'.$filename)){
                $path = 'uploads/video/'.$dir_name.'/'.$filename;
                $result['code'] = 0;
                $result['msg'] = '上传成功';
                $result['data'] = ['url' => $path,'id'=>$path];
            }else{
                $result['code'] = 1;
                $result['msg'] = '上传失败';
            }
        }
        return response()->json($result);
    }

    /* 合同上传 */
    public function uploadContract(Request $request)
    {
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('file',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $files = json_decode($base64_img,true);
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/contract/'.$time;
            $img_name = $cloud_file.'/'.time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传文件失败';
                return response()->json($this->returnData);
            }
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['url'] = $url['ObjectURL'];
            $result['data']['id'] = $url['ObjectURL'];
        } else{
            $file = $request->file('file');
            if (!$file){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $dir_name = date('Ymd',time());
            $inputFileName = $request->file('file');
            //文件扩展名
            $ext = $inputFileName->getClientOriginalExtension();
            //获取文件的绝对路径
            $path = $inputFileName->getRealPath();
            //定义文件名
            $filename = time().'.'.$ext;
            $new_path = public_path().'/uploads/contract/'.$dir_name;
            if (!is_dir($new_path)){
                mkdir($new_path,0777,true);
            }
            //移动保存文件
            if (move_uploaded_file($path,$new_path.'/'.$filename) && file_exists($new_path.'/'.$filename)){
                $path = 'uploads/contract/'.$dir_name.'/'.$filename;
                $result['code'] = 0;
                $result['msg'] = '请求成功';
                $result['data'] = ['url' => $path,'id'=>$path];
            }else{
                $result['code'] = 1;
                $result['msg'] = '上传失败';
            }
        }
        return response()->json($result);
    }

    //上传图片增加记录
    public function imageUpload(Request $request){
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('file',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $type = ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp',''];
            $files = json_decode($base64_img,true);
            if (!in_array($files['type'],$type)){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '文件类型错误';
                return response()->json($this->returnData);
            }
            $temp_file = tempnam(sys_get_temp_dir(),"php");  //临时文件
            $content = $files['content'];
            file_put_contents($temp_file,base64_decode($content));        //文件流写入文件
            //创建文件夹
            $time = date('Ymd',time());
            $cloud_file = '/contract/'.$time;
            $img_name = $cloud_file.'/'.time().$files['name'];
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传图片失败';
                return response()->json($this->returnData);
            }
            $picture_info['name'] = $img_name;
            $picture_info['uid'] = $this->AU['id'];
            $picture_info['url'] = $url['ObjectURL'];
            $picture_info['type'] = $files['type'];
            $picture_info['status'] = 1;
            $picture_info['src'] = $url['ObjectURL'];
            $picture_info['time'] = Carbon::now();
            $id = DB::table('picture')->insertGetId($picture_info);
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['title'] = $img_name;
            $result['data']['url'] = $url['ObjectURL'];
            $result['data']['type'] = $files['type'];
            $result['data']['id'] = $url['ObjectURL'];
        } else{
            $base64_img = $request->file('file');
            $res = (new UploadFile([
                'upload_dir' => './uploads/picture/',
                'type'       => ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp']
            ]))->upload($base64_img);
            if($res['code'] > 0) {
                return response()->json($res);
            }
            $picture_info['name'] = $res['name'];
            $picture_info['uid'] = $this->AU['id'];
            $picture_info['url'] = 'https://'.$_SERVER['SERVER_NAME'].$res['url'];
            $picture_info['type'] = $res['type'];
            $picture_info['status'] = 1;
            $picture_info['src'] = $res['url'];
            $picture_info['time'] = Carbon::now();
            $id = DB::table('picture')->insertGetId($picture_info);
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['title'] = $res['name'];
            $result['data']['url'] = $picture_info['url'];
            $result['data']['type'] = $res['type'];
            $result['data']['id'] = $res['url'];
        }
        return response()->json($result);
    }

    //获取用户上传历史图片
    public function getHistoryList(Request $request){
        $uid = $this->AU['id'];
        $pageNumber = $request->input("page",1);  //当前页码
        $pageSize = $request->input("pageSize",10);   //一页显示的条数
        $start = ($pageNumber-1)*$pageSize;   //开始位置
        $rows = DB::table('picture')->where('uid',$uid)->where('status',1);
        $data['code'] = 0;
        $data['msg'] = '请求成功';
        $data['data']['count'] = $rows->count();
        $data['data']['list'] = $rows->skip($start)->take($pageSize)->orderBy('id','desc')->get();
        $data['data']['list'] = json_decode(json_encode($data['data']['list']),true);
        foreach ($data['data']['list'] as &$v){
            $v['id'] = $v['src'];
            $v['src'] = $this->processingPictures($v['src']);
        }
        return response()->json($data);
    }

    //软删除用户历史图片
    public function delHistory(Request $request){
        $id = $request->input('ids');
        $data['status'] = 0;
        $res = DB::table('picture')->where('src',$id)->update($data);
        return response()->json($this->returnData);
    }

    public function dalete(Request $request){
        $id = $request->input('ids');
        $res = DB::table('picture')->where('src',$id)->delete();
        return response()->json($this->returnData);
    }

    //云点播上传视频 签名
    public function vodSign(){
        global $scf_data;
        $con = Configs::first();
        //云开发 改用云点播 上传视频
        if (!$con->tencent_secretid || !$con->tencent_secrekey){
            if (!$scf_data['cloud']['secretId'] || !$scf_data['cloud']['secretKey']){
                $result['code'] = 1;
                $result['msg'] = '访问秘钥未配置';
                return response()->json($result);
            }else{
                $secret_id = $scf_data['cloud']['secretId'];
                $secret_key = $scf_data['cloud']['secretKey'];
            }
        }else{
            $secret_id = $con->tencent_secretid;
            $secret_key = $con->tencent_secrekey;
        }
        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + 60;  // 签名有效期：1分钟

        // 向参数列表填入参数
        $arg_list = array(
            "secretId" => $secret_id,
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "random" => rand()
        );

        // 计算签名
        $orignal = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $orignal, $secret_key, true).$orignal);
        $result['code'] = 0;
        $result['msg'] = '请求成功';
        $result['data'] = ['sign' => $signature];
        return response()->json($result);
    }
}

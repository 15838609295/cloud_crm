<?php

namespace App\Http\Controllers\Web;

use App\Http\Config\ErrorCode;
use App\Library\UEditorUpload;
use Illuminate\Http\Request;
use App\Library\UploadFile;


class FilesController extends BaseController
{
    public function __construct(Request $request){
        parent::__construct($request);
    }

    protected function UEditorConfig(){
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
    public function getUEditorConfig(){
        $this->returnData['data'] = $this->UEditorConfig();
        return response()->json($this->returnData);
    }

    /* UEditor上传图片 */
    public function uploadPicByUEditor(){
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
        $this->returnData['data'] = ['url' => $res['url']];
        return response()->json($this->returnData);
    }

    /* 图片上传 */
    public function UploadPicture(Request $request){
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
    public function uploadFiles(Request $request){
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
}

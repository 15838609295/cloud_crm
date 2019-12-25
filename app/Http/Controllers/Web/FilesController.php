<?php

namespace App\Http\Controllers\Web;

use App\Http\Config\ErrorCode;
use App\Library\UEditorUpload;
use App\Models\Admin\Configs;
use App\Models\Admin\Picture;
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
        if ($this->returnData['code'] > 0){
            return response()->json($this->returnData);
        }
        $con = Configs::first();
        $env = $con->env;
        if ($env == 'CLOUD'){  //配置云开发版
            $base64_img = trim($request->post('picture',''));
            if (!$base64_img){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '缺少文件';
                return response()->json($this->returnData);
            }
            $type = ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp'];
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
            $tmpName = time() . rand(10000, 99999);
            $newFile = $tmpName . '.' . strtolower(str_replace("image/", "", $files['type']));
            $cloud_file = '/uploads/picture/'.$time;
            $img_name = $cloud_file.'/'.$newFile;
            $pictureModel = new Picture();
            $url = $pictureModel->uploadImg($img_name,$temp_file);
            if (!$url){
                $this->returnData = ErrorCode::$admin_enum['params_error'];
                $this->returnData['msg'] = '上传图片失败';
                return response()->json($this->returnData);
            }
            $url['ObjectURL'] = str_ireplace('http://crm-1251017581.cos.ap-chengdu.myqcloud.com','',$url['ObjectURL']);
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['title'] = $img_name;
            $result['data']['url'] = $this->processingPictures($url['ObjectURL']);
            $result['data']['type'] = $files['type'];
            $result['data']['id'] = $url['ObjectURL'];
        } else{
            $base64_img = $request->file('picture');
            $res = (new UploadFile([
                'upload_dir' => './uploads/picture/',
                'type'       => ['image/jpg', 'image/png', 'image/jpeg', 'image/bmp']
            ]))->upload($base64_img);
            if($res['code'] > 0) {
                return response()->json($res);
            }
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data']['title'] = $res['name'];
            $result['data']['url'] = $this->processingPictures($res['url']);
            $result['data']['type'] = $res['type'];
            $result['data']['id'] = $res['url'];
        }
        return response()->json($result);
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

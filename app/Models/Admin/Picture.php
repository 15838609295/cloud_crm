<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Qcloud\Cos\Client;

class Picture extends Model
{
    protected $table='picture';


    //腾讯云COS存储图片
    function uploadImg($fileName,$realPath){
        global $scf_data;
        if ($scf_data['IS_SCF'] === true){
            $bucket = $scf_data['system']['bucketConfig']['bucket'];
        }else{
            $bucket = '';
        }
        require  base_path().'/vendor/cos-php-sdk/autoload.php';
        $cosClient = new Client(config('app.tengxunyun'));
        try {
            $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' =>  $fileName,
                    'Body' => fopen($realPath, 'rb'),
                    'ServerSideEncryption' => 'AES256')
            );
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    //腾讯云COS获取存储桶文件内容
    public function getObgect($file_url){
        global $scf_data;
        if ($scf_data['IS_SCF'] === true){
            $bucket = $scf_data['system']['bucketConfig']['bucket'];
        }else{
            $bucket = '';
        }
        require  base_path().'/vendor/cos-php-sdk/autoload.php';
        $cosClient = new Client(config('app.tengxunyun'));
        try {
            $result = $cosClient->getObject(array(
                'Bucket' => $bucket,
                'Key' => $file_url
            ));
            //获取成功 删除
            $cosClient->deleteObject(array(
                'Bucket' => $bucket,
                'Key' => $file_url
            ));
            return $result['Body'];
        } catch (\Exception $e) {
            return $e;
        }
    }
}

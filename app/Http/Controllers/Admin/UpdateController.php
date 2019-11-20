<?php
namespace App\Http\Controllers\Admin;

use App\Http\Config\ErrorCode;
use App\Http\Controllers\Controller;
use App\Library\Fileutil;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{

    public function __construct()
    {
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        /*--- end 跨域测试用---*/
    }

    //检查版本信息
    public function chack_version()
    {
        $edition = fopen('version.txt', 'r');  //获取版本信息
        $version = fread($edition, filesize("version.txt"));
        fclose($edition);
        //去查询有没有新版本
        $data = array(
            'project_name' => 'testcrm99',
            'version_number' => $version,
        );
        $data = http_build_query($data);
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencodedrn",
                "Content-Length: " . strlen($data) . "rn",
                'content' => $data
            )
        );
        $context = stream_context_create($opts);
        $res = file_get_contents('https://update.netbcloud.com/public/index.php/get/project', false, $context);
        $res = json_decode(trim($res, chr(239) . chr(187) . chr(191)), true);
        //是否有新版本
        if ($res['code'] == 0) {
            $result['code'] = 0;
            $result['status'] = 1;
            $result['msg'] = '有新版本';
            $result['data']['url'] = $res['data'][0]['route'];
            $result['data']['version_number'] = $res['data'][0]['version_number'];
            $result['data']['remarks'] = $res['data'][0]['remarks'];
            $result['data']['number'] = $res['data'][0]['number'];

            Cache::pull('number');
            Cache::add('number',$res['data'][0]['number'],120);  //保存需要下载压缩包的数量
            //判断之前有没有下载压缩包
            $pach = public_path().'/toUpdate';
            if (is_dir($pach)){
                $this->deleteDir($pach);  //删除这个目录下所有文件和文件夹
            }
            //删除重名的压缩包
            $new_name = date('Ymd',time()).'.zip';  //本地另存为新的压缩包
            $pach =  public_path().'/'.$new_name;
            if (is_file($pach)){
                unlink($pach);
            }

            return response()->json($result);
        } else {
            $result['code'] = 0;
            $result['status'] = 0;
            $result['msg'] = '您已经更新到最新版本';
            return response()->json($result);
        }
    }
    //更新之前禁止用户操作把token设置为过期
    public function settingOutdated(Request $request){
        $token = $request->post('token','');
        //查询没有过期的token
        $time = time();
        //设置管理token
        $admin_session = DB::table('admin_session')->where('expire_time','>',$time)->get();
        if ($admin_session){
            $admin_session = json_decode(json_encode($admin_session),true);
            foreach ($admin_session as $v){
                if ($v['session_id'] != $token){
                    $where['expire_time'] = $time - 60;
                    $token_res = DB::table('admin_session')->where('id',$v['id'])->update($where);
                    if (!$token_res){
                        $result['code'] = 1;
                        $result['msg'] = '站点关闭失败';
                        return response()->json($result);
                    }
                }
            }
        }
        //设置客户token过期时间
        $member_token = DB::table('member_session')->where('expire_time','>',$time)->get();
        if ($member_token){
            $member_token = json_decode(json_encode($member_token),true);
            foreach ($member_token as $v){
                $where['expire_time'] = $time - 60;
                $token_res = DB::table('member_session')->where('id',$v['id'])->update($where);
                if (!$token_res){
                    $result['code'] = 1;
                    $result['msg'] = '站点关闭失败';
                    return response()->json($result);
                }
            }
        }
        //关闭站点
        $site['site_status'] = 0;
        $con_res = DB::table('configs')->where('id',1)->update($site);
        if (!$con_res){
            $result['code'] = 1;
            $result['msg'] = '站点关闭失败';
            return response()->json($result);
        }
        $result['code'] = 0;
        $result['msg'] = '站点关闭成功';
        return response()->json($result);
    }
    //下载压缩包
    public function ready_download(Request $request){
        if($request->getMethod() == "OPTIONS"){
            return json_encode(ErrorCode::$admin_enum['fail']);
        }
        $number = Cache::get('number');
        if (!$number){
            $result['code'] = 0;
            $result['msg'] = '下载信息失效，请重新检查';
            return response()->json($result);
        }
        $file_number = $request->input('number','') -1;
        $file_path = $request->input('url','');
        $new_path  =str_replace('.zip','',$file_path);
        if ($number-1 == $file_number ){   //下载最后一个压缩包 之后把压缩包组合
            $new_path = $new_path.'.'.$file_number.'.zip';
            $name = date('Ymd',time()).'.'.$file_number.'.zip';
            $this->download_test($new_path,$name);
            //组合被切割的压缩包
            $new_name = date('Ymd',time()).'.zip';  //本地另存为新的压缩包
            $path = public_path().'/toUpdate/'.date('Ymd',time());
            Cache::pull('zipname');
            Cache::add('zipname',public_path().'/'.$new_name,360);
            $this->file_combine($path,$new_name);
             //获取压缩包所有文件列表
             $path = public_path().'/'.$new_name;
             $res = $this->dr_one_unzip($path);
             if ($res){
                 $result['code'] = 0;
                 $result['msg'] = '下载完成';
                 $result['data'] = $res;
                 return response()->json($result);
             }else{
                 $result['code'] = 0;
                 $result['msg'] = '压缩包损坏，请联系网站维护人员';
                 $result['data']['result'] = 1;
                 //删除压缩包和文件夹
                 $pach = public_path().'/toUpdate';
                 if (is_dir($pach)){
                     $this->deleteDir($pach);  //删除这个目录下所有文件和文件夹
                 }
                 //删除重名的压缩包
                 $new_name = date('Ymd',time()).'.zip';  //本地另存为新的压缩包
                 $pach =  public_path().'/'.$new_name;
                 if (is_file($pach)){
                     unlink($pach);
                 }
                 return response()->json($result);
             }
        }else{  //下载压缩包
            $new_path = $new_path.'.'.$file_number.'.zip';
            $name = date('Ymd',time()).'.'.$file_number.'.zip';
            $res = $this->download_test($new_path,$name);
            if ($res){
                $result['code'] = 0;
                $result['msg'] = '下载成功';
                $result['data'] = '';
                return response()->json($result);
            }else{
                $result['code'] = 1;
                $result['msg'] = '下载失败';
                $result['data'] = '';
                return response()->json($result);
            }
        }
    }

    /**
     * 解压zip文件到指定目录 前端循环调用
     * $filepath： 文件路径
     * $extractTo: 解压路径
     * $file_name: 要提取的文件
     */
    public function ready_unzip(Request $request){
        if($request->getMethod() == "OPTIONS"){
            return json_encode(ErrorCode::$admin_enum['fail']);
        }
        $filepath = Cache::get('zipname');
        $extractTo = public_path().'/toUpdate/';
        $file_name = $request->input('file_name');
        $zip = new ZipArchive;
        $res = $zip->open($filepath);
        if ($res === TRUE) {
            //解压缩到$extractTo指定的文件夹
            $zip->extractTo($extractTo,$file_name);
            $zip->close();
            $result['code'] = 0;
            $result['msg'] = '解压成功';
            return response()->json($result);
        } else {
            $result['code'] = 1;
            $result['msg'] = '解压失败';
            return response()->json($result);
        }
    }

    //获取解压的文件夹目录
    public function get_dir_name(){
         $dir = public_path().'/toUpdate/';
         $file = scandir($dir,1);
         $folder_name = [];
         foreach ($file as $v) {
             if (is_dir($dir.$v) && $v != '.' & $v !='..') {
                 $folder_name[] = $v;
             }
         }
         $result['code'] = 0;
         $result['msg'] = '请求成功';
         $result['data'] = $folder_name;
         return response()->json($result);
    }

    //移动文件夹
    public function move_dir(Request $request){
        if($request->getMethod() == "OPTIONS"){
            return json_encode(ErrorCode::$admin_enum['fail']);
        }
         $data = $request->input('dirName');
         $oldDir = '../'.$data.'/';
         $newDir = public_path().'/toUpdate/'.$data.'/';
         $fileutil = new Fileutil();
         $res = $fileutil->moveDir($newDir,$oldDir,false);
         if ($res){
             $result['code'] = 0;
             $result['msg'] = '移动成功';
             return response()->json($result);
         }else{
             $result['code'] = 1;
             $result['msg'] = '移动失败';
             return response()->json($result);
         }
    }

    //更新失败 删除下载的压缩包和更新目录（避免积累压缩包）
    public function update_fail(Request $request){
        if($request->getMethod() == "OPTIONS"){
            return json_encode(ErrorCode::$admin_enum['fail']);
        }
        $new_name = date('Ymd',time()).'.zip';  //本地另存为新的压缩包
        $path = public_path().'/'.$new_name;
        if (is_file($path)){
            unlink($path);
        }
        $file_path = public_path().'/toUpdate';
        if (is_dir($file_path)){
            $this->deleteDir($file_path);  //删除这个目录下所有文件和文件夹
        }
        //开启站点
        $where['site_status'] = 1;
        DB::table('configs')->where('id',1)->update($where);
        $data['code'] = 0;
        $data['msg'] = '更新完成';
        return response()->json($data);
    }

    //删除下载和解压的文件之后读取数据库文件修改数据库
    public function updateDatabase(Request $request){
        if($request->getMethod() == "OPTIONS"){
            return json_encode(ErrorCode::$admin_enum['fail']);
        }
        $target_folder = dirname(public_path());
        $target_file = $target_folder . '/app/Models/Scripts/newcrm.sql';
        //读取文件内容
        if (file_exists($target_file)){
            $_sql = file_get_contents($target_file);
            $_arr = explode(';', $_sql);
            //执行sql语句
            foreach ($_arr as $k=>$_value) {
                $num = strstr($_value, '--');
                if ($num){
                    continue;
                }else{
                    try{
                        $sql_message = @DB::statement($_value);
                        Log::info('result. ', array('result' => $sql_message,'data' =>$_value,'kay' => $k ));
                    }
                    catch(\Illuminate\Database\QueryException $ex) {
                        Log::info('error. ', array('result' => $_value,'key' => $k));
                    }
                }
            }
        }
        $result['code'] = 0;
        $result['msg'] = '已完成更新';
        //添加日志更新记录
        $edition = fopen('version.txt', 'r');  //获取版本信息
        $version = fread($edition, filesize("version.txt"));
        fclose($edition);
        $data = array(
            'project_name' => 'testcrm99',
            'version_number' => $version,
        );
        $data = http_build_query($data);
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencodedrn",
                "Content-Length: " . strlen($data) . "rn",
                'content' => $data
            )
        );
        $context = stream_context_create($opts);
        $res = file_get_contents('https://update.netbcloud.com/public/index.php/get/updateLog', false, $context);
        $res = json_decode(trim($res, chr(239) . chr(187) . chr(191)), true);
        if ($res['code'] != 1){
            $data_info['content'] = $res['data']['content'];
            $data_info['title'] = $res['data']['title'];
            $data_info['typeid'] = 3;
            $data_info['read_power'] = 1;
            $data_info['created_at'] = Carbon::now()->toDateTimeString();
            DB::table('articles')->insert($data_info);
        }
        //开启站点
        $where['site_status'] = 1;
        DB::table('configs')->where('id',1)->update($where);
        return response()->json($result);
    }


    /**
     *下载ZIP
     * $res 目标文件路径
     *$name 保存本地的文件名
     */
    public function download_test($res,$name){
        ob_start();
        readfile($res);
        $content = ob_get_contents();
        ob_end_clean();
        //文件大小
        $pach = public_path().'/toUpdate';
        if (!is_dir($pach)){  //不存在就创建文件夹
            mkdir($pach);
        }
        $fp2 = @fopen($pach . '/' . $name, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        return true;
    }

    //删除指定文件夹
    public function deleteDir($dir){
        if (!$handle = @opendir($dir)) {
            return false;
        }
        while (false !== ($file = readdir($handle))) {
            if ($file !== "." && $file !== "..") {
                $file = $dir . '/' . $file;
                if (is_dir($file)) {
                    $this->deleteDir($file);
                }else{
                    @unlink($file);
                }
            }
        }
        @rmdir($dir);
        return true;
    }

    /*
    * 合并文件
    * 如果合并后的文件为 CPCUxcp111.flv.0.esplit
    * 则 file=CPCUxcp111.flv，不包含.x.esplit后缀
    * save_file为另存为的文件名
    */
    public function file_combine($file,$save_file=''){
        $filename=basename($file);
        $filepath=dirname($file).'/';
        $block_info=array();
        for($i=0;;$i++){
            if(file_exists($file.'.'.$i.'.zip') && filesize($file.'.'.$i.'.zip')>0){
                $block_info[]=$file.'.'.$i.'.zip';
            }else{
                break;
            }
        }
        if($save_file){
            $fp = fopen($save_file,"wb");
        }else{
            $fp   = fopen($file,"wb");
        }
        foreach ($block_info as $block_file) {
            $handle = fopen($block_file,"rb");
            fwrite($fp,fread($handle,filesize($block_file)));
            fclose($handle);
            unset($handle);
        }
        fclose ($fp);
        unset($fp);
    }

    /*
    * 从zip压缩文件中提取文件列表
    * $filepath 要解压的zip
    */
    public function dr_one_unzip($filepath){
        $zip = new ZipArchive;
        $arr_file = [];
        $final_file = [];
        if ($zip->open($filepath) === TRUE) {
            for ($i=0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if(strstr($filename,'.')){
                    $arr_file[] = $filename;
                    if (count($arr_file) >10 ){
                        $final_file[] = $arr_file;
                        $arr_file = [];
                    }
                }
            }
            $zip->close();
            $final_file[] = $arr_file; //最后不足10个 补充到数组中
            return $final_file;
        } else {
            return false;
        }
    }

    /**
     * php备份数据库 获取表名
     */
    public function getDbTableNames(){
        try{
            //查看数据库更新文件，是否有表或数据更新
            $target_folder = dirname(public_path());
            $target_file = $target_folder . '/app/Models/Scripts/newcrm.sql';
            //读取文件内容
            $sql = file_get_contents($target_file);
            if (!$sql){  //如果没有内容则不必备份数据库
                $result['code'] = 0;
                $result['msg'] = '请求成功';
                $result['data'] = [];
                return response()->json($result);
            }
            //获取指定的数据库备份
            include_once ('database.php');
            $data = [];
            foreach ($arr as $k=>$v){
                $count = DB::table($v)->count();
                if ($count == 0){
                    $data[$k]['table'] = $v;
                    $data[$k]['number'] = 1;
                }else{
                    $number = ceil($count/10000);
                    $data[$k]['table'] = $v;
                    $data[$k]['number'] = $number;
                }
            }
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            $result['data'] = $data;
            return response()->json($result);
        }
        catch(\Illuminate\Database\QueryException $ex) {
            $result['code'] = 1;
            $result['msg'] = '备份失败';
            return response()->json($result);
        }
    }

    /*
     * 备份表结构
     * $table_name 表名
     * */
    public function backupsTableName(Request $request){
        $tables = $request->input('table','');
        $number = $request->input('number',1);
        try{
            $mysql = '';
            //获取表结构
            if ($number == 1){
                $table_res = DB::select("show create table $tables");
                $table_res[0] = json_decode(json_encode($table_res[0]),1);
                //备份表结构
                $isDropInfo   = "DROP TABLE IF EXISTS `" . $tables . "`;\r\n";
                $mysql .= $isDropInfo.$table_res[0]["Create Table"].";\r\n";
            }
            $count = DB::table($tables)->count();
            if (!$count){
                //创建备份的sql文件
                $fileName = 'backups';
                $file_path = public_path().'/'.$fileName;
                if (!is_dir($file_path)){
                    mkdir($file_path);
                }
                $edition = fopen('version.txt', 'r');  //获取版本信息
                $version = fread($edition, filesize("version.txt"));
                fclose($edition);
                //按版本备份数据库
                $fileName = $file_path.'/'.date('Ymd').'MySQL_'.$version.'_bakeup_.sql';
                $myfile = fopen($fileName, "a+");
                fwrite($myfile, $mysql);
                fclose($myfile);
                $result['code'] = 0;
                $result['msg'] = '请求成功';
                return response()->json($result);
            }
            $start = ($number-1)*10000;
            $res = DB::table($tables)->select('*');
            $info_res = $res->skip($start)->take(10000)->get();
            $info_res = json_decode(json_encode($info_res),true);
            foreach ($info_res as $value){
                $sqlStr = '';
                foreach ($value as $v){
                    if(!$v){
                        $sqlStr .="'',";
                    }else{
                         if(strstr($v ,'\\')){
                             $v = str_replace('\\','',$v);
                         }
                        if (strstr($v ,"'")){
                            $v = str_replace("'","\'",$v);
                        }
                        if(strstr($v ,'"')){
                            $v = str_replace('"','\"',$v);
                        }
                        $sqlStr .= "'$v',";
                    }
                }
                $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 1);
                $mysql .= "INSERT INTO `".$tables."` VALUES (".$sqlStr.");\r\n";
            }
           //创建备份的sql文件
           $fileName = 'backups';
           $file_path = public_path().'/'.$fileName;
           if (!is_dir($file_path)){
               mkdir($file_path);
           }
           $edition = fopen('version.txt', 'r');  //获取版本信息
           $version = fread($edition, filesize("version.txt"));
           fclose($edition);
           //按版本备份数据库
           $fileName = $file_path.'/'.date('Ymd').'MySQL_'.$version.'_bakeup_.sql';
           $myfile = fopen($fileName, "a+");
           fwrite($myfile, $mysql);
           fclose($myfile);
            $result['code'] = 0;
            $result['msg'] = '请求成功';
            return response()->json($result);
        }
        catch(\Illuminate\Database\QueryException $ex) {
            $result['code'] = 1;
            $result['msg'] = '备份失败';
            return response()->json($result);
        }
    }

}


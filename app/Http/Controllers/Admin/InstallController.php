<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    public $result = array("code"=>0,'msg'=>'请求成功','data'=>"");

    public function __construct(Request $request){
        /*--- start 跨域测试用 (待删除) ---*/
        header('Access-Control-Allow-Origin: *');                                                                 // 允许任意域名发起的跨域请求
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        if($request->getMethod() == "OPTIONS"){
            $this->result['code'] = 99;
            $this->result['msg'] = '错误';
            return $this->result;
        }
    }



    public function index(){
        if($this->result['code'] > 0){
            return response()->json($this->result);
        }
        if (!is_file(base_path() . "/install.lock") || !is_file(base_path().'/vue.sql')){
            $data['file'] = 'sql文件不存在';
            $data['status'] = 'no';
            $list['list'] = [$data];
            $list['system_status'] = 1;
            $this->result['data'] = $list;
        }else{
            $data['file'] = '文件存在';
            $data['status'] = 'yes';
            $list['list'] = [$data];
            $list['system_status'] = 0;
            $this->result['data'] = $list;
        }
        return response()->json($this->result);
    }

    public function testing(){
        $system_status = 0;
        $s = php_uname('s');//获取系统类型
        $sysos = $_SERVER["SERVER_SOFTWARE"];//获取php版本及运行环境
        $phpinfo = PHP_VERSION;//获取PHP信息
        $data[0]['name'] = '操作系统';
        $data[0]['local'] = $s;
        $data[0]['proposal'] = '类Unix';
        if (in_array($data[0]['local'],['Windows NT','Linux'])){
            $data[0]['status'] = 'yes';
        }else{
            $data[0]['status'] = 'no';
        }
        $data[1]['name'] = 'php版本';
        $data[1]['local'] = $phpinfo;
        $data[1]['proposal'] = '7.1';
        $target_version = [7,1,0];
        if ($phpinfo){
            $this_version = explode('.',$phpinfo);
            if ((int)$this_version[0] < $target_version[0]){
                $data['status'] = 'no';
                $system_status = 1;
            }else{
                if ((int)$this_version[1] < $target_version[1]){
                    $data[1]['status'] = 'no';
                    $system_status = 1;
                }else{
                    $data[1]['status'] = 'yes';
                }
            }
        }
        $data[2]['name'] = 'jd库';
        $data[2]['proposal'] = '开启';
        if (get_extension_funcs('gd')){
            $data[2]['local'] = '开启';
            $data[2]['status'] = 'yes';
        }else{
            $data[2]['local'] = '未安装';
            $data[2]['status'] = 'no';
            $system_status = 1;
        }
        $data[3]['name'] = 'pdo库';
        $data[3]['proposal'] = '开启';
        if (get_extension_funcs('pdo')){
            $data[3]['local'] = '开启';
            $data[3]['status'] = 'yes';
        }else{
            $data['local'] = '未安装';
            $data['status'] = 'no';
            $system_status = 1;
        }
        $data[4]['name'] = 'openssl库';
        $data[4]['proposal'] = '开启';
        if (get_extension_funcs('openssl')){
            $data[4]['local'] = '开启';
            $data[4]['status'] = 'yes';
        }else{
            $data[4]['local'] = '未安装';
            $data[4]['status'] = 'no';
            $system_status = 1;
        }
        $data[5]['name'] = 'curl库';
        $data[5]['proposal'] = '开启';
        if (get_extension_funcs('curl') ){
            $data[5]['local'] = '开启';
            $data[5]['status'] = 'yes';
        }else{
            $data[5]['local'] = '未安装';
            $data[5]['status'] = 'no';
            $system_status = 1;
        }
        $data[6]['name'] = 'xml库';
        $data[6]['proposal'] = '开启';
        if (get_extension_funcs('xml') ){
            $data[6]['local'] = '开启';
            $data[6]['status'] = 'yes';
        }else{
            $data[6]['local'] = '未安装';
            $data[6]['status'] = 'no';
            $system_status = 1;
        }
        $data[7]['name'] = 'zip库';
        $data[7]['proposal'] = '开启';
        if (get_extension_funcs('zip') ){
            $data[7]['local'] = '开启';
            $data[7]['status'] = 'yes';
        }else{
            $data[7]['local'] = '未安装';
            $data[7]['status'] = 'no';
            $system_status = 1;
        }
        $data[8]['name'] = '附件上传';
        $data[8]['proposal'] = '2M';
        $data[8]['local'] = ini_get('upload_max_filesize');
        $proposal_upload = substr($data[8]['proposal'],0,strlen($data[8]['proposal'])-1);
        $local_uplpad = substr($data[8]['local'],0,strlen($data[8]['local'])-1);
        if ((int)$local_uplpad > (int)$proposal_upload){
            $data[8]['status'] = 'yes';
        }else{
            $data[8]['status'] = 'no';
            $system_status = 1;
        }
        $list['list'] = array_values($data);
        $list['system_status'] = $system_status;
        $this->result['data'] = $list;
        return response()->json($this->result);
    }

    public function checkDir(){
        if($this->result['code'] > 0){
            return response()->json($this->result);
        }
        $system_status = 0;
        $items = [
            ['dir'=>base_path().'/app','dir_name' => 'app','status' => ''],
            ['dir'=>base_path().'/config','dir_name' => 'config','status' => ''],
            ['dir'=>base_path().'/public','dir_name' => 'public','status' => ''],
            ['dir'=>base_path().'/routes','dir_name' => 'routes','status' => ''],
            ['dir'=>base_path().'/vendor','dir_name' => 'vendor','status' => ''],
            ['dir'=>base_path().'/storage','dir_name' => 'storage','status' => ''],
        ];
        foreach ($items as &$v){
            $res = $this->is_really_writable($v['dir']);
            if ($res){
                $v['status'] = 'yes';
            }else{
                $v['status'] = 'no';
                $system_status = 1;
            }
        }
        $list['list'] = $items;
        $list['system_status'] = $system_status;
        $this->result['data'] = $list;
        return response()->json($this->result);
    }

    //数据库配置填写
    public function mkDatabase(Request $request){
        if($this->result['code'] > 0){
            return response()->json($this->result);
        }
        $host = $request->input('host','');
        $port = $request->input('port','');
        $database = $request->input('database','');
        $username = $request->input('username','');
        $password = $request->input('password','');


        if ($host == '' || $port == '' || $database == '' || $username == '' || $password == ''){
            $this->result['code'] = 1;
            $this->result['msg'] = '请完善数据库配置';
            return response()->json($this->result);
        }
        if (is_file(base_path() . "/config/database.php")){
            $config = <<<INFO
<?php
return [

    'fetch' => PDO::FETCH_OBJ,

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', 'crm'),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '{$host}'),
            'port' => env('DB_PORT', '{$port}'),
            'database' => env('DB_DATABASE', '{$database}'),
            'username' => env('DB_USERNAME', '{$username}'),
            'password' => env('DB_PASSWORD', '{$password}'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

    ],
    
    'migrations' => 'migrations',
    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
INFO;
            if (file_put_contents(base_path() . "/config/database.php",$config)){
                //创建数据库
                $conn = new \mysqli($host . ":" . $port, $username, $password);
                // 检测连接
                if ($conn->connect_error) {
                    $data['code'] = 1;
                    $data['msg'] = '数据库配置无效';
                    return response()->json($data);
                }
                if (!mysqli_select_db($conn, $database)) {
                    $sql = "CREATE DATABASE " . $database;
                    if ($conn->query($sql) != TRUE) {
                        $data['code'] = 1;
                        $data['msg'] = '创建数据库失败';
                        $data['data'] = '';
                        return response()->json($data);
                    }
                }
                $data['code'] = 0;
                $data['msg'] = '数据库配置成功';
                $conn->close();
                return response()->json($data);
            }else{
                $data['code'] = 1;
                $data['msg'] = '数据库配置写入失败，请重新填写';
                return response()->json($data);
            }
        }else{
            $data['code'] = 1;
            $data['msg'] = '文件损坏，请重新下载';
            return response()->json($data);
        }

    }

    //初始化数据库
    public function formatDataBase(){
        if($this->result['code'] > 0){
            return response()->json($this->result);
        }

        $target_file = base_path() . "/vue.sql";
        $install_file = base_path() . "/public/install";
        $install_lock = base_path() . "/install.lock";
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $passwd = config('database.connections.mysql.password');
        $dbname = config('database.connections.mysql.database');

        //连接数据库
        $conn = new \mysqli($host . ":" . $port, $username, $passwd, $dbname);
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
                        //执行sql语句
                        $conn->query($_value . ';');
                    }
                    catch(\Illuminate\Database\QueryException $ex) {
                        Log::info('error. ', array('result' => $_value,'key' => $k));
                        $this->result['code'] = 1;
                        $this->result['msg'] = '初始化数据库失败';
                    }

                }
            }
            $conn->close();
            //数据库安装成功删除sql文件
            @unlink($target_file);
            @unlink($install_lock);
            $this->deleteDir($install_file);
        }else{
            $this->result['code'] = 1;
            $this->result['msg'] = '文件缺失，请重新下载安装包';
        }
        $this->result['data'] = ['url' => '/public/index.html'];
        return response()->json($this->result);
    }

    //检测文件夹读写权限
    private function is_really_writable($file){
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE) {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand(1,100) . mt_rand(1,100));
            if (($fp = @fopen($file, "w+")) === FALSE) {
                return FALSE;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
        } elseif (!is_file($file) OR ($fp = @fopen($file, "r+")) === FALSE) {
            fclose($fp);
            return FALSE;
        }
        return TRUE;
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
}

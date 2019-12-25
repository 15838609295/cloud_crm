<?php
$scf_data = [];
function main_handler($event, $context) {
    global $scf_data;
    $scf_data['IS_SCF'] = true;
    print "start main handler\n";
    // 处理php文件
    print "start main PHP\n";
    system("mkdir -p /tmp/cache");
    system("mkdir -p /tmp/framework/sessions");
    system("mkdir -p /tmp/framework/cache");
    system("mkdir -p /tmp/framework/views");
    system("chmod -R 755 /tmp");
    system("/var/lang/php7/bin/php -v");
    print "start main laravel\n";
    //laravel框架启动
    require __DIR__.'/bootstrap/autoload.php';
    $app = require __DIR__.'/bootstrap/app.php';


    //处理定时任务
    if(isset($event->Type) && $event->Type == "Timer"){ // 定时触发器
        $path = '/admin/test'; // 路由
        $event->httpMethod = 'POST';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::create($path,$event->httpMethod,[], [], [], []);
        $response = $kernel->handle($request);
        $content = $response->getContent();
        \Illuminate\Support\Facades\DB::disconnect();
        $headers = [
            'Content-Type'  => 'application/json;charset=utf-8',
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'x-requested-with,content-type',
        ];
        return array(
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => $headers,
            'body' => $content
        );
    }else{
        if (is_file(__DIR__ . "/wsConfig.json")) {
            $wsConfig = json_decode(file_get_contents(__DIR__ . '/wsConfig.json'), 1);
            foreach ($wsConfig as $k => $v) {
                $scf_data[$k] = $v;
            }
            unset($wsConfig);
        }
        //腾讯云地图API key
        $scf_data['apiKey'] = '';
        //获取请求域名
        $array = get_object_vars($event->headers);
        if (isset($array['referer'])){
            $scf_data['host'] = $array['referer'];
        }else{
            $scf_data['host'] = '';
        }
        //获取ip地址
        if (isset($event->requestContext->sourceIp)){
            $scf_data['ip'] = $event->requestContext->sourceIp;
        }else{
            $scf_data['ip'] = '';
        }
        if ($event->httpMethod == 'OPTIONS') {         //过滤OPTIONS请求方式
            $headers = getHeaders();
            $content = ['code' => '99', 'msg' => 'error', 'data' => ''];
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => json_encode($content)
            );
        }
        if ($event->path == '/createDatabase') {       //创建数据库
            CreateDatabase();
            insert();
            $data['code'] = 0;
            $data['msg'] = '初始化成功';
            $headers = getHeaders();
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => json_encode($data)
            );
        }else if ($event->path == '/getUpgradeData') {   //更新数据库
            getUpgradeData();
            $data['code'] = 0;
            $data['msg'] = '更新数据库成功';
            $headers = getHeaders();
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => json_encode($data)
            );
        }

        if ($event->path == "/") {
            $event->path = '/index.html';
        }
        if ($event->path == "/agent/") {
            $event->path = '/agent/index.html';
        }
        if ($event->path == "/manage/") {
            $event->path = '/manage/index.html';
        }
        $event->path = str_replace("//", "/", $event->path);

        // 处理js, css文件
        if (preg_match('#\.html.*|\.js.*|\.css.*|\.html.*#', $event->path)) {
            $filename = "/var/user/public" . $event->path;
            echo $filename;
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            $headers = [
                'Content-Type' => '',
                'Cache-Control' => "max-age=8640000",
                'Accept-Ranges' => 'bytes',
            ];
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => $contents
            );
        }
        // 处理图片
        if (preg_match('#\.gif.*|\.jpg.*|\.png.*|\.jepg.*|\.swf.*|\.bmp.*|\.ico.*#', $event->path)) {
            $filename = "/var/user/public" . $event->path;
            echo $filename;
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            $headers = [
                'Content-Type' => '',
                'Cache-Control' => "max-age=86400",
            ];
            return array(
                "isBase64Encoded" => true,
                "statusCode" => 200,
                "headers" => $headers,
                "body" => base64_encode($contents),
            );
        }
        //获取提交信息
        $req = '';
        $res = '';
        if ($event->httpMethod == 'GET') {
            $req = $event->queryString;
            $req = json_encode($req);
        } else if ($event->httpMethod == 'POST') {
            $req = $event->body;
            $res = $event->queryString;   //当post请求URL带参数时处理
            $res = json_encode($res);
        }
        //去除{}在判断是否为空
        $new = str_replace("{", "", $req);
        $new_req = str_replace("}", "", $new);

        //解析提交的参数
        $data = [];
        if($new_req){
            $data = !empty($req) ? json_decode($req, true) : [];
            if(!$data){
                $data = parseData($req,$array);   //解析form-data数据
            }
        }
        if($res){
            $info = !empty($res) ? json_decode($res, true) : [];
            $data = array_merge($data,$info);
        }
        //企业微信登陆
        if($event->path == '/admin/crm.netbcloud.com'){
            $path = '/admin/qy/login'; // 路由
            $event->httpMethod = 'GET';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $request = \Illuminate\Http\Request::create($path,$event->httpMethod,$data, [], [], []);
            $response = $kernel->handle($request);
            $content = $response->getContent();
            \Illuminate\Support\Facades\DB::disconnect();
            $headers = [
                'Content-Type'  => 'text/html;charset=utf-8',
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Headers' => 'x-requested-with,content-type',
            ];
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => $content
            );
        }
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::create($event->path,$event->httpMethod,$data, [], [], []);
        $response = $kernel->handle(
            $request
        );
        $content = $response->getContent();
        //释放数据库连接
        \Illuminate\Support\Facades\DB::disconnect();
        //获取返回结果
        $result = json_decode($content, true);
        //规定code=3为下载信息
        if (isset($result['code']) && $result['code'] == 3) {
            $filename = $result['data'];
            $handle = fopen($filename, 'r');
            $re = fread($handle, filesize($filename));
            return array(
                'isBase64Encoded' => true,
                'statusCode' => 200,
                'headers' => array(
                    "Content-type" => "application/octet-stream",
                    "Content-Disposition" => "attachment; filename=" . $result['name'],
                    'Accept-Ranges' => 'bytes',
                ),
                'body' => base64_encode($re),
            );
        }
        $headers = getHeaders();
        return array(
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => $headers,
            'body' => $content
        );
    }
}

//form-data参数解析为数组
function parseData($data, $array){
    $formDataTag = "multipart/form-data; boundary=";
    $key = "--" . str_replace($formDataTag, "", $array['content-type']);
    $params = preg_split("/[\\s]*${key}-*[\\s]*/", $data);
    array_pop($params);
    array_shift($params);
    $requestParam = [];
    foreach ($params as $str) {
        $strArr = explode("\r\n\r\n", $str);
        if (count($strArr) < 2) {
            $strArr = explode("\n\n", $str);
        }
        $string = preg_replace("/[\\s\\S]*Content-Disposition: form-data; name=\"([^\"]+)\"[\\s\\S]*/", "$1", $strArr);
        if ($string[0] === 'file') {
            // 文件类型
            $requestParam[$string[0]] = $string[1];
        } else {
            $requestParam[$string[0]] = $string[1];
        }
    }
    return $requestParam;
}

//创建数据库
function CreateDatabase(){
    global $scf_data;
    $host = $scf_data['system']['database']['hostname'];
    $port = $scf_data['system']['database']['hostPort'];
    $database = $scf_data['system']['database']['database'];
    $username = $scf_data['system']['database']['username'];
    $password = $scf_data['system']['database']['password'];
    $conn = new \mysqli($host . ":" . $port, $username, $password);
    // 检测连接
    if ($conn->connect_error) {
        $data['code'] = 1;
        $data['msg'] = '数据库配置无效';
        $headers = getHeaders();
        return array(
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => $headers,
            'body' => json_encode($data)
        );
    }
    if (!mysqli_select_db($conn, $database)) {
        $sql = "CREATE DATABASE " . $database;
        if ($conn->query($sql) != TRUE) {
            $data['code'] = 1;
            $data['msg'] = '创建数据库失败';
            $data['data'] = '';
            $headers = getHeaders();
            return array(
                'isBase64Encoded' => false,
                'statusCode' => 200,
                'headers' => $headers,
                'body' => json_encode($data)
            );
        }
    }
    $conn->close();
}

//插入数据库
function insert(){
    global $scf_data;
    $target_file =__DIR__ ."/vue.sql";
    if (is_file($target_file)){
        $_sql = file_get_contents($target_file);
        $_arr = explode(';', $_sql);
        $host = $scf_data['system']['database']['hostname'];
        $port = $scf_data['system']['database']['hostPort'];
        $dbname = $scf_data['system']['database']['database'];
        $username = $scf_data['system']['database']['username'];
        $passwd = $scf_data['system']['database']['password'];

        $conn = new \mysqli($host . ":" . $port, $username, $passwd, $dbname);
        //执行sql语句
        foreach ($_arr as $k=>$_value) {
            $conn->query($_value . ';');
        }
        $sql1 = "update configs set env='CLOUD' where id=1;";
        $conn->query($sql1);
        $conn->close();
        return true;
    }
}

//更新数据库
function getUpgradeData(){
    global $scf_data;
    $host = $scf_data['system']['database']['hostname'];
    $port = $scf_data['system']['database']['hostPort'];
    $database = $scf_data['system']['database']['database'];
    $username = $scf_data['system']['database']['username'];
    $password = $scf_data['system']['database']['password'];
    $conn = new \mysqli($host . ":" . $port, $username, $password);
    // 检测连接
    if ($conn->connect_error) {
        $data['code'] = 1;
        $data['msg'] = '数据库配置无效';
        $headers = getHeaders();
        return array(
            'isBase64Encoded' => false,
            'statusCode' => 200,
            'headers' => $headers,
            'body' => json_encode($data)
        );
    }
    if (!mysqli_select_db($conn, $database)) {
        //没有数据库 创建
        CreateDatabase();
        insert();
        return true;
    }
    $conn->close();
    include __DIR__ ."/database.php";
    $conn = new \mysqli($host . ":" . $port, $username, $password, $database);
    $sql = 'select `version` from `sys_version` order by id desc limit 1';
    $res = $conn->query($sql);
    $res = $res->fetch_array();
    if ($res){
        $len = (int)$res['version'];
    }else{
        $len = 0;
    }
    $verion = 0;
    if ($len == 0){
        foreach ($arr as $k=>$v) {
            foreach ($v as $_value){
                $conn->query($_value);
            }
            $verion = $k;
        }
    }else{
        foreach ($arr as $k=>$v) {
            if ($k > $len){
                foreach ($v as $_value){
                    $conn->query($_value);
                }
            }
            $verion = $k;
        }
    }
    $data['code'] = 1;
    $data['msg'] = '更新成功';
    //插入数据库
    if ($len != $verion){
        $time = date('Y-m-d H:i:s',time());
        $sql1 = "INSERT INTO `sys_version`(`version`,`status`,`create_time`) VALUES ('$verion',2,'$time');";
        $conn->query($sql1);
    }
    $conn->close();
    return true;
}

function getHeaders(){
    $headers = [
        'Content-Type' => 'application/json;charset=utf-8',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Headers' => 'x-requested-with,content-type',
    ];
    return $headers;
}

?>

<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;
use Illuminate\Support\Facades\DB;

class Tools
{
    /* 数组转xml */
    public static function arrayToXml($arr)
    {
        ksort($arr);
        $xml = '<xml>';
        foreach ($arr as $key => $val){
            if(is_array($val)){
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /* xml转数组 */
    public static function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    /* 获取总条数 */
    public static function total($obj, $fields)
    {
        $query = $obj->toSql();
        foreach ($fields as $key=>$value){
            if($key=='searchKey'){
                if(!is_array($value)){
                    return 0;
                }
                foreach ($value as $k=>$v){
                    $c_value = "'%".$v."%'";
                    $query = self::str_replace_once('?',$c_value,$query);
                }
            }
            $query = self::str_replace_once('?',$value,$query);
        }
        return DB::table(DB::raw("({$query}) as total"))->count();
    }

    /* 创建随机字符串 */
    public static function createNoncestr()
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($str),0,30);
    }

    /* 批量更新 */
    public static function updateBatch($data, $table_name)
    {
        if(!$table_name || empty($data)){
            return false;
        }
        $update_column = array_keys($data[0]);
        $reference_column = $update_column[0];
        unset($update_column[0]);
        $whereIn = "";
        $sql = "UPDATE `".$table_name."` SET `";
        foreach ( $update_column as $u_column ) {
            $sql .=  $u_column."` = CASE ";
            foreach( $data as $item ) {
                $sql .= "WHEN ". $reference_column ." = ". $item[$reference_column] ." THEN '". $item[$u_column] ."' ";
            }
            $sql .= "ELSE `". $u_column ."` END, ";
        }
        foreach( $data as $item ) {
            $whereIn .= "'". $item[$reference_column] ."', ";
        }
        $sql = rtrim($sql, ", ")." WHERE ".$reference_column." IN (".  rtrim($whereIn, ', ').")";
        return DB::update(DB::raw($sql));
    }

    /*curl 请求*/
    public function curlRequest($requestArgs, $requestUrl, $timeout = 30,$type="GET")
    {
        $dataUrlParam = http_build_query($requestArgs, "", "&");
        if($type=="POST"){
            $url = $requestUrl  ;
            return $this->request($url, $dataUrlParam, "POST", null, $timeout);
        }
        else{
            $url = $requestUrl ."?". $dataUrlParam;
            return $this->request($url, null, "GET", null, $timeout);
        }

    }

    public function request($url, $data = null, $type='POST', $referer = null, $timeout=30)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_URL, $url);
        if($type=='POST'){
            curl_setopt($ch,CURLOPT_POST,1);
            if ($data) {
                curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
            }
        }else{
            curl_setopt($ch,CURLOPT_HTTPGET,1);
        }
        if ($referer) curl_setopt($ch,CURLOPT_REFERER, $referer);
        curl_setopt($ch,CURLOPT_TIMEOUT, $timeout);
        ob_start();
        curl_exec($ch);
        $contents = ob_get_contents();
        ob_end_clean();
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $contents;
    }

    /* 自定义curl(待完善) */
    public static function curl($url,$data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        return $output;
    }

    /*生成GUID*/
    public static function createGUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return strtolower(str_replace("-","",trim(com_create_guid(), '{}')));
        }
        return strtolower(sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
    }

    /* 数组排序 */
    public function array_sort($array, $column, $sort = 'SORT_ASC')
    {
        $new_array = array();
        $sort_array = array();
        foreach($array as $key => $value){
            if(!is_array($value)){
                $sort_array[$key] = $value;
            }else{
                foreach($value as $new_key => $new_value){
                    if($new_key==$column){
                        $sort_array[$key] = $new_value;
                    }
                }
            }
        }
        switch($sort)
        {
            case 'SORT_ASC':
                asort($sort_array);
                break;
            case 'SORT_DESC':
                arsort($sort_array);
                break;
        }
        foreach ($sort_array as $key => $value) {
            $new_array[$key] = $array[$key];
        }
        return $new_array;
    }

    /* 参数格式化 */
    public static function stringFormat($str)
    {
        // replaces str "Hello {0}, {1}, {0}" with strings, based on
        // index in array
        $numArgs = func_num_args () - 1;
        if ($numArgs > 0) {
            $arg_list = array_slice ( func_get_args (), 1 );

            // start after $str
            for($i = 0; $i < $numArgs; $i ++) {
                $str = str_replace ( "{" . $i . "}", $arg_list [$i], $str );
            }
        }
        return $str;
    }

    /**
     *  获取客户端ip地址
     *  @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     */
    public static function get_client_ip($type = 0)
    {
        if(isset($_SERVER)){
            $ip = self::_getClientIPByServer();
        }else{
            $ip = self::_getClientIPByEnv();
        }
        $IPAddressNum = sprintf("%u",ip2long($ip));
        $ip   = $IPAddressNum ? array($ip, $IPAddressNum) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    public static function _getClientIPByServer()
    {
        $ip = '0.0.0.0';
        if (isset($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        }
        else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        }
        else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function _getClientIPByEnv()
    {
        $ip = '0.0.0.0';
        if (getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR')){
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED')){
            $ip = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR')){
            $ip = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED')){
            $ip = getenv('HTTP_FORWARDED');
        }
        else if(getenv('REMOTE_ADDR')){
            $ip = getenv('REMOTE_ADDR');
        }
        return $ip;
    }

    /* 迭代无限极分类 */
    public static function iterationTree($list, $id='id', $pid='pid',$root=0,$child='children')
    {
        $data = array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(!empty($list)){
                    $child = self::iterationTree($list,$id,$pid,$val[$id],$child);
                    if(!empty($child)){
                        $val['children']=$child;
                    }
                }
                $data[]=$val;
            }
        }
        return $data;
    }

    /* 只替换一次 */
    public static function str_replace_once($needle, $replace, $haystack)
    {
        $strpos = strpos($haystack, $needle);
        if ($strpos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $strpos, strlen($needle));
    }
}
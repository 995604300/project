<?php
error_reporting(E_ERROR | E_PARSE );
// 应用公共文件

/**
 * 语言包数组 Opt格式
 * @return option;
 * @author liwqbj   2018-1-31 18:00:17
 * */
function langOpt($arr,$key=''){
    $res = $arr;
    $str = '';
    foreach($res as $k=>$v){
        if($k == $key ){
            $str .= "<option value=$k selected='selected'>".$v."</option>";
        }else{
            $str .= "<option value=$k>".$v."</option>";
        }
    }
    return $str;
}

/**
 * 转下拉选框格式
 * @param array $arr 多维数组
 * @param int $id 选中id
 * @return string  下拉选框格式
 * @author liwqbj   2018-1-31 18:00:17
 * */
function opt($arr,$id){
    $str = '';
    foreach($arr as $k=>$v){
        if($k == $id ){
            $str .= "<option value=$v[id] selected='selected'>".$v['name']."</option>";
        }else{
            $str .= "<option value=$v[id]>".$v['name']."</option>";
        }
    }
    return $str;
}

/**
 * 随机生成验证码
 * @param int $length
 * @return int      随机字符串
 * @author liwqbj   2018-1-31 18:00:17
 */
function createSMSCode($length = 4){
    $min = pow(10 , ($length - 1));
    $max = pow(10, $length) - 1;
    return rand($min, $max);
}


function getCurl($url = '') {
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    var_dump($data);
}

/* PHP CURL HTTPS POST */
function curl_post_https($url, $data = []){ // 模拟提交数据函数
    $curl = curl_init();  //初始化
    curl_setopt($curl,CURLOPT_URL,$url);  //设置url
    curl_setopt($curl,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);  //设置http验证方法
    curl_setopt($curl,CURLOPT_HEADER,0);  //设置头信息
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);  //设置curl_exec获取的信息的返回方式
    curl_setopt($curl,CURLOPT_POST,1);  //设置发送方式为post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($data));// 必须为字符串
//    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);  //设置post的数据

    $result = curl_exec($curl);
    if($result === false){
        echo curl_errno($curl);
        var_dump(curl_error($curl));
        exit();
    }
    curl_close($curl);
    return $result;
}

function geturl($url = '') {
    $handle = stream_socket_client("udp://{$url}", $errno, $errstr);
    if (!$handle) {
        die("ERROR: {$errno} - {$errstr}\n");
    }
}

//打印OSS图片数据
function printImage($func, $imageFile)
{
    $array = getimagesize($imageFile);
    Common::println("$func, image width: " . $array[0]);
    Common::println("$func, image height: " . $array[1]);
    Common::println("$func, image type: " . ($array[2] === 2 ? 'jpg' : 'png'));
    Common::println("$func, image size: " . ceil(filesize($imageFile)));
}

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

function put() {
    $json_data = file_get_contents('php://input');
    return json_decode($json_data,true);
}


/**
 * Ajax方式返回数据到客户端
 * @param int $code     返回码：-1：错误，0：成功，1：用户未登录，2：用户没有权限，3：非法请求 4：非法用户
 * @param string $msg   返回码提示信息：error，sucess，not login in，without permission，illegal request，illegal user
 * @param int $time     执行时间
 * @param array $data   返回数据
 * @param int $type     AJAX返回数据格式：json、xml、jsonp、eval
 * @return string       返回指定格式字符串
 * @author              liwqbj 2018-04-03 15:10:42
 * @example             ajaxReturn();
 */
function returnData($code = -1, $msg = '', $time = 0, $data = array(), $type = '') {
    $code = $code;
    //$msg = empty($msg) ? Mod::app()->params['returnCode'][$code] : CHtml::encode($msg);
    //$callback = CHtml::encode($callback);
    //$type = empty($callback) ? (int)$type : 'JSONP';

    $new_data = array('error' => $code, 'message' => $msg, 'datetime' => $time.' ms');
    if($code == 0 && is_array($data) && !empty($data)) {
        //还原html字符 by liwqbj 2017/10/05
        foreach($data as $k => $v) {
            if(is_string($v)) {
                $data[$k] = htmlspecialchars_decode($v, ENT_NOQUOTES);
            }
        }
        if(is_array($data['dataList'])) {
            foreach($data['dataList'] as $k1 => $v1) {
                if (is_array($v1)) {
                    foreach ($v1 as $k2 => $v2) {
                        if(is_string($v2)) {
                            $data['dataList'][$k1][$k2] = htmlspecialchars_decode($v2, ENT_NOQUOTES);
                        }
                    }
                }
            }
        }
    }
    $new_data['data'] = $data;
    if(empty($type)) $type = config('default_ajax_return');
    switch (strtoupper($type)){
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($new_data));
        case 'XML'  :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($new_data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler  =   isset($_GET[config('VAR_JSONP_HANDLER')]) ? $_GET[config('VAR_JSONP_HANDLER')] : config('DEFAULT_JSONP_HANDLER');
            exit($handler.'('.json_encode($new_data).');');
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($new_data);
    }
}

/**
 * 获取当时时间，精确到毫秒
 * @author  liwqbj 2018-04-03 15:10:31
 */
function getMicrotime() {
    $time = explode(" ", microtime());
    return ($time[1] * 1000) + ($time[0] * 1000);
}

//生成随机（A-Z：a-z）的一位
function rand_str($length = 1, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') //abcdefghijklmnopqrstuvwxyz
{
    // 字符列表长度
    $chars_length = (strlen($chars) - 1);
    // 开始我们的字符串
    $string = $chars{rand(0, $chars_length)};
    // 生成随机字符串
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // 从我们的列表中抓取一个随机字符
        $r = $chars{rand(0, $chars_length)};

        // 确保同样的两个字符不会出现在彼此旁边。
        if ($r != $string{$i - 1}) $string .=  $r;
    }
    // 返回字符串
    return $string;
}

function Push($data,$sn = null)
{
    $inner_text_url = config('inner_url');
    var_dump($inner_text_url);exit;
    // 建立socket连接到内部推送端口
    $client = stream_socket_client('tcp://' . $inner_text_url, $errno, $errmsg, 1);
    // 推送的数据，包含uid字段，表示是给这个uid推送
    $data = array('status' => true, 'code' => '200', 'data' => $data,'sn'=>$sn);
    // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
    fwrite($client, json_encode($data) . "\n");
    // 读取推送结果
    $message =  fread($client, 8192);
}







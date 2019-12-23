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
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);  //设置post的数据
    $result = curl_exec($curl);
    if($result === false){
        echo curl_errno($curl);
        exit();
    }
    var_dump($result);
    curl_close($curl);
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

//生成订单号
function create_order_no($prefix = 'ORDER') {
    $orderSn = rand_str().time().sprintf('%02d', rand(0, 99999));   //  随机数 5位
    return $prefix.$orderSn;
}



/**
 * @param        $list    二维数组
 * @param string $pk      主键
 * @param string $pid     父id
 * @param string $child   子键名
 * @param int    $root
 * @return array
 * author: Wang YX
 */
function list_to_tree($list, $pk='id', $pid = 'parent_id', $child = 'child', $root = 0) {
    //创建Tree
    $tree = array();

    if (is_array($list)) {
        //创建基于主键的数组引用
        $refer = array();

        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }

        foreach ($list as $key => $data) {
            //判断是否存在parent
            $parantId = $data[$pid];

            if ($root == $parantId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parantId])) {
                    $parent = &$refer[$parantId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }

    return $tree;
}

/**
 * 获取微信OPENDID
 * @param string $code  前端提供code参数
 * @return mixed
 * @throws Exception
 * @author liwqbj 2018年7月16日 16:06:57
 */
function get_openid($code = "") {
    $appid = config('wechat_miniprograms.app_id');
    $appsecret = config('wechat_miniprograms.app_secret');
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code";
    $curl = curl_init(); //初始化curl
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  //从证书中检查SSL加密算法是否存在
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);
    $data = json_decode($tmpInfo,true);
    //结果检测
    if(empty($data['openid'])) {
        throw new Exception($data['errmsg']);
    }
    $openid = $data['openid'];
    return $openid;
}


/**
 * @param string $email     收件人邮箱
 * @param string $text      内容
 * @param int $type         类型（0短信验证码、1通知模版）
 * @return string           返回类型
 * @author liwqbj           2018年7月21日 11:16:03
 */
function function_email_type($email = '', $text = '', $type = 0) {
    $date_time = date("Y-m-d H:i:s",time());
    switch ($type)
    {
        case 0:

            break;
        case 1:
            $content = <<< EOT
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email</title>
<style>* {
    margin: 0;
    padding: 0
}

.f-left {
    float: left
}

.f-right {
    float: right
}

.title-box span {
    display: inline-block;
    padding: 5px 15px;
    background-color: #666;
    font-size: 13px;
    color: #fff;
    border-radius: 5px
}

.item {
    padding-left: 60px
}

.light-color {
    color: #ec984b
}

.item > div a {
    text-decoration: underline
}

.box {
    background-color: #efefef;
    font-family: 微软雅黑
}

.header {
    width: 100%;
    height: 60px;
    line-height: 60px;
    font-size: 20px;
    color: #fff;
    text-align: center;
    background-color: #252c35
}

.content {
    box-sizing: border-box;
    border-left: 1px solid #fff;
    border-right: 1px solid #fff;
    width: 1024px;
    margin: 0 auto
}

.content > div {
    padding: 20px 35px 0 30px;
    color: #444
}

.content .section1 {
    text-align: center;
    box-sizing: border-box;
    line-height: 80px;
    width: 1024px;
    height: 80px;
    font-size: 18px;
    padding: 0 25px;
    color: #252c35;
    background-color: #fff
}

.section1 img {
    margin-top: 28px
}

.section2 .item > p:first-child {
    font-size: 18px;
    margin: 18px auto 10px
}

.section2 .item > p:last-child {
    margin-top: 10px;
    padding-bottom: 30px
}

.section3, .section4, .section5 {
    background-color: #fff;
}

.section3 .item > p, .section5 .item p {
    font-size: 14px;
    line-height: 30px;
}

.section3 .item > p:first-child, .section5 .item > p:first-child {
    margin-top: 16px
}

.section3 .item > p:last-child, .section5 .item > p:last-child {
    margin-bottom: 26px
}

.section3 .item, .section4 .section4-item {
    overflow: hidden;
    border-bottom: 1px dashed #d9d9d9
}

.section5-content {
    overflow: hidden;
}

.section5 .item {
    overflow: hidden;
    box-sizing: border-box;
    width: calc(100% - 200px);
}

.section5 .aside {
    width: 200px;
    height: 100%;
    font-size: 12px;
    text-align: center;
}

.section5 .aside img {
    padding: 0 10px;
    border-left: 1px solid #d3d3d4;
}

.section5 .aside p {
    line-height: 25px;
}

.section5 .item > div {
    margin-top: 10px;
    margin-bottom: 110px
}

.footer {
    width: 100%;
    height: 117px;
    margin: 0 auto;
    box-sizing: border-box;
    padding: 45px 0 37px 0;
    background-color: #252c35;
    text-align: center;
    color: #ccc
}

.section4 .section4-item p {
    text-align: right;
    font-size: 16px;
    line-height: 30px;
}

.section4 .section4-item > div {
    color: #2484ce;
    font-size: 16px;
    padding: 35px 0 12px;
}

.section4 .section4-item > div a {
    display: inline-block;
    cursor: pointer;
}

.section4 .section4-item > div a:nth-child(2) {
    margin: 0 10px;
    padding: 0 10px;
    border-left: 1px solid #2484ce;
    border-right: 1px solid #2484ce;
}
</style>
</head>
<body>
<div class="box">
    <div class="header"> 邮件提醒</div>
    <div class="content">
        <div class="section1">
            <div class="f-left"><img src="http://cloud.rulaiyun.net/admin/public/uploads/ico/logo.png"/></div>
            <div class="f-right">如来云平台</div>
        </div>
        <div class="section2">
            <div class="title-box"><span>如来云系统通知</span></div>
            <div class="item">
                <p class="light-color"> Hi!尊敬的如来云管理员：{$email}</p>
                <p><span><b>{$text}</b></span><span>（你的登录邮箱为：{$email}）</span></p>
            </div>
        </div>
        <div class="section4">
            <div class="section4-item">
                <p>如来云运营团队</p>
                <p>{$date_time}</p>
                <div>
                    <a>官网网站</a>
                    <a>技术论坛</a>
                    <a>帮助中心</a>
                </div>
            </div>

        </div>
        <div class="section3">
            <div class="title-box"><span>注意事项</span></div>
            <div class="item">
                <p><span>该邮件为如来云系统邮件，请勿直接回复。</span></p>
                <p><span>为保证邮箱正常接收，请将《server@yunlaiyun.net》添加进你的通讯录。</span></p>
                <p><span>如果你并未发过此请求，可能是因为其他用户在注册帐户时而误输入了你的邮箱地址而使你收到了这封邮件，</span></p>
                <p><span>你可以放心忽略此封邮件，无需进行任何操作。 </span></p>
            </div>
        </div>
        <div class="section5">
            <div class="title-box"><span>团队简介</span></div>
            <div class="section5-content">
                <div class="item f-left">
                    <p>
                        如来云为安徽德纵信息技术有限公司旗下产品 团队成员多来自于知名网络公司(Meru、聚美物联网、腾讯、
                        中国联通、TP-LINK、中兴通信等)，拥有丰富的产品资历。 对全国城中村，工厂宿舍、建筑工地宿舍、医院、
                        休闲会所、高新 科技园区等场景WiFi运营模式熟识，有专研WiFi多年的研究员，也有痴迷流控的代码工，有
                        熟悉市场的产品人员，更少不了喜欢小怪 物的设计师。“为情怀的人心怀大爱走的长远”，这是团队成员抱着
                        一颗颗热忱的心，做着自己热爱的事业！
                    </p>
                </div>
                <div class="aside f-right">
                    <img src="http://cloud.rulaiyun.net/admin/public/uploads/ico/wexin.png"/>
                    <p>扫一扫关注公众号</p>
                    <p>或添加微信号 "如小德"</p>
                </div>
            </div>

        </div>
    </div>
    <div class="footer">
        <div>由如来云提供Portal认证服务 |  技术支持：0551-85200200</div>
        <div>© 2018 RuLaiYun. All Rights Reserved. Anhui desheng information</div>
    </div>
</div>
</body>
</html>
EOT;
            return $content;
            break;
        default:

    }
}

//加密
function phpEncrypt($data , $key , $iv) {
    return openssl_encrypt($data,'AES-128-CBC', $key,0 , $iv);
}

//解密
function phpDecrypt($data , $key ,$iv)
{
    return openssl_decrypt($data,'AES-128-CBC', $key,0 , $iv);
}
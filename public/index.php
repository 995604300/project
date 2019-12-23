<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 跨越请求设置
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: http://localhost:9527");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,HEAD,OPTIONS,PATCH");
header("Access-Control-Allow-Headers: *");

//ini_set('session.cookie_path', '/');
//ini_set('session.cookie_domain', '.rulaiyun.cn'); //注意domain.com换成你自己的域名
//ini_set('session.cookie_lifetime', '1800');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';

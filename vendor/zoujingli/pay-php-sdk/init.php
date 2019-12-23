<?php

// +----------------------------------------------------------------------
// | pay-php-sdk
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/pay-php-sdk
// +----------------------------------------------------------------------

spl_autoload_register(function ($class) {
    if (0 === stripos($class, 'Pay\\')) {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['\\', 'Pay/',], ['/', 'src/'], $class) . '.php';
        file_exists($filename) && include($filename);
    }
});
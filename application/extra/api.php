<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | Company: YG | User: ShouKun Liu  |  Email:24147287@qq.com  | Time:2017/3/10 10:17
// +----------------------------------------------------------------------
// | TITLE: this to do?
// +----------------------------------------------------------------------
return [
    'api_auth' => true,  //是否开启授权认证
    'auth_class' => \app\api\auth\OauthAuth::class, //授权认证类
    //'auth_class' => \app\admin\auth\BasicAuth::class, //授权认证类
    'api_debug' => false,//是否开启调试
];
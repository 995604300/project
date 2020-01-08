<?php

/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: login.php
 *      AUTH: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-20
 */

namespace app\api\controller;
use app\api\auth\OauthAuth;
use DawnApi\facade\ApiController;
use think\Request;

/**
 * Class Login
 * @title 登入登出接口
 * @url  http://localhost
 * @desc  登入登出接口
 * @version V1.0
 */
class Login extends ApiController
{
    //附加方法
    protected $extraActionList = [
        'login',
        'logout',
        'garble'
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [
        'login',
        'garble'
    ];

    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->user_model = Model('User');
    }
    /**
     * @title 运营者登录接口
     * @readme /doc/md/api/Login/login.md
     */
    public function login(Request $request) {
        $OauthAuth = new OauthAuth();
        return  $OauthAuth->accessToken($request);
    }

    /**
     * @title 运营者退出登录接口
     * @readme /doc/md/api/Login/logout.md
     */
    public function logout(Request $request) {
        $start = getMicrotime();
        $res = self::$app['auth']->clearAccessToken();
        if ($res) {
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start));
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start));
        }
    }

    /**
     * @title md5混淆
     * @readme /doc/md/api/Login/garble.md
     */
    public function garble(Request $request) {
        $start = getMicrotime();
        $res = ['apikey'=>md5('apikey'.date('Y-m-d'))];
        $end = getMicrotime();
        return $this->sendSuccess(($end - $start),$res);
    }

    public static function getRules()
    {
        $rules = [];
        //可以合并公共参数
        return $rules;
    }
}
<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Auth.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-09-16
 */
namespace app\api\auth;

use DawnApi\auth\Basic;
use think\Request;

class BasicAuth extends Basic
{
    public function getUser()
    {
        return $this->getUserInfo();
    }

    /**
     * 获取用户信息后 验证权限,
     * @param Request $request
     * @return bool
     */
    public function certification(Request $request)
    {
        return ($this->username == 'liwqbj' && $this->password == 'liwqbj336699') ? true : false;
    }

    /**
     * 获取用户信息
     * @return mixed
     */
    public function getUserInfo()
    {
        return [
            'client_id' => $this->username,//app_id
            'secret' => $this->password,
            'name' => 'test_client'
        ];
    }
}
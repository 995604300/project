<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Base.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-11
 */
namespace app\api\controller;
use DawnApi\facade\ApiController;
use gmars\rbac\Rbac;
use think\App;
use think\Session;
use think\Db;
use think\Model;
use think\Request;

class Base extends ApiController
{
    public  $apiAuth = true;                   //是否开启授权认证
    protected $error_code = 0;                 //接口状态码
    protected $error_message = "成功";         //接口状态信息
    protected $is_login = false;                //是否开启登录验证
    protected $rbac;                           //权限对象


    public function __construct()
    {
        $start = getMicrotime();
        parent::__construct();
        $this->rbac = new Rbac();
        //判断否开启登录验证
        if ($this->is_login) {
            $admin_info = session('admin_info');//用户信息
            if (empty($admin_info)) {
                $this->error_code = "-1";
                $this->error_message = "session过期！";
                $end = getMicrotime();
                returnData($this->error_code, $this->error_message, ($end - $start));
            }
            $role_id = Db::name('user_role')->where(['user_id' => $admin_info['id']])->value('role_id');
            $role = ['role'=>$this->rbac->getRole($role_id)]; //角色信息
            if (empty($role['role'])) {
                $admin_info['role'] = [];
                $this->admin_info = $admin_info;
                /*$this->error_code = "-1";
                $this->error_message = "请设置角色后登录！";
                $end = getMicrotime();
                returnData($this->error_code, $this->error_message, ($end - $start));*/
            } else {
                $this->admin_info = array_merge($admin_info,$role); //合并用户及角色信息
                defined('ROLE_ID') or define('ROLE_ID', $this->admin_info['role']['id']);
            }
        }
        //验证是否有权限访问
//        if ($this->admin_info['role']['id'] != 1) { //判断角色是否是超级管理员 //$this->admin_info['role']['id'] != 1
//            $path = '/'.\request()->module().'/'.\request()->controller().'/'.\request()->action();
//            if(request()->controller() != 'Publics'){
//                if (!$this->rbac->can($path,$this->admin_info['id'])) {
//                    $start = getMicrotime();
//                    $end = getMicrotime();
//                    returnData(-1, '没有权限操作!', ($end - $start));
//                }
//            }
//
//        }
    }

    public function Push($data,$sn = null)
    {
        $inner_text_url = '192.168.0.111:2001';
        // 建立socket连接到内部推送端口
        $client = stream_socket_client('tcp://' . $inner_text_url, $errno, $errmsg, 1);
        // 推送的数据，包含uid字段，表示是给这个uid推送
        $data = array('status' => true, 'code' => '200', 'data' => $data,'sn'=>$sn);
        // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
        fwrite($client, json_encode($data) . "\n");
        // 读取推送结果

        $message =  fread($client, 8192);
    }

    public function authenticate(Request $request)
    {
        // TODO: Implement authenticate() method.
        return true;
    }
}
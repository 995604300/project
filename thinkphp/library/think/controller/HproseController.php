<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\controller;
/**
 * ThinkPHP Hprose控制器类
 */
abstract class HproseController {
    // 允许访问的方法
    protected $allowMethodList  =   '';
    protected $crossDomain      =   false;
    protected $P3P              =   false;
    protected $get              =   true;
    protected $debug            =   false;

   /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        //导入类库
        Vendor('Hprose.HproseHttpServer');
        //实例化HproseHttpServer
        $server     =   new \HproseHttpServer();
        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','__call','_initialize'));
        }
        // 发布方法
        $server->addMethods($methods,$this);
        if( $this->debug ) {
            // 打开调试
            $server->setDebugEnabled(true);
        }
        // Hprose设置
        $server->setCrossDomainEnabled($this->crossDomain);
        $server->setP3PEnabled($this->P3P);
        $server->setGetEnabled($this->get);
        // 启动server
        $server->start();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args){}
}

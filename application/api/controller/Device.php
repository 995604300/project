<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Room.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-31
 */

namespace app\api\controller;
use think\Db;
use think\Request;
/**
 * Class Classroom
 * @title 教室管理
 * @url  localhost/api/Device
 * @desc 教室管理相关接口
 * @version 1.0
 */
class Device extends Base
{
    //附加方法
    protected $extraActionList = [

    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $device_model;

    public function __construct()
    {
        parent::__construct();
        $this->device_model = Model('Device');
    }

    /**
     * @title 设备列表列表

     * @readme /doc/md/api/Device/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $where = [];

       $type = $request->get('type');
       if (empty($type)) {
           $where['type'] = $type;
       }
       $list = $this
           ->device_model
           ->where($where)
           ->select();
       if ($list) {
           $list = collection($list)->toArray();
       }
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 教室的新增与编辑

     * @readme /doc/md/api/Device/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();
       $result = $this->device_model->allowField(true)->isUpdate(TRUE)->save($array_data);
       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }


    /**
     * 参数规则
     * @name 字段名称
     * @type 类型
     * @require 是否必须
     * @default 默认值
     * @desc 说明
     * @range 范围
     * @return array
     */
    public static function getRules()
    {
        $rules = [
            'index' => [
                'type' => ['name' => 'type', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '', 'range' => '',],
            ],
            'save' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'Title' => ['name' => 'Title', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '设备名称', 'range' => '',],
                'sbID' => ['name' => 'sbID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '序列号', 'range' => '',],
                'IP' => ['name' => 'IP', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'ip地址', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
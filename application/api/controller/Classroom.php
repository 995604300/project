<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Classroom.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-13
 */

namespace app\api\controller;
use think\Db;
use think\Request;
/**
 * Class Classroom
 * @title 教室管理
 * @url  localhost/api/Classroom
 * @desc 教室管理相关接口
 * @version 1.0
 */
class Classroom extends Base
{
    //附加方法
    protected $extraActionList = [

    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = ['save'];
    protected $classroom_model;

    public function __construct()
    {
        parent::__construct();
        $this->classroom_model = Model('Classroom');
    }

    /**
     * @title 教室列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classroom/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->get('size') : 10; //每页条数
       $where = [];

       $list = $this
           ->classroom_model
           ->where($where)
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 教室的新增与编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classroom/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();
       if (!empty($array_data['classroomName'])) {
           if (!empty($array_data['id'])) {
               $result = $this->classroom_model->allowField(true)->isUpdate(TRUE)->save($array_data);
           } else {
               $result = $this->classroom_model->allowField(true)->isUpdate(FALSE)->save($array_data);
           }

       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }

       if ($result) {
           Push(['info'=>'isUpdate']);
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 教室删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classroom/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->classroom_model->delAll($array['id']);
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }

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
                'page' => ['name' => 'page', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '页', 'range' => '',],
                'size' => ['name' => 'size', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '每页数据条数', 'range' => '',],
            ],
            'save' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '存在时为更新,否则为创建', 'range' => '',],
                'classroomName' => ['name' => 'classroomName', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '教室名称', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
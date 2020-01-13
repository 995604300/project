<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Lesson.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-16
 */

namespace app\api\controller;
use think\Db;
use think\Request;
/**
 * Class Lesson
 * @title 课程
 * @url  localhost/api/Lesson
 * @desc 考试类型相关接口
 * @version 1.0
 */
class Lesson extends Base
{
    //附加方法
    protected $extraActionList = [

    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $lesson_model;
    protected $lesson_class_model;

    public function __construct()
    {
        parent::__construct();
        $this->lesson_model = Model('Lesson');
        $this->lesson_class_model = Model('LessonClass');
    }

    /**
     * @title 课程列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Lesson/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $classId  = $request->get('classId');
       $classroomId  = $request->get('classroomId');
       if (!empty($classId)) {
           $list = $this
               ->lesson_class_model
               ->with('classes')
               ->alias('lc')
               ->field('l.*,cr.classroomName,classId')
               ->join('kx_php_lesson l','lc.lessonId=l.id','left')
               ->join('kx_php_classroom cr','l.classroomId=cr.id','left')
               ->where('classId',$classId)
               ->order(['date','startTime'])
               ->select();
       } else {
           if (!empty($classroomId))  {
               $where['classroomId'] = $classroomId;
           }
           $list = $this
               ->lesson_model
               ->with(['lessonClass'=>function($query){
                   $query->alias('a')->join('kx_php_class c','a.classId=c.id');
               },])
               ->alias('l')
               ->field('l.*,cr.classroomName')
               ->join('kx_php_classroom cr','l.classroomId=cr.id','left')
               ->where($where)
               ->order(['date DESC','startTime ASC'])
               ->select();
       }
       if ($list) {
           $list = collection($list)->toArray();
       } else {
           $list = '暂无数据';
       }
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }


    /**
     * @title 课程新增与编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Lesson/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
       $array_data = $request->post();
       if (!empty($array_data['date'])) {
           $number_wk = date("w",$array_data['date']);
           $array_data['week'] = $weekArr[$number_wk];
       }
       if (!empty($array_data['id'])) {
           $result = $this->lesson_model->allowField(true)->isUpdate(TRUE)->save($array_data);
           $lesson_id = $array_data['id'];
       } else {
           $result = $this->lesson_model->allowField(true)->isUpdate(FALSE)->save($array_data);
           $lesson_id =  $this->lesson_model->getLastInsID();
       }

       if ($result) {
           if (!empty($array_data['classId'])) {
               $this->lesson_class_model->where('lessonId',$lesson_id)->delete();
               foreach ($array_data['classId'] as $key=>$value) {
                   $save_data[$key]['classId'] = $value;
                   $save_data[$key]['lessonId'] = $lesson_id;
               }
               $this->lesson_class_model->isUpdate(FALSE)->saveAll($save_data);
           }
           Push(['info'=>'isUpdate']);
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 课程删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Lesson/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->lesson_model->delAll($array['id']);
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
                'date' => ['name' => 'date', 'type' => 'Date', 'require' => 'false', 'default' => '', 'desc' => '上课日期', 'range' => '',],
                'startTime' => ['name' => 'startTime', 'type' => 'date', 'require' => 'false', 'default' => '', 'desc' => '开始时间', 'range' => '',],
                'endTime' => ['name' => 'endTime', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '结束时间', 'range' => '',],
                'lessonName' => ['name' => 'lessonName', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '课程名称', 'range' => '',],
                'teacherName' => ['name' => 'teacherName', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '授课老师', 'range' => '',],
                'classId' => ['name' => 'classId', 'type' => 'array', 'require' => 'false', 'default' => '', 'desc' => '班级id', 'range' => '',],
                'classroomId' => ['name' => 'classroomId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '教室id', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
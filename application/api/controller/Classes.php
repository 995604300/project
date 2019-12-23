<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Classes.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-13
 */

namespace app\api\controller;
use think\Config;
use think\Db;
use think\Request;
/**
 * Class Classes
 * @title 教室管理
 * @url  localhost/api/Classes
 * @desc 班级管理相关接口
 * @version 1.0
 */
class Classes extends Base
{
    //附加方法
    protected $extraActionList = [
        'getColor',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $classes_model;
    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->classes_model = Model('Classes');
        $this->user_model = Model('User');
    }

    /**
     * @title 班级列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classes/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->get('size') : 10; //每页条数
       $where = [];

       $list = $this
           ->classes_model
           ->where($where)
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 班级的新增与编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classes/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();

       $array_data = $request->post();
           if (!empty($array_data['id'])) {
               $result = $this->classes_model->allowField(true)->isUpdate(TRUE)->save($array_data);
           } else {
               if (!empty($array_data['className'])) {
                   $color_all = Config::get('color');
                   $color_all = array_column($color_all,'code');
                   $color = $this->classes_model->field('color')->group('color')->select();
                   if (empty($color)) {
                       $array_data['color'] = $color_all[0];
                   } else {
                       $color = array_column(collection($color)->toArray(),'color');
                       $color = array_diff($color_all,$color);
                       if (!empty($color)) {
                           $array_data['color'] = reset($color);
                       } else {
                           $array_data['color'] = $color_all[0];
                       }
                   }
                   $result = $this->classes_model->allowField(true)->isUpdate(FALSE)->save($array_data);
               } else {
                   $end = getMicrotime();
                   return $this->sendError(($end - $start),1,'参数错误！');
               }
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
     * @title 班级删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classes/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->classes_model->delAll($array['id']);
           $this->user_model->where('ClassId','in',$array['id'])->delete();
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
     * @title 获取颜色列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Classes/getColor.md
     */
   public function getColor(Request $request){
       $start = getMicrotime();
       $color = Config::get('color');
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start),$color);

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
                'className' => ['name' => 'className', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '班级名称', 'range' => '',],
                'color' => ['name' => 'color', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '颜色(新增时不需要传值)', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'getColor' => [
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
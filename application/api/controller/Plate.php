<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Plate.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-24
 */

namespace app\api\controller;
use think\Db;
use think\Request;
/**
 * Class Plate
 * @title 教室管理
 * @url  localhost/api/Plate
 * @desc 教室管理相关接口
 * @version 1.0
 */
class Plate extends Base
{
    //附加方法
    protected $extraActionList = [

    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $plate_model;

    public function __construct()
    {
        parent::__construct();
        $this->plate_model = Model('Plate');
    }

    /**
     * @title 教室列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->get('size') : 10; //每页条数

       $list = $this
           ->plate_model
           ->with(['classroom','device'])
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 班牌的绑定
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();

       if (empty($array_data['sbID']) || empty($array_data['classroomId'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }
       $this->plate_model->where('sbID',$array_data['sbID'])->delete();
       $result = $this->plate_model->allowField(true)->isUpdate(FALSE)->save($array_data);

       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 班牌删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->plate_model->delAll($array['id']);
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
     * @title 设置班牌显示信息
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/setMessage.md
     */
   public function setMessage(Request $request){
       $start = getMicrotime();
       $title = $request->post('title');
       $content = $request->post('content');
       if (!empty($title)) {
           $save_array['title'] = $title;
       }
       if (!empty($content)) {
           $save_array['content'] = $content;
       }
       if (empty($save_array)) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }
       $res = Db::table('kx_php_plate_message')->where('id',1)->update($save_array);
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start),$res);
   }

    /**
     * @title 电子班牌打卡
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/record.md
     */
   public function record(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();
       if (empty($array_data['sbID'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),'参数错误!');
       }
       if (empty($array_data['KaiID'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),'参数错误!');
       }
       $array_data['recordDate'] = date('Y-m-d');
       $array_data['recordTime'] = date('H:i:s');
       $array_data['time'] = date('Y-m-d H:i:s');
       $result = Db::name('kx_kq_record')->allowField(true)->isUpdate(FALSE)->save($array_data);

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
                'sbID' => ['name' => 'SN', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '班牌唯一识别码', 'range' => '',],
                'classroomId' => ['name' => 'classroomId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '绑定教室', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'setMessage' => [
                'title' => ['name' => 'title', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'content' => ['name' => 'content', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
            ],
            'record' => [
                'sbID' => ['name' => 'sbID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '设备id', 'range' => '',],
                'KaiID' => ['name' => 'KaiID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '卡id', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
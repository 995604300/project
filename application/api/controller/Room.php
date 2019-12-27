<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Room.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-13
 */

namespace app\api\controller;

use Ramsey\Uuid\Uuid;
use think\Request;
/**
 * Class Room
 * @title 楼层总控台管理
 * @url  localhost/api/Room
 * @desc 班级管理相关接口
 * @version 1.0
 */
class Room extends Base
{
    //附加方法
    protected $extraActionList = [
        'floorList',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $room_model;

    public function __construct()
    {
        parent::__construct();
        $this->room_model = Model('Room');
    }

    /**
     * @title 房间列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->get('size') : 10; //每页条数
       $where['Type'] = 2;
       $where['FuJiSuSheID'] = $request->get('floorId');
       if (empty($where['FuJiSuSheID'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }
       $list = $this
           ->room_model
           ->alias('r')
           ->field('r.*,u.UserID,u.UserCode,u.UserName,u.RealName,u.PhoneNum,u.ClassId,u.RoleId')
           ->join('kx_jc_SuShe_DoorLock_Arrange a','r.DoorLockNO=a.DoorLockNO','left')
           ->join('kx_jc_ka k','a.DecKaID=k.DecKaID','left')
           ->join('kx_jc_yonghuka y','k.KaID=y.KaID','left')
           ->join('kx_jc_user u','y.UserID=y.UserID','left')
           ->where($where)
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 房间的新增与编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();

       $array_data = $request->post();
       if (!empty($array_data['id'])) {
           $result = $this->room_model->allowField(true)->isUpdate(TRUE)->save($array_data);
       } else {
           $uuid1 = Uuid::uuid1();
           $array_data['SuSheID'] = $uuid1->toString();
           $result = $this->room_model->allowField(true)->isUpdate(FALSE)->save($array_data);
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
     * @title 房间删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->room_model->delAll($array['id']);
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
     * @title 楼层列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/index.md
     */
    public function floorList(Request $request){
        $start = getMicrotime();
        $where['Type'] = 1;
        $list = $this
            ->room_model
            ->select();
        $list = collection($list)->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
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
                'floorId' => ['name' => 'floorId', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '楼层id', 'range' => '',],
            ],
            'save' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '存在时为更新,否则为创建', 'range' => '',],
                'roomName' => ['name' => 'roomName', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '房间名称', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'floorList' => [
            ],
        ];
        //可以合并公共参数
        return $rules;
    }
}
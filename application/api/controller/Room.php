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
use think\Db;
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
        'bindCard',
        'bindUser',
        'getUsers',
        'getUserCards',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $room_model;
    protected $ka_model;
    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->room_model = Model('Room');
        $this->ka_model = Model('Ka');
        $this->user_model = Model('User');
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
       $size = $request->get('size') ? $request->get('size') : 101; //每页条数
       $where['Type'] = 2;
       $floorId = $request->get('floorId');
       if (!empty($floorId)) {
           $where['FuJiSuSheID'] = $floorId;
       }

       $list = $this
           ->room_model
           ->where($where)
           ->order('SuSheMingCheng')
           ->paginate($size)
           ->toArray();
       foreach ($list['data'] as $key=>$value) {
           $res = Db::table('kx_jc_SuShe_DoorLock_Arrange')
             ->alias('a')
             ->field('u.UserID,u.UserCode,u.UserName,u.RealName,u.PhoneNum,u.ClassId,u.RoleId,c.color')
             ->join('kx_jc_ka k','a.DecKaID=k.DecKaID','left')
             ->join('kx_jc_yonghuka y','k.KaID=y.KaID','left')
             ->join('kx_jc_user u','y.UserID=u.UserID','left')
             ->join('kx_php_class c','c.id=u.ClassId','left')
             ->where('DoorLockNO',$value['DoorLockNO'])
             ->select();
           if ($res) {
               $list['data'][$key]['user'] = collection($res)->toArray();
           } else {
               $list['data'][$key]['user'] = [];
           }
       }

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
       for ($i=$array_data['SuSheMingChengStart'];$i<=$array_data['SuSheMingChengEnd'];$i++) {
           $uid = Uuid::uuid1();
           $save_data[] = [
               'SuSheMingCheng'=>$i,
               'FuJiSuSheID'=>$array_data['FuJiSuSheID'],
               'Type'=>$array_data['Type'],
               'SuSheID'=>$uid->toString(),
           ];
       }
       $result = $this->room_model->insertAll($save_data);

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
     * @title 房间编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/edit.md
     */
   public function edit(Request $request){
       $start = getMicrotime();
       $array = $request->get();

       if (!empty($array['id']) || !empty($array['genre'])) {
           $result = $this->room_model->isUpdate(true)->save($array);
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
     * @readme /doc/md/api/Room/floorList.md
     */
    public function floorList(Request $request){
        $start = getMicrotime();
        $where['Type'] = 1;
        $list = $this
            ->room_model
            ->where($where)
            ->order('XuHao')
            ->select();
        $list = collection($list)->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 获取未绑定房间的房间卡
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/getCards.md
     */
    public function getCards(Request $request){
        $start = getMicrotime();
        $list = $this
            ->ka_model
            ->alias('k')
            ->field('k.*,a.id')
            ->join('kx_jc_SuShe_DoorLock_Arrange a','a.DecKaID=k.DecKaID','left')
            ->where('id',null)
            ->select();
        $list = collection($list)->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 房间与卡绑定
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/bindCard.md
     */
    public function bindCard(Request $request){
        $start = getMicrotime();
        $array_data = $request->post();
        $array_data['CreateOn'] = '';
        $array_data['UserID'] = '';
        if (empty($array_data['DoorLockNO'] || empty($array_data['DecKaID']))) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误!');
        }
        $result = Db::table('kx_jc_SuShe_DoorLock_Arrange')->insert($array_data);

        if ($result) {
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start));
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start));
        }
    }

    /**
     * @title 去除卡与客房的关联
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/deleteCard.md
     */
    public function deleteCard(Request $request){
        $start = getMicrotime();
        $array_data = $request->post();
        if (empty($array_data['DoorLockNO'] || empty($array_data['DecKaID']))) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误!');
        }
        $result = Db::table('kx_jc_SuShe_DoorLock_Arrange')->where($array_data)->delete();

        if ($result) {
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start));
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start));
        }
    }


    /**
     * @title 卡与用户绑定
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/bindUser.md
     */
    public function bindUser(Request $request){
        $start = getMicrotime();
        $array_data = $request->post();
        if (empty($array_data['UserID'] || empty($array_data['KaID']))) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误!');
        }
        $array_data['JieShuShiJian'] = '2099-12-31 00:00:00.000';
        $array_data['KaiShiShiJian'] = date('Y-m-d H:i:s');
        $result = Db::table('kx_jc_yonghuka')->insert($array_data);
        if ($result) {
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start));
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start));
        }
    }

    /**
     * @title 退房
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/checkOut.md
     */
    public function checkOut(Request $request){
        $start = getMicrotime();
        $array_data = $request->post();
        if (empty($array_data['UserID'] || empty($array_data['KaID']))) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误!');
        }

        $result = Db::name('kx_jc_yonghuka')->where($array_data)->delete();

        if ($result) {
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start));
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start));
        }
    }

    /**
     * @title 获取未绑定房卡的用户
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/getUsers.md
     */
    public function getUsers(Request $request){
        $start = getMicrotime();
        $list = $this->user_model
            ->alias('u')
            ->field('u.*,y.KaID')
            ->join('kx_jc_yonghuka y','u.UserID=y.UserID','left')
            ->where('KaID',null)
            ->select();
        $list = collection($list)->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 获取未绑定用户的房卡
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/getUserCards.md
     */
    public function getUserCards(Request $request){
        $start = getMicrotime();
        $list = $this->ka_model
            ->alias('k')
            ->field('k.*,y.UserID')
            ->join('kx_jc_yonghuka y','y.KaID=k.KaID','left')
            ->where('UserID',null)
            ->select();
        $list = collection($list)->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 根据楼层获取房间号(创建页面)
   * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/getUserCards.md
     */
    public function rommList(Request $request){
        $start = getMicrotime();
        $floor = $request->get('floor');
        $list = [];
        for ($i=1;$i<100;$i++) {

            if ($i<10){
                $value = $floor.'0'.$i;
            } else {
                $value = $floor.$i;
            }
            $list[] = $value;
        }
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
                'SuSheMingChengStart' => ['name' => 'SuSheMingChengStart', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '房间名称起始', 'range' => '',],
                'SuSheMingChengEnd' => ['name' => 'SuSheMingChengEnd', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '房间名称结束', 'range' => '',],
                'FuJiSuSheID' => ['name' => 'FuJiSuSheID', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '父级id(楼层为空)', 'range' => '',],
                'Type' => ['name' => 'Type', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '1,楼层,2,房间', 'range' => '',],
            ],
            'edit' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
                'genre' => ['name' => 'genre', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => '房间类型', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'floorList' => [
            ],
            'getCards' => [
            ],
            'bindCard' => [
                'DoorLockNO' => ['name' => 'DoorLockNO', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
                'DecKaID' => ['name' => 'DecKaID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'bindUser' => [
                'UserID' => ['name' => 'UserID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
                'KaID' => ['name' => 'KaID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
        ];
        //可以合并公共参数
        return $rules;
    }
}
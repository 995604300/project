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
        'setCheckTime'
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
       if (!empty($type)) {
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
     * @title 设备的编辑
     * @readme /doc/md/api/Device/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();
       foreach ($array_data['data'] as $key=>$value){
           if (empty($value['Id']) || empty($value['sbID']) || empty($value['Title']) || empty($value['IP'])) {
               $end = getMicrotime();
               return $this->sendError(($end - $start),1,'参数错误!');
           }
           $save_data = [
               'sbID' => $value['sbID'],
               'Title' => $value['Title'],
               'IP' => $value['IP']
           ];
           $this->device_model->where('Id', $value['Id'])->update($save_data);
       }
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start));
   }

    /**
     * @title 考勤时间设置
     * @readme /doc/md/api/Device/setCheckTime.md
     */
   public function setCheckTime(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();

       $result = Db::table('kx_php_check_time')->insert($array_data);
       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }


    public function test(Request $request)
    {
        //这里写业务逻辑.推荐使用方法调用的形式。例如模型中的方法
        $statistics_model = new Statistics();
        $user_model = new User();
        $violation_log = []; //违规记录
        $date = date('Y-m-d');
        $date = '2019-12-28';
        $start_time =  $date . ' 00:00:00';
        $end_time = $date . ' 23:59:59';
        $where['recordDate'] = [['>',$start_time],['<',$end_time ]];


        //获取门禁,食堂,房间的考勤时间
        $check_time = Db::table('kx_php_check_time')->select();
        $check_time = collection($check_time)->toArray();
        foreach ($check_time as $value) {
            if ($value['type'] == 1) {
                $door_check_time = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            }elseif($value['type'] == 2) {
                $shitang_check_time[] = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            } else {
                $room_check_time = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            }
        }


        // 获取客房打卡违规记录
        $room_log = $statistics_model
            ->where(['RoleId'=>3,'Types'=>4])
            ->where(function($query) use ($room_check_time,$start_time,$end_time){
                $query->where('recordDate','between',[$start_time,$room_check_time['startTime']])
                      ->whereOr('recordDate','between',[$room_check_time['endTime'],$end_time]);
            })
            ->select();
        $room_log = collection($room_log)->toArray();
        foreach ($room_log as $value) {
            $value ['message'] = '客房打卡违规';
            $violation_log[] = $value;
        }

        //
        //获取门禁考勤违规记录
        $door_log = $statistics_model
            ->where(['RoleId'=>3,'Types'=>2])
            ->where(function($query) use ($door_check_time,$start_time,$end_time){
                $query->where('recordDate','between',[$start_time,$door_check_time['startTime']])
                      ->whereOr('recordDate','between',[$door_check_time['endTime'],$end_time]);
            })
            ->select();
        $door_log = collection($door_log)->toArray();
        foreach ($door_log as $value) {
            $value ['message'] = '门禁打卡违规';
            $violation_log[] = $value;
        }

        //
        //获取当天全部学生数据以及需要参与课程数据
        $user = $user_model
            ->with([
                       'classes'=>function($query) use ($date) {
                           $query->with([
                                            'lessonClass'=>function($query) use ($date)
                                            {$query->field('l.*')->join('kx_php_lesson l','lessonId=l.id')->where('date', $date);
                                            }]
                           );}
                   ])
            ->where('roleId',3)
            ->select();
        $user = collection($user)->toArray();

        foreach ($user as $value){
            $shitang_log = $statistics_model->where(['UserID'=>$value['UserID'],'Types'=>1,'recordDate'=>['between',[$start_time,$end_time]]])->select();
            $shitang_log = collection($shitang_log)->toArray();

            foreach ($shitang_check_time as $val) {

                $res = array_filter($shitang_log, function($v) use ($val) { return strtotime($v[0]) >= strtotime($val['startTime']) && strtotime($v[0]) <= strtotime($val['endTime']);});

                if (!$res) {
                    $violation_log[] = [
                        'UserID'=>$value['UserID'],
                        'UserName'=>$value['UserName'],
                        'RealName'=>$value['RealName'],
                        'RoleId'=>$value['RoleId'],
                        'IDCard'=>$value['IDCard'],
                        'message'=>$val['startTime'].' - '.$val['endTime'].'食堂考勤未打卡'
                    ];
                }
            }

            foreach ($value['classes']['lesson_class'] as $val) {

                $res = Db::table('kx_kq_record')->field('a.time AS recordDate, b.UserID, b.UserName, b.UserCode, b.RealName, b.RoleId, b.ClassId, b.IDCard, a.sbID AS sbID')->alias('a')->join('kx_sb_guanli f','a.sbID = f.sbID')->join('kx_jc_user b','a.KaID = b.UserCode')->where(['recordDate'=>['between',[$start_time,$end_time]]])->select();

            }
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
                'Id' => ['name' => 'Id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'Title' => ['name' => 'Title', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '设备名称', 'range' => '',],
                'sbID' => ['name' => 'sbID', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '序列号', 'range' => '',],
                'IP' => ['name' => 'IP', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => 'ip地址', 'range' => '',],
            ],
            'setCheckTime' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'startTime' => ['name' => 'startTime', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '开始时间', 'range' => '',],
                'endTime' => ['name' => 'endTime', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '结束时间', 'range' => '',],
                'type' => ['name' => 'type', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '类型 1:门禁,2:食堂,3:客房', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
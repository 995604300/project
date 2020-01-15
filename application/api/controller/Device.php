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
        'checkList',
        'setCheckTime',
        'synchronous',
        'clearData',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = ['setCheckTime','checkList'];
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
     * @title 考勤时间列表
     * @readme /doc/md/api/Device/checkList.md
     */
    public function checkList(Request $request){
        $start = getMicrotime();
        $type = $request->get('type');
        $where = [];
        if (!empty($type)){
            $where['type'] = $type;
        }
        $result = Db::table('kx_php_check_time')->where($where)->select();

        if ($result) {
            $result = collection($result)->toArray();
        } else {
            $result = [];
        }
        $end = getMicrotime();
        return $this->sendSuccess(($end - $start),$result);
    }

    /**
     * @title 考勤时间设置
     * @readme /doc/md/api/Device/setCheckTime.md
     */
   public function setCheckTime(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();

       if (empty($array_data['id'])) {
           $result = Db::table('kx_php_check_time')->insert($array_data);
       } else {
           $result = Db::table('kx_php_check_time')->update($array_data);
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
     * @title  同步小麦,消费机,门禁用户以及设备数据
     * @readme /doc/md/api/Device/synchronous.md
     */
    public function synchronous(Request $request)
    {
        $start = getMicrotime();
        //用户数据
        $user_list = Db::table('kx_jc_user u')->join('kx_php_role r','u.RoleId=r.id')->where('type',2)->select();
        $user_list = collection($user_list)->toArray();

        //小麦人脸设备数据
        $dev_info = Db::table('kx_sb_guanli')->where('type','XA_FACE')->select();
        $dev_info = collection($dev_info)->toArray();
        $message = '';
        $res = curl_post_https('http://127.0.0.1:15511/syncXAfaceDevicesUser', ['apikey' => md5('apikey' . date('Y-m-d')), 'dev_info' => $dev_info,'userlist'=>$user_list,'updateall'=>0]);
        $res = json_decode($res);
        $message .= '通过事件单号['.$res->data->log_no.',';

        //小麦人脸设备数据
        $dev_info = Db::table('kx_sb_guanli')->where('type','ZKT_CM20')->select();
        $dev_info = collection($dev_info)->toArray();

        $res = curl_post_https('http://127.0.0.1:15511/syncConsumerMachineUser', ['apikey' => md5('apikey' . date('Y-m-d')), 'dev_info' => $dev_info,'userlist'=>$user_list,'updateall'=>0]);
        $res = json_decode($res);
        $message .= $res->data->log_no.',';

        //小麦人脸设备数据
        $dev_info = Db::table('kx_sb_guanli')->where('type','ZKT_INBLO46')->select();
        $dev_info = collection($dev_info)->toArray();

        $res = curl_post_https('http://127.0.0.1:15511/syncAccessControlUser', ['apikey' => md5('apikey' . date('Y-m-d')), 'dev_info' => $dev_info,'updateall'=>0]);
        $res = json_decode($res);
        $message .= $res->data->log_no.']查询同步数据执行情况!';


        $end = getMicrotime();
        return $this->sendSuccess(($end - $start),[],$message);
    }

    /**
     * @title  获取事件执行情况
     * @readme /doc/md/api/Device/getLogInfo.md
     */
    public function getLogInfo(Request $request)
    {
        $start = getMicrotime();
        $log_no = $request->get('log_no');
        if (empty($log_no)) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'请输入事件单号');
        }
        $res = Db::table('kx_jc_OperationLog')->where('log_no',$log_no)->find();
        $end = getMicrotime();
        return $this->sendSuccess(($end - $start),$res);
    }


    /**
     * @title  清除学生数据以及考勤记录
     * @readme /doc/md/api/Device/clearData.md
     */
    public function clearData(){
        $start = getMicrotime();
//        Db::execute('truncate table kx_jc_yonghuka;truncate table kx_kq_record;truncate table sb_Device_Record;truncate table 图书借阅统计;');
//        Db::table('kx_jc_user')->join('kx_php_role','RoleId=id')->where('type',2)->delete();
        $end = getMicrotime();
        return $this->sendSuccess(($end - $start));
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
            'checkList' => [
                'type' => ['name' => 'type', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '类型 1:门禁,2:食堂,3:客房', 'range' => '',],
            ],
            'setCheckTime' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'startTime' => ['name' => 'startTime', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '开始时间', 'range' => '',],
                'endTime' => ['name' => 'endTime', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '结束时间', 'range' => '',],
                'type' => ['name' => 'type', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '类型 1:门禁,2:食堂,3:客房', 'range' => '',],
            ],
            'getLogInfo' => [
                'log_no' => ['name' => 'log_no', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
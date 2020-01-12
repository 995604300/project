<?php

/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: statistics.php
 *      AUTH: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-24
 */
namespace app\api\controller;

use app\api\common\Export;
use think\Db;
use think\Request;
/**
 * Class Statistics
 * @title 楼层总控台管理
 * @url  localhost/api/Statistics
 * @desc 班级管理相关接口
 * @version 1.0
 */
class Statistics extends Base
{
    //附加方法
    protected $extraActionList = [
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [
        'totalViolation'
    ];
    protected $statistics_model;
    protected $user_model;
    protected $violation_model;

    public function __construct()
    {
        parent::__construct();
        $this->statistics_model = Model('Statistics');
        $this->user_model = Model('User');
        $this->violation_model = Model('Violation');
    }

    /**
     * @title 统计查询
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Statistics/index.md
     */
    public function index(Request $request){
        $start = getMicrotime();
        $size = $request->get('size') ? $request->get('size') : 10; //每页条数
        $Types = $request->get('Types');
        $StartTime = $request->get('StartTime');
        $EndTime = $request->get('EndTime');
        $RealName = $request->get('RealName');
        $ClassId = $request->get('ClassId');
        $export = $request->get('export');
        $where = [];
        if (!empty($Types)) {
            $where['Types'] = $Types;
        }
        if (!empty($StartTime)) {
            $where['recordDate'] = ['>=',$StartTime];
        }
        if (!empty($EndTime)) {
            $where['recordDate'] = ['<=',$EndTime];
        }
        if (!empty($RealName)) {
            $where['RealName'] = ['like',"%".$RealName."%"];
        }
        if (!empty($ClassId)) {
            $where['ClassId'] = $ClassId;
        }
        if (!empty($export)){
            $exportList = [];
            $res = $this
                ->statistics_model
                ->order('recordDate DESC')
                ->where($where)
                ->select();
            $exportData = collection($res)->toArray();
            $exportTitle = [
                '0' => ['A1','序号','ROW_NUMBER'],
                '1' => ['B1','姓名','RealName'],
                '2' => ['C1','打卡时间','recordDate'],
                '3' => ['D1','打卡设备','Types'],
            ];
            $count = count($exportData);
            for ($i = 2; $i <= $count + 1; $i++) {
                switch ($exportData[$i - 2]['Types']) {
                    case 1:
                        $type = '食堂记录';
                        break;
                    case 2:
                        $type = '门禁记录';
                        break;
                    case 3:
                        $type = '班牌记录';
                        break;
                    case 4:
                        $type = '客房记录';
                        break;
                    case 5:
                        $type = '借阅记录';
                        break;
                }
                $exportList[$i - 2][0] = ['A'.$i,$exportData[$i - 2]['ROW_NUMBER']];
                $exportList[$i - 2][1] = ['B'.$i,$exportData[$i - 2]['RealName']];
                $exportList[$i - 2][2] = ['C'.$i,$exportData[$i - 2]['recordDate']];
                $exportList[$i - 2][3] = ['D'.$i,$type];
            }
            $name = date("Y-m-d").'导出考勤记录数据';
            (new Export())->exportExcel($exportTitle,$exportList,$name);
        }
        $list = $this
            ->statistics_model
            ->where($where)
            ->order('recordDate DESC')
            ->paginate($size)
            ->toArray();
        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 违规记录列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Statistics/attendanceViolation.md
     */
    public function attendanceViolation(Request $request) {
        $start = getMicrotime();
        $size = $request->get('size') ? $request->get('size') : 10; //每页条数
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $classId = $request->get('classId');
        $type = $request->get('type');
        $export = $request->get('export');

        $where = [];
        if (!empty($start_date) && !empty($end_date)) {
            $where['date'] = [['>',$start_date],['<',$end_date ]];
        }
        if (!empty($classId)) {
            $where['classId'] = $classId;
        }
        if (!empty($type)) {
            $where['type'] = $type;
        }


        if (!empty($export)){
            $exportList = [];
            $res = $this
                ->violation_model
                ->order('date DESC')
                ->where($where)
                ->select();
            $exportData = collection($res)->toArray();
            $exportTitle = [
                '0' => ['A1','序号','ROW_NUMBER'],
                '1' => ['B1','姓名','RealName'],
                '2' => ['C1','违规日期','date'],
                '3' => ['D1','打卡设备','type'],
                '3' => ['E1','违规记录','message'],
            ];
            $count = count($exportData);
            for ($i = 2; $i <= $count + 1; $i++) {
                switch ($exportData[$i - 2]['type']) {
                    case 1:
                        $type = '食堂记录';
                        break;
                    case 2:
                        $type = '门禁记录';
                        break;
                    case 3:
                        $type = '班牌记录';
                        break;
                    case 4:
                        $type = '客房记录';
                        break;
                    case 5:
                        $type = '借阅记录';
                        break;
                }
                $exportList[$i - 2][0] = ['A'.$i,$exportData[$i - 2]['ROW_NUMBER']];
                $exportList[$i - 2][1] = ['B'.$i,$exportData[$i - 2]['RealName']];
                $exportList[$i - 2][2] = ['C'.$i,$exportData[$i - 2]['date']];
                $exportList[$i - 2][3] = ['D'.$i,$type];
                $exportList[$i - 2][2] = ['E'.$i,$exportData[$i - 2]['message']];
            }
            $name = date("Y-m-d").'导出违规记录数据';
            (new Export())->exportExcel($exportTitle,$exportList,$name);
        }

        $list = $this
            ->violation_model
            ->order('date DESC')
            ->where($where)
            ->paginate($size)
            ->toArray();

        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);

    }

    public function totalViolation(Request $request){
        $start = getMicrotime();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $classId = $request->get('classId');
        $type = $request->get('type');

        $where = [];
        if (!empty($start_date) && !empty($end_date)) {
            $where['date'] = [['>=',$start_date],['<=',$end_date ]];
        }
        if (!empty($classId)) {
            $where['classId'] = $classId;
        }
        if (!empty($type)) {
            $where['type'] = $type;
        }

        $list = $this->violation_model
            ->field('RealName,UserID,count(UserID) as count')
            ->order('count DESC')
            ->where($where)
            ->group('UserID,RealName')
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
                'Types' => ['name' => 'Types', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '1,消费信息,2门禁信息,3,班牌考勤信息,4,客房门锁信息,5,图书借阅信息', 'range' => '',],
                'StartTime' => ['name' => 'StartTime', 'type' => 'date', 'require' => 'false', 'default' => '', 'desc' => '开始时间', 'range' => '',],
                'EndTime' => ['name' => 'EndTime', 'type' => 'date', 'require' => 'false', 'default' => '', 'desc' => '结束时间', 'range' => '',],
                'RealName' => ['name' => 'RealName', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '姓名', 'range' => '',],
                'ClassId' => ['name' => 'ClassId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '班级id', 'range' => '',],
                'export' => ['name' => 'export', 'type' => 'bool', 'require' => 'false', 'default' => 'false', 'desc' => '是否导出', 'range' => '',],
            ],
            'attendanceViolation' => [
                'start_date' => ['name' => 'start_date', 'type' => 'date', 'require' => 'false', 'default' => 'false', 'desc' => '统计开始日期', 'range' => '',],
                'end_date' => ['name' => 'end_date', 'type' => 'date', 'require' => 'false', 'default' => 'false', 'desc' => '统计结束日期', 'range' => '',],
            ]
        ];
        //可以合并公共参数
        return $rules;
    }
}
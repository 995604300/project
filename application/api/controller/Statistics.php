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
    protected $skipAuthActionList = [];
    protected $statistics_model;

    public function __construct()
    {
        parent::__construct();
        $this->statistics_model = Model('Statistics');
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
        $list = $this
            ->statistics_model
            ->where($where)
            ->paginate($size)
            ->toArray();
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
                'Types' => ['name' => 'Types', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '1,消费信息,2小麦考勤信息,3,班牌考勤信息,4,门锁信息,5,图书借阅信息', 'range' => '',],
                'StartTime' => ['name' => 'StartTime', 'type' => 'date', 'require' => 'false', 'default' => '', 'desc' => '开始时间', 'range' => '',],
                'EndTime' => ['name' => 'EndTime', 'type' => 'date', 'require' => 'false', 'default' => '', 'desc' => '结束时间', 'range' => '',],
                'RealName' => ['name' => 'RealName', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '姓名', 'range' => '',],
                'ClassId' => ['name' => 'ClassId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '班级id', 'range' => '',],
            ],
        ];
        //可以合并公共参数
        return $rules;
    }
}
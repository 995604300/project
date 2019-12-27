<?php
return [
    '1' => ['name' => '状态码', 'id' => '1', 'parent' => '0', 'readme' => '/doc/md/code.md', 'class' => ''],

    '2' => ['name' => '组织管理', 'id' => '2', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Role::class],
    '3' => ['name' => '班级管理', 'id' => '3', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Classes::class],
    '4' => ['name' => '课程管理', 'id' => '4', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Lesson::class],
    '5' => ['name' => '楼层总控台管理', 'id' => '5', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Room::class],
    '6' => ['name' => '教室管理', 'id' => '6', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Classroom::class],
    '7' => ['name' => '用户管理', 'id' => '7', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\User::class],
    '8' => ['name' => '统计查询', 'id' => '8', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Statistics::class],
    '11' => ['name' => '登入登出', 'id' => '11', 'parent' => '0', 'readme' => '', 'class' => \app\api\controller\Login::class],
];
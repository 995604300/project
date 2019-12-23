<?php
/**
 * 验证器
 * */
namespace app\api\validate;
use think\Validate;

class Classroom extends Validate {

    protected $rule =   [
        'id'=>'require|number',
        'name'=>'require',
        'campus_num'=>'require',
        'capacity'=>'require|number',
        'test_capacity'=>'require|number',
    ];

    protected $message  =   [
        'id.require' => '请选择编辑数据',
        'name.require' => '请输入教室名称',
        'campus_num.require' => '请选择科目',
        'capacity.require' => '用户名不能为空',
        'test_capacity.require' => '专业不能为空',
        'id.number' => 'id为整数',
        'capacity.number' => '教室容量为整数',
        'test_capacity.number' => '实际容量为整数',
    ];

    // 指定验证某些字段 （一般情况下用不到）
    protected $scene = [
        'save' =>  ['name','campus_num','capacity','test_capacity'],
        'edit' =>  ['id','name','campus_num','capacity','test_capacity'],
    ];
}
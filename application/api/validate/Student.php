<?php
/**
 * 验证器
 * */
namespace app\api\validate;
use think\Validate;

class Student extends Validate {

    protected $rule =   [
        'id'=>'require|number',
        'student_num'=>'require|number',
        'campus_num'=>'require|number',
        'facuilty_num'=>'require|number',
        'specialty_num'=>'require|number',
        'class_num'=>'require|number',
        'grade_num'=>'require|number',
//        'mobile'=>'require|unique:user,mobile|max:11|number|regex:/^1[34578]{1}[0-9]{9}$/',
        'name' => 'require',
    ];

    protected $message  =   [
        'id.require' => '请选择编辑数据',
        'student_num.require' => '请填写学号',
        'campus_num.require' => '请选择校区',
        'facuilty_num.require' => '请选择院系',
        'specialty_num.require' => '请选择专业',
        'class_num.require' => '请选择班级',
        'grade_num.require' => '请选择年级',
        'name.require' => '请填写学生姓名',
        'id.number' => 'id为整数',
        'student_num.number' => '学号为整数',
        'campus_num.number' => '校区编码为整数',
        'facuilty_num.number' => '院系编码为整型',
        'specialty_num.number' => '专业编码为整型',
        'class_num.number' => '班级编码为整型',
        'grade_num.number' => '年级编码为整型',
    ];

    // 指定验证某些字段 （一般情况下用不到）
    protected $scene = [
        'save' => ['student_num','campus_num','facuilty_num','specialty_num','class_num','grade_num','name'],
        'edit' =>  ['id','student_num','campus_num','facuilty_num','specialty_num','class_num','grade_num','name'],
    ];
}
<?php
/**
 * 验证器
 * */
namespace app\api\validate;
use think\Validate;

class Test extends Validate {

    protected $rule =   [
        'id'=>'require|number',
        'exam_id'=>'require|number',
        'subject_num'=>'require|number',
        'facuilty_num'=>'require|number',
        'specialty_num'=>'require|number',
//        'mobile'=>'require|unique:user,mobile|max:11|number|regex:/^1[34578]{1}[0-9]{9}$/',
        'date' => 'require',
        'start_time'=>'require',
        'end_time'=>'require',
    ];

    protected $message  =   [
        'id.require' => '请选择编辑数据',
        'exam_id.require' => '请选择考试',
        'subject_num.require' => '请选择科目',
        'facuilty_num.require' => '请选择院系',
        'specialty_num.require' => '请选择专业',
        'date.require' => '请选择日期',
        'start_time.require' => '请输入开始时间',
        'end_time.require' => '请输入结束时间',
        'id.number' => 'id为整数',
        'exam_id.number' => '考试id为整数',
        'subject_num.number' => '科目编码为整数',
        'facuilty_num.number' => '院系编码为整数',
        'specialty_num.number' => '专业编码为整数',
    ];

    // 指定验证某些字段 （一般情况下用不到）
    protected $scene = [
        'save' =>  ['exam_id','subject_num','facuilty_num','date','start_time','end_time'],
        'edit' =>  ['id','exam_id','subject_num','facuilty_num','specialty_num','date','start_time','end_time'],
    ];
}
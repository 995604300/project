<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Ka.php
 *      AUTHOR: Wang YX(Ka)
 *      CREATE_TIME: 2019-12-24
 */
namespace app\api\model;
use think\Model;

class Ka extends Model
{
    protected $table = 'kx_jc_ka';

    //关联教室数据
    public function classroom(){
        return $this->hasOne('Classroom','id','ClassroomId',[],'LEFT');
    }

    public function lesson()
    {
        return $this->hasMany('Lesson','classroomId','ClassroomId');
    }
    /**
     * 单个与多个删除
     * @param $id
     * @return int
     */
    public function delAll($id)
    {
        $res = $this->destroy($id);
        return $res;
    }

}
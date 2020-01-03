<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Plate.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-24
 */
namespace app\api\model;
use think\Model;

class Plate extends Model
{
    protected $table = 'kx_php_plate';


    public function lesson()
    {
        return $this->hasMany('Lesson','classroomId','classroomId');
    }
    public function classroom()
    {
        return $this->hasOne('Classroom','id','classroomId');
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
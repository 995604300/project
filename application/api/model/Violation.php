<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Classroom.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-13
 */
namespace app\api\model;
use think\Model;
use think\Session;

class Violation extends Model
{
    protected $table = 'kx_php_violation';


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
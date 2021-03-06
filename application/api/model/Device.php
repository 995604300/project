<?php

/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Device.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-30
 */
namespace app\api\model;
use think\Model;

class Device extends Model
{
    protected $table = 'kx_sb_guanli';

    public function plate()
    {
        return $this->hasOne('Plate','sbID','sbID');
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
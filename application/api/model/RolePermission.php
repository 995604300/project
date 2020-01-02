<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: RolePermission.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-10-08
 */
namespace app\api\model;
use think\Model;
use think\Session;

class RolePermission extends Model
{
    protected $table = 'kx_php_role_permission';


    public function permission(){
        return $this->hasOne('Permission','id','permissionId',[],'LEFT')->field('id,name,path');
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
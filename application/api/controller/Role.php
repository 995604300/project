<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Role.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-13
 */

namespace app\api\controller;
use think\Config;
use think\Db;
use think\Request;
/**
 * Class Role
 * @title 楼层总控台管理
 * @url  localhost/api/Classes
 * @desc 班级管理相关接口
 * @version 1.0
 */
class Role extends Base
{
    //附加方法
    protected $extraActionList = [
        'permissionList',
        'userList',
        'rolePermissionList',
        'assign',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $role_model;
    protected $user_model;
    protected $permission_model;
    protected $role_permission_model;

    public function __construct()
    {
        parent::__construct();
        $this->role_model = Model('Role');
        $this->user_model = Model('User');
        $this->permission_model = Model('Permission');
        $this->role_permission_model = Model('RolePermission');
    }

    /**
     * @title 角色列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $list = $this
           ->role_model
           ->select();
       if ($list) {
           $list = collection($list)->toArray();
       }

       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 角色的新增与编辑
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();

       $array_data = $request->post();
       if (!empty($array_data['id'])) {
           $result = $this->role_model->allowField(true)->isUpdate(TRUE)->save($array_data);
       } else {
           if (empty($array_data['name'])){
               $end = getMicrotime();
               return $this->sendError(($end - $start),1,'参数错误！');
           }
           $result = $this->role_model->allowField(true)->isUpdate(FALSE)->save($array_data);
       }
       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 角色删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $id = $request->get('id');
       if (!empty($id)) {
           if (in_array($id,[1,2,3])){
               $end = getMicrotime();
               return $this->sendError(($end - $start),1,'该角色不允许删除');
           }
           $result = $this->role_model->delAll($id);
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }

       if ($result) {
           $this->role_permission_model->where('roleId',$id)->delete();
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 权限列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/permissionList.md
     */
    public function permissionList(Request $request){
        $start = getMicrotime();
        $list = $this
            ->permission_model
            ->select();
        if ($list) {
            $list = collection($list)->toArray();
        }

        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title 当前角色已经分配的权限列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/rolePermissionList.md
     */
    public function rolePermissionList(Request $request){
        $start = getMicrotime();
        $id = $request->get('id');
        if (empty($id)) {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误!');
        }
        $list = $this
            ->role_permission_model
            ->with('permission')
            ->where('roleId',$id)
            ->select();
        if ($list) {
            $list = collection($list)->toArray();
        }

        $end = getMicrotime();
        return $this->sendSuccess(($end-$start),$list);
    }

    /**
     * @title  获取非该角色下的用户数据
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/userList.md
     */
   public function userList(Request $request){
       $start = getMicrotime();
       $id = $request->get('id');
       if (empty($id)) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }
       $where['RoleId'] = ['<>',$id];
       $list = $this
           ->user_model
           ->where($where)
           ->whereOr('RoleId',NULL)
           ->select();
       if ($list) {
           $list = collection($list)->toArray();
       }
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start),$list);

   }

    /**
     * @title  为用户分配角色,为角色分配权限接口
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Role/assign.md
     */
   public function assign(Request $request){
       $start = getMicrotime();
       $array_data = $request->post('');
       if (empty($array_data['id'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }

       if (is_array($array_data['userId'])) {
           foreach ($array_data['userId'] as $key=>$value) {
               $user_data[$key]['UserID'] = $value;
               $user_data[$key]['RoleId'] = $array_data['id'];
           }
           $this->user_model->isUpdate(TRUE)->saveAll($user_data);
       }

       if (is_array($array_data['permissionId'])) {
           $this->role_permission_model->where('roleId',$array_data['id'])->delete();
           foreach ($array_data['permissionId'] as $key=>$value) {
               $permission_data[$key]['permissionId'] = $value;
               $permission_data[$key]['roleId'] = $array_data['id'];
           }
           $this->role_permission_model->saveAll($permission_data);
       }

       $end = getMicrotime();
       return $this->sendSuccess(($end - $start));
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
            ],
            'save' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '存在时为更新,否则为创建', 'range' => '',],
                'name' => ['name' => 'name', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '角色名称', 'range' => '',],
                'type' => ['name' => 'type', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '角色类型(1,员工;2,学员)', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'permissionList' => [
            ],
            'rolePermissionList' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'userList' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'assignPermission' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],

            ],
            'assign' => [
                'id' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '角色id', 'range' => '',],
                'userId' => ['name' => 'userId', 'type' => 'array', 'require' => 'false', 'default' => '', 'desc' => '用户id', 'range' => '',],
                'permissionId' => ['name' => 'permissionId', 'type' => 'array', 'require' => 'false', 'default' => '', 'desc' => '权限id', 'range' => '',],
            ],
        ];
        //可以合并公共参数
        return $rules;
    }
}
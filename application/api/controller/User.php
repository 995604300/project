<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: User.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-19
 */

namespace app\api\controller;
use think\Db;
use think\Loader;
use think\Request;
/**
 * Class User
 * @title 用户列表
 * @url  localhost/api/User
 * @desc
 * @version 1.0
 */
class User extends Base
{
    //附加方法
    protected $extraActionList = [

    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = [];
    protected $user_model;

    public function __construct()
    {
        parent::__construct();
        $this->user_model = Model('User');
    }

    /**
     * @title 用户列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/User/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->post('size') : 10; //每页条数
       $RealName = $request->get('RealName') ; //姓名
       $IDCard = $request->get('IDCard') ; //身份证号
       $ClassId = $request->get('ClassId') ; //班级
       $type = $request->get('type') ; //用户角色类型(1,员工,2,学生)
       $where = [];

       if (!empty($RealName)) {
           $where['RealName'] = ['like','%'.$RealName.'%'];
       }
       if (!empty($IDCard)) {
           $where['IDCard'] = ['like','%'.$IDCard.'%'];
       }
       if (!empty($ClassId)) {
           $where['ClassId'] = $ClassId;
       }
       if (!empty($type)) {
           $where['type'] = $type;
       }
       $list = $this->user_model
           ->alias('u')
           ->field('u.*,r.name as RoleName,r.type,c.className')
           ->join('kx_php_role r','r.id=RoleId','left')
           ->join('kx_php_class c','c.id=ClassId','left')
           ->where($where)
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 用户列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/User/edit.md
     */
   public function edit(Request $request){
       $start = getMicrotime();
       $array_data = $request->get('');
       if (empty($array_data['userId'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),'参数错误!');
       }
       $save_data['UserId'] = $array_data['userId'];
       if (!empty($array_data['classId'])) {
           $save_data['ClassId'] = $array_data['classId'];
       }
       if (empty($array_data['roleId'])) {
           $save_data['RoleId'] = $array_data['roleId'];
       }
       $result = $this->user_model->isUpdate(TRUE)->saveAll($save_data);

       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 用户删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Room/delete.md
     */
    public function delete(Request $request){
        $start = getMicrotime();
        $array = $request->get();
        if (!empty($array['id'])) {
            $result = $this->user_model->delAll($array['id']);
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start),1,'参数错误！');
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
                'page' => ['name' => 'page', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '页', 'range' => '',],
                'size' => ['name' => 'size', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '每页数据条数', 'range' => '',],
                'RealName' => ['name' => 'RealName', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '姓名', 'range' => '',],
                'IDCard' => ['name' => 'IDCard', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '身份证号', 'range' => '',],
                'ClassName' => ['name' => 'ClassName', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '班级', 'range' => '',],
                'type' => ['name' => 'type', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '用户角色类型(1,员工,2,学生)', 'range' => '',],
            ],
            'edit' => [
                'userId' => ['name' => 'id', 'type' => 'integer', 'require' => 'true', 'default' => '', 'desc' => '用户id', 'range' => '',],
                'roleId' => ['name' => 'roleId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '角色id', 'range' => '',],
                'classId' => ['name' => 'classId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '班级id', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
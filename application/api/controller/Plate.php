<?php
/**
 *      [Wang YX] (C)2017-2099
 *      This is not a free software, without any authorization is not allowed to use and spread.
 *
 *      FILE_NAME: Plate.php
 *      AUTHOR: Wang YX(wyx141592@163.com)
 *      CREATE_TIME: 2019-12-24
 */

namespace app\api\controller;
use think\Db;
use think\Request;
/**
 * Class Plate
 * @title 班牌管理
 * @url  localhost/api/Plate
 * @desc 教室管理相关接口
 * @version 1.0
 */
class Plate extends Base
{
    //附加方法
    protected $extraActionList = [
        'setMessage',
        'setBanner',
        'deleteBanner',
        'publicUpload',
    ];
    //跳过鉴权的方法
    protected $skipAuthActionList = ['setMessage'];
    protected $plate_model;
    protected $device_model;

    public function __construct()
    {
        parent::__construct();
        $this->plate_model = Model('Plate');
        $this->device_model = Model('Device');
    }

    /**
     * @title 班牌列表
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/index.md
     */
   public function index(Request $request){
       $start = getMicrotime();
       $size = $request->get('size') ? $request->get('size') : 10; //每页条数
       $list = $this
           ->device_model
           ->alias('a')
           ->field('a.*,p.classroomId,c.classroomName')
           ->join('kx_php_plate p','a.sbID=p.sbID','left')
           ->join('kx_php_classroom c','p.classroomId=c.id','left')
           ->where('type','电子班牌(考勤)')
           ->paginate($size)
           ->toArray();
       $end = getMicrotime();
       return $this->sendSuccess(($end-$start),$list);
   }

    /**
     * @title 班牌的绑定
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/save.md
     */
   public function save(Request $request){
       $start = getMicrotime();
       $array_data = $request->post();

       if (empty($array_data['sbID']) || empty($array_data['classroomId'])) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误！');
       }
       $this->plate_model->where('sbID',$array_data['sbID'])->delete();
       $result = $this->plate_model->allowField(true)->isUpdate(FALSE)->save($array_data);

       if ($result) {
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       } else {
           $end = getMicrotime();
           return $this->sendError(($end - $start));
       }
   }

    /**
     * @title 班牌删除
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/delete.md
     */
   public function delete(Request $request){
       $start = getMicrotime();
       $array = $request->get();
       if (!empty($array['id'])) {
           $result = $this->plate_model->delAll($array['id']);
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
     * @title 设置班牌显示信息(POST)
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/setMessage.md
     */
   public function setMessage(Request $request){
       $start = getMicrotime();
       $title = $request->post('title');
       $content = $request->post('content');
       if (!empty($title)) {
           $save_array['title'] = $title;
       }
       if (!empty($content)) {
           $save_array['content'] = $content;
       }
       if (empty($save_array)) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }
       $res = Db::table('kx_php_plate_message')->where('id',1)->update($save_array);
       if ($res) {
           Push(['info'=>'isUpdate']);
           $end = getMicrotime();
           return $this->sendSuccess(($end - $start));
       }

   }

    /**
     * @title 设置班牌轮播图(POST)
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/setBanner.md
     */
   public function setBanner(Request $request){
       $start = getMicrotime();
       $name = $request->post('name');
       $path = $request->post('path');
       if (!empty($title)) {
           $save_array['name'] = $name;
       }
       if (!empty($content)) {
           $save_array['path'] = $path;
       }
       if (empty($save_array)) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }
       $res = Db::table('kx_php_plate_banner')->insert($save_array);
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start),$res);
   }

    /**
     * @title 删除班牌轮播图(GET)
     * @param Request $request
     * @throws \think\Exception
     * author: Wang YX
     * @readme /doc/md/api/Plate/setBanner.md
     */
   public function deleteBanner(Request $request){
       $start = getMicrotime();
       $id = $request->get('id');

       if (empty($id)) {
           $end = getMicrotime();
           return $this->sendError(($end - $start),1,'参数错误!');
       }
       $res = Db::table('kx_php_plate_banner')->where('id',$id)->delete();
       $end = getMicrotime();
       return $this->sendSuccess(($end - $start),$res);
   }

    /**
     * @title 上传图片接口(POST)
     * @param Request $request
     * author: Wang YX
     * @readme /doc/md/api//publicUpload.md
     */
    public function publicUpload(Request $request)
    {
        $start = getMicrotime();
        $file = $request->file('file');    //tp5封装好的方法，报错的话记得在最上面引入use think\Request;
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
            $file_url = [
                'ext' => $info->getExtension(),
                'file_name' => $info->getFilename(),
                'file_url' => config("http_url"). DS . 'uploads' . DS . $info->getSaveName()
            ];
            $end = getMicrotime();
            return $this->sendSuccess(($end - $start), $file_url);
        } else {
            $end = getMicrotime();
            return $this->sendError(($end - $start), 1, $file->getError());
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
            ],
            'save' => [
                'sbID' => ['name' => 'sbID', 'type' => 'string', 'require' => 'false', 'default' => '', 'desc' => '班牌唯一识别码', 'range' => '',],
                'classroomId' => ['name' => 'classroomId', 'type' => 'integer', 'require' => 'false', 'default' => '', 'desc' => '绑定教室', 'range' => '',],
            ],
            'delete' => [
                'id' => ['name' => 'id', 'type' => 'array', 'require' => 'true', 'default' => '', 'desc' => 'id', 'range' => '',],
            ],
            'setMessage' => [
                'title' => ['name' => 'title', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'content' => ['name' => 'content', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
            ],
            'setBanner' => [
                'name' => ['name' => 'name', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '名称', 'range' => '',],
                'path' => ['name' => 'path', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '路径', 'range' => '',],
            ],
            'deleteBanner' => [
                'title' => ['name' => 'title', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
                'content' => ['name' => 'content', 'type' => 'string', 'require' => 'true', 'default' => '', 'desc' => '', 'range' => '',],
            ],
            'publicUpload' => [
                'file' => ['name' => 'file', 'type' => 'file', 'require' => 'true', 'default' => '', 'desc' => '上传文件', 'range' => '',],
            ],

        ];
        //可以合并公共参数
        return $rules;
    }
}
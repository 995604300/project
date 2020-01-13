<?php
namespace app\api\Controller;
use think\controller\HproseController;
use think\Db;

/**
 * 服务控制器
 *
 * @version $Id$
 */
class Service extends HproseController
{
    protected $allowMethodList = array('get','key');
    protected $crossDomain = true;
    protected $P3P         = true;
    protected $get         = true;
    protected $debug       = true;

    public function key()
    {
        return json_encode(array('quantity'=>40));
    }

    public function index()
    {
    }
    public function get($arg)
    {
        switch ($arg['module'])
        {
            case 'INFO':
                return $this->get_info($arg);
            case 'LESSON':
                return $this->get_lesson($arg);
            case 'MESSAGE':
                return $this->get_message($arg);
            case 'BANNER':
                return $this->get_banner($arg);
            case 'NOWLESSON':
                return $this->get_now_lesson($arg);
        }


        return null;
    }

    public function get_info($arg = []) {
        $sn = $arg['sn'];
        if (!empty($sn)) {
            $plate_model =  Model('Plate');
            $res = $plate_model
                ->with('classroom')
                ->where('sbID',$sn)
                ->find()
                ->toArray();
            return json(['data'=>$res]);
        } else {
            return json(['message'=>'请传递班牌序列号']);
        }
    }

    public function get_lesson($arg = []) {

        $sn = $arg['sn'];
        if (!empty($sn)) {
            $plate_model =  Model('Plate');
            $res = $plate_model
                ->alias('a')
                ->field('l.date,l.classroomId,l.week')
                ->join('kx_php_lesson l','a.classroomId=l.classroomId')
                ->where('sbID',$sn)
                ->order('date')
                ->group('l.date,l.classroomId,l.week')
                ->select();
            if ($res) {
                $data = collection($res)->toArray();
                foreach ($data as $key=>$value) {
                    $result = Db::table('kx_php_lesson')->where(['date'=>$value['date'],'classroomId'=>$value['classroomId']])->order('startTime')->select();
                    $data[$key]['lesson'] = collection($result)->toArray();
                }
            } else {
                $data = [];
            }

            return json(['data'=>$data]);
        } else {
            return json(['message'=>'请传递班牌序列号']);
        }
    }

    public function get_now_lesson($arg = []) {
        $sn = $arg['sn'];
        if (!empty($sn)) {
            $plate_model =  Model('Plate');
            $date = date('Y-m-d');
            $res = $plate_model
                ->with(['lesson'=>function($query) use ($date){
                    $query->order('date')->order('startTime')->where('date',$date);
                }])
                ->where('sbID',$sn)
                ->find();
            if ($res) {
                $res = $res->toArray();
            }else {
                $res = [];
            }
            return json(['data'=>$res]);
        } else {
            return json(['message'=>'请传递班牌序列号']);
        }
    }

    public function get_message($arg = []) {
            $res = Db::table('kx_php_plate_message')
                ->find(['id'=>1]);
            return json(['data'=>$res]);

    }

    public function get_banner($arg = []) {
            $res = Db::table('kx_php_plate_banner')
                ->select();
            if ($res) {
                $res = collection($res)->toArray();
            } else {
                $res = [];
            }
            return json(['data'=>$res]);

    }
}
?>
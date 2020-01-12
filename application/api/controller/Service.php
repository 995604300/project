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
                ->with(['lesson'=>function($query){
                    $query->order('date')->order('startTime');
                }])
                ->where('sbID',$sn)
                ->find()
                ->toArray();
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
            $res = collection($res)->toArray();
            return json(['data'=>$res]);

    }
}
?>
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

    public function get($arg)
    {
        switch ($arg['module'])
        {
            case 'LESSON':
                return $this->get_lesson($arg);
            case 'MESSAGE':
                return $this->get_message($arg);
        }


        return null;
    }

    public function get_lesson($arg = []) {

        $sn = $arg['sn'];
        if (!empty($sn)) {
            $week = getWeekMyActionAndEnd();
            $plate_model =  Model('Plate');
            $res = $plate_model
                ->with(['lesson'=>function($query) use ($week){
                    $query->where('date','between',[$week['week_start'],$week['week_end']])->order('date')->order('startTime');
                },'classroom'])
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
}
?>
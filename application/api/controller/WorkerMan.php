<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use Workerman\Worker;
use Workerman\Lib\Timer;

class WorkerMan extends Controller
{

    public function index()
    {
        require_once 'C:\wamp64\www\information\card\app\Workerman\Autoloader.php';
        // 心跳间隔55秒
        define('HEARTBEAT_TIME', 30);
        //websocket url
        $socket_url = '192.168.0.111:2000';
        // 初始化一个worker容器，监听1234端口
        $worker = new Worker('websocket://'.$socket_url);
        // 新增加一个属性，用来保存uid到connection的映射
        $worker->uidConnections = array();
        /*
        * 注意这里进程数必须设置为1，否则会报端口占用错误
        * (php 7可以设置进程数大于1，前提是$inner_text_worker->reusePort=true)
        */
        $worker->count = 1;
        // worker进程启动后创建一个text Worker以便打开一个内部通讯端口
        $worker->onWorkerStart = function ($worker) {
            $inner_text_url ='192.168.0.111:2001';
            // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
            $inner_text_worker = new Worker('text://'. $inner_text_url);
            $inner_text_worker->onMessage = function ($connection, $buffer) {
                // $data数组格式，里面有uid，表示向那个uid的页面推送数据
                $data = json_decode($buffer, true);
                $uid = $data['uid'];
                if($data['data'] == 'exam'){
                    $number = $this->push_exam($buffer);
                }else{
                    // 通过workerman，向uid的页面推送数据
                    $number = $this->broadcast($buffer);
                }

                // 返回推送结果
                $connection->send('推送成功'. $number .'个设备');
            };

            Timer::add(1, function () use ($worker) {
                $time_now = time();
                foreach ($worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                        try {
//                            $this->outline($connection->uid);
                        } catch (\Exception $e) {}
                        $connection->close();
                        unset($worker->uidConnections[$connection->uid]);

                    }
                }
            });
            // ## 执行监听 ##
            $inner_text_worker->listen();

        };

        // 当有客户端发来消息时执行的回调函数
        $worker->onMessage = function ($connection, $data) {
            global $worker;
            $connection->lastMessageTime = time();
            // 判断当前客户端是否已经验证,既是否设置了uid
            if (!isset($connection->uid)) {
                // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
                $connection->uid = $data;
                /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                * 实现针对特定uid推送数据
                */
                $worker->uidConnections[$connection->uid] = $connection;
                try {
                    $this->line($connection->uid);
                } catch (\Exception $e) { }
                return;
            }
        };



        // 当有客户端连接断开时
        // $worker->onClose = function ($connection) {
        //     global $worker;
        //     if (isset($connection->uid)) {
        //         try {
        //             $this->outline($connection->uid);
        //         } catch (\Throwable $th) {
        //             echo '设备' + $connection->uid + '下线失败';
        //         }
        //         // 连接断开时删除映射
        //         unset($worker->uidConnections[$connection->uid]);
        //     }
        // };
        // 运行所有的worker
        Worker::runAll();
    }

    // 向所有验证的用户推送数据
    public function broadcast($message = null)
    {
        global $worker;
        $i = 0;
        foreach ($worker->uidConnections as $connection) {
            $connection->send($message);
            $i++;
        }
        return $i;
    }

    // 向所有验证的用户推送数据
    public function push_exam($message = null)
    {
        global $worker;
        $i = 0;
        $data = json_decode($message, true);
        foreach ($worker->uidConnections as $connection) {
            if (in_array($connection->uid, $data['sn'])) {
                $connection->send($message);
                $i++;
            }
        }
        return $i;
    }

    // 针对uid推送数据
    public function sendMessageByUid($uid = null, $message = null)
    {
        global $worker;
        if (isset($worker->uidConnections[$uid])) {
            $connection = $worker->uidConnections[$uid];
            $connection->send($message);
        }
    }

    //处理设备在线状态
    public function line($sn = null,$data = null){
        try {
            $device = Db::name('Device')->where(['sn' => $sn])->find();
            if ($device) {
                if ($device['line'] == 2) {
                    D('Device')->where(['sn' => $sn])->save(['line' => 1]);
                }
            }
            if ($sn != 'device') {
                $this->sendMessageByUid('device', 'line');
            }
        } catch (\Exception $e) { }

    }

    public function outline($sn = null){
        try {
            $device = D('Device')->where(['sn' => $sn])->find();
            if ($device) {
                if ($device['line'] == 1) {
                    D('Device')->where(['sn' => $sn])->save(['line' => 2]);
                }
            }
            if ($sn != 'device') {
                $this->sendMessageByUid('device', 'line');
            }
        } catch (\Exception $e) { }

    }
}
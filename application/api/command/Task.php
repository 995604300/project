<?php

namespace app\api\command;    //命名空间要注意

use app\api\model\Statistics;
use app\api\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Task extends Command{

    protected function configure(){
        $this->setName('Task')->setDescription("测试");
        //这里的setName和php文件名一致,setDescription随意
    }

    /*
     * 报表-每日统计
     */
    protected function execute(Input $input, Output $output)
    {
        //这里写业务逻辑.推荐使用方法调用的形式。例如模型中的方法
        $statistics_model = new Statistics();
        $user_model = new User();
        $violation_log = []; //违规记录
        $date = date('Y-m-d',strtotime("-1 day"));
        $date = '2020-01-06';
        $start_time =  $date . ' 00:00:00';
        $end_time = $date . ' 23:59:59';
        $where['recordDate'] = [['>',$start_time],['<',$end_time ]];


        //获取门禁,食堂,房间的考勤时间
        $check_time = Db::table('kx_php_check_time')->select();
        $check_time = collection($check_time)->toArray();
        foreach ($check_time as $value) {
            if ($value['type'] == 1) {
                $door_check_time = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            }elseif($value['type'] == 2) {
                $shitang_check_time[] = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            } else {
                $room_check_time = [
                    'startTime'=>$date.' '.explode('.',$value['startTime'])[0],
                    'endTime'=>$date.' '.explode('.',$value['endTime'])[0],
                ];
            }
        }


        // 获取客房打卡违规记录
        $room_log = $statistics_model
            ->where(['RoleId'=>3,'Types'=>4])
            ->where(function($query) use ($room_check_time,$start_time,$end_time){
                $query->where('recordDate','between',[$start_time,$room_check_time['startTime']])
                      ->whereOr('recordDate','between',[$room_check_time['endTime'],$end_time]);
            })
            ->select();
        $room_log = collection($room_log)->toArray();
        foreach ($room_log as $value) {
            $violation_log[] = [
                'type'=>4,
                'date'=>$date,
                'UserID'=>$value['UserID'],
                'UserName'=>$value['UserName'],
                'RealName'=>$value['RealName'],
                'RoleId'=>$value['RoleId'],
                'IDCard'=>$value['IDCard'],
                'message'=>'客房打卡违规'
            ];
        }

//
        //获取门禁考勤违规记录
        $door_log = $statistics_model
            ->where(['RoleId'=>3,'Types'=>2])
            ->where(function($query) use ($door_check_time,$start_time,$end_time){
                $query->where('recordDate','between',[$start_time,$door_check_time['startTime']])
                      ->whereOr('recordDate','between',[$door_check_time['endTime'],$end_time]);
            })
            ->select();
        $door_log = collection($door_log)->toArray();
        foreach ($door_log as $value) {
            $violation_log[] = [
                'type'=>2,
                'date'=>$date,
                'UserID'=>$value['UserID'],
                'UserName'=>$value['UserName'],
                'RealName'=>$value['RealName'],
                'RoleId'=>$value['RoleId'],
                'IDCard'=>$value['IDCard'],
                'message'=>'门禁打卡违规'
            ];
        }

//
        //获取当天全部学生数据以及需要参与课程数据
        $user = $user_model
            ->with([
                       'classes'=>function($query) use ($date) {
                           $query->with([
                                            'lessonClass'=>function($query) use ($date)
                                            {$query->field('l.*')->join('kx_php_lesson l','lessonId=l.id')->where('date', $date);
                                            }]
                           );}
                   ])
            ->where('roleId',3)
            ->select();
        $user = collection($user)->toArray();

        foreach ($user as $value){
            $shitang_log = $statistics_model->where(['UserID'=>$value['UserID'],'Types'=>1,'recordDate'=>['between',[$start_time,$end_time]]])->select();
            $shitang_log = collection($shitang_log)->toArray();

            foreach ($shitang_check_time as $val) {

                $res = array_filter($shitang_log, function($v) use ($val) { return strtotime($v['recordDate']) >= strtotime($val['startTime']) && strtotime($v['recordDate']) <= strtotime($val['endTime']);});

                if (!$res) {
                    $violation_log[] = [
                        'type'=>1,
                        'date'=>$date,
                        'UserID'=>$value['UserID'],
                        'UserName'=>$value['UserName'],
                        'RealName'=>$value['RealName'],
                        'RoleId'=>$value['RoleId'],
                        'IDCard'=>$value['IDCard'],
                        'message'=>$val['startTime'].' - '.$val['endTime'].'食堂考勤未打卡'
                    ];
                }
            }
            foreach ($value['classes']['lesson_class'] as $val) {
                $startTime = $val['date']. ' '.$val['startTime'];
                $endTime = $val['date']. ' '.$val['endTime'];
                $startTime = strtotime($startTime);
                $endTime = strtotime($endTime);
                $plate_log =  $statistics_model
                    ->alias('a')
                    ->field('a.*,p.classroomId')
                    ->join('kx_php_plate p','a.sbID=p.sbID')
                    ->where(['UserID'=>$value['UserID'],'Types'=>3,'recordDate'=>['between',[$start_time,$end_time]]])
                    ->select();
                $plate_log = collection($plate_log)->toArray();
                $res = array_filter($plate_log, function($v) use ($startTime,$endTime) { return strtotime($v['recordDate']) >= $startTime-3600  && strtotime($v['recordDate']) <= $startTime;});
                if (!$res){
                    $res1 = array_filter($plate_log, function($v) use ($startTime,$endTime) { return strtotime($v['recordDate']) >= $startTime  && strtotime($v['recordDate']) <= $endTime;});
                    if ($res1){
                        $violation_log[] = [
                            'type'=>3,
                            'date'=>$date,
                            'UserID'=>$value['UserID'],
                            'UserName'=>$value['UserName'],
                            'RealName'=>$value['RealName'],
                            'RoleId'=>$value['RoleId'],
                            'IDCard'=>$value['IDCard'],
                            'message'=>$val['lessonName'].'课程迟到'
                        ];
                    } else {
                        $violation_log[] = [
                            'type'=>3,
                            'date'=>$date,
                            'UserID'=>$value['UserID'],
                            'UserName'=>$value['UserName'],
                            'RealName'=>$value['RealName'],
                            'RoleId'=>$value['RoleId'],
                            'IDCard'=>$value['IDCard'],
                            'message'=>$val['lessonName'].'课程旷课'
                        ];
                    }
                }
            }
        }

        Db::table('kx_php_violation')->insertAll($violation_log);
    }

}
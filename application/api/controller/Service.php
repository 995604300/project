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
            case 'CLASS':
                return $this->get_class($arg);
            case 'DEVICE':
                return $this->get_device($arg);
            case 'EVENT':
                return $this->get_event($arg);
            case 'NEWS':
                return $this->get_news($arg);
            case 'PHOTO':
                return $this->get_photo($arg);
            case 'TIMETABLE':
                return $this->get_timetable($arg);
            case 'USER':
                return $this->get_user($arg);
            case 'CMD':
                return $this->get_cmd($arg);
            case 'STATUS':
                return $this->status($arg);
            case 'WHEATHER':
                return $this->get_wheather();
            case 'VIDEOS':
                return $this->get_video($arg);
            case 'STAGE':
                return $this->get_stage($arg);
            case 'TIANQI':
                return $this->get_tianqi($arg);
            case 'ROOM' :
                return $this->get_room($arg);
            case 'TEACHER' :
                return $this->get_teacher($arg);
            case 'PASSWORD' :
                return $this->get_password($arg);
            case 'WEBM' :
                return $this->get_webm($arg);
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

    public function get_user($arg = ''){}

    public function get_tianqi($arg='')
    {
        $city = '合肥';
        $request_url = 'http://apis.juhe.cn/simpleWeather/query?city='. $city .'&key=a76638fab83f4d6a996ff731afbaddb8';

        $weather_info = file_get_contents($request_url);
        $weather = json_decode($weather_info, true);
        return array('success' => true, 'data' => $weather);
    }

    public function get_wheather(){
        //获取外网IP的地址
        $ip_url = 'http://tool.huixiang360.com/zhanzhang/ipaddress.php';
        $ip_info = file_get_contents($ip_url);
        preg_match('/\[(.*)\]/', $ip_info, $ip);
        $ip = $ip[1];
        // 获取位置
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
        $code_json = file_get_contents($url);
        $json = json_decode($code_json, true);
        if ($json) {
            $shengfen = $json['data']['region']; //获取省份
            $city = $json['data']['city']; //获取市
            if (!$city) $city = C('city'); //不存在市
        } else {
            $city = C('city');
        }
        //获取天气数据
        $time = date('Ymd');
        if(S($city.'-'.$time)){
            $info = S($city.'-'.$time);
        }else{
            $request_url = 'https://www.sojson.com/open/api/weather/json.shtml?city='.$city;
            $weather_info = file_get_contents($request_url);
            $weather = json_decode($weather_info,true);

            if($weather['status']=='200'){
                $name = $weather['city'].'-'.$weather['date'];
                $info = $weather['data']['forecast'][0];
                S($name,$info,86400); //缓存一天
            }
        }
        return $info;
    }

    public function get_video($arg){
        $sDevice = M('Device')->where(['sn'=>$arg['sn']])->getField('class_id');
        $sDevice = $sDevice?$sDevice:1;
        //需要置顶的视频
        $info = D('Vedio')->where(['is_top'=>1,'class_id'=>$sDevice])->find();
        if(!$info){
            $info['file'] = '201807051418355454.webm';
        }
        return $info;
    }

    public function get_class($arg = '')
    {
        $sn = $arg['sn'];
        if (!empty($sn)){
            $device = D('Device')->where(array('sn'=>$sn))->find();
            if (empty($device)) {
                return array('msg'=>'当前设备未注册！');
            }
            $class = D('Class')->where(array('id'=>$device['class_id']))->relation(['teacher', 'tshow'])->find();
            $grade = D('Grade')->where(['id'=>$class['grade_id']])->find();
            $class['grade_name'] = $grade['name'];
            $class['type'] = '班主任';
            $class['num'] = D('Student')->where('class_id=' . $class['id'])->count();
            $class['content'] = $class['desc'];
            $subject = D('Subject');
            if($class && !empty($class['teacher'])){

                $class['teacher']['subject'] = $subject->where(['id' => $class['teacher']['subject_id']])->find();
            }
            if($class && !empty($class['tshow'])){
                $class['tshow']['subject'] = $subject->where(['id' => $class['tshow']['subject_id']])->find();
            }
        }else{
            $class = D('Class')->select();
        }
        return array('success'=>true, 'data'=>$class);
    }

    public function get_stage($arg = ''){
        $sn = $arg['sn'];
        //获取班级ID
        $device = D('Device')->where(['sn' => $sn])->find();
        //获取当前班级的所有课程根据teacher进行分组
        $timetable = D('Timetable')->field('teacher_id')->where(['class_id' => $device['class_id']])->group('teacher_id')->select();
        $tids = array_column($timetable, 'teacher_id');
        $data = D('Teacher')->field('realname,subject_id,face')->where(array('id' => array('in', $tids)))->select();
        foreach ($data as &$value) {
            $subject = D('Subject')->where(['id' => $value['subject_id']])->getField('name');
            $value['subject'] = $subject ? $subject . '老师' : '';
            $value['face'] = $value['face'];
        }
        return array('success' => true, 'data' => $data);
    }

    public function get_device($arg)
    {
        $sn = $arg['sn'];
        if (!empty($sn))
        {
            $device = D('Device')->where(array('sn'=>$sn))->find();
            if (empty($device)) {
                return array('msg'=>'当前设备未注册！');
            }
            $class = D('Class')->field('room')->where('id='.$device['class_id'])->find();
            $device['room'] = $class['room'];
            return $device;
        }
        return null;
    }

    public function get_event($arg)
    {
        $limit = $arg['limit'];
        return D('Event')->limit($limit)->order('create_time DESC')->select();
    }

    public function get_photo($arg)
    {
        $device = D('Device')->where(array('sn'=>$arg['sn']))->find();
        if (empty($device))
        {
            return;
        }
        $limit = $arg['limit'];
        return D('Photo')->where(array('class_id'=>$device['class_id']))->limit($limit)->order('create_time DESC')->select();
    }

    public function get_news($arg)
    {
        $device = D('Device')->where(array('sn'=>$arg['sn']))->find();
        if (empty($device))
        {
            return;
        }

        $id_r = [0, $device['class_id']];

        $limit = $arg['limit'];
        $result = D('News')->limit($limit)->order('create_time DESC')->select();
        return $result;
    }

    public function get_timetable($arg){
        $device = D('Device')->where(array('sn'=>$arg['sn']))->find();
        if (empty($device)){
            return;
        }
        $condition['class_id'] = $device['class_id'];

        if ($arg['today']){
            $now = date('H:i:s');
            if (date('w') == 0) {
                $week = 6;
            } else {
                $week = intval(date('w')) - 1;
            }
            $data = $tmp = [];
            $table_r = D('Timetable')->where($condition)->where(array('week'=>$week))->relation(true)->select();

            foreach ($table_r as $t){
                $t['active'] = '';
                if ($now > $t['lesson']['begin_time'] && $now < $t['lesson']['end_time']){
                    $t['active'] = 'true';
                }
                $data[] = $t;
            }
            $arr = array_column($data, 'lesson_id');
            sort($arr);
            foreach($arr as $val) foreach($data as $v) if($v['lesson_id'] == $val) $tmp[] = $v;
            return $tmp;
        }else{
            $lesson_r = D('Lesson')->select();
            foreach ($lesson_r as $l){
                $l['data'] = D('Timetable')->where($condition)->where(array('lesson_id'=>$l['id']))->relation(array('subject','teacher'))->select();
                $data[] = $l;
            }
            return $data;
        }
    }

    public function get_cmd($arg)
    {
        $device = D('Device')->where(array('sn'=>$arg['sn']))->find();
        if (empty($device))
        {

            return;
        }

        /* 优先处理全局指令 */
        $cmd = D('DeviceCmd')->where(array('device_id'=>0,'status'=>0))->order('create_time ASC')->find();
        if (!empty($cmd))
        {

            //$cmd['status'] = 1;
            D('DeviceCmd')->delete($cmd['id']);
            return $cmd;
        }

        $cmd = D('DeviceCmd')->where(array('device_id'=>$device['id'],'status'=>0))->order('create_time ASC')->find();
        if (!empty($cmd))
        {

            // $cmd['status'] = 1;
            //D('DeviceCmd')->doEdit($cmd);
            D('DeviceCmd')->delete($cmd['id']);
        }
        return $cmd;
    }

    public function status($arg)
    {
        $device = D('Device')->where(array('sn'=>$arg['sn']))->find();
        if (empty($device))
        {
            return;
        }

        $device['status'] = intval($arg['status']);
        D('Device')->doEdit($device);
    }

    public function get_room($arg)
    {
        $info = [];
        $time = date('Y-m-d H:i:s',time());
        $date = date('Y-m-d',time());
        $where_exam = "start_at <= '{$date}' and end_at >= '{$date}'";
        //获取考试安排
        $exam = D('Exam')->field('id,name')->where($where_exam)->find();
        if (!$exam) {
            return;
        }
        //通过设备号获取当前设备所属班级
        $device = D('Device')->where(array('sn' => $arg['sn']))->find();
        if (empty($device)) {
            return;
        }
        $map['c_id'] = $device['class_id'];
        $map['e_id'] = $exam['id'];
        $where['start_time'] = ['elt', $time];
        $where['end_time'] = ['egt', $time];
        if ($info = D('Room')->where($where)->where($map)->find()) {
            //获取考试名称
            $exam = D('Exam')->where('id=' . $info['e_id'])->find();
            $info['exam_name'] = $exam['name'];
            //获取考点名称
            $points = D('Points')->where('id=' . $info['p_id'])->find();
            $info['points_name'] = $points['name'];
            //获取班级名称
            $class = D('Class')->where('id=' . $info['c_id'])->find();
            $info['class_name'] = $class['name'];
            //获取考试名称
            $subject = D('Subject')->where('id=' . $info['s_id'])->find();
            $info['subject_name'] = $subject['name'];
            $info['s_time'] = strtotime($info['start_time']);
            $info['e_time'] = strtotime($info['end_time']);
        } else {
            $info = D('Room')->where($map)->where('end_time >="' . $time . '"')->order('start_time ASC')->find();
            if ($info) {
                //获取考试名称
                $exam = D('Exam')->where('id=' . $info['e_id'])->find();
                $info['exam_name'] = $exam['name'];
                //获取考点名称
                $points = D('Points')->where('id=' . $info['p_id'])->find();
                $info['points_name'] = $points['name'];
                //获取班级名称
                $class = D('Class')->where('id=' . $info['c_id'])->find();
                $info['class_name'] = $class['name'];
                //获取考试名称
                $subject = D('Subject')->where('id=' . $info['s_id'])->find();
                $info['subject_name'] = $subject['name'];
                $info['s_time'] = strtotime($info['start_time']);
                $info['e_time'] = strtotime($info['end_time']);
            }
        }
        return array('success'=>true,'data'=>$info);
    }

    public function get_teacher($arg){
        $device_sn = $arg['sn'];
        $teacher_info = [];
        //获取今天星期几
        if (date('w') == 0) {
            $week = 6;
        } else {
            $week = intval(date('w')) - 1;
        }
        //通过设备号获取当前班级
        $device = D('Device')->where(['sn' => $device_sn])->find();

        //获取当前时间
        $time = date('H:i:s', time());
        $where = "begin_time < '{$time}' and end_time > '{$time}'";
        //通过当前时间获取第几节课,如果不存在,则获取最近的下一节课
        $lesson = D('Lesson')->where($where)->find();
        if ($lesson) {
            $map = ['class_id' => $device['class_id'], 'week' => $week, 'lesson_id' => $lesson['id']];
            //通过班级ID,LessonID，星期几,获取课程表信息
            $time_table = D('Timetable')->field('teacher_id')->where($map)->find();
            if(!$time_table){
                return array('success' => true, 'data' =>[]);
            }
            //获取教师信息
            $teacher_info = D('Teacher')
                ->field('id,username,realname,domain,year,mobile,face')
                ->where('id=' . $time_table['teacher_id'])
                ->find();
            $teacher_info['face'] = C('UPLOAD_URL') . $teacher_info['face'];
        } else {
            //获取离当前最近的一堂课
            $next_lesson = D('Lesson')->where('begin_time > "' . $time . '"')->order('begin_time asc')->find();
            if ($next_lesson) {
                $map = ['class_id' => $device['class_id'], 'week' => $week, 'lesson_id' => $next_lesson['id']];
                //通过班级ID,LessonID，星期几,获取课程表信息
                $time_table = D('Timetable')->field('teacher_id')->where($map)->find();
                if(!$time_table){
                    return array('success' => true, 'data' =>[]);
                }
                //获取教师信息
                $teacher_info = D('Teacher')
                    ->field('id,username,realname,domain,year,mobile,face')
                    ->where('id=' . $time_table['teacher_id'])
                    ->find();
                $teacher_info['face'] = C('UPLOAD_URL') . $teacher_info['face'];
            } else {
                //获取当前班级信息
                $class_info = D('Class')->field('teacher_id')->where('id=' . $device['class_id'])->find();
                //获取教师信息
                if(!$class_info['teacher_id'] == null){
                    return array('success' => true, 'data' =>[]);
                }
                $teacher_info = D('Teacher')
                    ->field('id,username,realname,domain,year,mobile,face')
                    ->where('id=' . $class_info['teacher_id'])
                    ->find();
                $teacher_info['face'] = C('UPLOAD_URL') . $teacher_info['face'];
            }
        }
        return array('success' => true, 'data' => $teacher_info);
    }

    //校验设备密码
    public function get_password($arg){
        $sn = $arg['sn'];
        $pass = $arg['password'];
        $info = D('Device')->where(array('sn'=>$sn,'exit_pass' => $pass))->find();
        if ($info) {
            return array('success' => true);
        }else{
            return array('success' => false);
        }
    }

    public function get_webm($arg){
        dump($arg);
        return 1111;
    }
}
?>
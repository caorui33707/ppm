<?php
/**
 * 定时处理任务-统计分析
 */
namespace Task\Controller;
use Think\Controller;

class StatisticsController extends Controller {

    private $daily_url = '/statistics/daily_statistics'; // 地址


//    public function  _initialize(){
//        $this->daily_url = C('ADMIN_ROOT').$this->daily_url;
//        load('Admin/function');
//    }

    public function __construct(){
        $user = array();
        $this->daily_url = C('ADMIN_ROOT').$this->daily_url;
        load('Admin/function');
        $user['uid'] = 1;
        session(ADMIN_SESSION,$user);
    }

    public function daily_statistics(){
        set_time_limit(300);
        $end_date = '2018-01-01';
        $date = date('Y-m-d',strtotime('-1 day'));

        $this->put_file('start');
        while (ture){
            $this->put_file('-'.$date);
            //dump($daily_url);
            $array = array($date);
            R("Admin/Statistics/daily_statistics",$array);
            //$ret = post($daily_url); dump($ret);exit;//
            if($date>$end_date) $date = date('Y-m-d', strtotime("$date -1 day"));
            else break;
        }
        $this->put_file('end');
        // curl http://cg.test.ppmiao.cn/task.php/pushMsg/run_new/device_type/1
       // post();
        return;
    }

   //记录日志
    public function put_file($s){
        $fileName = LOG_PATH  .date("Y-m-d"). 'statistics.log';
        $s  = '['.date("Y-m-d H:i:s").']  '.$s."\r\n";
        file_put_contents($fileName,$s,FILE_APPEND);
    }

}
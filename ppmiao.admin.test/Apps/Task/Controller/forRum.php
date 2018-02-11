<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/11 0011
 * Time: 11:46
 */

namespace Home\Controller;


class forRum{

    public function for_run(){
        $time = time();
        $pid = pcntl_fork();    //创建子进程

        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die('could not fork');
        } else if ($pid) {

            $device_type = 1;

            if($device_type == 2){
                $ver = 'android_ver';
            } else if($device_type == 1){
                $ver = 'ios_ver';
            }else{
                return;
            }

            $where = array(
                'status'=>0,
                'is_delete'=>0,
                'push_time'=>array('elt',$time),
                $ver=>array('egt','2.0')
            );

            $list = M('MsgPush')->field('id,target,content,registration_id,android_ver,ios_ver,push_extra')
                ->where($where)->select();//"status = 0 and is_delete=0  and push_time<=$time"

            $this->run($list,$device_type);
            pcntl_wait($status,WNOHANG); //等待子进程中断，防止子进程成为僵尸进程。
        } else {

            $device_type = 2;

            if($device_type == 2){
                $ver = 'android_ver';
            } else if($device_type == 1){
                $ver = 'ios_ver';
            }else{
                return;
            }

            $where = array(
                'status'=>0,
                'is_delete'=>0,
                'push_time'=>array('elt',$time),
                $ver=>array('egt','2.0')
            );

            $list = M('MsgPush')->field('id,target,content,registration_id,android_ver,ios_ver,push_extra')
                ->where($where)->select();//"status = 0 and is_delete=0  and push_time<=$time"

            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $this->run($list,$device_type);
            exit(0) ;
        }
    }

}
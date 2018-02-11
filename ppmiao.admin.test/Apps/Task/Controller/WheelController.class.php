<?php
/**
 * 定时处理任务-钱包计息
 */
namespace Task\Controller;
use Think\Controller;

class WheelController extends Controller {

    /**
     * 统计指定时段内用户累计投资金额
     */
    public function sortDueUser(){
        $taskWheelObj = M('TaskWheel');//A轮融资活动回馈用户记录表
        $start_time = date('Y-m-d', time());//当前日期
        $front_time = date('Y-m-d',time()-24*3600);// 前一天
        $action_st = "2016-01-21";//活动开始时间
        $action_et = "2016-01-24";//活动结束时间
        //判断是否在活动期间之内
        $flg = $this->check_date($start_time);
        if(!$flg){
            exit("活动结束");
        }
        //统计记录
        $tongji_sql = "SELECT n.`id`,n.`real_name`,n.`username`, m.`add_time`,SUM(m.`due_capital`) AS due_total FROM stone.`s_user_due_detail` AS m,stone.`s_user` AS n WHERE m.`add_time`>='".$front_time." 00:00:00.000000' AND m.`add_time`<='".$front_time." 23:59:59.999000' AND n.`id` = m.`user_id` AND m.`project_id` IN(SELECT m.`id` FROM stone.`s_project` AS m WHERE m.`new_preferential` = 4  AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') GROUP BY m.`user_id`";
        $due_list_arr = M()->query($tongji_sql);
        if(!$due_list_arr){
            exit("没有数据可以处理");
        }
        foreach($due_list_arr as $k=>$v){
            //判断当前日期当前用户是否已经记录
            $exsit_list = $taskWheelObj->where(array('user_id'=>$v['id'],'due_time'=>$front_time,'add_time'=>$start_time))->find();
            if($exsit_list){//update
                $data = array(
                    'amount'=>$v['due_total'],
                );
                $taskWheelObj->where(array('user_id'=>$v['id'],'due_time'=>$front_time,'add_time'=>$start_time))->save($data);
            }else{ //add
                $data = array(
                    'user_id'=>$v['id'],
                    'real_name'=>$v['real_name'],
                    'user_name'=>$v['username'],
                    'amount'=>$v['due_total'],
                    'due_time'=>$front_time,
                    'add_time'=>$start_time
                );
                $taskWheelObj->add($data);
            }
        }
        exit("处理完成");

    }
    /**
     * 判断是否是在A轮融资活动期间
     * return boolean
     */
    public function check_date($date){
        $st = "2016-01-22";
        $et = "2016-01-24";
        if($date>=$st && $date<=$et){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 定时发送短信
     */
    public function task_send_msg(){
        $taskWheelObj = M('TaskWheel');//A轮融资活动回馈用户记录表
        $start_time = date('Y-m-d', time());//当前日期
        $front_time = date('Y-m-d',time()-24*3600);// 前一天
        //判断是否在活动期间之内
        $flg = $this->check_date($start_time);
        if(!$flg){
            exit("活动结束");
        }
        //查询需要发送短信的用户
        $where = array(
            'due_time'=>$front_time
        );
        $need_send_user_list = $taskWheelObj->where($where)->select();
        if(!$need_send_user_list){
            exit("没有需要处理的数据");
        }
        foreach($need_send_user_list as $k=>$v){
            $msg_id = $this->send_msg($v['amount'],$v['user_name']);

            if($msg_id){
                //修改记录表
                $modify_time = date("Y-m-d H:i:s",time());
                $taskWheelObj->where(array('id'=>$v['id']))->save(array('msg_no'=>$msg_id,'modify_time'=>$modify_time));
            }
        }
        exit("发送短信完成");

    }
    //单条发送短信
    public function send_msg($amount,$mobile){
        $prize_list_arr = array(
            '0'=>'300元京东礼品卡',
            '1'=>'800元Beats耳机',
            '2'=>'1500元京东礼品卡',
            '3'=>'ipad mini4 64G',
            '4'=>'iphone6s 64G',
        );
        if($amount<10000){
            $prize_str =$prize_list_arr[0];
        }else if($amount>=10000 && $amount<20000){
            $prize_str =$prize_list_arr[1];
        }else if($amount>=20000 && $amount<50000){
            $prize_str =$prize_list_arr[2];
        }else if($amount>=50000 && $amount<100000){
            $prize_str =$prize_list_arr[3];
        }else if($amount>=100000 && $amount<150000){
            $prize_str =$prize_list_arr[4];
        }
        $msg = "尊敬的用户，截止到昨天24点，您本次活动已投资".$amount."元，距离获取".$prize_str."已经很近了哦，继续加油吧！";

        $params = 'account='.C('SMS_INTDERFACE.account');
        $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
        $params .= '&mobile='.$mobile;
        $params .= '&msg='.$msg;
        $params .= '&needstatus=true';

        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }

        $msgid = trim($arr[1][0]);
        return $msgid;
    }
    
    /**
    * 统计新手活动用户
    * @date: 2017-6-19 上午11:46:14
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function staticEvent20170407User(){
        $start_time = date("Y-m-d",strtotime('-65 day'));
        $end_time = date("Y-m-d",strtotime('-64 day'));
        $sql = "insert into s_hd20170407_statics(user_id,user_name,mobile,reg_time,create_time) SELECT id,real_name,mobile,add_time,now() from s_user WHERE real_name_auth = 1 and add_time>='$start_time' and add_time<'$end_time'";
        //echo $sql;        
        M()->execute($sql);
    }
    
    public function staticEvent20170407Amount(){
        
        //$url = 'http://server.ppmiao.com/stone-rest/payment/activity/inviteFriend/getNewProjects.htm';    
        $url = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getNewProjects.htm';    
        $list = M('hd20170407Statics')->field('id,user_id,mobile')->where('status = 0')->select();
        
        foreach ($list as $val) {           
            $dd =array(
                'mobile'=>$val['mobile']
            );
            $res = json_decode($this->send_post($url, $dd),true);
            $awardCash = 0;
            if($res['code'] == 0){
                
                $compeleteCount = $res['result'][0]['compeleteCount'];
                $intTotal = $res['result'][0]['intTotal'];
                            
                if($intTotal >= 30000 && $intTotal <50000){
                    $awardCash = 20;
                } else if($intTotal >= 50000 && $intTotal <100000){
                    $awardCash = 30;
                } else if($intTotal >= 100000 && $intTotal <140000){
                    $awardCash = 40;
                } else if($intTotal >= 140000){
                    $awardCash = 50;
                }
                if($compeleteCount>4 && $intTotal>=30000){
                    $awardCash+=10;
                }
            }
            
            $row = array(
                'amount' => $awardCash,
                'status' => 1,
                'static_time'=>date('Y-m-d H:i:s')

            );
            
            M('hd20170407Statics')->where('id='.$val['id'].' and user_id='.$val['user_id'])->save($row);
            
        }
        
        unset($list);
        
    }
    
    public function sendCash(){        
        $list = M('hd20170407Statics')->field('id,user_id,mobile,amount')->where('status = 1 and amount >0')->select();   
        $update_time = date("Y-m-d H:i:s");
        $add_time = date("Y-m-d 00:00:00");        
        $expire_time = date("Y-m-d 23:59:59",strtotime('+30 day'));        
        $dd = array(
            'title'=>'新手特训营现金券',
            'subtitle'=>'投资返现',
            'type'=>0,
            'add_user_id'=>1,
            'modify_time'=>$update_time,
            'modify_user_id'=>0,
            'create_time'=>time(),
            'expire_time'=>$expire_time,
            'add_time'=>$add_time

        );
        
        foreach ($list as $val){
            $dd['amount'] = $val['amount'];
            $dd['user_id'] = $val['user_id'];         
            
            M("UserCashCoupon")->startTrans();            
            $res = M("UserCashCoupon")->add($dd);
            $b = M('hd20170407Statics')->where('id='.$val['id'] .' and user_id='.$val['user_id'])->save(array('status'=>2,'send_time'=>$update_time));
            
            if($res && $b) {
               M("UserCashCoupon")->commit();
            } else {
               M("UserCashCoupon")->rollback();
            }
        }
        echo 'ok';
    }
    
    
    public function test(){
        echo 'test...';
    }
    
    private function send_post($url, $post_data) {
    
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
    
    /*
    public function staticAmt(){
        
        set_time_limit(0);
        //$url = 'http://server.ppmiao.com/stone-rest/payment/activity/inviteFriend/getNewProjects.htm';
        $url = 'https://notify.ppmiao.com/ppmiao-rest/payment/activity/inviteFriend/getNewProjects.htm';
        $list = M('hd20170407Statics')->field('id,user_id,mobile')->where("create_time>'2017-08-19' and create_time<'2017-09-05'")->select();
                      
        
        foreach ($list as $val) {
            $dd =array(
                'mobile'=>$val['mobile']
            );
            $res = json_decode($this->send_post($url, $dd),true);
            $awardCash = 0;
            if($res['code'] == 0){
                
                $val['amount'] = 0;
                $compeleteCount = $res['result'][0]['compeleteCount'];
                $intTotal = $res['result'][0]['intTotal'];
    
                if($intTotal >= 30000 && $intTotal <50000){
                    $awardCash = 20;
                } else if($intTotal >= 50000 && $intTotal <100000){
                    $awardCash = 30;
                } else if($intTotal >= 100000 && $intTotal <140000){
                    $awardCash = 40;
                } else if($intTotal >= 140000){
                    $awardCash = 50;
                }
                if($compeleteCount>4 && $intTotal>=30000){
                    $awardCash+=10;
                }

                
                if($awardCash>0) {
                    $val['amount'] = $awardCash;
                }
                
                M('hd20170407StaticsTmp')->add($val);
                
            }
            
            
        }
        unset($list);
    }
    */

}
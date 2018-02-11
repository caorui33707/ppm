<?php
namespace Admin\Controller;


class MeController /*extends AdminController*/ {

    
    
    public function count_10(){
        
        exit('403');
        
        ini_set("memory_limit", "10000M");
        ini_set("max_execution_time", 0);
        
        $list = M('User')->field('id,card_no,device_type')->where("add_time >='2016-10-01' and add_time<'2016-11-1' and real_name_auth = 1")->select();
        echo count($list);
        echo "<br/>";
        
        $cnt = 0;
        $m = 0;
        $w = 0;
        $a = 0;
        $ios = 0;
        $qt = 0;
        $ppp = array();
        foreach ($list as $val){
            $user_id = $val['id'];
            $res = M('UserDueDetail')->where("user_id = $user_id and add_time >='2016-10-01' and add_time<'2016-11-1'")->find();
            if($res) {
                $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
                if($sex % 2 != 0) {
                    $m +=1;
                } else {
                    $w +=1;
                }
                
                if($val['device_type'] == 1){
                    $ios +=1;
                } else if($val['device_type'] == 2){
                    $a +=1;
                } else {
                    $qt +=1;
                }
                $cnt+=1;
            }
            else{
                $ppp[] = $user_id;
            }
        }
        
        echo 'buy project:'.$cnt;
        echo "<br/>";
        echo 'm:'.$m;
        echo "<br/>";
        echo 'w:'.$w;
        echo "<br/>";
        
        echo 'a:'.$a;
        echo "<br/>";
        echo 'ios:'.$ios;
        echo "<br/>";
        echo 'qt:'.$qt;
        echo "<br/>";
        
        $tt = 0;
        
        foreach ($ppp as $val) {
            $res = M('UserWalletRecords')->where("user_id = $val and add_time >='2016-10-01' and add_time<'2016-11-1' and type=1 and pay_status=2")->find();
            if($res) {
                $cnt += 1;
            }
        }
        
        echo 'tt:'.$tt;
    }
    
    public function tg_channel_count(){
        $this->assign('tg_channel_count');
    }
    
    
    /*
     * 统计 每日还本付息 ，一天跑一次
     */
    public function test_hbfx() {
        exit('403');
        
        ini_set("max_execution_time", 0);
    
        $now_time = date('Y-m-d');
    
        $stat_time = date('Y-m-d',strtotime("-1 day"));
         
        $projectObj = M('Project');
    
        $rechargeLogObj = M('RechargeLog');
    
        $project_list = M('Project')->field('id,financing,user_interest')->where('status <5 and is_delete=0')->select();
    
        $_tmp = array();
    
        foreach ($project_list as $val) {
    
            $val['total_amount'] = M('RechargeLog')->field('amount')->where("project_id = ".$val['id'] ." and status=2 and user_id >0 and add_time < '$now_time' ")->sum('amount');
    
            $val['day_interest'] = $val['total_amount'] * $val['user_interest'] / 100 / 365;
    
            $_tmp[] = $val;
    
        }
    
    
        $res[1]['name'] = '杭州洛亚物资有限公司';
        $res[2]['name'] = '杭州中诺金属材料有限公司';
        $res[3]['name'] = '杭州晟硕贸易有限公司';
    
        
    
    
        foreach ($_tmp as $val) {
            if ($val['financing'] == $res[1]['name']) {
                $res[1]['total_interest'] +=$val['day_interest'];
                $res[1]['amount'] += $val['total_amount'];
            } else if($val['financing'] == $res[2]['name']){
                
                $res[2]['total_interest'] +=$val['day_interest'];
                $res[2]['amount'] += $val['total_amount'];
                
            } else if($val['financing'] == $res[3]['name']){
                $res[3]['total_interest'] +=$val['day_interest'];
                $res[3]['amount'] += $val['total_amount'];
            }
            
        }
    
    
        for ($i=1;$i<=count($res);$i++) {
            $dd['treaty_rate'] = M('ProjectProtocolRate')->where(array('financing'=>$res[$i]['name'],'add_time'=>$stat_time))->getField('rate');
            if(!$dd['treaty_rate']) {
                $dd['treaty_rate'] = 10;
            }
            $dd['avg_rate'] = ($res[$i]['total_interest']*365)/$res[$i]['amount'] * 100;
            $dd['avg_rate'] = number_format($dd['avg_rate'], 2, '.', '');
            $dd['earnings'] = ($dd['treaty_rate'] - $dd['avg_rate']) / 100 * $res[$i]['amount'] / 365;
            $dd['amount'] = $res[$i]['amount'];
            $dd['stat_date'] = $stat_time;
            $dd['financing_id'] = $i;
            $dd['create_time'] = time();
            
            print_r($dd);
            echo "<br/>";
            //M('StatisticsDailyProjectEarnings')->add($dd);
        }
    }
    
    /*
     * 更改银行需求
     * user_bank          bank_card_no requestid 为空 requestid_bf 为空
     * user_due_detail    card_no;
     * recharge_log       card_no
     * investment_detail  card_no
     */
    
    public function changeBank() {
        
    }
    
    public function dele() {
        /*
        $sql = "delete from s_user_cash_coupon where title='金秋理财现金券'";
        if(M()->execute($sql)) {
            echo '0k';
        }*/
    }
    
    
    
    
    public function evt20161215() {
        
        exit('403');
        
        header("Content-type: text/html; charset=utf-8");
        
        //$sql = "SELECT d.`user_id` as user_id ,d.`due_capital` as due_capital ,d.`due_interest` as due_interest ,d.`duration_day` as duration_day ,p.`title` as title,d.`add_time` as add_time  from `s_user_due_detail` as d ,`s_project` as p where d.`project_id` =p.`id` and p.`new_preferential` != 1 and d.`add_time` >='2016-09-01' and d.`add_time` <'2016-09-08' and d.`user_id` > 0";
        
        
        $sql  ="SELECT d.`user_id` as user_id ,d.`due_capital` as due_capital ,d.`due_interest` as due_interest ,d.`duration_day` as duration_day ,p.`title` as title,d.`add_time` as add_time  FROM `s_user_due_detail` d,`s_project` p,`s_user` u
 	WHERE d.`user_id`> 0
   and d.`add_time`>= '2016-12-12'
   and d.`add_time`<= '2016-12-14 23:59:59'
   and p.new_preferential = 2 and `user_id` = u.id and d.project_id = p.id";
        
        $list = M()->query($sql);
        echo count($list)."<br/>";
        echo "<table>";
        
        echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='10%'>手机号码</td>
                <td width='10%'>用户名</td>
                <td width='10%'>本金</td>
                <td width='10%'>总利息</td>
                <td width='10%'>天数</td>
                <td width='10%'>三天金额</td>
                <td width='10%'>三天金额2</td>
                <td width='10%'>标题</td>
                <td width='10%'>投资日期</td>
               </tr>";
        
        $n = 1;
        
        $total_hb = 0;
        
        
        foreach ($list as $val) {
            $value = $val['due_interest'] / $val['duration_day'] * 3 *2;
            
            $value2 = round($value,2);
            
            if($value2 < 1) {
                $value2 = 1;
            }
            
            $total_hb += $value2;
            
            $userinfo = M('User')->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
            $user_id = $val['user_id'];
            
            /*
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$userinfo['username']."</td>";
            echo "<td width='10%'>".$userinfo['real_name']."</td>";
            echo "<td width='10%'>".$val['due_capital']."</td>";
            echo "<td width='10%'>".$val['due_interest']."</td>";
            echo "<td width='10%'>".$val['duration_day']."</td>";
            echo "<td width='10%'>".$value."</td>";
            echo "<td width='10%'>".$value2."</td>";
            echo "<td width='10%'>".$val['title']."</td>";
            echo "<td width='10%'>".$val['add_time']."</td>";
            echo "<tr/>";
            */
            
            
           $sqlyy =
            "INSERT INTO `s_user_cash_coupon` (`title`,`amount`,`subtitle`,`expire_time`,`add_user_id`,`modify_time`,`add_time`,`status`,`type`,`user_id`,`create_time`) 
                VALUES ('双十二感恩回馈','".$value2."','感谢支持','2017-02-11 12:00:00','1','2016-12-15 12:00:00','2016-12-15 12:00:00','0','1','".$user_id."',unix_timestamp());";
           
           echo $sqlyy."<br/>";
            /*
           if(M()->execute($sqlyy)) {
               $n++;
               echo '成功:'.$sql."<br/>";
           }
           */
            $n++;
        }
        
        echo "</table>";
        echo '总共发放：'.$total_hb;
    }
    
    
    public function evt201611112() {
        
        exit('403');
        
        header("Content-type: text/html; charset=utf-8");
    
        //$sql = "SELECT d.`user_id` as user_id ,d.`due_capital` as due_capital ,d.`due_interest` as due_interest ,d.`duration_day` as duration_day ,p.`title` as title,d.`add_time` as add_time  from `s_user_due_detail` as d ,`s_project` as p where d.`project_id` =p.`id` and p.`new_preferential` != 1 and d.`add_time` >='2016-09-01' and d.`add_time` <'2016-09-08' and d.`user_id` > 0";
    
    
        $sql  ="SELECT d.`user_id` as user_id ,d.`due_capital` as due_capital ,d.`due_interest` as due_interest ,d.`duration_day` as duration_day ,p.`title` as title,d.`add_time` as add_time  FROM `s_user_due_detail` d,`s_project` p,`s_user` u
 	WHERE d.`user_id`> 0
   and d.`add_time`>= '2016-11-04'
   and d.`add_time`<= '2016-11-10 23:59:59'
   and p.new_preferential = 6 and `user_id` = u.id and d.project_id = p.id";
    
        $list = M()->query($sql);
        /*
        echo count($list)."<br/>";
        echo "<table>";
    
        echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='10%'>手机号码</td>
                <td width='10%'>用户名</td>
                <td width='10%'>本金</td>
                <td width='10%'>总利息</td>
                <td width='10%'>天数</td>
                <td width='10%'>七天金额</td>
                <td width='10%'>七天金额2</td>
                <td width='10%'>标题</td>
                <td width='10%'>投资日期</td>
               </tr>";
        */
        //$n = 1;
    
        //$total_hb = 0;
    
    
        foreach ($list as $val) {
            $value = $val['due_interest'] / $val['duration_day'] * 7;
    
            $value2 = round($value,2);
            
            if($value2 < 1) {
                $value2 = 1;
            }
    
            //$total_hb += $value2;
    
            $userinfo = M('User')->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
            $user_id = $val['user_id'];
            /*
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$userinfo['username']."</td>";
            echo "<td width='10%'>".$userinfo['real_name']."</td>";
            echo "<td width='10%'>".$val['due_capital']."</td>";
            echo "<td width='10%'>".$val['due_interest']."</td>";
            echo "<td width='10%'>".$val['duration_day']."</td>";
            echo "<td width='10%'>".$value."</td>";
            echo "<td width='10%'>".$value2."</td>";
            echo "<td width='10%'>".$val['title']."</td>";
            echo "<td width='10%'>".$val['add_time']."</td>";
            echo "<tr/>";
            */
    
             $sqlyy =
             "INSERT INTO `s_user_cash_coupon` (`title`,`amount`,`subtitle`,`expire_time`,`add_user_id`,`modify_time`,`add_time`,`status`,`type`,`user_id`,`create_time`)
             VALUES ('7天双息活动奖励','".$value2."','双11活动专享','2016-12-11 00:00:00','1','2016-11-11 18:00:00','2016-11-11 18:00:00','0','1','".$user_id."',unix_timestamp());";
              
             echo $sqlyy."<br/>";
            /*
             if(M()->execute($sqlyy)) {
             $n++;
             echo '成功:'.$sql."<br/>";
             }
            $n++;*/
        }
    
        //echo "</table>";
        //echo '总共发放：'.$total_hb;
    }
    
    public function ttt()
    {
        
        $user_list = M('User')->field('id')->where("add_time>='2016-08-19' and card_no_auth= 1")->select();
        
        
        
        $total = 0;
        
        foreach ($user_list as $val) {
            $total += M('UserAccount')->where('user_id='.$val['id'])->getField('wallet_totle');
        }
        
        echo $total;
    }
    
    
    public function test(){
        exit('403');
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
         
        $row['title'] = '';
        
        $row['subtitle'] = I('post.subtitle','','strip_tags');
        
        $row['interest_rate'] = I('post.interest_rate', 0, 'float');//金额
        
        $row['min_invest'] = I('post.min_invest', 0, 'float');	//红包最小投资金额：
        
        $row['min_due'] = I('post.min_due', 0, 'int');	//红包最小投资期限：
        
        $row['due_time'] = I('post.due_date', 0, 'int');	//红包到期天数：
        
        $send_scope = I('post.send_scope', 0, 'int');//发放范围
        
        if (!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));
        
        if (!$row['subtitle']) $this->ajaxReturn(array('subtitle' => 0, 'info' => '请输入子标题'));
        
        if (!$row['interest_rate'] || $row['interest_rate'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入券包利率'));
        
        if (!$row['min_due']  || $row['min_due'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资金额'));
        
        if (!$row['due_time'] || $row['due_time'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资期限'));
        
        
        //红包到期天数 等于当前时间 + $due_time
        $row['expire_time'] = date("Y-m-d H:i:s",time()+$row['due_time'] * 86400);
        $row['add_user_id'] = $row['modify_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];
        $row['add_time'] =  $row['modify_time'] = date("Y-m-d H:i:s");
        $row['status'] = 0;
        
        $row['coupon_id'] = M('UserInterestCoupon')->max('coupon_id') + 1;
        
        //$send_scope 1 全部用户 ，2 指定用户
        if(2 == $send_scope) {
        
            $userIdStr = I('post.userId', '', 'strip_tags');//发放范围
        
            if($userIdStr == "") {
                $this->ajaxReturn(array('status' => 0, 'info' => '请添加需要指定发放的用户!'));
            }
        
            $userIdArr = explode("#",$userIdStr);
            $row['scope'] = 1;
            $res = false;
        
            M("UserInterestCoupon")->startTrans();
        
            foreach ($userIdArr as $val) {
        
                $row['user_id'] = $val;
        
                $res = M("UserInterestCoupon")->add($row);
            }
        
            if($res) {
                M("UserInterestCoupon")->commit();
                $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
            }else{
                M("UserInterestCoupon")->rollback();
                $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
            }
            exit;
        } else {
        
            $row['scope'] = 2;
        
            $userList = M('User')->field('id')->select();
        
            $res = false;
        
            M("UserInterestCoupon")->startTrans();
        
            foreach ($userList as $val) {
        
                $row['user_id'] = $val['id'];
        
                $res = M("UserInterestCoupon")->add($row);
            }
        
            if($res) {
                M("UserInterestCoupon")->commit();
                $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
            }else{
                M("UserInterestCoupon")->rollback();
                $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
            }
            exit;
        }
    }
    
    public function staticUser() {
        //exit('403');
        ini_set("memory_limit", "1000M");
        
        ini_set("max_execution_time", 0);
        
        // 201  - AppStore_personal
        //195  - AppStore_common
        //194 - AppStore_pro
        //203  AppStore_feedback
        //210  AppStore_welfare
        //204 AppStore_honorable

        //exit('403');
        //20 oppo
        //9 小米
        //178 酷华
        //184 木蚂蚁 24
        //185 pp;
        //206 流量宝
        
        //204 至遵版
        
        $channel_id =9;// 203;//'201,195,194,203,204,210';
        
        
        $project_list = M('Project')->field('id')->where('status in(2,3,4) and is_delete=0')->select();
    
        $user_list = M('User')->field('id,username,add_time,channel_id,channel')
                    ->where("channel_id in($channel_id) and `real_name_auth` = 1 and add_time >='2017-01-01' and add_time<'2017-02-10'")->select();
        
        $user_id = '';
        
        foreach ($user_list as $val) {
            $user_id .= ','.$val['id'];
        }
        
        $user_id = ltrim($user_id,',');
        
        $total_money = 0;
    
        foreach ($project_list as $val) {
            
            $id= $val['id'];
            
            $amount = M('RechargeLog')->where("project_id=$id and status=2 and user_id in($user_id)")->sum('amount');
    
            if($amount) {
                $total_money +=$amount;
            }
        }
        
        echo $total_money;

    }
    
    public function run_profit() {
        
        exit('403');
        
        header("Content-type: text/html; charset=utf-8");
        
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
    
        $date = date("2016-08-08");
    
        $project_list = M('Project')->field('id')->where('status in(2,3,4) and is_delete=0')->select();
    
        //平台总存量统计
        $totalMoney = 0;
        foreach ($project_list as $val) {
            $totalMoney += M('RechargeLog')->field('amount')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and add_time<'."'$date'")->sum('amount');
        }
        echo 'total :'.$totalMoney;
    
        //渠道存量统计
        $channelPid = M('Constant')->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
        $channelList = M('Constant')->field('id,cons_value')->where(array('parent_id'=>$channelPid))->select();
    
        $res = array();
    
        foreach ($channelList as $val) {
            $r['channel_id'] = $val['id'];
            $r['money'] = 0;
            $r['cons_value'] = $val['cons_value'];
            $res[$val['id']] = $r;
        }
    
        foreach ($project_list as $val) {
    
            $RechargeLogList = M('RechargeLog')->field('amount,user_id')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and add_time<'."'$date'")->select();
    
            foreach ($RechargeLogList as $v) {
    
                $channelId = M('User')->where(array('id'=>$v['user_id']))->getField('channel_id');
    
                //if($channelId == 0) { //内部测试用户
                 //   continue;
                //}
                
               $res[$channelId]['money'] += $v['amount'];
                
            }
        }
    
        
        echo "<br/>";
        
        foreach ($res as $val){
            
            echo "channel_id:".$val['channel_id'] .'--'."channel_name:".$val['cons_value'].'---'. $val['money']."<br>";
        }
    }
    
    
    public function channl(){
        exit();
        //渠道存量统计
        $channelPid = M('Constant')->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
        $channelList = M('Constant')->field('id,cons_value')->where(array('parent_id'=>$channelPid))->select();
        
        $res = array();
        
        foreach ($channelList as $val) {
            $r['channel_id'] = $val['id'];
            $r['money'] = 0;
            $r['cons_value'] = $val['cons_value'];
            $res[$val['id']] = $r;
        }
        
        print_r($res);
    }
    
    
    public function ri(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        
        $date = date("Y-m-d");
        
        $project_list = M('Project')->field('id')->where('status in(2,3,4) and is_delete=0')->select();
        
        //平台总存量统计
        /*
        $totalMoney = 0;
        foreach ($project_list as $val) {
            $totalMoney += M('RechargeLog')->field('amount')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and add_time<'."'$date'")->sum('amount');
        }
        $row['money'] = $totalMoney;
        $row['add_time'] = time();
        $row['stat_date'] =  date("Y-m-d",strtotime("-1 day"));
        $row['channel_id'] = 0;
        M('StatisticsNetprofit')->add($row);
       
        
        
        //渠道存量统计
        $channelPid = M('Constant')->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
        $channelList = M('Constant')->field('id')->where(array('parent_id'=>$channelPid))->select();
        
        $res = array();
        
        foreach ($channelList as $val) {
            $r['channel_id'] = $val['id'];
            $r['money'] = 0;
            $res[$val['id']] = $r;
        }
         */
        
        $default_channel_money = 0;
        
        foreach ($project_list as $val) {
        
            $RechargeLogList = M('RechargeLog')->field('amount,user_id')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and add_time<'."'$date'")->select();
        
            foreach ($RechargeLogList as $v) {
        
                $channelId = M('User')->where(array('id'=>$v['user_id']))->getField('channel_id');
        
                if($channelId == 0) { //内部测试用户
                   // continue;
                    $default_channel_money +=$v['amount'];
                }
        
               // $res[$channelId]['money'] += $v['amount'];
            }
        }
        
        echo 'default_channel_money:'.$default_channel_money;
        
        
    }
    
    public function tmf(){
        exit();
        
        $sql="update s_user set username = '15073547208',mobile='15073547208' where id=42990 limit 1";
        $r = M()->execute($sql);
        
        exit;
        
        exit('--444');
        $sql1 = "update `s_project` set able=1092900 where id=263 limit 1";
        
        $sql2 = "update `s_project` set able=651600 where id=262 limit 1";
        
        
        $r = M()->execute($sql1);
        $r = M()->execute($sql2);
        
        if(M()->execute($sql1) && M()->execute($sql2)) {
            echo 'ok';
        }
        
        
        
        $sql = "CREATE TABLE `s_event_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL COMMENT '1红包2券包3现金',
  `act` tinyint(1) unsigned NOT NULL COMMENT '1注册2登录',
  `create_time` int(10) NOT NULL,
  `begin_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `add_user_id` int(10) unsigned NOT NULL,
  `edit_user_id` int(10) unsigned NOT NULL,
  `edit_time` int(10) unsigned NOT NULL,
  `content` varchar(256) NOT NULL,
  `memo` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        
        $r = M()->execute($sql);
        print_r($r);
        
        exit('403');
        
        $sql1 = "INSERT INTO `s_auth_rule` VALUES (null, 'admin', '1', 'Admin/event/event_conf_index', '活动管理 - 活动配置', '1', '')";
        $sql2 = "INSERT INTO `s_auth_rule` VALUES (null, 'admin', '1', 'Admin/event/event_conf_add', '活动管理 - 活动配置 - 增加', '1', '')";
        $sql3 = "INSERT INTO `s_auth_rule` VALUES (null, 'admin', '1', 'Admin/event/event_conf_edit', '活动管理 - 活动配置 - 编辑', '1', '')";
        $sql4 = "INSERT INTO `s_auth_rule` VALUES (null, 'admin', '1', 'Admin/event/event_conf_delete', '活动管理 - 活动配置 - 删除', '1', '');";
        
        if(M()->execute($sql1)
            && M()->execute($sql2)
            && M()->execute($sql3)
            && M()->execute($sql4)
        ) {
            echo 'ok';
        } else {
            echo 'err';
        }
        exit('fff');
        
        
        
        
        
        $sql1 = "update s_statistics_daily_project_earnings set financing_id=3 where financing_id=2";
        
        $sql2 = "update s_statistics_daily_project_earnings set financing_id=2 where financing_id=1";
        
        $sql3 = "update s_statistics_daily_project_earnings set financing_id=1 where financing_id=3";
        
        
        $r = M()->execute($sql1);
        $r = M()->execute($sql2);
        $r = M()->execute($sql3);
        
        
        
        
        $sql = "CREATE TABLE `s_statistics_daily_project_earnings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `avg_rate` decimal(5,2) NOT NULL COMMENT '平均利率',
  `treaty_rate` decimal(5,2) NOT NULL COMMENT '协议利率',
  `amount` decimal(15,2) unsigned NOT NULL,
  `earnings` decimal(15,2) NOT NULL COMMENT '收益',
  `stat_date` date NOT NULL,
  `financing_id` smallint(1) unsigned NOT NULL COMMENT '融资方id',
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `add_date` (`stat_date`,`financing_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        
        $r = M()->execute($sql);
        print_r($r);
        
        
        
        
        
        /*
        
        $sql = "INSERT INTO `s_auth_rule` VALUES (null, 'admin', '1', 'Admin/project/project_protocol_rate_edit', '产品管理 - 预设协议利率 - 增加(编辑)', '1', '')";
        if(M()->execute($sql)){
            echo 'ok';
        }
        
        exit;
        */
        
        //票票喵客户更改手机号码
        $sql = "update s_user set username='15857169455',mobile='15857169455' where id=28988 and username=17051064577 limit 1";
        $sql = "update s_user_bank set mobile='15857169455' where user_id=28988 and id=4404 limit 1";
        
        
        exit('403');
        
        $sql1 = "update s_statistics_daily_wallet_earnings set  due_amount = due_amount/100 where id = 2 limit 1";
        if(M()->execute($sql1)){
            echo 'ok';
        }
        exit;
        /*
        $sql1 = "update s_investment_detail set  inv_total = inv_total+50,inv_succ=inv_succ+50 where id = 12816 limit 1";
        
        $sql2 = "update s_recharge_log set  amount = amount+50 where id = 19609 limit 1";
        
        $sql3 = "update s_user_due_detail set  due_amount = due_amount+50,due_capital = due_capital+50  where id = 12814 limit 1";
        
        $sql4 = "update s_project set able = able -50 where id= 241 limit 1";
        
        
        if(M()->execute($sql1) && M()->execute($sql2) && M()->execute($sql3) && M()->execute($sql4)) {
            echo 'ok';
        }
        
        改幽灵账号的数据
        
        */
        
        
        exit;
        $sql = "update s_project set able = able -11000 where id=241 limit 1";
        if(M()->execute($sql)) {
            echo 'ok';
        }
        
        
        
        $sql = "ALTER TABLE  `s_project` ADD  `project_group_id` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0',
ADD  `activate_project_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0'";
        if(M()->execute($sql)) {
            echo 'oowe';
        }
        
        
        
        JPushLog(date("Y-m-d H:i:s"));
        exit('403');
        
        
        
        
        //产品分组权限 
        
        $sql1 = "INSERT INTO `s_auth_rule` VALUES ('240', 'admin', '1', 'Admin/project/project_group', '产品管理 - 产品分组管理', '1', '')";
        $sql2 = "INSERT INTO `s_auth_rule` VALUES ('241', 'admin', '1', 'Admin/project/project_group_add', '产品管理 - 添加产品分组', '1', '')";
        $sql3 = "INSERT INTO `s_auth_rule` VALUES ('242', 'admin', '1', 'Admin/project/project_group_edit', '产品管理 - 编辑产品分组', '1', '')";
        $sql4 = "INSERT INTO `s_auth_rule` VALUES ('243', 'admin', '1', 'Admin/project/project_group_delete', '产品管理 - 删除产品分组', '1', '');";
        
        if(M()->execute($sql1)
            && M()->execute($sql2)
            && M()->execute($sql3)
            && M()->execute($sql4)
           ) {
            echo 'ok';
        } else {
            echo 'err';
        }
        exit('fff');
        
        $sql = "CREATE TABLE `s_project_group` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL DEFAULT '',
              `memo` varchar(120) DEFAULT NULL,
              `add_time` datetime NOT NULL,
              `modify_time` datetime DEFAULT NULL,
              `add_user_id` int(10) unsigned DEFAULT NULL,
              `modify_user_id` int(10) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        
        $r = M()->execute($sql);
        print_r($r);
        
        exit;
        
        
        /*
        $sql1 = "UPDATE s_investment_detail SET card_no = '6217003190002768331' WHERE user_id='39651' and card_no = '6217003110008590992'";
        
        $sql2 = "UPDATE s_user_bank SET bank_card_no = '6217003190002768331' where user_id = 39651 limit 1";
        
        $sql3 = "UPDATE s_recharge_log SET card_no='6217003190002768331' WHERE user_id=39651 and card_no='6217003110008590992'";
        
        $sql4 = "UPDATE  s_user_due_detail SET card_no = '6217003190002768331' WHERE card_no='6217003110008590992' and user_id=39651";
        */
        
        
        
        
        
        exit('403');
        
        $sql = " insert into s_ios_version (version_code,version_name,type,add_time) values 
									(60,'1.0.6',0,now()),
									(61,'1.1.0',0,now()),
									(62,'1.1.2',0,now()),
									(63,'1.1.3',0,now()),
									(64,'1.1.4',0,now());
    ";
        $r = M()->execute($sql);
        print_r($r);
        
        
        
        
        $sql = "ALTER TABLE  `s_user` ADD  `app_version` VARCHAR( 32 ) NULL COMMENT  'app 版本号'";
        $r = M()->execute($sql);
        print_r($r);
        exit('-403');
        
        
        $sql = "update s_bank_pay_way set per_transaction='5万',per_day='5万' where id=3 and bank_name='中国银行'";
        $r = M()->execute($sql);
        echo $r;
        
        
        
        $sql = "ALTER TABLE  `s_statistics_netprofit` ADD  `channel_id` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '渠道id'";
        $r = M()->execute($sql);
        print_r($r);
        exit;
        
        
        $sql = "update s_constant set id=24 where id=200 limit 1";
        $r = M()->execute($sql);
        if($r) {
            echo 'ok';
        } else {
            echo 'fail';
        }
        
        
        $sql = "update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=1;
                update s_bank_pay_way set per_transaction='2万', per_day='2万' where id=2;
                update s_bank_pay_way set per_transaction='1万', per_day='1万' where id=3;
                update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=4;
                update s_bank_pay_way set per_transaction='1万', per_day='2万' where id=5;
                update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=6;
                update s_bank_pay_way set per_transaction='5万', per_day='无上限' where id=7;
                update s_bank_pay_way set per_transaction='5万', per_day='500万' where id=8;
                update s_bank_pay_way set per_transaction='0万', per_day='0万' where id=9;
                update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=10;
                update s_bank_pay_way set per_transaction='5万', per_day='无上限' where id=11;
                update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=12;
                update s_bank_pay_way set per_transaction='10万', per_day='100万' where id=13;
                update s_bank_pay_way set per_transaction='10万', per_day='100万' where id=14;
                update s_bank_pay_way set per_transaction='5万', per_day='5万' where id=15;
                update s_bank_pay_way set per_transaction='5千', per_day='5万' where id=16;
                update s_bank_pay_way set per_transaction='1万', per_day='2万' where id=17;";
    
        $r = M()->execute($sql);
        if($r) {
            echo 'ok';
        } else {
            echo 'on';
        }
        
        exit('啊规');
        
        $sql = "ALTER TABLE  `s_user_due_detail` ADD  `interest_coupon` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0' COMMENT  '加息券金额'";
        $r = M()->execute($sql);
        print_r($r);
        echo $r;
        
        
        exit('fff');
        
        echo "start .....\n";
        
        $create_tab = "CREATE TABLE `s_user_interest_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) unsigned NOT NULL COMMENT '券id',
  `title` varchar(50) NOT NULL DEFAULT '',
  `subtitle` varchar(50) NOT NULL DEFAULT '',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `recharge_no` varchar(50) DEFAULT '' COMMENT '充值编码',
  `interest_rate` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `min_due` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最小投资期限',
  `min_invest` decimal(10,2) DEFAULT NULL COMMENT '最小投资金额',
  `project_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `expire_time` datetime DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0初始，1已使用，2过期',
  `scope` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0个人，1所有人',
  `add_time` datetime DEFAULT NULL,
  `add_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加用户UID',
  `modify_time` datetime DEFAULT NULL COMMENT '修改时间',
  `modify_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改用户UID',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_read` tinyint(1) DEFAULT '0' COMMENT '0:未读；1：已读',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`recharge_no`) USING BTREE,
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户加息券列表';";
        
        $r = M()->execute($create_tab);
        print_r($r);
        echo $r;
        exit;
        
        
        
        exit('123');
        /*
        $sql1 = "INSERT INTO `s_auth_rule` VALUES ('227', 'admin', '1', 'Admin/InterestCoupon/add', '券包管理  - 券包发放', '1', '')";
        $sql2 = "INSERT INTO `s_auth_rule` VALUES ('228', 'admin', '1', 'Admin/InterestCoupon/index', '券包管理  - 券包数据管理', '1', '')";
        $sql3 = "INSERT INTO `s_auth_rule` VALUES ('229', 'admin', '1', 'Admin/InterestCoupon/user_coupon_list', '券包管理  - 券包使用人数列表', '1', '')";
        $sql4 = "INSERT INTO `s_auth_rule` VALUES ('230', 'admin', '1', 'Admin/InterestCoupon/exportExcel', '券包管理  - 券包使用人数列表导出Excel', '1', '')";
        $sql5 = "INSERT INTO `s_auth_rule` VALUES ('231', 'admin', '1', 'Admin/InterestCoupon/history_index', '券包管理  - 券包发放历史记录', '1', '')";
        $sql6 = "INSERT INTO `s_auth_rule` VALUES ('232', 'admin', '1', 'Admin/InterestCoupon/delete', '券包管理  - 券包发放历史记录删除', '1', '')";
        $sql7 = "INSERT INTO `s_auth_rule` VALUES ('233', 'admin', '1', 'Admin/InterestCoupon/day_index', '券包管理  - 券包每日数据查询', '1', '')";
        $sql8 = "INSERT INTO `s_auth_rule` VALUES ('234', 'admin', '1', 'Admin/InterestCoupon/day_index_export_excel', '券包管理  - 券包每日数据查询导出Excel', '1', '')";
        
        $sql9 = "INSERT INTO `s_auth_rule` VALUES ('235', 'admin', '1', 'Admin/CashCoupon/add', '奖励管理  - 现金券发放', '1', '')";
        $sql10 = "INSERT INTO `s_auth_rule` VALUES ('236', 'admin', '1', 'Admin/CashCoupon/cash_index', '奖励管理  - 现金券发放记录', '1', '')";
        $sql11 = "INSERT INTO `s_auth_rule` VALUES ('237', 'admin', '1', 'Admin/CashCoupon/delete', '奖励管理  - 现金券删除', '1', '')";
        $sql12 = "INSERT INTO `s_auth_rule` VALUES ('238', 'admin', '1', 'Admin/CashCoupon/invite_index', '奖励管理  - 推荐发放记录', '1', '')";
        */
        $sql = "INSERT INTO `s_auth_rule` VALUES ('239', 'admin', '1', 'Admin/CashCoupon/invite_detail', '奖励管理  - 推荐发放记录 - 推荐明细', '1', '')";
        
        if(M()->execute($sql)) {
            echo 'ok';
        } else{
            echo 'on';
        }
    
        /*
        if(M()->execute($sql1)
            && M()->execute($sql2)
            && M()->execute($sql3)
            && M()->execute($sql4)
            && M()->execute($sql5)
            && M()->execute($sql6)
            && M()->execute($sql7)
            && M()->execute($sql8)
            && M()->execute($sql9)
            && M()->execute($sql10)
            && M()->execute($sql12)
            && M()->execute($sql12)
            && M()->execute($sql3)
            
            ) {
      
            echo '1 ok';
        } else {
            echo 'fail';
        }
        */
        exit;
        
        
        
        $sql = "CREATE TABLE `s_user_invite_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(24) unsigned DEFAULT '0' COMMENT '邀请人ID',
  `invited_user_id` bigint(24) DEFAULT '0' COMMENT '被邀请人ID',
  `invited_phone` varchar(11) DEFAULT '' COMMENT '邀请人手机号',
  `add_time` datetime DEFAULT NULL COMMENT '成功邀请时间',
  `modify_time` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '奖励金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invited_user_id` (`invited_user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='好友邀请信息表'";
        
        
        $r = M()->execute($sql);
        echo $r;
        exit;
        
        //改手机号码 2016-07-05
        $sql1 = "update s_user  set username='15515892592' where id=4210 limit 1";
        
        $sql2 = "update s_user_bank set mobile='15515892592' where id=909 and user_id=4210 limit 1";
        
        
        if(M()->execute($sql1) && M()->execute($sql2)) {
            echo '1 ok';
        }
        
        exit;
        
        $sql = "update s_user_bank set wallet_money=5.00 where id=19 and user_id=5";
        
        //$sql = "update s_constant set cons_desc='400-992-8855' where id=175 and cons_key='consumer_hotline'";
        if(M()->execute($sql)) {
            echo '1 ok';
        }
        
        
        
        
        $sql = "DELETE FROM s_constant WHERE id = 196 limit 1";
        $sql2 = "UPDATE s_constant SET parent_id = 4  WHERE id = 195 limit 1";
        
        if(M()->execute($sql)) {
            echo '1 ok';
        }
        
        if(M()->execute($sql2)) {
            echo '2 ok';
        }
    }
    
        
    public function test22() {
        
        /*
        $id = I('post.id', 0, 'int');
        $rid = I('post.rid', 0, 'int');
        $status = I('post.status', 0, 'int'); // (1:未还款/2:已还款/3:正在支付)
        */
        exit;
        $id = 55;
        $rid = 54;
        $status = 3;
        
        
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $investmentDetailObj = M('InvestmentDetail');
        
        // 检查还款中是否还有未处理的钱包订单
        if($userDueDetailObj->where("repay_id=".$rid." and status=1 and (to_wallet=1 or from_wallet=1)")->getField('id')){
        
            echo "还有未处理的转入钱包订单";
            exit;
        }
        
        $repaymentDetail = $repaymentDetailObj->where(array('id'=>$rid,'project_id'=>$id))->find();
        
        if($repaymentDetail['status'] == 2) {
            echo '该条目订单已支付完成';
            exit;
        }
        
        
        
        if($repaymentDetail['status'] == 1){
        
            if($status != 3) {
                echo '非法操作1';
                exit;
            }
        
        }else if($repaymentDetail['status'] == 3){
        
            if($status != 2) {
                echo '非法操作2';
                exit;
            }
        }
        
       
        
        $time = date('Y-m-d H:i:s').'.'.getMillisecond();
        
        if($status == 3){ // 执行支付操作(状态改成正在支付)
            $rows = array(
                'status' => 3,
                'status_new' => 3,
                'real_time' => $time,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
        }else if($status == 2){ // 执行支付完成操作(将状态改成支付完成)
            $rows = array(
                'status' => 2,
                'status_new' => 2,
                'real_time' => $time,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
        }else{
           
            echo '非法操作3';
        }
        /*
        if(!$repaymentDetailObj->where(array('id'=>$rid))->save($rows)) {
            
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#1'));
        }
        */
        // 检查是否有用户购买列表
        if($status == 3){ // 支付动作
            $userList = $userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status_new=1 and to_wallet=0 and from_wallet=0")->select();
        }else if($status == 2){ // 支付完成动作
            $userList = $userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status_new=3 and to_wallet=0 and from_wallet=0")->select();
        }else{
            $repaymentDetailObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        }
        $userIds = '';
        if(count($userList) > 0){
            if($status == 2){ // 执行支付完成动作,变动用户账户金额信息
                foreach($userList as $key => $val){
                    $userIds .= ','.$val['user_id'];
                    $sql = "update s_user_account set ";
                    $sql .= "total_invest_capital=total_invest_capital+" . $val['due_capital'];
                    $sql .= ",total_invest_interest=total_invest_interest+" . $val['due_interest'];
                    $sql .= ",wait_amount=wait_amount-" . $val['due_amount'];
                    $sql .= ",wait_capital=wait_capital-" . $val['due_capital'];
                    $sql .= ",wait_interest=wait_interest-" . $val['due_interest'];
                    $sql .= " where user_id=".$val['user_id'];
                    $userAccountObj->execute($sql);
                }
                if($userIds) $userIds = substr($userIds, 1);
            }
            if($status == 3){ // 支付动作
                $rowsSub = array(
                    'status_new' => 3,
                    'real_time' => $time,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
                $rowsSub2 = array(
                    'status_new' => 5,
                );
            }else if($status == 2){ // 支付完成动作
                $rowsSub = array(
                    'status' => 2,
                    'status_new' => 2,
                    'real_time' => $time,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
                $rowsSub2 = array(
                    'status' => 4,
                    'status_new' => 4,
                );
            }else{
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
            }
            $dueList = $userDueDetailObj->field('invest_detail_id')->where("repay_id=".$repaymentDetail['id']." and status=1 and to_wallet=0 and from_wallet=0")->select();
            if(!$userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status=1 and to_wallet=0 and from_wallet=0")->save($rowsSub)){
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#2 '.$repaymentDetail['id']));
            }
            $investIds = '';
            foreach($dueList as $key => $val){
                $investIds .= ','.$val['invest_detail_id'];
            }
            if($investIds) $investIds = substr($investIds, 1);
            if($investIds){
                if(!$investmentDetailObj->where("project_id=".$id." and id in (".$investIds.")")->save($rowsSub2)){
                    $repaymentDetailObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#3'));
                }
            }
        }
        if($status == 3){ // 支付动作
            $rowsProject = array(
                'status' => 4,
            );
        }else if($status == 2){ // 支付完成动作
            $rowsProject = array(
                'status' => 5,
                'repayment_time' => $time, // 设置已还款时间
            );
        }else{
            $repaymentDetailObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        }
        if(!$projectObj->where(array('id'=>$id))->save($rowsProject)){
            $repaymentDetailObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }
    }
    
    private function getBagAmount($userId,$orderId) {
        $amount = 0;
        if($userId && $orderId) {
            $amount = M('UserRedenvelope')->where(array('user_id'=>$userId,'recharge_no'=>$orderId,'status'=>1))->getField('amount');
        }
        return $amount;
    }
    
    
    /**
     * 解绑卡，解决之前的数据问题 
     */
    public function test5() {
        
        exit;
        $user_bank_list = M('UserBank')->field('id,user_id,bank_card_no,wait_money')->where('has_pay_success = 2 and wait_money >0')->select();
        
        echo 'total:'.count($user_bank_list)."<br/>";
        
        if($user_bank_list) {
            
            $n = 0;
            
            foreach ($user_bank_list as $val) {
                
                $money = M('UserDueDetail')->where(array('card_no'=>$val['bank_card_no'],'status'=>1,'from_wallet'=>0))->sum('due_amount');
                
                if(!$money) $money = 0;
                
                if($money != $val['wait_money'])  {
                    
                    echo 'id:'.$val['id']."&nbsp;&nbsp;&nbsp;&nbsp;";
                    
                    echo "card_no:".$val['bank_card_no'] ."&nbsp;&nbsp;&nbsp;&nbsp;";
                    
                    echo 'wait_money:'.$val['wait_money']."&nbsp;&nbsp;&nbsp;&nbsp;";
                    
                    echo 'money:'.$money."<br/>";
                    
                    $card_no = $val['bank_card_no'];
                    
//                     $sql  = "update s_user_bank set wait_money=".$money .' where id='.$val['id'] ." and bank_card_no='$card_no'";                    
                    
                    
//                   if(  M()->execute($sql)) {
//                       echo "<br/> ok";
//                   }
                    
                    echo "<br/>";
                    $n ++;
                    
                }
                
            }
            echo "<br/>";
            echo '共：'.$n;
        }
    }
    
    
    /**
     * 补单 很久的单
     */
    public function test55() {
        
        exit;
        
        $date = date("2016-05-19 10:19:01");
        
        $recharge_no = '201605190110000633260060';
        
        $userId = 4413;
        
        $project_id = 90;
        
        $rechargeObj = M('RechargeLog')->field('project_id,recharge_no,amount,device_type,card_no,type')
                        ->where(array('recharge_no' => $recharge_no/*,'status' => 2*/))->find();
        
        $order_amount = $rechargeObj['amount'];

        $total_amount = $order_amount;

        $projectObj = M('Project')->field('amount,able,type,start_time,end_time,user_interest')->where(array('id' => $project_id))->find();

        $account_detail['user_id'] = $userId;
        $account_detail['account_total'] = $total_amount;
        $account_detail['account_able'] = $total_amount;
        $account_detail['change_amount'] = $total_amount;
        $account_detail['type'] = 2;
        $account_detail['status'] = 1;
        $account_detail['remark'] = '用户充值，充值编号：' . $recharge_no;
        $account_detail['add_time'] = $date;
        $account_detail['add_user_id'] = $userId;
        $account_detail['modify_time'] = $date;
        $account_detail['modify_user_id'] = $userId;
        $adId = M('AccountDetail')->add($account_detail);

        $investment_detail['user_id'] = $userId;
        $investment_detail['project_id'] = $project_id;
        $investment_detail['inv_total'] = $total_amount;
        $investment_detail['inv_succ'] = $total_amount;
        $investment_detail['device_type'] = $rechargeObj['device_type'];
        $investment_detail['auto_inv'] = 1;
        $investment_detail['recharge_no'] = $recharge_no;
        $investment_detail['status'] = 2;
        $investment_detail['status_new'] = 2;
        $investment_detail['bow_type'] = $projectObj['type'];
        $investment_detail['card_no'] = $rechargeObj['card_no'];
        $investment_detail['add_time'] = $date;
        $investment_detail['add_user_id'] = $userId;
        $investment_detail['modify_time'] = $date;
        $investment_detail['modify_user_id'] = $userId;
        $idId = M('InvestmentDetail')->add($investment_detail);

        $user = M('User')->field('real_name_auth')->where(array('id' => $userId))->find();

        if ($user['real_name_auth'] == 0) {
            $sql_4 = M()->execute("UPDATE `s_user` SET `real_name_auth`=1,`mobile_auth`=1,`card_no_auth`=1 WHERE id=$userId");
        } 

        $user_bank_id = M('UserBank')->where(array('bank_card_no' => $rechargeObj['card_no'],'user_id' => $userId))->getField('id');

        $day = $this->count_days('2016-05-19', $projectObj['end_time']);

        $wait_money = $total_amount + ($total_amount * $projectObj['user_interest'] * $day / 100 / 365); // 本金+红包+利息

        $sql_5 = M()->execute("UPDATE `s_user_bank` SET pay_type = " . $rechargeObj['type'] . ", has_pay_success = 2, latest_payment_time = '$date', total_money = `total_money` + $order_amount, wait_money = `wait_money` + $wait_money, modify_time = '$date', modify_user_id = $userId where id = $user_bank_id and is_deleted = 0");

        $repayment_detail_Obj = M('RepaymentDetail')->field('id,repayment_time')->where(array('project_id' => $project_id))->find();

        $user_due_detail['user_id'] = $userId;
        $user_due_detail['project_id'] = $project_id;
        $user_due_detail['repay_id'] = $repayment_detail_Obj['id'];
        $user_due_detail['invest_detail_id'] = $idId;
        $user_due_detail['due_amount'] = $wait_money;
        $user_due_detail['due_capital'] = $total_amount;
        $user_due_detail['due_interest'] = ($total_amount * $projectObj['user_interest'] * $day / 100 / 365);
        $user_due_detail['period'] = 1;
        $user_due_detail['duration_day'] = $day;
        $user_due_detail['start_time'] = date("2016-05-19 10:19:01",strtotime("+1 day"));
        $user_due_detail['due_time'] = $repayment_detail_Obj['repayment_time'];
        $user_due_detail['status'] = 1;
        $user_due_detail['bow_type'] = $projectObj['type'];
        $user_due_detail['card_no'] = $rechargeObj['card_no'];
        $user_due_detail['repayment_no'] = 'RP' . date("YmdHis") . $userId . rand(10, 110);
        $user_due_detail['add_time'] = $date;
        $user_due_detail['add_user_id'] = $userId;
        $user_due_detail['modify_time'] = $date;
        $user_due_detail['modify_user_id'] = $userId;
    
        $uddId = M('UserDueDetail')->add($user_due_detail);
    
        
        echo 'ok';
        
    }
        
    function count_days($start, $end)
    {
        return floor(abs(strtotime($start) - strtotime($end)) / 86400);
    }
        
    
    public function test77() {
        exit;
        $sql = "UPDATE  `s_user_bank` SET `bank_name` ='中国银行',`bank_code` ='01040000' WHERE `id` =1274";
        if(M()->execute($sql)) {
            echo '0k';
        } else {
            echo 'NO';
        }
        
    }
    
    public function test66() {
        exit('jjjj');
        
        $sql= "UPDATE `s_user_bank` set `requestid` ='' WHERE `user_id` =9135";
        if(M()->execute($sql)) {
            echo '0k';
        } else{
            echo 'no';
        }
        
        
        
       /* $sql = "INSERT INTO `s_constant` (`parent_id`, `cons_key`, `cons_value`, `cons_desc`, `add_time`, `add_user_id`, `modify_time`, `modify_user_id`) VALUES

	(0, 'AppStore_pro', 'AppStore_pro', '', '2016-06-13 15:45:49.000000', 0, '2016-06-13 15:45:49.000000', 0),
	(0, 'AppStore_common', 'AppStore_common', '', '2016-06-13 15:45:49.000000', 0, '2016-06-13 15:45:49.000000', 0);
        ";
        
        
        if(M()->execute($sql)) {
            echo '0k';
        } else{
            echo 'no';
        }
        */
        
        
//         $sql = "update s_user_bank set requestid='' where id=3049 and user_id=19214";
//         if(M()->execute($sql)) {
//             echo '0k';
//         } else{
//             echo 'no';
//         }
        
        
        //$sql1 = "INSERT INTO `s_auth_rule` VALUES ('222', 'admin', '1', 'Admin/statistics/statistics_payment_channel', '统计分析  - 支付渠道统计', '1', '')";
        //$sql2 = "INSERT INTO `s_auth_rule` VALUES ('223', 'admin', '1', 'Admin/statistics/statistics_payment_channel_detail', '统计分析  - 支付渠道统计 - 明细', '1', '')";
        //$sql3 = "INSERT INTO `s_auth_rule` VALUES ('224', 'admin', '1', 'Admin/statistics/statistics_payment_channel_export', '统计分析  - 支付渠道统计 - 导出excel', '1', '')";
        
        
       
        
        

        if(M()->execute($sql1)) {
            echo '0k';
        } else {
            echo 'NO';
        }
    }
        
    
    public function got() {
        exit();
        $sql = "select sum(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                            where p.id = s.project_id and p.new_preferential != 1 and s.user_id>0 and s.user_id = u.id
                            and p.start_time >= '2016-05-02 20:00:00'
                            and s.add_time <= '2016-10-22 23:59:59'
                            and p.is_delete = 0 GROUP BY user_id ORDER BY sum(s.due_capital) DESC";
        $list = M()->query($sql);
    
        $ret = array();
        $n = 1;
        foreach ($list as $key => $val) {
            echo $val['user_id'].'---'.$val['amount'].'-------'.$val['real_name']."<br/>";
            
            $dd['rank'] = $n++;
            $dd['real_name'] = $this->substr_cut($val['real_name']); 
            $dd['username'] = substr_replace($val['username'],'****',3,4);
            $dd['amount'] = $val['amount'];
            
            $ret[$val['user_id']] = $dd;
        }
         
        print_r($ret);
        
        foreach ($ret as $val) {
            echo $val['user_id'].'---'.$val['amount'].'-------'.$val['real_name']."<br/>";
        }
    }
    
    private function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
    
    }
    
    /**
     * 导出Excel(宝付支付)
     */
    public function exporttoexcel(){
        //exit();
        vendor('PHPExcel.PHPExcel');
        $id = 469;//;//I('get.id', 0, 'int'); // 项目ID
        $repay_id = 458;//I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = 1;// I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)
    
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');
    
        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        if($act == 2){
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }else{
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }
        $totle_capital = 0;
        $totle_interest = 0;
        $totle_count = count($list); // 总笔数
    
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
        ->setCreator("票票喵票据")
        ->setLastModifiedBy("票票喵票据")
        ->setTitle("title")
        ->setSubject("subject")
        ->setDescription("description")
        ->setKeywords("keywords")
        ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
        ->setCellValue("A1", "*收款方姓名")
        ->setCellValue("B1", "*收款方银行账号")
        ->setCellValue("C1", "*开户行所在省")
        ->setCellValue("D1", "*开户行所在市")
        ->setCellValue("E1", "*开户行名称")
        ->setCellValue("F1", "*收款方银行名称")
        ->setCellValue("G1", "*金额")
        ->setCellValue("H1", "商户订单号")
        ->setCellValue("I1", "商户备注");
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];
    
            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();
    
            if($bankInfo['bank_name'] == '邮政储蓄') {
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            }
    
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['card_no']); // 收款银行账号
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,''); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,''); // 开户行所在市
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$bankInfo['bank_name']); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,($val['due_capital']+$val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,str_replace('RP', '', $val['repayment_no'])); // 商户订单号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,''); // 商户备注
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户付息表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    public function test_push(){
        pushMsg('1', $reg_id='1', $_platform='1', array(),$app=2);
    }
    
    
    
    public function export_contract(){
        exit();
        vendor('PHPExcel.PHPExcel');

        $list = M('Contract')->order('id asc')->select();
        
        
    
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                    ->setCreator("票票喵票据")
                    ->setLastModifiedBy("票票喵票据")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('合同列表')
                    ->setCellValue("A1", "合同编号")
                    ->setCellValue("B1", "票号")
                    ->setCellValue("C1", "票面金额(元)")
                    ->setCellValue("D1", "出票日期")
                    ->setCellValue("E1", "到期日期");
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['name']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['ticket_number']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['price']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,date('Y-m-d',$val['start_time'])); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,date('Y-m-d',$val['end_time'])); 
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="合同列表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    
    public function export_act_rank(){
        exit();
        $sql = "select SUM(s.due_capital) as amount ,s.user_id,u.username,u.real_name from s_project p ,s_user_due_detail s,s_user u
                where p.id = s.project_id and s.duration_day >= 30 and s.user_id>0 and s.user_id = u.id
                AND s.add_time >= '2016-12-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY SUM(s.due_capital) DESC LIMIT 100";
        
        $list = M()->query($sql);
        
        $res = array();
        
        foreach ($list as $key => $val) {
            $dd['real_name'] = $val['real_name'];
            $dd['user_name'] = $val['username'];
            $dd['amount'] = $val['amount'];
            $dd['type'] = 0;
            $dd['user_id'] = $val['user_id'];
            $res[$val['user_id']] = $dd;
        }
        unset($list);
        
        //系统假数据
        $sql2 = "SELECT SUM(s.due_capital) AS amount,s.user_id FROM s_project p ,s_user_due_detail s
                WHERE p.id = s.project_id AND s.duration_day >= 30
                AND s.user_id >=-120
                AND s.user_id <=-101
                AND s.add_time >= '2016-12-01'
                AND s.add_time <= '2016-12-31 23:59:59'
                AND p.is_delete = 0 GROUP BY s.user_id ORDER BY sum(s.due_capital) DESC";
        
        $list = M()->query($sql2);
        
        foreach ($list as $val) {
            $info = $this->ghostaccount($val['user_id']);
            if($info) {
                $dd['real_name'] = $info[1];
                $dd['user_name'] = $info[0];
                $dd['amount'] = $val['amount'];
                $dd['user_id'] = $val['user_id'];
                $dd['type'] = '1';
                $res[$val['user_id']] = $dd;
            }
        }
        
        unset($list);
        
        $sort_arr = array();
        foreach ($res as $val) {
            $sort_arr[] = $val['amount'];
        }
        
        array_multisort($sort_arr, SORT_DESC, $res);
        
        vendor('PHPExcel.PHPExcel');
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("年未狂欢活动冲值排行")
            ->setLastModifiedBy("年未狂欢活动冲值排行")
            ->setTitle("title")
            ->setSubject("subject")
            ->setDescription("description")
            ->setKeywords("keywords")
            ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('冲值排行')
            ->setCellValue("A1", "名次")
            ->setCellValue("B1", "金额")
            ->setCellValue("C1", "手机号码")
            ->setCellValue("D1", "真实姓名")
            ->setCellValue("E1", "用户类型 0真1假");
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        
        
        $n = 1;
        $pos = 2;
        
        foreach ($res as $k => $val){
            if($n>100) {
                break;
            }
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n++);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['amount']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['user_name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['type']);
            $pos += 1;
        }
        
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="年未狂欢活动冲值排行.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    private function ghostaccount($k){
        $arr = array(
            '-101'=>array('13600009252','鲍琛'),
            '-102'=>array('13300006969','程旭文'),
            '-103'=>array('13300001518','丁叶敏'),
            '-104'=>array('17800001844','方静'),
            '-105'=>array('15200005715','丰收'),
            '-106'=>array('13000006566','何乐'),
            '-107'=>array('13400003922','黄玉佩'),
            '-108'=>array('18600008078','江小宇'),
            '-109'=>array('18800000708','李嘉'),
            '-110'=>array('15800009477','潘婕'),
            '-111'=>array('18700000140','钱东'),
            '-112'=>array('13500006064','李天涯'),
            '-113'=>array('18200004205','王勇'),
            '-114'=>array('15100009049','朱健康'),
            '-115'=>array('15300002022','郑浩'),
            '-116'=>array('17800001844','廖建东'),
            '-117'=>array('15900003419','陆剑波'),
            '-118'=>array('15200004790','方辉'),
            '-119'=>array('18800003520','方勇'),
            '-120'=>array('18200009291','吴建杰'),
    
        );
        return $arr[$k];
    }
    
    public function update_name() {
        exit();
        ini_set("memory_limit", "10000M");
        ini_set("max_execution_time", 0);
        
        $list = M('investmentDetail')->distinct(true)->field('ghost_phone')->where("project_id >= 700 and project_id < 800 and user_id>=-120 and user_id<=0 and ghost_phone!=''")->select();
        
        foreach ($list as $val){
            
            if(!M('atmpName')->where(array('mobile'=>$val['ghost_phone']))->count()){
                $dd['mobile'] = $val['ghost_phone'];
                $dd['real_name'] = $this->get_real_name();
                M('atmpName')->add($dd);
            }
        }
        
        echo 'ok1333teeett';
    }
    
    
   private function get_real_name(){
       $sql = "SELECT real_name FROM `s_user` WHERE `real_name_auth` = 1 and  id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `s_user`)))ORDER BY id LIMIT 1";
       $res = M()->query($sql);
       return $res[0]['real_name'];
   }
   
   public function act_list(){
       exit();
       header("Content-type: text/html; charset=utf-8");
       
       $sql = "SELECT log.user_id,uu.username as username, uu.real_name as real_name,log.lottery_award_id as lai,aw.`name` as a_name,FROM_UNIXTIME(log.create_time) as ctime from s_lottery_log as log ,s_user uu,s_lottery_award as aw  where uu.id = log.user_id  and log.lottery_award_id = aw.id and  log.lottery_id = 3 and log.lottery_award_id > 29 and log.user_id >0 order by log.user_id;";
        
       $list = M()->query($sql);
       
       echo count($list)."<br/>";
       
       echo "<table>";
       
       echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='10%'>手机号码</td>
                <td width='10%'>用户名</td>
                <td width='10%'>奖品Id</td>
                <td width='10%'>奖品名称</td>
                <td width='10%'>中奖时间</td>                
               </tr>";
       
       $n = 1;
       $total = 0;
       foreach ($list as $val) {
           /*
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$val['username']."</td>";
            echo "<td width='10%'>".$val['real_name']."</td>";
            echo "<td width='10%'>".$val['lai']."</td>";
            echo "<td width='10%'>".$val['a_name']."</td>";
            echo "<td width='10%'>".$val['ctime']."</td>";
            echo "<tr/>";
            $n++;
            */
            $user_id = $val['user_id'];
            if($user_id != 51817 && $user_id !=49838) {
                $total ++;
                $sqlyy = "INSERT INTO `s_user_cash_coupon` (`title`,`amount`,`subtitle`,`add_time`,`expire_time`,`add_user_id`,`modify_time`,`create_time`,`status`,`type`,`user_id`) VALUES ('新春集字奖励','30','感谢支持','2017-01-13 00:00:00','2017-04-13 00:00:00','1','2017-01-13 18:50:37','1484304637','0','1',$user_id);";
                echo $sqlyy."<br/>";
            }
           
       }
       echo 'total:'.$total;
       echo '</table>';
   }
   
   public function event20170111(){
       
       header("Content-type: text/html; charset=utf-8");
       
       $total_amount = 0;
       
       $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.add_time >= '2017-01-13 10:00:00'
                and s.add_time <= '2017-01-23 18:03:00'
                and p.is_delete = 0";
       
       $ret = M()->query($sql);
        
       if($ret) {
           if($ret[0]['amount']>0){
               $total_amount = $ret[0]['amount'];
           }
       }
       
       
       $sql = "select sum(s.due_capital) as amount from s_project p ,s_user_due_detail s
                where p.id = s.project_id and p.new_preferential = 6
                and s.add_time >= '2017-01-13 10:00:00'
                and s.add_time <= '2017-01-23 18:02:00'
                and p.is_delete = 0 and s.user_id<=0";
        
       $ret = M()->query($sql);
       
       $yl_total_amount = 0;
       if($ret) {
           if($ret[0]['amount']>0){
               $yl_total_amount = $ret[0]['amount'];
           }
       }
        
       echo '全部:'.$total_amount;
       echo "<br/>";
       echo '其中幽灵:'.$yl_total_amount;
       echo "<table>";
        
       echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='10%'>手机号码</td>
                <td width='10%'>用户名</td>
                <td width='10%'>投资金额</td>
                <td width='10%'>分红金额</td>
                <td width='10%'>分红金额2</td>
               </tr>";
        
       
       $sql = "select sum(s.due_capital) as amount,s.`user_id`,un.username,un.real_name  from s_project p ,s_user_due_detail s,`s_user` un 
                        where p.id = s.project_id and p.new_preferential = 6 and un.id= s.user_id 
                        and s.add_time >= '2017-01-13 10:00:00'
                        and s.add_time <= '2017-01-23 18:00:00'
                        and p.is_delete = 0 
                        GROUP BY s.`user_id` ORDER BY s.user_id;";
       $list = M()->query($sql);
       $n = 1;
       $send_amount = 0;
       foreach ($list as $val) {
            $income_amount = $val['amount'] / $total_amount * 100000;
            
            $income_amount2 = floor($income_amount)+1;
            
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$val['username']."</td>";
            echo "<td width='10%'>".$val['real_name']."</td>";
            echo "<td width='10%'>".$val['amount']."</td>";
            echo "<td width='10%'>".$income_amount."</td>";
            echo "<td width='10%'>".$income_amount2."</td>";
            echo "<tr/>";
            
            $send_amount +=$income_amount2;
            $n++;
            
            //$user_id = $val['user_id'];                   
            //$sqlyy = "INSERT INTO `s_user_cash_coupon` (`title`,`amount`,`subtitle`,`add_time`,`expire_time`,`add_user_id`,`modify_time`,`create_time`,`status`,`type`,`user_id`) VALUES ('瓜分奖励现金券',$income_amount2,'新春狂欢','2017-01-24 10:00:00','2017-03-25 10:00:00','1','2017-01-24 10:00:00','1485221378','0','1',$user_id);";
            //echo $sqlyy."<br/>";
       }
       
       echo "</table>";
       echo '分：'.$send_amount;
   }
   
   public function update_projectid857() {
        $sql = "SELECT * FROM s_user_due_detail WHERE project_id=857 AND DATE_FORMAT(start_time,'%y-%m-%d')= DATE_FORMAT(add_time,'%y-%m-%d')";
        $list = M()->query($sql);
        foreach ($list as $val) {                        
            $start_time = date('Y-m-d H:i:s',strtotime($val['start_time']) - 86400);
            //echo 'old time:'.$val['start_time'] .' time:'. $start_time ."<br/>";
            
        }       
   }
   
   
   public function update_projectid866() {
       exit('403');
        header("Content-type: text/html; charset=utf-8");
        $list = M('userDueDetail')->where('project_id=866 and user_id>0')->select();
        
        echo "<table>";
        
        echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>            
                <td width='10%'>投资金额</td>
                <td width='10%'>2月5号金额 + 利息</td>                
                <td width='6%'>2月5号天数</td>
                <td width='10%'>2月5号利息</td> 
                       
                <td width='10%'>2月8号利息</td>
                <td width='6%'>2月8号天数</td>
                <td width='10%'>2月8号金额 + 利息</td>   

            
                <td width='8%'>利息差额</td>  
            
            
               </tr>";
        
        $n = 1;
        
        foreach ($list as $val) {      
            
            $new_due_interest = round(($val['due_interest'] / $val['duration_day'] ) * ($val['duration_day'] + 3),2);
            
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$val['due_capital']."</td>";
            echo "<td width='10%'>".$val['due_amount']."</td>";     
            echo "<td width='6%'>".$val['duration_day']."</td>";
            echo "<td width='10%'>".$val['due_interest']."</td>";        
            echo "<td width='10%'>".$new_due_interest."</td>";     
            echo "<td width='6%'>".($val['duration_day'] + 3)."</td>";
            echo "<td width='10%'>".($val['due_capital'] + $new_due_interest)."</td>";
            
            
            echo "<td width='8%'>".($new_due_interest - $val['due_interest'])."</td>";
            echo "<tr/>";
            /*
            $due_amount = $val['due_capital'] + $new_due_interest;
            
            $sql = "update s_user_due_detail set duration_day = duration_day + 3,due_interest = $new_due_interest,due_amount = $due_amount where id =".$val['id'];
                
            echo $sql."<br/>";
            
            */
            
            $n++;
        }
        
        echo "</table>";
    }
   
    
    public function update_projectid866_1() {
         exit('403');
        header("Content-type: text/html; charset=utf-8");
        $list = M('userDueDetail')->where('project_id=866 and user_id>0')->select();
    
        echo "<table>";
    
        echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='10%'>投资金额</td>
                <td width='10%'>2月5号金额 + 利息</td>
                <td width='6%'>2月5号天数</td>
                <td width='10%'>2月5号利息</td>
            
                <td width='10%'>2月8号利息</td>
                <td width='6%'>2月8号天数</td>
                <td width='10%'>2月8号金额 + 利息</td>
    
    
                <td width='8%'>利息差额</td>
    
    
               </tr>";
    
        $n = 1;
    
        foreach ($list as $val) {
    
            $new_due_interest = round(($val['due_interest'] / $val['duration_day'] ) * ($val['duration_day'] + 3),2);
    
            echo "<tr>";
            echo "<td width='5%'>".$n."</td>";
            echo "<td width='8%'>".$val['user_id']."</td>";
            echo "<td width='10%'>".$val['due_capital']."</td>";
            echo "<td width='10%'>".$val['due_amount']."</td>";
            echo "<td width='6%'>".$val['duration_day']."</td>";
            echo "<td width='10%'>".$val['due_interest']."</td>";
            echo "<td width='10%'>".$new_due_interest."</td>";
            echo "<td width='6%'>".($val['duration_day'] + 3)."</td>";
            echo "<td width='10%'>".($val['due_capital'] + $new_due_interest)."</td>";
    
    
            echo "<td width='8%'>".($new_due_interest - $val['due_interest'])."</td>";
            echo "<tr/>";
            
             $due_amount = $val['due_capital'] + $new_due_interest;
    
             $sql = "update s_user_due_detail set duration_day = duration_day + 3,due_interest = $new_due_interest,due_amount = $due_amount,due_time='2017-02-08 22:00:00' where id =".$val['id'].';';
    
             echo $sql."<br/>";
    
             $value = $new_due_interest - $val['due_interest'];
             
             if($val['from_wallet'] == 0) {
                 $sql_bank = "update s_user_bank set wait_money = wait_money + $value where user_id = ".$val['user_id']." and bank_card_no = ".$val['card_no'] ." limit 1;";
                 echo $sql_bank."<br/>"; 
             }
             
             $sql_account = "update s_user_account set wait_amount = wait_amount + $value,wait_interest = wait_interest + $value where user_id = ".$val['user_id']." limit 1;";
              
             echo $sql_account."<br/>";
    
            $n++;
        }
    
        echo "</table>";
    }
    /**
    * 元宵节统计
    * @date: 2017-2-13 下午7:45:46
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function static_yxj(){
        
        header("Content-type: text/html; charset=utf-8");
        
        $list = M('lotteryLog')->group('user_id')->field('user_id')->where('lottery_id = 5')->select();
        
        
        echo "<table>";
        
        echo "<tr>
                <td width='5%'>N</td>
                <td width='8%'>userId</td>
                <td width='8%'>username</td>
                <td width='8%'>姓名</td>
                <td width='10%'>次数</td>
               </tr>";
        
        $n = 0;
        if($list) {
                    
            foreach ($list as $val) {
                
                $user_id = $val['user_id'];
                
                $user_info = M('user')->field('username,real_name,real_name_auth')->where('id='.$user_id)->find();
                
                $count = 0;
                //if($user_info['real_name_auth'] == 1) {
                
                    $sql = "SELECT `user_id` ,`due_capital` ,`project_id`  FROM s_user_due_detail AS d,s_project AS p
                              WHERE p.is_delete =0 AND d.project_id = p.id AND
                              p.new_preferential=6 AND d.due_capital>=5000 AND
                              d.due_capital<=100000 AND d.user_id=$user_id AND d.add_time>='2017-02-08 08:00:00' AND d.add_time<='2017-02-13 20:00:00'";
                    
                    $data = M()->query($sql);
                    
                    foreach ($data as $v) {
                        $count += floor($v['due_capital'] / 5000) + 1;
                    }
                //}
                
                if($count >=5) {
                
                    echo "<tr>";
                    echo "<td width='5%'>".$n."</td>";
                    echo "<td width='8%'>".$user_id."</td>";
                    echo "<td width='10%'>".$user_info['username']."</td>";
                    echo "<td width='10%'>".$user_info['real_name']."</td>";
                    echo "<td width='8%'>".$count."</td>";
                    echo "<tr/>";
                    
                    $n++;
                }
                
            }
            
        }
        echo "</table>";
        
    }
   
    public function create_yxj_sql(){
        
        header("Content-type: text/html; charset=utf-8");
    
        $list = M('lotteryLog')->group('user_id')->field('user_id')->where('lottery_id = 5')->select();
        
        $total = 0;
        
        if($list) {
    
            foreach ($list as $val) {
    
                $user_id = $val['user_id'];
    
                $user_info = M('user')->field('username,real_name,real_name_auth')->where('id='.$user_id)->find();
    
                $count = 0;
                if($user_info['real_name_auth'] == 1) {
    
                    $sql = "SELECT `user_id` ,`due_capital` ,`project_id`  FROM s_user_due_detail AS d,s_project AS p
                    WHERE p.is_delete =0 AND d.project_id = p.id AND
                    p.new_preferential=6 AND d.due_capital>=5000 AND
                    d.due_capital<=100000 AND d.user_id=$user_id AND d.add_time>='2017-02-08 08:00:00' AND d.add_time<='2017-02-13 20:00:00'";
    
                    $data = M()->query($sql);
    
                    foreach ($data as $v) {
                        $count += floor($v['due_capital'] / 5000) + 1;
                    }
                }
                
                if($count >=5) {
                    
                    if($count >=5 && $count<10) {
                        $money = 40;
                    } else if($count >=10 && $count<20 ) {
                        $money = 100;
                    } else if($count >=20 && $count<30) {
                        $money = 300;
                    } else if($count >=30 && $count<40) {
                        $money = 500;
                    } else if($count >=40 && $count<50) {
                        $money = 800;
                    } else if($count >=50 && $count<60) {
                        $money = 1000;
                    } else if($count >=60 && $count<70) {
                        $money = 1300;
                    } else if($count >=70 && $count<80) {
                        $money = 1600;
                    } else if($count >=80 ) {
                        $money = 2000;
                    }
                    
                    $total +=$money;
                    
                    $sql = "INSERT INTO `s_user_cash_coupon` (`title`,`amount`,`subtitle`,`add_time`,`expire_time`,`add_user_id`,`modify_time`,`create_time`,`status`,`type`,`user_id`) VALUES ('元宵现金券',$money,'累计投资奖励','2017-02-14 11:58:51','2017-05-15 11:58:51','1','2017-02-14 11:58:51','1487044731','0','1',$user_id);";
                    echo $sql."<br/>";
                }
            }
        }
        echo 'total:'.$total;
    }
    
    
    public function cl(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
    
        $date = date("Y-m-d");
    
        $project_list = M('Project')->field('id')->where('status in(2,3,4) and is_delete=0')->select();
    
        $default_channel_money = 0;
        
        $start_time = '2017-01-01';
        $end_time = '2017-02-01';
    
        foreach ($project_list as $val) {
    
            
            $project_id = $val['id'];
            
            $RechargeLogList = M('RechargeLog')->field('amount,user_id')
                ->where("status=2 and user_id >0 and add_time >='$start_time' and add_time<'$end_time'")->select();
    
            foreach ($RechargeLogList as $v) {
                
                    $default_channel_money +=$v['amount'];
            }
        }
    
        echo 'default_channel_money:'.$default_channel_money;
    }
    
    
    public function newamount(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
    
                
        $list = M('User')->field('id')->where("add_time>='2017-01-01' and add_time<'2017-02-01' and real_name_auth = 1")->select();
        
        $total = 0;
        
        foreach ($list as $val) {
            $uid = $val['id'];
            $total += M('rechargeLog')->where("status=2 and user_id =$uid ")->sum('amount');
        }
        echo 'total:'.$total;
    }
    
    public function tt(){
        
        $val['user_id'] = 12;
        
        $levelObj = M('userVipLevel')->where('uid ='.$val['user_id'])->find();
        
        $rows['vip_level'] = -1;
        
        if($levelObj) {
            $rows['vip_level'] = $levelObj['vip_level'];
        }
        
        print_r($rows);
    }
    
    
    /**
     * @author hui.xu
     * @date 2016\04\14
     * 导出Excel(融宝支付)
     */
    public function exportToExcelRb(){
    
        vendor('PHPExcel.PHPExcel');
        /*
        $id = I('get.id', 0, 'int'); // 项目ID
    
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID
    
        $act = I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)
    */
        
        $id = 469;//;//I('get.id', 0, 'int'); // 项目ID
        $repay_id = 458;//I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = 1;// I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)
        
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');
    
        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        if($act == 2){
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }else{
            //不是钱包购买的，也没有转入钱包
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }
    
    
        $objPHPExcel = new \PHPExcel();
    
        $objPHPExcel->getProperties()
        ->setCreator("票票喵票据")
        ->setLastModifiedBy("票票喵票据")
        ->setTitle("title")
        ->setSubject("subject")
        ->setDescription("description")
        ->setKeywords("keywords")
        ->setCategory("Category");
    
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('融宝..')->setCellValue("A1", "序号")
        ->setCellValue("B1", "银行账号")
        ->setCellValue("C1", "开户名")
        ->setCellValue("D1", "开户行")
        ->setCellValue("E1", "分行")
        ->setCellValue("F1", "支行")
        ->setCellValue("G1", "账户类型")
        ->setCellValue("H1", "金额")
        ->setCellValue("I1", "币种")
        ->setCellValue("J1", "省")
        ->setCellValue("K1", "市")
        ->setCellValue("L1", "手机号码")
        ->setCellValue("M1", "证件类型")
        ->setCellValue("N1", "证件号")
        ->setCellValue("O1", "用户协议号")
        ->setCellValue("P1", "商户订单号")
        ->setCellValue("Q1", "备注");
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
    
        // 设置列表值
        $pos = 2;
    
        $n = 1;
    
        foreach ($list as $key => $val) {
    
    
            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();
    
            if($bankInfo['bank_name'] == '邮政储蓄'){
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            } else if($bankInfo['bank_name'] == '建设银行'){
                $bankInfo['bank_name'] = '中国建设银行';
            } else if($bankInfo['bank_name'] == '农业银行'){
                $bankInfo['bank_name'] = '中国农业银行';
            } else if($bankInfo['bank_name'] == '民生银行'){
                $bankInfo['bank_name'] = '中国民生银行';
            } else if($bankInfo['bank_name'] == '广发银行'){
                $bankInfo['bank_name'] = '广东发展银行';
            } else if($bankInfo['bank_name'] == '光大银行'){
                $bankInfo['bank_name'] = '中国光大银行';
            } else if($bankInfo['bank_name'] == '浦发银行'){
                $bankInfo['bank_name'] = '上海浦东发展银行';
            } else if($bankInfo['bank_name'] == '工商银行'){
                $bankInfo['bank_name'] = '中国工商银行';
            }
    
    
            $userInfo = M('User')->field("username,card_no")->where(array("id"=>$val['user_id']))->find();
    
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['card_no']); // 收款银行账号
    
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $bankInfo['acct_name']); // 收款方开户姓名
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$bankInfo['bank_name']); // 收款方银行名称
    
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos,'分行');
    
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,'支行');
    
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,'私');
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,($val['due_capital']+$val['due_interest']), \PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
    
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, 'CNY');
    
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, '省');
    
            $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, '市');
    
            $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, '13888001111',\PHPExcel_Cell_DataType::TYPE_STRING);//$userInfo['username']  //手机号码
    
            $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, '身份证');//身分证   证件类型
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("N".$pos, '430101199001010011',\PHPExcel_Cell_DataType::TYPE_STRING);// $userInfo['card_no'],\PHPExcel_Cell_DataType::TYPE_STRING; 身分证号
    
            $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, ''); //$val['repayment_no']
    
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("P".$pos, str_replace('TX', '', $val['repayment_no']));//商户订单号
    
            $objPHPExcel->getActiveSheet()->setCellValue("Q".$pos, '还款至银行卡');//备注
    
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户付息表 - 融宝('.date("Y-m-d H:i:s").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
}
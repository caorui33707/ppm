<?php
/**
 * 定时处理任务-统计净额
 */
namespace Task\Controller;

use Think\Controller;

class ProfitController extends Controller {
    
    public function run_profit(){
        
        $date = date("Y-m-d");
        //平台总存量
        $totalMoney = M('UserDueDetail')->where("user_id>0 and status=1 and add_time<'$date'")->sum('due_capital');
        !$totalMoney && $totalMoney = 0;
        $row['money'] = $totalMoney;
        $row['add_time'] = time();
        $row['stat_date'] =  date("Y-m-d",strtotime("-1 day"));
        $row['channel_id'] = 0;
        M('StatisticsNetprofit')->add($row);
        
        //各渠道存量
        
        //渠道存量统计
        $channelPid = M('Constant')->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
        $channelList = M('Constant')->field('id,cons_key')->where(array('parent_id'=>$channelPid))->select();
        
        $r['add_time'] = $row['add_time'];
        $r['stat_date'] = $row['stat_date'];
        
        foreach ($channelList as $val) {
            $r['channel_id'] = $val['id'];
            $r['money'] = 0;

            $userSql = "SELECT id as user_id from s_user WHERE real_name_auth = 1 and channel_id = ".$val['id'];
            $userChannelSql = "SELECT user_id FROM s_user_channel WHERE channel LIKE '".$val['cons_key']."%'";

            $user_ret = M()->query($userSql);
            $user_channe_ret = M()->query($userChannelSql);

            $user_ret_arr = $user_channe_arr = array();

            foreach ($user_ret as $user){
                $user_ret_arr[] = $user['user_id'];
            }

            foreach ($user_channe_ret as $user_channe){
                $user_channe_arr[] = $user_channe['user_id'];
            }

            $userIdArr = array_unique(array_merge($user_ret_arr,$user_channe_arr));

            if($userIdArr){
                $userIdStr =  implode(',',$userIdArr);

                $sql = "SELECT sum(due_capital) as amt from s_user_due_detail WHERE user_id in( ".$userIdStr.") and `status` = 1 and add_time<'$date'";

                $sql_ret = M()->query($sql);

                if($sql_ret) {
                    $r['money'] =  $sql_ret[0]['amt'];
                }
            }


            M('StatisticsNetprofit')->add($r);
        }
        
    }
    /*
    public function run_profit() {
        
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        
        $date = date("Y-m-d");        
        
        $project_list = M('Project')->field('id')->where('status in(2,3,4) and is_delete=0')->select();
        
        //平台总存量统计
        $totalMoney = 0;
        foreach ($project_list as $val) {
            $totalMoney += M('RechargeLog')->field('amount')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and modify_time<'."'$date'")->sum('amount');
        }
       
        
        $totalMoney = M('UserDueDetail')->where("user_id>0 and status=1 and add_time<'$date'")->sum('due_capital');
        
        !$totalMoney && $totalMoney = 0;
        
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
        
        foreach ($project_list as $val) {
        
            $RechargeLogList = M('RechargeLog')->field('amount,user_id')->where("project_id = ".$val['id'] .' and status=2 and user_id >0 and modify_time<'."'$date'")->select();
        
            foreach ($RechargeLogList as $v) {
        
                $channelId = M('User')->where(array('id'=>$v['user_id']))->getField('channel_id');
        
                if($channelId == 0) { //内部测试用户
                    continue;
                }
                
                $res[$channelId]['money'] += $v['amount'];
            }
        }
        
        $dd['add_time'] = $row['add_time'];
        $dd['stat_date'] = $row['stat_date'];
        
        foreach ($res as $val){
            $dd['money'] = $val['money'];
            $dd['channel_id'] = $val['channel_id'];
        
            if($dd['channel_id']) {
                M('StatisticsNetprofit')->add($dd);
            }
        }
    }
    
    */
    
    //每日平台收益还本付息
    public function day_repay(){
        
        ini_set("max_execution_time", 0);
        $nowTime = date('Y-m-d');
        $statisticsTime = date('Y-m-d',strtotime("-1 day"));
        $projectObj = M('Project');
        //$rechargeLogObj = M('RechargeLog');
        $userDueDetailObj = M('userDueDetail');
        
        $financingList = M('Financing')->field('id,name')->select();
        
        if($financingList){

            foreach ($financingList as $key => $val){
                
                $financingList[$key]['total_amt'] = 0;
                $financingList[$key]['total_interest'] = 0;
                
                
                $projectList = $projectObj->field('id,fid,user_interest')->where('status <5 and is_delete=0 and fid='.$val['id'])->select();
                
                foreach ($projectList as $k=>$v){
                    
                    //$_amt = $rechargeLogObj->where("project_id = " . $v['id'] . " and status=2 and user_id >0 and modify_time < '$nowTime' ")->sum('amount');
                    
                    $_amt = $userDueDetailObj->where("project_id = " . $v['id'] . " and status=1 and user_id >0 and add_time < '$nowTime' ")->sum('due_capital');
                    
                    
                    \Think\Log::write('融资方Id：'.$val['id'].';projectId：'.$v['id'].';金额：'.$_amt,'INFO');
                    
                    
                    $_interest = $_amt * $v['user_interest']/100/365;
                    
                    $financingList[$key]['total_amt'] += $_amt;
                    $financingList[$key]['total_interest'] += $_interest;
                }
                
                
                if($financingList[$key]['total_amt']>0){
                    
                    $dd['treaty_rate'] = M('ProjectProtocolRate')->where(array('fid'=>$val['id'],'add_time'=>$statisticsTime))->getField('rate');
                    
                    if(!$dd['treaty_rate']) {
                        $dd['treaty_rate'] = 15;
                    }
                    
                    $dd['avg_rate'] = ($financingList[$key]['total_interest']*365)/$financingList[$key]['total_amt'] * 100;
                    $dd['avg_rate'] = number_format($dd['avg_rate'], 2, '.', '');
                    $dd['earnings'] = ($dd['treaty_rate'] - $dd['avg_rate']) / 100 * $financingList[$key]['total_amt'] / 365;
                    $dd['amount'] = $financingList[$key]['total_amt'];
                    $dd['stat_date'] = $statisticsTime;
                    $dd['financing_id'] = $val['id'];
                    $dd['create_time'] = time();
                    M('StatisticsDailyProjectEarnings')->add($dd);
                    
                }
                
            }
        }
    }
    
    
    
    /**
     * 统计平台销售额
     */
    public function run_platform_sales() {
        $date = date("Y-m-d");
        $projectTotleMoney = M('UserDueDetail')->where("user_id>0 and add_time<'$date'")->sum('due_capital');
        $dd['amount'] = $projectTotleMoney;
        $dd['stat_date'] = date("Y-m-d",strtotime("-1 day"));
        $dd['create_time'] = time();
        M('StatisticsPlatformSaleDaily')->add($dd);
    }
    
    /**
     * 每天下午0点统计账户余额
     */
    public function run_statistics_wallet(){
        $dd['amount'] = M("userAccount")->sum('wallet_totle');
        $dd['stat_date'] = date("Y-m-d H:i:s");
        M('statisticsDailyWallet')->add($dd);
    }
}

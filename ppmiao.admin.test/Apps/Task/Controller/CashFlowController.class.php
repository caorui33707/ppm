<?php
/**
 * 定时处理任务-(T+1)
 * 每日资金流状况处理
 */
namespace Task\Controller;
use Think\Controller;

class CashFlowController extends Controller {
    //资金流处理
    public function flow(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        $yesterday = date("Y-m-d",strtotime("-1 days",time()));//昨天
        $before_yesterday = date("Y-m-d",strtotime("-2 days",time()));//前天
        if(!$yesterday || !$before_yesterday){
            exit;
        }
        $UserDueDetailObj = M("UserDueDetail");//用户投资记录表
        $UserWalletRecordsObj = M("UserWalletRecords");//用户钱包转入/转出记录表
        $PlatformCashFlowObj  = M("PlatformCashFlow");//平台资金流记录表
        //理财流入
        //1.0银行卡转理财
        $b2due_amount = $UserDueDetailObj->where("user_id>0 and from_wallet=0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum('due_capital');
        //2.0钱包转理财
        $w2due_amount = $UserDueDetailObj->where("user_id>0 and from_wallet=1 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("due_capital");
        //3.0理财转钱包
        $due2w_amount = $UserDueDetailObj->where("user_id>0 and to_wallet=1 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("due_capital");
        $due_inflow_amount = $b2due_amount+$w2due_amount-$due2w_amount;
        //理财流出
        $due_flowout_amount = $UserDueDetailObj->where("user_id>0 and to_wallet = 0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("due_capital");
        //钱包流入
        //1.0银行卡转钱包
        $b2wallet_amount = $UserWalletRecordsObj->where("type=1 and pay_status =2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("value");
        //2.0钱包转理财
        $wallet2p_amount = $UserWalletRecordsObj->where("type=2  and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("value");
        //3.0理财转钱包
        $p2wallet_amount = $UserWalletRecordsObj->where("type=1 and pay_status =2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum("value");
        $wallet_inflow_amount = $b2wallet_amount-$wallet2p_amount+$p2wallet_amount;
        //钱包流出
        $wallet_flowout_amount = $UserWalletRecordsObj->where("type=2  and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$before_yesterday." 15:00:00.000000' and add_time<='".$yesterday." 15:00:00.000000'")->sum("value");
        //理财存量
        //1.0银行卡投资
        $b2p_sql = "select sum(h.`due_capital`) as b2p_money from stone.`s_user_due_detail` as h where h.`user_id`>0 and h.`from_wallet`= 0 and  h.`add_time`<='".$yesterday." 23:59:59.999000' and h.`project_id` in(select k.id from stone.`s_project` as k where (k.`term_type` =1 AND k.`is_delete` = 0))";
        $due_b_to_p_tmp_amount = M()->query($b2p_sql);
        $due_b_to_p_amount = $due_b_to_p_tmp_amount[0]['b2p_money'];
        //1.1钱包投资
        $w2p_sql = "select sum(h.`due_capital`) as w2p_money from stone.`s_user_due_detail` as h where h.`user_id`>0 and h.`from_wallet`= 1 and  h.`add_time`<='".$yesterday." 23:59:59.999000' and h.`project_id` in(select k.id from stone.`s_project` as k where (k.`term_type` =1 AND k.`is_delete` = 0))";
        $due_w_to_p_tmp_amount = M()->query($w2p_sql);
        $due_w_to_p_amount = $due_w_to_p_tmp_amount[0]['w2p_money'];
        //1.2还款到银行卡
        $p2b_sql = "select sum(h.`due_capital`) as p2b_money from stone.`s_user_due_detail` as h where h.`user_id`>0 and h.`to_wallet`= 0 and  h.`add_time`<='".$yesterday." 23:59:59.999000' and h.`project_id` in(select k.id from stone.`s_project` as k where (k.`term_type` =1 AND k.`is_delete` = 0))";
        $due_p_to_b_tmp_amount = M()->query($p2b_sql);
        $due_p_to_b_amount = $due_p_to_b_tmp_amount[0]['p2b_money'];
        //1.3还款到钱包
        $p2w_sql = "select sum(h.`due_capital`) as p2w_money from stone.`s_user_due_detail` as h where h.`user_id`>0 and h.`to_wallet`= 1 and  h.`add_time`<='".$yesterday." 23:59:59.999000' and h.`project_id` in(select k.id from stone.`s_project` as k where (k.`term_type` =1 AND k.`is_delete` = 0))";
        $due_p_to_w_tmp_amount = M()->query($p2w_sql);
        $due_p_to_w_amount = $due_p_to_w_tmp_amount[0]['p2w_money'];
        //1.4理财存量
        $due_stock_amount = $due_b_to_p_amount+$due_w_to_p_amount-$due_p_to_b_amount-$due_p_to_w_amount;
        //钱包存量

        $wallet_stock_amount   = 0;
        $tj_start_time = "2015-07-01";//start time
        $tj_end_time   = $yesterday;//end time
        for($i=strtotime($tj_start_time);$i<=strtotime($tj_end_time);$i+=86400){
            $date_time = date("Y-m-d",$i);//日期
            $date_yesterday = date("Y-m-d",strtotime("-1 days",$i));//指定日期前一日
            //1.0充值
            $cz_sql = "SELECT SUM(e.`value`) AS cz_money FROM stone.`s_user_wallet_records` AS e WHERE e.`type` = 1 AND e.`pay_status`= 2 AND  e.`user_bank_id`>0 AND e.`user_due_detail_id`=0 AND e.`add_time`>='".$date_time." 00:00:00.000000' AND e.`add_time`<='".$date_time." 23:59:59.999000'";
            $cz_tmp_amount = M()->query($cz_sql);
            $cz_amount = $cz_tmp_amount[0]['cz_money'];
            //1.1理财还款
            $p2w_sql = "SELECT SUM(e.`value`) AS p2w_money FROM stone.`s_user_wallet_records` AS e WHERE e.`type` = 1 AND e.`pay_status`= 2 AND  e.`user_bank_id`=0 AND e.`user_due_detail_id`>0 AND e.`add_time`>='".$date_time." 00:00:00.000000' AND e.`add_time`<='".$date_time." 23:59:59.999000'";
            $p2w_tmp_amount = M()->query($p2w_sql);
            $p2w_amount = $p2w_tmp_amount[0]['p2w_money'];
            //1.2提现
            $w2b_sql = "SELECT SUM(e.`value`) AS w2b_money FROM stone.`s_user_wallet_records` AS e WHERE e.`type` = 2  AND  e.`user_bank_id`>0 AND e.`user_due_detail_id`=0 AND e.`add_time`>='".$date_yesterday." 15:00:00.000000' AND e.`add_time`<='".$date_time." 15:00:00.000000'";
            $w2b_tmp_amount = M()->query($w2b_sql);
            $w2b_amount = $w2b_tmp_amount[0]['w2b_money'];
            //1.3购买理财
            $w2p_sql = "SELECT SUM(e.`value`) AS w2p_money FROM stone.`s_user_wallet_records` AS e WHERE e.`type` = 2  AND  e.`user_bank_id`=0 AND e.`user_due_detail_id`>0 AND e.`add_time`>='".$date_time." 00:00:00.000000' AND e.`add_time`<='".$date_time." 23:59:59.999000'";
            $w2p_tmp_amount = M()->query($w2p_sql);
            $w2p_amount = $w2p_tmp_amount[0]['w2p_money'];
            //1.4钱包存量
            $tmp_wallet_stock = abs($cz_amount)+abs($p2w_amount)-abs($w2b_amount)-abs($w2p_amount);
            //1.5查出指定日期的年化利率
            $WalletAnnualizedRateSql = "SELECT m.`rate` FROM stone.`s_user_wallet_annualized_rate` AS m WHERE m.`add_time` = '".$date_time."'";
            $WalletAnnualizedRateObj = M()->query($WalletAnnualizedRateSql);
            $WalletAnnualizedRate = $WalletAnnualizedRateObj[0]['rate'];
            $wallet_stock_amount+=$tmp_wallet_stock+($tmp_wallet_stock*($WalletAnnualizedRate/100/365));
        }
        //写入数据表
        $CashFlowRecordId = $PlatformCashFlowObj->where("day='".$yesterday."'")->getField('id');
        if($CashFlowRecordId){//update
            $data=array(
                'due_inflow'=>$due_inflow_amount,
                'due_flowout'=>$due_flowout_amount,
                'due_stock'=>$due_stock_amount,
                'wallet_inflow'=>$wallet_inflow_amount,
                'wallet_flowout'=>$wallet_flowout_amount,
                'wallet_stock'=>$wallet_stock_amount,
                'update_time'=>date("Y-m-d H:i:s"),
            );
            $update_status = $PlatformCashFlowObj->where("id=".$CashFlowRecordId)->save($data);
            if($update_status!==false){//success
                echo "update success";exit;
            }else{//fail
                echo "update fail";exit;
            }
        }else{//add
            $data=array(
                'day'=>$yesterday,
                'due_inflow'=>$due_inflow_amount,
                'due_flowout'=>$due_flowout_amount,
                'due_stock'=>$due_stock_amount,
                'wallet_inflow'=>$wallet_inflow_amount,
                'wallet_flowout'=>$wallet_flowout_amount,
                'wallet_stock'=>$wallet_stock_amount,
                'update_time'=>date("Y-m-d H:i:s"),
                'add_time'=>date('Y-m-d H:i:s')
            );
            $add_status = $PlatformCashFlowObj->add($data);
            if($add_status){
                echo "add success";exit;
            }else{
                echo "add fail";exit;
            }
        }

    }
}
<?php
/**
 * 钱包每日应付利息
 */
namespace Task\Controller;

use Think\Controller;

class WalletController extends Controller
{

    public function earnings()
    {
        // 计算总金额
        $current_date = date("Y-m-d",strtotime("-1 day"));
        $totle = M("UserAccount")->sum('wallet_totle');
        $info = M('UserWalletAnnualizedRate')->where(array('add_time'=>$current_date))->find();
        
        if(!$info) {
            $info['rate'] = 6.5;
            $info['pf_rate'] = 10;
            $info['financing'] = '默认';
        }
        
        $takeout_amount = abs(M('userWalletRecords')->where('user_id>0 and type=2 and user_bank_id>0 and status=3')->sum('value'));
        $totle = $totle + $takeout_amount;
        
        $due_amount = $totle * ($info['pf_rate'] - $info['rate']) / 365 / 100;

        $dd = array(
            'amount'=>$totle,
            'due_amount' =>$due_amount,
            'rate'=>$info['rate'],
            'pf_rate'=>$info['pf_rate'],
            'financing'=>$info['financing'],
            'add_time'=>$current_date,
            'takeout_amount'=>$takeout_amount
        );
       if(!M("StatisticsDailyWalletEarnings")->add($dd)) {
           \Think\Log::write('出错啦'.$dd,'INFO');
       }   
    }
}
<?php
/**
 * 定时处理任务-昨天(T-1)理财收支处理(每日凌晨40分处理 00:40)
 *
 */
namespace Task\Controller;
use Think\Controller;

class IncomeController extends Controller {
    /**
     * 理财收支处理(过滤掉幽灵账号)
     * 理财收入计算公式:每日银行卡转理财资金+每日钱包转理财资金）X 线下利率(合同利率)X期限(注释:产品到期日期-当天日期)/100/365  + （银行卡转理财资金+钱包转理财资金）X合同手续费/100
     * 理财支出计算公式:（银行卡转理财资金+钱包转理财资金）X 线上利率X期限(注释:产品到期日期-当天日期)/100/365  + （银行卡转理财资金+钱包转理财资金）*0.2％ + 理财转银行卡订单数
     * 理财收益计算公式：每日理财收入-每日理财支出
     */
    public function due_income(){
        //昨日时间处理
        $yesterday = date("Y-m-d",strtotime("-1 days",time()));
        if(!$yesterday){
            exit;
        }

        $rows['dt'] = $yesterday;
        $projectObj = M("Project");
        $userDueDetailObj = M("UserDueDetail");
        $userWalletRecordsObj = M("UserWalletRecords");
        $statisticsDailyProfitObj = M("StatisticsDailyProfit");
        $contractObj = M("Contract");
        $projectContractObj = M("ContractProject");
        $taskIncomeObj = M("TaskIncome");
        $projectInterest = array();

        $result = $userDueDetailObj->field('project_id')->where("user_id>0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->group("project_id")->select();
        foreach($result as $key => $val){

            $interest = $projectObj->field('id,contract_interest,user_interest,end_time,start_time,title')->where(array('id'=>$val['project_id'],'term_type'=>1))->find();
            //手续费
            $project_contract = $projectContractObj->field("contract_id")->where(array('project_id'=>$val['project_id'],'project_name'=>$interest['title']))->find();

            if($project_contract){
                $contract_info = $contractObj->field("fee,interest")->where(array('id'=>$project_contract['contract_id']))->find();
            }

            $user_start_time = $userDueDetailObj->field('add_time')->where('project_id='.$val['project_id'])->order("add_time asc")->find();

            if($interest && $contract_info) $projectInterest[$val['project_id']] = array(
                'id'=>$interest['id'],
                'contract_interest'=>$contract_info['interest'],
                'user_interest'=>$interest['user_interest'],
                'fee'=>$contract_info['fee'],
                'start_time'=>$user_start_time['add_time'],
                'end_time'=>$interest['end_time']

            );
        }
        //理财收入/支出变量定义
        $incomeSum = 0; $expensesSum = 0;
        foreach($projectInterest as $key => $val){

            $dueList = $userDueDetailObj->field('due_capital,from_wallet,to_wallet')->where("user_id>0 and project_id=".$val['id']." and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->select();

            $days = count_days(date('Y-m-d', strtotime($val['end_time'])).' 08:00:00', date('Y-m-d', strtotime($yesterday)).' 08:00:00');
            $sum = 0; $payback_count = 0;

            foreach($dueList as $k => $v){
                $sum += $v['due_capital'];
                if($v['from_wallet'] == 0 && $v['to_wallet'] == 0) $payback_count ++;
            }

            $incomeSum += $sum*$days*$val['contract_interest']/100/365+ $sum*$val['fee']/100;
            $expensesSum += $sum*$days*$val['user_interest']/100/365 + $sum*0.002;

        }

        $rows['p_income'] = round($incomeSum, 2);
        $rows['p_expenses'] = round($expensesSum, 2);
        $rows['p_profit'] = round($incomeSum, 2)-round($expensesSum, 2);

        $existId = $statisticsDailyProfitObj->where(array('dt'=>$yesterday))->getField('id');
        if(!$existId){
            if(!$statisticsDailyProfitObj->add($rows)){//fail
                $dealRows = array(
                    'day'=>$yesterday,
                    'type'=>1,
                    'deal'=>0,
                    'add_time'=>date("Y-m-d H:i:s"),
                    'update_time'=>date("Y-m-d H:i:s")
                );
                $taskIncomeObj->add($dealRows);
            }else{ //success
                $dealRows = array(
                    'day'=>$yesterday,
                    'type'=>1,
                    'deal'=>1,
                    'add_time'=>date("Y-m-d H:i:s"),
                    'update_time'=>date("Y-m-d H:i:s")
                );
                $taskIncomeObj->add($dealRows);
            }
        }else{
            $update_status = $statisticsDailyProfitObj->where(array('id'=>$existId))->save($rows);
            if($update_status!==false){ //success
                $dealRows = array(
                    'type'=>1,
                    'deal'=>1,
                    'update_time'=>date("Y-m-d H:i:s")
                );
                $taskIncomeObj->where(array('day'=>$yesterday))->save($dealRows);
            }else{ //fail
                $dealRows = array(
                    'type'=>1,
                    'deal'=>0,
                    'update_time'=>date("Y-m-d H:i:s")
                );
                $taskIncomeObj->where(array('day'=>$yesterday))->save($dealRows);
            }
        }
    }
    /**
     * 钱包支出处理-昨天(T-1)-(每晚凌晨50分处理 00:50)
     * 钱包支出计算公式:昨日钱包存量 * (今日线上利率/100/365) + 每日钱包充值金额*0.2％ + 每日钱包提现手续费
     */
    public function wallet_expenses(){

        $userWalletRecordsObj = M("UserWalletRecords");//钱包转入/转出记录表
        $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");//钱包年化利率预设表
        $StatisticsDailyProfitObj = M("StatisticsDailyProfit");//每日平台收支记录表
        $TaskIncomeObj = M("TaskIncome");//每日平台收支处理记录表
        $yesterday_date = date("Y-m-d",strtotime("-1 days",time()));//昨天日期
        $before_yesterday_date = date("Y-m-d",strtotime("-2 days",time()));//前天日期
        if(!$yesterday_date || !$before_yesterday_date){
            exit;
        }
        //昨日钱包存量
        $residualAmount = $userWalletRecordsObj->where("((type=1 and pay_status=2) or type=2) and add_time<='".$before_yesterday_date." 23:59:59.999000'")->sum('value');
        //银行卡充值钱包金额
        $rechargeFromBank = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yesterday_date." 00:00:00.000000' and add_time<='".$yesterday_date." 23:59:59.999000'")->sum('value');
        //充值钱包金额手续费2/1000
        if($rechargeFromBank){
            $rechargeFromBankFee = $rechargeFromBank*(2/1000);
        }
        //当天钱包的年化利率
        $userWalletAnnualizedRateArr = $userWalletAnnualizedRateObj->where("add_time='".$yesterday_date."'")->find();
        $yesterdayUserWalletAnnualizedRate = $userWalletAnnualizedRateArr['rate'];
        //当天钱包需要支付的利息
        $rechargeInterestAmount = $residualAmount * ($yesterdayUserWalletAnnualizedRate/100/365);
        //获取当天钱包提现记录
        $tx_sql = "SELECT m.`add_time`,m.`value`,m.`pay_type`,k.`bank_code`  FROM stone.`s_user_wallet_records` AS m,stone.`s_user_bank` AS k WHERE m.`type` = 2 AND m.`user_bank_id`>0 AND m.`user_due_detail_id` = 0 AND m.`add_time`>='".$before_yesterday_date." 15:00:00.000000' AND m.`add_time`<='".$yesterday_date." 15:00:00.000000' AND m.`user_bank_id` = k.`id`";
        $rechargeToBankArr = M()->query($tx_sql);
        /**
         * 当天钱包提现手续费
         * 连连每笔1块,盛付通1W以下1块(包括),1W以上2块),3家银行用连连:邮政(01000000)，华夏(03040000)，兴业(03090000)
         * */
        $tx_fee=0;
        foreach($rechargeToBankArr as $k=>$v){
            if($v['add_time']<'2015-11-03 23:59:59.999000'){
                if(in_array($v['bank_code'], array('01000000','03040000','03090000'))){
                    $tx_fee += 1;
                }else{
                    if(abs($v['value']) <= 10000){
                        $tx_fee += 1;
                    }else{
                        $tx_fee += 2;
                    }
                }
            }else{//融宝支付
                $boundary_fee = 50000;
                $tx_value  = abs($v['value']);
                if($tx_value <= $boundary_fee){
                    $tx_fee+=1;
                }else{
                    $tx_fee+=round($tx_value/$boundary_fee);
                }
            }
        }
        //每日钱包支出
        $wallet_expenses_amount = $rechargeInterestAmount+$rechargeFromBankFee+$tx_fee;
        //查看收支记录表
        $StatisticsDailyProfitArr = $StatisticsDailyProfitObj->where("dt='".$yesterday_date."'")->find();
        $add_time = date("Y-m-d H:i:s").'.000000';
        if($StatisticsDailyProfitArr){//update
            $update_status = $StatisticsDailyProfitObj->where('id='.$StatisticsDailyProfitArr['id'])->save(array('w_expenses'=>$wallet_expenses_amount));
            if($update_status!==false){//success
                $data=array(
                    'day'=>$yesterday_date,
                    'type'=>2,
                    'deal'=>1,
                    'add_time'=>$add_time,
                    'update_time'=>$add_time,
                );
            }else{//fail
                $data=array(
                    'day'=>$yesterday_date,
                    'type'=>2,
                    'deal'=>0,
                    'add_time'=>$add_time,
                    'update_time'=>$add_time,
                );
            }
            $TaskIncomeObj->add($data);
        }else{//add
            //先添加收支记录
            $profit_data=array(
                'dt'=>$yesterday_date,
                'w_expenses'=>$wallet_expenses_amount
            );
            $StatisticsDailyProfitObj->add($profit_data);
            //再添加收支处理记录
            $data=array(
                'day'=>$yesterday_date,
                'type'=>2,
                'deal'=>1,
                'add_time'=>$add_time,
                'update_time'=>$add_time,
            );
            $TaskIncomeObj->add($data);
        }
    }
    /**
     * 钱包收入处理-昨天(T-1)-(每晚凌晨1点钟)
     * 钱包收入计算公式:每日线下收入+每日钱包投资理财*02/100
     */
    public function wallet_income(){
        $UserWalletRecordsObj = M("UserWalletRecords");//钱包转入/转出记录表
        $WalletIncomeRecordsObj = M("WalletIncomeRecords");//钱包线下收入记录表
        $StatisticsDailyProfitObj = M("StatisticsDailyProfit");//每日平台收支记录表
        $TaskIncomeObj = M("TaskIncome");//每日平台收支处理记录表
        //昨日时间处理
        $yesterday = date("Y-m-d",strtotime("-1 days",time()));
        if(!$yesterday){
            exit;
        }
        //钱包投资理财(每日钱包转理财*0.2%)
        $walletToDueAmountTmp = $UserWalletRecordsObj->where("type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$yesterday." 00:00:00.000000' and add_time<='".$yesterday." 23:59:59.999000'")->sum('value');
        $walletToDueAmount = abs($walletToDueAmountTmp)*(2/1000);
        //钱包线下收入:one表示15%,two表示18%,three表示16%,four表示16.5%,five表示14%
        $walletOfflineIncomeAmount = 0;
        //进入
        $wallet_enter_amount_one   = 0;
        $wallet_enter_amount_two   = 0;
        $wallet_enter_amount_three = 0;
        $wallet_enter_amount_four  = 0;
        $wallet_enter_amount_five  = 0;
        //出去
        $wallet_out_amount_one     = 0;
        $wallet_out_amount_two     = 0;
        $wallet_out_amount_three   = 0;
        $wallet_out_amount_four    = 0;
        $wallet_out_amount_five    = 0;
        //总收入
        $total_wallet_income_amount = 0;
        //具体统计

        //one-15%
         $one_where = array();
         $one_where[] = "dt<='".$yesterday."'";
         $one_where[] = "rate_type=1";
         if($one_where){
            $one_cond = implode(" and ",$one_where);
         }
         $wallet_enter_amount_one = $WalletIncomeRecordsObj->where($one_cond)->sum('income_amount');
         $wallet_out_amount_one   = $WalletIncomeRecordsObj->where($one_cond)->sum('expense_amount');
         $total_wallet_income_amount+=($wallet_enter_amount_one-$wallet_out_amount_one)*0.15/365;
        //two-18%
        $two_where = array();
        $two_where[] = "dt<='".$yesterday."'";
        $two_where[] = "rate_type=2";
        if($two_where){
            $two_cond = implode(" and ",$two_where);
        }
        $wallet_enter_amount_two = $WalletIncomeRecordsObj->where($two_cond)->sum('income_amount');
        $wallet_out_amount_two   = $WalletIncomeRecordsObj->where($two_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_two-$wallet_out_amount_two)*0.18/365;
        //three-16%
        $three_where = array();
        $three_where[] = "dt<='".$yesterday."'";
        $three_where[] = "rate_type=3";
        if($three_where){
            $three_cond = implode(" and ",$three_where);
        }
        $wallet_enter_amount_three = $WalletIncomeRecordsObj->where($three_cond)->sum('income_amount');
        $wallet_out_amount_three   = $WalletIncomeRecordsObj->where($three_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_three-$wallet_out_amount_three)*0.16/365;
        //four-16.5%
        $four_where = array();
        $four_where[] = "dt<='".$yesterday."'";
        $four_where[] = "rate_type=4";
        if($four_where){
            $four_cond = implode(" and ",$four_where);
        }
        $wallet_enter_amount_four = $WalletIncomeRecordsObj->where($four_cond)->sum('income_amount');
        $wallet_out_amount_four   = $WalletIncomeRecordsObj->where($four_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_four-$wallet_out_amount_four)*0.165/365;
        //five-14%
        $five_where = array();
        $five_where[] = "dt<='".$yesterday."'";
        $five_where[] = "rate_type=5";
        if($five_where){
            $five_cond = implode(" and ",$five_where);
        }
        $wallet_enter_amount_five = $WalletIncomeRecordsObj->where($five_cond)->sum('income_amount');
        $wallet_out_amount_five   = $WalletIncomeRecordsObj->where($five_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_five-$wallet_out_amount_five)*0.18/365;
        //最终钱包收入
        $result_wallet_income = $walletToDueAmount+$total_wallet_income_amount;
        //查看收支记录表
        $StatisticsDailyProfitArr = $StatisticsDailyProfitObj->where("dt='".$yesterday."'")->find();
        $add_time = date("Y-m-d H:i:s").'.000000';
        $w_profit_amount =$result_wallet_income-$StatisticsDailyProfitArr['w_expenses'];
        if($StatisticsDailyProfitArr){//update
            $update_status = $StatisticsDailyProfitObj->where('id='.$StatisticsDailyProfitArr['id'])->save(array('w_income'=>$result_wallet_income,'w_profit'=>$w_profit_amount));
            if($update_status!==false){//success
                $data=array(
                    'day'=>$yesterday,
                    'type'=>3,
                    'deal'=>1,
                    'add_time'=>$add_time,
                    'update_time'=>$add_time,
                );
            }else{//fail
                $data=array(
                    'day'=>$yesterday,
                    'type'=>3,
                    'deal'=>0,
                    'add_time'=>$add_time,
                    'update_time'=>$add_time,
                );
            }
            $TaskIncomeObj->add($data);
        }else{//add
            //先添加收支记录
            $profit_data=array(
                'dt'=>$yesterday,
                'w_income'=>$result_wallet_income,
                'w_profit'=>$w_profit_amount
            );
            $StatisticsDailyProfitObj->add($profit_data);
            //再添加收支处理记录
            $data=array(
                'day'=>$yesterday,
                'type'=>3,
                'deal'=>1,
                'add_time'=>$add_time,
                'update_time'=>$add_time,
            );
            $TaskIncomeObj->add($data);
        }
    }
}
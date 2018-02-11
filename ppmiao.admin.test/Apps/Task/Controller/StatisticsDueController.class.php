<?php
/**
 * 定时处理任务-(T+1)
 * 每日投资用户数据和投资资金统计处理
 */
namespace Task\Controller;
use Think\Controller;

class StatisticsDueController extends Controller {
    //处理逻辑
    public function due(){
        //处理内存分配
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        //确定日期
        $yesterday = date("Y-m-d",strtotime("-1 days",time()));//昨天
        if(!$yesterday){
            exit;
        }
        $dt = $yesterday;
        //数据模型
        $StatisticsDailyDueObj = M("StatisticsDailyDue");
        //变量定义
        $allUids = '';
        $productUids = '';
        $walletUids = '';
        $allUidsArr = array();
        $productUidsArr = array();
        $walletUidsArr = array();
        $total_user = 0;//投资用户总数
        $p_user = 0;//理财投资用户
        $w_user = 0;//钱包投资用户
        $due_amount = 0;//投资总额
        $first_due_amount = 0;//首投金额
        $second_due_amount = 0;//二投金额
        $reply_due_amount  = 0;//复投金额

        $rows['dt'] = $dt;
        // 购买产品用户UID
        $sql = "select user_id from s_user_due_detail where user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
        $uidsResult = M()->query($sql);
        foreach($uidsResult as $key => $val){
            $productUids .= ','.$val['user_id'];
        }
        if($productUids) {
            $productUids = substr($productUids, 1);
            $productUidsArr = explode(',', $productUids);
            $rows['p_user'] = count($productUidsArr);//每日理财投资用户总数
        }
        // 购买产品资金
        $p_user_amount_sql = "select due_capital from s_user_due_detail where user_id>0 and user_id in(".$productUids.") and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'";
        $p_user_amountResult = M()->query($p_user_amount_sql);
        foreach($p_user_amountResult as $key => $val){
            $due_amount += $val['due_capital'];
        }
        // 购买钱包用户UID
        $sql = "select user_id from s_user_wallet_records where user_id >0 and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
        $uidsResult = M()->query($sql);
        foreach ($uidsResult as $key => $val){
            $walletUids .= ','.$val['user_id'];
        }
        if($walletUids) {
            $walletUids = substr($walletUids, 1);
            $walletUidsArr = explode(',', $walletUids);
            $rows['w_user'] = count($walletUidsArr);//每日钱包投资用户总数
        }
        // 购买钱包资金
        $w_user_amount_sql = "select value from s_user_wallet_records where user_id >0 and user_id in(".$walletUids.") and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'";
        $w_user_amountResult = M()->query($w_user_amount_sql);
        foreach($w_user_amountResult as $key => $val){
            $due_amount += $val['value'];
        }
        if($productUids && $walletUids)
        {
            $allUids = $productUids.','.$walletUids;
        }else if($productUids && !$walletUids){
            $allUids = $productUids;
        }else if(!$productUids && $walletUids){
            $allUids = $walletUids;
        }

        // 购买产品+钱包用户UID
        if($allUids) {
            $allUids = implode(',', array_unique(explode(',', $allUids)));
            $allUidsArr = explode(',', $allUids);
            $total_user = count($allUidsArr);
        }

        $rows['total_user'] = $total_user;//每日投资理财(包括钱包)用户总数

        //每日理财首投用户
        $first_total_user_arr = array();
        $first_p_user_sql = "SELECT w.user_id FROM (SELECT m.`id`,m.`user_id` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'  AND m.`user_id` NOT IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id`) AS w";
        $uidsResult = M()->query($first_p_user_sql);
        $first_p_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($first_total_user_arr,$val['user_id']);
            $first_p_user_str .= ','.$val['user_id'];
        }
        if($first_p_user_str) {
            $first_p_user_str = substr($first_p_user_str, 1);
            $first_p_user_arr = explode(',', $first_p_user_str);
            $rows['first_p_user'] = count($first_p_user_arr);//每日理财首投用户
        }
        //每日理财首投资金
        $first_p_amount_sql = "SELECT m.`id`,m.`user_id`,m.`due_capital` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`user_id` IN(".$first_p_user_str.") AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $first_p_amountResult = M()->query($first_p_amount_sql);
        foreach ($first_p_amountResult as $key => $val){
            $first_due_amount += $val['due_capital'];
        }
        //每日钱包首投用户
        $first_w_user_sql = "SELECT p.user_id FROM (SELECT  m.`id` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND m.`type`=1 AND m.`pay_status`=2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000' AND m.`user_id` NOT IN(SELECT n.`user_id` FROM stone.`s_user_wallet_records` AS n WHERE n.`user_id`>0  AND n.`type`=1 AND n.`pay_status`=2 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id`) AS p";
        $uidsResult = M()->query($first_w_user_sql);
        $first_w_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($first_total_user_arr,$val['user_id']);
            $first_w_user_str .= ','.$val['user_id'];
        }
        if($first_w_user_str) {
            $first_w_user_str = substr($first_w_user_str, 1);
            $first_w_user_arr = explode(',', $first_w_user_str);
            $rows['first_w_user'] = count($first_w_user_arr);//每日钱包首投用户
        }
        //每日钱包首投资金
        $first_w_amount_sql = "SELECT  m.`id`,m.`value ` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND  m.`user_id` in(".$first_w_user_str.") AND m.`type`=1 AND m.`pay_status`=2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $first_w_amountResult = M()->query($first_w_amount_sql);
        foreach ($first_w_amountResult as $key => $val){
            $first_due_amount += $val['value'];
        }
        //首投用户总数
        $rows['first_total_user'] = count(array_unique($first_total_user_arr));
        //二投理财用户数
        $second_total_user_arr = array();
        $second_p_user_sql = "SELECT m.`user_id` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'  AND m.`user_id`  IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$dt." 00:00:00.000000' GROUP BY n.`user_id` HAVING COUNT(n.`id`)=1) GROUP BY m.`user_id` HAVING COUNT(m.`id`)=1";
        $uidsResult = M()->query($second_p_user_sql);
        $second_p_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($second_total_user_arr,$val['user_id']);
            $second_p_user_str .= ','.$val['user_id'];
        }
        if($second_p_user_str) {
            $second_p_user_str = substr($second_p_user_str, 1);
            $second_p_user_arr = explode(',', $second_p_user_str);
            $rows['second_p_user'] = count($second_p_user_arr);//每日二投理财用户
        }
        //二投理财资金
        $second_p_amount_sql = "SELECT m.`user_id`,m.`due_capital` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`user_id` IN(".$second_p_user_str.") AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $second_p_amountResult = M()->query($second_p_amount_sql);
        foreach ($second_p_amountResult as $key => $val){
            $second_due_amount += $val['due_capital'];
        }
        //二投钱包用户数
        $second_w_user_sql = "SELECT m.`user_id` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND m.`type`=1 AND m.`pay_status` = 2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'  AND m.`user_id`  IN(SELECT n.`user_id` FROM stone.`s_user_wallet_records` AS n WHERE n.`user_id`>0 AND n.`type` = 1 AND n.`pay_status`= 2 AND n.`add_time`<='".$dt." 00:00:00.000000' GROUP BY n.`user_id` HAVING COUNT(n.`id`)=1) GROUP BY m.`user_id` HAVING COUNT(m.`id`)=1";
        $uidsResult = M()->query($second_w_user_sql);
        $second_w_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($second_total_user_arr,$val['user_id']);
            $second_w_user_str .= ','.$val['user_id'];
        }
        if($second_w_user_str) {
            $second_w_user_str = substr($second_w_user_str, 1);
            $second_w_user_arr = explode(',', $second_w_user_str);
            $rows['second_w_user'] = count($second_w_user_arr);//每日二投钱包用户
        }
        //二投钱包资金
        $second_w_amount_sql = "SELECT m.`user_id`,m.`value` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND m.`user_id` in(".$second_w_user_str.") AND m.`type`=1 AND m.`pay_status` = 2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $second_w_amountResult = M()->query($second_w_amount_sql);
        foreach ($second_w_amountResult as $key => $val){
            $second_due_amount += $val['value'];
        }
        //二投用户总数
        $rows['second_total_user'] = count(array_unique($second_total_user_arr));

        //每日复投理财用户数
        $reply_total_user_arr = array();
        $reply_p_user_sql = "SELECT w.user_id FROM (SELECT m.`id` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'  AND m.`user_id` IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id`) AS w";
        $uidsResult = M()->query($reply_p_user_sql);
        $reply_p_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($reply_total_user_arr,$val['user_id']);
            $reply_p_user_str .= ','.$val['user_id'];
        }
        if($reply_p_user_str) {
            $reply_p_user_str = substr($reply_p_user_str, 1);
            $reply_p_user_arr = explode(',', $reply_p_user_str);
            $rows['reply_p_user'] = count($reply_p_user_arr);//每日复投理财用户数
        }
        //每日复投理财金额
        $reply_p_amount_sql = "SELECT m.`id`,m.`due_capital` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0  AND m.`user_id` IN(".$reply_p_user_str.") AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $reply_p_amountResult = M()->query($reply_p_amount_sql);
        foreach ($reply_p_amountResult as $key => $val){
            $reply_due_amount += $val['due_capital'];
        }
        //每日复投钱包用户数
        $reply_w_user_sql = "SELECT  p.user_id FROM (SELECT  m.`id` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND m.`type`=1 AND m.`pay_status`=2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000' AND m.`user_id` IN(SELECT n.`user_id` FROM stone.`s_user_wallet_records` AS n WHERE n.`user_id`>0  AND n.`type`=1 AND n.`pay_status`=2 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id`) AS p";
        $uidsResult = M()->query($reply_w_user_sql);
        $reply_w_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($reply_total_user_arr,$val['user_id']);
            $reply_w_user_str .= ','.$val['user_id'];
        }
        if($reply_w_user_str) {
            $reply_w_user_str = substr($reply_w_user_str, 1);
            $reply_w_user_arr = explode(',', $reply_w_user_str);
            $rows['reply_p_user'] = count($reply_w_user_arr);//每日复投理财用户数
        }
        //每日复投钱包金额
        $reply_w_amount_sql = "SELECT  m.`id`,m.`value` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0  AND m.`user_id` IN(".$reply_w_user_str.") AND m.`type`=1 AND m.`pay_status`=2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'";
        $reply_w_amountResult = M()->query($reply_w_amount_sql);
        foreach ($reply_w_amountResult as $key => $val){
            $reply_due_amount += $val['value'];
        }
        //每日复投用户总数
        $rows['reply_total_user'] = count(array_unique($reply_total_user_arr));
        //当日激活当日理财用户
        $act_total_user_arr = array();
        $act_p_user_sql = "SELECT w.`user_id` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id` IN(SELECT k.`id` FROM stone.`s_user` AS k WHERE k.`status` = 2  AND k.`add_time`>='".$dt." 00:00:00.000000' AND k.`add_time`<='".$dt." 23:59:59.999000') AND w.`add_time`>='".$dt." 00:00:00.000000' AND w.`add_time`<='".$dt." 23:59:59.999000' GROUP BY w.`user_id`";
        $uidsResult = M()->query($act_p_user_sql);
        $act_p_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($act_total_user_arr,$val['user_id']);
            $act_p_user_str .= ','.$val['user_id'];
        }
        if($act_p_user_str) {
            $act_p_user_str = substr($act_p_user_str, 1);
            $act_p_user_arr = explode(',', $act_p_user_str);
            $rows['act_p_user'] = count($act_p_user_arr);//当日激活当日理财用户
        }
        //当日激活当日钱包用户
        $act_w_user_sql = "SELECT w.`user_id` FROM stone.`s_user_wallet_records` AS w WHERE w.`type`=1 AND w.`pay_status` = 2 AND w.`user_bank_id`>0 AND w.`user_due_detail_id` = 0 AND w.`user_id` IN(SELECT k.`id` FROM stone.`s_user` AS k WHERE k.`status` = 2  AND k.`add_time`>='".$dt." 00:00:00.000000' AND k.`add_time`<='".$dt." 23:59:59.999000') AND w.`add_time`>='".$dt." 00:00:00.000000' AND w.`add_time`<='".$dt." 23:59:59.999000' GROUP BY w.`user_id`";
        $uidsResult = M()->query($act_w_user_sql);
        $act_w_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($act_total_user_arr,$val['user_id']);
            $act_w_user_str .= ','.$val['user_id'];
        }
        if($act_w_user_str) {
            $act_w_user_str = substr($act_w_user_str, 1);
            $act_w_user_arr = explode(',', $act_w_user_str);
            $rows['act_w_user'] = count($act_w_user_arr);//当日激活当日钱包用户
        }
        //当日激活当日投用户数
        $rows['act_total_user'] = count(array_unique($act_total_user_arr));

        //当日首投当日复投理财用户
        $first_reply_total_user_arr = array();
        $first_reply_p_user_sql = "SELECT w.user_id FROM (SELECT m.`id`,m.`user_id`FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000'  AND m.`user_id` NOT IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id` HAVING COUNT(m.`id`)>1) AS w";
        $uidsResult = M()->query($first_reply_p_user_sql);
        $first_reply_p_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($first_reply_total_user_arr,$val['user_id']);
            $first_reply_p_user_str .= ','.$val['user_id'];
        }
        if($first_reply_p_user_str) {
            $first_reply_p_user_str = substr($first_reply_p_user_str, 1);
            $first_reply_p_user_arr = explode(',', $first_reply_p_user_str);
            $rows['first_reply_p_user'] = count($first_reply_p_user_arr);//当日首投当日复投理财用户
        }
        //当日首投当日复投钱包用户
        $first_reply_w_user_sql = "SELECT p.user_id FROM (SELECT  m.`id` FROM stone.`s_user_wallet_records` AS m WHERE m.`user_id`>0 AND m.`type`=1 AND m.`pay_status`=2 AND m.`add_time`>='".$dt." 00:00:00.000000' AND m.`add_time`<='".$dt." 23:59:59.999000' AND m.`user_id` NOT IN(SELECT n.`user_id` FROM stone.`s_user_wallet_records` AS n WHERE n.`user_id`>0  AND n.`type`=1 AND n.`pay_status`=2 AND n.`add_time`<='".$dt." 00:00:00.000000') GROUP BY m.`user_id` having count(m.`id`)>1) AS p";
        $uidsResult = M()->query($first_reply_p_user_sql);
        $first_reply_w_user_str ="";
        foreach ($uidsResult as $key => $val){
            array_push($first_reply_total_user_arr,$val['user_id']);
            $first_reply_w_user_str .= ','.$val['user_id'];
        }
        if($first_reply_w_user_str) {
            $first_reply_w_user_str = substr($first_reply_w_user_str, 1);
            $first_reply_w_user_arr = explode(',', $first_reply_w_user_str);
            $rows['first_reply_w_user'] = count($first_reply_w_user_arr);//当日首投当日复投钱包用户
        }
        //当日首投当日复投
        $rows['first_reply_total_user'] = count(array_unique($first_reply_total_user_arr));
        // 投资总额
        $rows['due_amount'] = $due_amount;
        //首投金额
        $rows['first_due_amount'] = $first_due_amount;
        //二投金额
        $rows['second_due_amount'] = $second_due_amount;
        //复投金额
        $rows['reply_due_amount'] = $reply_due_amount;
        //人均投资金额
        $rows['avg_due_amount'] = round($due_amount/$total_user);
        //人均首投金额
        $rows['avg_first_amount'] = round($first_due_amount/$rows['first_total_user']);
        //人均二投金额
        $rows['avg_second_amount'] = round($second_due_amount/$rows['second_total_user']);
        //人均复投金额
        $rows['avg_reply_amount'] = round($reply_due_amount/$rows['reply_total_user']);
        //二投率
        $rows['two_rate'] = round($rows['second_total_user']/$total_user);
        //处理时间
        $rows['add_time'] = date("Y-m-d H:i:s",time());
        //更新时间
        $rows['modify_time'] = date("Y-m-d H:i:s",time());
        $StatisticsDailyDueList = $StatisticsDailyDueObj->where(array('dt'=>$dt))->find();
        if($StatisticsDailyDueList){//update
            $StatisticsDailyDueObj->where(array('dt'=>$dt))->save($rows);
        }else{//add
            $StatisticsDailyDueObj->add($rows);
        }
    }

}
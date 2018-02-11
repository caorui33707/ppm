<?php
/**
 * 邀请好友自动发放现金券
 * 2017/08/10 陈俊杰
 */
namespace Task\Controller;
use Think\Controller;

class InviteUserMoneyController extends Controller {



    /*
     * 主函数
     */
    public function main(){
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        $obj = M('UserInviteList');
        $last = $this->getLastYM();
        $sendMonth = $last[0];
        $thisMonth = date('Y-m');

        $cash_title = $last[1].'月邀请好友奖励';
        $hasSend = $obj->where("DATE_FORMAT(add_time,'%Y-%m') = '".$thisMonth."' and type = 3 ")->find();
        $lastMonthTime = $sendMonth."-01 00:00:00";
        $thisTime = date('Y-m-d H:i:s');
        $eTime = date("Y-m-d",strtotime("+2 month"));
        if($hasSend){

        }else{
            $obj->startTrans();

            $invite_sql = "insert into s_user_invite_list (user_id, add_time, modify_time, amount, type) ( select * from (select suil.user_id, '$thisTime' as add_time , '$thisTime' as modify_time,
(case when sum(inv_succ) >= 1000000 then 1000.00
when sum(inv_succ) >= 500000 then 500.00
when sum(inv_succ) >= 200000 then 200.00
when sum(inv_succ) >= 100000 then 100.00
when sum(inv_succ) >= 50000 then 50.00
when sum(inv_succ) >= 10000 then 15.00
else 0.00 end) as amount,
3 as type
from s_investment_detail sudd left join s_user_invite_list suil on suil.invited_user_id = sudd.user_id
where 1 = 1
and suil.type = 1
and DATE_FORMAT(sudd.add_time,'%Y-%m') = DATE_FORMAT('$lastMonthTime', '%Y-%m')
and date_add(suil.register_time, interval 30 day) >= sudd.add_time
group by suil.user_id) aa where aa.amount>0);";
            $invite_insert = M()->execute($invite_sql);

            $cash_sql = "insert into s_user_cash_coupon (title,subtitle,user_id, add_time, expire_time,modify_time, amount, type,status) ( select * from (select '$cash_title' as title,'$cash_title' as sub_title, suil.user_id, '$thisTime' as add_time , '$eTime' as expire_time , '$thisTime' as modify_time,
(case when sum(inv_succ) >= 1000000 then 1000.00
when sum(inv_succ) >= 500000 then 500.00
when sum(inv_succ) >= 200000 then 200.00
when sum(inv_succ) >= 100000 then 100.00
when sum(inv_succ) >= 50000 then 50.00
when sum(inv_succ) >= 10000 then 15.00
else 0.00 end) as amount,
1 as type,
0 as status
from s_investment_detail sudd left join s_user_invite_list suil on suil.invited_user_id = sudd.user_id
where 1 = 1
and suil.type = 1
and DATE_FORMAT(sudd.add_time,'%Y-%m') = DATE_FORMAT('$lastMonthTime', '%Y-%m')
and date_add(suil.register_time, interval 30 day) >= sudd.add_time
group by suil.user_id) aa where aa.amount>0);";

            $cash_insert = M()->execute($cash_sql);

            if($cash_insert && $invite_insert){
                $obj->commit();
            }else{
                $obj->rollback();
            }


        }

    }

    public function getLastYM(){
        $month = date('m');
        if($month == 1){
            return [(date('Y')-1).'-12','12'];
        }else{
            $last_month = sprintf('%02s', $month-1);
            return [date("Y").'-'.$last_month,$month-1];
        }
    }



}
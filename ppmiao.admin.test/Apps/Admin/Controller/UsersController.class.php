<?php
namespace Admin\Controller;

/**
 * 测试环境下用户功能
 * 请勿上传到正式环境后台中
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 17/4/18
 * Time: 下午4:04
 */
class UsersController extends AdminController{
    public function test_delete(){
        $user = I("get.user", 0, "int"); // 项目ID
        $sql[1] = "DELETE FROM s_user_bank where id_no = (select card_no from s_user where id = $user);";
        $sql[2] = "DELETE FROM s_user_bank where user_id = $user;";
        $sql[3] = "DELETE FROM s_user_account where user_id = $user;";
        $sql[4] = "DELETE FROM s_user_wallet_interest where user_id = $user;";
        $sql[5] = "DELETE FROM s_user_wallet_records where user_id = $user;";
        $sql[6] = "DELETE FROM s_recharge_log where user_id = $user;";
        $sql[7] = "DELETE FROM s_investment_detail where user_id = $user;";
        $sql[8] = "DELETE FROM s_user_due_detail where user_id = $user;";
        $sql[9] = "DELETE FROM s_user_redenvelope where user_id = $user;";
        $sql[10] = "DELETE FROM s_user_cash_coupon where user_id = $user;";
        $sql[11] = "DELETE FROM s_user_interest_coupon where user_id = $user;";
        $sql[12] = "DELETE FROM s_user where id = $user;";
        foreach($sql as $s){
            if(M()->execute($s)){
                echo $s.'执行成功'.'<br>';
            }
        }
//        echo '删除成功';
    }


    public function test_delete_func($id){
        if($id){
            $user = $id; // 项目ID
//        $sql[1] = "DELETE FROM s_user_bank where id_no = (select card_no from s_user where id = $user);";
            $sql[2] = "DELETE FROM s_user_bank where user_id = $user;";
            $sql[3] = "DELETE FROM s_user_account where user_id = $user;";
            $sql[4] = "DELETE FROM s_user_wallet_interest where user_id = $user;";
            $sql[5] = "DELETE FROM s_user_wallet_records where user_id = $user;";
            $sql[6] = "DELETE FROM s_recharge_log where user_id = $user;";
            $sql[7] = "DELETE FROM s_investment_detail where user_id = $user;";
            $sql[8] = "DELETE FROM s_user_due_detail where user_id = $user;";
            $sql[9] = "DELETE FROM s_user_redenvelope where user_id = $user;";
            $sql[10] = "DELETE FROM s_user_cash_coupon where user_id = $user;";
            $sql[11] = "DELETE FROM s_user_interest_coupon where user_id = $user;";
            $sql[12] = "DELETE FROM s_user where id = $user;";
            foreach($sql as $s){
                if(M(0)->execute($s)){
                    echo $s.'执行成功'.'<br>';
                }
            }
        }
    }


    public function online_to_test_v1(){
        $mobile = I("get.id", 0, "int"); // 项目ID
        $id = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("select id from s_user where username = $mobile");
        if(!$id){
            echo '找不到该用户';

            echo "<input type='button' value='返回' onclick='window.location.href=\"/admin.php/users/online_to_test\"'>";
            exit;
        }
        $id = $id[0]['id'];


        $online_username = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("select username from s_user where id = $id");


        $online_sql[0] = "select * from s_user_bank where user_id = $id ";
        $online_sql[1] = "select * from s_user_account where user_id = $id;";
        $online_sql[2] = "select * from s_user_wallet_interest where user_id = $id ";
        $online_sql[3] = "select * from s_user_wallet_records where user_id = $id "; //user_bank_id
        $online_sql[4] = "select * from s_recharge_log where user_id = $id "; //project_id
        $online_sql[5] = "select * from s_investment_detail where user_id = $id "; //project_id
        $online_sql[6] = "select * from s_user_due_detail where user_id = $id "; //project_id
        $online_sql[7] = "select * from s_user_redenvelope where user_id = $id "; //project_id
        $online_sql[8] = "select * from s_user_cash_coupon where user_id = $id ";
        $online_sql[9] = "select * from s_user_interest_coupon where user_id = $id "; //project_id
        $online_sql[10] = "select * from s_user where id = $id ";

        $project_id = [];

        $online_sql[11] = "select * from s_project where id in (select project_id from s_user_due_detail where user_id = $id) ;";
        $online_sql[12] = "select * from s_repayment_detail where project_id in (select project_id from s_user_due_detail where user_id = $id) ;";


        $test_model[0] = "UserBank";
        $test_model[1] = "UserAccount";
        $test_model[2] = "UserWalletInterest";
        $test_model[3] = "UserWalletRecords";
        $test_model[4] = "RechargeLog";
        $test_model[5] = "InvestmentDetail";
        $test_model[6] = "UserDueDetail";
        $test_model[7] = "UserRedenvelope";
        $test_model[8] = "UserCashCoupon";
        $test_model[9] = "UserInterestCoupon";
        $test_model[10] = "User";
        $test_model[11] = "Project";
        $test_model[12] = "RepaymentDetail";

        /**
         * 循环获取老数据
         */
        for($i = 0 ; $i <=12 ; $i++){
            $data = M()->db(1)->query($online_sql[$i]);
            $userInfo[$i] = $data;
        }




        /**
         * 查询测试数据库的用户
         */
        $test_user = M('User')->where(['username',$online_username])->find();

        $test_user_id = $test_user['id'];

        /*
         * 删除测试数据库的用户信息
         */
        $this->test_delete_func($test_user_id);



        /**
         * 转移project表并获取新的project id
         */

        foreach($userInfo[11] as $key => $project){
            $old_id = $project['id'];

            $where['title'] = $project['title'];
            $p = M($test_model[11])->where($where)->find();
            if($p){
                $project_id[$old_id] = $p['id'];
            }else{
                unset($project['id']);
                $new_project_id = M($test_model[11])->add($project);
                $project_id[$old_id] = $new_project_id;
            }

        }
        echo '转移数据'.$test_model[11].'表<br>';




        /**
         * 转移repayment_detail表
         */

        foreach($userInfo[12] as $key => $repayment){
            $where['project_id'] = $project_id[$repayment['project_id']];
            $r = M($test_model[12])->where($where)->find();
            if(!$r){
                unset($repayment['id']);
                $repayment['project_id'] = $project_id[$repayment['project_id']];
                M($test_model[12])->add($repayment);
            }

        }
        echo '转移数据'.$test_model[12].'表<br>';


        /*
         * 生成新的用户id
         */
        unset($userInfo[10][0]['id']);
        $new_user_id = M($test_model[10])->add($userInfo[10][0]);

        echo '转移数据'.$test_model[10].'表<br>';
        $bank_id = [];

        /**
         * 生成新的bank_id数组
         */
        foreach($userInfo[0] as $key => $user_bank){
            $old_id = $userInfo[0][$key]['id'];
            unset($userInfo[0][$key]['id']);
            $userInfo[0][$key]['user_id'] = $new_user_id;
            $new_bank_id = M($test_model[0])->add($userInfo[0][$key]);
            $bank_id[$old_id] = $new_bank_id;
        }

        echo '转移数据'.$test_model[0].'表<br>';



        /**
         * 生成除了用户表和银行卡表之外的表的数据
         */
        for($i = 9 ; $i >0 ; $i--){
            if($i == 3){//判断是否表里有bank_id
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $b['user_bank_id'] = $bank_id[$b['user_bank_id']];
                    $res= M($test_model[$i])->add($b);
                }
            }elseif($i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 8 ){//判断是否表里有project_id
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $b['project_id'] = $project_id[$b['project_id']];
                    $res= M($test_model[$i])->add($b);
                }
            }else{
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $res= M($test_model[$i])->add($b);
                }
            }

            echo '转移数据'.$test_model[$i].'表<br>';
        }


        echo "<input type='button' value='返回' onclick='window.location.href=\"/admin.php/users/online_to_test\"'>";
    }


    /**
     *
     */
    public function online_to_test(){


        $html = "
        <html>
            <body>
                <form action='/admin.php/users/online_to_test_v1' method='get'>
                    <input type='text' placeholder='正式库username' name='id'>
                    <input type='submit' value='确认转移'>
                </form>
            </body>
        </html>



        ";
        echo $html;
        exit;




        $id = I("get.id", 0, "int"); // 项目ID
        $online_username = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("select username from s_user where id = $id");

        $online_sql[0] = "select * from s_user_bank where user_id = $id ";
        $online_sql[1] = "select * from s_user_account where user_id = $id;";
        $online_sql[2] = "select * from s_user_wallet_interest where user_id = $id ";
        $online_sql[3] = "select * from s_user_wallet_records where user_id = $id "; //user_bank_id
        $online_sql[4] = "select * from s_recharge_log where user_id = $id "; //project_id
        $online_sql[5] = "select * from s_investment_detail where user_id = $id "; //project_id
        $online_sql[6] = "select * from s_user_due_detail where user_id = $id "; //project_id
        $online_sql[7] = "select * from s_user_redenvelope where user_id = $id "; //project_id
        $online_sql[8] = "select * from s_user_cash_coupon where user_id = $id ";
        $online_sql[9] = "select * from s_user_interest_coupon where user_id = $id "; //project_id
        $online_sql[10] = "select * from s_user where id = $id ";

        $project_id = [];

        $online_sql[11] = "select * from s_project where id in (select project_id from s_user_due_detail where user_id = $id) ;";


        $test_model[0] = "UserBank";
        $test_model[1] = "UserAccount";
        $test_model[2] = "UserWalletInterest";
        $test_model[3] = "UserWalletRecords";
        $test_model[4] = "RechargeLog";
        $test_model[5] = "InvestmentDetail";
        $test_model[6] = "UserDueDetail";
        $test_model[7] = "UserRedenvelope";
        $test_model[8] = "UserCashCoupon";
        $test_model[9] = "UserInterestCoupon";
        $test_model[10] = "User";
        $test_model[11] = "Project";

        /**
         * 循环获取老数据
         */
        for($i = 0 ; $i <=11 ; $i++){
            $data = M()->db(1)->query($online_sql[$i]);
            $userInfo[$i] = $data;
        }




        /**
         * 查询测试数据库的用户
         */
        $test_user = M('User')->where(['username',$online_username])->find();
        $test_user_id = $test_user['id'];

        /*
         * 删除测试数据库的用户信息
         */
        $this->test_delete_func($test_user_id);



        /**
         * 获取标id
         */

        foreach($userInfo[11] as $key => $project){
            $old_id = $project['id'];

            $where['title'] = $project['title'];
            $p = M($test_model[11])->where($where)->find();
            if($p){
                $project_id[$old_id] = $p['id'];
            }else{
                unset($project['id']);
                $new_project_id = M($test_model[11])->add($project);
                $project_id[$old_id] = $new_project_id;
            }

        }
        echo '转移数据'.$test_model[11].'表<br>';

        /*
         * 生成新的用户id
         */
        unset($userInfo[10][0]['id']);
        $new_user_id = M($test_model[10])->add($userInfo[10][0]);

        echo '转移数据'.$test_model[10].'表<br>';
        $bank_id = [];

        /**
         * 生成新的bank_id数组
         */
        foreach($userInfo[0] as $key => $user_bank){
            $old_id = $userInfo[0][$key]['id'];
            unset($userInfo[0][$key]['id']);
            $userInfo[0][$key]['user_id'] = $new_user_id;
            $new_bank_id = M($test_model[0])->add($userInfo[0][$key]);
            $bank_id[$old_id] = $new_bank_id;
        }

        echo '转移数据'.$test_model[0].'表<br>';



        /**
         * 生成除了用户表和银行卡表之外的表的数据
         */
        for($i = 9 ; $i >0 ; $i--){
            if($i == 3){//判断是否表里有bank_id
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $b['user_bank_id'] = $bank_id[$b['user_bank_id']];
                    $res= M($test_model[$i])->add($b);
                }
            }elseif($i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 8 ){//判断是否表里有project_id
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $b['project_id'] = $project_id[$b['project_id']];
                    $res= M($test_model[$i])->add($b);
                }
            }else{
                foreach($userInfo[$i] as $a => $b){
                    unset($b['id']);
                    $b['user_id'] = $new_user_id;
                    $res= M($test_model[$i])->add($b);
                }
            }

            echo '转移数据'.$test_model[$i].'表<br>';
        }
    }


    public function account(){
        $uid = I("get.uid", 0, "int"); // 项目ID
        $data1 = M()->query("SELECT * FROM
(SELECT SUM(`VALUE`) AS walletIn FROM s_user_wallet_records WHERE user_id=$uid AND TYPE=1 AND pay_type IN (3,4,1) AND pay_status=2) AS walletIn,
(SELECT SUM(amount) AS redUsed FROM s_user_redenvelope WHERE user_id=$uid AND STATUS=1) AS redUseds ,
(SELECT SUM(amount) AS projectIn FROM s_recharge_log WHERE user_id=$uid AND STATUS=2 AND TYPE<>3) AS projectIn,
(SELECT SUM(due_interest) AS projectInterest FROM s_user_due_detail WHERE user_id=$uid) AS projectInterest,
(SELECT SUM(interest) AS walletInterest FROM s_user_wallet_interest WHERE user_id=$uid) AS walletInterest,
(SELECT SUM(VALUE) AS withdraw FROM s_user_wallet_records WHERE user_id=$uid AND TYPE=2 AND recharge_no LIKE 'TX%')  AS withdraw,
(SELECT SUM(due_amount)  AS repay FROM s_user_due_detail WHERE user_id=$uid AND STATUS=2 AND from_wallet=0 AND to_wallet=0) AS repay;");

        $a = $data1[0]['walletin']+$data1[0]['redused']+$data1[0]['projectin']+$data1[0]['projectinterest']+$data1[0]['walletinterest']+$data1[0]['withdraw']-$data1[0]['repay'];
        echo 'a:'.$a.' ';


        $data2 = M()->query("SELECT id,wait_amount,wallet_totle FROM s_user_account WHERE user_id=$uid;");

//        var_dump($data2);
        $b= $data2[0]['wait_amount']+$data2[0]['wallet_totle'];

        echo 'b:'.$b.' ';

        $c = $a-$b;
        if($c < 0.01)$c = $c*-1;

        if($c < 0.01)$c = 0;
        echo 'c:'.$c;

    }
    public function account_online($uid){
        if(!$uid){
            $uid = I("get.uid", 0, "int"); // 项目ID
        }
        $data1 = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("SELECT * FROM
(SELECT SUM(`VALUE`) AS walletIn FROM s_user_wallet_records WHERE user_id=$uid AND TYPE=1 AND pay_type IN (3,4,1,5,6) AND pay_status=2) AS walletIn,
(SELECT SUM(amount) AS redUsed FROM s_user_redenvelope WHERE user_id=$uid AND STATUS=1) AS redUseds ,
(SELECT SUM(amount) AS projectIn FROM s_recharge_log WHERE user_id=$uid AND STATUS=2 AND TYPE<>3) AS projectIn,
(SELECT SUM(due_interest) AS projectInterest FROM s_user_due_detail WHERE user_id=$uid) AS projectInterest,
(SELECT SUM(interest) AS walletInterest FROM s_user_wallet_interest WHERE user_id=$uid) AS walletInterest,
(SELECT SUM(VALUE) AS withdraw FROM s_user_wallet_records WHERE user_id=$uid AND TYPE=2 AND recharge_no LIKE 'TX%')  AS withdraw,
(SELECT SUM(due_amount)  AS repay FROM s_user_due_detail WHERE user_id=$uid AND STATUS=2 AND from_wallet=0 AND to_wallet=0) AS repay;");

        $a = $data1[0]['walletin']+$data1[0]['redused']+$data1[0]['projectin']+$data1[0]['projectinterest']+$data1[0]['walletinterest']+$data1[0]['withdraw']-$data1[0]['repay'];

        echo 'ID:'.$uid.'-----';
        echo 'a:'.$a.' ';


        $data2 = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("SELECT id,wait_amount,wallet_totle FROM s_user_account WHERE user_id=$uid;");

//        var_dump($data2);
        $b= $data2[0]['wait_amount']+$data2[0]['wallet_totle'];



        echo 'b:'.$b.' ';

        $c = $a-$b;
        if($c < 0.01)$c = $c*-1;

        if($c < 0.01)$c = 0;
        echo 'c:'.$c.'<br>';
        if($c != 0){
            $arr[] = $uid;
        }

        var_dump($arr);

    }

    public function user_bank_online($uid){
        if(!$uid){
            $uid = I("get.uid", 0, "int"); // 项目ID
        }
        $banks_info = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("SELECT id FROM s_user_bank WHERE user_id=$uid;");
        foreach($banks_info as $key => $val){
            $this->bank_online($val['id']);
        }
    }


    public function bank_online($bank_id){

        if(!$bank_id){
            $bank_id = I("get.bank_id", 0, "int"); // 项目ID
        }
        $bank_info = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("SELECT bank_card_no,lock_money FROM s_user_bank WHERE id=$bank_id;");
        $bank_card_no = $bank_info[0]['bank_card_no'];

        $sql = "select due_amount,due_capital,due_interest,is_new_type,red_amount from s_user_due_detail where status = 1 and card_no = '$bank_card_no' and from_wallet = 0";

        $need_data = M()->db(1)->query($sql);

        $sum = 0;
        foreach($need_data as $key => $val){
            if($val['is_new_type'] == 0){
                $this_sum = $val['due_amount'];
            }else{
                $this_sum = $val['due_capital']-$val['red_amount'];
            }
            $sum = $this_sum+$sum;
        }

        echo 'BankID:'.$bank_id.'-----';
        $c = $sum-$bank_info[0]['lock_money'];
        echo $sum.'<br>';
        echo $bank_info[0]['lock_money'].'<br>';

        if($c < 0.01)$c = $c*-1;

        if($c < 0.01)$c = 0;
        echo $c.'<br>';


    }




    public function recharge_online($uid){
        if(!$uid){
            $uid = I("get.uid", 0, "int"); // 项目ID
        }
        $sql = "SELECT SUM(wallet_money)+SUM(capital_money)-SUM(lock_money) as value FROM s_user_bank WHERE user_id=$uid and has_pay_success = 2";
        $bank_info = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query($sql);
        $bank_value = $bank_info[0]['value'];
//        echo $bank_value.'<br>';
        $sql2 = "SELECT wallet_interest+wallet_product_interest+banks_product_interest+cash_coupon_total+red_coupon_total as value,wallet_totle FROM s_user_account WHERE user_id=$uid;";
        $account_info = M()->db(1)->query($sql2);
        $account_value = $account_info[0]['value'];
//        echo $account_value.'<br>';


        $sql3 = "select sum(r.amount) as value from s_user_due_detail as d left join `s_investment_detail` as i on i.id = d.invest_detail_id left join s_recharge_log as r on r.recharge_no = i.recharge_no where d.user_id = $uid and d.status = 1 and d.from_wallet = 1";
        $due_info = M()->db(1)->query($sql3);
        $due_value = $due_info[0]['value'];
//        echo $due_value.'<br>';


        $val1 = $due_value+$account_info[0]['wallet_totle'];
//        echo 'wallet_totle+recharge_lo.amount '.$val1.'<br>';
        $val2 = $bank_value+$account_value;
//        echo 'wallet_money+capital_money-lock_money  + account '.$val2.'<br>';




        echo 'UserID:'.$uid.'-----';
        $c = $val1-$val2;

        if($c < 0.01)$c = $c*-1;

        if($c < 0.01)$c = 0;
        echo $c.'<br>';

    }






    public function check_wrong_bank_ids(){

        ini_set("memory_limit", "1000M");

        ini_set("max_execution_time", 0);
        $sql = "SELECT DISTINCT(user_id) FROM s_user_wallet_records WHERE add_time > '2017-05-05 00:00:00' and   `type`=2 AND recharge_no LIKE 'TX%' AND user_id>0;";
        $bank_infos = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query($sql);

//        var_dump($bank_infos);
//        exit;

        foreach($bank_infos as $bank_info){
            $this->recharge_online($bank_info['user_id']);
        }
    }


    public function users1(){

        $sql = "select id,username,add_time from s_user where real_name_auth = 0 and add_time > '2017-01-01 00:00:00' and  add_time < '2017-04-21 00:00:00'";

        $users = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query($sql);
    }


    public function users2(){

        $sql = "select due.user_id from `s_user_due_detail` as due left join `s_project` as p on p.id = due.project_id where  due.user_id not in(  select due.user_id from `s_user_due_detail` as due left join `s_project` as p on p.id = due.project_id where p.`new_preferential` = 1 group by due.user_id ) group by due.user_id ";

        $users = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query($sql);
    }

    public function show_phpinfo(){
        phpinfo();
    }


    public function monthly_user_due(){

    }
    public function get_most_day(){
        M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");

        for($day = 1483200000;$day<1490976000;$day = $day+86400){
            $next_day = date('Ymd',$day+86400);
            $this_day = date('Ymd',$day);
            $sql = "select sum(amount) from `s_recharge_log` where DATE_FORMAT(add_time, '%Y%m%d%H') < '$next_day' and  DATE_FORMAT(add_time, '%Y%m%d%H') > '$this_day' and user_id > 0 and status = 2";
            echo M()->db(1)->query($sql).'---'.$this_day.'<br>';
        }
    }
    function do_change($id){
        $online_username = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query("select username from s_user where id = $id");
//        print_r($user);


        $online_sql[0] = "select * from s_user_bank where user_id = $id ";
        $online_sql[1] = "select * from s_user_account where user_id = $id;";
        $online_sql[2] = "select * from s_user_wallet_records where user_id = $id and add_time >='2017-04-19 00:00:00.000000'"; //user_bank_id
        $online_sql[3] = "select * from s_recharge_log where user_id = $id "; //project_id
        $online_sql[4] = "select * from s_user_due_detail where user_id = $id and due_time >='2017-04-19 00:00:00.000000' "; //project_id
        $online_sql[5] = "select * from s_user where id = $id ";


        $test_model[0] = "UserBankCopy";
        $test_model[1] = "UserAccountCopy";
        $test_model[2] = "UserWalletRecordsCopy";
        $test_model[3] = "RechargeLogCopy";
        $test_model[4] = "UserDueDetailCopy";
        $test_model[5] = "UserCopyBak";


        for($i = 0 ; $i <=5 ; $i++){
            $data = M()->db(1)->query($online_sql[$i]);
            $userInfo[$i] = $data;
        }
//        var_dump($userInfo[7]);
//        foreach($userInfo[7] as $a => $b){
//            echo $b['id'];
//            M('UserRedenvelope')->add($b);
//        }

//        var_dump($userInfo[7]);
//        exit;

        $test_user = M('User')->where(['username',$online_username])->find();
//        var_dump($test_user);

        $test_user_id = $test_user['id'];


        $this->test_delete_func($test_user_id);



        for($i = 5 ; $i >=0 ; $i--){

            foreach($userInfo[$i] as $a => $b){
                @M($test_model[$i])->where('id = '.$b['id'])->delete();
                $res= @M($test_model[$i])->add($b);
            }

            echo '转移数据'.$test_model[$i].'表<br>';
        }

//        print_r($userInfo);
//        $users = M()->db(1)->query("select * from s_user where username = '18805813633'");
//        foreach($users as $key => $val){
//            unset($users[$key]['id']);
//        }
//        $userObj = M('User')->addAll($users);
//        var_dump($userObj);
    }




    public function update_to_back_wrong(){
//        $user_id = 85189;

        $bank_id = I("get.bank_id", 0, "int"); // 项目ID
//        $bank_id = 24720;





        $userBankObj = M("UserBankBakTest");
        $userBankRightObj = M("UserBankCopy");
        $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');

        if(!$card_no){
            $card_info = $userBankRightObj->where(array('id'=>$bank_id))->find();
            $card_info['total_money'] = 0;
            $card_info['wait_money'] = 0;
            $card_info['wallet_money'] = 0;
            $card_info['capital_money'] = 0;
            $card_info['lock_money'] = 0;

            $userBankObj->add($card_info);
            $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');
        }



        $userWalletRecordsObj = M("UserWalletRecordsCopy");
        $rechargeLogObj = M("RechargeLogCopy");
        $userDueDetailObj = M("UserDueDetailCopy");



        $all_records = [];


        //1.卡投活期
        $type1_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and pay_status = 2 and type = 1 and pay_type <>0 and pay_type <> 3 ")
            ->select();
        foreach( $type1_results as $key=> $result){




            $_temp = [];
            $_temp['type'] = '1';
            $_temp['amount'] = $result['value'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['bank_id'] = $result['user_bank_id'];

            $all_records[] = $_temp;

        }


        //2.卡投定期
        $type2_results = $rechargeLogObj
            ->where("card_no = '$card_no' and add_time > '2017-04-20 03:00:00.000000' and status = 2 and type <> 3")
            ->select();
        foreach( $type2_results as $key=> $result){


            $_temp = [];
            $_temp['type'] = '2';
            $_temp['amount'] = $result['amount'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;

        }


        //3.1 卡投定期回卡 new_type = 0
        $type3_1_results = $userDueDetailObj
            ->where("card_no = '$card_no' and due_time < '2017-05-02 00:00:00.000000' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 0 and status = 2 ")
            ->select();

        foreach( $type3_1_results as $key=> $result){






            $_temp = [];
            $_temp['type'] = '3.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //3.2 卡投定期回卡 new_type = 1
        $type3_2_results = $userDueDetailObj
            ->where("card_no = '$card_no' and due_time < '2017-05-02 00:00:00.000000' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 1 and status = 2 ")
            ->select();

        foreach( $type3_2_results as $key =>  $result){



            $_temp = [];
            $_temp['type'] = '3.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'];
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //4.1 卡投定期会钱包 new_type = 0

        $type4_1_results = $userDueDetailObj
            ->where("card_no = '$card_no' and due_time < '2017-05-02 00:00:00.000000' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =1 and is_new_type = 0 and status = 2 ")
            ->select();

        foreach( $type4_1_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //4.2 卡投定期会钱包 new_type = 1

        $type4_2_results = $userDueDetailObj
            ->where("card_no = '$card_no' and due_time < '2017-05-02 00:00:00.000000' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =1 and is_new_type = 1 and status = 2 ")
            ->select();

        foreach( $type4_2_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'] ;
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //5 提现

        $type5_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and type = 2 and user_due_detail_id = 0 and user_bank_id <> 0")
            ->select();

        foreach( $type5_results as $key=> $result){


            $user_bank = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$result['user_bank_id']))->find();


            $_temp = [];
            $_temp['type'] = '5';
            $_temp['amount'] = abs($result['value']) ;
            $_temp['add_time'] = $result['add_time'];
            $_temp['wallet_money'] = $user_bank['wallet_money'];
            $_temp['capital_money'] = $user_bank['capital_money'];
            $_temp['lock_money'] = $user_bank['lock_money'];
            $_temp['bank_id'] = $result['user_bank_id'];



            $all_records[] = $_temp;




        }



        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'add_time',       //排序字段
        );



        $arrSort = array();
        foreach($all_records AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $all_records);
        }


        foreach($all_records as $k => $v){


            switch ($v['type'])
            {
                case '1':
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('capital_money', $v['amount']);
                    echo 'method 1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '2':
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('capital_money', $v['amount']);
                    echo 'method 2:'.$userBankObj->getLastSql().'<br>';
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('lock_money', $v['amount']);
                    echo 'method 2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '3.1':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money', $v['amount']); //-total
                    echo 'method 3.1:'.$userBankObj->getLastSql().'<br>';
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('capital_money', $v['amount']); //-total
                    echo 'method 3.1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '3.2':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money',  $v['amount']); //-净值
                    echo 'method 3.2:'.$userBankObj->getLastSql().'<br>';

                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('capital_money',  $v['amount']); //-净值
                    echo 'method 3.2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '4.1':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setInc('wallet_money', $v['amount']);//错误操作 +total
                    echo 'method 4.1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '4.2':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money', $v['amount']);
                    echo 'method 4.2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '5':
                    //获取当前银行卡信息
                    $user_bank = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$bank_id))->find();
                    $enable = $user_bank['wallet_money']+$user_bank['capital_money']-$user_bank['lock_money'];

                    if($v['amount'] <= $user_bank['wallet_money'] ){
                        $wallet_money = $user_bank['wallet_money'] - $v['amount'];

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', $wallet_money);
                        echo 'method 5.1:'.$userBankObj->getLastSql().'<br>';

                    }elseif($v['amount'] >= $enable){

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', 0);
                        echo 'method 5.2:'.$userBankObj->getLastSql().'<br>';
                        $capital_money = $user_bank['lock_money'];
                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('capital_money', $capital_money);
                        echo 'method 5.2:'.$userBankObj->getLastSql().'<br>';
                    }elseif($v['amount'] < $enable && $v['amount'] > $user_bank['wallet_money'] ){

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', 0);
                        echo 'method 5:'.$userBankObj->getLastSql().'<br>';

                        $capital_money = $user_bank['capital_money']-($v['amount']-$user_bank['wallet_money']);
                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('capital_money', $capital_money);
                        echo 'method 5:'.$userBankObj->getLastSql().'<br>';
                    }
                    break;
            }


        }


    }





    public function get_wrong_bank_ids(){

        ini_set("memory_limit", "1000M");

        ini_set("max_execution_time", 0);
        $sql = "select DISTINCT(card_no),b.id as bank_id from `s_user_due_detail` as d left join s_user_bank as b on b.bank_card_no = d.card_no where d.due_time < '2017-05-02 00:00:00.000000' and  d.due_time >= '2017-04-19 00:00:00.000000' and d.`from_wallet` = 0 and d.`to_wallet` =1 and d.is_new_type = 0 and d.status = 2 and d.user_id > 0  and b.has_pay_success = 2 order by b.id ASC";
        $bank_infos = M()->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online")->query($sql);

//        var_dump($bank_infos);
//        exit;

        foreach($bank_infos as $bank_info){
            $this->update_to_back_true($bank_info['bank_id']);
        }
    }

    public function get_one(){

        $id = I("get.bank_id", 0, "int"); // 项目ID{
        $this->update_to_back_true($id);
    }

    public function update_to_back_true($bank_id){



        //修改的20号备份数据，修改完成后赋值到上一个Obj
        $userBankObj = M("UserBankBakTest");
        //用于查询的正式user_bank数据库
        $userBankRightObj = M("UserBank")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式user_wallet_records数据库
        $userWalletRecordsObj = M("UserWalletRecords")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式recharge_log数据库
        $rechargeLogObj = M("RechargeLog")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式user_due_detail数据库
        $userDueDetailObj = M("UserDueDetail")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");

        //待修改的正式数据库
        $userBankUpdateObj = M("UserBank")->db(2,"mysql://ppmiao:dfsd093lke0935nSDFD93323@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");





        $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');

        //查询备份数据库中是否有这条记录,如果没有就新增一条
        if(!$card_no){
            $card_info = $userBankRightObj->where(array('id'=>$bank_id))->find();
            $card_info['total_money'] = 0;
            $card_info['wait_money'] = 0;
            $card_info['wallet_money'] = 0;
            $card_info['capital_money'] = 0;
            $card_info['lock_money'] = 0;

            $userBankObj->add($card_info);
            $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');
        }



        $all_records = [];


        //1.卡投活期
        $type1_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and pay_status = 2 and type = 1 and pay_type <>0 and pay_type <> 3 ")
            ->select();
        foreach( $type1_results as $key=> $result){




            $_temp = [];
            $_temp['type'] = '1';
            $_temp['amount'] = $result['value'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['bank_id'] = $result['user_bank_id'];

            $all_records[] = $_temp;

        }


        //2.卡投定期
        $type2_results = $rechargeLogObj
            ->where("card_no = '$card_no' and add_time > '2017-04-20 03:00:00.000000' and status = 2 and type <> 3")
            ->select();
        foreach( $type2_results as $key=> $result){


            $_temp = [];
            $_temp['type'] = '2';
            $_temp['amount'] = $result['amount'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;

        }


        //3.1 卡投定期回卡 new_type = 0
        $type3_1_results = $userDueDetailObj
            ->where("card_no = '$card_no'  and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 0 and status = 2 ")
            ->select();

        foreach( $type3_1_results as $key=> $result){






            $_temp = [];
            $_temp['type'] = '3.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //3.2 卡投定期回卡 new_type = 1
        $type3_2_results = $userDueDetailObj
            ->where("card_no = '$card_no' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 1 and status = 2 ")
            ->select();

        foreach( $type3_2_results as $key =>  $result){



            $_temp = [];
            $_temp['type'] = '3.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'];
            $_temp['add_time'] = date('Y-m-d H:i:s',strtotime($result['due_time'])+86400 ).'.000000';
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //4.1 卡投定期会钱包 new_type = 0

        $type4_1_results = $userDueDetailObj
            ->field('s_user_due_detail.*,s_user_wallet_records.add_time as get_true_time')
            ->join('s_user_wallet_records on s_user_wallet_records.user_due_detail_id = s_user_due_detail.id')
            ->where("s_user_due_detail.card_no = '$card_no' and  s_user_due_detail.due_time >= '2017-04-19 00:00:00.000000' and s_user_due_detail.from_wallet = 0 and s_user_due_detail.to_wallet =1 and s_user_due_detail.is_new_type = 0 and s_user_due_detail.status = 2 and s_user_wallet_records.type=1")
            ->select();

        foreach( $type4_1_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['get_true_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //4.2 卡投定期会钱包 new_type = 1

        $type4_2_results = $userDueDetailObj
            ->field('s_user_due_detail.*,s_user_wallet_records.add_time as get_true_time')
            ->join('s_user_wallet_records on s_user_wallet_records.user_due_detail_id = s_user_due_detail.id')
            ->where("s_user_due_detail.card_no = '$card_no' and  s_user_due_detail.due_time >= '2017-04-19 00:00:00.000000' and s_user_due_detail.from_wallet = 0 and s_user_due_detail.to_wallet =1 and s_user_due_detail.is_new_type = 1 and s_user_due_detail.status = 2 and s_user_wallet_records.type=1 ")
            ->select();

        foreach( $type4_2_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'] ;
            $_temp['add_time'] = $result['get_true_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //5 提现

        $type5_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and type = 2 and user_due_detail_id = 0 and user_bank_id <> 0")
            ->select();

        foreach( $type5_results as $key=> $result){


            $user_bank = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$result['user_bank_id']))->find();


            $_temp = [];
            $_temp['type'] = '5';
            $_temp['amount'] = abs($result['value']) ;
            $_temp['add_time'] = $result['add_time'];
            $_temp['wallet_money'] = $user_bank['wallet_money'];
            $_temp['capital_money'] = $user_bank['capital_money'];
            $_temp['lock_money'] = $user_bank['lock_money'];
            $_temp['bank_id'] = $result['user_bank_id'];



            $all_records[] = $_temp;




        }


        /*********** start 按照时间排序 ***********/
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'add_time',       //排序字段
        );



        $arrSort = array();
        foreach($all_records AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $all_records);
        }
        /*********** end 按照时间排序 ***********/



//        var_dump($all_records);
//
//        exit;
        foreach($all_records as $k => $v){

            $userBankObj->startTrans();

            switch ($v['type'])
            {
                case '1':
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('capital_money', $v['amount']);
                    echo 'method 1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '2':
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('capital_money', $v['amount']);
                    echo 'method 2:'.$userBankObj->getLastSql().'<br>';
                    $userBankObj->where(array('id'=>$bank_id))
                        ->setInc('lock_money', $v['amount']);
                    echo 'method 2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '3.1':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money', $v['amount']); //-total
                    echo 'method 3.1:'.$userBankObj->getLastSql().'<br>';
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('capital_money', $v['amount']); //-total
                    echo 'method 3.1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '3.2':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money',  $v['amount']); //-净值
                    echo 'method 3.2:'.$userBankObj->getLastSql().'<br>';

                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('capital_money',  $v['amount']); //-净值
                    echo 'method 3.2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '4.1':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money', $v['amount']);//正确操作 -total
                    echo 'method 4.1:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '4.2':
                    $userBankObj->where(array('id'=>$bank_id,'has_pay_success'=>2))
                        ->setDec('lock_money', $v['amount']);
                    echo 'method 4.2:'.$userBankObj->getLastSql().'<br>';
                    break;
                case '5':
                    //获取当前银行卡信息
                    $user_bank = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$bank_id))->find();
                    $enable = $user_bank['wallet_money']+$user_bank['capital_money']-$user_bank['lock_money'];

                    if($v['amount'] <= $user_bank['wallet_money'] ){
                        $wallet_money = $user_bank['wallet_money'] - $v['amount'];

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', $wallet_money);
                        echo 'method 5.1:'.$userBankObj->getLastSql().'<br>';

                    }elseif($v['amount'] >= $enable){

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', 0);
                        echo 'method 5.2:'.$userBankObj->getLastSql().'<br>';
                        $capital_money = $user_bank['lock_money'];
                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('capital_money', $capital_money);
                        echo 'method 5.2:'.$userBankObj->getLastSql().'<br>';
                    }elseif($v['amount'] < $enable && $v['amount'] > $user_bank['wallet_money'] ){

                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('wallet_money', 0);
                        echo 'method 5:'.$userBankObj->getLastSql().'<br>';

                        $capital_money = $user_bank['capital_money']-($v['amount']-$user_bank['wallet_money']);
                        $userBankObj->where(array('id'=>$bank_id))
                            ->setField('capital_money', $capital_money);
                        echo 'method 5:'.$userBankObj->getLastSql().'<br>';
                    }
                    break;
            }
            $userBankObj->where(array('id'=>$bank_id))
                ->setField('update_status', 1);

            $userBankObj->commit();



        }

//        $user_bank_updated = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$bank_id))->find();
//
//        $userBankUpdateObj->startTrans();
//
//
//        echo '-----------------------------------------------------------------------------------------------------------------------------------------------<br>';
//        echo '<br>';
//
//
//
//
//        $userBankUpdateObj->where(array('id'=>$bank_id))
//            ->setField('wallet_money', $user_bank_updated['wallet_money']);
////        echo '正式库操作:'.$userBankUpdateObj->getLastSql().'<br>';
//
//        $userBankUpdateObj->where(array('id'=>$bank_id))
//            ->setField('capital_money', $user_bank_updated['capital_money']);
////        echo '正式库操作:'.$userBankUpdateObj->getLastSql().'<br>';
//
//        $userBankUpdateObj->where(array('id'=>$bank_id))
//            ->setField('lock_money', $user_bank_updated['lock_money']);
////        echo '正式库操作:'.$userBankUpdateObj->getLastSql().'<br>';
//
//
//
//        echo '<br>';
//        echo '-----------------------------------------------------------------------------------------------------------------------------------------------<br>';
//
//
//        $userBankUpdateObj->commit();



    }
    public function update_to_back_show($bank_id){



        //修改的20号备份数据，修改完成后赋值到上一个Obj
        $userBankObj = M("UserBankBakTest");
        //用于查询的正式user_bank数据库
        $userBankRightObj = M("UserBank")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式user_wallet_records数据库
        $userWalletRecordsObj = M("UserWalletRecords")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式recharge_log数据库
        $rechargeLogObj = M("RechargeLog")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");
        //用于查询的正式user_due_detail数据库
        $userDueDetailObj = M("UserDueDetail")->db(1,"mysql://ppmiao_online:knfa04yF5@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");

        //待修改的正式数据库
        $userBankUpdateObj = M("UserBank")->db(2,"mysql://ppmiao:dfsd093lke0935nSDFD93323@rdsx68knfa04yf50mj51.mysql.rds.aliyuncs.com:3306/ppmiao_online");





        $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');

        //查询备份数据库中是否有这条记录,如果没有就新增一条
        if(!$card_no){
            $card_info = $userBankRightObj->where(array('id'=>$bank_id))->find();
            $card_info['total_money'] = 0;
            $card_info['wait_money'] = 0;
            $card_info['wallet_money'] = 0;
            $card_info['capital_money'] = 0;
            $card_info['lock_money'] = 0;

            $userBankObj->add($card_info);
            $card_no= $userBankObj->where(array('id'=>$bank_id))->getField('bank_card_no');
        }



        $all_records = [];


        //1.卡投活期
        $type1_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and pay_status = 2 and type = 1 and pay_type <>0 and pay_type <> 3 ")
            ->select();
        foreach( $type1_results as $key=> $result){




            $_temp = [];
            $_temp['type'] = '1';
            $_temp['amount'] = $result['value'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['bank_id'] = $result['user_bank_id'];

            $all_records[] = $_temp;

        }


        //2.卡投定期
        $type2_results = $rechargeLogObj
            ->where("card_no = '$card_no' and add_time > '2017-04-20 03:00:00.000000' and status = 2 and type <> 3")
            ->select();
        foreach( $type2_results as $key=> $result){


            $_temp = [];
            $_temp['type'] = '2';
            $_temp['amount'] = $result['amount'];
            $_temp['add_time'] = $result['add_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;

        }


        //3.1 卡投定期回卡 new_type = 0
        $type3_1_results = $userDueDetailObj
            ->where("card_no = '$card_no'  and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 0 and status = 2 ")
            ->select();

        foreach( $type3_1_results as $key=> $result){






            $_temp = [];
            $_temp['type'] = '3.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['due_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //3.2 卡投定期回卡 new_type = 1
        $type3_2_results = $userDueDetailObj
            ->where("card_no = '$card_no' and  due_time >= '2017-04-19 00:00:00.000000' and from_wallet = 0 and to_wallet =0 and is_new_type = 1 and status = 2 ")
            ->select();

        foreach( $type3_2_results as $key =>  $result){



            $_temp = [];
            $_temp['type'] = '3.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'];
            $_temp['add_time'] = date('Y-m-d H:i:s',strtotime($result['due_time'])+86400 ).'.000000';
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //4.1 卡投定期会钱包 new_type = 0

        $type4_1_results = $userDueDetailObj
            ->field('s_user_due_detail.*,s_user_wallet_records.add_time as get_true_time')
            ->join('s_user_wallet_records on s_user_wallet_records.user_due_detail_id = s_user_due_detail.id')
            ->where("s_user_due_detail.card_no = '$card_no' and  s_user_due_detail.due_time >= '2017-04-19 00:00:00.000000' and s_user_due_detail.from_wallet = 0 and s_user_due_detail.to_wallet =1 and s_user_due_detail.is_new_type = 0 and s_user_due_detail.status = 2 and s_user_wallet_records.type=1")
            ->select();

        foreach( $type4_1_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.1';
            $_temp['amount'] = $result['due_amount'];
            $_temp['add_time'] = $result['get_true_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }

        //4.2 卡投定期会钱包 new_type = 1

        $type4_2_results = $userDueDetailObj
            ->field('s_user_due_detail.*,s_user_wallet_records.add_time as get_true_time')
            ->join('s_user_wallet_records on s_user_wallet_records.user_due_detail_id = s_user_due_detail.id')
            ->where("s_user_due_detail.card_no = '$card_no' and  s_user_due_detail.due_time >= '2017-04-19 00:00:00.000000' and s_user_due_detail.from_wallet = 0 and s_user_due_detail.to_wallet =1 and s_user_due_detail.is_new_type = 1 and s_user_due_detail.status = 2 and s_user_wallet_records.type=1 ")
            ->select();

        foreach( $type4_2_results as $key=> $result){



            $_temp = [];
            $_temp['type'] = '4.2';
            $_temp['amount'] = $result['due_capital'] - $result['red_amount'] ;
            $_temp['add_time'] = $result['get_true_time'];
            $_temp['card_no'] = $result['card_no'];

            $all_records[] = $_temp;



        }


        //5 提现

        $type5_results = $userWalletRecordsObj
            ->where("user_bank_id = '$bank_id' and add_time > '2017-04-20 03:00:00.000000' and type = 2 and user_due_detail_id = 0 and user_bank_id <> 0")
            ->select();

        foreach( $type5_results as $key=> $result){


            $user_bank = $userBankObj->field('wallet_money,capital_money,lock_money')->where(array('id'=>$result['user_bank_id']))->find();


            $_temp = [];
            $_temp['type'] = '5';
            $_temp['amount'] = abs($result['value']) ;
            $_temp['add_time'] = $result['add_time'];
            $_temp['wallet_money'] = $user_bank['wallet_money'];
            $_temp['capital_money'] = $user_bank['capital_money'];
            $_temp['lock_money'] = $user_bank['lock_money'];
            $_temp['bank_id'] = $result['user_bank_id'];



            $all_records[] = $_temp;




        }


        /*********** start 按照时间排序 ***********/
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'add_time',       //排序字段
        );



        $arrSort = array();
        foreach($all_records AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $all_records);
        }
        /*********** end 按照时间排序 ***********/




//        var_dump($type1_results);
//        var_dump($type2_results);
//        var_dump($type3_1_results);
//        var_dump($type3_2_results);
//        var_dump($type4_1_results);
//        var_dump($type4_2_results);
//        var_dump($type5_results);

        var_dump($all_records);
//
//        exit;



    }

}
<?php
/**
 * 定时处理任务-零钱喵计息
 */
namespace Task\Controller;
use Think\Controller;

class TaskController extends Controller {

    /**
     * 钱包每日计息处理入口(T+0)
     */
    public function interest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);

		$taskInterestObj = M('TaskInterest');

		//第一步 筛选需要审核的转入或者转出的订单
		$chooseWalletIds = $this->chooseWalletRecords();

		//第二步  批量审核转入或者转出的订单
		$batch_audit_flag = $this->batch_audit_amount($chooseWalletIds);
		
		if($batch_audit_flag == 1){

            //第三步 检查当前是否还有未操作的转入/转出数据
            $check_flg_arr = $this->check_noperate_amount();
            if($check_flg_arr){
                if($check_flg_arr['status'] == 1){//还有用户未处理完的转入/转出记录
                    $data = array(
                        'type'=>3,
                        'add_time'=>date('Y-m-d H:i:s',time()),
                        'desc'=>'还有用户未处理完的转入/转出记录',
                        'operated_user'=>$check_flg_arr['user_str'],
                    );
                    $taskInterestObj->add($data);
                }
                //第四步 批量计息钱包每日的利息(T+1)
                $do_interest_flg = $this->batch_do_interest($check_flg_arr['user_str']);

                if($do_interest_flg == 4 || $do_interest_flg == 5){//success
                    $data = array(
                        'type'=>1,
                        'add_time'=>date('Y-m-d H:i:s',time()),
                        'desc'=>'计息成功'
                    );
                    $taskInterestObj->add($data);
                }
            }
		}
       
    }
    /**
     * 筛选需要审核的转入或者转出的订单
     */
    public function chooseWalletRecords(){

        $start_time = date('Y-m-d', time());//当前日期

        $befor_yestoday = date('Y-m-d', strtotime('-1 days', strtotime($start_time))); // 指定日期前一日
        $befoe_befor_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($start_time))); // 指定日期前前一日

        $cond[] = "(type=1 and pay_status=2 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转入
        $cond[] = "(type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$befoe_befor_yestoday." 15:00:01.000000' and add_time<='".$befor_yestoday." 15:00:00.999000')"; // 转出（提现）
        $cond[] = "(type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转出（购买产品）
        $conditions = implode(' or ', $cond);
        $conditions = "(".$conditions.") and enable_interest=0";


        $userWalletRecordsObj = M('UserWalletRecords');

        $counts = $userWalletRecordsObj->where($conditions)->count();

        $list = $userWalletRecordsObj->field('id')->where($conditions)->order('add_time asc')->select();
        $choose_id_str='';
        $return_choose_id='';
        if($list){
            foreach($list as $k=>$v){
                $choose_id_str.=','.$v['id'];
            }
            if($choose_id_str){
                $return_choose_id = substr($choose_id_str,1);
            }
        }
        if($return_choose_id){

            return $return_choose_id;
        }
    }
    /**
     * 批量审核转入或者转出的订单
     */
    public function batch_audit_amount($ids){

        $startTime = date('Y-m-d', time());

        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($startTime)));

        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');
		$taskInterestObj = M('TaskInterest');
		$userObj = M('User');

        if(!$ids) return 1;
        $list = $userWalletRecordsObj->where("id in (".$ids.") and enable_interest=0")->select();
        if(!$list) return 1;

        $errMsg = ''; // 错误消息
        foreach($list as $key => $val){
            if($val['add_time'] > $yestoday.' 23:59:59.999000'){ // 时间不满足,无法确认份额
                continue;
            }else{
                if($val['type'] == 1){ // 转入
                    $userWalletRecordsObj->startTrans();
                    if($userAccountObj->where(array('user_id'=>$val['user_id']))->setInc('wallet_enable_interest', $val['value'])){
                        if(!$userWalletRecordsObj->where(array('id'=>$val['id'],'enable_interest'=>0))->save(array('enable_interest'=>1,'modify_time'=>date('Y-m-d H:i:s',time()).'.'.getMillisecond().'000'))) {
                            $userWalletRecordsObj->rollback();
                        }else{
                            $userWalletRecordsObj->commit();
                        }
                    }
                }else if($val['type'] == 2){ // 转出
                    $value = abs($val['value']); // 转出金额
                    $userInfo = $userAccountObj->where(array('user_id'=>$val['user_id']))->find(); // 用户账户信息
                    if($value <= $userInfo['wallet_enable_interest']){
                        $userWalletRecordsObj->startTrans();
                        if($userAccountObj->where(array('user_id'=>$val['user_id']))->setDec('wallet_enable_interest', $value)){
                            if(!$userWalletRecordsObj->where(array('id'=>$val['id'],'enable_interest'=>0))->save(array('enable_interest'=>1))) {
                                $userWalletRecordsObj->rollback();
                            }else{
                                $userWalletRecordsObj->commit();
                            }
                        }else{
                            $userWalletRecordsObj->commit();
                        }
                    }else{//记录转出异常的数据
						$single_user_info = $userObj->where(array('id'=>$val['user_id']))->find();//用户信息
						$data = array(
							'username'=>$single_user_info['username'],
							'real_name'=>$single_user_info['real_name'],
							'type'=>2,
							'add_time'=>date('Y-m-d H:i:s',time()),
                            'desc'=>'转出金额大于可计息金额,数据异常,请检查'
						);	
						$taskInterestObj->add($data);
					}
                }
            }
        }
		return 1;
    }
    /**
     * 检查当前是否还有未操作的转入/转出数据
     */
    public function check_noperate_amount(){

        $userWalletRecordsObj = M("UserWalletRecords");

        $datetime = date("Y-m-d",strtotime('-1 days',time()));

        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($datetime)));

        // 检查当前是否还有未操作的转入/转出数据
        $cond2[] = "(type=1 and pay_status=2 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转入
        $cond2[] = "(type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yestoday." 15:00:01.000000' and add_time<='".$datetime." 15:00:00.999000')"; // 转出（提现）
        $cond2[] = "(type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转出（购买产品）
        $conditions2 = implode(' or ', $cond2);
        $conditions2 = "(".$conditions2.") and enable_interest=0";
        $no_operate_userRecords = $userWalletRecordsObj->where($conditions2)->getField('user_id',true);
        if($no_operate_userRecords){//还有用户未处理完的转入/转出记录
            $user_str_list = implode(',',array_unique($no_operate_userRecords));
            return array(
                'status'=>1,
                'user_str'=>$user_str_list,
            );//fail

        }else{//审核成功了所有转入/转出的记录
            return array(
                'status'=>2,
                'user_str'=>'',
            );//success
        }
    }
    /***
     * 批量计息钱包每日的利息(T+0)
     */
    public function batch_do_interest($no_operated_user=''){

        $datetime = date("Y-m-d",strtotime('-1 days',time()));//昨天日期
        $default_rate = 6.35;//默认利率

        $userAccountObj = M('UserAccount');
        $userWalletInterestObj = M('UserWalletInterest');
        $userWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $userWalletRecordsObj = M("UserWalletRecords");

        // 检查当天是否已确定利率
        $userWalletAnnualizedRate = $userWalletAnnualizedRateObj->where(array('add_time'=>$datetime))->find();
        if(!$userWalletAnnualizedRate){// default rate
			$rate = $default_rate;
		}else{//set rate
			$rate = $userWalletAnnualizedRate['rate'];
		}
        if($no_operated_user){
            $cond[] = "user_id not in(".$no_operated_user.")";
        }
        $cond[] = "wallet_enable_interest>0";
        $cond[] = "wallet_last_interest_time<'".$datetime." 00:00:00.000000'";
        $conditions = implode(' and ', $cond);
        $list = $userAccountObj->where($conditions)->select();
        if(!$list){//没有任何可处理记录

			return 4;
		}
        $rate = round($rate/100, 4); // 利率
        $time = $datetime.' '.date('H:i:s', time()).'.'.getMillisecond().'000';
        $rows = array(
            'interest_rate' => $rate,
            'interest_time' => $time,
            'add_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
        );
        foreach($list as $key => $val){

            $userAccountObj->startTrans();
            $interest = round($val['wallet_enable_interest']*$rate/365, 4); // 该日所得利息(四舍五入小数点后4位)

            $rows['user_id'] = $val['user_id'];
            $rows['interest_capital'] = $val['wallet_enable_interest'];
            $rows['interest'] = $interest;

            if($userWalletInterestObj->add($rows)){

                $sql = "update s_user_account set wallet_interest=wallet_interest+".$interest.",wallet_interest_totle=wallet_interest_totle+".$interest;
                $sql.= ",wallet_totle=wallet_totle+".$interest.",wallet_enable_interest=wallet_enable_interest+".$interest.",wallet_last_interest_time='".$time.' '.date('H:i:s', time())."' ";
                $sql.= "where user_id=".$val['user_id'];

                if($userAccountObj->execute($sql)){
                    $userAccountObj->commit();
                }else{
                    $userAccountObj->rollback();
                }
            }else{
                $userAccountObj->rollback();
            }
        }
		return 5;
    }
    
    
    
    
    //邀请报表
    public function count_invite_report(){
    
        $userObj = M('user');
        $userInviteObj = M('userInviteList');
        $userRedenvelopeObj = M('userRedenvelope');
        $rechargeLogObj = M('rechargeLog');
        $userDueDetailObj = M('userDueDetail');
    
        $list = M('userInviteList')->field('user_id')->where("add_time>='2017-07-01'")->group('user_id')->select();
    
        foreach ($list as $val){
    
            unset($dd);
    
            $user_info = $userObj->field('real_name,username')->where('id='.$val['user_id'])->find();
            //邀请列表
            $invite_list =  M('userInviteList')->field('invited_user_id')->where("user_id = ".$val['user_id']." and add_time>='2017-07-01' and type=1")->select();
    
            $_invite_total_amt = 0;
            foreach ($invite_list as $value){
                $amt = $userDueDetailObj->where('user_id='.$value['invited_user_id'])->sum('due_capital');
                if($amt>0){
                    $_invite_total_amt += $amt;
                }
            }
    
            $dd['invite_total_amt'] = $_invite_total_amt;
    
            $dd['user_id'] = $val['user_id'];
            $dd['real_name'] = $user_info['real_name'];
            $dd['mobile'] = $user_info['username'];
            $dd['redbag_count'] = $dd['invite_count'] = count($invite_list);
            $dd['invite_invest_count'] = M('userInviteList')->where("user_id = ".$val['user_id']." and add_time>='2017-07-01' and type = 0 and first_invest_amount >0")->count();
            $dd['award_cash'] = M('userInviteList')->where("user_id = ".$val['user_id']." and add_time>='2017-07-01' and type in(2,3)")->sum('amount');
            
            if(!$dd['award_cash']) {
                $dd['award_cash'] = 0;
            }
            
            $_redbag_invest_amt = 0;
            $_redbag_use_count = 0;
            $red_bag_list = M('userRedenvelope')->field('user_id,recharge_no,amount,status')->where('user_id='.$val['user_id']. " and title = '好友邀请红包'")->select();
    
            foreach ($red_bag_list as $v){
    
                if($v['recharge_no'] && $v['status'] == 1){
                    $_redbag_use_count +=1;
                    $_amt = $rechargeLogObj->where("user_id=".$v['user_id']." and status = 2 and recharge_no='".$v['recharge_no']."'")->getField('amount');
    
                    if($_amt>0){
                        $_redbag_invest_amt += $_amt;
                    }
                }
            }
            $dd['redbag_invest_amt'] = $_redbag_invest_amt;
            $dd['redbag_use_count'] = $_redbag_use_count;
    
            $dd['count_date'] = date("Y-m-d");
            $dd['create_time'] = time();
            M('userInviteReport')->add($dd);
        }
    
    }
    
    //更新banner上下架状态
    public function update_banner_status(){
        $time = date("Y-m-d H:i:00");
        //上架
        $conds = "position=3 and is_delete=0 and status in(1) and start_time<='$time'";    // and is_activity=1 去掉是否是活动
        $sql = "update s_advertisement set status=2,edit_time='$time' where $conds";
        M()->execute($sql);

        //下架
        $conds = "position=3 and is_delete=0  and status=2 and end_time<='$time'";    // and is_activity=1 去掉是否是活动
        $sql = "update s_advertisement set status=3,edit_time='$time' where $conds";        
        M()->execute($sql);
    }

    //更新产品公告上下架状态
    public function update_notic_status(){
        $time = date("Y-m-d H:i:00");
        //上架
        $conds = "position=6 and is_delete=0  and status in(1) and start_time<='$time' and end_time >'$time'";
        $sql = "update s_advertisement set status=2,edit_time='$time' where $conds";
        M()->execute($sql);

        //下架
        $conds = "position=6 and is_delete=0  and status=2 and end_time<='$time'";
        $sql = "update s_advertisement set status=3,edit_time='$time' where $conds";
        M()->execute($sql);
    }

    //更新启动页上下架状态
    public function update_page_status(){
        $time = date("Y-m-d H:i:00");
        //上架
        $conds = "position=2 and is_delete=0 and status in(1) and start_time<='$time' and end_time >'$time' ";
        $sql = "update s_advertisement set status=2,edit_time='$time' where $conds";
        M()->execute($sql);
        
        //下架
        $conds = "position=2 and is_delete=0  and status=2 and end_time<='$time'";
        $sql = "update s_advertisement set status=3,edit_time='$time' where $conds";
        M()->execute($sql);
    }

    //更新导航图标上下架状态
    public function update_adv_icon_status(){
        $time = date("Y-m-d H:i:00");
        //上架
        $conds = " is_delete=0 and status in(1) and start_time<='$time' and end_time >'$time' ";
        $sql = "update s_adv_icon set status=2,edit_time='$time' where $conds";
        M()->execute($sql);

        //下架
        $conds = " is_delete=0  and status=2 and end_time<='$time'";
        $sql = "update s_adv_icon set status=3,edit_time='$time' where $conds";
        M()->execute($sql);
    }
    
    
}
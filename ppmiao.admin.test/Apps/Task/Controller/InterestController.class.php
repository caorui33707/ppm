<?php

/*
 *  每天计息
 */
namespace Task\Controller;
use Think\Controller;

class InterestController extends Controller {
    
    /**
     * 钱包每日计息处理入口(T+0)
     */
    public function run_interest(){
        
        ini_set("memory_limit", "1000M");
        
        ini_set("max_execution_time", 0);
        
        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');
        $userObj = M('User');
        $taskInterestObj = M('TaskInterest');
        
        
        $today = date("Y-m-d");        
        M('walletInterestLog')->startTrans();
        $log = M('WalletInterestLog')->lock(true)->where(array('create_time'=>$today))->find();
        if($log){
            echo $today.' 已经计息过了...';
            M('walletInterestLog')->commit();
            return;
        }
        $dd['create_time'] = $today;
        $dd['status'] = 0;
        $dd['start_time'] = date('Y-m-d H:i:s').'.'.getMillisecond().'000';        
        $rId = M('walletInterestLog')->add($dd);        
        M('WalletInterestLog')->commit();
        
        
        $end_time = date("Y-m-d", strtotime("-1 day"));
        
        $current_date = date("Y-m-d 00:00:00");
        
        //$cond[] = "(type=1 and pay_status=2 and add_time >='".$end_time." 00:00:00.000000' and add_time<='" . $end_time . " 23:59:59.999000')";
        
        // 转入
        $cond[] = "(type=1 and pay_status=2 and add_time<='" . $end_time . " 23:59:59.999000')";
        
        // 转出（提现）
        $cond[] = "(user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and status = 1 and modify_time<'" . $current_date . "')";
        
        //$cond[] = "(type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$end_time." 00:00:00.000000' and add_time<'" . $end_time . " 15:00:00.000000')";
        
        // 转出（购买产品）
        $cond[] = "(user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$end_time." 00:00:00.000000' and add_time<'" . $end_time . " 15:00:00.000000' )";
        
        $conditions = implode(' or ', $cond);
        
        $conditions = "(" . $conditions . ") and enable_interest=0";
        
        $list = $userWalletRecordsObj->field('id,type,user_id,value')->where($conditions)->order('add_time asc')->select();
        
        if($list) {
            
            foreach ($list as $val) {
                // 转入
                if ($val['type'] == 1) {
                    
                    $userWalletRecordsObj->startTrans();
                    
                    if ($userAccountObj->where(array('user_id' => $val['user_id']))->setInc('wallet_enable_interest', $val['value'])) {
                        
                        if (! $userWalletRecordsObj->where(array('id' => $val['id'],'enable_interest' => 0 ))
                                    ->save(array('enable_interest' => 1,'modify_time' => date('Y-m-d H:i:s', time()) . '.' . getMillisecond() . '000'))) {
                                        
                            $userWalletRecordsObj->rollback();
                        } else {
                            $userWalletRecordsObj->commit();
                        }
                    }
                } else if ($val['type'] == 2) {
                    // 转出金额
                    $value = abs($val['value']);
                    $userInfo = $userAccountObj->where(array('user_id' => $val['user_id']))->find();
                    
                    if ($value <= $userInfo['wallet_enable_interest']) {
                        
                        $userWalletRecordsObj->startTrans();
                        
                        if ($userAccountObj->where(array(
                                'user_id' => $val['user_id']))->setDec('wallet_enable_interest', $value)) {
                                
                            if (! $userWalletRecordsObj->where(array('id' => $val['id'],'enable_interest' => 0))->save(array( 'enable_interest' => 1,'modify_time'=>date('Y-m-d H:i:s', time())))) {
                                $userWalletRecordsObj->rollback();
                            } else {
                                $userWalletRecordsObj->commit();
                            }
                        } else {
                            $userWalletRecordsObj->commit();
                        }
                    } else {
                        // 记录转出异常的数据
                        $single_user_info = $userObj->where(array('id' => $val['user_id']))->find();
                        $err_amount = ($value - $userInfo['wallet_enable_interest']);
                        $data = array(
                            'username' => $single_user_info['username'],
                            'real_name' => $single_user_info['real_name'],
                            'type' => 2,
                            'add_time' => date('Y-m-d H:i:s', time()),
                            'desc' => '转出金额大于可计息金额'.$err_amount.',数据异常,请检查'
                        );
                        $taskInterestObj->add($data);
                    }
                }
            }
        }
        
        //默认利率
        $default_rate = 6.35;
        $userWalletInterestObj = M('UserWalletInterest');
        $userWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        
        // 检查当天是否已确定利率
        $userWalletAnnualizedRate = $userWalletAnnualizedRateObj->where(array('add_time'=>$end_time))->find();
        
        if (! $userWalletAnnualizedRate) { 
            $rate = $default_rate;
        } else {
            $rate = $userWalletAnnualizedRate['rate'];
        }
        
        $cond = null;
        $conditions = null;
        
        $today = date("Y-m-d");
        
        $cond[] = "wallet_enable_interest>0";
        $cond[] = "wallet_last_interest_time<'" . $today . " 00:00:00.000000'";
        $conditions = implode(' and ', $cond);
        $list = $userAccountObj->where($conditions)->select();
        
        if ($list) { 
            //利率
            $rate = round($rate/100, 4); 
            
            $time = $end_time.' '.date('H:i:s', time()).'.'.getMillisecond().'000';
            
            $rows = array(
                'interest_rate' => $rate,
                'interest_time' => $time,
                'add_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
            );
            
            foreach ($list as $key => $val) {
                
                $userAccountObj->startTrans();
                
                //该日所得利息(四舍五入小数点后4位)
                $interest = round($val['wallet_enable_interest'] * $rate / 365, 4); 
                
                $rows['user_id'] = $val['user_id'];
                $rows['interest_capital'] = $val['wallet_enable_interest'];
                $rows['interest'] = $interest;
                
                $levelObj = M('userVipLevel')->where('uid ='.$val['user_id'])->find();
                
                $rows['vip_level'] = -1;
                
                if($levelObj) {
                    $rows['vip_level'] = $levelObj['vip_level'];
                }
                
                if ($userWalletInterestObj->add($rows)) {
                    
                    $sql = "update s_user_account set wallet_interest=wallet_interest+" . $interest . ",wallet_interest_totle=wallet_interest_totle+" . $interest;
                    $sql .= ",wallet_totle=wallet_totle+" . $interest . ",wallet_enable_interest=wallet_enable_interest+" . $interest . ",wallet_last_interest_time='" . $time . "' ";
                    $sql .= "where user_id=" . $val['user_id'];
                    
                    if ($userAccountObj->execute($sql)) {
                        $userAccountObj->commit();
                    } else {
                        $userAccountObj->rollback();
                    }
                } else {
                    $userAccountObj->rollback();
                }
            }
        }
        
        //处理三点后提现，购买产品的数据
        
        //$_cond[] = "(type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$end_time." 15:00:00.000000' and add_time<='".$end_time." 23:59:59.999000')"; // 转出（提现）
        
        $_cond[] = "(user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$end_time." 15:00:00.000000' and add_time<='".$end_time." 23:59:59.999000')"; // 转出（购买产品）
        
        $_conditions = implode(' or ', $_cond);
        
        $_conditions = "(" . $_conditions . ") and enable_interest=0";
        
        $list = $userWalletRecordsObj->field('id,type,value,user_id')->where($_conditions)->order('add_time asc')->select();
        
        if($list) {
            foreach ($list as $val) {
                    
                $value = abs($val['value']);
                $userInfo = $userAccountObj->where(array('user_id'=>$val['user_id']))->find();
                
                if ($value <= $userInfo['wallet_enable_interest']) {
                    
                    $userWalletRecordsObj->startTrans();
                    
                    if ($userAccountObj->where(array('user_id' => $val['user_id']))->setDec('wallet_enable_interest', $value)) {
                        
                        if (! $userWalletRecordsObj->where(array(
                            'id' => $val['id'],
                            'enable_interest' => 0))
                                ->save(array('enable_interest' => 1,'modify_time'=>date('Y-m-d H:i:s', time() )))) {
                            $userWalletRecordsObj->rollback();
                        } else {
                            $userWalletRecordsObj->commit();
                        }
                    } else {
                        $userWalletRecordsObj->commit();
                    }
                } else {
    
                    $single_user_info = $userObj->where(array('id'=>$val['user_id']))->find();//用户信息
                    
                    $err_amount = ($value - $userInfo['wallet_enable_interest']);
                    
                    $data = array(
                        'username' => $single_user_info['username'],
                        'real_name' => $single_user_info['real_name'],
                        'type' => 2,
                        'add_time' => date('Y-m-d H:i:s', time()),
                        'desc' => '转出金额大于可计息金额:'.$err_amount.',数据异常,请检查'
                    );
                    $taskInterestObj->add($data);
                }
            }
        }
        
        $data['end_time'] = date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000';
        $data['status'] = 1;
        M('WalletInterestLog')->where('id='.$rId)->save($data);
        
        //end
    }
    
}
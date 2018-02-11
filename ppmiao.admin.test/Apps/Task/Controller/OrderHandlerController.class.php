<?php
/**
 * 定时处理任务-处理异常订单
 */
namespace Task\Controller;

use Think\Controller;

class OrderHandlerController extends Controller
{

    public function index() {
        
        $now = date("Y-m-d H:i:s");
        
        $log = M('RechargeExceptionLog')->where(array('status' => 0))->select();
        
        if (empty($log) || count($log) <= 0 ) {
            exit;
        } 
        
        foreach ($log as $val) {
            
            $red_amount = 0;
            
            $userId = $val['user_id'];
            
            $rechargeObj = M('RechargeLog')->field('project_id,recharge_no,amount,device_type,card_no,type,add_time')
                                            ->where(array('recharge_no' => $val['recharge_no'],'status' => 2))->find();
            
            if(!$rechargeObj) {
                $dd['handle_time'] = time();
                $dd['status'] = 3;
                $dd['error_info'] = '`s_echargeLog` 没有订单号:`'.$val['recharge_no'].'` 记录';
                M("RechargeExceptionLog")->where(array('id' => $val['id']))->save($dd);
                continue;
            }
            
            $order_time = $rechargeObj['add_time'];
            
            M()->startTrans();
            
            if ($val['red_id'] > 0) {
                $red_amount = M('UserRedenvelope')->where(array('id' => $val['red_id'],'status' => 0))->getField('amount');
                // 修改红包使用状态
                $sql_0 = M()->execute("UPDATE `s_user_redenvelope` SET `status` = 1 WHERE id=" . $val['red_id']);
            } else {
                $sql_0 = true;
            }
            
            if ($sql_0) {
                
                $order_amount = $rechargeObj['amount'];
                
                $total_amount = $order_amount + $red_amount;
                
                $project_id = $rechargeObj['project_id'];
                
                $recharge_no = $rechargeObj['recharge_no'];
                
                $projectObj = M('Project')->field('amount,able,type,start_time,end_time,user_interest')
                                            ->where(array('id' => $project_id))->find();
                
                $p_progress = round($projectObj['amount'] - ($projectObj['able'] - $total_amount) / 100, 2);
                
                // 更新project 可投金额、进度
                $sql_1 = M()->execute("UPDATE `s_project` SET able = `able` - $total_amount, percent = $p_progress, modify_time = '$order_time' WHERE id=" . $project_id);
                
                if ($sql_1) {
                    
                    $account_detail['user_id'] = $userId;
                    $account_detail['account_total'] = $total_amount;
                    $account_detail['account_able'] = $total_amount;
                    $account_detail['change_amount'] = $total_amount;
                    $account_detail['type'] = 2;
                    $account_detail['status'] = 1;
                    $account_detail['remark'] = '用户充值，充值编号：' . $recharge_no;
                    $account_detail['add_time'] = $order_time;
                    $account_detail['add_user_id'] = $userId;
                    $account_detail['modify_time'] = $order_time;
                    $account_detail['modify_user_id'] = $userId;
                    $adId = M('AccountDetail')->add($account_detail);
                    
                    if ($adId) {
                        
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
                        $investment_detail['add_time'] = $order_time;
                        $investment_detail['add_user_id'] = $userId;
                        $investment_detail['modify_time'] = $order_time;
                        $investment_detail['modify_user_id'] = $userId;
                        $idId = M('InvestmentDetail')->add($investment_detail);
                        
                        if ($idId) {
                            $user = M('User')->field('real_name_auth')->where(array('id' => $userId))->find();
                            
                            if ($user['real_name_auth'] == 0) {
                                $sql_4 = M()->execute("UPDATE `s_user` SET `real_name_auth`=1,`mobile_auth`=1,`card_no_auth`=1 WHERE id=$userId");
                            } else {
                                $sql_4 = true;
                            }
                            
                            if ($sql_4) {
                                
                                $user_bank_id = M('UserBank')->where(array('bank_card_no' => $rechargeObj['card_no'],'user_id' => $userId))->getField('id');
                                
                                $day = $this->count_days(date('Y-m-d',strtotime($order_time)),$projectObj['end_time']);
                                
                                $wait_money = $total_amount + ($total_amount * $projectObj['user_interest'] * $day / 100 / 365); // 本金+红包+利息
                                
                                $sql_5 = M()->execute("UPDATE `s_user_bank` SET pay_type = " . $rechargeObj['type'] . ", has_pay_success = 2, latest_payment_time = '$order_time', total_money = `total_money` + $order_amount, wait_money = `wait_money` + $wait_money, modify_time = '$order_time', modify_user_id = $userId where id = $user_bank_id and is_deleted = 0");
                                
                                if($sql_5) {
                                
                                    $repayment_detail_Obj = M('RepaymentDetail')->field('id,repayment_time')->where(array('project_id' => $project_id))->find();
                                    
                                    if ($repayment_detail_Obj) {
                                        
                                        $user_due_detail['user_id'] = $userId;
                                        $user_due_detail['project_id'] = $project_id;
                                        $user_due_detail['repay_id'] = $repayment_detail_Obj['id'];
                                        $user_due_detail['invest_detail_id'] = $idId;
                                        $user_due_detail['due_amount'] = $wait_money;
                                        $user_due_detail['due_capital'] = $total_amount;
                                        $user_due_detail['due_interest'] = ($total_amount * $projectObj['user_interest'] * $day / 100 / 365);
                                        $user_due_detail['period'] = 1;
                                        $user_due_detail['duration_day'] = $day;
                                        
                                        $tmp_date = date('Y-m-d H:i:s',strtotime($order_time));
                                        
                                        $user_due_detail['start_time'] = date("Y-m-d H:i:s", strtotime("$tmp_date +1 day"));
                                        $user_due_detail['due_time'] = $repayment_detail_Obj['repayment_time'];
                                        $user_due_detail['status'] = 1;
                                        $user_due_detail['bow_type'] = $projectObj['type'];
                                        $user_due_detail['card_no'] = $rechargeObj['card_no'];
                                        $user_due_detail['repayment_no'] = 'RP' . date("YmdHis",strtotime($order_time)) . $userId . rand(10, 110);
                                        $user_due_detail['add_time'] = $order_time;
                                        $user_due_detail['add_user_id'] = $userId;
                                        $user_due_detail['modify_time'] = $order_time;
                                        $user_due_detail['modify_user_id'] = $userId;
                                        
                                        $uddId = M('UserDueDetail')->add($user_due_detail);
                                        
                                        if($uddId) {
                                            echo 'ok';
                                            M()->commit();
                                            $dd['status'] = 1;
                                        } else {
                                            echo '0';
                                            M()->rollback();
                                            $dd['status'] = 2;
                                        }
                                    } else {
                                        echo '1';
                                        M()->rollback();
                                        $dd['status'] = 2;
                                    }
                                } else {
                                    echo '2';
                                    M()->rollback();
                                    $dd['status'] = 2;
                                }
                            } else {
                                echo '3';
                                M()->rollback();
                                $dd['status'] = 2;
                            }
                        } else {
                            echo '4';
                            M()->rollback();
                            $dd['status'] = 2;
                        }
                    } else {
                        echo '5';
                        M()->rollback();
                        $dd['status'] = 2;
                    }
                } else {
                    echo '6';
                    M()->rollback();
                    $dd['status'] = 2;
                }
            } else {
                echo '7';
                M()->rollback();
                $dd['status'] = 2;
            }
                            
            $dd['handle_time'] = time();
            M("RechargeExceptionLog")->where(array('id' => $val['id']))->save($dd);
            
        } //endforeach

    }

    function count_days($start, $end)
    {
        return floor(abs(strtotime($start) - strtotime($end)) / 86400);
    }
}
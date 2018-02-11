<?php
namespace Admin\Controller;

/**
 * 付息管理
 */
class InterestController extends AdminController {



    /**
     * 还本付息   28.27.3
     */
    public function repay() {
        
        exit;
        
        if (!IS_POST) {
            $id = I('get.id', 0, 'int'); // 债权ID
            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');

            $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
            if (!$detail) {
                $this->error('项目不存在或已被删除');
                exit;
            }
            $list = $repaymentDetailObj->where(array('project_id' => $id))->select(); // 项目还本付息表条目列表
            if (!$list) { // 还未生成还本付息表,生成
                $time = time();
                $uid = $_SESSION[ADMIN_SESSION]['uid'];
                $endTime = date('Y-m-d', strtotime($detail['end_time'])); // 项目结束时间戳
                $tmpTime = '1970-01-01'; // 固定还款日时间戳
                if ($detail['repayment_type'] == 1) { // 一次性还本付息
                    $rows['project_id'] = $detail['id'];
                    $rows['repayment_time'] = $detail['end_time'];
                    $rows['period'] = 1;
                    $rows['status'] = 1;
                    $rows['add_time'] = date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000';
                    $rows['add_user_id'] = $uid;
                    $rows['modify_time'] = date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000';
                    $rows['modify_user_id'] = $uid;
                    $repaymentDetailObj->add($rows);
                } else if ($detail['repayment_type'] == 2) { // 按月付息
                    $month = 1;
                    while ($tmpTime < $endTime) {
                        $tmpTime = strtotime($detail['start_time'] . ' +' . $month . ' month');
                        $tmpTime = date('Y', $tmpTime) . '-' . date('m', $tmpTime) . '-' . $detail['repayment_day'];
                        $rows['project_id'] = $detail['id'];
                        $rows['period'] = $month;
                        $rows['status'] = 1;
                        $rows['add_time'] = date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000';
                        $rows['add_user_id'] = $uid;
                        $rows['modify_time'] = date('Y-m-d H:i:s', $time).'.'.getMillisecond().'000';
                        $rows['modify_user_id'] = $uid;
                        if ($tmpTime < $endTime) {
                            $rows['repayment_time'] = $tmpTime;
                        } else { // 结束
                            $rows['repayment_time'] = $endTime;
                        }
                        $repaymentDetailObj->add($rows);
                        $month++;
                    }
                }
                $list = $repaymentDetailObj->where(array('project_id' => $id))->select();
            }

            $interest_sum = 0;
            $capital_sum = 0;
            foreach($list as $key => $val){ // 计算每期利息和本金
                $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$detail['id'],'repay_id'=>$val['id']))->sum('due_interest');
                $interest_sum += $list[$key]['interest_totle'];
                $list[$key]['capital_totle'] = $userDueDetailObj->where(array('project_id'=>$detail['id'],'repay_id'=>$val['id']))->sum('due_capital');
                $capital_sum += $list[$key]['capital_totle'];
            }

            $this->assign('detail', $detail);
            $this->assign('interest_sum', $interest_sum);
            $this->assign('capital_sum', $capital_sum);
            $this->assign('list', $list);
            $this->display();
        } else {
            // 更改还本付息条目支付状态
            $id = I('post.id', 0, 'int');
            $rid = I('post.rid', 0, 'int');
            $status = I('post.status', 0, 'int'); // (1:未还款/2:已还款/3:正在支付)

            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');
            $userObj = M('User');
            $userAccountObj = M('UserAccount');
            $investmentDetailObj = M('InvestmentDetail');

            // 检查还款中是否还有未处理的钱包订单
            if($userDueDetailObj->where("user_id > 0 and repay_id=".$rid." and status=1 and (to_wallet=1 or from_wallet=1)")->getField('id')){

                $this->ajaxReturn(array('status'=>0,'info'=>'还有未处理的转入钱包订单'));
            }

            $repaymentDetail = $repaymentDetailObj->where(array('id'=>$rid,'project_id'=>$id))->find();

            if($repaymentDetail['status'] == 2) $this->ajaxReturn(array('status'=>0,'info'=>'该条目订单已支付完成'));

            if($repaymentDetail['status'] == 1){

//                if($status != 3) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
//
//            }else if($repaymentDetail['status'] == 3){

                if($status != 2) $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
            }

            $repaymentDetailObj->startTrans();

            $time = date('Y-m-d H:i:s').'.'.getMillisecond();

            if($status == 3){ // 执行支付操作(状态改成正在支付)
                $rows = array(
                    'status' => 3,
                    'status_new' => 3,
                    'real_time' => $time,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
            }else if($status == 2){ // 执行支付完成操作(将状态改成支付完成)
                $rows = array(
                    'status' => 2,
                    'status_new' => 2,
                    'real_time' => $time,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
            }else{
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
            }
            if(!$repaymentDetailObj->where(array('id'=>$rid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#1'));
            // 检查是否有用户购买列表
            if($status == 3){ // 支付动作
                $userList = $userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status_new=1 and to_wallet=0 and from_wallet=0")->select();
            }else if($status == 2){ // 支付完成动作
                $userList = $userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status_new=1 and to_wallet=0 and from_wallet=0")->select();
            }else{
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
            }
            $userIds = '';
            if(count($userList) > 0){
                if($status == 2){ // 执行支付完成动作,变动用户账户金额信息
                    foreach($userList as $key => $val){
                        //解绑银行卡
                        
                        M('UserBank')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2,'user_id'=>$val['user_id']))
                                     ->setDec('wait_money', $val['due_amount']);
                                     
                        if($val['is_new_type'] == 1) {
                            $_amount = $val['due_capital'] - $val['red_amount'];
                        } else {
                            $_amount = $val['due_amount'];
                        }
                        
                        M('UserBank')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2,'user_id'=>$val['user_id']))
                                    ->setDec('lock_money', $_amount);
                        
                        M('UserBank')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2,'user_id'=>$val['user_id']))
                                    ->setDec('capital_money', $_amount);
                                    
                                    
                        
                        $userIds .= ','.$val['user_id'];
                        $sql = "update s_user_account set ";
                        $sql .= "total_invest_capital=total_invest_capital+" . $val['due_capital'];
                        $sql .= ",total_invest_interest=total_invest_interest+" . $val['due_interest'];
                        $sql .= ",wait_amount=wait_amount-" . $val['due_amount'];
                        $sql .= ",wait_capital=wait_capital-" . $val['due_capital'];
                        $sql .= ",wait_interest=wait_interest-" . $val['due_interest'];
                        $sql .= " where user_id=".$val['user_id'];
                        $userAccountObj->execute($sql);

                    }
                    if($userIds) $userIds = substr($userIds, 1);
                }
                if($status == 3){ // 支付动作
                    $rowsSub = array(
                        'status_new' => 3,
                        'real_time' => $time,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    $rowsSub2 = array(
                        'status_new' => 5,
                    );
                }else if($status == 2){ // 支付完成动作
                    $rowsSub = array(
                        'status' => 2,
                        'status_new' => 2,
                        'real_time' => $time,
                        'modify_time' => $time,
                        'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                    );
                    $rowsSub2 = array(
                        'status' => 4,
                        'status_new' => 4,
                    );
                }else{
                    $repaymentDetailObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
                }
                $dueList = $userDueDetailObj->field('invest_detail_id')->where("repay_id=".$repaymentDetail['id']." and status=1 and to_wallet=0 and from_wallet=0")->select();
                if(!$userDueDetailObj->where("repay_id=".$repaymentDetail['id']." and status=1 and to_wallet=0 and from_wallet=0")->save($rowsSub)){
                    $repaymentDetailObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#2 '.$repaymentDetail['id']));
                }
                $investIds = '';
                foreach($dueList as $key => $val){
                    $investIds .= ','.$val['invest_detail_id'];
                }
                if($investIds) $investIds = substr($investIds, 1);
                if($investIds){
                    if(!$investmentDetailObj->where("project_id=".$id." and id in (".$investIds.")")->save($rowsSub2)){
                        $repaymentDetailObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试#3'));
                    }
                }
            }
            if($status == 3){ // 支付动作
                $rowsProject = array(
                    'status' => 4,
                );
            }else if($status == 2){ // 支付完成动作
                $rowsProject = array(
                    'status' => 5,
                    'repayment_time' => $time, // 设置已还款时间
                );
            }else{
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
            }
            if(!$projectObj->where(array('id'=>$id))->save($rowsProject)){
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
            $repaymentDetailObj->commit();

            $pushResult = '';
            if($userIds){
                // 处理到账通知推送(极光)
                $userArr = $userObj->field('id,registration_id,channel,latest_device_type,last_channel')->where("id in (".$userIds.")")->select();

                $projectTitle = $projectObj->where(array('id'=>$id))->getField('title');
                $result = array();
                foreach($userArr as $key => $val){

                    $registration_id = trim($val['registration_id']);

                    $channel_name = trim($val['channel']);

                    $latest_device_type = trim($val['latest_device_type']);
                    $last_channel = trim($val['last_channel']);

                    if($registration_id && !empty($registration_id)) {
                        $tips = '';
                        if($this->isUseBag($val['id'])) {
                            $tips = '记得使用您的红包喔！';
                        }
                        send_personal_message(0, $val['id'], '您购买的产品['.$projectTitle.']本息已飞奔至您的银行卡，请注意查收。'.$tips);
                        
                        $_app = getAppId($channel_name);

                        JPushLog('您购买的产品['.$projectTitle.']本息飞奔至您的银行卡，请注意查收。'.$tips.',
                            registration_id:'.$registration_id.',user_id:'.$val['id'].',action:12,app:'.$_app);

                        $result[] = pushMsg('您购买的产品['.$projectTitle.']本息飞奔至您的银行卡，请注意查收。'.$tips,
                            $registration_id,'',array('action'=>12),$_app);

                        $data = array('pushType'=>2,
                            'registrationId'=>$registration_id,
                            'position'=>3,
                            'page'=>3,
                            'lastDeviceType'=>$latest_device_type,
                            'lastChannel'=>$last_channel
                        );
                        updatePushMsg($data);
                    }
                }
                if($result) unset($result[0]);
                foreach($result as $key => $val){
                    if($val->isOK){
                        $pushResult .= "第".($key+1)."波推送成功~\r\n";
                    }
                }
            }
            $this->ajaxReturn(array('status'=>1,'info'=>$pushResult));
        }
    }

    /**
     * 撤销支付动作
     */
    public function revoke(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int'); // 产品ID
        $rid = I('post.rid', 0, 'int'); // 付息ID

        $userAccountObj = M('UserAccount');
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userAccountObj = M('UserAccount');
        $repaymentDetail = $repaymentDetailObj->where(array('id'=>$rid,'project_id'=>$id))->find();
        if(!$repaymentDetail) $this->ajaxReturn(array('status'=>0,'info'=>'付息信息不存在或已被删除'));
        // 非已支付状态
        if($repaymentDetail['status'] != 2) $this->ajaxReturn(array(''=>0,'info'=>'不在已支付状态,无法撤销'));
        $repaymentDetailObj->startTrans();
        $time = date('Y-m-d H:i:s').'.'.getMillisecond();
        $rows = array(
            'status' => 1,
            'real_time' => '',
            'modify_time' => $time,
            'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
        );
        if(!$repaymentDetailObj->where(array('id'=>$rid))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        // 检查是否有用户购买列表
        $userList = $userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id'],'status'=>2))->select();
        if($repaymentDetail['status2'] != 0){ // 逾期、坏账
            $rowsSub = array(
                'status' => 1,
                'real_time' => null,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id']))->save($rowsSub)){
                $repaymentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
        }else{ // 正常支付流程(需要把用户账户数据同时返回)
            if(count($userList) > 0){
                foreach($userList as $key => $val){
                    $sql = "update s_user_account set ";
                    $sql .= "total_invest_capital=total_invest_capital-" . $val['due_capital'];
                    $sql .= ",total_invest_interest=total_invest_interest-" . $val['due_interest'];
                    $sql .= ",wait_amount=wait_amount+" . $val['due_amount'];
                    $sql .= ",wait_capital=wait_capital+" . $val['due_capital'];
                    $sql .= ",wait_interest=wait_interest+" . $val['due_interest'];
                    $sql .= " where user_id=".$val['user_id'];
                    $userAccountObj->execute($sql);
                }
                $rowsSub = array(
                    'status' => 1,
                    'real_time' => null,
                    'modify_time' => $time,
                    'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                );
                if(!$userDueDetailObj->where(array('repay_id'=>$repaymentDetail['id']))->save($rowsSub)){
                    $repaymentDetailObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                }
            }
        }
        $repaymentDetailObj->commit();
        // 检查该是否已全部支付完成
        if($projectObj->where(array('status'=>5,'id'=>$id))->getField('id')){ // 产品状态为已支付状态
            if(!$projectObj->where(array('id'=>$id))->save(array('status'=>3))){
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
        }
        $this->ajaxReturn(array('status'=>1));
    }




    /**
     * 最新付息信息列表
     */
    public function lastestpay(){
        if(!IS_POST){
            $force = I('get.force', 1, 'int'); // 是否强制使用1%手续费计算(只针对石头1号)
            $days = C('LASTEST_REPAY_DAYS'); // 最近7天需要付息的列表或过期的列表

            $datetime = I('get.dt', '', 'strip_tags');
            $updatecache = I('get.uc', 0, 'int'); // 更新缓存
            if(!$datetime) $datetime = date('Y-m-d');
            $start_time = $datetime;
            $this->assign('start_time', $start_time);
            $this->assign('days', $days);
            $this->assign('start_day', $days);
            $this->assign('force', $force);
            $this->display();
        }else{

            if(!IS_AJAX) exit;

            $force = I('post.force', 1, 'int');

            $date = I('post.date', date('Y-m-d'));
            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');
            $rechargeLogObj = M("RechargeLog");
            
            $days = C('LASTEST_REPAY_DAYS'); // 最近7天需要付息的列表或过期的列表

			$time = $date.' 00:00:00.000000';
			$endtime = $date.' 23:59:59.999000';
			
			$conditions = "(repayment_time>='".$time."' and repayment_time<='".$endtime."' and (status=1 or status=3)) 
			                     or ( real_time>='".$time."' and real_time<='".$endtime."' and status=3)";

            $list = $repaymentDetailObj->where($conditions)->order('status desc,repayment_time,real_time')->select(); // 项目还本付息表条目列表

            $html = '';

            foreach($list as $key => $val){ // 计算每期利息和本金

                $projectInfo = $projectObj->field('id,title,status,duration,amount,contract_interest,user_interest,start_time,end_time,advance_end_time,repay_review,is_delete')
                                            ->where(array('id'=>$val['project_id']))->find();
                
                if($projectInfo['status'] != 8) {
                
                $list[$key]['project_title'] = $projectInfo['title'];

                $list[$key]['project_status'] = $projectInfo['status'];

                if($projectInfo['status'] > 2){
                    $list[$key]['project_amount'] = $projectInfo['amount'];
                    //实际金额
                    $list[$key]['capital_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>array('gt',0),'status'=>1))->sum('due_capital');
                    //幽灵购买
                    $list[$key]['ghost_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>array('elt',0),'status'=>1))->sum('due_capital');
                    
                    //合同利率 - 给用户利率
                    
                    $list[$key]['counter_fee'] = $projectInfo['amount']*($projectInfo['contract_interest']-$projectInfo['user_interest'])*$projectInfo['duration']/100/365;
                    
                    $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'status'=>1))->sum('due_interest');
                    
                    $list[$key]['more_interest'] = 0;
                    

                    $moreMoney = abs($projectInfo['amount'] - $list[$key]['capital_totle'] - $list[$key]['ghost_totle']);

                    if($moreMoney > 0){

                        $lastItem = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->order('add_time desc')->limit(1)->find();

                        $moreInterest = $moreMoney * $lastItem['duration_day'] * $projectInfo['user_interest']/100/365;

                        $list[$key]['more_interest'] = $moreInterest;
                    }
                    

                    // 检查是否是今天到期的标
                    if(date('Y-m-d', strtotime($val['repayment_time'])) == date('Y-m-d', time())){
                        $html .= '<tr class="row" style="background-color:yellow;">';
                    }else if($val['status'] == 3){
                        $html .= '<tr class="row" style="background-color:#00FFA9;">';
                    }else{
                        $html .= '<tr class="row">';
                    }

                    // 判断是否有转入钱包的订单
                    if($userDueDetailObj->where("project_id=".$val['project_id']." and (from_wallet=1 or to_wallet=1)")->getField('id')) {
                        $html .= '<td><input type="checkbox" class="input_users to_bank to_wallet" data-id="' . $val['id'] . '" data-projectId="'.$val['project_id'].'" name="checkbox" data-time="'.date('Y-m-d', strtotime($val['repayment_time'])).'"  data-title="'.$projectInfo['title'].'" data-interest="'.number_format($list[$key]['interest_totle'], 2).'" data-amount="'.$projectInfo['amount'].'"></td>';
                    }else{
                        $html .= '<td><input type="checkbox" class="input_users to_bank to_wallet" data-id="' . $val['id'] . '" data-projectId="'.$val['project_id'].'" name="checkbox" data-time="'.date('Y-m-d', strtotime($val['repayment_time'])).'"  data-title="'.$projectInfo['title'].'" data-interest="'.number_format($list[$key]['interest_totle'], 2).'" data-amount="'.$projectInfo['amount'].'"></td>';
                    }
                    $html .= '<td>'.$projectInfo['title'].'</td>';
                    $html .= '<td align="center">'.date('Y-m-d', strtotime($val['repayment_time'])).'</td>';
                    $html .= '<td align="right">'.number_format($list[$key]['interest_totle'], 2).' 元</td>';                   
                    $html .= '<td align="right">'.number_format($list[$key]['more_interest'], 2).' 元</td>';                    
                    $html .= '<td align="right">'.number_format($list[$key]['capital_totle'], 2).' 元</td>';                    
                    $html .= '<td align="right">'.number_format($moreMoney, 2).' 元</td>';
                    $html .= '<td align="right">'.number_format($list[$key]['ghost_totle'], 2).' 元</td>';
                    
                    $html .= '<td align="center">';

                    switch($projectInfo['repay_review']){
                        case 0:
                            $html .= '<span style="color:red;">财务未审核</span>';
                            break;
                        case 1:
                            $html .= '<span style="color:green;">财务已审核</span>';
                            break;
                        case 2:
                            $html .= '<span style="color:#CD05FF;">融资方已还款</span>';
                            break;
                        case 3:
                            $html .= '<span style="color:#CD05FF;">已支付</span>';
                            break;
                        case 4:
                            $html .= '<span style="color:#CD05FF;">财务审核失败</span>';
                            break;
                        
                    }
                    $html .= '</td>';
                    
                    
                    $html .= '<td align="center">';
                    switch($val['status']){
                        case 1:
                            $html .= '<span style="color:red;">未支付</span>';
                            break;
                        case 2:
                            $html .= '<span style="color:green;">已支付</span>';
                            break;
                        case 3:
                            $html .= '<span style="color:#CD05FF;">正在支付</span>';
                            break;
                    }
                    $html .= '</td>';
                    
                    $html .= '<td>';
                    
                    if($projectInfo['repay_review'] == 2){
                        $html .= '<a href="javascript:;" style="color:green;" onclick="pay('.$val['project_id'].', '.$val['id'].', 2, \'支付\')">支付</a>&nbsp;&nbsp;';
                    }
                    
                    $html .= '<a href="javascript:;" onclick="batchto_wallet(' . $val['project_id'] . ', ' . $val['id'] . ')">还款 </a>&nbsp;&nbsp;';
                    
                    // 判断是否有转入钱包的订单
                    $html .= '<a href="javascript:;" onclick="towalletlist(' . $val['project_id'] . ', ' . $val['id'] . ')">用户列表 </a>&nbsp;&nbsp;';
                    
                    //$html .= '<a href="javascript:;" onclick="buylist('.$val['project_id'].', '.$val['id'].')">购买列表</a>&nbsp;&nbsp;';
                    
                    if(checkAuth('Admin/project/paysms')) {
                        if($val['is_sms'] == 0) $html .= '<a href="javascript:;" onclick="paysms('.$val['id'].', this)">短信通知</a>&nbsp;&nbsp;';
                    }
                    
                    if(checkAuth('Admin/project/checksms')) {
                        if($val['is_sms'] == 1) $html .= '<a href="javascript:;" onclick="checksms('.$val['id'].', this)">检查短信到达情况</a>&nbsp;&nbsp;';
                    }
                    $html .= '</td></tr>';
                }
                }
            }
            $this->ajaxReturn(array('status'=>1, 'info'=>$html));
        }
    }


    /**
     * 到期金额转入钱包-单条处理
     */
    public function project_to_wallet(){

        if(!IS_POST || !IS_AJAX) {
            exit;
        }

        $id = I('post.id', 0, 'int'); //UserDueDetail ID

        if(!$id) {
            $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        }

        $userDueDetailObj = M('UserDueDetail');
        $userDueDetailInfo = $userDueDetailObj->where("id = $id and user_id > 0")->find();//array('id'=>$id)

        if(!$userDueDetailInfo) {
            $this->ajaxReturn(array('status'=>0,'info'=>'用户订单信息不存在'));
        }
        if($userDueDetailInfo['status'] != 1) {
            $this->ajaxReturn(array('status'=>0,'info'=>'订单不是未支付状态'));
        }
        
        $projectInfo = M("Project")->field('id,title,repay_review')->where(array('id'=>$userDueDetailInfo['project_id']))->find();
        if(!$projectInfo) {
            $this->ajaxReturn(array('status'=>0,'info'=>'期数不存在'));
        }
        
        if($projectInfo['repay_review'] !=3){
            if($projectInfo['repay_review'] == 0){
                $info='融资方还款还没有审核，操作失败。';                
            } else if($projectInfo['repay_review'] == 1) {
                $info='融资方还款已审核，融资方还没有把资金转入标的账户，操作失败。';
            } else if($projectInfo['repay_review'] == 2) {
                $info='标的账户的资金还没有转打投资用户的账户，请先操作 ·支付·。';
            } else {
                $info='融资方还款审核失败';
            }
            $this->ajaxReturn(array('status'=>0,'info'=>$info));
        }
        
        
        $userWalletRecordsObj = M("UserWalletRecords");
        $investmentDetailObj = M("InvestmentDetail");
        $userAccountObj = M("UserAccount");
        $userObj = M('User');


        $rows = array(
            'user_id' => $userDueDetailInfo['user_id'],
            'value' => $userDueDetailInfo['due_amount'],
            'type' => 4,
            'pay_status' => 2,
            'status' => 1,
            'user_due_detail_id' => $userDueDetailInfo['id'],
            'add_time' => date('Y-m-d H:i:s'),
            'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
            'modify_time'=>date('Y-m-d H:i:s'),
            'remark'=>$projectInfo['title'].'标的还款'
        );

        $userWalletRecordsObj->startTrans();

        //添加记录明细
        if(!$userWalletRecordsObj->add($rows)) {
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        //更新 s_user_due_detail 设为已还款。
        if(!$userDueDetailObj->where(array('id'=>$userDueDetailInfo['id']))->save(array('status'=>2,'status_new'=>2))){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        //设为已还款
        if(!$investmentDetailObj->where(array('id'=>$userDueDetailInfo['invest_detail_id']))->save(array('status'=>4,'status_new'=>4))){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        $sql = "update s_user_account set wallet_totle=wallet_totle+" . $userDueDetailInfo['due_amount'];
        $sql .= ",total_invest_capital=total_invest_capital+" . $userDueDetailInfo['due_capital'];
        $sql .= ",total_invest_interest=total_invest_interest+" . $userDueDetailInfo['due_interest'];
        $sql .= ",account_total=account_total-" . $userDueDetailInfo['due_amount'];
        $sql .= ",wait_amount=wait_amount-" . $userDueDetailInfo['due_amount'];
        $sql .= ",wait_capital=wait_capital-" . $userDueDetailInfo['due_capital'];
        $sql .= ",wait_interest=wait_interest-" . $userDueDetailInfo['due_interest'];
        $sql .= ",wallet_product_interest=wallet_product_interest+" . $userDueDetailInfo['due_interest'];
        
        //判断订单是否使用红包;解决客户端提现问题
        $red_amt = $userDueDetailInfo['red_amount'];
        if($red_amt == 0){
            $recharge_no = M('InvestmentDetail')->where(array('id'=>$userDueDetailInfo['invest_detail_id'],'user_id'=>$userDueDetailInfo['user_id']))->getField('recharge_no');
            $red_amt = $this->getBagAmount($userDueDetailInfo['user_id'], $recharge_no);
        }
        
        if($red_amt>0){
            $sql.= ",red_coupon_total = red_coupon_total+".$red_amt;
        }
        
        $sql.= " where user_id=".$userDueDetailInfo['user_id'] .' limit 1';
        
        if(!$userAccountObj->execute($sql)){
            $userWalletRecordsObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }

        $userWalletRecordsObj->commit();

        $tips = '';
        if($this->isUseBag($userDueDetailInfo['user_id'])) {
            $tips = '记得使用您的红包喔！';
        }
        // 发送个人消息
        send_personal_message(0, $userDueDetailInfo['user_id'], '您购买的产品['.$projectInfo['title'].']本息已转入平台账户，请注意查收。'.$tips);

        // 转入钱包完成后发送推送给用户,处理到账通知推送(极光)
        $base_user = $userObj->field('registration_id,channel,last_channel,latest_device_type')->where(array('id'=>$userDueDetailInfo['user_id']))->find();

        $registration_id = trim($base_user['registration_id']);

        $channel_name = trim($base_user['channel']);

        if($registration_id && !empty($registration_id)) {

            $_app = getAppId($channel_name);
            /*
            JPushLog('您购买的产品['.$projectInfo['title'].']本息已转入钱包，请注意查收。'.$tips.',
                registration_id:'.$registration_id.',user_id:'.$userDueDetailInfo['user_id'].',action:12,app:'.$_app);
            */
            pushMsg('您购买的产品['.$projectInfo['title'].']本息已转入平台账户，请注意查收。'.$tips, $registration_id, '', array('action'=>12),$_app);
            
            $data = array(
                'pushType'=>2,
                'registrationId'=>$registration_id,
                'position'=>3,
                'page'=>3,
                'lastDeviceType'=>$base_user['latest_device_type'],
                'lastChannel'=>$base_user['last_channel']
            );
            updatePushMsg($data);
        }

        $this->ajaxReturn(array('status'=>1));
    }

	 /**
     * 到期金额转入钱包-批量处理
     */
    public function project_batchto_wallet()
    {

        header("Content-type:text/html;charset=utf-8");
        set_time_limit(0);
		//第一步查出待处理的转入钱包的支付记录

        $id = I('post.id', 0, 'int'); // 项目ID
        $repay_id = I('post.rid', 0, 'int'); // 还本付息表条目ID


        $detail = M('Project')->field('id,title,repay_review')->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            exit("项目不存在或已被删除<br/>");
        }
        
        if($detail['repay_review'] !=3){
            if($detail['repay_review'] == 0){
                $info='融资方还款还没有审核，操作失败。';
            } else if($detail['repay_review'] == 1) {
                $info='融资方还款已审核，融资方还没有把资金转入标的账户，操作失败。';
            } else if($detail['repay_review'] == 2) {
                $info='标的账户的资金还没有转打投资用户的账户，请先操作 ·支付·。';
            }else{
                $info='融资方还款审核失败';
            }
            $this->ajaxReturn(array('status'=>0,'info'=>$info));
        }
        
        $repayDetail = M('RepaymentDetail')->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
			exit("还本付息条目不存在或已被删除<br/>");
        }

        $cond[] = 'user_id > 0 ';
        $cond[] = "project_id=".$id;
        $cond[] = "repay_id=".$repay_id;
        $cond[] = 'status = 1';
        $conditions = implode(' and ', $cond);
        
        $userDueDetailObj = M('UserDueDetail');
        
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();

		if(empty($list)){
            $this->ajaxReturn(array('status'=>0,'info'=>'没有需要转入钱包的数据'));
		}

		
		$userObj = M('User');
		$userAccountObj = M('UserAccount');
		$userWalletRecordsObj = M("UserWalletRecords");
		$investmentDetailObj = M("InvestmentDetail");
		
		
		//第二步批量处理数据

        foreach($list as $i => $v) {

			if($v['status'] !=1 ){
                $this->ajaxReturn(array('status'=>0,'info'=>'订单不是未支付状态'));
			}

			$rows = array(
				'user_id' => $v['user_id'],
				'value' => $v['due_amount'],
				'type' => 4,
				'pay_status' => 2,
				'status' => 1,
				'user_due_detail_id' => $v['id'],
				'add_time' => date('Y-m-d H:i:s'),
			    'edit_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
			    'modify_time'=>date('Y-m-d H:i:s'),
			    'remark'=>$detail['title'].'标的还款'
			);

			$userWalletRecordsObj->startTrans();

			if(!$userWalletRecordsObj->add($rows)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
			}

			if(!$userDueDetailObj->where(array('id'=>$v['id']))->save(array('status'=>2,'status_new'=>2))){
				$userWalletRecordsObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
			}

			if(!$investmentDetailObj->where(array('id'=>$v['invest_detail_id']))->save(array('status'=>4,'status_new'=>4))){
				$userWalletRecordsObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
			}

            
            $sql = "update s_user_account set wallet_totle=wallet_totle+".$v['due_amount'];
            $sql.= ",total_invest_capital=total_invest_capital+".$v['due_capital'];
            $sql.= ",total_invest_interest=total_invest_interest+".$v['due_interest'];
            $sql.= ",account_total=account_total-".$v['due_amount'];
            $sql.= ",wait_amount=wait_amount-".$v['due_amount'];
            $sql.= ",wait_capital=wait_capital-".$v['due_capital'];
            $sql.= ",wait_interest=wait_interest-".$v['due_interest'];
            $sql.= ",wallet_product_interest=wallet_product_interest+".$v['due_interest'];
            
            //判断订单是否使用红包;解决客户端提现问题
            $red_amt = $v['red_amount'];
            if($red_amt <= 0){
                $recharge_no = M('InvestmentDetail')->where(array('id'=>$v['invest_detail_id'],'user_id'=>$v['user_id']))->getField('recharge_no');
                $red_amt = $this->getBagAmount($v['user_id'], $recharge_no);
            }
            if($red_amt>0){
                $sql.= ",red_coupon_total = red_coupon_total+".$red_amt;
            }
            
            $sql.= " where user_id=".$v['user_id'] .' limit 1';

            if(!$userAccountObj->execute($sql)){
                $userWalletRecordsObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }

            $userWalletRecordsObj->commit();

    		/*给个人通知信息*/

            $tips = '';
            if($this->isUseBag($v['user_id'])) {
                $tips = '记得使用您的红包喔！';
            }
            // 发送个人消息
            send_personal_message(0, $v['user_id'], '您购买的产品['.$detail['title'].']本息已转入平台账户，请注意查收。'.$tips);

            // 转入钱包完成后发送推送给用户,处理到账通知推送(极光)
            $base_user = $userObj->field('registration_id,channel,latest_device_type,last_channel')->where(array('id'=>$v['user_id']))->find();

            $registration_id = trim($base_user['registration_id']);

            $channel_name = trim($base_user['channel']);

            if($registration_id && !empty($registration_id)) {

                $_app = getAppId($channel_name);
                
                /*
                JPushLog('您购买的产品['.$detail['title'].']本息已转入钱包，请注意查收。'.$tips.',
                            registration_id:'.$registration_id.',user_id:'.$v['user_id'].',action:12,app:'.$_app);
                */
                pushMsg('您购买的产品['.$detail['title'].']本息已转入平台账户，请注意查收。'.$tips, $registration_id, '', array('action'=>12),$_app);
                
                $data = array('pushType'=>2,
                            'registrationId'=>$registration_id,
                            'position'=>3,
                            'page'=>3,
                            'lastDeviceType'=>$base_user['latest_device_type'],
                            'lastChannel'=>$base_user['last_channel']
                );
                updatePushMsg($data);
                
            }
        }
        $this->ajaxReturn(array('status'=>1,'info'=>'处理完成'));
    }

    /**
     * （存管）财务审核
     */
    public function repay_review(){
        if(!IS_POST){
            $force = I('get.force', 1, 'int'); // 是否强制使用1%手续费计算(只针对石头1号)
            $days = C('LASTEST_REPAY_DAYS'); // 最近7天需要付息的列表或过期的列表

            $datetime = I('get.dt', '', 'strip_tags');
            $updatecache = I('get.uc', 0, 'int'); // 更新缓存
            if(!$datetime) $datetime = date('Y-m-d',strtotime("+2 day"));
            $flist = M('Financing')->field('id,name')->select();
            $this->assign('start_time', $datetime);
            $this->assign('days', $days);
            $this->assign('flist', $flist);
            $this->assign('start_day', $days);
            $this->assign('force', $force);
            $this->display();
        }else{

            if(!IS_AJAX) exit;

            $force = I('post.force', 1, 'int');
            $fid = I('post.fid', 0, 'int');

            $date = I('post.date', date('Y-m-d',strtotime("+2 day")));

            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');
            $rechargeLogObj = M("RechargeLog");

            $days = C('LASTEST_REPAY_DAYS'); // 最近7天需要付息的列表或过期的列表

            $time = $date.' 00:00:00.000000';
            $endtime = $date.' 23:59:59.999000';


            $conditions = "(repayment_time>='".$time."' and repayment_time<='".$endtime."' and (status=1 or status=3))
			                     or ( real_time>='".$time."' and real_time<='".$endtime."' and status=3)";


            $list = $repaymentDetailObj->where($conditions)->order('status desc,repayment_time,real_time')->select(); // 项目还本付息表条目列表

            $html = '';

            $total_interest = 0;
            $total_capital=0;
            foreach($list as $key => $val){ // 计算每期利息和本金

                $projectInfo = $projectObj->field('id,fid,title,status,duration,amount,contract_interest,user_interest,start_time,end_time,advance_end_time,repay_review')
                    ->where(array('id'=>$val['project_id']))->find();

                $financingInfo = M('Financing')->field('id,name')->where(['id'=>$projectInfo['fid']])->find();

                $list[$key]['project_title'] = $projectInfo['title'];

                $list[$key]['project_status'] = $projectInfo['status'];

                if($fid == 0){
                    $f_check = true;
                }else if($fid== $financingInfo['id']){
                    $f_check = true;
                }else {
                    $f_check = false;
                }

                if($projectInfo['status'] == 3 && $projectInfo['repay_review'] == 0 && $f_check){
                    $list[$key]['project_amount'] = $projectInfo['amount'];
                    //实际金额
                    $list[$key]['capital_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>array('gt',0),'status'=>1))->sum('due_capital');
                    //幽灵购买
                    $list[$key]['ghost_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>array('elt',0)))->sum('due_capital');

                    //合同利率 - 给用户利率

                    $list[$key]['counter_fee'] = $projectInfo['amount']*($projectInfo['contract_interest']-$projectInfo['user_interest'])*$projectInfo['duration']/100/365;

                    $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'status'=>1))->sum('due_interest');

                    $list[$key]['more_interest'] = 0;


                    $moreMoney = abs($projectInfo['amount'] - $list[$key]['capital_totle'] - $list[$key]['ghost_totle']);

                    if($moreMoney > 0){

                        $lastItem = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->order('add_time desc')->limit(1)->find();

                        $moreInterest = $moreMoney * $lastItem['duration_day'] * $projectInfo['user_interest']/100/365;

                        $list[$key]['more_interest'] = $moreInterest;
                    }


                    // 检查是否是今天到期的标
                    if(date('Y-m-d', strtotime($val['repayment_time'])) == date('Y-m-d', time())){
                        $html .= '<tr class="row" style="background-color:yellow;">';
                    }else if($val['status'] == 3){
                        $html .= '<tr class="row" style="background-color:#00FFA9;">';
                    }else{
                        $html .= '<tr class="row">';
                    }

                    // 判断是否有转入钱包的订单
                    if($userDueDetailObj->where("project_id=".$val['project_id']." and (from_wallet=1 or to_wallet=1)")->getField('id')) {
                        $html .= '<td><input type="checkbox" class="input_users to_bank to_wallet" data-id="' . $val['id'] . '" data-projectId="'.$val['project_id'].'" name="checkbox" data-time="'.date('Y-m-d', strtotime($val['repayment_time'])).'"  data-title="'.$projectInfo['title'].'" data-interest="'.number_format($list[$key]['interest_totle'], 2).'" data-amount="'.$projectInfo['amount'].'"></td>';
                    }else{
                        $html .= '<td><input type="checkbox" class="input_users to_bank" data-id="' . $val['id'] . '" data-projectId="'.$val['project_id'].'" name="checkbox" data-time="'.date('Y-m-d', strtotime($val['repayment_time'])).'"  data-title="'.$projectInfo['title'].'" data-interest="'.number_format($list[$key]['interest_totle'], 2).'" data-amount="'.$projectInfo['amount'].'"></td>';
                    }
                    $html .= '<td>'.$projectInfo['title'].'</td>';
                    $html .= '<td align="center">'.date('Y-m-d', strtotime($val['repayment_time'])).'</td>';
                    $html .= '<td align="right">'.number_format($list[$key]['interest_totle']+$list[$key]['capital_totle'], 2).' 元</td>';
                    $html .= '<td align="right">'.number_format($list[$key]['interest_totle'], 2).' 元</td>';
                    $html .= '<td align="right">'.number_format($list[$key]['capital_totle'], 2).' 元</td>';
                    $html .= '<td align="right">'.$financingInfo['name'].'</td>';
                    $total_interest = $total_interest+$list[$key]['interest_totle'];
                    $total_capital = $total_capital+$list[$key]['capital_totle'];

                    $html .= '<td align="center">';
                    switch($projectInfo['repay_review']){
                        case 0:
                            $html .= '<span style="color:red;">未审核</span>';
                            break;
                        case 4:
                            $html .= '<span style="color:green;">审核失败</span>';
                            break;
                    }
                    $html .= '</td>';
                    $html .= '<td>';
                    $html .= '<a href="javascript:;" style="color:green;" onclick="review('.$val['project_id'].', '.$val['id'].', 2, \'审核\')">审核</a>&nbsp;&nbsp;';

                    $html .= '<a href="javascript:;" onclick="buylist('.$val['project_id'].', '.$val['id'].')">购买列表</a>&nbsp;&nbsp;';
                    $html .= '</td></tr>';


                }


            }
            $html .= '<tr class="row">';
            $html .= '<td colspan="3">总计</td>';
            $html .= '<td>'.number_format($total_capital+$total_interest, 2).' 元</td>';
            $html .= '<td>'.number_format($total_interest, 2).' 元</td>';
            $html .= '<td>'.number_format($total_capital, 2).' 元</td>';
            $html .= '<td colspan="3"></td></tr>';
            $this->ajaxReturn(array('status'=>1, 'info'=>$html));
        }


    }

    public function range_review(){
        $ids = I('post.check_string');
        $ids_array = explode(':',$ids);
        $projectObj = M('Project');

        $projectObj->startTrans();
        $res = true;

        foreach($ids_array as $id){

            $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
            if (!$detail) {
                $res = false;

            }elseif($detail['repay_review'] != 0 || $detail['status'] != 3){
                $res = false;
            }else{

                $rowsProject = array(
                    'repay_review' => 1,
                );
                if(!$projectObj->where(array('id'=>$id))->save($rowsProject)){
                    $res = false;
                }else{
                    $res = true;
                }
            }
        }

        if(!$res){
            $projectObj->rollback();
            $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        }else{
            $projectObj->commit();
            $this->ajaxReturn(array('status'=>1,'info'=>'审核成功'));
        }

    }


    public function review(){

        if(IS_POST){
            // 更改还本付息条目支付状态
            $id = I('post.id', 0, 'int');
            $rid = I('post.rid', 0, 'int');
            $status = I('post.status', 0, 'int'); // (1:未还款/2:已还款/3:正在支付)

            $projectObj = M('Project');
            $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
            if (!$detail) {
                $this->error('项目不存在或已被删除');
                exit;
            }elseif($detail['repay_review'] != 0 || $detail['status'] != 3){
                $this->error('项目无法审核');
                exit;
            }else{

                $rowsProject = array(
                    'repay_review' => 1,
                );
                if(!$projectObj->where(array('id'=>$id))->save($rowsProject)){
                    $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                }else{
                    $this->ajaxReturn(array('status'=>1,'info'=>'审核成功'));
                }
            }



        }else{
            $this->error('非法操作');
        }

    }

    public function review_buylist(){

        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }

        $count = 15;
        $conditions = "user_id > 0 and project_id=".$id." and repay_id=".$repay_id;
        $counts = $userDueDetailObj->where($conditions)->count();
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();
        $page_totle_capital = 0;
        $page_totle_interest = 0;
        $page_totle_red = 0;
        $page_totle_coupon = 0;
        foreach ($list as $key => $val) {
            $list[$key]['real_name'] = $userObj->where(array('id' => $val['user_id']))->getField('real_name');
            $list[$key]['bank_name'] = $userBankObj->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->getField('bank_name');
            $list[$key]['to_wallet'] = $userAccountObj->where(array('user_id' => $val['user_id']))->getField('to_wallet');
            if($val['interest_coupon'] > 0){
                $list[$key]['coupon_income'] =  $this->countInterest($val['due_capital'],$val['add_time'],$val['due_time'],$val['interest_coupon'],$val['duration_day']);
            }else{
                $list[$key]['coupon_income'] =  0;
            }
            $page_totle_capital += $val['due_capital'];
            $page_totle_interest += $val['due_interest'];
            $page_totle_red += $val['red_amount'];
            $page_totle_coupon += $list[$key]['coupon_income'];
        }

        $this->assign('totle_interest', $userDueDetailObj->where($conditions)->sum('due_interest'));
        $this->assign('totle_capital', $userDueDetailObj->where($conditions)->sum('due_capital'));

        $this->assign('page_totle_red', $page_totle_red);
        $this->assign('page_totle_coupon', $page_totle_coupon);
        $this->assign('page_totle_interest', $page_totle_interest);
        $this->assign('page_totle_capital', $page_totle_capital);

        $this->assign('detail', $detail);
        $this->assign('repay_detail', $repayDetail);
        $this->assign('list', $list);
        $this->assign('id', $id);
        $this->assign('rid', $repay_id);
        $this->assign('count', $counts);
        $this->display();
    }
    public function export_all(){

    }
    
    /**
     * （存管）财务审核 导出
     */
    public function repay_review_export(){
        
        vendor('PHPExcel.PHPExcel');
        
        $fid = I('fid', 0, 'int');    
        $date = I('date', date('Y-m-d',strtotime("+2 day")));    
        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');          
        $time = $date.' 00:00:00.000000';
        $endtime = $date.' 23:59:59.999000';    

        $conditions = "(repayment_time>='".$time."' and repayment_time<='".$endtime."' and (status=1 or status=3))
		                     or ( real_time>='".$time."' and real_time<='".$endtime."' and status=3)";

        $list = $repaymentDetailObj->where($conditions)->order('status desc,repayment_time,real_time')->select(); // 项目还本付息表条目列表

        $res = array();
        
        foreach($list as $key => $val){ // 计算每期利息和本金
    
                $projectInfo = $projectObj->field('id,fid,title,status,duration,amount,contract_interest,user_interest,start_time,end_time,advance_end_time,repay_review')
                 ->where(array('id'=>$val['project_id']))->find();
    
                 $financingInfo = M('Financing')->field('id,name')->where(['id'=>$projectInfo['fid']])->find();
    
                 $list[$key]['project_title'] = $projectInfo['title'];
    
                 $list[$key]['project_status'] = $projectInfo['status'];
    
                 if($fid == 0){
                     $f_check = true;
                 }else if($fid== $financingInfo['id']){
                     $f_check = true;
                 }else {
                     $f_check = false;
                 }
    
                 if($projectInfo['status'] == 3 && $projectInfo['repay_review'] == 0 && $f_check){
                    
                     $list[$key]['capital_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>array('gt',0),'status'=>1))->sum('due_capital');
                     $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'status'=>1))->sum('due_interest');
                     
                     $list[$key]['amt_total'] = $list[$key]['capital_totle']+$list[$key]['interest_totle'];
                     $list[$key]['f_name'] = $financingInfo['name'];
                     
                     
                     $res[] = $list[$key];
                 }
           }           
           
           
           $objPHPExcel = new \PHPExcel();
           $objPHPExcel->getProperties()
           ->setCreator("票票喵票据")
           ->setLastModifiedBy("票票喵票据")
           ->setTitle("title")
           ->setSubject("subject")
           ->setDescription("description")
           ->setKeywords("keywords")
           ->setCategory("Category");
           
           $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
           ->setCellValue("A1", "产品名称")
           ->setCellValue("B1", "付息时间")
           ->setCellValue("C1", "总额")
           ->setCellValue("D1", "支付利息")
           ->setCellValue("E1", "支付本金")
           ->setCellValue("F1", "融资方");
           
           $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setName('宋体')->setSize(11);
           $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
           $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
           $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
           $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
           $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
           $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
           
           
           if($res){               
               $pos = 2;
               foreach ($res as $val){        
                    
                   //$val['repayment_time'] = date("Y-m-d",strtotime($val['repayment_time']));
                   
                   $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['project_title']); 
                   $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$date);                    
                   $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['amt_total']); 
                   $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['interest_totle']); 
                   $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['capital_totle']); 
                   $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['f_name']); 
                   $pos += 1;
               }
           }
           
           header("Content-Type: application/vnd.ms-excel");
           header('Content-Disposition: attachment;filename="'.$date.'付息产品.xls"');
           header('Cache-Control: max-age=0');
           $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
           $objWriter->save('php://output');
           exit;
    }


    /**
     * 计算利息
     */
    private  function countInterest($amount,$start_time,$end_time,$interest,$day) {
        return $amount * $day * $interest /100 / 365;
    }

    /**
     * 导出Excel(宝付支付)
     */
    public function exporttoexcel(){
        vendor('PHPExcel.PHPExcel');
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID
        $act = I('get.act', 1, 'int'); // 导出动作(1:普通还款用户/2:还款到钱包的用户)

        $projectObj = M('Project');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('user');
        $userBankObj = M('UserBank');
        $investmentDetailObj = M('InvestmentDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            $this->error('项目不存在或已被删除');
            exit;
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            $this->error('还本付息条目不存在或已被删除');
            exit;
        }
        if($act == 2){
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and (to_wallet=1 or from_wallet=1)")->order('add_time desc')->select();
        }else{
            $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=0 and from_wallet=0")->order('add_time desc')->select();
        }
        $totle_capital = 0;
        $totle_interest = 0;
        $totle_count = count($list); // 总笔数

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("票票喵票据")
            ->setLastModifiedBy("票票喵票据")
            ->setTitle("title")
            ->setSubject("subject")
            ->setDescription("description")
            ->setKeywords("keywords")
            ->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')
            ->setCellValue("A1", "*收款方姓名")
            ->setCellValue("B1", "*收款方银行账号")
            ->setCellValue("C1", "*开户行所在省")
            ->setCellValue("D1", "*开户行所在市")
            ->setCellValue("E1", "*开户行名称")
            ->setCellValue("F1", "*收款方银行名称")
            ->setCellValue("G1", "*金额")
            ->setCellValue("H1", "商户订单号")
            ->setCellValue("I1", "商户备注");

        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];

            $bankInfo = $userBankObj->field('acct_name,area,bank_code,bank_address,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();

            if($bankInfo['bank_name'] == '邮政储蓄') {
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            }

            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['card_no']); // 收款银行账号

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,''); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,''); // 开户行所在市
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$bankInfo['bank_name']); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,($val['due_capital']+$val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,str_replace('RP', '', $val['repayment_no'])); // 商户订单号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,''); // 商户备注
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户付息表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }





    /*
     *
     */
    private function isUseBag($uid) {
        $cnt = M('UserRedenvelope')->where(array('user_id'=>$uid,'status'=>1))->count();
        if($cnt > 0) {
            return false;
        }
        $cnt = M('UserRedenvelope')->where(array('user_id'=>$uid,'status'=>0))->count();
        if($cnt > 0) {
            return true;
        }
        return false;
    }

    /*
     *
     */
    private function getBagAmount($userId,$orderId) {
        $amount = 0;
        if($userId && $orderId) {
            $amount = M('UserRedenvelope')->where(array('user_id'=>$userId,'recharge_no'=>$orderId,'status'=>1))->getField('amount');
        }
        return $amount;
    }


    /**
     * 产品分组管理
     * create_time 2016/08/02
     */
    public function project_group(){

        if(!IS_POST){
            $this->assign('list', M('ProjectGroup')->order('id asc')->select());
            $this->display();
        }
    }
}
<?php
namespace Admin\Controller;

/**
 * 订单管理控制器
 * @package Admin\Controller
 * 2017-12-08
 */

class OrderController extends AdminController{

    protected $depository_url = 'account/info' ;//

    public function depository_index(){
        // s_depository_log
        if(!IS_POST) {
            $start_datetime = I('get.st', '', 'strip_tags');
            $datetime = I('get.dt', '', 'strip_tags');

            $type = I('get.type', 1, 'int');

            if(!$start_datetime) $start_datetime = date('Y-m-d', strtotime('0 days'));
            if (!$datetime) $datetime = date('Y-m-d', strtotime('0 days'));

            $start_time = $datetime. ' 00:00:00';
            $end_time = $datetime .' 23:59:59';//date('Y-m-d H:i:s', strtotime($datetime) + (24 * 3600 - 1));


            $depositoryObj = M('depository_log');

            $depositoryWhere = array(
                'order_type' => array('in', array(6, 7)),// 6 充值 7 提现
               // 'order_status' => array('in', array(2, 3)),
                'order_status' => array('in', array( 3)),
               // 'amt'=>array('gt',0),
                'add_time' => array('between', array($start_time, $end_time))
            );


            $count = 20; // 每页显示条数
            $counts = $depositoryObj->where($depositoryWhere)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();


            $list = $depositoryObj->where($depositoryWhere)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            vendor('Fund.FD');
            vendor('Fund.sign');
            $fd = new \FD();
            foreach ($list as $k => $l) {


                $platcust = $l['add_platcust'];

                $where = array(
                    'platcust' => $platcust
                );

                $mobile = M('user')->where($where)->getField('mobile');

                $list[$k]['mobile'] = isset($mobile) ? trim($mobile) : '';

                $order_no = $l['order_no'];
                $data = array(
                    'query_order_no' => $order_no
                );

                $rest_params = json_decode($l['rest_params'],true);

                $plainText = \SignUtil::params2PlainText($data);
                $sign = \SignUtil::sign($plainText);
                $data['signdata'] = $sign;

                $json = $fd->post('trade/orderinfo', $data);

                $dataReg = json_decode($json);

                if($dataReg->result->status == 21){ // 银行成功
                    $list[$k]['order_bank_status'] = $dataReg->result->status;
                    $list[$k]['error_bank_msg'] = $dataReg->errorMsg;

                }else {
                    unset($list[$k]);
                }
            }


            $this->assign('show', $show);
            $this->assign('startTime', $start_datetime);
            $this->assign('datetime', $datetime);
            $this->assign('type',$type);
            $this->assign('list',$list);
            $this->display();
        } else{
            $datetime = I('post.dt');
            $stdatetime = I('post.st');
            $flushcache = I('post.flushcache');
            $quest = '';
        //    if($stdatetime) $quest .= '/st/'.$stdatetime;
            if($datetime) $quest .= '/dt/'.$datetime;
            if($flushcache) $quest .= '/uc/1';
            redirect(C('ADMIN_ROOT') . '/Order/depository_index'.$quest);
        }
    }


    /*
    * 修复掉单
    */
    public function depository_save(){
        $idArr = I('post.idArr',array());

        if(!$idArr){
            $this->ajaxReturn(array('status'=>0,'info'=>'请选在一个数据'));
        }


        $where = array(
            's_depository_log.id'=>array('in',$idArr),
            //'s_depository_log.order_status'=>array('in',array(2,3)),//等待修复
            's_depository_log.order_status'=>array('in',array(3)),//等待修复
            's_depository_log.order_type'=>array('in',array(6,7)),// 6 充值 7 提现
        );
        $model = M();
        $model->startTrans(); // 开启事务

        $userWalletRecordsObj = M('user_wallet_records');
        $userObj = M('user');


        $userAccountObj  = M('user_account');
        $depositoryLogObj =M('depository_log');

        $list = M('depository_log')->field('s_depository_log.id as id,u.id as user_id,u.real_name,u.mobile,
            s_depository_log.order_no,s_depository_log.add_platcust,s_depository_log.rest_params,s_depository_log.amt,s_depository_log.add_time,s_depository_log.order_type')
            ->join('s_user as u ON s_depository_log.add_platcust = u.platcust', 'LEFT')
            ->where($where)->order('s_depository_log.id asc')
            ->select();



        if(!$list){
            $this->ajaxReturn(array('status' => 0, 'info' => '没有修复的数据！'));
        }


        $is_commit = true; //  是否commit
        $s = 0;//成功的个数
        $count = count($list); // 执行的总数
        foreach ($list as $l) {

            $model = M();
            $model->startTrans(); // 开启事务

            $orderTypeStatus = $l['order_type'];

            //$rest_params = json_decode($l['rest_params'],true);

            $order_no = isset($l['order_no'])?trim($l['order_no']):'';//I('get.order_no','','strip_tags');
            $user_id  = isset($l['user_id'])?intval($l['user_id']):0;//I('get.user_id',0,'int');
            //$platcust = isset($rest_params['plat_no'])?trim($rest_params['plat_no']):'';//I('get.platcust','','strip_tags');
            $platcust = isset($l['add_platcust'])?trim($l['add_platcust']):'';//I('get.platcust','','strip_tags');
            $amt = isset($l['amt'])?trim($l['amt']):0;//I('get.amt','','string');//修复金额

            $id = isset($l['id'])?intval($l['id']):0;

            if(empty($user_id)){
                $remark = '订单号为：'.$order_no.',用户不存在';
                $model->rollback();
                $this->setRemark($id,$remark);
                continue;
                $this->ajaxReturn(array('status' => 0, 'info' => '订单号为：'.$order_no.',用户不存在！'));
            }
            else if (!$userObj->where(array('platcust' => $platcust))->find()) { // user_wallet_records 是否有电子账户
                $is_commit = false;
                $remark = '银行存管客户编号不存在';
                $model->rollback();
                $this->setRemark($id,$remark);
                continue;
                $this->ajaxReturn(array('status' => 0, 'info' => '修复失败,银行存管客户编号不存在！'));
            }
            else if (!$userAccountObj->where(array('user_id' => $user_id))->find()) { // user_account 是否有这个用户
                $is_commit = false;
                $remark = '账单用户不存在';
                $model->rollback();
                $this->setRemark($id,$remark);
                continue;
                $this->ajaxReturn(array('status' => 0, 'info' => '修复失败,账单用户不存在！'));
            } else {
                $accountWhere = array(
                    'user_id' => array('gt', 0),
                    'user_id' => $user_id,
                );

                vendor('Fund.FD');
                vendor('Fund.sign');
                $dataRefund['account'] = $platcust;
                $plainText = \SignUtil::params2PlainText($dataRefund);
                $sign = \SignUtil::sign($plainText);
                $dataRefund['signdata'] = $sign;

                $fd  = new \FD();
                if ($jsonArr = $fd->post($this->depository_url, $dataRefund)) { // 厦门接口账户余额明细查询

                    $jsonData = json_decode($jsonArr);

                    $success = $jsonData->success;
                    if ($success) {
                        $result = $jsonData->result;

                        $balance = isset($result->balance) ? trim($result->balance) : 0;


                        $balance = sprintf('%.2f', $balance);



                        $wallet_totle = $this->getWalletTotle($accountWhere,$platcust,$order_no);


                        if ($balance == $wallet_totle) { // 对比金额 // $balance == $wallet_totle
                            $dateTime = date('Y-m-d H:i:s',time());
                            $dataSave = array('order_status'=>1,'modify_time'=>$dateTime);
                            if($depositoryLogObj->where(array('id'=>$id))->save($dataSave)) {

                                $is_commit = $this->getTypeStatus($model,$l,$orderTypeStatus,$accountWhere); // 成功相关操作

                                if(!$is_commit){
                                    $model->rollback();
                                    $remark = '充值，提现相关错误';
                                    $this->setRemark($id,$remark);
                                    continue;
                                    return ;
                                }

                                $model->commit();
                                $s++;
                            }else{
                                $is_commit = false;
                                $model->rollback();
                                $remark = '修改掉单状态失败';
                                $this->setRemark($id,$remark);
                                continue;
                                $this->ajaxReturn(array('status' => 0, 'info' => '修改掉单状态失败！'));
                            }
                        }else{
                            $is_commit = false;
                            $model->rollback();
                            $remark = '金额不对';
                            $this->setRemark($id,$remark);
                            continue;
                            $this->ajaxReturn(array('status' => 0, 'info' => '修复失败,金额不对！'));
                        }
                    } else {
                        $is_commit = false;
                        $model->rollback();
                        $remark = $jsonData->errorMsg;
                        $this->setRemark($id,$remark);
                        continue;
                        $errorMsg = $jsonData->errorMsg;
                        $this->ajaxReturn(array('status' => 0, 'info' => $errorMsg));
                    }
                }



            }

        }



        $j = $count-$s; //错误个数
        $msgArr = array();// 信息数组

        if(!$s){
            $this->ajaxReturn(array('status' => 0,'info'=>'修复失败!'));
        }

        if($s) $msgArr[] = '成功修复'.$s.'条订单';
        if($j) $msgArr[] = $j.'条修复失败';

        $msg = implode(',',$msgArr);


        $this->ajaxReturn(array('status' => 1,'info'=>$msg));



//        if($is_commit) { // 是否 都正确
//            $model->commit(); // 所有 验证正确 验证
//            $this->ajaxReturn(array('status' => 1));
//        }else{
//            $model->rollback();
//        }

    }

    /*
     * 修复成功的数据
     */

    public function depository_succ(){
        if(!IS_POST) {
            $startTime = I('get.st', '', 'strip_tags');
            $datetime = I('get.dt', '', 'strip_tags');
            if (!$datetime) $datetime = date('Y-m-d', strtotime('0 days'));
            if (!$startTime) $startTime = date('Y-m-d', strtotime('0 days'));

            $start_time = $startTime . ' 00:00:00';
            $end_time = $datetime . ' 23:59:59';//date('Y-m-d H:i:s', strtotime($datetime) + (24 * 3600 - 1));


            $depositorySuccObj = M('depository_succ');

            $depositoryWhere = array(
                'order_type' => array('in', array(6, 7)),// 6 充值 7 提现
                'order_status' => array('in', array(1)),
                'modify_time' => array('between', array($start_time, $end_time))
            );
            $count = 20; // 每页显示条数
            $counts = $depositorySuccObj->where($depositoryWhere)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();


            $list = $depositorySuccObj->where($depositoryWhere)->order('modify_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();



            $this->assign('show', $show);
            $this->assign('startTime', $startTime);
            $this->assign('datetime', $datetime);
            $this->assign('list',$list);
            $this->display();
        }else{
            $start_datetime = I('post.st');
            $datetime = I('post.dt');
            $flushcache = I('post.flushcache');
            $quest = '';
            if($start_datetime) $quest .= '/st/'.$start_datetime;
            if($datetime) $quest .= '/dt/'.$datetime;
            if($flushcache) $quest .= '/uc/1';
            redirect(C('ADMIN_ROOT') . '/order/depository_succ'.$quest);
        }
    }


    private function getTypeStatus($model,$val,$orderTypeStatus,$accountWhere){
        $is_commit = true;
        $userAccountObj = M('UserAccount');
        $userWalletRecordsObj = M('UserWalletRecords');
        $depositorySuccObj = M('DepositorySucc');

        $dateTime = date('Y-m-d H:i:s',time());
        $user_id = $val['user_id'];
        $amt = $val['amt'];

        // 6 充值 7 提现   充值累加  提现累减
        if($orderTypeStatus== 6){
            if(!$userAccountObj->where($accountWhere)->setInc('wallet_totle', $amt)){ //累加$amt
                $is_commit = false;
                $model->rollback();
               // $this->ajaxReturn(array('status' => 0, 'info' => '修复失败,累加金额失败'));
            }

            $dataAdd = array(
                'recharge_no'=>$val['order_no'],
                'user_id'=>$user_id,
                'value'=>$amt,
                'type'=>1,
                'pay_status'=>2,
                'add_time'=>$val['add_time'],
                'modify_time'=>$dateTime,
                'remark'=>'修复充值订单'
            );

            $error_msg =  '充值订单';

        }else if($orderTypeStatus == 7){
            $rest_params = $val['rest_params'];
            $rest_params = json_decode($rest_params,true);

            $amt = isset($rest_params['amt'])?trim($rest_params['amt']):0;
            if(!$userAccountObj->where($accountWhere)->setDec('wallet_totle', $amt)) { //累减$amt
                $is_commit = false;
                $model->rollback();
               // $this->ajaxReturn(array('status' => 0, 'info' => '修复失败,累减金额失败'));
            }

            $dataAdd = array(
                'trade_no'=>$val['order_no'],
                'user_id'=>$user_id,
                'value'=>$amt,
                'type'=>2,
                'status'=>1,
                'add_time'=>$val['add_time'],
                'modify_time'=>$dateTime,
                'remark'=>'修复提现订单'
            );

            $error_msg =  '提现订单';
        }

        if(!$userWalletRecordsObj->add($dataAdd)) {
            $is_commit = false;
            $model->rollback();
           // $this->ajaxReturn(array('status' => 0, 'info' => '修复订单失败'));
        }

        //成功插入 $depositorySuccObj 表

        $platcust = $val['add_platcust'];

        $where = array(
            'platcust' => $platcust
        );

        $mobile = M('user')->where($where)->getField('mobile');
        $mobile = isset($mobile) ? trim($mobile) : '';

        $succData = array(
            'order_no'=>$val['order_no'],
            'order_type'=>$orderTypeStatus,
            'order_status'=>1,
            'amt'=>$amt,
            'platcust'=>$platcust,
            'mobile'=>$mobile,
            'add_time'=>$val['add_time'],
            'modify_time'=>$dateTime,
            'error_msg'=> $error_msg
        );

        if(!$depositorySuccObj->add($succData)){
            $is_commit = false;
            $model->rollback();
          //  $this->ajaxReturn(array('status' => 0, 'info' => '添加成功数据失败！'));
        }

        return $is_commit;
    }

    private function getWalletTotle($accountWhere,$platcust,$order_no=null){
        $wallet_totle =  M('user_account')->where($accountWhere)->getField('wallet_totle');


        $infoWhere = array(
            'add_platcust'=>$platcust,
           // 'order_status'=>array('in',array(2,3)),//等待修复
            'order_status'=>array('in',array(3)),//等待修复
            'order_type'=>array('in',array(6,7)),// 6 充值 7 提现
        );

        $infoWhere['order_no'] = $order_no;

        $info = M('depository_log')->field('amt,rest_params,order_type')->where($infoWhere)->select();

        foreach ($info as $in){
            if($in['order_type'] == 6){ //dump('+'.$in['amt']);
                $wallet_totle =  $wallet_totle + $in['amt'];
            }else if($in['order_type'] == 7){
                $info_rest_params = json_decode($in['rest_params'],true); //dump('-'.$info_rest_params['amt']);
                $wallet_totle =  $wallet_totle-$info_rest_params['amt'];
            }
        }




        $wallet_totle = sprintf('%.2f', $wallet_totle);

        return $wallet_totle;
    }

    private function setRemark($id,$remark){  //   失败添加错误信息
        $depositoryLogObj = M('depositoryLog');
        $data = array('remark'=>$remark);

        $depositoryLogObj->where('id = '.$id)->save($data);
    }


}
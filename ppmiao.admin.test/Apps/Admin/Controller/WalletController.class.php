<?php
namespace Admin\Controller;

/**
 * 钱包控制器
 * @package Admin\Controller
 */
class WalletController extends AdminController{
	 
    protected  $pay_bank_arr;
	 
	private  $pageSize = 25; 
    //初始化
    public function _initialize(){

        $this->pay_bank_arr=array(
            '0'=>'中国工商银行',
            '1'=>'中国农业银行',
            '2'=>'中国银行',
            '3'=>'中国建设银行',
            '4'=>'招商银行',
            '5'=>'浦发银行',
            '6'=>'中国光大银行',
            '7'=>'平安银行',
            '8'=>'华夏银行',
            '9'=>'兴业银行',
            '10'=>'中信银行',
            '11'=>'中国邮政储蓄银行',
            '12'=>'广发银行',
            '13'=>'中国民生银行',
            '14'=>'交通银行',
        );
    }

    /**
     * 用户存钱记录(转入)（查询）
     * 
     * 加入红包。。。。
     * 最后修改时间： 2016.04.26 
     * 
     */
    public function deposit(){
        if(!IS_POST){
            $yestoday = date('Y-m-d', strtotime('-2 days'));
            $page = I('get.p', 1, 'int'); // 页码
            $ei = I('get.ei', 2, 'int');
            
            $type = I('get.type',0,'int');
            
            $start_time = I('get.st', $yestoday.' 00:00:00', 'strip_tags');
            $end_time = I('get.et', $yestoday.' 23:59:59', 'strip_tags');
            $params = array(
                'page' => $page,
                'ei' => $ei,
                'start_time' => str_replace('|', ' ', $start_time),
                'end_time' => str_replace('|', ' ', $end_time),
                'type'=>$type,
            );
            $this->assign('params', $params);
            
            if($ei != 2) {
                $cond[] = "enable_interest=".$ei;
            }
            
            $cond[] = "user_id > 0 ";
            
            switch ($type) {
                case 0://所有的
                    $cond[] = "((type=1 and pay_status=2) or type=2)";
                    break;
                case 1://提现到银行卡
                    $cond[] = "(type = 2 and user_bank_id> 0 and user_due_detail_id = 0)";
                    break;
                case 2://钱包购买产品
                    $cond[] = "(type = 2 and user_due_detail_id > 0)";
                    break;
                case 3://钱包充值
                    $cond[] = "(type = 1 and pay_status = 2 and pay_type IN (1,4,5))";
                    break;
                case 4://还款至零钱包
                    $cond[] = "(type = 1 and pay_status = 2 and pay_type = 0)";
                    break;
                case 5://现金奖励
                    $cond[] = "(type = 1 and pay_type = 3 and pay_status = 2)";
                    break;
            }
            
            $where[] = 'user_id>0';
            
            if ($start_time) {
                $cond[] = "add_time>='" . $start_time . " 00:00:00'";
                $where[] = "add_time>='" . $start_time . " 00:00:00'";
            }
            if ($end_time) {
                $cond[] = "add_time<='" . $end_time . " 23:59:59.999000'";
                $where[] = "add_time<='" . $end_time . " 23:59:59.999000'";
            }
            
            $conditions = implode(' and ', $cond);

            $userWalletRecordsObj = M('UserWalletRecords');
            
            $userObj = M('User');
            
            $counts = $userWalletRecordsObj->where($conditions)->count();
            
            $Page = new \Think\Page($counts, $this->pageSize);
            
            $show = $Page->show();
            
            $list = $userWalletRecordsObj->where($conditions)
                        ->field('id,user_due_detail_id,type,recharge_no,user_id,add_time,value,enable_interest,pay_type,pay_status,user_bank_id')
                        ->order('add_time asc')
                        ->limit($Page->firstRow . ',' . $Page->listRows)
                        ->select();

            $ids = '';
            
            $res = array();
            
            $total_bag_amount = 0;
            
            foreach ($list as $val) {
                
                $val['bag_amount'] = ' -- ';
                
                if($val['type'] == 2) {
                    
                    
                    if($val['user_bank_id'] > 0 && $val['user_due_detail_id'] == 0){
                        $val['do'] = '提现到银行卡';
                    }
                    
                    if($val['user_due_detail_id'] > 0) {

                        $val['do'] = '钱包购买产品';
                        
                        $val['bag_amount'] = M('UserRedenvelope')->where(array('recharge_no'=>$val['recharge_no']))->getField("amount");
                        
                        if( $val['bag_amount'] > 0) {
                            $total_bag_amount += $val['bag_amount'];
                            $val['value'] -= $val['bag_amount'];
                        }
                    }
                    
                }else{
                    
                    if($val['pay_type'] == 3 && $val['pay_status'] == 2){
                        $val['do'] = '现金奖励';
                    }
                    
                    if($val['pay_status'] == 2 && ($val['pay_type'] ==1 || $val['pay_type']==4 || $val['pay_type'] == 5)){
                        $val['do'] = '钱包充值   ';
                    }
                    
                    if($val['pay_status'] == 2 && $val['pay_type'] == 0) {
                        $val['do'] = '还款至零钱包';
                    }
                }
                
                $ids .= ','.$val['id'];
                
                $val['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                
                $res[] = $val;
            }
            
            $total_in = 0;
            $total_out = 0;
            
            if($type == 0) {
                $w = implode(' and ', $where);
                $total_in = $userWalletRecordsObj->where($w.' and type = 1 and pay_status=2')->sum('value');
                $total_out = $userWalletRecordsObj->where($w.' and type = 2')->sum('value');
            } else if($type == 1){
               $total_in = $userWalletRecordsObj->where($conditions)->sum('value');
            } else {
               $total_out = $userWalletRecordsObj->where($conditions)->sum('value');
            }
            
            if($ids) $ids = substr($ids, 1);
            
            $this->assign('yestoday', $yestoday);
            $this->assign('cnt', $counts);
            $this->assign('ids', $ids);
            $this->assign('show', $show);
            $this->assign('list', $res);
            
            $this->assign('total_in',$total_in);
            $this->assign('total_out',abs($total_out));
            $this->assign('total_bag_amount',$total_bag_amount);
            
            $this->display();
        }else{
            
            $ei = I('post.enable_interest', 2, 'int');
            $type = I('post.type', 0, 'int');
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            
            $quest = '/ei/'.$ei;
            $quest .= '/type/'.$type;
            
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            redirect(C('ADMIN_ROOT') . '/wallet/deposit'.$quest);
        }
    }
    
    /*
     *  转入/转出(查) 导出
     */
    public function depositExportExcel(){
        
        $yestoday = date('Y-m-d', strtotime('-2 days'));           
        $ei = I('get.ei', 2, 'int');
        $type = I('get.type',0,'int');
        
        $start_time = urldecode(I('get.start_time', $yestoday.' 00:00:00', 'strip_tags'));
        $end_time = urldecode(I('get.end_time', $yestoday.' 23:59:59', 'strip_tags'));
    
        if($ei != 2) {
            $cond[] = "uwr.enable_interest=".$ei;
        }
        
        $cond[] = 'uwr.user_id > 0';
        if($start_time) $cond[] = "uwr.add_time>='".$start_time."'";
        if($end_time) $cond[] = "uwr.add_time<='".$end_time."'";
        
        switch ($type) {
            case 0://所有的
                $cond[] = "((uwr.type=1 and uwr.pay_status=2) or uwr.type=2)";
                break;
            case 1://提现到银行卡
                $cond[] = "(uwr.type = 2 and uwr.user_bank_id> 0 uwr.and user_due_detail_id = 0)";
                break;
            case 2://钱包购买产品
                $cond[] = "(uwr.type = 2 and uwr.user_due_detail_id > 0)";
                break;
            case 3://钱包充值
                $cond[] = "(uwr.type = 1 and uwr.pay_status = 2 and uwr.pay_type IN (1,4,5,6))";
                break;
            case 4://还款至零钱包
                $cond[] = "(uwr.type = 1 and uwr.pay_status = 2 and uwr.pay_type = 0)";
                break;
            case 5://现金奖励
                $cond[] = "(uwr.type = 1 and uwr.pay_type = 3 and uwr.pay_status = 2)";
                break;
        }
        
        $conditions = implode(' and ', $cond);
        
        $sql = "SELECT uwr.user_due_detail_id, uwr.type, uwr.recharge_no, uwr.user_id, uwr.add_time
	               , uwr.value, uwr.enable_interest, uwr.pay_type, uwr.pay_status, uwr.user_bank_id
	               , u.username, u.real_name
                FROM `s_user_wallet_records` uwr LEFT JOIN s_user u ON uwr.user_id = u.id
                WHERE $conditions ORDER BY uwr.id ASC";
                                                    
        $list = M()->query($sql);        
        if(!$list) exit('没有记录');
        
        vendor('PHPExcel.PHPExcel');        
        $objPHPExcel = new \PHPExcel();        
        $objPHPExcel->getProperties()
                    ->setCreator("票票喵票据")
                    ->setLastModifiedBy("票票喵票据")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包 转入转出')
                    ->setCellValue("A1", "序号")
                    ->setCellValue("B1", "账号")
                    ->setCellValue("C1", "姓名")
                    ->setCellValue("D1", "变动金额")
                    ->setCellValue("E1", "红包金额")
                    ->setCellValue("F1", "是否计息")
                    ->setCellValue("G1", "交易时间")
                    ->setCellValue("H1", "描述");
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
       
        $pos = 2;
        $n = 1;
        
        foreach ($list as $val) {
            
            $val['bag_amount'] = 0;    
            
            if($val['type'] == 2) {                
                if($val['user_bank_id'] > 0 && $val['user_due_detail_id'] == 0){
                    $val['do'] = '提现到银行卡';
                }
                if($val['user_due_detail_id'] > 0) {
                    $val['do'] = '钱包购买产品';
                    $val['bag_amount'] = M('userRedenvelope')->where(array('user_id'=>$val['user_id'],'recharge_no'=>$val['recharge_no']))->getField("amount");
                    if(!$val['bag_amount']) {
                        $val['bag_amount'] = 0;
                    } else {
                        $val['value'] -= $val['bag_amount'];
                    }
                }
            }else{
                if($val['pay_type'] == 3 && $val['pay_status'] == 2){
                    $val['do'] = '现金奖励';
                }
                if($val['pay_status'] == 2 && ($val['pay_type'] ==1 || $val['pay_type']==4 || $val['pay_type'] == 5 || $val['pay_type'] == 6)){
                    $val['do'] = '钱包充值   ';
                }
                if($val['pay_status'] == 2 && $val['pay_type'] == 0) {
                    $val['do'] = '还款至零钱包';
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['username']);            
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['value'], \PHPExcel_Cell_DataType::TYPE_NUMERIC); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos,$val['bag_amount']);            
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['bag_amount'] == 1 ?'已计息':'未计息');            
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,date('Y-m-d H:i:s',strtotime($val['add_time'])));            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['do']); // 金额
            $pos += 1;
        }

        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="钱包管理 - 转入/转出(查)('.date("Y-m-d H:i:s").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 用户存钱记录（转入）（审核）
     */
    public function deposit_do(){
        if(!IS_POST){
            $yestoday = date('Y-m-d', strtotime('-1 days')); // 昨日
            $page = I('get.p', 1, 'int'); // 页码
            $start_time = I('get.st', date('Y-m-d', time()), 'strip_tags');
            $params = array(
                'page' => $page,
                'start_time' => $start_time,
            );
            $befor_yestoday = date('Y-m-d', strtotime('-1 days', strtotime($start_time))); // 指定日期前一日
            $befoe_befor_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($start_time))); // 指定日期前前一日
            $this->assign('params', $params);
            $count = 20; // 每页显示条数
            
            $cond[] = "(user_id > 0 and type=1 and pay_status=2 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转入
            $cond[] = "(user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$befoe_befor_yestoday." 15:00:01.000000' and add_time<='".$befor_yestoday." 15:00:00.999000')"; // 转出（提现）
            $cond[] = "(user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$befor_yestoday." 00:00:00.000000' and add_time<='".$befor_yestoday." 23:59:59.999000')"; // 转出（购买产品）
            $conditions = implode(' or ', $cond);
            $conditions = "(".$conditions.") and enable_interest=0";

            $userWalletRecordsObj = M('UserWalletRecords');
            $userObj = M('User');
            $counts = $userWalletRecordsObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userWalletRecordsObj->where($conditions)->order('add_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $ids = '';
            foreach($list as $key => $val){
                $ids .= ','.$val['id'];
                $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
            }
            if($ids) $ids = substr($ids, 1);

            $this->assign('yestoday', $yestoday);
            $this->assign('ids', $ids);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $quest = '';
            if($start_time) $quest .= '/st/'.$start_time;
            redirect(C('ADMIN_ROOT') . '/wallet/deposit_do'.$quest);
        }
    }

    /**
     * 用户取钱记录(转出,提现)
     */
    public function takeout(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $status = I('get.status', 3, 'int');
            $bank = I('get.bank',15,'int');
            $start_time = I('get.st', date('Y-m-d 15:00:00', time()-24*60*60), 'strip_tags');
            $end_time = I('get.et', date('Y-m-d 15:00:00', time()), 'strip_tags');
            $params = array(
                'page' => $page,
                'status' => $status,
                'bank' =>$bank,
                'start_time' => str_replace('|', ' ', $start_time),
                'end_time' => str_replace('|', ' ', $end_time),
            );
            $this->assign('params', $params);
            $count = 20; // 每页显示条数

            if($bank !=15){
                
                $cond[] = "a.user_id>0";
                
                $cond[] = "a.type=2";
                $cond[] = "a.user_bank_id>0";
                $cond[] = "a.user_due_detail_id=0";
                $cond[] = "a.user_id = b.user_id";
                $cond[] = "a.user_bank_id = b.id";
                if ($this->pay_bank_arr[$bank]) $cond[] = "b.bank_name = '" . $this->pay_bank_arr[$bank] . "'";
                if($status) $cond[] = "a.status=".$status;
                if($start_time) $cond[] = "a.add_time>='".$start_time." 15:00:00.000000'";
                if($end_time) $cond[] = "a.add_time<='".$end_time." 15:00:00.000000'";
                $conditions = implode(' and ', $cond);

                //$userWalletRecordsObj = M('UserWalletRecords');
                $userObj = M('User');
                $counts = M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->count();
                $Page = new \Think\Page($counts, $count);
                $show = $Page->show();
                $list = M()->table('s_user_wallet_records a,s_user_bank b')->field('a.id,a.add_time,a.value,a.status,a.user_id,b.bank_name')->where($conditions)->order('a.add_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $totle = 0; // 总的提现金额
                foreach($list as $key => $val){
                    $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                }
                $totle = M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->sum('value');

            } else {//全部
                
                $cond[] = "a.user_id>0";
                
                $cond[] = "a.type=2";
                
                $cond[] = "a.user_bank_id>0";
                
                $cond[] = "a.user_due_detail_id=0";
                
                $cond[] = "a.user_bank_id = b.id";
                
                if($status) $cond[] = "a.status=".$status;
                
                if($start_time) $cond[] = "a.add_time>='".$start_time." 15:00:00.000000'";
                
                if($end_time) $cond[] = "a.add_time<='".$end_time." 15:00:00.000000'";
                
                $conditions = implode(' and ', $cond);

                //$userWalletRecordsObj = M('UserWalletRecords');
                
                $userObj = M('User');
                
                $counts = M()->table('s_user_wallet_records a,s_user_bank b')->field('a.id,a.add_time,a.value,a.status,a.user_id,b.bank_name')->where($conditions)->count();
                
                $Page = new \Think\Page($counts, $count);

                $show = $Page->show();

                $list = M()->table('s_user_wallet_records a,s_user_bank b')->field('a.id,a.add_time,a.value,a.status,a.user_id,b.bank_name')->where($conditions)->order('a.add_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                                
                $totle = 0; // 总的提现金额
                foreach($list as $key => $val){
                    
                    $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
                }

                $totle = M()->table('s_user_wallet_records a,s_user_bank b')->field('a.id,a.add_time,a.value,a.status,a.user_id,b.bank_name')->where($conditions)->sum('value');
            }
            $this->assign('counts', $counts);
            $this->assign('totle', $totle);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        } else {
            $status = I('post.status', 0, 'int');
            $bank = I('post.bank', 0, 'int');
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $quest = '/status/'.$status.'/bank/'.$bank;
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            redirect(C('ADMIN_ROOT') . '/wallet/takeout'.$quest);
        }
    }

    /**
     * 审核金额
     */
    public function audit_amount(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $startTime = I('post.st', date('Y-m-d', time()), 'strip_tags');
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($startTime)));
        $before_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($startTime)));

        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');
        
        //array('id'=>$id,'enable_interest'=>0)
        
        $detail = $userWalletRecordsObj->where("id = ".$id.' and enable_interest = 0 and user_id > 0')->find();
        
        if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'审核信息不存在或已被审核通过'));

        if($detail['add_time'] > $before_yestoday.' 23:59:59.999000') $this->ajaxReturn(array('status'=>0,'info'=>'未到操作时间'));
        if($detail['type'] == 1){ // 转入
            $userWalletRecordsObj->startTrans();
            if(!$userAccountObj->where(array('user_id'=>$detail['user_id']))->setInc('wallet_enable_interest', $detail['value'])) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            if(!$userWalletRecordsObj->where(array('id'=>$id,'enable_interest'=>0))->save(array('enable_interest'=>1,'modify_time'=>date('Y-m-d H:i:s',time()).'.'.getMillisecond().'000'))){
                $userWalletRecordsObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
            $userWalletRecordsObj->commit();
            $this->ajaxReturn(array('status'=>1));
        }else if($detail['type'] == 2){ // 转出
            $value = abs($detail['value']); // 转出金额
            $userInfo = $userAccountObj->where(array('user_id'=>$detail['user_id']))->find(); // 用户账户信息
            if($value > $userInfo['wallet_enable_interest']) $this->ajaxReturn(array('status'=>0,'info'=>'转出金额大于可计息金额,数据异常,请检查'));
            if(!$userAccountObj->where(array('user_id'=>$detail['user_id']))->setDec('wallet_enable_interest', $value)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            $userWalletRecordsObj->startTrans();
            if (!$userWalletRecordsObj->where(array('id' => $id, 'enable_interest' => 0))->save(array('enable_interest' => 1))) {
                $userWalletRecordsObj->rollback();
                $this->ajaxReturn(array('status' => 0, 'info' => '操作失败,请重试'));
            }
            $userWalletRecordsObj->commit();
            $this->ajaxReturn(array('status'=>1));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
        }
    }

    /**
     * 审核一页金额
     */
    public function audit_amount_page(){
        if(!IS_POST || !IS_AJAX) exit;

        $ids = I('post.ids', '', 'strip_tags');
        $startTime = I('post.st', date('Y-m-d', time()), 'strip_tags');
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($startTime)));
        $before_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($startTime)));

        $userWalletRecordsObj = M('UserWalletRecords');
        $userAccountObj = M('UserAccount');

        if(!$ids) $this->ajaxReturn(array('status'=>0,'info'=>'没有可审核的金额'));
        $list = $userWalletRecordsObj->where("id in (".$ids.") and enable_interest=0 and user_id > 0")->select();
        if(!$list) $this->ajaxReturn(array('status'=>0,'info'=>'没有可审核的金额'));

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
                    }
                }
            }
        }
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 支付提取金额
     */
    public function extraction_amount(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $isBatch = I('post.isBatch', 0, 'int'); // 是否批量操作(0:否/1:是)
        $st = I('post.st', '', 'strip_tags'); // 开始时间
        $et = I('post.et', '', 'strip_tags'); // 结束时间
        $bank = I('post.bk',15,'int');//银行卡标识
        $bank_name = $this->pay_bank_arr[$bank];
        $userWalletRecordsObj = M('UserWalletRecords');
        if($bank == 15){//全部
            if(!$isBatch) { // 单条处理
                $detail = $userWalletRecordsObj->where(array('id'=>$id,'type'=>2,'status'=>3))->find();
                if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'提现记录不存在或该笔记录已被操作'));
                $rows = array(
                    'status' => 2,
                    'modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!$userWalletRecordsObj->where(array('id'=>$id,'type'=>2,'status'=>3))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else{
                $cond[] = 'user_id > 0';
                $cond[] = 'type=2';
                $cond[] = 'status=3';
                if($st) $cond[] = "add_time>='".$st."'";
                if($et) $cond[] = "add_time<='".$et."'";
                $conditions = implode(' and ', $cond);
                $count = $userWalletRecordsObj->where($conditions)->count();
                if(!$count) $this->ajaxReturn(array('status'=>0,'info'=>'没有可以批量操作的记录'));
                $rows = array(
                    'status' => 2,
                    'modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!$userWalletRecordsObj->where($conditions)->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }
        }else{//银行
            if(!$isBatch) { // 单条处理array('a.id'=>$id,'a.type'=>2,'a.status'=>3
                $detail = M()->table('s_user_wallet_records a,s_user_bank b')->where("a.id=$id and a.type=2 and a.status=3 and a.user_id = b.user_id and a.user_bank_id = b.id and b.bank_name='$bank_name'")->find();
                if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'提现记录不存在或该笔记录已被操作'));
                $rows = array(
                    'a.status' => 2,
                    'a.modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                //M()->table('s_user_wallet_records a,s_user_bank b')->where("a.id=$id and a.type=2 and a.status=3 and a.user_id = b.user_id and b.bank_name='$bank_name'")
                $update_status = M()->table('s_user_wallet_records a,s_user_bank b')->where("a.id=$id and a.type=2 and a.status=3 and a.user_id = b.user_id and a.user_bank_id = b.id and b.bank_name='$bank_name'")->save($rows);
                if(!$update_status) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else{
                $cond[] = 'a.user_id > 0';
                $cond[] = 'a.type=2';
                $cond[] = 'a.status=3';
                if($st) $cond[] = "a.add_time>='".$st."'";
                if($et) $cond[] = "a.add_time<='".$et."'";
                $cond[] = "a.user_id = b.user_id";
                $cond[] = "a.user_bank_id = b.id";
                if($this->pay_bank_arr[$bank]) $cond[]="b.bank_name = '".$this->pay_bank_arr[$bank]."'";
                $conditions = implode(' and ', $cond);
                $count = M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->count();
                if(!$count) $this->ajaxReturn(array('status'=>0,'info'=>'没有可以批量操作的记录'));
                $rows = array(
                    'a.status' => 2,
                    'a.modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }
        }
    }
    /**
     * 支付完成提取金额完成
     */
    public function extraction_amount_finish(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $isBatch = I('post.isBatch', 0, 'int'); // 是否批量操作(0:否/1:是)
        $st = I('post.st', '', 'strip_tags'); // 开始时间
        $et = I('post.et', '', 'strip_tags'); // 结束时间
        $bank = I('post.bk',15,'int');//银行卡标识
        $bank_name = $this->pay_bank_arr[$bank];

        $userWalletRecordsObj = M('UserWalletRecords');
        if($bank == 15){//全部
            if(!$isBatch){ // 单条处理
                $detail = $userWalletRecordsObj->where(array('id'=>$id,'type'=>2,'status'=>2))->find();
                if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'提现记录不存在或该笔记录已被操作'));
                $rows = array(
                    'status' => 1,
                    'modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!$userWalletRecordsObj->where(array('id'=>$id,'type'=>2,'status'=>2))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else{ // 批量处理
                $cond[] = 'user_id > 0';
                $cond[] = 'type=2';
                $cond[] = 'status=2';
                if($st) $cond[] = "add_time>='".$st."'";
                if($et) $cond[] = "add_time<='".$et."'";
                $conditions = implode(' and ', $cond);
                $count = $userWalletRecordsObj->where($conditions)->count();
                if(!$count) $this->ajaxReturn(array('status'=>0,'info'=>'没有可以批量操作的记录'));
                $rows = array(
                    'status' => 1,
                    'modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!$userWalletRecordsObj->where($conditions)->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }
        }else{//银行
            if(!$isBatch){ // 单条处理
                //$detail = $userWalletRecordsObj->where(array('id'=>$id,'type'=>2,'status'=>2))->find();
                $detail = M()->table('s_user_wallet_records a,s_user_bank b')->where("a.id=$id and a.type=2 and a.status=2 and a.user_id = b.user_id and a.user_bank_id = b.id and b.bank_name='$bank_name'")->find();
                if(!$detail) $this->ajaxReturn(array('status'=>0,'info'=>'提现记录不存在或该笔记录已被操作'));
                $rows = array(
                    'a.status' => 1,
                    'a.modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(!M()->table('s_user_wallet_records a,s_user_bank b')->where("a.id=$id and a.type=2 and a.status=2 and a.user_id = b.user_id and a.user_bank_id = b.id and b.bank_name='$bank_name'")->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else{ // 批量处理
                $cond[] = 'a.user_id > 0';
                $cond[] = 'a.type=2';
                $cond[] = 'a.status=2';
                if($st) $cond[] = "a.add_time>='".$st."'";
                if($et) $cond[] = "a.add_time<='".$et."'";
                $cond[] = "a.user_id = b.user_id";
                $cond[] = "a.user_bank_id = b.id";
                if($this->pay_bank_arr[$bank]) $cond[]="b.bank_name = '".$this->pay_bank_arr[$bank]."'";
                $conditions = implode(' and ', $cond);
                $count =  M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->count();
                if(!$count) $this->ajaxReturn(array('status'=>0,'info'=>'没有可以批量操作的记录'));
                $rows = array(
                    'a.status' => 1,
                    'a.modify_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                );
                if(! M()->table('s_user_wallet_records a,s_user_bank b')->where($conditions)->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }
        }
    }

    /**
     * 计息列表
     */
    public function interest(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $start_time = I('get.st', '', 'strip_tags');
            $params = array(
                'page' => $page,
                'start_time' => $start_time,
            );
            $this->assign('params', $params);
            $count = 20; // 每页显示条数
            $datetime = ($start_time ? $start_time : date('Y-m-d', strtotime('-1 days')));

            $userObj = M('User');
            $userAccountObj = M('UserAccount');
            $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");
            $conditions = "wallet_enable_interest>0 and wallet_last_interest_time<'".$datetime." 00:00:00.000000'";
            $counts = $userAccountObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userAccountObj->where($conditions)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
            }

            // 获取该日是否有利率记录
            $rateInfo = $userWalletAnnualizedRateObj->where(array('add_time'=>$datetime))->find();

            $this->assign('rate_info', $rateInfo);
            $this->assign('datetime', $datetime);
            $this->assign('interest', ($rateInfo['rate'] ? $rateInfo['rate'] : 6.35)); // 年化利率
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $quest = '';
            if($start_time) $quest .= '/st/'.$start_time;
            redirect(C('ADMIN_ROOT') . '/wallet/interest'.$quest);
        }
    }

    /**
     * 操作计息
     */
    public function interest_do(){
        if(!IS_POST || !IS_AJAX) exit;

        $id = I('post.id', 0, 'int');
        $datetime = I('post.dt', '', 'strip_tags');
        $rate = I('post.rate', 6.35, 'float');
        $isBatch = I('post.isBatch', 0, 'int'); // 是否批量操作
        if(!$datetime) $this->ajaxReturn(array('status'=>0,'info'=>'计息日期不能为空'));
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($datetime)));

        $userAccountObj = M('UserAccount');
        $userWalletInterestObj = M('UserWalletInterest');
        $userWalletAnnualizedRateObj = M('UserWalletAnnualizedRate');
        $userWalletRecordsObj = M("UserWalletRecords");

        // 检查当天是否已确定利率
        if(!$userWalletAnnualizedRateObj->where(array('add_time'=>$datetime))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'该日年化利率还未设置'));

        if(!$isBatch){ // 单个计息
            $uaDetail = $userAccountObj->where(array('id'=>$id))->find();
            if(!$uaDetail) $this->ajaxReturn(array('status'=>0,'info'=>'用户账户信息不存在'));
            if(!$uaDetail['wallet_enable_interest']) $this->ajaxReturn(array('status'=>0,'info'=>'可计息金额为0,该日无法计息'));
            if($userWalletInterestObj->where("user_id=".$uaDetail['user_id']." and interest_time>='".$datetime." 00:00:00.000000' and interest_time<='".$datetime." 23:59:59'")->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该账户今天已计息,请勿重复计息'));

            // 检查当前是否还有未操作的转入/转出数据
            
            $cond2[] = "(user_id > 0 and type=1 and pay_status=2 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转入
            $cond2[] = "(user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yestoday." 15:00:01.000000' and add_time<='".$datetime." 15:00:00.999000')"; // 转出（提现）
            $cond2[] = "(user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转出（购买产品）
            $conditions2 = implode(' or ', $cond2);
            $conditions2 = "(".$conditions2.") and enable_interest=0 and user_id=".$uaDetail['user_id'];
            if($userWalletRecordsObj->where($conditions2)->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'该用户还有未处理完的转入/转出记录'));
            //if($userWalletRecordsObj->where("user_id=".$uaDetail['user_id']." and ((type=1 and pay_status=2) or type=2) and enable_interest=0 and add_time>='".$before_yestoday." 00:00:00.000000' and add_time<='".$before_yestoday." 23:59:59.999000'")->find()) $this->ajaxReturn(array('status'=>0,'info'=>'该用户还有未处理完的转入/转出记录'));

            $rate = round($rate/100, 4); // 利率
            $interest = round($uaDetail['wallet_enable_interest']*$rate/365, 4); // 该日所得利息(四舍五入小数点后4位)

            $userAccountObj->startTrans();
            $rows = array(
                'user_id' => $uaDetail['user_id'],
                'interest_capital' => $uaDetail['wallet_enable_interest'],
                'interest' => $interest,
                'interest_rate' => $rate,
                'interest_time' => $datetime.' '.date('H:i:s', time()).'.'.getMillisecond().'000',
                'add_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
            );
            if(!$userWalletInterestObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            $sql = "update s_user_account set wallet_interest=wallet_interest+".$interest.",wallet_interest_totle=wallet_interest_totle+".$interest;
            $sql.= ",wallet_totle=wallet_totle+".$interest.",wallet_enable_interest=wallet_enable_interest+".$interest.",wallet_last_interest_time='".$datetime.' '.date('H:i:s', time()).'.'.getMillisecond()."000' ";
            $sql.= "where user_id=".$uaDetail['user_id'];
            if(!$userAccountObj->execute($sql)){
                $userAccountObj->rollback();
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
            }
            $userAccountObj->commit();
            $this->ajaxReturn(array('status'=>1));
        }else{ // 批量计息
            // 检查当前是否还有未操作的转入/转出数据
            
            
            $cond2[] = "(user_id > 0 and type=1 and pay_status=2 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转入
            $cond2[] = "(user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yestoday." 15:00:01.000000' and add_time<='".$datetime." 15:00:00.999000')"; // 转出（提现）
            $cond2[] = "(user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000')"; // 转出（购买产品）
            $conditions2 = implode(' or ', $cond2);
            $conditions2 = "(".$conditions2.") and enable_interest=0";
            if($userWalletRecordsObj->where($conditions2)->getField('id')) $this->ajaxReturn(array('status'=>0,'info'=>'还有用户未处理完的转入/转出记录'));
            //if($userWalletRecordsObj->where("((type=1 and pay_status=2) or type=2) and enable_interest=0 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000'")->find()) $this->ajaxReturn(array('status'=>0,'info'=>'还有用户未处理完的转入/转出记录'));

            $cond[] = "wallet_enable_interest>0";
            $cond[] = "wallet_last_interest_time<'".$datetime." 00:00:00.000000'";
            $conditions = implode(' and ', $cond);
            $list = $userAccountObj->where($conditions)->select();
            if(!$list) $this->ajaxReturn(array('status'=>0,'info'=>'没有任何可处理记录'));
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
            $this->ajaxReturn(array('status'=>1));
        }
    }

    /**
     * 设置钱包某日的年化利率
     */
    public function set_rate(){
        if(!IS_POST || !IS_AJAX) exit;

        $datetime = I('post.dt', '', 'strip_tags');
        $rate = I('post.rate', '', 'strip_tags');

        $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");
        if($userWalletAnnualizedRateObj->where(array('add_time'=>$datetime))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'今天已设置过年化利率,请勿重复设置'));
        $rows = array(
            'add_time' => $datetime,
            'rate' => $rate,
        );
        if(!$userWalletAnnualizedRateObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'操作失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 导出Excel
     */
    public function exporttoexcel(){
        vendor('PHPExcel.PHPExcel');
        $startTime = str_replace('%20', ' ', I('get.st', '', 'strip_tags'));
        $endTime = str_replace('%20', ' ', I('get.et', '', 'strip_tags'));
        $status = I('get.status', 0, 'int');

        $cond[] = "user_id > 0"; 
        $cond[] = "type=2"; // 提现类型
        $cond[] = "user_bank_id>0"; // 银行卡ID必须大于零
        $cond[] = "user_due_detail_id=0"; // 不是用钱包购买产品则无购买记录ID
        if($startTime) $cond[] = "add_time>='".$startTime." 15:00:00.000000'";
        if($endTime) $cond[] = "add_time<='".$endTime." 15:00:00.000000'";
        
        if($status) $cond[] = "status=".$status;
        
        //临时过渡下
        //$cond[] = "recharge_no not like 'JB%'";
        
        $conditions = implode(' and ', $cond);

        $userBankObj = M('UserBank');
        $userWalletRecordsObj = M('UserWalletRecords');
        $list = $userWalletRecordsObj->where($conditions)->order('add_time asc')->select();

        $totle_price = 0; // 总金额
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(11);

        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $totle_price += $val['value'];

            $bankInfo = $userBankObj->field('acct_name,bank_code,bank_card_no,bank_name')->where("id=".$val['user_bank_id'])->find();
            if($bankInfo['bank_name'] == '邮政储蓄') {
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            }
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$bankInfo['bank_card_no']); // 收款银行账号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,''); // 开户行所在省
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,''); // 开户行所在市
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$bankInfo['bank_name']); // 收款方银行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['value']*(-1),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,str_replace('TX', '', $val['recharge_no'])); // 商户订单号
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,''); // 商户备注
            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户钱包提现表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    


    /**
     * 导出Excel 连连支付
     */
    public function exporttoexcelLL(){
        vendor('PHPExcel.PHPExcel');
        $startTime = str_replace('%20', ' ', I('get.st', '', 'strip_tags'));
        $endTime = str_replace('%20', ' ', I('get.et', '', 'strip_tags'));
        $status = I('get.status', 0, 'int');

        $cond[] = "user_id > 0"; 
        $cond[] = "type=2"; // 提现类型
        $cond[] = "user_bank_id>0"; // 银行卡ID必须大于零
        $cond[] = "user_due_detail_id=0"; // 不是用钱包购买产品则无购买记录ID
        if($startTime) $cond[] = "add_time>='".$startTime." 15:00:00.000000'";
        if($endTime) $cond[] = "add_time<='".$endTime." 15:00:00.000000'";
        
        if($status) $cond[] = "status=".$status;
        
        //临时过渡下
        //$cond[] = "recharge_no not like 'JB%'";
        
        $conditions = implode(' and ', $cond);

        $userBankObj = M('UserBank');
        $userWalletRecordsObj = M('UserWalletRecords');
        $list = $userWalletRecordsObj->where($conditions)->order('add_time asc')->select();

        $totle_price = 0; // 总金额
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
            ->setCellValue("A3", "*商户付款流水号")
            ->setCellValue("B3", "*1-对公/0-对私")
            ->setCellValue("C3", "*收款方开户名")
            ->setCellValue("D3", "*收款银行账号")
            ->setCellValue("E3", "*金额(单位元：精确到分)")
            ->setCellValue("F3", "银行编号")
            ->setCellValue("G3", "收款备注")
            ->setCellValue("H3", "付款用途");

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A1", "*日期")
            ->setCellValue("B1", "*总金额")
            ->setCellValue("C1", "*总笔数");

        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);

        // 设置列表值
        $pos = 4;
        foreach ($list as $key => $val) {
            $totle_price += $val['value'];

            $money = $val['value']*(-1);

            if($money >= 50000){
                $bak = "零钱提现";
            }else{
                $bak = "";
            }
            $bankInfo = $userBankObj->field('acct_name,bank_code,bank_card_no,bank_name')->where("id=".$val['user_bank_id'] .' and user_id='.$val['user_id'])->find();

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$pos,str_replace('TX', '', $val['recharge_no'])); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,0); 

            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$bankInfo['acct_name']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,trim($bankInfo['bank_card_no']));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['value']*(-1),\PHPExcel_Cell_DataType::TYPE_NUMERIC); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,''); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$bak); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,'零钱提现'); // 商户订单号
            $pos += 1;

        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue("A2", date("Ymd"))
            ->setCellValue("B2", abs($totle_price))
            ->setCellValue("C2", count($list));
            
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户钱包提现表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 2016.04.13
     * 导出Excel (融宝模板)
     */
    public function exportToExcelRb(){
        
        vendor('PHPExcel.PHPExcel');        
        $startTime = str_replace('%20', ' ', I('get.st', '', 'strip_tags'));        
        $endTime = str_replace('%20', ' ', I('get.et', '', 'strip_tags'));        
        $status = I('get.status', 0, 'int');        
        $cond[] = "uwr.user_id > 0";        
        $cond[] = "uwr.type=2"; // 提现类型        
        $cond[] = "uwr.user_bank_id>0"; // 银行卡ID必须大于零        
        $cond[] = "uwr.user_due_detail_id=0"; // 不是用钱包购买产品则无购买记录ID --0 提现，>0购物
        
        if($startTime) $cond[] = "uwr.add_time>='".$startTime." 15:00:00.000000'";        
        if($endTime) $cond[] = "uwr.add_time<='".$endTime." 15:00:00.000000'";        
        if($status) $cond[] = "uwr.status=".$status;
                
        //临时过渡下
        //$cond[] = "recharge_no not like 'JB%'";
        
        $conditions = implode(' and ', $cond);    
        
        $sql = "SELECT uwr.user_id, uwr.value, uwr.modify_time, uwr.recharge_no, uwr.user_bank_id
	           , ub.acct_name, ub.bank_card_no, ub.bank_name
                FROM `s_user_wallet_records` uwr LEFT JOIN s_user_bank ub ON uwr.user_bank_id = ub.id
                WHERE $conditions ORDER BY uwr.id ASC";

        $list = M()->query($sql);
        
        if(!$list) {
            exit('没有记录');
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
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('融宝..')->setCellValue("A1", "序号")
                    ->setCellValue("B1", "银行账号")
                    ->setCellValue("C1", "开户名")
                    ->setCellValue("D1", "开户行")
                    ->setCellValue("E1", "分行")
                    ->setCellValue("F1", "支行")
                    ->setCellValue("G1", "账户类型")
                    ->setCellValue("H1", "金额")
                    ->setCellValue("I1", "币种")
                    ->setCellValue("J1", "省")
                    ->setCellValue("K1", "市")
                    ->setCellValue("L1", "手机号码")
                    ->setCellValue("M1", "证件类型")
                    ->setCellValue("N1", "证件号")
                    ->setCellValue("O1", "用户协议号")
                    ->setCellValue("P1", "商户订单号")
                    ->setCellValue("Q1", "备注");
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
    
        // 设置列表值
        $pos = 2;
        $n = 1;
        foreach ($list as $key => $val) {
            
            if($val['bank_name'] == '邮政储蓄'){
                $val['bank_name'] = '中国邮政储蓄银行';
            } else if($val['bank_name'] == '建设银行'){
                $val['bank_name'] = '中国建设银行';
            } else if($val['bank_name'] == '农业银行'){
                $val['bank_name'] = '中国农业银行';
            } else if($val['bank_name'] == '民生银行'){
                $val['bank_name'] = '中国民生银行';
            } else if($val['bank_name'] == '广发银行'){
                $val['bank_name'] = '广东发展银行';
            } else if($val['bank_name'] == '光大银行'){
                $val['bank_name'] = '中国光大银行';
            } else if($val['bank_name'] == '浦发银行'){
                $val['bank_name'] = '上海浦东发展银行';
            } else if($val['bank_name'] == '工商银行'){
                $val['bank_name'] = '中国工商银行';
            }
            
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['bank_card_no']); // 收款银行账号            
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['acct_name']); // 收款方开户姓名            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['bank_name']); // 收款方银行名称            
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos,'分行');             
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,'支行');            
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,'私');            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['value']*(-1),\PHPExcel_Cell_DataType::TYPE_NUMERIC);             
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, 'CNY');            
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, '省');            
            $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, '市');            
            $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, '13888001111',\PHPExcel_Cell_DataType::TYPE_STRING);            
            $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, '身份证');//  身份证    证件类型            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("N".$pos, '430101199001010011',\PHPExcel_Cell_DataType::TYPE_STRING);            
            $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, ''); //$val['recharge_no']            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("P".$pos, str_replace('TX', '', $val['recharge_no']));//商户订单号            
            $objPHPExcel->getActiveSheet()->setCellValue("Q".$pos, '零钱包提现');//备注
            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户钱包提现表 - 融宝('.date("Y-m-d H:i:s").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    
    
    /**
     * 导出Excel(盛付通)
     */
    public function exporttoexcel_sft(){
        vendor('PHPExcel.PHPExcel');
        $startTime = str_replace('%20', ' ', I('get.st', '', 'strip_tags'));
        $endTime = str_replace('%20', ' ', I('get.et', '', 'strip_tags'));
        
        $cond[] = "user_id > 0";
        $cond[] = "type=2"; // 提现类型
        $cond[] = "user_bank_id>0"; // 银行卡ID必须大于零
        $cond[] = "user_due_detail_id=0"; // 不是用钱包购买产品则无购买记录ID
        if($startTime) $cond[] = "add_time>='".$startTime." 15:00:00.000000'";
        if($endTime) $cond[] = "add_time<='".$endTime." 15:00:00.000000'";
        $conditions = implode(' and ', $cond);

        $userBankObj = M('UserBank');
        $userWalletRecordsObj = M('UserWalletRecords');
        $list = $userWalletRecordsObj->where($conditions)->order('add_time asc')->select();

        $totle_price = 0; // 总金额
        $totle_count = count($list); // 总笔数

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('批量付款')->setCellValue("A1", "批次号")->setCellValue("A2", "商户流水号")
            ->setCellValue("B2", "省份")->setCellValue("C2", "城市")->setCellValue("D2", "开户支行名称")
            ->setCellValue("E2", "银行名称")->setCellValue("F2", "收款人账户类型（C为个人B为企业）")->setCellValue("G2", "收款人户名")
            ->setCellValue("H2", "收款方银行账号")->setCellValue("I2", "付款金额（元）")->setCellValue("J2", "付款理由");
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A2:J2')->getFont()->setName('宋体')->setSize(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(23);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(21);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(19);

        // 设置列表值
        $pos = 3;
        foreach ($list as $key => $val) {
            $totle_price += $val['value'];

            $bankInfo = $userBankObj->field('acct_name,bank_name,bank_card_no')->where("id=".$val['user_bank_id'])->find();
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("A".$pos, str_replace('TX', '', $val['recharge_no']), \PHPExcel_Cell_DataType::TYPE_STRING); // 商户流水号
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, ''); // 省份
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, ''); // 城市
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, ''); // 开户支行名称
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $bankInfo['bank_name']); // 银行名称
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, 'C');
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos, $bankInfo['bank_card_no'], \PHPExcel_Cell_DataType::TYPE_STRING); // 收款银行账号
            $objPHPExcel->getActiveSheet()->getStyle('I'.$pos)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format($val['value']*(-1), 2)); // 金额
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, '钱包提现');
            $pos += 1;
        }

        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="盛付通用户钱包提现表('.time().').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 每日流水
     */
    public function dayflow(){
        if(!IS_POST){
            $startTime = I('get.st', date('Y-m-d', strtotime('-1 days', time())), 'strip_tags');
            $params = array(
                'start_time' => $startTime,
            );
            $this->assign('params', $params);

            $cond[] = "((type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0) or (type=2 and user_due_detail_id>0 and user_bank_id=0))";
            if($startTime) {
                $cond[] = "add_time>='".$startTime." 00:00:00.000000'";
                $cond[] = "add_time<='".$startTime." 23:59:59.999000'";
            }
            $conditions = implode(' and ', $cond);

            $userAccountObj = M("UserAccount");
            $userWalletRecordsObj = M('UserWalletRecords');
            $userWalletInterestObj = M('UserWalletInterest');
            $userObj = M('User');
            $counts = $userWalletRecordsObj->where($conditions)->count();
            $Page = new \Think\Page($counts, 15);
            $show = $Page->show();
            $list = $userWalletRecordsObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $key => $val){
                $list[$key]['uinfo'] = $userObj->field('username,real_name')->where(array('id'=>$val['user_id']))->find();
            }
            // 计算总金额
            $totle = $userAccountObj->sum('wallet_totle');
            // 计算可用总利息
            $totleEnable = $userAccountObj->sum('wallet_interest');// + $userAccountObj->sum('wallet_product_interest');
            $totleIn = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type in(1,2,4,5) and  user_bank_id>0 and user_due_detail_id=0 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('value'); // 总进入金额
            $totleOut = $userWalletRecordsObj->where("type=2 and user_due_detail_id>0 and user_bank_id=0 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('value'); // 总支出金额
            //pay_type =3 现金券
            $send_red_wallet =  $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type =3 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('value'); // 总进入金额
            // 计算总利息
            $totleInterest = $userWalletInterestObj->where("interest_time>='".$startTime." 00:00:00.000000' and interest_time<='".$startTime." 23:59:59.999000'")->sum('interest');

            $this->assign('totle', $totle);
            $this->assign('totle_enable', $totleEnable);
            $this->assign('totle_in', $totleIn);
            $this->assign('totle_out', $totleOut);
            $this->assign('totle_interest', $totleInterest);
            $this->assign('send_red_wallet',$send_red_wallet);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $startTime = I('post.start_time', date('Y-m-d', strtotime('-1 days', time())), 'strip_tags');
            $quest = '/st/'.$startTime;
            redirect(C('ADMIN_ROOT') . '/wallet/dayflow'.$quest);
        }
    }

    /**
     * 钱包投资记录列表
     */
    public function investment(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $startTime = I('get.st', '', 'strip_tags');
            $params = array(
                'page' => $page,
                'st' => $startTime,
            );
            $this->assign('params', $params);

            $count = 10;
            $userWalletInvestmentObj = M("UserWalletInvestment");
            $userAccountObj = M('UserAccount');
            $userWalletInterestObj = M('UserWalletInterest');

            $conditions2 = '';
            if($startTime) $conditions2 = "start_time>='".$startTime." 00:00:00.000000' and start_time<='".$startTime." 23:59:59.999000'";
            $counts = $userWalletInvestmentObj->where($conditions2)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userWalletInvestmentObj->where($conditions2)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

            // 计算剩余金额
            $totle = $userAccountObj->where("wallet_totle>0")->sum('wallet_totle');
            // 计算总利息
            $totleInterest = $userWalletInterestObj->sum('interest');

            $this->assign('totle', $totle);
            $this->assign('totle_interest', $totleInterest);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $startTime = I('post.start_time', date('Y-m-d', time()), 'strip_tags');
            if($startTime) $quest = '/st/'.$startTime;
            redirect(C('ADMIN_ROOT') . '/wallet/investment'.$quest);
        }
    }

    /**
     * 添加钱包投资记录
     */
    public function investment_add(){
        if(!IS_POST){
            $this->display();
        }else{
            $money = trim(I('post.money', '', 'strip_tags'));
            $rate = trim(I('post.rate', '', 'strip_tags'));
            $startTime = I('post.start_time', '', 'strip_tags');
            $endTime = I('post.end_time', '', 'strip_tags');
            $direction = trim(I('post.direction', '', 'strip_tags'));

            $userWalletInvestmentObj = M("UserWalletInvestment");

            $days = count_days($endTime, $startTime) + 1;
            $interest = round($money*$rate*$days/100/365, 2);
            $time = date('Y-m-d H:i:s', time());
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'money' => $money,
                'rate' => round($rate/100, 4),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'direction' => $direction,
                'days' => $days,
                'interest' => $interest,
                'add_time' => $time,
                'add_user_id' => $uid,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$userWalletInvestmentObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/wallet/investment'));
        }
    }

    /**
     * 编辑钱包投资记录
     */
    public function investment_edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $page = I('get.p', 1, 'int');
            $datetime = I('get.dt', '', 'strip_tags');
            $params = array(
                'page' => $page,
                'dt' => $datetime,
            );
            $this->assign('params', $params);

            $userWalletInvestmentObj = M("UserWalletInvestment");
            $detail = $userWalletInvestmentObj->where(array('id'=>$id))->find();
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $dt = I('post.dt', '', 'strip_tags');
            $id = I('post.id', 0, 'int');
            $money = trim(I('post.money', '', 'strip_tags'));
            $rate = trim(I('post.rate', '', 'strip_tags'));
            $startTime = I('post.start_time', '', 'strip_tags');
            $endTime = I('post.end_time', '', 'strip_tags');
            $direction = trim(I('post.direction', '', 'strip_tags'));

            $userWalletInvestmentObj = M("UserWalletInvestment");

            $days = count_days($endTime, $startTime) + 1;
            $interest = round($money*$rate*$days/100/365, 2);
            $time = date('Y-m-d H:i:s', time());
            $uid = $_SESSION[ADMIN_SESSION]['uid'];
            $rows = array(
                'money' => $money,
                'rate' => round($rate/100, 4),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'direction' => $direction,
                'days' => $days,
                'interest' => $interest,
                'modify_time' => $time,
                'modify_user_id' => $uid,
            );
            if(!$userWalletInvestmentObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            $query = '';
            if($dt) $query .= '/dt/'.$dt;
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/wallet/investment/p/'.$page.$query));
        }
    }
	    /** 设置钱包年化利率-列表 */
    public function setrate(){
        if(IS_POST){
            $start_time = trim(I('post.start_time', '', 'strip_tags'));
            $end_time = trim(I('post.end_time', '', 'strip_tags'));
            $quest="";
            if($start_time) $quest .= '/st/'.str_replace(' ', '|', $start_time);
            if($end_time) $quest .= '/et/'.str_replace(' ', '|', $end_time);
            redirect(C('ADMIN_ROOT') . '/wallet/setrate'.$quest);
        }else{
            $user_wallet_annualized_rate = M('UserWalletAnnualizedRate');
            $limit = 25;
            $start_time = I("get.st",date("Y-m-d",time()-(10*24*3600)),'strip_tags');//开始时间
            $end_time = I('get.et', date('Y-m-d', time()+(10*34*3600)), 'strip_tags');//结束时间
            if($start_time) $cond[] = "add_time>='".$start_time."'";
            if($end_time) $cond[] = "add_time<='".$end_time."'";
            $condition = implode(" and ",$cond);
            $params=array(
                'start_time'=>$start_time,
                'end_time'=>$end_time
            );
            $counts = $user_wallet_annualized_rate->where($condition)->count();
            $Page = new \Think\Page($counts, $limit);
            $show = $Page->show();
            $annualized_rate_list = $user_wallet_annualized_rate->where($condition)->order("id desc")->limit($Page->firstRow.",".$Page->listRows)->select();
			foreach($annualized_rate_list as $k => $v){
                $annualized_rate_list[$k]['week'] =getWeek(strtotime($v['add_time']));
            }
            $now_date = date("Y-m-d",time());
            $this->assign("params",$params);
            $this->assign("now_date",$now_date);
            $this->assign('show',$show);
            $this->assign("rate_list",$annualized_rate_list);
            $this->display();
        }

    }
    /** 添加/编辑年化利率 */
    public function editrate(){
        $rate_obj = M('UserWalletAnnualizedRate');
        if(IS_POST){//添加/编辑
            $rate_id = I('post.annualized_rate_id',0,'int');//年化利率ID
            $rate_date = I('post.add_time',date("Y-m-d",time()-24*3600),'strip_tags');//年化利率日期
            $rate_num = I('post.rate',0,'strip_tags');//年化利率
            $pf_rate_num = I('post.pf_rate',0,'strip_tags');//年化利率
            
            $financing = I('post.financing','','strip_tags');//年化利率
            
            if(!$rate_date){
                $this->error("日期未设置");
            }
            if(!$rate_num){
                $this->error("年化利率未设置");
            }
            if(!$pf_rate_num){
                $this->error("协议利率未设置");
            }
            
            if(!$financing) {
                $this->error("融资方未设置");
            }
            
            if($rate_id){//编辑
                $row = array(
                    'add_time'=>$rate_date,
                    'rate'=>$rate_num,
                    'pf_rate'=>$pf_rate_num,
                    'financing'=>$financing
                );
                $update_status = $rate_obj->where(array('id'=>$rate_id))->save($row);
                if($update_status!==false){
                    $this->success("更新该日的年化利率成功");
                }else{
                    $this->error("更新该日的年化利率失败,重新更新");
                }
            }else{//添加
                //判断该日期是否已经录入了
                $rate_exist = $rate_obj->where(array('add_time'=>$rate_date))->find();
                if($rate_exist){
                    $this->error("该日期已经录入了");
                }
                $row = array(
                    'add_time'=>$rate_date,
                    'rate'=>$rate_num,
                    'pf_rate'=>$pf_rate_num,
                    'financing'=>$financing
                );
                $add_status = $rate_obj->add($row);
                if($add_status){
                    $this->success("录入年化利率成功");
                }else{
                    $this->error("录入年化利率失败");
                }
            }
        }else{
            $rate_id = I('get.rate_id',0,'int');//年化利率ID
            if($rate_id){
                $rate_info = $rate_obj->where(array('id'=>$rate_id))->find();
            }
            $this->assign("rate_id",$rate_id);
            $this->assign("add_time",$rate_info['add_time']);
            $this->assign("rate",$rate_info['rate']);
            $this->assign("pf_rate",$rate_info['pf_rate']);
            $this->assign("financing",$rate_info['financing']);
            $this->display();
        }

    }
	    /**
     * 转入/转出查看(转入：1,充值2,产品到期（还本付息);转出:1,提现2,购买产品)
	 *@params type(1表示转入2表示转出) status(1表示充值2表示产品到期3表示提现4表示购买产品) start_time表示起始时间 end_time表示结束时间
     */
    public function import_export_list(){
		if(!IS_POST){//get 
			$start_time = I('get.start_time','2015-07-01','strip_tags');//起始时间
			$end_time   = I('get.end_time',date("Y-m-d",time()),'strip_tags');//结束时间
			$type      = I('get.type',1,'int');//类型1表示转入2表示转出
			$status     = I('get.status',1,'int');//1表示充值2表示产品到期3表示提现4表示购买产品
			$page = I('get.p', 1, 'int'); // 页码	
			$cond=array();
			$totle = 0;
			$params = array(
				'type'=>$type,
				'status'=>$status,
				'start_time'=>$start_time,
				'end_time'=>$end_time,
				'page'=>$page
			);
			
			$limit =  30;
			if($type == 1){//转入
					if($status == 1){//充值
					    $cond[] = "a.user_id>0";
						$cond[] = "a.user_bank_id>0";
						$cond[] = "a.user_due_detail_id=0";	
						$cond[] = "a.pay_status=2";	
					}else if($status == 2){//还本付息 		
					    $cond[] = "a.user_id>0";
						$cond[] = "a.user_bank_id=0";
						$cond[] = "a.user_due_detail_id>0";
                        $cond[] = "a.pay_status=2";
                    }
					$cond[] =  "a.type=".$type;
					$cond[] = "a.`user_id` = b.`id`";
					$cond[] = "a.add_time>='".$start_time." 00:00:00.000000'";
					$cond[] = "a.add_time<='".$end_time." 23:59:59.999000'";
					$condition = implode(" and ",$cond);					
					$counts = M()->table("s_user_wallet_records a,s_user b")->where($condition)->count();
					$Page = new \Think\Page($counts, $limit);
					$show = $Page->show();
					$list = M()->table("s_user_wallet_records a,s_user b")->field('b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time`')->where($condition)->order("a.add_time desc")->limit($Page->firstRow.",".$Page->listRows)->select();
					
					foreach($list as $k=>$v){ 
						$totle+=$v['value'];
					}
					
				
			}else if($type == 2){//转出
					if($status == 3){//提现				
						
						for($k=strtotime($start_time);$k<=strtotime($end_time);$k+=86400){
						    
							$tmp_date = date("Y-m-d",$k);							
							$befor_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($tmp_date))); // 指定日期前一日
							$befoe_befor_yestoday = date('Y-m-d', strtotime('-3 days', strtotime($tmp_date))); // 指定日期前前一日	
							
							$withdraw_sql = "select b.username,b.real_name,a.recharge_no,a.value,a.add_time from s_user_wallet_records a,s_user b where a.user_id >0 and a.`user_id` = b.`id` and a.user_bank_id>0 and a.status = 1 and a.user_due_detail_id=0 and a.type=".$type." and a.add_time>='".$befoe_befor_yestoday." 15:00:00.000000' and a.add_time<='".$befor_yestoday." 15:00:00.999000' order by a.add_time asc";			
							$list_arr = M()->query($withdraw_sql);
							foreach($list_arr as $key=>$v){								
								$tmp_list = array(
									'username'=>$v['username'],
									'real_name'=>$v['real_name'],
									'recharge_no'=>$v['recharge_no'],
									'value'=>$v['value'],
									'add_time'=>$v['add_time'],
									'pay_time'=>$tmp_date
								);
								
								$totle+=$v['value'];
						
							    $withdrawMoney[]=$tmp_list;								
							}								
							$list = $withdrawMoney;	
						}						
							
						
					}else if($status == 4){//购买产品 
					    $cond[] = "a.user_id>0";
						$cond[] = "a.user_bank_id=0";
						$cond[] = "a.user_due_detail_id>0";
						$cond[] = "a.type=".$type;
						$cond[] = "a.`user_id` = b.`id`";
						$cond[] = "a.add_time>='".$start_time." 00:00:00.000000'";
						$cond[] = "a.add_time<='".$end_time." 23:59:59.999000'";
						$condition = implode(" and ",$cond);
						$condition = implode(" and ",$cond);					
						$counts = M()->table("s.s_user_wallet_records a,s.s_user b")->where($condition)->count();
						$Page = new \Think\Page($counts, $limit);
						$show = $Page->show();
						$list = M()->table("s_user_wallet_records a,s_user b")->field('b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time`')->where($condition)->order("a.add_time desc")->limit($Page->firstRow.",".$Page->listRows)->select();
						foreach($list as $k=>$v){ 
							$totle+=$v['value'];
						}
					}
			}
            
			$this->assign('params',$params);
			$this->assign('list',$list);
			$this->assign('totle',$totle);
			$this->assign('show',$show);	
			$this->display();		
		}else{//post 
			$start_time = I('post.start_time','2015-07-01','strip_tags');//起始时间
			$end_time   = I('post.end_time',date("Y-m-d",time()),'strip_tags');//结束时间
			$type      = I('post.type',1,'int');//类型1表示转入2表示转出
			$status     = I('post.status',1,'int');//1表示充值2表示产品到期3表示提现4表示购买产品
            $quest = '/start_time/'.$start_time.'/end_time/'.$end_time.'/type/'.$type.'/status/'.$status;
            redirect(C('ADMIN_ROOT') . '/wallet/import_export_list'.$quest);
		}
    }
    /**
     * 转入/转出execel(转入：1,充值2,产品到期（还本付息);转出:1,提现2,购买产品)
     */
    public function import_export_excel(){
		if(!IS_GET){ 
			exit();
		}
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$start_time = I('get.st','2015-07-01','strip_tags');//起始时间
		$end_time   = I('get.et',date("Y-m-d",time()),'strip_tags');//结束时间
		$type      = I('get.type',1,'int');//类型1表示转入2表示转出
		$status     = I('get.status',1,'int');//1表示充值2表示产品到期3表示提现4表示购买产品
		
		if($type == 1){//转入 
		    
			if($status == 1){//充值 
			    
				$take_in_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM `s_user_wallet_records` AS a,`s_user` AS b WHERE a.user_id > 0 and a.`user_id` = b.`id` AND a.pay_status = 2 and a.type=".$type." AND a.user_bank_id>0 AND a.user_due_detail_id=0 AND a.add_time>='".$start_time." 00:00:00.000000' AND a.add_time<='".$end_time." 23:59:59.999000'";
				
				$list = M()->query($take_in_sql);				
				
				$objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
					->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
				
				$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包银行卡充值')->setCellValue("A1", "用户账号")->setCellValue("B1", "用户名称")
					->setCellValue("C1", "交易编号")->setCellValue("D1", "银行卡充值(元)")->setCellValue("E1", "充值时间");
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

				// 设置列表值
				$pos = 2;
				
				foreach ($list as $key => $val) {
				    
				    $t = date('H',strtotime($val['add_time']));
				    if($t>=15) {
				        $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'E'.$pos)->getFont()->getColor()->setARGB('FFFF0000');
				    }			    
				    
					$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);
					$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
					$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);
					$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
					$pos += 1;
				}

				header("Content-Type: application/vnd.ms-excel");
				header('Content-Disposition: attachment;filename="('.$start_time.'-'.$end_time.')钱包银行卡充值.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				exit;
				
			}else if($status == 2){//还本付息
		
				$take_in_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM `s_user_wallet_records` AS a,`s_user` AS b WHERE a.user_id > 0 and a.`user_id` = b.`id` AND a.type=".$type." AND a.user_bank_id=0 AND a.user_due_detail_id>0 AND a.pay_status = 2 AND a.add_time>='".$start_time." 00:00:00.000000' AND a.add_time<='".$end_time." 23:59:59.999000'";
				
				$list = M()->query($take_in_sql);				
				
				$objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
				
				->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
				
				$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包转入还本付息')->setCellValue("A1", "用户账号")->setCellValue("B1", "用户名称")
				
				->setCellValue("C1", "交易编号")->setCellValue("D1", "还本付息(元)")->setCellValue("E1", "交易时间");
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

				// 设置列表值
				$pos = 2;
				foreach ($list as $key => $val) {
				    $t = date('H',strtotime($val['add_time']));
				    if($t>=15) {
				        $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'E'.$pos)->getFont()->getColor()->setARGB('FFFF0000');
				    }
    				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);    				
    				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);    				
    				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);    				
    				$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));    				
    				$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));    				
    				$pos += 1;
				}

				header("Content-Type: application/vnd.ms-excel");
				header('Content-Disposition: attachment;filename="('.$start_time.'-'.$end_time.')钱包转入还本付息.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				exit;

			}
		}else if($type == 2){//转出 
		    
			if($status == 3){//提现 
				
			    $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
					        ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
			    
				$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包转出提现记录')->setCellValue("A1", "用户账号")->setCellValue("B1", "用户名称")
					        ->setCellValue("C1", "交易编号")->setCellValue("D1", "提现金额")->setCellValue("E1", "交易时间")->setCellValue("F1", "打款时间");
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				
				$tmp_date="";
				$befor_yestoday = "";
				$befoe_befor_yestoday = "";
				$list = array();
				$pos = 2;
				
				/*
                  2016.04.13 修改  
				 */
				$tmp_start_time = strtotime($start_time);
				$tmp_end_time = strtotime($end_time);
				
				for($i=$tmp_start_time;$i<=$tmp_end_time;$i+=86400){
				    
					$tmp_date = date("Y-m-d",$i);
					
					$befor_yestoday = date('Y-m-d', strtotime('-2 days', strtotime($tmp_date))); // 指定日期前一日
					
					$befoe_befor_yestoday = date('Y-m-d', strtotime('-3 days', strtotime($tmp_date))); // 指定日期前前一日
					
					$take_out_sql = "";
					
					$list = array();
					
					$take_out_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM `s_user_wallet_records` AS a,`s_user` AS b WHERE a.user_id > 0 and a.`user_id` = b.`id` and  a.type=".$type." and a.user_bank_id>0 and a.user_due_detail_id=0 and a.add_time>='".$befoe_befor_yestoday." 15:00:01.000000' and a.add_time<='".$befor_yestoday." 15:00:00.999000' order by a.add_time asc";
					
					$list = M()->query($take_out_sql);
					
					// 设置列表值
					foreach ($list as $key => $val) {
					    $t = date('H',strtotime($val['add_time']));
					    if($t>=15) {
					        $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'F'.$pos)->getFont()->getColor()->setARGB('FFFF0000');
					    }
						$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);
						$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
						$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);
						$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));
						$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
						$objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $tmp_date);
						$pos += 1;
					}
				}
				header("Content-Type: application/vnd.ms-excel");
				header('Content-Disposition: attachment;filename="('.$start_time.'-'.$end_time.')钱包提现.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				exit;
				
			}else if($status == 4){//购买产品 
				
			    $take_in_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM `s_user_wallet_records` AS a,`s_user` AS b WHERE a.user_id > 0 and a.`user_id` = b.`id` AND a.type=".$type." AND a.user_bank_id=0 AND a.user_due_detail_id>0 AND a.add_time>='".$start_time." 00:00:00.000000' AND a.add_time<='".$end_time." 23:59:59.999000'";
				
			    $list = M()->query($take_in_sql);
				
			    $objPHPExcel = new \PHPExcel();
				
				$objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
				            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
				
				$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包转出购买产品记录')->setCellValue("A1", "用户账号")->setCellValue("B1", "用户名称")
				            ->setCellValue("C1", "交易编号")->setCellValue("D1", "购买产品(元)")->setCellValue("E1", "交易时间");
				
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

				// 设置列表值
				$pos = 2;
				foreach ($list as $key => $val) {
				    $t = date('H',strtotime($val['add_time']));
				    if($t>=15) {
				        $objPHPExcel->getActiveSheet()->getStyle('A'.$pos.':'.'E'.$pos)->getFont()->getColor()->setARGB('FFFF0000');
				    }
    				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);
    				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
    				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);
    				$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));
    				$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
    				$pos += 1;
				}

				header("Content-Type: application/vnd.ms-excel");
				header('Content-Disposition: attachment;filename="('.$start_time.'-'.$end_time.')钱包购买产品记录.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				exit;
			}
		}

    }
    /**
     * 导出指定时间段之内的钱包利息表
     */
    public function timeperiod_wallet_interest(){
        $st = I("get.st",'','strip_tags');//开始时间
        $et = I('get.et','',"strip_tags");//结束时间
        if($st && $et){
            ini_set("memory_limit", "1000M");
            ini_set("max_execution_time", 0);
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('利息列表统计')->setCellValue("A1", "日期")->setCellValue("B1", "本金")->setCellValue("C1", "利息")->setCellValue("D1", "利率");
            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

            $begin_time =$st; //开始时间
            $end_time   =$et; //结束时间
            if(empty($begin_time) || empty($end_time)){
                exit("请输入筛选日期");
            }
            $pos = 2;
            for($i=strtotime($begin_time);$i<=strtotime($end_time);$i+=86400) {
                $date = date("Y-m-d", $i);
                $sql = "SELECT SUM(m.`interest_capital`) AS capital_total,SUM(m.`interest`) AS interest_total,m.`interest_rate` FROM stone.`s_user_wallet_interest` AS m WHERE m.`interest_time`>='".$date." 00:00:00.000000' AND m.`interest_time`<='".$date." 23:59:59.999000'";
                $wallet_interest_list = M()->query($sql);
                $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $date);
                $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $wallet_interest_list[0]['capital_total']);
                $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $wallet_interest_list[0]['interest_total']);
                $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $wallet_interest_list[0]['interest_rate']);
                $pos++;
            }

            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')利息列表统计.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
        $this->display("timeperiod_wallet");
    }
    
    /*
     * 钱包每日收益
     */
    public function earnings(){
        $st = I("get.start_time",'','strip_tags');//开始时间
        $et = I('get.end_time','',"strip_tags");//结束时间
        
        $cond = '1 = 1';
        if($st) { 
            $cond .= " and add_time >= '$st'";   
        }
        
        if($et) {
            $cond .= " and add_time <= '$et'";
        }
        
        $totalCnt =  M("StatisticsDailyWalletEarnings")->where($cond)->count();
        $Page = new \Think\Page($totalCnt, $this->pageSize);
        $list = M("StatisticsDailyWalletEarnings")->where($cond)->order("add_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('totalCnt',$totalCnt);
        $this->assign('showPage',$Page->show());
        $this->display();
    }
    
    /**
    * 钱包计息发现的错误
    * @date: 2017-2-8 下午1:34:27
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function err_interest(){
        $totalCnt =  M("taskInterest")->where("username !=''")->count();
        $Page = new \Think\Page($totalCnt, $this->pageSize);
        $list = M("taskInterest")->where("username !=''")->order("add_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('totalCnt',$totalCnt);
        $this->assign('showPage',$Page->show());
        $this->display();
    }

    /**
     * 钱包15点存量
     * @date: 2017-2-8 下午1:34:27
     * @author: hui.xu
     * @param: variable
     * @return:
     */
    public function stockList(){
        $totalCnt =  M("statisticsDailyWallet")->count();
        $Page = new \Think\Page($totalCnt, $this->pageSize);
        $list = M("statisticsDailyWallet")->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list',$list);
        $this->assign('totalCnt',$totalCnt);
        $this->assign('showPage',$Page->show());
        $this->display();
    }
    
    
}
<?php
namespace Admin\Controller;

/**
 * 现金金管理
 */
class CashCouponController extends AdminController {
    
        private $pageSize = 15;
        
        /**
         * 现金券管理
         */
        public function cash_index() {
            
            $title = I("get.title","",'strip_tags');
            $start_time = I("get.start_time","",'strip_tags');
            $end_time = I("get.end_time","",'strip_tags');
            $status = I("get.status",-1,'int');

            // 根据用户名字或者账户筛选
            $account = trim(I("get.account", '', 'strip_tags'));

            $title = urldecode($title);
            
            $cond = 'is_delete = 0';
            
            if ($start_time != "") {
                $_start_time = $start_time .' 00:00:00';
                $cond .= " AND `modify_time` >= '$_start_time' ";
            
            }
            if ($end_time != "") {
                $_end_time = $end_time .' 23:59:59';
                $cond .= " AND `modify_time` <= '$_end_time'";
            }
            
            if ($title) {
                $cond .= " AND title like '$title%'";
            }
            
            if($status >=0) {
                $cond .= " AND status = '$status'";
            }

            if ($account != '') {
                // 查询用户id
                $user_id = M('User')->where("`real_name` = '$account' or `username` = '$account'")->getField('id');
                $cond .= " AND user_id = '$user_id'";
            }
            
            $totalCnt =  M("UserCashCoupon")->where($cond)->count();
            $Page = new \Think\Page($totalCnt, $this->pageSize);
            $list = M("UserCashCoupon")->where($cond)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $res = array();
            if($list) {
                foreach ($list as $val) {
                    
                    $val['user_info'] = $this->getUserInfo($val['user_id']);
                    
                    if($val['status'] == 0){
                        $val['status'] = '未使用';
                        $val['modify_time'] = '';
                        $val['color'] = '#080808';
                    }else if($val['status'] == 1){
                        $val['status'] = '已使用';
                        $val['color'] = '#FF0000';
                    }else if($val['status'] == 2){
                        $val['status'] = '已过期';
                        $val['color'] = '#8A8A8A';
                    }else if($val['status'] == 3){
                        $val['status'] = '正在打款';
                        $val['color'] = '#FF0000';
                    }else if($val['status'] == 4){
                        $val['status'] = '已冻结';
                        $val['color'] = '#FF0000';
                    }else{
                        $val['status'] = '已过期';
                        $val['modify_time'] = '';
                        $val['color'] = '#8A8A8A';
                    }
                    
                    if($val['type'] == 1){
                        $val['type'] = '后台';
                    } else if($val['type'] == 0){
                        $val['type']='系统';
                    } else  if($val['type'] == 2){
                        $val['type']='抽奖';
                    } else {
                        $val['type']='活动';
                    }
                    /*
                    if($val['type'] == 1){
                        $val['type'] = '后台';
                    } else{
                        $val['type']='系统';
                    }*/
                    
                    $res[] = $val;
                }
            }
            
            
            $total_money = M("UserCashCoupon")->where($cond)->sum('amount');
            $total_use_money = M("UserCashCoupon")->where($cond . ' and status = 1')->sum('amount');
            
            $param = array(
                "title" => $title,
                "start_time" => $start_time,
                "end_time" => $end_time,
                'total_cnt'=>$totalCnt,
                'total_money' => $total_money,
                'total_use_money'=>$total_use_money,
                'status' => $status,
                'account' => $account
            );
            
            $this->assign("params",$param);
            $this->assign("list",$res);
            $this->assign('showPage',$Page->show());
            $this->display('cash_index');
        }
        
        /**
         * 现金券添加
         */
        public function add() {
            if(!IS_POST){
                $this->display('add');
            }else{
                $row['title'] = I('post.title','','strip_tags');
                $row['amount'] = I('post.amount', 0, 'float');//金额
                $row['due_time'] = I('post.due_date', 0, 'int');	//红包到期天数：
                
                $row['subtitle'] = I('post.subtitle','','strip_tags');
            
                if(!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));
                if(!$row['amount']) $this->ajaxReturn(array('status' => 0, 'info' => '金额必须大于  0 元'));
                if(!$row['due_time']) $this->ajaxReturn(array('status' => 0, 'info' => '现金券有效天数必须大于0'));

                
                $row['add_time'] = trim(I('post.start_time','','strip_tags'));
                if(!$row['add_time']) {
                    $row['add_time'] = date("Y-m-d 00:00:00");
                }
                
                //红包到期天数 等于当前时间 + $due_time
                $row['expire_time'] = date("Y-m-d 23:59:59",strtotime($row['add_time'])+($row['due_time']-1) * 86400);
                
                $row['add_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];
                $row['modify_time'] =  date("Y-m-d H:i:s");
                $row['create_time'] = time();
                
                $row['status'] = 0;
                $row['type'] = 1;
            
                $userIdStr = I('post.userId', '', 'strip_tags');//发放范围
        
                if($userIdStr == "") {
                    $this->ajaxReturn(array('status' => 0, 'info' => '请添加需要指定发放的用户!'));
                }
    
                $userIdArr = explode("#",$userIdStr);
                $res = false;
    
                M("UserCashCoupon")->startTrans();
    
                foreach ($userIdArr as $val) {
    
                    $row['user_id'] = $val;
    
                    $res = M("UserCashCoupon")->add($row);
                }
    
                if($res) {
                    M("UserCashCoupon")->commit();
                    $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
                }else{
                    M("UserCashCoupon")->rollback();
                    $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
                }
                exit;
            }
        }
        
        public function delete(){
            if(IS_AJAX) {
            
                $id = I('post.id', 0, 'int');
            
                if(!$id) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "参数错误"));
                }
            
                $status = M('UserCashCoupon')->where(array('id'=>$id))->getField('status');
            
                if($status == 1) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "该现金券已经有用户使用，无法删除"));
                }
                
                $dd['is_delete'] = 1;
                
                if(! M('UserCashCoupon')->where('id='.$id)->save($dd)) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "删除失败"));
                }
                $this->ajaxReturn(array('status' => 0, 'info' => "删除成功"));
            } else {
                $this->ajaxReturn(array('status' => 1, 'info' => "非法访问"));
            }
        }
        
        /**
         * 邀请管理
         */
        public function invite_index() {
            
            $starttime = I('get.start_time', '', 'strip_tags');
            $endtime = I('get.end_time', '', 'strip_tags');
            
            $userName = I('get.userName','','strip_tags');
            
            if(!$starttime) {
                $starttime = date('Y-m-d');
            }
            
            $param['start_time'] = $starttime;
            
            $start_time = $starttime.' 00:00:00';
            
            $condition = "add_time>'$start_time'";
            
            if($endtime) {
                $end_time = $endtime.' 23:59:59';
                $condition .= " AND add_time<='$end_time'";
            }
            
            $cond = '';
            
            if($userName) {
                $userId = M('User')->where('username='.$userName)->getField('id');
                if($userId) {
                    $cond = " AND user_id=$userId";
                }
            }
            
            $inviteall = M('UserInviteList')->where($condition . $cond)->group('user_id')->select();           
                        
            $Page = new \Think\Page(count($inviteall), $this->pageSize);
            $show = $Page->show();
            $inviteList = M("UserInviteList")->where($condition .$cond)->group('user_id')->order("add_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();

            $res = array();
            $total_invite_num = 0;
            $total_award_amount = 0;
            
            
            foreach ($inviteList as $val) {
                $val['user_info'] = $this->getUserInfo($val['user_id']);
                
                $val['invite_num'] = M('UserInviteList')->where($condition. 'and user_id='.$val['user_id'])->count();//邀请人数
                
                $val['invite_amount'] = M('UserInviteList')->where($condition .'and user_id='.$val['user_id'])->sum('amount');//发放金额
                //邀请投资人数
                $val['invite_invest_num'] = M('UserInviteList')->where($condition .'and amount > 0 and user_id='.$val['user_id'])->count();
                
                $total_award_amount += $val['invite_amount'];
                $total_invite_num += $val['invite_num'];
                $res[] = $val;
            }

            $allList = M('UserInviteList')->field('amount,invited_user_id')->where('amount > 0')->select();
            $total_people = count($allList);
            $total_man = 0;
            $total_woman = 0;
            
            foreach ($allList as $val){

                $invited_user_id = isset($val['invited_user_id'])?intval($val['invited_user_id']):0;
                
                $cardNo = M("User")->where('id='.$invited_user_id)->getField('card_no');
                
                $sex = $this->getSex($cardNo);
                
                if($sex == 0) {
                    $total_man +=1;
                } else{
                    $total_woman +=1;
                }
            }
            
            $param['total_invite_num'] = $total_invite_num;
            $param['total_award_amount'] = $total_award_amount;
            $param['total_amount'] = M('UserInviteList')->sum('amount');
            $param['total_people'] = $total_people;
            $param['total_man'] = $total_man;
            $param['total_woman'] = $total_woman;
            $param['end_time'] = $end_time;
            $param['userName'] = $userName;
            $this->assign('params',$param);
            $this->assign('show', $show);
            $this->assign('totalcnt',count($inviteall));
            $this->assign('res',$res);
            $this->display('invite_index');
        }
        
        /**
         * 邀请明细
         */
        public function invite_detail() {
            $userId = I('user_id',0,'int');
            $res = array();
            if($userId) {
                $inviteCnt = M('UserInviteList')->where(array('user_id'=>$userId))->count();
                $Page = new \Think\Page($inviteCnt, $this->pageSize);
                $inviteList = M("UserInviteList")->where(array('user_id'=>$userId))->order("add_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $n = 0;
                foreach ($inviteList as $val) {
                    $val['n'] +=1;
                    $val['user_info'] = $this->getUserInfo($val['invited_user_id']);
                    $val['first_amount'] = M('RechargeLog')->where(array('user_id'=>$val['invited_user_id'],'status'=>2))->order("modify_time asc")->limit(1)->getField('amount');
                    $res[] = $val;
                }
            }
            $this->assign('list',$res);
            $this->display('invite_detail');
        }
        
        
        private function getSex($cardNo){
            if(!$cardNo) return '';
            $sex = substr($cardNo, strlen($cardNo) - 2, 1);
            if($sex % 2 != 0) {
                return 0;//男
            } else {
                return 1;
            }
        }
       
        
        /**
         * 取银行名字
         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getBankInfo($userId,$cardNo) {
            
            if($userId != "" && $cardNo != "") {
                
                $data = M("UserBank")->field('bank_name,bank_card_no')->where(array("user_id"=>$userId,"bank_card_no"=>$cardNo))->find();
                
                if(!empty($data)) {
                    return $data;
                }
            }
            return "";
        }
        
        /**
         * 取用户的真实姓名
         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getUserInfo($userId) {
        
            if($userId != "") {
                $data = M("User")->field('real_name,username')->where('id='.$userId)->find();
                if(!empty($data)) {
                    return $data;
                }
            }
            return "";
        }
        
        
        /**
         * 取订单信息
         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getRechargeLogInfo($orderId,$field) {
            
            if($orderId != "") {
                $data = M("RechargeLog")->field('amount,card_no')->where(array("recharge_no"=>$orderId,"status"=>2))->find();
                if(!empty($data)) {
                    return $data[$field];
                }
            }
            return "";
        }
        
        
        /**
		 * 根据手机号码，查用户uid
		 */
		
		public function findUser(){
			
			$username = trim(I('post.username', '', 'strip_tags'));
			if(!empty($username)) {
				$user = M('User')->field('id,username')->where(array('username'=>$username))->find();
				if(!$user) {
					$this->ajaxReturn(array('status' => 0, 'info' => '没有`'.$username.'用户!`'));															
				}
				$this->ajaxReturn(array('status' => 1, 'info' => $user));
			} else{
				$this->ajaxReturn(array('status' => 0, 'info' => '不能提交空数据'));		
			}			
		}
        
	
		
        /**
         *  导出项目用券包的记录
         * @param unknown $project_id            
         */
        public function exportExcel()
        {
            $projectId = I("get.projectId",0,'int');
            
            if (!$projectId) {
                exit('参数有误');
            }
            
            $res = array();
                
            $list = M("UserInterestCoupon")->field('id,interest_rate,recharge_no,user_id,project_id,modify_time')->where('project_id='.$projectId)->order("modify_time DESC")->select();
                                
            if($list) {
                $n = 0;
                foreach ($list as $val) {
                    
                    $rows['n'] = ++ $n;
                
                    $rows['user_info'] = $this->getUserInfo($val['user_id']);
                    
                    $rows['order_info'] = $this->getRechargeLogInfo($val['recharge_no']);
                    
                    $rows['bank_info'] = $this->getBankInfo($val['user_id'], $rows['card_no']);
                    
                    $rows['interest_rate'] = $val['interest_rate'];
                    
                    $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                    $_project_end_time = date('Y-m-d',strtotime($this->getProjectInfo($val['project_id'], 'end_time')));
                    
                    $rows['coupon_income'] = $this->countInterest(
                                            $rows['order_info']['amount'] ,$_project_end_time,$_buy_time,$val['interest_rate']);

                    $rows['user_interest'] = $this->getProjectInfo($val['project_id'], 'user_interest');
                    $row['modify_time'] = $val['modify_time'];
                    
                    $res[] = $rows;
                }
                unset($list);
            }
            
            vendor('PHPExcel.PHPExcel');
            
            $objPHPExcel = new \PHPExcel();
            
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")
            
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('用户使用名细')
                        ->setCellValue("A1", "编号")
                        ->setCellValue("B1", "姓名")
                        ->setCellValue("C1", "账号")
                        ->setCellValue("D1", "银行卡")
                        ->setCellValue("E1", "发卡银行")
                        ->setCellValue("F1", "项目利率")
                        ->setCellValue("G1", "券包利率")
                        ->setCellValue("H1", "投资金额")
                        ->setCellValue("I1", "券包收益金额")
                        ->setCellValue("J1", "购买日期");
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

            // 设置列表值
            $pos = 2;
            $n = 1;
            foreach ($res as $key => $val) {                
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['user_info']['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['user_info']['user_name']);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos, $val['bank_info']['card_no']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['bank_info']['bank_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, number_format($val['user_interest'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($val['interest_rate'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['order_info']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format($val['coupon_income'], 2));
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $val['modify_time']);
                $pos += 1;
                $n++;
            }
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="券包使用明细('.date("Y-m-d").').xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
		
		
        
        /**
         *
         * 日数据导出excel
         *
         */
        public function day_index_export_excel(){
        
            $starttime = I('get.start_time', '', 'strip_tags');
            $endtime = I('get.end_time', '', 'strip_tags');
             
            $pay_method = I("get.pay_method",0,"int");
        
            $condition = "project_id >0 and status = 1 and is_delete = 0 ";
             
            if(!$starttime) {
                $starttime = date('Y-m-d');
            }
             
            $condition .= " AND `modify_time` >= '$starttime'";
            
            if($endtime) {
                $condition .= " AND `modify_time` < '$endtime'";
            }
             
            if($pay_method == 1) {
                 
                $condition .= " AND recharge_no LIKE 'QB%'";
                 
            } else if($pay_method == 2) {
        
                $condition .= " AND recharge_no NOT LIKE 'QB%' ) ";
            }
             
            $res = array();
            
            $list = M("UserInterestCoupon")->field("user_id,interest_rate,modify_time,recharge_no,project_id")
                                            ->where($condition)
                                            ->order("modify_time desc")
                                            ->select();
            
            if($list){
                $n = 1;
                foreach ($list as $val) {
                    $val['n'] = $n ++;
                    $val['user_info'] = $this->getUserInfo($val['user_id']);
                    if (strpos($val['recharge_no'], 'QB') !== false) {
                        $val['pay_method'] = "钱包";
                    } else {
                        $val['pay_method'] = "银行卡";
                    }
                    $val['projectName'] = $this->getProjectInfo($val['project_id'], "title");
                    $val['RechargeLogInfo'] = $this->getRechargeLogInfo($val['recharge_no'], 'amount');
                    $_project_end_time = $this->getProjectInfo($val['project_id'], "end_time");
                    $_project_end_time = date('Y-m-d',strtotime($_project_end_time));
                    $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                    
                    //收益
                    $val['coupon_income'] = $this->countInterest(
                                    $val['RechargeLogInfo']['amount'] ,
                                    $_project_end_time,$_buy_time,
                                    $val['interest_rate'] );
                    
                    $total_coupon_income += $val['coupon_income'];
                    
                    $res[] = $val;
                }
                unset($list);
            }
            
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
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('券包每日数据管理')
                ->setCellValue("A1", "编号")
                ->setCellValue("B1", "用户姓名")
                ->setCellValue("C1", "账号")
                ->setCellValue("D1", "使用日期")
                ->setCellValue("E1", "项目期数")
                ->setCellValue("F1", "券包利率")
                ->setCellValue("G1", "投资金额")
                ->setCellValue("H1", "收益金额")
                ->setCellValue("I1", "订单号")
                ->setCellValue("J1", "付款方式");
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
            
            // 设置列表值
            $pos = 2;
            
            $n = 1;
            
            foreach ($res as $key => $val) {
            
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['user_info']['username']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['user_info']['real_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['modify_time']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['projectName']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['interest_rate']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$val['RechargeLogInfo']['amount']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['coupon_income']);
                
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['recharge_no']);
                
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("J".$pos,$val['pay_method']);
            
                $pos += 1;
            }
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="券包每日数据管理('.date("Y-m-d H:i:s").').xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
            
        }

    /**
     * 现金券审核
     */
    public function check_index(){
        
        $start_time = urldecode(I("get.start_time",date('Y-m-d 16:00:00', strtotime('-1 days')),'strip_tags'));
        $end_time = urldecode(I("get.end_time",date("Y-m-d").' 16:00:00','strip_tags'));
        $status = I("get.status",3,'int');
        $cond = 'is_delete = 0';
        
        $start_time = str_replace('+',' ', $start_time);
        $end_time = str_replace('+',' ', $end_time);
        
        
        if ($start_time != "") {
            $cond .= " AND `modify_time` >= '$start_time' ";
        }
        if ($end_time != "") {            
            $cond .= " AND `modify_time` <= '$end_time'";
        }

        $cond .= " AND status = $status";

        $totalCnt =  M("UserCashCoupon")->where($cond)->count();
        $Page = new \Think\Page($totalCnt, $this->pageSize);
        $list = M("UserCashCoupon")->where($cond)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $res = array();
        if($list) {
            foreach ($list as $key =>$val) {
                $list[$key]['user_info']= $this->getUserInfo($val['user_id']);
                if($val['type'] == 1){
                    $list[$key]['type'] = '后台';
                } else if($val['type'] == 0){
                    $list[$key]['type']='系统';
                } else  if($val['type'] == 2){
                    $list[$key]['type']='抽奖';
                } else {
                    $list[$key]['type']='活动';
                }
            }
        }

        $total_money = M("UserCashCoupon")->where($cond)->sum('amount');
        $total_use_money = M("UserCashCoupon")->where($cond . ' and status = 1')->sum('amount');

        $param = array(
            "start_time" => $start_time,
            "end_time" => $end_time,
            'total_cnt'=>$totalCnt,
            'total_money' => $total_money,
            'total_use_money'=>$total_use_money,
            'status' => $status,
        );

        $this->assign("params",$param);
        $this->assign("list",$list);
        $this->assign('showPage',$Page->show());
        $this->display('check_index');
    }
    
    
    /**
     *
     * 日数据导出excel
     *
     */
    public function check_index_export_excel(){
    
        $start_time = urldecode(I("get.start_time",date('Y-m-d 16:00:00', strtotime('-1 days')),'strip_tags'));
        $end_time = urldecode(I("get.end_time",date("Y-m-d").' 16:00:00','strip_tags'));
        $status = I("get.status",3,'int');
        $cond = 'ucc.is_delete = 0';
        
        if ($start_time != "") {
            $cond .= " AND ucc.`modify_time` >= '$start_time' ";
        }
        if ($end_time != "") {
            $cond .= " AND ucc.`modify_time` <= '$end_time'";
        }
        
        $cond .= " AND ucc.status = $status";
        $sql = "SELECT ucc.*,u.username,u.real_name,u.platcust FROM `s_user_cash_coupon` as ucc left join s_user as u ON ucc.user_id = u.id WHERE ( $cond ) ORDER BY ucc.id desc";
        $list = M()->query($sql);      
          
        if($list){          
            vendor('PHPExcel.PHPExcel');    
            $objPHPExcel = new \PHPExcel();    
            $objPHPExcel->getProperties()
                ->setCreator("票票喵票据")
                ->setLastModifiedBy("票票喵现金券审核")
                ->setTitle("title")
                ->setSubject("subject")
                ->setDescription("description")
                ->setKeywords("keywords")
                ->setCategory("Category");
    
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('券包每日数据管理')
                ->setCellValue("A1", "编号")
                ->setCellValue("B1", "账号")
                ->setCellValue("C1", "用户姓名")
                ->setCellValue("D1", "存管平台账号")
                ->setCellValue("E1", "现金券标题")
                ->setCellValue("F1", "类型")
                ->setCellValue("G1", "金额")
                ->setCellValue("H1", "发放日期")
                ->setCellValue("I1", "使用日期")
                ->setCellValue("J1", "状态");
    
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
    
            // 设置列表值
            $pos = 2;
            $n = 1;    
            foreach ($list as $val) {  
    
                $type = '';
                if($val['type'] == 1){
                    $type = '后台';
                } else if($val['type'] == 0){
                    $type = '系统';
                } else  if($val['type'] == 2){
                    $type = '抽奖';
                } else {
                    $type = '活动';
                }
                
                $status = '';
                if($val['status'] == 3){
                    $status = '待审核';
                } else if($val['status'] == 1){
                    $status = '已审核';
                } else if($val['status'] == 4){
                    $status = '未审核通过';
                }
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);    
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['username']);    
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);    
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['platcust']);               
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['title']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$type);                
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$val['amount']);  
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['add_time']);    
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['modify_time']);    
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("J".$pos,$status);    
                $pos += 1;
            }
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="票票喵现金券审核('.date("Y-m-d H:i:s").').xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    }
    

    public function range_check(){        
        
        $start_time = urldecode(I("start_time",date('Y-m-d 16:00:00', strtotime('-1 days')),'strip_tags'));
        $end_time = urldecode(I("end_time",date("Y-m-d").' 16:00:00','strip_tags'));        
        $cond = 'is_delete = 0';        
        $start_time = str_replace('+',' ', $start_time);
        $end_time = str_replace('+',' ', $end_time);       
        
        if ($start_time != "") {
            $cond .= " AND `modify_time` >= '$start_time' ";
        }
        if ($end_time != "") {
            $cond .= " AND `modify_time` <= '$end_time'";
        }        
        $cond .= " AND status = 3";
        $list = M("UserCashCoupon")->where($cond)->field('id,status')->select();    

        //$ids = I('post.check_string');
        //$ids_array = explode(':',$ids);
        //foreach($ids_array as $id){
        foreach ($list as $val){
            
            $id = $val['id'];
    
            $UserCashCoupon = M('UserCashCoupon')->where(array('id'=>$id))->find();

            if($UserCashCoupon['status'] == 3) {
                $re = $this->check_done($id,$UserCashCoupon['user_id'],$UserCashCoupon['amount'],$UserCashCoupon['recharge_no']);
            }

        }
        $this->ajaxReturn(array('status' => 0, 'info' => "审核完成"));
    }


    public function check(){
        if(IS_AJAX) {

            $id = I('post.id', 0, 'int');

            if(!$id) {
                $this->ajaxReturn(array('status' => 0, 'info' => "参数错误"));
            }

            $UserCashCoupon = M('UserCashCoupon')->where(array('id'=>$id))->find();

            if($UserCashCoupon['status'] != 3) {
                $this->ajaxReturn(array('status' => 0, 'info' => "现金券使用失败"));
            }

            $re = $this->check_done($id,$UserCashCoupon['user_id'],$UserCashCoupon['amount'],$UserCashCoupon['recharge_no']);

            if($re === true){


                $this->ajaxReturn(array('status' => 1, 'info' => "审核成功"));


            }else{
                $this->ajaxReturn(array('status' => 0, 'info' => "审核失败"));
            }


        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => "非法访问"));
        }
    }

    public function uncheck(){
        if(IS_AJAX) {

            $id = I('post.id', 0, 'int');

            if(!$id) {
                $this->ajaxReturn(array('status' => 0, 'info' => "参数错误"));
            }

            $UserCashCoupon = M('UserCashCoupon')->where(array('id'=>$id))->find();

            if($UserCashCoupon['status'] != 3) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败"));
            }



            $cash = M('UserCashCoupon')->where(array('id'=>$id))
                ->setField('status', 4);

            M('UserWalletRecords')->where(array('recharge_no'=>$UserCashCoupon['recharge_no']))
                ->setField('pay_status', 3);

            if($cash){


                $this->ajaxReturn(array('status' => 1, 'info' => "操作成功"));


            }else{
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败"));
            }


        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => "非法访问"));
        }
    }


    public function check_done($record_id,$user_id,$amount,$recharge_no){


//        M('UserCashCoupon')->startTrans();
//        M('UserAccount')->startTrans();
//        M('UserWalletRecords')->startTrans();
        $modelDone = M();
        $modelDone->startTrans(); // 开启事务

        $da = [
            'status'=>1,
            'modify_time' => date('Y-m-d H:i:s')
        ];
        $cash = M('UserCashCoupon')->where(array('id'=>$record_id))->save($da);


        $account = M('UserAccount')->where(array('user_id'=>$user_id))
            ->setInc('wallet_totle', $amount);

        $da = [
            'pay_status'=>2,
            'modify_time' => date('Y-m-d H:i:s')
        ];

        $record = M('UserWalletRecords')->where(array('recharge_no'=>$recharge_no))->save($da);


        if($cash && $account && $record){

            $model = M('UserCashCoupon')->where(array('id'=>$record_id))->find();
            vendor('Fund.FD');
            vendor('Fund.sign');
            $fd  = new \FD();
            $user = M('User')->where(array('id'=>$model['user_id']))->find();
            $platcust =$user['platcust'];
            $postData = [
                'platcust' => $platcust,
                'amount' => $model['amount'],
                'cause_type' =>1,
                'acct_type'=>9
            ];


            $plainText =  \SignUtil::params2PlainText($postData);

            $sign =  \SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
            $postData['signdata'] = $sign;
            $data  = $fd->post('trade/plattrans',$postData);
            $data = json_decode($data);


        }

        if($data->success){ //$data->success == 'true'


            M('UserWalletRecords')->where(array('recharge_no'=>$recharge_no))
                ->setField('trade_no', $data->result);


            $modelDone->commit();
//            M('UserCashCoupon')->commit();
//            M('UserAccount')->commit();
//            M('UserWalletRecords')->commit();
            return true;
        }else{
            $modelDone->rollback();
//            M('UserCashCoupon')->rollback();
//            M('UserAccount')->rollback();
//            M('UserWalletRecords')->rollback();
            return false;
        }




    }


    /**
     * 现金券管理 excel
     */
    public function cash_index_excel() {

        $title = I("get.title","",'strip_tags');
        $start_time = I("get.start_time","",'strip_tags');
        $end_time = I("get.end_time","",'strip_tags');
        $status = I("get.status",-1,'int');

        $title = urldecode($title);

        $cond = 'is_delete = 0';

        if ($start_time != "") {
            $_start_time = $start_time .' 00:00:00';
            $cond .= " AND `modify_time` >= '$_start_time' ";

        }
        if ($end_time != "") {
            $_end_time = $end_time .' 23:59:59';
            $cond .= " AND `modify_time` <= '$_end_time'";
        }

        if ($title) {
            $cond .= " AND title like '$title%'";
        }

        if($status >=0) {
            $cond .= " AND status = '$status'";
        }

        $totalCnt =  M("UserCashCoupon")->where($cond)->count();

        $list = M("UserCashCoupon")->where($cond)->order("id desc")->select();
        $res = array();
        if($list) {
            foreach ($list as $val) {

                $val['user_info'] = $this->getUserInfo($val['user_id']);

                if($val['status'] == 0){
                    $val['status'] = '未使用';
                    $val['modify_time'] = '';
                    $val['color'] = '#080808';
                }else if($val['status'] == 1){
                    $val['status'] = '已使用';
                    $val['color'] = '#FF0000';
                }else if($val['status'] == 2){
                    $val['status'] = '已过期';
                    $val['color'] = '#8A8A8A';
                }else if($val['status'] == 3){
                    $val['status'] = '正在打款';
                    $val['color'] = '#FF0000';
                }else if($val['status'] == 4){
                    $val['status'] = '已冻结';
                    $val['color'] = '#FF0000';
                }else{
                    $val['status'] = '已过期';
                    $val['modify_time'] = '';
                    $val['color'] = '#8A8A8A';
                }

                if($val['type'] == 1){
                    $val['type'] = '后台';
                } else if($val['type'] == 0){
                    $val['type']='系统';
                } else  if($val['type'] == 2){
                    $val['type']='抽奖';
                } else {
                    $val['type']='活动';
                }
                /*
                if($val['type'] == 1){
                    $val['type'] = '后台';
                } else{
                    $val['type']='系统';
                }*/

                $res[] = $val;
            }
        }


        $total_money = M("UserCashCoupon")->where($cond)->sum('amount');
        $total_use_money = M("UserCashCoupon")->where($cond . ' and status = 1')->sum('amount');

        $param = array(
            "title" => $title,
            "start_time" => $start_time,
            "end_time" => $end_time,
            'total_cnt'=>$totalCnt,
            'total_money' => $total_money,
            'total_use_money'=>$total_use_money,
            'status' => $status,
        );

        $title = '现金券管理';

        vendor('PHPExcel.PHPExcel');

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")

            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");

        $objPHPExcel->setActiveSheetIndex(0)->setTitle($title.'用户使用名细')
            ->setCellValue("A1", "编号")
            ->setCellValue("B1", "姓名")
            ->setCellValue("C1", "账号")
            ->setCellValue("D1", "标题")
            ->setCellValue("E1", "类型")
            ->setCellValue("F1", "金额")
            ->setCellValue("G1", "状态")
            ->setCellValue("H1", "使用时间")
            ->setCellValue("I1", "发放时间");
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        // 设置列表值
        $pos = 2;
        $n = 1;
        $status = ["未使用","已使用","过期"];


        foreach ($res as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['id']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['user_info']['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['user_info']['username']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['title']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['type']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['amount']); // number_format($val['amount'], 2)
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['status']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['modify_time']);
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $val['add_time']);
            $pos += 1;
            $n++;
        }
        ob_end_clean();//清除缓冲区,避免乱码
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$title.'使用明细('.date("Y-m-d").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }



}
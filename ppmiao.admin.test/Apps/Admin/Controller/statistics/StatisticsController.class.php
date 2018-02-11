<?php
namespace Admin\Controller;

/**
 * 统计分析
 * @package Admin\Controller
 */
class StatisticsController extends AdminController{
    
    private $pageSize = 20;

    private $excelModel = null;

    protected function _initialize(){
        $this->excelModel = new \Admin\Model\ExcelModel();
    }

    /**
     * 充值用户统计
     */
    public function recharge(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $chn = I('get.chn', 0, 'int'); // 渠道ID
            $start_time = I('get.st', '');
            $end_time = I('get.et', '');
            
            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->field('id,cons_key,cons_value')->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            
            $this->assign('channel_list', $channelList);

            // 用户列表
            $userObj = M('User');
            $userAccount = M("UserAccount");
            $rechargeLogObj = M('RechargeLog');
            $userDueDetailObj = M("UserDueDetail");

            $cond[] = "status=2";
            $cond[] = "user_id>0";
            if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
            if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
            $conditions = implode(' and ', $cond);
            $ucondition = "id in (select user_id from s_recharge_log where " . $conditions . ")";
            if($chn) $ucondition .= " and channel_id=".$chn;

            $counts = $userObj->where($ucondition)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            
            $list = $userObj->field('id,username,real_name,channel,channel_id,add_time,status,card_no')
                            ->where($ucondition)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            
            $chnStr = "";
            
            if($chn) $chnStr = $constantObj->where(array('id'=>$chn))->getField('cons_value');
            
            //统计人数 和渠道相关
            $countInfo = $this->countSexAndChn($start_time,$end_time,$chn);
           
            
            foreach($list as $key => $val){
                unset($cond2);
                if($start_time) $cond2[] = "add_time>='".$start_time." 00:00:00.000000'";
                if($end_time) $cond2[] = "add_time<='".$end_time." 23:59:59.999000'";
                $cond2[] = "user_id=".$val['id'];
                $conditions2 = implode(' and ', $cond2);
                $rechargeList = $rechargeLogObj->where($conditions2.' and status=2')->select();//购买成功次数(支付成功)

                $orderCount = $rechargeLogObj->where($conditions2.' and status=1')->count();//下单失败次数(未支付)
                
                $interest = 0;
                $where = 'user_id='.$val['id'];
                
                if(count($rechargeList) > 0){ // 有充值记录的
                    $buyCount = 0;
                    $orderCount = $orderCount + count($rechargeList);
                    foreach($rechargeList as $k => $v){
                        if($v['status'] == 2) $buyCount += 1;
                    }
                    $list[$key]['buy_count'] = $buyCount; // 购买次数
                    $list[$key]['order_count'] = $orderCount; // 下单次数
                    if(!$rechargeLogObj->where('user_id='.$rechargeList[0]['user_id'].' and id<'.$rechargeList[0]['id'].' and status=2')->getField('id')){
                        $list[$key]['first_recharge'] = $rechargeList[0]['amount'];
                        unset($rechargeList[0]);
                    }
                    $secondTotle = 0;
                    foreach($rechargeList as $k => $v){
                        $secondTotle += $v['amount'];
                    }
                    $list[$key]['second_recharge'] = $secondTotle;
                    if(!$chnStr) {
                        $list[$key]['channelStr'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
                    }else{
                        $list[$key]['channelStr'] = $chnStr;
                    }
                    
                    if($start_time) $where .= " AND add_time>='".$start_time." 00:00:00.000000'";
                    if($end_time) $where .= " AND add_time<='".$end_time." 23:59:59.999000'";
                   
                    //产品收益
                    $interest += M('UserDueDetail')->where($where)->sum('due_interest');
                    
                } else {
                    $list[$key]['first_recharge'] = 0;
                    $list[$key]['second_recharge'] = 0;
                    $list[$key]['buy_count'] = 0; // 购买次数
                    $list[$key]['order_count'] = 0; // 下单次数
                    if(!$chnStr) {
                        $list[$key]['channelStr'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
                    }else{
                        $list[$key]['channelStr'] = $chnStr;
                    }
                }
                
                if($start_time) $where .= " AND interest_time>='".$start_time." 00:00:00.000000'";
                if($end_time) $where .= " AND interest_time<='".$end_time." 23:59:59.999000'";
                
                $interest += M('UserWalletInterest')->where($where)->sum('interest');
                
                $list[$key]['interest'] = $interest;
                
                // 计算用户年龄和性别
                $year = substr($val['card_no'], 6, 4);
                $nowYear = date('Y', time());
                $list[$key]['year'] = $nowYear - $year;
                $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
                if($sex % 2 != 0) {
                    $list[$key]['sex'] = '男';
                } else {
                    $list[$key]['sex'] = '女';
                }
                
                // 获取用户最后一次购买记录时间
                $lastestBuyTime = $userDueDetailObj->where(array('user_id'=>$val['id']))->order('add_time desc')->limit(1)->getField('add_time');
                if($lastestBuyTime) $list[$key]['lastest_buy_time'] = date('Y-m-d H:i:s', strtotime($lastestBuyTime));
                else $list[$key]['lastest_buy_time'] = '-';
                // 获取用户钱包余额
                $list[$key]['wallet'] = $userAccount->where(array('user_id'=>$val['id']))->getField('wallet_totle');
            }
            
            $_stime = date('Y-m-01', strtotime('-1 month'));
            $_etime = date('Y-m-01');
            
            $_cond = "add_time >='$_stime' AND add_time <'$_etime'";
            
            $k = md5($_cond);
            
            $dd = S($k);
            
            if(!$dd){
                $dd['reg_number'] =  M("User")->where($_cond)->count();
                $condition .= ' and real_name_auth = 1';//没有实名认证过
                $dd['trade_number'] = M("User")->where($_cond .$condition)->count();
                S($k, $dd,1800);
            }
           
            $params = array(
                'page' => $page,
                'chn' => $chn,
                'start_time' => ($start_time=='-'?'':$start_time),
                'end_time' => ($end_time=='-'?'':$end_time),
                'countInfo' =>$countInfo,
                'reg_info'=>$dd,
            );
            $this->assign('params', $params);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $chn = I('post.chn', 0, 'int');
            $start_time = trim(I('post.start_time', '-'));
            $end_time = trim(I('post.end_time', '-'));
            $quest = '/chn/'.$chn;
            if($start_time) $quest .= '/st/'.$start_time;
            if($end_time) $quest .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT') . '/statistics/recharge'.$quest);
        }
    }

    /**
     * 充值用户统计(新)
     */
    public function recharge_new(){
        if(!IS_POST) {
            $page = I('get.p', 1, 'int'); // 页码
            $chn = I('get.chn', 0, 'int'); // 渠道ID
            $start_time = I('get.st', '');
            $end_time = I('get.et', '');

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if (!$channelList) {
                $channelPid = $constantObj->where(array('cons_key' => 'channel', 'parent_id' => 0))->getField('id');
                $channelList = $constantObj->field('id,cons_key,cons_value')->where(array('parent_id' => $channelPid))->select();
                F('channel_list', $channelList);
            }

            $this->assign('channel_list', $channelList);

            // 用户列表
            $userObj = M('User');
            $userAccount = M("UserAccount");
            $rechargeLogObj = M('RechargeLog');
            $userDueDetailObj = M("UserDueDetail");

            $cond[] = "user_id>0";
          //  if (!$start_time) $start_time = date('Y-m-d');
            if ($start_time)  $cond[] = "add_time>='" . $start_time . " 00:00:00.000000'";
            if ($end_time) $cond[] = "add_time<='" . $end_time . " 23:59:59.999000'";
            $conditions = implode(' and ', $cond);
            $ucondition = "id in (select distinct(user_id) from s_user_due_detail where " . $conditions . ")  ";
            if ($chn) $ucondition .= " and channel_id=" . $chn;


            $counts = $userObj->where($ucondition)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();

            $list = $userObj->field('id,username,real_name,channel,channel_id,add_time,status,card_no')
                ->where($ucondition)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();


            if(!$list){

                $params = array(
                    'page' => $page,
                    'chn' => $chn,
                    'start_time' => ($start_time=='-'?'':$start_time),
                    'end_time' => ($end_time=='-'?'':$end_time),
                    // 'countInfo' =>$countInfo,
                    //  'reg_info'=>$dd,
                );


                $this->assign('params', $params);

                $this->assign('list', array());
                $this->display();
                return;
            }

            $condPay = $uId = array();

            foreach ($list as $l){
                $uId[] = $l['id'];
            }

            if($uId) $condPay[] = " and  user_id in ( ".implode(' , ', $uId).")";

            if ($start_time) $condPay[] = "add_time>='" . $start_time . " 00:00:00.000000'";
            if ($end_time) $condPay[] = "add_time<='" . $end_time . " 23:59:59.999000'";

            $conditionsPay = implode(' and ', $condPay);

            $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id >0   "  . $conditionsPay;
           // $sql .= " UNION all ";
            // $sql .= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 " . $conditionsPay
            $sql .=  "   ) as t  order by add_time ";


            $userPayList = M()->query($sql);
            $totalUserList = array();
            $firstPaySum = 0; // 首投金额
            $secondPaySum = 0;// 二投金额

            $userPayArr = array();


            foreach ($userPayList as $val) {
                $userId = $val['user_id'];
                if(!$totalUserList[$userId]){
                    $firstTime = $val['add_time'];
                    $firstPaySum = $val['due_capital'];
                    $userPayArr[$val['user_id']]['first_time'] = $firstTime;
                    $userPayArr[$val['user_id']]['first_pay_sum'] = $firstPaySum;
                }else if(count($totalUserList[$userId])==1){
                    $secondTime = $val['add_time'];
                    $secondPaySum = $val['due_capital'];
                    $userPayArr[$val['user_id']]['second_time'] = $secondTime;
                    $userPayArr[$val['user_id']]['second_pay_sum'] = $secondPaySum;
                }

               $totalUserList[$userId][] = $userId;
            }

            foreach ($list as $i=>$l){
                $list[$i]['add_time'] = M('user_account')->where( array('user_id'=>$l['id']) )->getField('add_time');
                $list[$i]['first_time'] = isset($userPayArr[$l['id']]['first_time'])?trim($userPayArr[$l['id']]['first_time']):'0';
                $list[$i]['first_pay_sum'] = isset($userPayArr[$l['id']]['first_pay_sum'])?intval($userPayArr[$l['id']]['first_pay_sum']):'';
                $list[$i]['second_time'] = isset($userPayArr[$l['id']]['second_time'])?trim($userPayArr[$l['id']]['second_time']):'0';
                $list[$i]['second_pay_sum'] = isset($userPayArr[$l['id']]['second_pay_sum'])?intval($userPayArr[$l['id']]['second_pay_sum']):'';
            }

            $params = array(
                'page' => $page,
                'chn' => $chn,
                'start_time' => ($start_time=='-'?'':$start_time),
                'end_time' => ($end_time=='-'?'':$end_time),
               // 'countInfo' =>$countInfo,
              //  'reg_info'=>$dd,
            );


            $this->assign('params', $params);
            $this->assign('show', $show);
            $this->assign('list', $list);

            $this->display();
        }else{
            $chn = I('post.chn', 0, 'int');
            $start_time = trim(I('post.start_time', '-'));
            $end_time = trim(I('post.end_time', '-'));
            $quest = '/chn/'.$chn;
            if($start_time) $quest .= '/st/'.$start_time;
            if($end_time) $quest .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT') . '/statistics/recharge_new'.$quest);
        }
    }
    /*
     * 算男女人数的
     * 
     */
    private function countSexAndChn($start_time,$end_time,$chn) {
        $res = array('man'=>0,'woman'=>0,'total'=>0,'android'=>0,'ios'=>0,'platform'=>0);
        //$cond[] = "status=2";
        //$cond[] = "user_id>0";
        
        if($start_time) 
            $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
        if($end_time) 
            $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
        $cond[] = 'real_name_auth = 1';
        $conditions = implode(' and ', $cond);
        //$ucondition = "id in (select user_id from s_recharge_log where " . $conditions . ")";
        if($chn) $conditions .= " and channel_id=".$chn;
        $list = M('User')->field('card_no,channel')->where($conditions)->select();
        foreach ($list as $val) {
            $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
            if($sex % 2 != 0) {
                $res['man'] += 1;
            } else {
                $res['woman'] += 1;
            }
            
            if($val['channel'] == "") {
                $res['platform'] += 1;
            } else if($val['channel'] == "AppStore" or $val['channel'] == "AppStore_personal" 
                    or $val['channel'] == "AppStore_pro" or $val['channel'] == "AppStore_common"){ 
                $res['ios'] += 1;
            } else{
                $res['android'] += 1;
            }
            
        }
        //$res['total'] = $res['man']+$res['woman'];
        return $res;
    }
    

    /**
     * 充值用户统计(导出Excel)
     */
    public function recharge_export(){
        vendor('PHPExcel.PHPExcel');
        $chn = I('get.chn', 0, 'int'); // 渠道ID
        $start_time = I('get.st', '');
        $end_time = I('get.et', '');
        $ex_count = I('get.excount', 20, 'int'); // 导出每页条数
        $ex_page = I('get.expage', 1, 'int'); // 导出页码

        // 用户列表
        $userObj = M('User');
        $rechargeLogObj = M('RechargeLog');
        $constantObj = M('Constant');
        $userDueDetailObj = M("UserDueDetail");

        $cond[] = "status=2";
        $cond[] = "user_id>0";
        if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
        if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
        $conditions = implode(' and ', $cond);
        $conditions = "id in (select user_id from s_recharge_log where " . $conditions . ")";
        if($chn) $conditions .= " and channel_id=".$chn;

        $list = $userObj->field('id,username,real_name,channel,channel_id,add_time,status,card_no')
                        ->where($conditions)->order('add_time desc')->limit(($ex_page-1)*$ex_count.','.$ex_count)->select();
        
        $chnStr = '';
        if($chn) $chnStr = $constantObj->where(array('id'=>$chn))->getField('cons_value');
        foreach($list as $key => $val){
            unset($cond2);
            if($start_time) $cond2[] = "add_time>='".$start_time." 00:00:00.000000'";
            if($end_time) $cond2[] = "add_time<='".$end_time." 23:59:59.999000'";
            $cond2[] = "user_id=".$val['id'];
            $conditions2 = implode(' and ', $cond2);
            $rechargeList = $rechargeLogObj->where($conditions2.' and status=2')->select();

            $orderCount = $rechargeLogObj->where($conditions2.' and status=1')->count();
            
            $interest = 0;
            
            if(count($rechargeList) > 0){ // 有充值记录的
                $buyCount = 0;
                $orderCount = $orderCount + count($rechargeList);
                foreach($rechargeList as $k => $v){
                    if($v['status'] == 2) $buyCount += 1;
                }
                $list[$key]['buy_count'] = $buyCount; // 购买次数
                $list[$key]['order_count'] = $orderCount; // 下单次数
                $list[$key]['first_recharge'] = $rechargeList[0]['amount'];
                unset($rechargeList[0]);
                $secondTotle = 0;
                foreach($rechargeList as $k => $v){
                    $secondTotle += $v['amount'];
                }
                $list[$key]['second_recharge'] = $secondTotle;
                if(!$chnStr) {
                    $list[$key]['channelStr'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
                }else{
                    $list[$key]['channelStr'] = $chnStr;
                }
                
            }else{
                $list[$key]['first_recharge'] = 0;
                $list[$key]['second_recharge'] = 0;
                $list[$key]['buy_count'] = 0; // 购买次数
                $list[$key]['order_count'] = 0; // 下单次数
                if(!$chnStr) {
                    $list[$key]['channelStr'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
                }else{
                    $list[$key]['channelStr'] = $chnStr;
                }
            }
            // 计算用户年龄和性别
            $year = substr($val['card_no'], 6, 4);
            $nowYear = date('Y', time());
            $list[$key]['age'] = $nowYear - $year;
            $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
            if($sex % 2 != 0) $list[$key]['sex'] = '男';
            else $list[$key]['sex'] = '女';
            // 获取用户最后一次购买记录时间
            $lastestBuyTime = $userDueDetailObj->where(array('user_id'=>$val['id']))->order('add_time desc')->limit(1)->getField('add_time');
            if($lastestBuyTime) $list[$key]['lastest_buy_time'] = date('Y-m-d H:i:s', strtotime($lastestBuyTime));
            else $list[$key]['lastest_buy_time'] = '-';
        }

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('用户统计')
            ->setCellValue("A1", "渠道")
            ->setCellValue("B1", "用户名字")
            ->setCellValue("C1", "手机号")
            ->setCellValue("D1", "年龄")
            ->setCellValue("E1", "性别")
            ->setCellValue("F1", "注册时间")
            ->setCellValue("G1", "最近购买时间")
            ->setCellValue("H1", "首冲金额(元)")
            ->setCellValue("I1", "后续充值(元)")
            ->setCellValue("J1", "购买次数");
        
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(22);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(14);
        
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['channelStr']); // 渠道
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']); // 
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['username']); // 手机号
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['age']); // 年龄
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['sex']); // 注册时间
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, date('Y-m-d H:i', strtotime($val['add_time']))); // 最近购买时间
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['lastest_buy_time']); // 首冲金额(元)
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, number_format($val['first_recharge'], 2)); // 后续充值(元)
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format($val['second_recharge'], 2)); // 购买次数
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $val['buy_count']); // 购买次数
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户统计_'.$ex_page.'_'.$ex_count.'_('.date('YmdHis', time()).').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 渠道管理
     */
    public function channel(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $count = 20; // 每页显示条数
            $constantObj = M('Constant');
            $channelPid = $constantObj->where("cons_key='channel' and parent_id=0")->getField('id');

            if($channelPid){
                $counts = $constantObj->where(array('parent_id'=>$channelPid))->count();
                $Page = new \Think\Page($counts, $count);
                $show = $Page->show();
                $list = $constantObj->where(array('parent_id'=>$channelPid))->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $this->assign('show', $show);
                $this->assign('list', $list);
            }
            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->display();
        }
    }

    /**
     * 添加渠道
     */
    public function channel_add(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $sign = trim(I('post.sign', '', 'strip_tags'));
            $address = trim(I('post.address', '', 'url'));

            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'渠道名称不能为空'));
            if(!$sign) $this->ajaxReturn(array('status'=>0,'info'=>'包名不能为空'));

            $constantObj = M('Constant');
            $channelParent = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->find();
            if(!$channelParent) $this->ajaxReturn(array('status'=>0,'info'=>'渠道分类不存在'));
            // 检查包名是否已存在
            if($constantObj->where(array('parent_id'=>$channelParent['id'],'cons_key'=>$sign))->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同包名的渠道'));
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $rows = array(
                'parent_id' => $channelParent['id'],
                'cons_key' => $sign,
                'cons_value' => $name,
                'cons_desc' => $address,
                'add_time' => $time,
                'modify_time' => $time,
                'add_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$constantObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加渠道失败,请重试'));
            F('channel_list', null); // 渠道列表缓存清空
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/statistics/channel/p/'.$page));
        }
    }

    /**
     * 渠道编辑
     */
    public function channel_edit(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $id = I('get.id', 0, 'int');
            $params = array(
                'page' => $page,
            );
            $this->assign('params', $params);

            $constantObj = M('Constant');
            $channelPid = $constantObj->where(array('cons_key'=>'channel'))->getField('id');
            if(!$channelPid){
                $this->error('渠道分类不存在');exit;
            }
            $detail = $constantObj->where(array('parent_id'=>$channelPid,'id'=>$id))->find();
            if(!$detail){
                $this->error('渠道信息不存在');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $page = I('post.page', 1, 'int');
            $id = I('post.id', 0, 'int');
            $name = trim(I('post.name', '', 'strip_tags'));
            $sign = trim(I('post.sign', '', 'strip_tags'));
            $address = trim(I('post.address', '', 'url'));

            if(!$name) $this->ajaxReturn(array('status'=>0,'info'=>'渠道名称不能为空'));
            if(!$sign) $this->ajaxReturn(array('status'=>0,'info'=>'包名不能为空'));

            $constantObj = M('Constant');
            // 检查包名是否已存在
            if($constantObj->where("id<>".$id." and cons_key='".$sign."'")->find()) $this->ajaxReturn(array('status'=>0,'info'=>'已存在相同包名的渠道'));
            $time = date('Y-m-d H:i:s').'.'.getMillisecond().'000';
            $rows = array(
                'cons_key' => $sign,
                'cons_value' => $name,
                'cons_desc' => $address,
                'modify_time' => $time,
                'modify_user_id' => $_SESSION[ADMIN_SESSION]['uid'],
            );
            if(!$constantObj->where(array('id'=>$id))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'编辑渠道失败,请重试'));
            F('channel_list', null); // 渠道列表缓存清空
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/statistics/channel/p/'.$page));
        }
    }

    /**
     * 渠道删除
     */
    public function channel_delete(){
        
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
        
        if (M('User')->where('channel_id='.$id)->count()){
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,该渠道已经有用户存在'));
        }
        $constantObj = M('Constant');
        if(!$constantObj->where(array('id'=>$id))->delete())
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 渠道统计
     */
    public function channel_statistics(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int');
            $chn = I('get.chn', 0, 'int');
            $start_time = I('get.st');
            $end_time = I('get.et');
            $count = 10;
            $doSearch = I('get.dosearch', 0, 'int'); // 是否执行搜索操作

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);

            if($doSearch){
                $userObj = M('User');
                $conditions = '';
                $cond[] = array();
                $rechargeLogObj = M('RechargeLog');
                $userAccount = M("UserAccount");
                if(!$chn){ // 全部渠道
                    $counts = count($channelList);
                    $Page = new \Think\Page($counts, $count);
                    $show = $Page->show();

                    $min = ($page - 1)*$count;
                    $max = $page*$count;
                    if($max > $counts) $max = $counts;
                    $list = array();
                    $pos = 0;
                    for($i = $min; $i < $max; $i++){
                        unset($cond);
                        $list[$pos] = $channelList[$i]; // 渠道信息
                        $cond[] = "user_id in (select id from s_user where channel_id=".$channelList[$i]['id'].")";
                        $cond[] = "user_id>0";
                        $cond[] = "status=2";
                        if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
                        if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
                        $conditions = implode(' and ', $cond);
                        $userPayList = $rechargeLogObj->where($conditions)->order('add_time')->select();

                        $firstUserList = array(); // 第一次付费用户数组
                        $nextUserList = array(); // 第二次付费用户数组
                        $firstPayPersonSum = 0; // 第一次充值人数
                        $nextPayPersonSum = 0; // 续费用户充值人数
                        $firstPaySum = 0; // 第一次付费用户充值数
                        $nextPaySum = 0; // 续费用户充值数
                        $walletSum = 0; // 钱包余额
                        $userIds = ''; // 用户id组合列表
                        foreach($userPayList as $key => $val){
                            $userIds .= ','.$val['user_id'];
                            //取红包金额                           
                            $bagAmount = M('UserRedenvelope')->where(array('recharge_no'=>$val['recharge_no'],'user_id'=>$val['user_id'],'status'=>1))->getField('amount');
                            
                            
                            if(!in_array($val['user_id'], $firstUserList)){ // 是第一次购买用户ID
                                array_push($firstUserList, $val['user_id']);
                                if(!$rechargeLogObj->where('user_id='.$val['user_id'].' and status=2 and id<'.$val['id'])->getField('id')) { // 检查这个时间点外该用户是否有购买(没有)
                                    $firstPayPersonSum += 1;
                                    $firstPaySum += $val['amount'];
                                    if ($bagAmount > 0) {
                                        $firstPaySum += $bagAmount;
                                    }
                                }else{
                                    array_push($nextUserList, $val['user_id']);
                                    $nextPayPersonSum += 1;
                                    $nextPaySum += $val['amount'];
                                    if ($bagAmount > 0) {
                                        $nextPaySum += $bagAmount;
                                    }
                                }
                            }else{ // 不是第一次购买用户ID
                                if(!in_array($val['user_id'], $nextUserList)){
                                    array_push($nextUserList, $val['user_id']);
                                    $nextPayPersonSum += 1;
                                    $nextPaySum += $val['amount'];
                                }else{
                                    $nextPaySum += $val['amount'];
                                }
                                if ($bagAmount > 0) {
                                    $nextPaySum += $bagAmount;
                                }
                            }
                        }
                        if($userIds) {
                            $userIds = substr($userIds, 1);
                            $walletSum = $userAccount->where("user_id in (".$userIds.")")->sum('wallet_totle');
                        }
                        $list[$pos]['first_pay_person_sum'] = $firstPayPersonSum;
                        $list[$pos]['first_pay_sum'] = $firstPaySum;
                        $list[$pos]['next_pay_person_sum'] = $nextPayPersonSum;
                        $list[$pos]['next_pay_sum'] = $nextPaySum;
                        $list[$pos]['wallet_sum'] = $walletSum;
                        $list[$pos]['user_count'] = $userObj->where(array('channel_id'=>$channelList[$i]['id']))->count();
                        $pos += 1;
                    }
                }else{ // 选择某个渠道后
                    $list = array();
                    foreach($channelList as $key => $val){
                        unset($cond);
                        if($val['id'] == $chn){
                            $cond[] = "user_id in (select id from s_user where channel_id=".$chn.")";
                            $cond[] = "user_id>0";
                            $cond[] = "status=2";
                            if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
                            if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
                            $conditions = implode(' and ', $cond);
                            $list[0] = $val;
                            $userPayList = $rechargeLogObj->where($conditions)->order('add_time')->select();

                            $firstUserList = array(); // 第一次付费用户数组
                            $nextUserList = array(); // 第二次付费用户数组
                            $firstPayPersonSum = 0; // 第一次充值人数
                            $nextPayPersonSum = 0; // 续费用户充值人数
                            $firstPaySum = 0; // 第一次付费用户充值数
                            $nextPaySum = 0; // 续费用户充值数
                            $walletSum = 0; // 钱包余额
                            $userIds = '';
                            foreach($userPayList as $key => $val){
                                $userIds .= ','.$val['user_id'];
                                
                                //取红包金额
                                $bagAmount = M('UserRedenvelope')->where(array('recharge_no'=>$val['recharge_no'],'user_id'=>$val['user_id'],'status'=>1))->getField('amount');
                                
                                if(!in_array($val['user_id'], $firstUserList)){ // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);
                                    if(!$rechargeLogObj->where('user_id='.$val['user_id'].' and status=2 and id<'.$val['id'])->getField('id')){ // 检查这个时间点外该用户是否有购买(没有)
                                        $firstPayPersonSum += 1;
                                        $firstPaySum += $val['amount'];
                                        if($bagAmount > 0){
                                            $firstPaySum += $bagAmount;
                                        }
                                    }else{ // 有买过,是复投用户
                                        array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPaySum += $val['amount'];
                                        if($bagAmount > 0 ) {
                                            $nextPaySum += $bagAmount;
                                        }
                                    }
                                }else{ // 不是第一次购买用户ID
                                    if(!in_array($val['user_id'], $nextUserList)){
                                        array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPaySum += $val['amount'];
                                    }else{
                                        $nextPaySum += $val['amount'];
                                    }
                                    
                                    if($bagAmount > 0 ) {
                                        $nextPaySum += $bagAmount;
                                    }
                                }
                            }
                            if($userIds){
                                $userIds = substr($userIds, 1);
                                $walletSum = $userAccount->where("user_id in (".$userIds.")")->sum('wallet_totle');
                            }
                            $list[0]['first_pay_person_sum'] = $firstPayPersonSum;
                            $list[0]['first_pay_sum'] = $firstPaySum;
                            $list[0]['next_pay_person_sum'] = $nextPayPersonSum;
                            $list[0]['next_pay_sum'] = $nextPaySum;
                            $list[0]['wallet_sum'] = $walletSum;
                            $list[0]['user_count'] = $userObj->where("channel_id=".$chn." and add_time>='".$start_time." 00:00:00.000000' and add_time<='".$end_time." 23:59:59.999000'")->count();
                            break;
                        }
                    }
                }
                $this->assign('list', $list);
                $this->assign('show', $show);
            }

            $params = array(
                'page' => $page,
                'chn' => $chn,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $this->assign('params', $params);
            $this->display();
        }else{
            $chn = I('post.chn', 0, 'int');
            $start_time = I('post.start_time');
            $end_time = I('post.end_time');
            $quest = '';
            if($start_time) $quest .= '/st/'.$start_time;
            if($end_time) $quest .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT') . '/statistics/channel_statistics/dosearch/1/chn/'.$chn.$quest);
        }
    }

    /**
     * 还款数据
     */
    public function repayment_data(){
        if(!IS_POST){
            $datetime = I('get.dt', date('Y-m-d', time()), 'strip_tags');
            $userDueDetailObj = M('UserDueDetail');
            $now = date('Y-m', time());
            $date = get_the_month($datetime);
            $datetime = date('Y-m', strtotime($datetime));

            $projectObj = M('Project');
            $userDueDetailObj = M('UserDueDetail');

            $cacheData = F('repayment_data_'.str_replace('-', '_', $datetime));
            $cacheData = null;
            if(!$cacheData){
                $maxDay = date('d', strtotime($date[1])); // 某个最大号数
                $categories = "";
                $totlePrice = ""; // 投资总金额
                $totleInterest = "";
                $price = 0; // 投资金额
                $ghostProce = 0; // 幽灵账户购买
                $interest = 0; // 利息
                $plusDescr = array();

                for($i = 1; $i <= $maxDay; $i++){
                    $plus = ''; // tooltip额外描述内容
                    $price = 0;
                    $interest = 0;
                    $categories .= ",'" . $i. "日'";
                    $idList = $projectObj->field('id,user_interest,start_time,end_time')->where("is_delete=0 and end_time>='".$datetime.'-'.$i." 00:00:00.000000' and end_time<='".$datetime.'-'.$i." 23:59:59.999000'")->select();
                    if($idList){
                        $ids = '';
                        foreach($idList as $key => $val){
                            $ids .= ','.$val['id'];
                            $subPrice = $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_capital');
                            $price += $subPrice;
                            $plus .= count_days(date('Y-m-d', strtotime($val['end_time'])), date('Y-m-d', strtotime($val['start_time']))).'天  '.$subPrice.'元  '.$val['user_interest'].'%<br>';
                        }
                        if($ids) {
                            $ids = substr($ids, 1);
                            //$price = $userDueDetailObj->where("project_id in (".$ids.") and user_id>0")->sum('due_capital'); // 去掉幽灵账户购买的总金额
                            $interest = $userDueDetailObj->where("project_id in (".$ids.") and user_id>0")->sum('due_interest'); // 去掉幽灵账户购买的总利息
                            
                            //addtime 20160510 xuhui
                            if(!$interest)$interest = 0;
                            
                            $totlePrice .= ",".$price;
                            $totleInterest .= ",".$interest;
                        }else{
                            $totlePrice .= ",0";
                            $totleInterest .= ",0";
                        }
                    }else{
                        $totlePrice .= ",0";
                        $totleInterest .= ",0";
                    }
                    $plusDescr[$i-1]['datetime'] = $i.'日';
                    $plusDescr[$i-1]['descr'] = $plus;
                }
                if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                if($totlePrice) $totlePrice = substr($totlePrice, 1);
                if($totleInterest) $totleInterest = substr($totleInterest, 1);
                $rows = array(
                    'plusDescr' => $plusDescr,
                    'categories' => $categories,
                    'totlePrice' => $totlePrice,
                    'totleInterest' => $totleInterest,
                );
                if($datetime < $now){
                    F('repayment_data_'.str_replace('-', '_', $datetime), $rows);
                }
            }else{
                $plusDescr = $cacheData['plusDescr'];
                $categories = $cacheData['categories'];
                $totlePrice = $cacheData['totlePrice'];
                $totleInterest = $cacheData['totleInterest'];
            }
            $this->assign('plus_descr', $plusDescr); // 图表tooltip额外显示内容信息
            $this->assign('categories', $categories);
            $this->assign('totle_price', $totlePrice);
            $this->assign('totle_interest', $totleInterest);
            $this->assign('dt', $datetime);
            $this->display();
        }else{
            $datetime = I('post.dt', '', 'strip_tags');
            if($datetime){
                redirect(C('ADMIN_ROOT').'/statistics/repayment_data/dt/'.$datetime);
            }else{
                redirect(C('ADMIN_ROOT').'/statistics/repayment_data');
            }
        }
    }

    /**
     * 用户查询(用户相关信息和购买相关信息)
     */
    public function user_search(){
        if(!IS_POST){
            $search = I('get.key', '', 'strip_tags');
            if($search){
                $userObj = M('User');
                if(is_numeric($search)){ // 手机号码
                    if(mb_strlen($search)>=15){
                        $search = urldecode($search);
                        $userInfo = $userObj->where(array('card_no' => $search))->select();
                    }else {
                        $userInfo = $userObj->where(array('username' => $search))->select();
                    }
                }else{ // 用户名字
                    $search = urldecode($search);
                    $userInfo = $userObj->where(array('real_name' => $search))->select();
                }
                if($userInfo){
                    $projectObj = M('Project');
                    $rechargeLogObj = M('RechargeLog');
                    $constantObj = M('Constant');
                    $userBankObj = M('UserBank');
                    $userAccountObj = M('UserAccount');
                    $userWalletRecordsObj =M('userWalletRecords');
                    $userDueDetailObj = M('userDueDetail');
                    $investmentDetailObj = M('investmentDetail');
                    $bankArr = array();
                    foreach($userInfo as $key => $val){
                        // 获取用户渠道信息
                        $userInfo[$key]['channelStr'] = $constantObj->where(array('id'=>$val['channel_id']))->getField('cons_value');
                        // 计算用户年龄和性别
                        $year = substr($userInfo[$key]['card_no'], 6, 4);
                        $nowYear = date('Y', time());
                        $userInfo[$key]['year'] = $nowYear - $year;
                        $sex = substr($userInfo[$key]['card_no'], strlen($userInfo[$key]['card_no']) - 2, 1);
                        if($sex % 2 != 0) $userInfo[$key]['sex'] = '男';
                        else $userInfo[$key]['sex'] = '女';

                        // 获取用户购买记录
                        $rechargeLogList = $rechargeLogObj->where(array('user_id'=>$val['id'],'status'=>2))->order('add_time desc')->select();
                        foreach($rechargeLogList as $k => $v){
                            $rechargeLogList[$k]['project_info'] = $projectObj->field('title,end_time,user_interest')->where(array('id'=>$v['project_id']))->find();
                            if($v['type'] != 3){ // 不是用钱包购买
                                if(!$bankArr[$v['card_no']]){
                                    $userBankInfo = $userBankObj->field('id,bank_name,acct_name')->where("bank_card_no='".$v['card_no']."' and has_pay_success=2")->find();
                                    $userBankId = $userBankInfo['id'];
                                    $userBankFrom = $userBankInfo['bank_name'];
                                    $userBankUname = $userBankInfo['acct_name'];
                                    $userBankAddress = $userBankInfo['bank_address'];
                                    $userBankArea = $userBankInfo['area'];
                                    array_push($bankArr, array($v['card_no']=>$userBankInfo));
                                }else{
                                    $userBankId = $bankArr[$v['card_no']]['id'];
                                    $userBankFrom = $bankArr[$v['card_no']]['bank_name'];
                                    $userBankUname = $bankArr[$v['card_no']]['acct_name'];
                                    $userBankAddress = $bankArr[$v['card_no']]['bank_address'];
                                    $userBankArea = $bankArr[$v['card_no']]['area'];
                                }
                            }else{
                                $userBankId = 0;
                                $userBankFrom = '';
                                $userBankUname = '';
                                $userBankAddress = '';
                                $userBankArea = '';
                            }
                            //产品的期限，利率，利息
                            $investmentDetailArr = $investmentDetailObj->field('id')->where(array('user_id'=>$v['user_id'],'project_id'=>$v['project_id'],'recharge_no'=>$v['recharge_no']))->find();
                            $product_due_list_arr = $userDueDetailObj->field('due_interest,duration_day,interest_coupon')->where(array('user_id'=>$v['user_id'],'project_id'=>$v['project_id'],'invest_detail_id'=>$investmentDetailArr['id']))->find();
                            $rechargeLogList[$k]['duration_day'] = $product_due_list_arr['duration_day']; // 产品期限
                            $rechargeLogList[$k]['due_interest'] = $product_due_list_arr['due_interest']; // 产品利息
                            $rechargeLogList[$k]['interest_coupon'] = $product_due_list_arr['interest_coupon'] ? $product_due_list_arr['interest_coupon'] : '0.00'; // 加息券金额

                            $rechargeLogList[$k]['card_id'] = $userBankId; // 银行卡ID
                            $rechargeLogList[$k]['card_from'] = $userBankFrom; // 银行卡所属地
                            $rechargeLogList[$k]['card_uname'] = $userBankUname; // 银行卡拥有者名称
                            $rechargeLogList[$k]['card_address'] = $userBankAddress; // 银行卡支行信息
                            $rechargeLogList[$k]['card_area'] = $userBankArea; // 银行卡支行区域信息
							$rechargeLogList[$k]['recharge_id'] = $v["id"]; // 下单记录ID
                            $rechargeLogList[$k]['recharge_no'] = $v["recharge_no"]; // 充值编码
                            $rechargeLogList[$k]['project_id'] = $v["project_id"]; // 产品id

                        }
                        $userInfo[$key]['recharge_log_list'] = $rechargeLogList;
                        // 获取用户票票喵币
                        $accountInfo = $userAccountObj->field('wallet_totle,stone_money')->where(array('user_id'=>$val['id']))->find();
                        $userInfo[$key]['wallet_totle'] = $accountInfo['wallet_totle'];
                        $userInfo[$key]['money_stone'] = $accountInfo['stone_money'];
                        //用户充值总额
                        $takein_wallet_total = $userWalletRecordsObj->where("user_id = ".$val['id']." and type=1 and pay_status=2")->sum('value');
                        $userInfo[$key]['takein_wallet_total'] = $takein_wallet_total;
                        //用户提现总额
                        $takeout_wallet_total = $userWalletRecordsObj->where("user_id = ".$val['id']." and type=2")->sum('value');
                        $userInfo[$key]['takeout_wallet_total'] = $takeout_wallet_total;
                        $userInfo[$key]['key'] = $search;
                        $userInfo[$key]['register_time'] = date("Y-m-d",strtotime($val['add_time']));
                        //用户省份

                        $userInfo[$key]['province'] = $val['province'];
                        
                        $userInfo[$key]['app_version'] = $val['app_version'];
                    }
                    //$this->assign('recharge_log_list', $rechargeLogList);
                    $this->assign('user_info', $userInfo);
                }
            }
            $this->assign('key', $search);
            $this->display();
        }else{
            $search = I('post.key', '', 'strip_tags');
            redirect(C('ADMIN_ROOT').'/statistics/user_search'.($search != '' ? '/key/'.$search : ''));
        }
    }

    /**
     * 更新用户支行信息
     */
    public function user_bank_update()
    {
        if (!IS_POST || !IS_AJAX) exit;

        $bank_id = I('post.bid', 0, 'int');
        $bank_info = I('post.binfo', '', 'strip_tags');

        $userBankObj = M('UserBank');

        if(!$userBankObj->where(array('id'=>$bank_id))->save(array('bank_address'=>$bank_info))) $this->ajaxReturn(array('status'=>0,'info'=>'更新失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 更新用户手机号码
     */
    public function user_update(){
        if(!IS_POST || !IS_AJAX) exit;

        $act = I('post.act', '', 'strip_tags');
        $uid = I('post.uid', 0, 'int');

        switch($act){
            case 'alert_phone':
                $userObj = M('User');
                $userBankObj = M("UserBank");
                $phone = I('post.phone', '', 'strip_tags');
                $rows = array(
                    'username' => $phone,
                    'mobile' => $phone,
                );
                $oldPhone = $userObj->where(array('id'=>$uid))->getField('username');
                $userObj->startTrans();
                if($userObj->where(array('id'=>$uid))->save($rows) === false) {
                    $userObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'修改失败,请重试'));
                }
                $bankRows = array(
                    'mobile' => $phone,
                    'modify_time'=>date('Y-m-d H:i:s',time())
                );
                if($userBankObj->where(array('user_id'=>$uid,'mobile'=>$oldPhone))->save($bankRows)=== false){
                    $userObj->rollback();
                    $this->ajaxReturn(array('status'=>0,'info'=>'修改失败,请重试'));
                }
                $userObj->commit();
                $this->ajaxReturn(array('status'=>1));
                break;
            case 'stone_money':
                $userAccountObj = M('UserAccount');
                $constantObj = M('Constant');
                $userMoneyRecordsObj = M('UserMoneyRecords');
                $stoneType = $constantObj->where(array('cons_key'=>'money_stone'))->getField('id');
                $stoneMoney = I('post.money', 0, 'int');
                if($stoneMoney != 0){
                    $recordArr = array(
                        'user_id' => $uid,
                        'value' => $stoneMoney,
                        'descr' => '系统赠送',
                        'type' => $stoneType,
                        'add_time' => date('Y-m-d H:i:s', time()).'.'.getMillisecond().'000',
                    );
                    $userMoneyRecordsObj->startTrans();
                    if(!$userMoneyRecordsObj->add($recordArr)){
                        $userMoneyRecordsObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'赠送失败,请重试'));
                    }
                    if($stoneMoney > 0){
                        $result = $userAccountObj->where(array('user_id'=>$uid))->setInc('stone_money', $stoneMoney);
                    }else{
                        $result = $userAccountObj->where(array('user_id'=>$uid))->setDec('stone_money', abs($stoneMoney));
                    }
                    if(!$result){
                        $userMoneyRecordsObj->rollback();
                        $this->ajaxReturn(array('status'=>0,'info'=>'赠送失败,请重试'));
                    }
                    $userMoneyRecordsObj->commit();
                    $this->ajaxReturn(array('status'=>1));
                }
                break;
        }
        $this->ajaxReturn(array('status'=>0,'info'=>'非法操作'));
    }

    /**
     * 每日统计
     */
    public function daily_statistics(){
        if(!IS_POST){
            $chn = I('get.chn', '', 'int');
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', '', 'strip_tags');
            $params = array(
                'chn' => $chn,
                'st' => $start_time,
                'et' => $end_time,
            );
            $this->assign('params', $params);

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);

            if($start_time && $end_time){
                $now_time = $end_time;
                $list = array();
                $rows = array();
                $cond = array();
                $conditions = '';

//                $rechargeLogObj = M('RechargeLog');
                $userDueDetailObj = M('UserDueDetail');
                $activationDeviceObj = M('ActivationDevice');
                $userWalletRecordsObj = M("UserWalletRecords");
                $userObj = M("User");
                if(!$chn){ // 全部渠道
                    $sumNewSecondPayPersonCount = 0; // 新增二次用户
                    $sumTotlePayPersonCount = 0;
                    $sumTotleCount = 0; // 合计投资总次数
                    $sumFirstPayPerson = 0;
                    $sumNextPayPerson = 0;
                    $sumNextPayTimes = 0;
                    $sumFirstPay = 0;
                    $sumNextPay = 0;
                    $sumBackPay = 0; // 还款总数
                    $sumActivation = 0; // 激活用户总数
                    $sumActivationPay = 0; // 当日激活当日投总数
                    $sumWalletSecondPay = 0; // 新增钱包二次投资数
                    $sumThreePayPerson = 0; // 三投用户数量
                    $sumFourPayPerson = 0; // 四投用户数量
                    $sumFivePayPerson = 0; // 五投用户数量
                    while(true){
                        unset($rows);
                        unset($cond);
                        $rows['datetime'] = $now_time;
                        $cond[] = "user_id>0";
                        $cond[] = "status=2";
                        $cond[] = "add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'";
                        $conditions = implode(' and ', $cond);
                        //$userPayList = $rechargeLogObj->where($conditions)->order('add_time')->select();
                        $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' ";
                        $sql.= "UNION all ";
                        $sql.= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000') as t order by add_time";
                        $userPayList = M()->query($sql);

                        $firstUserList = array(); // 第一次付费用户数组
                        $nextUserList = array(); // 第二次付费用户数组
                        $userIdList = array(); // 用户ID数组(防止统计重复用户)
                        $firstPayPersonSum = 0; // 首投人数
                        $nextPayPersonSum = 0; // 复投人数
                        $newSecondPayPersonCount = 0; // 新增二次人数
                        $nextPayTimes = 0; // 复投次数
                        $firstPaySum = 0; // 首投总额
                        $nextPaySum = 0; // 复投总额
                        $totleCount = 0; // 投资总次数
                        $threePayPersonSum = 0; // 三投人数
                        $fourPayPersonSum = 0; // 四投人数
                        $fivePayPersonSum = 0; // 五投人数
                        $totlePayPersonCount = 0; // 总投用户数
                        $totleWalletSecondPay = 0; // 新增钱包二次投资
                        $totleRegisteredUsers = 0;//注册用户数

                        $backPaySum = $userDueDetailObj->where("user_id>0 and due_time>='".$now_time." 00:00:00.000000' and due_time<='".$now_time." 23:59:59.999000'")->sum('due_capital'); // 还款总额
                        $activationCount = $activationDeviceObj->where("active_time>='".$now_time." 00:00:00.000000' and active_time<='".$now_time." 23:59:59.999000'")->count(); // 设备激活数
                        $activationPayCountArr = $userDueDetailObj->query("select count(id) as count from s_user_due_detail where user_id in (select id from s_user where device_serial_id in (select device_serial_id from s_activation_device where active_time>='".$now_time." 00:00:00.000000' and active_time<='".$now_time." 23:59:59.999000')) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'");
                        $activationPayCount = $activationPayCountArr[0]['count']; // 当日激活当日投数量
                        foreach($userPayList as $key => $val){
                            if($val['frm'] == 1){
                                $totleCount += 1;
                                if(!in_array($val['user_id'], $firstUserList)){ // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);
                                    $totlePayPersonCount += 1;
                                    if(!$userDueDetailObj->where('user_id='.$val['user_id'].' and id<'.$val['id'])->getField('id')){
                                        $firstPayPersonSum += 1;
                                        $firstPaySum += $val['due_capital'];
                                    }else{
                                        if(!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                                }else{ // 不是第一次购买用户ID
                                    if(!in_array($val['user_id'], $nextUserList)){
                                        if(!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }else{
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                                }
                            }
                            if(!in_array($val['user_id'], $userIdList)) {
                                array_push($userIdList, $val['user_id']);
                                // 统计产品和钱包的新增二次用户
                                if($userDueDetailObj->where("user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000'")->count() == 1){
                                    if($val['frm'] == 1) {
                                        $newSecondPayPersonCount += 1;
                                    } else {
                                        $totleWalletSecondPay += 1;
                                    }
                                }
                                // 计算3投以上(包含3投)用户数量
                                $sql2 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000'";
                                $sql2.= " UNION all ";
                                $sql2.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000') as t order by add_time asc";
                                $beforTimes = count(M()->query($sql2)); // 今天之前下过几笔订单(产品+钱包)
                                $sql3 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time>='".$now_time." 00:00:00.000000'";
                                $sql3.= " UNION all ";
                                $sql3.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time>='".$now_time." 00:00:00.000000') as t order by add_time asc";
                                $nowTimes = count(M()->query($sql3)); // 今天下过的订单数量(产品+钱包)
                                switch($beforTimes){
                                    case 2: // 3投用户
                                        $threePayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 4) $fourPayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                        break;
                                    case 3: // 4投用户
                                        $fourPayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                        break;
                                    case 4: // 5投用户
                                        $fivePayPersonSum += 1;
                                        break;
                                }
                            }
                        }
                        //当日注册用户数
                        $totleRegisteredUsers = $userObj->where("add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->count();

                        $rows['new_second_pay_person_count'] = $newSecondPayPersonCount;
                        $rows['totle_pay_person_count'] = $totlePayPersonCount;
                        $rows['totle_count'] = $totleCount;
                        $rows['first_pay_person_sum'] = $firstPayPersonSum;
                        $rows['first_pay_sum'] = $firstPaySum;
                        $rows['next_pay_person_sum'] = $nextPayPersonSum;
                        $rows['next_pay_sum'] = $nextPaySum;
                        $rows['next_pay_times'] = $nextPayTimes;
                        $rows['back_pay_sum'] = $backPaySum;
                        $rows['activation_count'] = $activationCount;
                        $rows['activation_pay_count'] = $activationPayCount;
                        $rows['totle_wallet_second_pay'] = $totleWalletSecondPay;
                        $rows['three_pay_person_sum'] = $threePayPersonSum;
                        $rows['four_pay_person_sum'] = $fourPayPersonSum;
                        $rows['five_pay_person_sum'] = $fivePayPersonSum;
                        $rows['registered_users'] = $totleRegisteredUsers;
                        array_push($list, $rows);

                        // 计入合计
                        $sumNewSecondPayPersonCount += $newSecondPayPersonCount;
                        $sumTotlePayPersonCount += $totlePayPersonCount;
                        $sumTotleCount += $totleCount;
                        $sumFirstPayPerson += $firstPayPersonSum;
                        $sumNextPayPerson += $nextPayPersonSum;
                        $sumFirstPay += $firstPaySum;
                        $sumNextPay += $nextPaySum;
                        $sumNextPayTimes += $nextPayTimes;
                        $sumBackPay += $backPaySum;
                        $sumActivation += $activationCount;
                        $sumActivationPay += $activationPayCount;
                        $sumWalletSecondPay += $totleWalletSecondPay;
                        $sumThreePayPerson += $threePayPersonSum;
                        $sumFourPayPerson += $fourPayPersonSum;
                        $sumFivePayPerson += $fivePayPersonSum;

                        if($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                        else break;
                    }
                }else{ // 某个渠道
                    $sumNewSecondPayPersonCount = 0; // 新增二次用户
                    $sumTotlePayPersonCount = 0;
                    $sumTotleCount = 0; // 合计投资总次数
                    $sumFirstPayPerson = 0; // 首投用户数
                    $sumNextPayPerson = 0; // 复投用户数
                    $sumNextPayTimes = 0; // 复投次数
                    $sumFirstPay = 0; // 首投金额
                    $sumNextPay = 0; // 复投金额
                    $sumBackPay = 0; // 还款总数
                    $sumActivation = 0; // 设备激活总数
                    $sumActivationPay = 0; // 当日激活当日投总数
                    $sumWalletSecondPay = 0; // 新增钱包二次投资数
                    $sumThreePayPerson = 0; // 三投用户数量
                    $sumFourPayPerson = 0; // 四投用户数量
                    $sumFivePayPerson = 0; // 五投用户数量
                    while(true){
                        unset($rows);
                        unset($cond);
                        $rows['datetime'] = $now_time;
                        $cond[] = "user_id in (select id from s_user where channel_id=".$chn.")";
                        $cond[] = "user_id>0";
                        $cond[] = "status=2";
                        $cond[] = "add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'";
                        $conditions = implode(' and ', $cond);
                        $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' ";
                        $sql.= "UNION all ";
                        $sql.= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000') as t ";
                        $sql.= "where user_id in (select id from s_user where channel_id=".$chn.") order by add_time";
                        $userPayList = M()->query($sql);

                        $firstUserList = array(); // 第一次付费用户数组
                        $nextUserList = array(); // 第二次付费用户数组
                        $userIdList = array(); // 用户ID数组(防止统计重复用户)
                        $firstPayPersonSum = 0; // 首投人数
                        $nextPayPersonSum = 0; // 复投人数
                        $newSecondPayPersonCount = 0; // 新增二次人数
                        $nextPayTimes = 0; // 复投次数
                        $firstPaySum = 0; // 首投总额
                        $nextPaySum = 0; // 复投总额
                        $threePayPersonSum = 0; // 三投人数
                        $fourPayPersonSum = 0; // 四投人数
                        $fivePayPersonSum = 0; // 五投人数
                        $totleCount = 0; // 投资总次数
                        $totlePayPersonCount = 0; // 总投用户数
                        $totleWalletSecondPay = 0; // 新增钱包二次投资
                        $totleRegisteredUsers = 0;//注册用户数

                        $backPaySum = $userDueDetailObj->where("user_id in (select id from s_user where channel_id=".$chn.") and user_id>0 and due_time>='".$now_time." 00:00:00.000000' and due_time<='".$now_time." 23:59:59.999000'")->sum('due_capital');
                        $activationCount = $activationDeviceObj->where("channel_id=".$chn." and active_time>='".$now_time." 00:00:00.000000' and active_time<='".$now_time." 23:59:59.999000'")->count(); // 设备激活数
                        $activationPayCountArr = $userDueDetailObj->query("select count(id) as count from s_user_due_detail where user_id in (select id from s_user where device_serial_id in (select device_serial_id from s_activation_device where channel_id=".$chn." and active_time>='".$now_time." 00:00:00.000000' and active_time<='".$now_time." 23:59:59.999000')) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'");

                        $activationPayCount = $activationPayCountArr[0]['count']; // 当日激活当日投数量
                        foreach($userPayList as $key => $val){
                            if($val['frm'] == 1){
                                $totleCount += 1;
                                if(!in_array($val['user_id'], $firstUserList)){ // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);
                                    $totlePayPersonCount += 1;
                                    if(!$userDueDetailObj->where('user_id='.$val['user_id'].' and id<'.$val['id'])->getField('id')){
                                        $firstPayPersonSum += 1;
                                        $firstPaySum += $val['due_capital'];
                                    }else{
                                        if(!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                                }else{ // 不是第一次购买用户ID
                                    if(!in_array($val['user_id'], $nextUserList)){
                                        if(!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }else{
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                                }
                            }
                            if(!in_array($val['user_id'], $userIdList)) {
                                array_push($userIdList, $val['user_id']);
                                // 统计产品和钱包的新增二次用户
                                if($userDueDetailObj->where("user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000'")->count() == 1){
                                    if($val['frm'] == 1) {
                                        $newSecondPayPersonCount += 1;
                                    } else {
                                        $totleWalletSecondPay += 1;
                                    }
                                }
                                // 计算3投以上(包含3投)用户数量
                                $sql2 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000'";
                                $sql2.= " UNION all ";
                                $sql2.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time<'".$now_time." 00:00:00.000000') as t order by add_time asc";
                                $beforTimes = count(M()->query($sql2)); // 今天之前下过几笔订单(产品+钱包)
                                $sql3 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=".$val['user_id']." and add_time>='".$now_time." 00:00:00.000000'";
                                $sql3.= " UNION all ";
                                $sql3.= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=".$val['user_id']." and add_time>='".$now_time." 00:00:00.000000') as t order by add_time asc";
                                $nowTimes = count(M()->query($sql3)); // 今天下过的订单数量(产品+钱包)
                                switch($beforTimes){
                                    case 2: // 3投用户
                                        $threePayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 4) $fourPayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                        break;
                                    case 3: // 4投用户
                                        $fourPayPersonSum += 1;
                                        if($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                        break;
                                    case 4: // 5投用户
                                        $fivePayPersonSum += 1;
                                        break;
                                }
                            }
                        }
                        //当日注册用户数
                        $totleRegisteredUsers = $userObj->where("channel_id=".$chn." and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->count();
                        $rows['new_second_pay_person_count'] = $newSecondPayPersonCount;
                        $rows['totle_pay_person_count'] = $totlePayPersonCount;
                        $rows['totle_count'] = $totleCount;
                        $rows['first_pay_person_sum'] = $firstPayPersonSum;
                        $rows['first_pay_sum'] = $firstPaySum;
                        $rows['next_pay_person_sum'] = $nextPayPersonSum;
                        $rows['next_pay_sum'] = $nextPaySum;
                        $rows['next_pay_times'] = $nextPayTimes;
                        $rows['back_pay_sum'] = $backPaySum;
                        $rows['activation_count'] = $activationCount;
                        $rows['activation_pay_count'] = $activationPayCount;
                        $rows['totle_wallet_second_pay'] = $totleWalletSecondPay;
                        $rows['registered_users'] = $totleRegisteredUsers;

                        // 计入合计
                        $sumNewSecondPayPersonCount += $newSecondPayPersonCount;
                        $sumTotlePayPersonCount += $totlePayPersonCount;
                        $sumTotleCount += $totleCount;
                        $sumFirstPayPerson += $firstPayPersonSum;
                        $sumNextPayPerson += $nextPayPersonSum;
                        $sumFirstPay += $firstPaySum;
                        $sumNextPay += $nextPaySum;
                        $sumNextPayTimes += $nextPayTimes;
                        $sumBackPay += $backPaySum;
                        $sumActivation += $activationCount;
                        $sumActivationPay += $activationPayCount;
                        $sumWalletSecondPay += $totleWalletSecondPay;
                        $rows['three_pay_person_sum'] = $threePayPersonSum;
                        $rows['four_pay_person_sum'] = $fourPayPersonSum;
                        $rows['five_pay_person_sum'] = $fivePayPersonSum;
                        array_push($list, $rows);
                        if($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                        else break;
                    }
                }
                $sumParams = array(
                    'sumNewSecondPayPersonCount' => $sumNewSecondPayPersonCount,
                    'sumTotlePayPersonCount' => $sumTotlePayPersonCount,
                    'sumTotleCount' => $sumTotleCount,
                    'sumFirstPayPerson' => $sumFirstPayPerson,
                    'sumNextPayPerson' => $sumNextPayPerson,
                    'sumFirstPay' => $sumFirstPay,
                    'sumNextPay' => $sumNextPay,
                    'sumNextPayTimes' => $sumNextPayTimes,
                    'sumBackPay' => $sumBackPay,
                    'sumActivation' => $sumActivation,
                    'sumActivationPay' => $sumActivationPay,
                    'sumWalletSecondPay' => $sumWalletSecondPay,
                    'sumThreePayPerson' => $sumThreePayPerson,
                    'sumFourPayPerson' => $sumFourPayPerson,
                    'sumFivePayPerson' => $sumFivePayPerson,
                );
                $this->assign('sumParams', $sumParams);
                $this->assign('list', $list);
            }
            $this->display();
        }else{
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $chn = I('post.chn', '', 'int');
            $query = '';
            if($chn) $query .= '/chn/'.$chn;
            if($start_time) $query .= '/st/'.$start_time;
            if($end_time) $query .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT').'/statistics/daily_statistics'.$query);
        }
    }

    /**
     * 销售图表
     */
    public function sales_figures(){
        if(!IS_POST){
            $target = I('get.target', '', 'strip_tags');
            $datetime = I('get.dt', date('Y-m-d', time()), 'strip_tags');

            $rechargeLog = M('RechargeLog');
            switch($target){
                case 'daily': // 每日数据
                    $now = date('Y-m-d', time());
                    $cacheData = F('sales_figures_daily_'.str_replace('-', '_', $datetime));

                    if(!$cacheData){
                        $rows = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);
                        $categories = "";
                        $totlePrice = ""; // 投资总金额
                        $totlePerson = ""; // 投资总人数
                        $price = 0; // 投资金额
                        $person = 0; // 投资人数
                        $time = 0;
                        $list = $rechargeLog->field('amount,add_time')->where("user_id>0 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000' and status=2")->order('add_time')->select();
                        foreach($rows as $key => $val){
                            $price = 0;
                            $person = 0;
                            $categories .= ",'" . $val. "点'";
                            foreach($list as $k => $v){
                                $time = strtotime($v['add_time']);
                                if($time >= strtotime($datetime.' '.($val>9?$val:'0'.$val).':00:00.000000') and $time <= strtotime($datetime.' '.($val>9?$val:'0'.$val).':59:59.999000')){
                                    $price += $v['amount'];
                                    $person += 1;
                                }
                            }
                            $totlePrice .= ",".$price;
                            $totlePerson .= ",".$person;
                        }
                        if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                        if($totlePrice) $totlePrice = substr($totlePrice, 1);
                        if($totlePerson) $totlePerson = substr($totlePerson, 1);
                        $rows = array(
                            'categories' => $categories,
                            'totlePrice' => $totlePrice,
                            'totlePerson' => $totlePerson,
                        );
                        if($datetime < $now){
                            F('sales_figures_daily_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $categories = $cacheData['categories'];
                        $totlePrice = $cacheData['totlePrice'];
                        $totlePerson = $cacheData['totlePerson'];
                    }

                    $this->assign('categories', $categories);
                    $this->assign('totle_price', $totlePrice);
                    $this->assign('totle_person', $totlePerson);
                    $this->assign('dt', $datetime);
                    $this->display('sales_figures_daily');
                    exit;
                    break;
                case 'monthly': // 每月数据
                    $now = date('Y-m', time());
                    $date = get_the_month($datetime);
                    $datetime = date('Y-m', strtotime($datetime));
                    $cacheData = F('sales_figures_monthly_'.str_replace('-', '_', $datetime));
                    if(!$cacheData){
                        $maxDay = date('d', strtotime($date[1])); // 某个最大号数
                        $categories = "";
                        $totlePrice = ""; // 投资总金额
                        $totlePerson = ""; // 投资总人数
                        $price = 0; // 投资金额
                        $person = 0; // 投资人数
                        $time = 0;
                        $list = $rechargeLog->field('amount,add_time')->where("user_id>0 and add_time>='".$date[0]." 00:00:00.000000' and add_time<='".$date[1]." 23:59:59.999000' and status=2")->order('add_time')->select();
                        for($i = 1; $i <= $maxDay; $i++){
                            $price = 0;
                            $person = 0;
                            $categories .= ",'" . $i. "日'";
                            foreach($list as $k => $v){
                                $time = strtotime($v['add_time']);
                                if($time >= strtotime($datetime.'-'.($i>9?$i:'0'.$i).' 00:00:00.000000') and $time <= strtotime($datetime.'-'.($i>9?$i:'0'.$i).' 23:59:59.999000')){
                                    $price += $v['amount'];
                                    $person += 1;
                                }
                            }
                            $totlePrice .= ",".$price;
                            $totlePerson .= ",".$person;
                        }
                        if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                        if($totlePrice) $totlePrice = substr($totlePrice, 1);
                        if($totlePerson) $totlePerson = substr($totlePerson, 1);
                        $rows = array(
                            'categories' => $categories,
                            'totlePrice' => $totlePrice,
                            'totlePerson' => $totlePerson,
                        );
                        if($datetime < $now){
                            F('sales_figures_monthly_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $categories = $cacheData['categories'];
                        $totlePrice = $cacheData['totlePrice'];
                        $totlePerson = $cacheData['totlePerson'];
                    }

                    $this->assign('categories', $categories);
                    $this->assign('totle_price', $totlePrice);
                    $this->assign('totle_person', $totlePerson);
                    $this->assign('dt', $datetime);
                    $this->display('sales_figures_monthly');
                    exit;
                    break;
                case 'year': // 每年数据
                    $now = date('Y', time());
                    if($datetime>=$now) {
                        $datetime = date('Y', strtotime($datetime));
                    }
                    $cacheData = F('sales_figures_yearly_'.$datetime);
                    if(!$cacheData){
                        $categories = "";
                        $totlePrice = ""; // 投资总金额
                        $totlePerson = ""; // 投资总人数
                        $price = 0; // 投资金额
                        $person = 0; // 投资人数
                        $time = 0;
                        $list = $rechargeLog->field('amount,add_time')->where("user_id>0 and add_time>='".$datetime."-01-01 00:00:00.000000' and add_time<'".($datetime+1)."-01-01 00:00:00.000000' and status=2")->order('add_time')->select();
                        for($i = 1; $i <= 12; $i++){
                            $price = 0;
                            $person = 0;
                            $categories .= ",'" . $i. "月'";
                            foreach($list as $k => $v){
                                $time = strtotime($v['add_time']);
                                if($i < 12){
                                    if($time >= strtotime($datetime.'-'.($i>9?$i:'0'.$i).'-01 00:00:00.000000') and $time < strtotime($datetime.'-'.(($i+1)>9?($i+1):'0'.($i+1)).'-01 00:00:00.000000')){
                                        $price += $v['amount'];
                                        $person += 1;
                                    }
                                }else{
                                    if($time >= strtotime($datetime.'-'.($i>9?$i:'0'.$i).'-01 00:00:00.000000') and $time < strtotime(($datetime+1).'-01-01 00:00:00.000000')){
                                        $price += $v['amount'];
                                        $person += 1;
                                    }
                                }
                            }
                            $totlePrice .= ",".$price;
                            $totlePerson .= ",".$person;
                        }
                        if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                        if($totlePrice) $totlePrice = substr($totlePrice, 1);
                        if($totlePerson) $totlePerson = substr($totlePerson, 1);
                        $rows = array(
                            'categories' => $categories,
                            'totlePrice' => $totlePrice,
                            'totlePerson' => $totlePerson,
                        );
                        if($datetime < $now){
                            F('sales_figures_yearly_'.$datetime, $rows);
                        }
                    }else{
                        $categories = $cacheData['categories'];
                        $totlePrice = $cacheData['totlePrice'];
                        $totlePerson = $cacheData['totlePerson'];
                    }

                    $this->assign('categories', $categories);
                    $this->assign('totle_price', $totlePrice);
                    $this->assign('totle_person', $totlePerson);
                    $this->assign('dt', $datetime);
                    $this->display('sales_figures_year');
                    exit;
                    break;
                case 'bk': // 爆款数据
                    $bk_id = I('get.id', 0, 'int');
                    $projectObj = M('Project');
                    $userObj = M('User');
                    $bkList = $projectObj->field('id,title')->where(array('type'=>107,'is_delete'=>0))->order('add_time desc')->select();
                    $this->assign('bk_list', $bkList);
                    if($bk_id > 0){
                        $detail = $projectObj->field('id,title,start_time')->where(array('id'=>$bk_id,'is_delete'=>0))->find();
                        if(!$detail){
                            $this->error('项目信息不存在或已被删除');exit;
                        }
                        $this->assign('detail', $detail);
                        $datetime = date('Y-m-d', strtotime($detail['start_time']));
                        $categories = "";
                        $totlePrice = ""; // 投资总金额
                        $totlePerson = ""; // 投资总人数
                        $price = 0; // 投资金额
                        $person = 0; // 投资人数
                        $time = 0;
                        $list = $rechargeLog->field('user_id,amount,add_time')->where("user_id>0 and project_id=".$bk_id." and status=2")->order('add_time')->select();

                        for($i = 0; $i <= 15; $i++){
                            $price = 0;
                            $person = 0;
                            $categories .= ",'10点" . ($i+10). "分'";
                            foreach($list as $k => $v){
                                $time = strtotime($v['add_time']);
                                if($time >= strtotime($datetime.' 10:'.($i+10).':00.000000') and $time < strtotime($datetime.' 10:'.($i+11).':00.000000')){
                                    $price += $v['amount'];
                                    $person += 1;
                                }
                            }
                            $totlePrice .= ",".$price;
                            $totlePerson .= ",".$person;
                        }
                        if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                        if($totlePrice) $totlePrice = substr($totlePrice, 1);
                        if($totlePerson) $totlePerson = substr($totlePerson, 1);

                        $this->assign('categories', $categories);
                        $this->assign('totle_price', $totlePrice);
                        $this->assign('totle_person', $totlePerson);

                        // 设备占比率
                        $user_ids = '';
                        foreach($list as $key => $val){
                            $user_ids .= ','.$val['user_id'];
//                            $sl = 0;
//                            for($i = $key; $i < count($list); $i++){
//                                if($val['user_id'] == $list[$i]['user_id']) $sl += 1;
//                            }
//                            if($sl > 1) echo $val['user_id'].',';
                        }
                        if($user_ids) $user_ids = substr($user_ids, 1);
                        $uList = $userObj->where("id in (".$user_ids.")")->select();
                        $ios = 0; $android = 0; $wap = 0; $pc = 0; $other = 0; $totle = 0;
                        foreach($uList as $key => $val){
                            switch($val['device_type']){
                                case 1:
                                    $ios += 1;
                                    break;
                                case 2:
                                    $android += 1;
                                    break;
                                case 3:
                                    $wap += 1;
                                    break;
                                case 4:
                                    $pc += 1;
                                    break;
                                default:
                                    $other += 1;
                                    break;
                            }
                        }
                        $totle = $ios + $android + $wap + $pc + $other;
                        $deviceData = '[\'ios('.$ios.')\','.number_format($ios/$totle, 1).'],';
                        $deviceData .= '[\'android('.$android.')\','.number_format($android/$totle, 1).'],';
                        $deviceData .= '[\'wap('.$wap.')\','.number_format($wap/$totle, 1).'],';
                        $deviceData .= '[\'pc('.$pc.')\','.number_format($pc/$totle, 1).'],';
                        $deviceData .= '[\'other('.$other.')\','.number_format($other/$totle, 1).']';
                        $this->assign('device_totle', $totle);
                        $this->assign('device_data', $deviceData);
                    }
                    $this->display('sales_figures_bk');
                    exit;
                    break;
                case 'hbfxztl': // 还本付息再次投资率
                    $userDueDetailObj = M('UserDueDetail');
                    $now = date('Y-m', time());
                    $date = get_the_month($datetime);
                    $datetime = date('Y-m', strtotime($datetime));
                    if($now != $datetime){
                        $nowDay = 32;
                    }else{
                        $nowDay = date('d', time());
                    }
                    $cacheData = F('sales_figures_hbfxztl_'.str_replace('-', '_', $datetime));
                    //$cacheData = null;
                    if(!$cacheData){
                        $maxDay = date('d', strtotime($date[1])); // 某个最大号数
                        $categories = "";
                        $percent = ""; // 还本付息再次投资率

                        for($i = 1; $i <= $maxDay; $i++){
                            if($i <= $nowDay){
                                $categories .= ",'" . $i. "日'";
                                //$totlePerson = $userDueDetailObj->query("select user_id from s_user_due_detail where user_id>0 and status=2 and add_time<='".$datetime.'-'.$i." 23:59:59.999000' group by user_id");
                                //$totlePerson2 = $userDueDetailObj->query("select id from s_user_due_detail where user_id in (select user_id from s_user_due_detail where user_id>0 and status=2 group by user_id) and add_time<='".$datetime.'-'.$i." 23:59:59.999000' group by user_id having count(id)>1");
                                $totlePerson = $userDueDetailObj->query("select user_id from s_user_due_detail where user_id>0 and due_time<='".$datetime."-".$i." 23:59:59.999000' group by user_id");
                                $totlePerson2 = $userDueDetailObj->query("select id from s_user_due_detail where user_id in (select user_id from s_user_due_detail where user_id>0 and due_time<='".$datetime."-".$i." 23:59:59.999000' group by user_id) and add_time<='".$datetime.'-'.$i." 23:59:59.999000' group by user_id having count(id)>1");
                                $percent .= ",".number_format((count($totlePerson2)/count($totlePerson)), 6);
                            }else{
                                $categories .= ",'" . $i. "日'";
                                $percent .= ",0.000000";
                            }
                        }
                        if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
                        if($percent) $percent = substr($percent, 1);
                        $rows = array(
                            'categories' => $categories,
                            'percent' => $percent,
                        );
                        if($datetime < $now){
                            F('sales_figures_hbfxztl_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $categories = $cacheData['categories'];
                        $percent = $cacheData['percent'];
                    }

                    $this->assign('categories', $categories);
                    $this->assign('percent', $percent);
                    $this->assign('dt', $datetime);
                    $this->display('sales_figures_hbfxztl');
                    exit;
                    break;
                case 'xsectz': // 购买 新手标二次投资 用户数
                    $projectObj = M('Project');
                    $userDueDetailObj = M('UserDueDetail');

                    // 设备占比率
                    $projectIds = '';
                    $list = $projectObj->field('id')->where(array('new_preferential'=>1))->select();
                    foreach($list as $key => $val){
                        $projectIds .= ','.$val['id'];
                    }
                    if($projectIds) $projectIds = substr($projectIds, 1);
                    $totleCount = count($userDueDetailObj->field('user_id')->where("project_id in (".$projectIds.")")->group('user_id')->select()); // 购买新手总人数
                    $sql = "select count(user_id) as count from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id in (".$projectIds.") group by user_id) group by user_id having count>1";
                    $towCount = count($userDueDetailObj->query($sql));

                    $deviceData = '[\'二次购买\','.number_format($towCount/$totleCount, 4).'],';
                    $deviceData .= '[\'单次购买\','.number_format(($totleCount-$towCount)/$totleCount, 4).'],';
                    $this->assign('device_totle', $totleCount);
                    $this->assign('device_data', $deviceData);

                    $this->display('sales_figures_xsectz');
                    exit;
                    break;
                case 'cumulative': // 累计数据
                    $userObj = M("User");
                    $userDueDetailObj = M('UserDueDetail');

                    $startTime = '2015-04-02'; // 开始时间
                    //$endTime = date('Y-m-d', strtotime('-1 days', time())); // 截止日期
                    $endTime = date('Y-m-d', time()); // 截止日期

                    $dayPriceTotle = 0; // 当日总额
                    $dayPersonTotle = 0; // 当日购买次数
                    $prevPriceTotle = 0; // 上一次总值
                    $prevPersonTotle = 0; // 上一次购买次数
                    $categories = "";
                    $totlePrice = ""; // 投资总金额
                    $totlePerson = ""; // 投资总人数
                    $count = 0;
                    do{
                        $dayPriceTotle = $userDueDetailObj->where("user_id>0 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('due_capital');
                        $dayPersonTotle = $userDueDetailObj->field('id')->where("user_id>0 and add_time<='".$startTime." 23:59:59.999000'")->group('user_id')->select();

                        $prevPriceTotle += $dayPriceTotle;
                        $prevPersonTotle = count($dayPersonTotle);

                        $categories .= ",'".str_replace('2015-', '', $startTime)."'";
                        $totlePrice .= ','.$prevPriceTotle;
                        $totlePerson .= ','.$prevPersonTotle;
                        $startTime = date('Y-m-d', strtotime('+1 days', strtotime($startTime)));
                        $count += 1;
                    }while($startTime != $endTime);

                    if($categories) $categories = substr($categories, 1);
                    if($totlePrice) $totlePrice = substr($totlePrice, 1);
                    if($totlePerson) $totlePerson = substr($totlePerson, 1);
                    $this->assign('categories', $categories);
                    $this->assign('totle_price', $totlePrice);
                    $this->assign('totle_person', $totlePerson);
                    $this->display('sales_figures_cumulative');
                    exit;
                    break;
                case 'cumulative_wallet':
                    $userObj = M("User");
                    $userDueDetailObj = M('UserDueDetail');
                    $userWalletRecordsObj = M("UserWalletRecords");

                    $startTime = '2015-04-02'; // 开始时间
                    //$endTime = date('Y-m-d', strtotime('-1 days', time())); // 截止日期
                    $endTime = date('Y-m-d', time()); // 截止日期

                    $dayPriceTotle = 0; // 当日总额
                    $dayPersonTotle = 0; // 当日购买次数
                    $dayPersonWalletTotle = 0; // 当日购买次数(钱包)
                    $prevPriceTotle = 0; // 上一次总值
                    $prevPersonTotle = 0; // 上一次购买次数
                    $categories = "";
                    $totlePrice = ""; // 投资总金额
                    $totlePerson = ""; // 投资总人数
                    $count = 0;
                    do{
                        // 产品统计数据
                        $dayPriceTotle = $userDueDetailObj->where("user_id>0 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('due_capital');
                        $dayPersonTotle = $userDueDetailObj->field('id')->where("user_id>0 and add_time<='".$startTime." 23:59:59.999000'")->group('user_id')->select();
                        // 钱包统计数据
                        $dayPriceTotle += $userWalletRecordsObj->where("type=1 and pay_status=2 and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$startTime." 23:59:59.999000'")->sum('value');
                        $sql = "select id from s_user_wallet_records where type=1 and pay_status=2 and add_time<='".$startTime." 23:59:59.999000' and user_id not in (select user_id from s_user_due_detail where user_id>0 and add_time<='".$startTime." 23:59:59.999000' group by user_id) group by user_id";
                        $dayPersonWalletTotle = M()->query($sql);

                        $prevPriceTotle += $dayPriceTotle;
                        $prevPersonTotle = count($dayPersonTotle) + count($dayPersonWalletTotle);

                        $categories .= ",'".str_replace('2015-', '', $startTime)."'";
                        $totlePrice .= ','.$prevPriceTotle;
                        $totlePerson .= ','.$prevPersonTotle;
                        $startTime = date('Y-m-d', strtotime('+1 days', strtotime($startTime)));
                        $count += 1;
                    }while($startTime != $endTime);

                    if($categories) $categories = substr($categories, 1);
                    if($totlePrice) $totlePrice = substr($totlePrice, 1);
                    if($totlePerson) $totlePerson = substr($totlePerson, 1);
                    $this->assign('categories', $categories);
                    $this->assign('totle_price', $totlePrice);
                    $this->assign('totle_person', $totlePerson);
                    $this->display('sales_figures_cumulative_wallet');
                    exit;
                    break;
            }
            $this->display();
        }else{
            $target = I('post.target', '', 'strip_tags');
            $datetime = I('post.dt', '', 'strip_tags');
            $id = I('post.id', 0, 'int');
            if($datetime){
                redirect(C('ADMIN_ROOT').'/statistics/sales_figures/target/'.$target.'/dt/'.$datetime.'/id/'.$id);
            }else{
                redirect(C('ADMIN_ROOT').'/statistics/sales_figures/target/'.$target.'/id/'.$id);
            }
        }
    }

    /**
     * 钱包统计
     * 
     * update 2016.07.18 加入现金券记录
     */
    public function wallet_data(){
        if(!IS_POST){
            $chn = I('get.chn', '', 'int');
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', '', 'strip_tags');
            $params = array(
                'chn' => $chn,
                'st' => $start_time,
                'et' => $end_time,
            );
            $this->assign('params', $params);

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
                F('channel_list', $channelList);
            }
            $this->assign('channel_list', $channelList);

            if($start_time && $end_time){
                $now_time = $end_time;
                $yesterday = date("Y-m-d",strtotime("-1 days",strtotime($now_time)));
                $list = array();
                $rows = array();
                $cond = array();

                $userWalletRecordsObj = M("UserWalletRecords");
                if(!$chn){ // 全部渠道
                    $sumTotleRechargePersons = 0; // 充值钱包人数
                    $sumTotleRechargeFromBank = 0; // 流水进(银行卡)
                    $sumTotleRechargeFromProject = 0; // 流水进(还本付息)
                    
                    //2016.07.18
                    $sumTotleRechargeFromCashCoupon = 0; // 流水进(现金券)
                    
                    $sumTotleRechargeToBank = 0; // 流水出(银行卡<提现>)
                    $sumTotleRechargeToProject = 0; // 流水出(购买产品)
                    $sumTotleResidualAmount = 0; // 钱包剩余金额
                    while(true){
                        unset($rows);
                        unset($cond);
                        $rows['datetime'] = $now_time;

                        $rechargePersons = 0; // 充值钱包人数
                        $rechargeFromBank = 0; // 银行卡充值
                        $rechargeFromProject = 0; // 还本付息充值
                            $rechargeFromCachCoupon = 0;//现金券转钱包         
                        $rechargeToBank = 0; // 提现
                        $rechargeToProject = 0; // 购买产品
                        $residualAmount = 0; // 钱包剩余金额

                        $oldRechargePersons = count($userWalletRecordsObj->query("select id from s_user_wallet_records where type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and user_id in (select user_id from s_user_wallet_records where type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' group by user_id) and add_time<'".$now_time." 00:00:00.000000' group by user_id"));
                        $totleRechargePersons = count($userWalletRecordsObj->query("select user_id from s_user_wallet_records where type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' group by user_id"));
                        $rechargePersons = $totleRechargePersons - $oldRechargePersons;
                        //加上 pay_type !=3;
                        $rechargeFromBank = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type != 3 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        $rechargeFromProject = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        $rechargeToBank = $userWalletRecordsObj->where("user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yesterday." 15:00:00.000000' and add_time<='".$now_time." 15:00:00.000000'")->sum('value');
                        $rechargeToProject = $userWalletRecordsObj->where("user_id > 0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        $residualAmount = $userWalletRecordsObj->where("((type=1 and pay_status=2) or type=2) and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        //现金券转钱包                        
                        $rechargeFromCachCoupon = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type = 3 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        
                        
                        $rows['recharge_persons'] = $rechargePersons;
                        $rows['recharge_from_bank'] = $rechargeFromBank;
                        $rows['recharge_from_project'] = $rechargeFromProject;
                        //现金券转钱包
                        $rows['recharge_from_cashCoupon'] = $rechargeFromCachCoupon;
                        
                        $rows['recharge_to_bank'] = $rechargeToBank;
                        $rows['recharge_to_project'] = $rechargeToProject;
                        $rows['residual_amount'] = $residualAmount;
                        array_push($list, $rows);

                        // 计入合计
                        $sumTotleRechargePersons += $rechargePersons;
                        $sumTotleRechargeFromBank += $rechargeFromBank;
                        $sumTotleRechargeFromProject += $rechargeFromProject;
                        $sumTotleRechargeToBank += $rechargeToBank;
                        $sumTotleRechargeToProject += $rechargeToProject;
                        $sumTotleResidualAmount += $residualAmount;
                        //现金券转钱包
                        $sumTotleRechargeFromCashCoupon +=$rechargeFromCachCoupon;

                        if($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                        else break;
                    }
                }else{ // 某个渠道
                    $sumTotleRechargePersons = 0; // 充值钱包人数
                    $sumTotleRechargeFromBank = 0; // 总充值(银行卡)
                    $sumTotleRechargeFromProject = 0; // 总充值(还本付息)
                    $sumTotleRechargeToBank = 0; // 流水出(银行卡<提现>)
                    $sumTotleRechargeToProject = 0; // 流水出(购买产品)
                    $sumTotleResidualAmount = 0; // 钱包剩余金额
                    
                    //2016.07.18
                    $sumTotleRechargeFromCashCoupon = 0; // 流水进(现金券)
                    
                    while(true){
                        unset($rows);
                        unset($cond);
                        $rows['datetime'] = $now_time;

                        $rechargePersons = 0; // 充值钱包人数
                        $rechargeFromBank = 0; // 银行卡充值
                        $rechargeFromProject = 0; // 还本付息充值
                        $rechargeToBank = 0; // 提现
                        $rechargeToProject = 0; // 购买产品
                        $residualAmount = 0; // 钱包剩余金额
                        
                        $rechargeFromCachCoupon = 0;//现金券转钱包

                        $oldRechargePersons = count($userWalletRecordsObj->query("select id from s_user_wallet_records where ((type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0) or (type=1 and user_bank_id=0 and user_due_detail_id>0)) and user_id in (select user_id from s_user_wallet_records where user_id in (select id from s_user where channel_id=".$chn.") and ((type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0) or (type=1 and user_bank_id=0 and user_due_detail_id>0)) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' group by user_id) and add_time<'".$now_time." 00:00:00.000000'"));
                        $totleRechargePersons = count($userWalletRecordsObj->query("select user_id from s_user_wallet_records where user_id in (select id from s_user where channel_id=".$chn.") and ((type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0) or (type=1 and user_bank_id=0 and user_due_detail_id>0)) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000' group by user_id"));
                        $rechargePersons = $totleRechargePersons - $oldRechargePersons;
                        // type_pay != 3;
                        $rechargeFromBank = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and ((type=1 and pay_status=2 and pay_type !=3 and user_bank_id>0 and user_due_detail_id=0) or (type=1 and user_bank_id=0 and user_due_detail_id>0)) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        
                        $rechargeFromProject = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        $rechargeToBank = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and user_id > 0 and type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$yesterday." 15:00:00.000000' and add_time<='".$now_time." 15:00:00.000000'")->sum('value');
                        $rechargeToProject = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and user_id >0 and type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        $residualAmount = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and ((type=1 and user_bank_id>0 and user_due_detail_id=0 and pay_status=2) or (type=1 and user_bank_id=0 and user_due_detail_id>0) or type=2) and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                    
                        $rechargeFromCachCoupon = $userWalletRecordsObj->where("user_id in (select id from s_user where channel_id=".$chn.") and ((type=1 and pay_status=2 and pay_type = 3 and user_bank_id>0 and user_due_detail_id=0) or (type=1 and user_bank_id=0 and user_due_detail_id>0)) and add_time>='".$now_time." 00:00:00.000000' and add_time<='".$now_time." 23:59:59.999000'")->sum('value');
                        
                        
                        $rows['recharge_persons'] = $rechargePersons;
                        $rows['recharge_fromBank'] = $rechargeFromBank;
                        $rows['recharge_from_project'] = $rechargeFromProject;
                        $rows['recharge_to_bank'] = $rechargeToBank;
                        $rows['recharge_to_project'] = $rechargeToProject;
                        $rows['residual_amount'] = $residualAmount;
                        
                        //现金券转钱包
                        $rows['recharge_from_cashCoupon'] = $rechargeFromCachCoupon;
                        
                        array_push($list, $rows);

                        // 计入合计
                        $sumTotleRechargePersons += $rechargePersons;
                        $sumTotleRechargeFromBank += $rechargeFromBank;
                        $sumTotleRechargeFromProject += $rechargeFromProject;
                        $sumTotleRechargeToBank += $rechargeToBank;
                        $sumTotleRechargeToProject += $rechargeToProject;
                        $sumTotleResidualAmount += $residualAmount;
                        
                        //现金券转钱包
                        $sumTotleRechargeFromCashCoupon +=$rechargeFromCachCoupon;

                        if($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                        else break;
                    }
                }
                $sumParams = array(
                    'sumTotleRechargePersons' => $sumTotleRechargePersons,
                    'sumTotleRechargeFromBank' => $sumTotleRechargeFromBank,
                    'sumTotleRechargeFromProject' => $sumTotleRechargeFromProject,
                    'sumTotleRechargeToBank' => $sumTotleRechargeToBank,
                    'sumTotleRechargeToProject' => $sumTotleRechargeToProject,
                    'sumTotleResidualAmount' => $sumTotleResidualAmount,
                    'sumTotleRechargeFromCashCoupon' => $sumTotleRechargeFromCashCoupon,
                );
                $this->assign('sumParams', $sumParams);
                $this->assign('list', $list);
            }
            $this->display();
        }else{
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $chn = I('post.chn', '', 'int');
            $query = '';
            if($chn) $query .= '/chn/'.$chn;
            if($start_time) $query .= '/st/'.$start_time;
            if($end_time) $query .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT').'/statistics/wallet_data'.$query);
        }
    }

    /**
     * 钱包统计条目明细
     */
    public function wallet_data_detail(){
        if(!IS_POST){
            $target = I('get.target', 0, 'int'); // 0:人数/1:流水进(银行卡)/2:流水进(还本付息)/3:流水出(提现)/4:流水出(购买产品)
            $datetime = I('get.dt', '', 'strip_tags');
            $chn = I('get.chn', 0, 'int'); // 渠道

            $userWalletRecordsObj = M("UserWalletRecords");
            $constantObj = M("Constant");
            $userObj = M("User");

            if($chn) $chnName = $constantObj->where(array('id'=>$chn))->getField('cons_value');
            else $chnName = '全部渠道';

            $params = array(
                'target' => $target,
                'datetime' => $datetime,
                'chnName' => $chnName,
            );
            $this->assign('params', $params);

            switch($target){
                case 0:
                    $count = 10;
                    if(!$chn){ // 所有渠道
                        $conditions = "type=1 and pay_status=2 and add_time>='".$datetime." 00:00:00.000000' and add_time<='".$datetime." 23:59:59.999000'";
                        $list = $userWalletRecordsObj->where($conditions)->group('user_id')->select();
                        foreach($list as $key => $val){
                            $list[$key]['uinfo'] = $userObj->where(array('id'=>$val['user_id']))->find();
                        }
                    }else{
                        $conditions = "";
                        $counts = $userWalletRecordsObj->where($conditions)->count();
                        $Page = new \Think\Page($counts, $count);
                        $show = $Page->show();
                        $list = $userWalletRecordsObj->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                    }
                    $this->assign('show', $show);
                    $this->assign('list', $list);
                    $this->display('wallet_data_detail0');
                    break;
                case 1:
                    $count = 10;

                    $this->display('wallet_data_detail1');
                    break;
                case 2:
                    $count = 10;

                    $this->display('wallet_data_detail2');
                    break;
                case 3:
                    $count = 10;

                    $this->display('wallet_data_detail3');
                    break;
                case 4:
                    $count = 10;

                    $this->display('wallet_data_detail4');
                    break;
            }
        }else{

        }
    }

    /**
     * 历史下单记录
     */
    public function history_order(){
        if(!IS_POST){
            $user_id = I('get.uid', 0, 'int');
            $page = I('get.p', 1, 'int');
            $count = 10;

            $rechargeLogObj = M("RechargeLog");

            $counts = $rechargeLogObj->where(array('user_id'=>$user_id))->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $rechargeLogObj->where(array('user_id'=>$user_id))->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

    /**
     * 提现记录
     */
    public function withdrawals_order(){
        if(!IS_POST){
            $user_id = I('get.uid', 0, 'int');
            $page = I('get.p', 1, 'int');
            $count = 10;
            $takeout_wallet_total = 0;
            $userWalletRecords = M('userWalletRecords');

            $cond[] = "user_id=".$user_id;
            $cond[] = "type=2";
            $conditions = implode(' and ', $cond);

            $counts = $userWalletRecords->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userWalletRecords->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $k=>$v){
                $takeout_wallet_total += $v['value'];
            }
            $this->assign('takeout_wallet_total', $takeout_wallet_total);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

    /**
     * 充值记录
     */
    public function recharge_order(){
        if(!IS_POST){
            $user_id = I('get.uid', 0, 'int');
            $page = I('get.p', 1, 'int');
            $count = 10;
            $takein_wallet_total = 0;
            $userWalletRecords = M('userWalletRecords');

            $cond[] = "user_id=".$user_id;
            $cond[] = "type=1";
            $conditions = implode(' and ', $cond);

            $counts = $userWalletRecords->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $userWalletRecords->where($conditions)->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach($list as $k=>$v){
                $takein_wallet_total +=$v['value'];
            }
            $this->assign("takein_wallet_total",$takein_wallet_total);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{

        }
    }

    /**
     * 收益统计
     */
    public function profit_statistics(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $start_time = I('get.st', '');
            $end_time = I('get.et', '');
            $params = array(
                'page' => $page,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $this->assign('params', $params);
            $count = 20; // 每页显示条数

            $statisticsDailyProfitObj = M("StatisticsDailyProfit");
            if($start_time && $end_time){
                $cond[] = "dt>='".$start_time."'";
                $cond[] = "dt<='".$end_time."'";
            }
            $conditions = implode(' and ', $cond);
            $counts = $statisticsDailyProfitObj->where($conditions)->count();
            $Page = new \Think\Page($counts, $count);
            $show = $Page->show();
            $list = $statisticsDailyProfitObj->where($conditions)->order('dt desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $sumRows = array(
                'sum_p_income' => 0,
                'sum_p_expenses' => 0,
                'sum_p_profit' => 0,
                'sum_w_income' => 0,
                'sum_w_offline_income' => 0,
                'sum_w_expenses' => 0,
                'sum_w_profit' => 0,
            );
            foreach($list as $key => $val){
                $sumRows['sum_p_income'] += $val['p_income'];
                $sumRows['sum_p_expenses'] += $val['p_expenses'];
                $sumRows['sum_p_profit'] += $val['p_income'] - $val['p_expenses'];
                $sumRows['sum_w_income'] += $val['w_income'];
                $sumRows['sum_w_offline_income'] += $val['w_offline_income'];
                $sumRows['sum_w_expenses'] += $val['w_expenses'];
                $sumRows['sum_w_profit'] += $val['w_income'] + $val['w_offline_income'] - $val['w_expenses'];
            }

            // 最后一条记录数据
            $lastestDt = $statisticsDailyProfitObj->order('dt desc')->getField('dt');
            $nextDt = date('Y-m-d', strtotime('+1 days', strtotime($lastestDt)));
            if($lastestDt == $list[0]['dt'] && $nextDt != date('Y-m-d', time())){ // 第一页数据(并且不是今天的日期)，显示未抓取的天数条目
                $this->assign('new_dt', $nextDt);
                $this->assign('showNewItem', 1);
            }

            $this->assign('sum_rows', $sumRows);
            $this->assign('show', $show);
            $this->assign('list', $list);
            $this->display();
        }else{
            $act = I('post.act', '', 'strip_tags');
            if($act == 'pagination'){
                $start_time = trim(I('post.start_time', '-'));
                $end_time = trim(I('post.end_time', '-'));
                $quest = '';
                if($start_time) $quest .= '/st/'.$start_time;
                if($end_time) $quest .= '/et/'.$end_time;
                redirect(C('ADMIN_ROOT') . '/statistics/profit_statistics'.$quest);
            }else if($act == 'offline_wallet'){
                $id = I('post.id', 0, 'int');
                $money = I('post.money', 0, 'float');
                $statisticsDailyProfitObj = M("StatisticsDailyProfit");
                if(!$statisticsDailyProfitObj->where(array('id'=>$id))->save(array('w_offline_income'=>$money))) $this->ajaxReturn(array('status'=>0,'info'=>'更新失败,请重试'));
                $this->ajaxReturn(array('status'=>1));
            }else if($act == 'update'){

                $dt = I('post.dt', '', 'strip_tags');
                if(!isDateFormat($dt)) $this->ajaxReturn(array('status'=>0,'info'=>'日期格式不正确'));
                $nowDt = date('Y-m-d', time());
                if($dt >= $nowDt) $this->ajaxReturn(array('status'=>1,'info'=>'只能抓取历史数据'));
                $rows['dt'] = $dt;

                $projectObj = M("Project");
                $userDueDetailObj = M("UserDueDetail");
                $userWalletRecordsObj = M("UserWalletRecords");
                $statisticsDailyProfitObj = M("StatisticsDailyProfit");
                $userWalletInterestObj = M("UserWalletInterest");
                $contractObj = M("Contract");
                $projectContractObj = M("ContractProject");
                //理财收支处理
                $projectInterest = array();
                $result = $userDueDetailObj->field('project_id')->where("user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->group("project_id")->select();
                foreach($result as $key => $val){
                    $interest = $projectObj->field('id,contract_interest,user_interest,end_time,start_time,title')->where(array('id'=>$val['project_id'],'term_type'=>1))->find();
                    //手续费
                    $project_contract = $projectContractObj->field("contract_id")->where(array('project_id'=>$val['project_id'],'project_name'=>$interest['title']))->find();
                    if($project_contract){
                        $contract_info = $contractObj->field("fee,interest")->where(array('id'=>$project_contract['contract_id']))->find();
                    }
                    $user_start_time = $userDueDetailObj->field('add_time')->where('project_id='.$val['project_id'])->order("add_time asc")->find();
                    if($interest && $contract_info) $projectInterest[$val['project_id']] = array(
                        'id'=>$interest['id'],
                        'contract_interest'=>$contract_info['interest'],
                        'user_interest'=>$interest['user_interest'],
                        'fee'=>$contract_info['fee'],
                        'start_time'=>$user_start_time['add_time'],
                        'end_time'=>$interest['end_time']

                    );
                }
                $incomeSum = 0; $expensesSum = 0;
                foreach($projectInterest as $key => $val){
                    $dueList = $userDueDetailObj->field('due_capital,from_wallet,to_wallet')->where("user_id>0 and project_id=".$val['id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->select();

                    $days = count_days(date('Y-m-d', strtotime($val['end_time'])).' 08:00:00', date('Y-m-d', strtotime($dt)).' 08:00:00');
                    $sum = 0; $payback_count = 0;
                    foreach($dueList as $k => $v){
                        $sum += $v['due_capital'];
                        if($v['from_wallet'] == 0 && $v['to_wallet'] == 0) $payback_count ++;
                    }
                    $incomeSum += $sum*$days*$val['contract_interest']/100/365+ $sum*$val['fee']/100;
                    $expensesSum += $sum*$days*$val['user_interest']/100/365 + $sum*0.002;

                }

                $rows['p_income'] = round($incomeSum, 2);
                $rows['p_expenses'] = round($expensesSum, 2);
				$rows['p_profit'] = $rows['p_income']-$rows['p_expenses'];

                // 钱包收支处理
                //1.0钱包支出
                $w_expenses_daily = $this->wallet_expenses($dt);
                //1.1钱包收入
                $w_income_daily  = $this->wallet_income($dt);

                $rows['w_offline_income'] = 0; // 每日线下投资收入
                $rows['w_income'] = round($w_income_daily, 2);
                $rows['w_expenses'] = round($w_expenses_daily, 2);
                $rows['w_profit'] = round($w_income_daily-$w_expenses_daily,2);

                $existId = $statisticsDailyProfitObj->where(array('dt'=>$dt))->getField('id');
                if(!$existId){
                    if(!$statisticsDailyProfitObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加数据失败，请重试'));
                }else{
                    if(!$statisticsDailyProfitObj->where(array('id'=>$existId))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'更新数据失败或无更新数据，请重试'));
                }
                $this->ajaxReturn(array('status'=>1));
            }
        }
    }
    /**
     * 钱包支出处理-昨天(T-1)
     * 钱包支出计算公式:昨日钱包存量 * (今日线上利率/100/365) + 每日钱包充值金额*0.2％ + 每日钱包提现手续费
     */
    public function wallet_expenses($dt){
        $yestoday = date('Y-m-d', strtotime('-1 days', strtotime($dt)));
        $userWalletRecordsObj = M("UserWalletRecords");//钱包转入/转出记录表
        $userWalletAnnualizedRateObj = M("UserWalletAnnualizedRate");//钱包年化利率预设表
        if(!$dt || !$yestoday){
            exit;
        }
        //昨日钱包存量
        $residualAmount = $userWalletRecordsObj->where("((type=1 and pay_status=2) or type=2) and add_time<='".$yestoday." 23:59:59.999000'")->sum('value');
        //银行卡充值钱包金额
        $rechargeFromBank = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
        //充值钱包金额手续费2/1000
        if($rechargeFromBank){
            $rechargeFromBankFee = $rechargeFromBank*(2/1000);
        }
        //当天钱包的年化利率
        $userWalletAnnualizedRateArr = $userWalletAnnualizedRateObj->where("add_time='".$dt."'")->find();
        $yesterdayUserWalletAnnualizedRate = $userWalletAnnualizedRateArr['rate'];
        //当天钱包需要支付的利息
        $rechargeInterestAmount = $residualAmount * ($yesterdayUserWalletAnnualizedRate/100/365);
        //获取当天钱包提现记录
        $tx_sql = "SELECT m.`add_time`,m.`value`,m.`pay_type`,k.`bank_code`  FROM `s_user_wallet_records` AS m,`s_user_bank` AS k WHERE m.`type` = 2 AND m.`user_bank_id`>0 AND m.`user_due_detail_id` = 0 AND m.`add_time`>='".$yestoday." 15:00:00.000000' AND m.`add_time`<='".$dt." 15:00:00.000000' AND m.`user_bank_id` = k.`id`";
        $rechargeToBankArr = M()->query($tx_sql);
        /**
         * 当天钱包提现手续费
         * 连连每笔1块,盛付通1W以下1块(包括),1W以上2块),3家银行用连连:邮政(01000000)，华夏(03040000)，兴业(03090000)
         * */
        $tx_fee=0;
        foreach($rechargeToBankArr as $k=>$v){
            if($v['add_time']<'2015-11-03 23:59:59.999000'){
                if(in_array($v['bank_code'], array('01000000','03040000','03090000'))){
                    $tx_fee += 1;
                }else{
                    if(abs($v['value']) <= 10000){
                        $tx_fee += 1;
                    }else{
                        $tx_fee += 2;
                    }
                }
            }else{//融宝支付
                $boundary_fee = 50000;
                $tx_value  = abs($v['value']);
                if($tx_value <= $boundary_fee){
                    $tx_fee+=1;
                }else{
                    $tx_fee+=round($tx_value/$boundary_fee);
                }
            }
        }
        //每日钱包支出
        $wallet_expenses_amount = $rechargeInterestAmount+$rechargeFromBankFee+$tx_fee;
        return $wallet_expenses_amount;
    }

    /**
     * 钱包收入处理-昨天(T-1)
     * 钱包收入计算公式:每日线下收入+每日钱包投资理财*02/100
     */
    public function wallet_income($dt){
        $UserWalletRecordsObj = M("UserWalletRecords");//钱包转入/转出记录表
        $WalletIncomeRecordsObj = M("WalletIncomeRecords");//钱包线下收入记录表

        //昨日时间处理
        $yesterday = $dt;
        if(!$yesterday){
            exit;
        }
        //钱包投资理财(每日钱包转理财*0.2%)
        $walletToDueAmountTmp = $UserWalletRecordsObj->where("type=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
        $walletToDueAmount = abs($walletToDueAmountTmp)*(2/1000);
        //钱包线下收入:one表示15%,two表示18%,three表示16%,four表示16.5%,five表示14%
        $walletOfflineIncomeAmount = 0;
        //进入
        $wallet_enter_amount_one   = 0;
        $wallet_enter_amount_two   = 0;
        $wallet_enter_amount_three = 0;
        $wallet_enter_amount_four  = 0;
        $wallet_enter_amount_five  = 0;
        //出去
        $wallet_out_amount_one     = 0;
        $wallet_out_amount_two     = 0;
        $wallet_out_amount_three   = 0;
        $wallet_out_amount_four    = 0;
        $wallet_out_amount_five    = 0;
        //总收入
        $total_wallet_income_amount = 0;
        //具体统计

        //one-15%
        $one_where = array();
        $one_where[] = "dt<='".$yesterday."'";
        $one_where[] = "rate_type=1";
        if($one_where){
            $one_cond = implode(" and ",$one_where);
        }
        $wallet_enter_amount_one = $WalletIncomeRecordsObj->where($one_cond)->sum('income_amount');
        $wallet_out_amount_one   = $WalletIncomeRecordsObj->where($one_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_one-$wallet_out_amount_one)*0.15/365;
        //two-18%
        $two_where = array();
        $two_where[] = "dt<='".$yesterday."'";
        $two_where[] = "rate_type=2";
        if($two_where){
            $two_cond = implode(" and ",$two_where);
        }
        $wallet_enter_amount_two = $WalletIncomeRecordsObj->where($two_cond)->sum('income_amount');
        $wallet_out_amount_two   = $WalletIncomeRecordsObj->where($two_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_two-$wallet_out_amount_two)*0.18/365;
        //three-16%
        $three_where = array();
        $three_where[] = "dt<='".$yesterday."'";
        $three_where[] = "rate_type=3";
        if($three_where){
            $three_cond = implode(" and ",$three_where);
        }
        $wallet_enter_amount_three = $WalletIncomeRecordsObj->where($three_cond)->sum('income_amount');
        $wallet_out_amount_three   = $WalletIncomeRecordsObj->where($three_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_three-$wallet_out_amount_three)*0.16/365;
        //four-16.5%
        $four_where = array();
        $four_where[] = "dt<='".$yesterday."'";
        $four_where[] = "rate_type=4";
        if($four_where){
            $four_cond = implode(" and ",$four_where);
        }
        $wallet_enter_amount_four = $WalletIncomeRecordsObj->where($four_cond)->sum('income_amount');
        $wallet_out_amount_four   = $WalletIncomeRecordsObj->where($four_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_four-$wallet_out_amount_four)*0.165/365;
        //five-14%
        $five_where = array();
        $five_where[] = "dt<='".$yesterday."'";
        $five_where[] = "rate_type=5";
        if($five_where){
            $five_cond = implode(" and ",$five_where);
        }
        $wallet_enter_amount_five = $WalletIncomeRecordsObj->where($five_cond)->sum('income_amount');
        $wallet_out_amount_five   = $WalletIncomeRecordsObj->where($five_cond)->sum('expense_amount');
        $total_wallet_income_amount+=($wallet_enter_amount_five-$wallet_out_amount_five)*0.18/365;
        //最终钱包收入
        $result_wallet_income = $walletToDueAmount+$total_wallet_income_amount;
        return $result_wallet_income;
    }
	/**
     * 每日统计数据记录
     */
    public function statistics_daily_data(){
        if(!IS_POST){
            $date_fourth_time = date("Y-m-d",strtotime("-6 days"));
            $now_date_time  = date("Y-m-d",time());
            $statistics_daily_db = M('StatisticsDaily');//每日统计表
            $daily_statistics_list = $statistics_daily_db->where("dt>='".$date_fourth_time."' and dt<='".$now_date_time."'")->order('dt desc')->select();
			$need_deal_date_arr =  $statistics_daily_db->field('dt')->order('dt desc')->find();
			
			if($need_deal_date_arr['dt']){ 
				$need_deal_date = date("Y-m-d",strtotime($need_deal_date_arr['dt'])+24*3600);
			}else{ 
				$need_deal_date = date("Y-m-d",time());
			}
            $this->assign('daily_statistics_list',$daily_statistics_list);
            $this->assign("datetime",$need_deal_date);
            $this->display();
        }else{
            $dt = I('post.date_time', '', 'strip_tags');
            if(!$dt) exit(json_encode(array("status"=>3,'msg'=>"没有选择日期")));
            $statisticsDailyObj = M("StatisticsDaily");
            $userDueDetailObj = M("UserDueDetail");
            $userWalletRecordsObj = M("UserWalletRecords");
            $activationDeviceObj = M("ActivationDevice");
            $projectObj = M("Project");
            $rechargeLogObj = M("RechargeLog");
            $contractProjectObj = M("ContractProject");
            $contractObj = M("Contract");
            $existID = $statisticsDailyObj->where(array('dt'=>$dt))->getField('id');

            //变量定义
            $allUids = '';
            $productUids = '';
            $walletUids = '';
            $firstPayUids = '';
            $repayUids = '';
            $firstProductPayUids = '';
            $firstWalletPayUids = '';
            $allUidsArr = array();
            $productUidsArr = array();
            $walletUidsArr = array();
            $firstPayUidsArr = array();
            $repayUidsArr = array();
            $firstProductPayUidsArr = array();
            $firstWalletPayUidsArr = array();

            $rows['dt'] = $dt;
            $payCount = 0; // 每日购买理财(包括钱包)用户总数

            // 购买产品用户UID
            $sql = "select user_id from s_user_due_detail where user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
            $uidsResult = M()->query($sql);
            foreach($uidsResult as $key => $val){
                $productUids .= ','.$val['user_id'];
            }
            if($productUids) {
                $productUids = substr($productUids, 1);
                $productUidsArr = explode(',', $productUids);
            }

            // 购买钱包用户UID
            $sql = "select user_id from s_user_wallet_records where type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' group by user_id";
            $uidsResult = M()->query($sql);
            foreach ($uidsResult as $key => $val){
                $walletUids .= ','.$val['user_id'];
            }
            if($walletUids) {
                $walletUids = substr($walletUids, 1);
                $walletUidsArr = explode(',', $walletUids);
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
                $payCount = count($allUidsArr);
            }

            $rows['pay_count'] = $payCount;//每日投资理财(包括钱包)用户总数

            // 首投用户UID
            if($allUids){
                $sql = "select * from (select user_id,1 as fr,add_time from s_user_due_detail where user_id in (".$allUids.") and add_time<'".$dt." 00:00:00.000000' ";
                $sql.= "union all ";
                $sql.= "select user_id,2 as fr,add_time from s_user_wallet_records where user_id in (".$allUids.") and add_time<'".$dt." 00:00:00.000000') as t group by user_id";
                $uidsResult = M()->query($sql);
                foreach ($uidsResult as $key => $val){
                    $repayUids .= ','.$val['user_id'];
                }
                if($repayUids) {
                    $repayUids = substr($repayUids, 1);
                    $repayUidsArr = explode(',', $repayUids);
                }
                $firstPayUidsArr = array_diff($allUidsArr, $repayUidsArr);
                $firstPayUids = implode(',', $firstPayUidsArr);
                $sql = "select user_id from s_user_due_detail where add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' and user_id in (".$firstPayUids.") group by user_id";
                $uidsResult = M()->query($sql);
                foreach($uidsResult as $key => $val){
                    $firstProductPayUids .= ','.$val['user_id'];
                }
                if($firstProductPayUids) {
                    $firstProductPayUids = substr($firstProductPayUids, 1);
                    $firstProductPayUidsArr = explode(',', $firstProductPayUids);
                    $firstWalletPayUidsArr = array_diff($firstPayUidsArr, $firstProductPayUidsArr);
                    $firstWalletPayUids = implode(',', $firstWalletPayUidsArr);
                }
            }

            // 每日银行卡转理财
            $rows['b2p_money'] = $userDueDetailObj->where("user_id>0 and from_wallet=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
            // 每日银行卡转钱包
            $rows['b2w_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            // 每日钱包转理财
            $rows['w2p_money'] = $userDueDetailObj->where("user_id>0 and from_wallet=1 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');;
            // 每日理财转钱包
            $rows['p2w_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and user_bank_id=0 and user_due_detail_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');;
            // 每日理财还款（转到银行卡）
            // 稳一稳到期还款产品
            $sql = "select id from s_project where end_time>='".$dt." 00:00:00.000000' and end_time<='".$dt." 23:59:59.999000' and term_type=1";
            $tmpList = M()->query($sql);
            $rows['p2b_money'] = 0;
            $rows['p2b_count'] = 0;
            foreach($tmpList as $key => $val){
                //$rows['p2b_money'] += round($userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_amount'), 2);
                $rows['p2b_money'] += round($userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->sum('due_capital'), 2); // 只算本金
                $rows['p2b_count'] += $userDueDetailObj->where("project_id=".$val['id']." and user_id>0")->count();
            }

            // 每日钱包提现
            /**
             * 钱包提现有时间段之分,昨天15:00之后至今天15:00之前的提现订单于今天打款,明天确认份额, 今天15:00之后至第二天15:00之前的提现订单于第二天打款, 第三天确认份额, 以此类推(仅提现有时间段之分,其他确认份额都按天算)
             */
            $before_three = date("Y-m-d",strtotime($dt)-(24*3600))." 15:00:00.000000";
            $after_three = $dt." 15:00:00.000000";
            $rows['w2b_money'] = abs($userWalletRecordsObj->where("type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$before_three."' and add_time<='".$after_three."'")->sum('value'));
            // 每日理财转银行卡订单数
            $rows['w2b_count'] = $userWalletRecordsObj->where("type=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->count();
            // 每日每个产品销售信息json字符串
            /********************************** 每日产品统计开始 ****************************************/
            $list = $userDueDetailObj->field('project_id,sum(due_capital) totlecapital')->where("add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->group('project_id')->order('project_id')->select();
            foreach($list as $key => $val){
                $list[$key]['project'] = $projectObj->field('id,title,amount,user_interest,financing,start_time,end_time,remark')->where(array('id'=>$val['project_id']))->find();
                $list[$key]['project']['days'] = count_days($list[$key]['project']['end_time'], $list[$key]['project']['start_time']);
                $more_money = $userDueDetailObj->where("project_id=".$val['project_id']." and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital') - $list[$key]['project']['amount'];
                if($more_money < 0) $more_money = 0;
                $list[$key]['money_more'] = $more_money;
                $ghost_money = $userDueDetailObj->where("user_id=0 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
                $list[$key]['ghost_money'] = $ghost_money;
                $yibao_money = $rechargeLogObj->where("type=2 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('amount');
                $list[$key]['yibao_money'] = $yibao_money;
                $wallet_money = $userDueDetailObj->where("from_wallet=1 and project_id=".$val['project_id']." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
                //$wallet_money = $rechargeLogObj->where("type=3 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('amount');
                $list[$key]['wallet_money'] = $wallet_money;
                // 计算还款手续费(连连每笔1块,盛付通1W以下1块(包括),1W以上2块)<3家银行用连连:邮政(01000000)，华夏(03040000)，兴业(03090000)>
                //$cardList = $userDueDetailObj->field('due_capital,card_no')->where("add_time>='".$start_time."' and add_time<='".$end_time."'")->select();
                $sql = "select a.due_capital,a.card_no,b.bank_code from s_user_due_detail a left join s_user_bank b on b.bank_card_no=a.card_no and b.has_pay_success=2 where a.user_id>0 and a.project_id=".$val['project_id']." and a.add_time>='".$dt." 00:00:00.000000' and a.add_time<='".$dt." 23:59:59.999000'";
                $payFeeList = M()->query($sql);
                $fee = 0;
                $llCount = 0;
                $sftCount = 0;
                foreach($payFeeList as $k => $v){
                    if(in_array($v['bank_code'], array('01000000','03040000','03090000'))){
                        $llCount += 1;
                        $fee += 1;
                    }else{
                        $sftCount += 1;
                        if($v['due_capital'] <= 10000){
                            $fee += 1;
                        }else{
                            $fee += 2;
                        }
                    }
                }
                $list[$key]['fee_info'] = array(
                    'fee' => $fee,
                    'll_count' => $llCount,
                    'stf_count' => $sftCount,
                );
                // 获取产品对应合同相关数据
                $contractId = $contractProjectObj->where(array('project_name'=>$list[$key]['project']['title']))->getField('contract_id');
                if($contractId){
                    $list[$key]['contract_info'] = $contractObj->where(array('id'=>$contractId))->find();
                }
            }
            $walletArr = array(
                'project' => array('id'=>0,'title'=>'票票喵钱包','financing'=>'王伟军'),
                'money_more' => 0,
                'ghost_money' => 0,
                'wallet_money' => 0,
            );
            $walletArr['totlecapital'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            if($walletArr['totlecapital'] > 0){
                $walletArr['yibao_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
                array_unshift($list, $walletArr);
            }
            $rows['json_product_daily'] = json_encode($list);
            /********************************** 每日产品统计结束 ****************************************/

            // 每日理财产品投资用户总数
            $rows['pay_p_count'] = count($productUidsArr);
            // 每日钱包投资用户总数
            $rows['pay_w_count'] = count($walletUidsArr);
            // 每日投资用户UID字符串(每个UID之间用','间隔)
            $rows['str_pay_uid_daily'] = $allUids;
            $rows['pay_uid_daily_num'] = count(explode(',',$allUids));
            // 每日首投用户UID字符串(每个UID之间用','间隔)
            $rows['str_first_pay_uid_daily'] = $firstPayUids;
            $rows['first_pay_uid_daily_num'] = count(explode(',',$firstPayUids));
            // 每日复投用户UID字符串(每个UID之间用','间隔)
            $rows['str_repay_uid_daily'] = $repayUids;
            $rows['repay_uid_daily_num'] = count(explode(',',$repayUids));

            // 每日首投理财用户数
            $rows['first_pay_p_count'] = count($firstProductPayUidsArr);
            // 每日首投钱包用户数
            $rows['first_pay_w_count'] = count($firstWalletPayUidsArr);
            // 每日二投理财用户数
            $rows['second_pay_p_count'] = 0;
            // 每日二投钱包用户数
            $rows['second_pay_w_count'] = 0;
            // 每日三投理财用户数
            $rows['third_pay_p_count'] = 0;
            // 每日三投钱包用户数
            $rows['third_pay_w_count'] = 0;
            // 每日四投理财用户数
            $rows['fourth_pay_p_count'] = 0;
            // 每日四投钱包用户数
            $rows['fourth_pay_w_count'] = 0;
            // 每日五投理财用户数
            $rows['fifth_pay_p_count'] = 0;
            // 每日五投钱包用户数
            $rows['fifth_pay_w_count'] = 0;
            // 每日复投用户数
            $rows['repay_count'] = count($repayUidsArr);
            // 每日复投金额
            if($repayUids) {
                $repay_product_money = $userDueDetailObj->where("user_id in (".$repayUids.") and from_wallet=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
                $repay_wallet_money = $userWalletRecordsObj->where("user_id in (".$repayUids.") and type=1 and pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
                $rows['repay_money'] = $repay_product_money + $repay_wallet_money;
            }
            // 当日激活当日投理财产品用户数
            if($firstProductPayUids) $rows['activation_pay_p_count'] = $activationDeviceObj->where("device_serial_id in (select device_serial_id from s_user where id in (".$firstProductPayUids.")) and active_time>='".$dt." 00:00:00.000000' and active_time<='".$dt." 23:59:59.999000'")->count();
            // 当日激活当日投钱包用户数
            if($firstWalletPayUids) $rows['activation_pay_w_count'] = $activationDeviceObj->where("device_serial_id in (select device_serial_id from s_user where id in (".$firstWalletPayUids.")) and active_time>='".$dt." 00:00:00.000000' and active_time<='".$dt." 23:59:59.999000'")->count();
            // 每日理财产品投资额
            $rows['pay_p_money'] = $userDueDetailObj->where("user_id>0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('due_capital');
            // 每日钱包投资额
            $rows['pay_w_money'] = $userWalletRecordsObj->where("type=1 and  pay_status=2 and user_bank_id>0 and user_due_detail_id=0 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000'")->sum('value');
            // 每日首投理财金额
            $rows['first_pay_p_money'] = 0;
            // 每日首投钱包金额
            $rows['first_pay_w_money'] = 0;
            // 每日二投理财金额
            $rows['second_pay_p_money'] = 0;
            // 每日二投钱包金额
            $rows['second_pay_w_money'] = 0;
            // 每日三投理财金额
            $rows['third_pay_p_money'] = 0;
            // 每日三投钱包金额
            $rows['third_pay_w_money'] = 0;
            // 每日四投理财金额
            $rows['fourth_pay_p_money'] = 0;
            // 每日四投钱包金额
            $rows['fourth_pay_w_money'] = 0;
            // 每日五投理财金额
            $rows['fifth_pay_p_money'] = 0;
            // 每日五投钱包金额
            $rows['fifth_pay_w_money'] = 0;

            // 计算用户1~5投数量和金额
            foreach($allUidsArr as $key => $val){
                $sql = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time<'".$dt." 00:00:00.000000' UNION ALL ";
                $sql.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time<'".$dt." 00:00:00.000000') as t order by add_time asc";
                $tmpList = M()->query($sql);
                switch(count($tmpList)){
                    case 0: // 首投用户
                        $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                        $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                        $tmpList2 = M()->query($sql2);
                        if(count($tmpList2) > 0){ // 首投
                            if($tmpList2[0]['fr'] == 1){ // 购买产品
                                $rows['first_pay_p_money'] += $tmpList2[0]['money'];
                            }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                                $rows['first_pay_w_money'] += $tmpList2[0]['money'];
                            }
                        }
                        if(count($tmpList2) > 1){ // 二投
                            if($tmpList2[1]['fr'] == 1){ // 购买产品
                                $rows['second_pay_p_count'] += 1;
                                $rows['second_pay_p_money'] += $tmpList2[1]['money'];
                            }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                                $rows['second_pay_w_count'] += 1;
                                $rows['second_pay_w_money'] += $tmpList2[1]['money'];
                            }
                        }
                        if(count($tmpList2) > 2){ // 三投
                            if($tmpList2[2]['fr'] == 1){ // 购买产品
                                $rows['third_pay_p_count'] += 1;
                                $rows['third_pay_p_money'] += $tmpList2[2]['money'];
                            }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                                $rows['third_pay_w_count'] += 1;
                                $rows['third_pay_w_money'] += $tmpList2[2]['money'];
                            }
                        }
                        if(count($tmpList2) > 3){ // 四投

                            if($tmpList2[3]['fr'] == 1){ // 购买产品
                                $rows['fourth_pay_p_count'] += 1;
                                $rows['fourth_pay_p_money'] += $tmpList2[3]['money'];
                            }else if($tmpList2[3]['fr'] == 2){ // 充值钱包
                                $rows['fourth_pay_w_count'] += 1;
                                $rows['fourth_pay_w_money'] += $tmpList2[3]['money'];
                            }
                        }
                        if(count($tmpList2) > 4){ // 五投
                            if($tmpList2[4]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[4]['money'];
                            }else if($tmpList2[4]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[4]['money'];
                            }
                        }
                        break;
                    case 1: // 二投用户
//                    if($tmpList[0]['fr'] == 1){ // 购买产品
//                        $rows['second_pay_p_count'] += 1;
//                        $rows['second_pay_p_money'] += $tmpList[1]['money'];
//                    }else if($tmpList[0]['fr'] == 2){ // 充值钱包
//                        $rows['second_pay_w_count'] += 1;
//                        $rows['second_pay_w_money'] += $tmpList[1]['money'];
//                    }
                        $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                        $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                        $tmpList2 = M()->query($sql2);
                        if(count($tmpList2) > 0){ // 二投
                            if($tmpList2[0]['fr'] == 1){ // 购买产品
                                $rows['third_pay_p_count'] += 1;
                                $rows['third_pay_p_money'] += $tmpList2[0]['money'];
                            }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                                $rows['third_pay_w_count'] += 1;
                                $rows['third_pay_w_money'] += $tmpList2[0]['money'];
                            }
                        }
                        if(count($tmpList2) > 1){ // 三投
                            if($tmpList2[1]['fr'] == 1){ // 购买产品
                                $rows['third_pay_p_count'] += 1;
                                $rows['third_pay_p_money'] += $tmpList2[1]['money'];
                            }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                                $rows['third_pay_w_count'] += 1;
                                $rows['third_pay_w_money'] += $tmpList2[1]['money'];
                            }
                        }
                        if(count($tmpList2) > 2){ // 四投
                            if($tmpList2[2]['fr'] == 1){ // 购买产品
                                $rows['fourth_pay_p_count'] += 1;
                                $rows['fourth_pay_p_money'] += $tmpList2[2]['money'];
                            }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                                $rows['fourth_pay_w_count'] += 1;
                                $rows['fourth_pay_w_money'] += $tmpList2[2]['money'];
                            }
                        }
                        if(count($tmpList2) > 3){ // 五投
                            if($tmpList2[3]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[3]['money'];
                            }else if($tmpList2[3]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[3]['money'];
                            }
                        }
                        break;
                    case 2: // 三投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['third_pay_p_count'] += 1;
//                        $rows['third_pay_p_money'] += $tmpList[2]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['third_pay_w_count'] += 1;
//                        $rows['third_pay_w_money'] += $tmpList[2]['money'];
//                    }
                        $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                        $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                        $tmpList2 = M()->query($sql2);
                        if(count($tmpList2) > 0){ // 三投
                            if($tmpList2[0]['fr'] == 1){ // 购买产品
                                $rows['fourth_pay_p_count'] += 1;
                                $rows['fourth_pay_p_money'] += $tmpList2[0]['money'];
                            }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                                $rows['fourth_pay_w_count'] += 1;
                                $rows['fourth_pay_w_money'] += $tmpList2[0]['money'];
                            }
                        }
                        if(count($tmpList2) > 1){ // 四投
                            if($tmpList2[1]['fr'] == 1){ // 购买产品
                                $rows['fourth_pay_p_count'] += 1;
                                $rows['fourth_pay_p_money'] += $tmpList2[1]['money'];
                            }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                                $rows['fourth_pay_w_count'] += 1;
                                $rows['fourth_pay_w_money'] += $tmpList2[1]['money'];
                            }
                        }
                        if(count($tmpList2) > 2){ // 五投
                            if($tmpList2[2]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[2]['money'];
                            }else if($tmpList2[2]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[2]['money'];
                            }
                        }
                        break;
                    case 3: // 四投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['fourth_pay_p_count'] += 1;
//                        $rows['fourth_pay_p_money'] += $tmpList[3]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['fourth_pay_w_count'] += 1;
//                        $rows['fourth_pay_w_money'] += $tmpList[3]['money'];
//                    }
                        $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                        $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                        $tmpList2 = M()->query($sql2);
                        if(count($tmpList2) > 0){ // 四投
                            if($tmpList2[0]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[0]['money'];
                            }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[0]['money'];
                            }
                        }
                        if(count($tmpList2) > 1){ // 五投
                            if($tmpList2[1]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[1]['money'];
                            }else if($tmpList2[1]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[1]['money'];
                            }
                        }
                        break;
                    case 4: // 五投用户
//                    if($val['fr'] == 1){ // 购买产品
//                        $rows['fifth_pay_p_count'] += 1;
//                        $rows['fifth_pay_p_money'] += $tmpList[4]['money'];
//                    }else if($val['fr'] == 2){ // 充值钱包
//                        $rows['fifth_pay_w_count'] += 1;
//                        $rows['fifth_pay_w_money'] += $tmpList[4]['money'];
//                    }
                        $sql2 = "select * from (select due_capital as money,1 as fr,add_time from s_user_due_detail where user_id=".$val." and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000' UNION ALL ";
                        $sql2.= "select value as money,2 as fr,add_time from s_user_wallet_records where user_id=".$val." and type=1 and pay_status=2 and add_time>='".$dt." 00:00:00.000000' and add_time<='".$dt." 23:59:59.999000') as t order by add_time asc";
                        $tmpList2 = M()->query($sql2);
                        if(count($tmpList2) > 0){ // 五投
                            if($tmpList2[0]['fr'] == 1){ // 购买产品
                                $rows['fifth_pay_p_count'] += 1;
                                $rows['fifth_pay_p_money'] += $tmpList2[0]['money'];
                            }else if($tmpList2[0]['fr'] == 2){ // 充值钱包
                                $rows['fifth_pay_w_count'] += 1;
                                $rows['fifth_pay_w_money'] += $tmpList2[0]['money'];
                            }
                        }
                        break;
                }
            }

            foreach($rows as $key => $val){
                if(is_null($val)) $rows[$key] = 0;
            }

            if(!$existID){ // 不存在每日统计信息
                if(!$statisticsDailyObj->add($rows)){
                    exit(json_encode(array("status"=>2,'msg'=>"处理失败!")));
                }else{
                    exit(json_encode(array("status"=>1,'msg'=>"处理成功!")));
                }
            }else{ // 已存在统计信息
                if(!$statisticsDailyObj->where(array('id'=>$existID))->save($rows)){
                    exit(json_encode(array("status"=>2,'msg'=>"处理失败!")));
                }else{
                    exit(json_encode(array("status"=>1,'msg'=>"处理成功!")));
                }
            }
        }

    }
    /**
     * 每日统计数据记录Excel
     */
    public function statistics_daily_excel(){
        $is_batch = I('batch',1,'int');//1表示单条导出2表示全部导出
        $daily_id = I('id',0,'int');//统计标识
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
        ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('每日数据统计')
        ->setCellValue("A1", "日期")
        ->setCellValue("B1", "银行卡购买理财")
        ->setCellValue("C1", "银行卡充值钱包")
		->setCellValue("D1", "钱包购买理财产品")
		->setCellValue("E1", "理财产品还款到钱包")
        ->setCellValue("F1", "理财产品还款到银行卡")		
        ->setCellValue("G1", "钱包提现到银行卡")
        ->setCellValue("H1", "理财转银行卡订单数")
        ->setCellValue("I1", "钱包已投金额对应线下利率")
        ->setCellValue("J1", "钱包存量")
        ->setCellValue("K1", "钱包线上利率")
        ->setCellValue("L1", "钱包提现次数")
        ->setCellValue("M1", "投资理财(包括钱包)用户总数")
        ->setCellValue("N1", "投资理财产品用户总数")
        ->setCellValue("O1", "充值钱包总数")
        ->setCellValue("P1", "理财产品首投用户数")
        ->setCellValue("Q1", "钱包首投用户数")
        ->setCellValue("R1", "理财产品二投用户数")
        ->setCellValue("S1", "钱包二投用户数")
        ->setCellValue("T1", "理财产品三投用户数")
        ->setCellValue("U1", "钱包三投用户数")
        ->setCellValue("V1", "理财产品四投用户数")
        ->setCellValue("W1", "钱包四投用户数")
        ->setCellValue("X1", "理财产品五投用户数")
        ->setCellValue("Y1", "钱包五投用户数")
        ->setCellValue("Z1", "复投用户数")
        ->setCellValue("AA1", "激活当日投理财产品用户数")
        ->setCellValue("AB1", "激活当日投钱包用户数")
        ->setCellValue("AC1","投资理财产品金额")
        ->setCellValue("AD1","投资钱包金额")
        ->setCellValue("AE1","首投理财产品金额")
        ->setCellValue("AF1","首投钱包金额")
        ->setCellValue("AG1","二投理财产品金额")
        ->setCellValue("AH1","二投钱包金额")
        ->setCellValue("AI1","三投理财产品金额")
        ->setCellValue("AJ1","三投钱包金额")
        ->setCellValue("AK1","四投理财产品金额")
        ->setCellValue("AL1","四投钱包金额")
        ->setCellValue("AM1","五投理财产品金额")
        ->setCellValue("AN1","五投钱包金额")
        ->setCellValue("AO1","复投金额")
        ->setCellValue("AP1","投资用户数量")
        ->setCellValue("AQ1","首投用户数量")
        ->setCellValue("AR1","复投用户数量");
        $objPHPExcel->getActiveSheet()->getStyle('A1:AR1')->getFont()->setName('宋体')->setSize(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('AP')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('AQ')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('AR')->setWidth(20);

        $pos = 2;
        if($is_batch == 1){
            if(!$daily_id){
                $this->error("导出参数有误");exit;
            }
        }
        $statistics_daily_db = M('StatisticsDaily');//每日统计表
        if($is_batch == 2){//all
            $daily_statistics_list = $statistics_daily_db->order('dt asc')->select();
        }else{ //single
            $list = $statistics_daily_db->where('id='.$daily_id)->find();
        }
        if($is_batch == 2){//all excel
            foreach($daily_statistics_list as $k=>$v){
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $v['dt']);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $v['b2p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $v['b2w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $v['w2p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $v['p2w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $v['p2b_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $v['w2b_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $v['p2b_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $v['wallet_interest_offline']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $v['wallet_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, $v['wallet_interest']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, $v['w2b_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, $v['pay_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$pos, $v['pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, $v['pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$pos, $v['first_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$pos, $v['first_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$pos, $v['second_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$pos, $v['second_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$pos,$v['third_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$pos, $v['third_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$pos, $v['fourth_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("W".$pos, $v['fourth_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("X".$pos, $v['fifth_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Y".$pos, $v['fifth_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Z".$pos, $v['repay_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AA".$pos, $v['activation_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AB".$pos, $v['activation_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AC".$pos, $v['pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AD".$pos, $v['pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AE".$pos, $v['first_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AF".$pos, $v['first_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AG".$pos, $v['second_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AH".$pos, $v['second_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AI".$pos, $v['third_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AJ".$pos, $v['third_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AK".$pos, $v['fourth_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AL".$pos, $v['fourth_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AM".$pos, $v['fifth_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AN".$pos, $v['fifth_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AO".$pos, $v['repay_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AP".$pos, $v['pay_uid_daily_num']);
                $objPHPExcel->getActiveSheet()->setCellValue("AQ".$pos, $v['first_pay_uid_daily_num']);
				$objPHPExcel->getActiveSheet()->setCellValue("AR".$pos, $v['repay_uid_daily_num']);
                $pos += 1;
            }
        }else{//single excel
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $list['dt']);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $list['b2p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $list['b2w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $list['w2p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $list['p2w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $list['p2b_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $list['w2b_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $list['p2b_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $list['wallet_interest_offline']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $list['wallet_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, $list['wallet_interest']);
                $objPHPExcel->getActiveSheet()->setCellValue("L".$pos, $list['w2b_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("M".$pos, $list['pay_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("N".$pos, $list['pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("O".$pos, $list['pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("P".$pos, $list['first_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Q".$pos, $list['first_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("R".$pos, $list['second_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("S".$pos, $list['second_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("T".$pos,$list['third_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("U".$pos, $list['third_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("V".$pos, $list['fourth_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("W".$pos, $list['fourth_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("X".$pos, $list['fifth_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Y".$pos, $list['fifth_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("Z".$pos, $list['repay_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AA".$pos, $list['activation_pay_p_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AB".$pos, $list['activation_pay_w_count']);
                $objPHPExcel->getActiveSheet()->setCellValue("AC".$pos, $list['pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AD".$pos, $list['pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AE".$pos, $list['first_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AF".$pos, $list['first_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AG".$pos, $list['second_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AH".$pos, $list['second_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AI".$pos, $list['third_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AJ".$pos, $list['third_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AK".$pos, $list['fourth_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AL".$pos, $list['fourth_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AM".$pos, $list['fifth_pay_p_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AN".$pos, $list['fifth_pay_w_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AO".$pos, $list['repay_money']);
                $objPHPExcel->getActiveSheet()->setCellValue("AP".$pos, $list['pay_uid_daily_num']);
                $objPHPExcel->getActiveSheet()->setCellValue("AQ".$pos, $list['first_pay_uid_daily_num']);
				$objPHPExcel->getActiveSheet()->setCellValue("AR".$pos, $list['repay_uid_daily_num']);
				$pos += 1;
        }
        $now_date = date("Y-m-d",time());
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="每日数据统计('.$now_date.').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
	    /***
     * 用户银行卡信息
     */
    public function bank_list(){
		$uid = I('get.uid','','int');//用户ID
		if(!$uid){ 
			$this->error("用户ID参数为空");exit;
		}
		$user_bank_db = M('UserBank');
        $userAccountObj = M('UserAccount');
		$user_bank_list = $user_bank_db->field('id,bank_name,bank_code,mobile,id_no,acct_name,bank_card_no,has_pay_success,latest_payment_time,add_time,wallet_money')->where(array('user_id'=>$uid))->select();
		foreach($user_bank_list as $k => $v){
            $userAccountList = $userAccountObj->field('wallet_interest,wallet_product_interest')->where(array('user_id'=>$v['user_id']))->find();
            $user_bank_list[$k]['wallet_money'] = $v['wallet_money']+$userAccountList['wallet_interest']+$userAccountList['wallet_product_interest'];
        }
        $this->assign('bank_list',$user_bank_list);
        $this->display();
    }
	/***
     * 修改用户银行卡信息
     */
    public function update_bank_card(){		
		$user_bank_id = I('post.id','','int');//用户银行卡编号
		$new_bank_card = I('post.newbankcard','','strip_tags');//新银行卡号
		if(!$user_bank_id || !$new_bank_card){ //参数有误
			$this->ajaxReturn(array('status'=>1,'msg'=>'参数有误'));
		}
		$user_bank_db = M('UserBank');
		$user_due_detail_db = M('UserDueDetail');
		$investment_detail_db = M('InvestmentDetail');
		$recharge_log_db = M('RechargeLog');
        //查出用户信息
        $user_bank_info = $user_bank_db->where(array('id'=>$user_bank_id))->find();
        if(empty($user_bank_info)){
            $this->ajaxReturn(array("status"=>2,'msg'=>'用户关联银行卡不存在'));
        }
        $user_id = $user_bank_info['user_id'];
        $old_bank_no = $user_bank_info['bank_card_no'];
        //状态
        $flag = false;
        //1.0修改用户关联银行卡表
        $user_bank_db->startTrans();
        $user_bank_data = array(
            'modify_time'=>date('Y-m-d H:i:s',time()).'.'.getMillisecond().'000',
            'bank_card_no'=>$new_bank_card
        );
        $user_bank_update_status = $user_bank_db->where(array('id'=>$user_bank_id))->save($user_bank_data);
        if($user_bank_update_status!==false){
            $user_bank_db->commit();
            $flag = true;
        }else{
            $user_bank_db->rollback();
            $flag = false;
        }
		//2.0修改用户充值记录表
        $recharge_log_db->startTrans();
        $user_recharge_data = array(
            'card_no'=>$new_bank_card
        );
        $user_recharge_update_status = $recharge_log_db->where(array('user_id'=>$user_id,'card_no'=>$old_bank_no))->save($user_recharge_data);
        if($user_recharge_update_status !==false){
            $recharge_log_db->commit();
            $flag = true;
        }else{
            $recharge_log_db->rollback();
            $flag = false;
        }
        //3.0 修改用户待收利息表
        $user_due_detail_db->startTrans();
        $user_due_detail_data = array(
            'card_no'=>$new_bank_card
        );
        $user_due_detail_update_status = $user_due_detail_db->where(array('user_id'=>$user_id,'card_no'=>$old_bank_no))->save($user_due_detail_data);
        if($user_due_detail_update_status !==false){
            $user_due_detail_db->commit();
            $flag = true;
        }else{
            $user_due_detail_db->rollback();
            $flag = false;
        }
        //4.0 修改用户投资记录表
        $investment_detail_db->startTrans();
        $investment_detail_data = array(
            'card_no'=>$new_bank_card
        );
        $investment_detail_update_status  = $investment_detail_db->where(array('user_id'=>$user_id,'card_no'=>$old_bank_no))->save($investment_detail_data);
        if($investment_detail_update_status!==false){
            $investment_detail_db->commit();
            $flag = true;
        }else{
            $investment_detail_db->rollback();
            $flag = false;
        }
        if($flag){
            $this->ajaxReturn(array('status'=>3,'msg'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('status'=>4,'msg'=>'修改失败'));
        }
	}
	/**
     * 收支图表
     */
    public function daily_income(){

        if(!IS_POST){
            $StatisticsDailyProfitObj = M("StatisticsDailyProfit");//理财产品(包括钱包)每日收益统计表
            $target = I('get.target', '', 'strip_tags');
            $datetime = I('get.dt', date('Y-m', time()), 'strip_tags');
            $cache_val = I("get.cache",0,'int');//是否缓存1表示刷新缓存0表示使用缓存

            switch($target){
                case 'Investment': // 理财收支状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('investment_income_daily_'.str_replace('-', '_', $datetime));
                    }
                    if(!$cacheData){
                        $StatisticsDailyProfit = $StatisticsDailyProfitObj->where("dt>='".$date[0]."' and dt<='".$date[1]."'")->order('dt asc')->select();
                        $due_date_str = "";//理财收支日期
                        $due_profit_str="";//理财收益
                        $due_income_str="";//理财收入
                        $due_expense_str="";//理财支出

                        foreach($StatisticsDailyProfit as $key => $val){
                            $dt_day = date("d",strtotime($val['dt']));
                            $due_date_str.=','.$dt_day;
                            $due_profit_str.=','.$val['p_profit'];
                            $due_income_str.=','.$val['p_income'];
                            $due_expense_str.=','.$val['p_expenses'];

                        }
                        if($due_profit_str) $due_profit_str = mb_substr($due_profit_str, 1, mb_strlen($due_profit_str) - 1, 'utf-8');
                        if($due_income_str) $due_income_str = substr($due_income_str, 1);
                        if($due_expense_str) $due_expense_str = substr($due_expense_str, 1);
                        if($due_date_str) $due_date_str = substr($due_date_str,1);

                        $rows = array(
                            'due_profit' => $due_profit_str,
                            'due_income' => $due_income_str,
                            'due_expense' => $due_expense_str,
                            'due_date'=>$due_date_str,
                        );
                        if($datetime < $now){
                            F('investment_income_daily_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $due_profit_str = $cacheData['due_profit'];
                        $due_income_str = $cacheData['due_income'];
                        $due_expense_str = $cacheData['due_expense'];
                        $due_date_str = $cacheData['due_date'];
                    }

                    $this->assign('due_profit', $due_profit_str);
                    $this->assign('due_income', $due_income_str);
                    $this->assign('due_expense', $due_expense_str);
                    $this->assign('due_date',$due_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('daily_income_investment');
                    exit;
                    break;
                case 'wallet': // 钱包收支状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('wallet_income_daily_'.str_replace('-', '_', $datetime));
                    }

                    if(!$cacheData){
                        $StatisticsDailyProfit = $StatisticsDailyProfitObj->where("dt>='".$date[0]."' and dt<='".$date[1]."'")->order('dt asc')->select();
                        $wallet_date_str = "";//钱包收支日期
                        $wallet_profit_str="";// 钱包收益
                        $wallet_income_str="";//钱包收入
                        $wallet_expense_str="";//钱包支出

                        foreach($StatisticsDailyProfit as $key => $val){
                            $dt_day = date("d",strtotime($val['dt']));
                            $wallet_date_str.=','.$dt_day;
                            $wallet_profit_str.=','.$val['w_profit'];
                            $wallet_income_str.=','.$val['w_income'];
                            $wallet_expense_str.=','.$val['w_expenses'];

                        }
                        if($wallet_profit_str) $wallet_profit_str = mb_substr($wallet_profit_str, 1, mb_strlen($wallet_profit_str) - 1, 'utf-8');
                        if($wallet_income_str) $wallet_income_str = substr($wallet_income_str, 1);
                        if($wallet_expense_str) $wallet_expense_str = substr($wallet_expense_str, 1);
                        if($wallet_date_str) $wallet_date_str = substr($wallet_date_str,1);

                        $rows = array(
                            'wallet_profit' => $wallet_profit_str,
                            'wallet_income' => $wallet_income_str,
                            'wallet_expense' => $wallet_expense_str,
                            'wallet_date'=>$wallet_date_str,
                        );
                        if($datetime < $now){
                            F('wallet_income_daily_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $wallet_profit_str = $cacheData['wallet_profit'];
                        $wallet_income_str = $cacheData['wallet_income'];
                        $wallet_expense_str = $cacheData['wallet_expense'];
                        $wallet_date_str = $cacheData['wallet_date'];
                    }
                    $this->assign('wallet_profit', $wallet_profit_str);
                    $this->assign('wallet_income', $wallet_income_str);
                    $this->assign('wallet_expense', $wallet_expense_str);
                    $this->assign('wallet_date',$wallet_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('daily_income_wallet');
                    exit;
                    break;
                case 'amount': // 总收支状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('amount_income_daily_'.str_replace('-', '_', $datetime));
                    }
                    if(!$cacheData){
                        $StatisticsDailyProfit = $StatisticsDailyProfitObj->where("dt>='".$date[0]."' and dt<='".$date[1]."'")->order('dt asc')->select();
                        $amount_date_str = "";//总收支日期
                        $amount_profit_str="";// 总收益
                        $amount_income_str="";//总收入
                        $amount_expense_str="";//总支出

                        foreach($StatisticsDailyProfit as $key => $val){
                            $dt_day = date("d",strtotime($val['dt']));
                            $amount_date_str.=','.$dt_day;
                            $amount_profit_str.=','.($val['w_profit']+$val['p_profit']);
                            $amount_income_str.=','.($val['w_income']+$val['p_income']);
                            $amount_expense_str.=','.($val['w_expenses']+$val['p_expenses']);

                        }
                        if($amount_profit_str) $amount_profit_str = mb_substr($amount_profit_str, 1, mb_strlen($amount_profit_str) - 1, 'utf-8');
                        if($amount_income_str) $amount_income_str = substr($amount_income_str, 1);
                        if($amount_expense_str) $amount_expense_str = substr($amount_expense_str, 1);
                        if($amount_date_str) $amount_date_str = substr($amount_date_str,1);

                        $rows = array(
                            'amount_profit' => $amount_profit_str,
                            'amount_income' => $amount_income_str,
                            'amount_expense' => $amount_expense_str,
                            'amount_date'=>$amount_date_str,
                        );
                        if($datetime < $now){
                            F('amount_income_daily_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $amount_profit_str = $cacheData['amount_profit'];
                        $amount_income_str = $cacheData['amount_income'];
                        $amount_expense_str = $cacheData['amount_expense'];
                        $amount_date_str = $cacheData['amount_date'];
                    }
                    $this->assign('amount_profit', $amount_profit_str);
                    $this->assign('amount_income', $amount_income_str);
                    $this->assign('amount_expense', $amount_expense_str);
                    $this->assign('amount_date',$amount_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('daily_income_amount');
                    exit;
                    break;
            }
           $this->display();

        }else{
            $target = I('post.target', '', 'strip_tags');
            $datetime = I('post.dt', '', 'strip_tags');
            $cache_val = I("post.cache",0,'int');//是否缓存1表示刷新缓存0表示使用缓存
            $id = I('post.id', 0, 'int');
            if($datetime){
                redirect(C('ADMIN_ROOT').'/statistics/daily_income/target/'.$target.'/dt/'.$datetime.'/cache/'.$cache_val);
            }else{
                redirect(C('ADMIN_ROOT').'/statistics/daily_income/target/'.$target.'/cache/'.$cache_val);
            }
        }
    }
	/**
     * 用户在投项目和用户累计投资项目(going在投，history历史累计投资)
     */
    public function due_search(){

        $type = I("get.type",'going','trim');//类型
        $user_id = I("get.user_id",'0','int');//用户ID
        $now_date = date("Y-m-d")." 23:59:59.999000";
        if(!$user_id || !$type){
            $this->error("参数有误,请联系管理员");
        }
        $total_due_capital = 0;
        $due_list=array();
        if($type == "going"){
            $sql = "SELECT m.`title`,a.`due_capital`,a.`from_wallet`,a.`add_time`,a.`due_time`,k.`bank_name` FROM `s_user_due_detail` AS a,`s_project` AS m,`s_user_bank` AS k WHERE a.`user_id` = k.`user_id` AND a.`card_no` = k.`bank_card_no` AND a.`project_id` = m.`id` AND a.`user_id`=".$user_id." AND a.`due_time`>'".$now_date."' order by a.`add_time` desc";
            $going_due_list = M()->query($sql);
            foreach($going_due_list as $k=>$v){
                $total_due_capital+=$v['due_capital'];
				if($v['from_wallet']){ 
					$going_due_list[$k]['bank_name'] = "票票喵钱包";
				}else{ 
					$going_due_list[$k]['bank_name'] = $v['bank_name'];
				}
            }
            $due_list = $going_due_list;
            $type_name = "在投";
        }else if($type == "history"){
            $sql = "SELECT m.`title`,a.`due_capital`,a.`from_wallet`,a.`add_time`,a.`due_time`,k.`bank_name` FROM `s_user_due_detail` AS a,`s_project` AS m,`s_user_bank` AS k WHERE a.`user_id` = k.`user_id` AND a.`card_no` = k.`bank_card_no` AND a.`project_id` = m.`id` AND a.`user_id`=".$user_id." order by a.`add_time` desc";
            $history_due_list = M()->query($sql);
            foreach($history_due_list as $k=>$v){
                $total_due_capital+=$v['due_capital'];
				if($v['from_wallet']){ 
					$history_due_list[$k]['bank_name'] = "票票喵钱包";
				}else{ 
					$history_due_list[$k]['bank_name'] = $v['bank_name'];
				}
            }
            $due_list = $history_due_list;
            $type_name = "累计";
        }
        $this->assign("total_due_capital",number_format($total_due_capital,2));
        $this->assign("due_list",$due_list);
        $this->assign("type_name",$type_name);
        $this->display();
    }
	/**
     * 平台资金流
     */
    public function cash_flow(){
        if(!IS_POST){
            $PlatformCashFlowObj = M("PlatformCashFlow");//平台资金流统计表
            $target = I('get.target', '', 'strip_tags');
            $datetime = I('get.dt', date('Y-m', time()), 'strip_tags');
            $cache_val = I("get.cache",0,'int');//是否缓存1表示刷新缓存0表示使用缓存

            switch($target){
                case 'Investment': // 理财资金流状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('cash_flow_investment_'.str_replace('-', '_', $datetime));
                    }
                    if(!$cacheData){
                        $PlatformCashFlowList = $PlatformCashFlowObj->where("day>='".$date[0]."' and day<='".$date[1]."'")->order('day asc')->select();
                        $due_cash_flow_date_str = "";//理财资金流日期
                        $due_stock_str="";//理财存量
                        $due_inflow_str="";//理财流入
                        $due_flowout_str="";//理财流出

                        foreach($PlatformCashFlowList as $key => $val){
                            $dt_day = date("d",strtotime($val['day']));
                            $due_cash_flow_date_str.=','.$dt_day;
                            $due_stock_str.=','.$val['due_stock'];
                            $due_inflow_str.=','.$val['due_inflow'];
                            $due_flowout_str.=','.$val['due_flowout'];

                        }
                        if($due_stock_str) $due_stock_str = mb_substr($due_stock_str, 1, mb_strlen($due_stock_str) - 1, 'utf-8');
                        if($due_inflow_str) $due_inflow_str = substr($due_inflow_str, 1);
                        if($due_flowout_str) $due_flowout_str = substr($due_flowout_str, 1);
                        if($due_cash_flow_date_str) $due_cash_flow_date_str = substr($due_cash_flow_date_str,1);

                        $rows = array(
                            'due_stock' => $due_stock_str,
                            'due_inflow' => $due_inflow_str,
                            'due_flowout' => $due_flowout_str,
                            'due_cash_flow_date'=>$due_cash_flow_date_str,
                        );
                        if($datetime < $now){
                            F('cash_flow_investment_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $due_stock_str = $cacheData['due_stock'];
                        $due_inflow_str = $cacheData['due_inflow'];
                        $due_flowout_str = $cacheData['due_flowout'];
                        $due_cash_flow_date_str = $cacheData['due_cash_flow_date'];
                    }

                    $this->assign('due_stock', $due_stock_str);
                    $this->assign('due_inflow', $due_inflow_str);
                    $this->assign('due_flowout', $due_flowout_str);
                    $this->assign('due_cash_flow_date',$due_cash_flow_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('cash_flow_investment');
                    exit;
                    break;
                case 'wallet': // 钱包收支状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('cash_flow_wallet_'.str_replace('-', '_', $datetime));
                    }

                    if(!$cacheData){
                        $PlatformCashFlowList = $PlatformCashFlowObj->where("day>='".$date[0]."' and day<='".$date[1]."'")->order('day asc')->select();
                        $wallet_cash_flow_date_str = "";//钱包资金流日期
                        $wallet_stock_str="";// 钱包存量
                        $wallet_inflow_str="";//钱包流入
                        $wallet_flowout_str="";//钱包流出

                        foreach($PlatformCashFlowList as $key => $val){
                            $dt_day = date("d",strtotime($val['day']));
                            $wallet_cash_flow_date_str.=','.$dt_day;
                            $wallet_stock_str.=','.$val['wallet_stock'];
                            $wallet_inflow_str.=','.$val['wallet_inflow'];
                            $wallet_flowout_str.=','.$val['wallet_flowout'];

                        }
                        if($wallet_stock_str) $wallet_stock_str = mb_substr($wallet_stock_str, 1, mb_strlen($wallet_stock_str) - 1, 'utf-8');
                        if($wallet_inflow_str) $wallet_inflow_str = substr($wallet_inflow_str, 1);
                        if($wallet_flowout_str) $wallet_flowout_str = substr($wallet_flowout_str, 1);
                        if($wallet_cash_flow_date_str) $wallet_cash_flow_date_str = substr($wallet_cash_flow_date_str,1);

                        $rows = array(
                            'wallet_stock' => $wallet_stock_str,
                            'wallet_inflow' => $wallet_inflow_str,
                            'wallet_flowout' => $wallet_flowout_str,
                            'wallet_cash_flow_date'=>$wallet_cash_flow_date_str,
                        );
                        if($datetime < $now){
                            F('cash_flow_wallet_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $wallet_stock_str = $cacheData['wallet_stock'];
                        $wallet_inflow_str = $cacheData['wallet_inflow'];
                        $wallet_flowout_str = $cacheData['wallet_flowout'];
                        $wallet_cash_flow_date_str = $cacheData['wallet_cash_flow_date'];
                    }
                    $this->assign('wallet_stock', $wallet_stock_str);
                    $this->assign('wallet_inflow', $wallet_inflow_str);
                    $this->assign('wallet_flowout', $wallet_flowout_str);
                    $this->assign('wallet_cash_flow_date',$wallet_cash_flow_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('cash_flow_wallet');
                    exit;
                    break;
                case 'amount': // 总收支状况
                    $now = date('Y-m-d', time());
                    $date = get_the_month($datetime);
                    if($cache_val){
                        $cacheData = null;
                    }else{
                        $cacheData = F('cash_flow_amount_'.str_replace('-', '_', $datetime));
                    }
                    if(!$cacheData){
                        $PlatformCashFlowList = $PlatformCashFlowObj->where("day>='".$date[0]."' and day<='".$date[1]."'")->order('day asc')->select();
                        $amount_cash_date_str = "";//总资金流日期
                        $amount_cash_flow_str="";// 总存量
                        $amount_inflow_str="";//总流入
                        $amount_flowout_str="";//总流出

                        foreach($PlatformCashFlowList as $key => $val){
                            $dt_day = date("d",strtotime($val['day']));
                            $amount_cash_date_str.=','.$dt_day;
                            $amount_cash_flow_str.=','.($val['w_profit']+$val['p_profit']);
                            $amount_inflow_str.=','.($val['w_income']+$val['p_income']);
                            $amount_flowout_str.=','.($val['w_expenses']+$val['p_expenses']);

                        }
                        if($amount_cash_flow_str) $amount_cash_flow_str = mb_substr($amount_cash_flow_str, 1, mb_strlen($amount_cash_flow_str) - 1, 'utf-8');
                        if($amount_inflow_str) $amount_inflow_str = substr($amount_inflow_str, 1);
                        if($amount_flowout_str) $amount_flowout_str = substr($amount_flowout_str, 1);
                        if($amount_cash_date_str) $amount_cash_date_str = substr($amount_cash_date_str,1);

                        $rows = array(
                            'amount_cash_flow' => $amount_cash_flow_str,
                            'amount_inflow' => $amount_inflow_str,
                            'amount_flowout' => $amount_flowout_str,
                            'amount_cash_date'=>$amount_cash_date_str,
                        );
                        if($datetime < $now){
                            F('cash_flow_amount_'.str_replace('-', '_', $datetime), $rows);
                        }
                    }else{
                        $amount_cash_flow_str = $cacheData['amount_cash_flow'];
                        $amount_inflow_str = $cacheData['amount_inflow'];
                        $amount_flowout_str = $cacheData['amount_flowout'];
                        $amount_cash_date_str = $cacheData['amount_cash_date'];
                    }
                    $this->assign('amount_cash_flow', $amount_cash_flow_str);
                    $this->assign('amount_inflow', $amount_inflow_str);
                    $this->assign('amount_flowout', $amount_flowout_str);
                    $this->assign('amount_cash_date',$amount_cash_date_str);
                    $this->assign('dt', $datetime);
                    $this->display('cash_flow_amount');
                    exit;
                    break;
            }
            $this->display();

        }else{
            $target = I('post.target', '', 'strip_tags');
            $datetime = I('post.dt', '', 'strip_tags');
            $cache_val = I("post.cache",0,'int');//是否缓存1表示刷新缓存0表示使用缓存
            if($datetime){
                redirect(C('ADMIN_ROOT').'/statistics/cash_flow/target/'.$target.'/dt/'.$datetime.'/cache/'.$cache_val);
            }else{
                redirect(C('ADMIN_ROOT').'/statistics/cash_flow/target/'.$target.'/cache/'.$cache_val);
            }
        }
    }
	//核查是否调单
	public function checkSingleOut(){ 
		if(IS_POST){
			$rechargeLogObj = M("RechargeLog");
			$recharge_id = I('post.id',0,'int');//下单记录ID
			if(!$recharge_id){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'订单参数有误，请联系系统管理员!!'));
			}
			//判断订单ID是否存在
			$rechargeLogList = $rechargeLogObj->where(array('id'=>$recharge_id))->find();
			if(!$rechargeLogList){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'此订单不存在'));
			}
			
			//判断用户投资记录表是否存在此订单
			$investmentDetailObj = M("InvestmentDetail");
			$investmentDetailList = $investmentDetailObj->where(array('user_id'=>$rechargeLogList['user_id'],'project_id'=>$rechargeLogList['project_id'],'recharge_no'=>$rechargeLogList['recharge_no']))->find();
			
			if(!$investmentDetailList){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'此订单已掉单，请及时补单'));
			}
			//判断用户待收利息表是否存在此订单
			$userDueDetailObj = M("UserDueDetail");
			$userDueDetailList = $userDueDetailObj->where(array('user_id'=>$investmentDetailList['user_id'],'project_id'=>$investmentDetailList['project_id'],'invest_detail_id'=>$investmentDetailList['id']))->find();
			if(!$userDueDetailList){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'此订单已掉单，请及时补单'));
			}
			//查出产品的名称
			$ProjectObj = M("Project");
			$projectName = $ProjectObj->where(array('id'=>$userDueDetailList['project_id']))->getField("title");
			$this->ajaxReturn(array('status' => 1,'info'=>$projectName.',此订单没有掉单'));
			
		}
	}
	//银行卡补单
	public function supplySingle(){ 
		if(IS_POST){
			$rechargeLogObj = M("RechargeLog");
			$recharge_id = I('post.id',0,'int');//下单记录ID
			if(!$recharge_id){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'订单参数有误，请联系系统管理员!!'));
			}
			//判断订单ID是否存在
			$rechargeLogList = $rechargeLogObj->where(array('id'=>$recharge_id))->find();
			if(!$rechargeLogList){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'此订单不存在'));
			}
			//先补用户投资记录表里面的订单
			//1.0判断订单是否已经存在			
			$investmentDetailObj = M("InvestmentDetail");
			$investmentDetailList = $investmentDetailObj->where(array('user_id'=>$rechargeLogList['user_id'],'project_id'=>$rechargeLogList['project_id'],'recharge_no'=>$rechargeLogList['recharge_no']))->find();			
			if($investmentDetailList){ 
				$this->ajaxReturn(array('status' => 0,'info'=>'用户投资记录表里面的订单记录已经存在，请联系系统管理员'));
			}
			//查出标的信息
			$ProjectObj = M("Project");
			$projectList = $ProjectObj->where(array('id'=>$rechargeLogList['project_id']))->find();
			//2.0补用户的投资记录			
			$investmentDetailObj->startTrans();
			$investmentDetailData = array( 
				'user_id'=>$rechargeLogList['user_id'],
				'project_id'=>$rechargeLogList['project_id'],
				'inv_total'=>$rechargeLogList['amount'],
				'inv_succ'=>$rechargeLogList['amount'],
				'device_type'=>$rechargeLogList['device_type'],
				'auto_inv'=>1,
				'recharge_no'=>$rechargeLogList['recharge_no'],
				'status'=>$rechargeLogList['status'],
				'status_new'=>$rechargeLogList['status'],
				'bow_type'=>$projectList['type'],
				'card_no'=>$rechargeLogList['card_no'],
				'ghost_phone'=>$rechargeLogList['ghost_phone'],
				'add_time'=>$rechargeLogList['add_time'],
				'add_user_id'=>$rechargeLogList['add_user_id'],
				'modify_time'=>$rechargeLogList['modify_time'],
				'modify_user_id'=>$rechargeLogList['modify_user_id'],			
			);
            $investmentDetailId = $investmentDetailObj->add($investmentDetailData);
			if(!$investmentDetailId){
                $investmentDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,''=>'补用户投资记录表里面的订单失败,请重试'));
            }
            $investmentDetailObj->commit();			
			
			//再补用户待收利息表里面的订单
            //1.0判断订单是否已经存在
			$userDueDetailObj = M("UserDueDetail");
			$userDueDetailList = $userDueDetailObj->where(array('user_id'=>$investmentDetailList['user_id'],'project_id'=>$investmentDetailList['project_id'],'invest_detail_id'=>$investmentDetailList['id']))->find();
			if($userDueDetailList){
                $this->ajaxReturn(array('status' => 0,'info'=>'用户待收记录表里面的订单记录已经存在，请联系系统管理员'));
			}
            //2.0补用户的待收记录
            //A产品待还信息记录表
            $RepaymentDetailObj = M("RepaymentDetail");
            $repaymentDetailList = $RepaymentDetailObj->where(array('project_id'=>$rechargeLogList['project_id']))->find();
            $userDueDetailObj->startTrans();
            $repayment_no = "RP".date('YmdHis', time()).getMillisecond().getMillisecond();

            switch($projectList['count_interest_type']){
                case 1://T+0
                    $start_time = $rechargeLogList['add_time'];
                break;
                case 2://T+1
                    $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+24*3600);
                    $start_time = $tmp_time;
                break;
                case 3://T+2
                    $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+(24*3600)*2);
                    $start_time = $tmp_time;
                break;
                case 4://T+3
                    $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+(24*3600)*3);
                    $start_time = $tmp_time;
                break;
            }

            $duration_day = (strtotime(date("Y-m-d",strtotime($projectList['end_time'])))-strtotime(date("Y-m-d",strtotime($rechargeLogList['add_time'])+24*3600)))/(24*3600)+1;
            $user_interest_tmp = (($rechargeLogList['amount']*$projectList['user_interest'])/100/365)*$duration_day;
            $user_interest = round($user_interest_tmp,2);
            $due_amount = $user_interest+$rechargeLogList['amount'];

            $userDueDetailData = array(
                'user_id'=>$rechargeLogList['user_id'],
                'project_id'=>$rechargeLogList['project_id'],
                'repay_id'=>$repaymentDetailList['id'],
                'invest_detail_id'=>$investmentDetailId,
                'due_amount'=>$due_amount,
                'due_capital'=>$rechargeLogList['amount'],
                'due_interest'=>$user_interest,
                'period'=>1,
                'duration_day'=>$duration_day,
                'start_time'=>$start_time,
                'due_time'=>$projectList['end_time'],
                'status'=>1,
                'status_new'=>1,
                'bow_type'=>$projectList['type'],
                'card_no'=>$rechargeLogList['card_no'],
                'repayment_no'=>$repayment_no,
                'device_type'=>$rechargeLogList['device_type'],
                'add_time'=>$rechargeLogList['add_time'],
                'add_user_id'=>$rechargeLogList['user_id'],
                'modify_time'=>$rechargeLogList['modify_time'],
                'modify_user_id'=>$rechargeLogList['user_id'],
            );
            if(!$userDueDetailObj->add($userDueDetailData)){
                $userDueDetailObj->rollback();
                $this->ajaxReturn(array('status'=>0,''=>'补用户待收记录表里面的订单失败,请重试'));
            }
            $userDueDetailObj->commit();
            $this->ajaxReturn(array('status'=>1,''=>'用户投资补单成功'));
        }
	}
	
	
	
	
	/**
	 * @2016.4.6
	 */
	//银行卡补单
	public function supplySingle2(){
	    
	    if(IS_POST){
	        
	        $rechargeLogObj = M("RechargeLog");
	        
	        $recharge_id = I('post.id',0,'int');//下单记录ID
	        
	        if(!$recharge_id){
	            
	            $this->ajaxReturn(array('status' => 0,'info'=>'订单参数有误，请联系系统管理员!!'));
	            
	        }
	        //判断订单ID是否存在
	        $rechargeLogList = $rechargeLogObj->where(array('id'=>$recharge_id))->find();
	        
	        if(!$rechargeLogList){
	            
	            $this->ajaxReturn(array('status' => 0,'info'=>'此订单不存在'));
	        }
	        
	        
	        //先补用户投资记录表里面的订单
	        //1.0判断订单是否已经存在
	        $investmentDetailObj = M("InvestmentDetail");
	        
	        $investmentDetailList = $investmentDetailObj->where(array('user_id'=>$rechargeLogList['user_id'],'project_id'=>$rechargeLogList['project_id'],'recharge_no'=>$rechargeLogList['recharge_no']))->find();

	        if($investmentDetailList){
	            
	            $this->ajaxReturn(array('status' => 0,'info'=>'用户投资记录表里面的订单记录已经存在，请联系系统管理员'));
	        }
	        
	        //查出标的信息
	        $ProjectObj = M("Project");
	        
	        $projectList = $ProjectObj->where(array('id'=>$rechargeLogList['project_id']))->find();
	        
	        //2.0补用户的投资记录
	        $investmentDetailObj->startTrans();
	        
            $investmentDetailData = array(
                'user_id' => $rechargeLogList['user_id'],
                'project_id' => $rechargeLogList['project_id'],
                'inv_total' => $rechargeLogList['amount'],
                'inv_succ' => $rechargeLogList['amount'],
                'device_type' => $rechargeLogList['device_type'],
                'auto_inv' => 1,
                'recharge_no' => $rechargeLogList['recharge_no'],
                'status' => $rechargeLogList['status'],
                'status_new' => $rechargeLogList['status'],
                'bow_type' => $projectList['type'],
                'card_no' => $rechargeLogList['card_no'],
                'ghost_phone' => $rechargeLogList['ghost_phone'],
                'add_time' => $rechargeLogList['add_time'],
                'add_user_id' => $rechargeLogList['add_user_id'],
                'modify_time' => $rechargeLogList['modify_time'],
                'modify_user_id' => $rechargeLogList['modify_user_id']
            );
	        
	        $investmentDetailId = $investmentDetailObj->add($investmentDetailData);

	        if(!$investmentDetailId){
	            
	            $investmentDetailObj->rollback();

	            $this->ajaxReturn(array('status'=>0,''=>'补用户投资记录表里面的订单失败,请重试'));
	        }
	        
	        $investmentDetailObj->commit();
	        	
	        //再补用户待收利息表里面的订单
	        //1.0判断订单是否已经存在
	        $userDueDetailObj = M("UserDueDetail");
	        $userDueDetailList = $userDueDetailObj->where(array('user_id'=>$investmentDetailList['user_id'],'project_id'=>$investmentDetailList['project_id'],'invest_detail_id'=>$investmentDetailList['id']))->find();
	        if($userDueDetailList){
	            $this->ajaxReturn(array('status' => 0,'info'=>'用户待收记录表里面的订单记录已经存在，请联系系统管理员'));
	        }
	        //2.0补用户的待收记录
	        //A产品待还信息记录表
	        $RepaymentDetailObj = M("RepaymentDetail");
	        $repaymentDetailList = $RepaymentDetailObj->where(array('project_id'=>$rechargeLogList['project_id']))->find();
	        $userDueDetailObj->startTrans();
	        $repayment_no = "RP".date('YmdHis', time()).getMillisecond().getMillisecond();
	
	        switch($projectList['count_interest_type']){
	            case 1://T+0
	                $start_time = $rechargeLogList['add_time'];
	                break;
	            case 2://T+1
	                $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+24*3600);
	                $start_time = $tmp_time;
	                break;
	            case 3://T+2
	                $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+(24*3600)*2);
	                $start_time = $tmp_time;
	                break;
	            case 4://T+3
	                $tmp_time = date("Y-m-d H:i:s",strtotime($rechargeLogList['add_time'])+(24*3600)*3);
	                $start_time = $tmp_time;
	                break;
	        }
	
	        $duration_day = (strtotime(date("Y-m-d",strtotime($projectList['end_time'])))-strtotime(date("Y-m-d",strtotime($rechargeLogList['add_time'])+24*3600)))/(24*3600)+1;
	        
	        $user_interest_tmp = (($rechargeLogList['amount']*$projectList['user_interest'])/100/365)*$duration_day;
	        
	        $user_interest = round($user_interest_tmp,2);
	        
	        $due_amount = $user_interest+$rechargeLogList['amount'];
	
	        $userDueDetailData = array(
	            'user_id'=>$rechargeLogList['user_id'],
	            'project_id'=>$rechargeLogList['project_id'],
	            'repay_id'=>$repaymentDetailList['id'],
	            'invest_detail_id'=>$investmentDetailId,
	            'due_amount'=>$due_amount,
	            'due_capital'=>$rechargeLogList['amount'],
	            'due_interest'=>$user_interest,
	            'period'=>1,
	            'duration_day'=>$duration_day,
	            'start_time'=>$start_time,
	            'due_time'=>$projectList['end_time'],
	            'status'=>1,
	            'status_new'=>1,
	            'bow_type'=>$projectList['type'],
	            'card_no'=>$rechargeLogList['card_no'],
	            'repayment_no'=>$repayment_no,
	            'device_type'=>$rechargeLogList['device_type'],
	            'add_time'=>$rechargeLogList['add_time'],
	            'add_user_id'=>$rechargeLogList['user_id'],
	            'modify_time'=>$rechargeLogList['modify_time'],
	            'modify_user_id'=>$rechargeLogList['user_id'],
	        );
	        if(!$userDueDetailObj->add($userDueDetailData)){
	            $userDueDetailObj->rollback();
	            $this->ajaxReturn(array('status'=>0,''=>'补用户待收记录表里面的订单失败,请重试'));
	        }
	        $userDueDetailObj->commit();
	        $this->ajaxReturn(array('status'=>1,''=>'用户投资补单成功'));
	    }
	}
	
	
	
	
	
	
    //用户历史累计利息表
    public function userInterestList(){
        $uid = I("get.uid",0,'int');
        $page = I('get.p', 1, 'int'); // 页码
        $count = 10; // 每页显示条数
        if(!$uid){
            $this->error("参数有误,请联系系统管理员");
        }
        $userWalletInterestObj = M('UserWalletInterest');
        $counts = $userWalletInterestObj->where(array('user_id'=>$uid))->count();
        $Page = new \Think\Page($counts, $count);
        $show = $Page->show();
        $list =$userWalletInterestObj->where(array('user_id'=>$uid))->order('add_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $totle = 0;
        foreach($list as $k=>$v){
			$list[$k]['add_time'] = date("Y-m-d",strtotime($v['add_time']));
			$list[$k]['interest_time'] = date("Y-m-d",strtotime($v['interest_time']));           
        }
		$money_list =$userWalletInterestObj->where(array('user_id'=>$uid))->select();
		foreach($money_list as $k => $v){ 
				$totle+=$v['interest'];
		}
        $this->assign('totle', $totle);
        $this->assign('show', $show);
        $this->assign('list', $list);
        $this->display();
    }
    //用户钱包补记录
    public function userWalletAddRecords(){
        $userWalletRecordsObj = M("UserWalletRecords");//用户钱包转入转出记录信息表
        $userObj    = M("User");//用户表
        $userBankObj =  M("UserBank");//用户关联银行卡
        if(IS_POST){
            $user_id = I("post.id",0,'int');//用户id
            $recharge_no = I("post.recharge_no",'','strip_tags');//充值编码
            $amount = I("post.amount",0,"trim");//充值金额
            $now_time = I("post.now_time",'',"strip_tags");//时间
            $pay_type = I("post.pay_type",'','int');//支付方式
            $type = I("post.type",'','int');//转入/转出类型
            $pay_status = I("post.pay_status",'','int');//支付成功与否
            $enable_interest = I("post.enable_interest",0,'int');//是否计息
            $remark  = I("post.remark",'','trim');//备注

            $key = I("post.key",'','strip_tags');
            if(!$user_id){
                $this->error("用户ID参数不能为空,联系系统管理员");
            }

            if(!$amount){
                $this->error("充值金额参数不能为空,联系系统管理员");
            }
            if(!$now_time){
                $this->error("补单时间参数不能为空,联系系统管理员");
            }
            if(!$pay_type){
                $this->error("支付方式参数不能为空,联系系统管理员");
            }
            if(!$type){
                $this->error("转入或者转出方式参数不能为空,联系系统管理员");
            }
            if(!$pay_status){
                $this->error("支付状态不能为空,联系系统管理员");
            }
            if(!$remark){
                $this->error("备注不能为空,联系系统管理员");
            }
            $userArr   = $userObj->where(array('id'=>$user_id))->find();
            //用户银行卡ID
            $userBankId  = $userBankObj->where(array('user_id'=>$user_id,'has_pay_success'=>2))->getField('id');
            //查出用户是什么机型
            $deviceType = $userObj->where(array('id'=>$user_id))->getField('device_type');
            //插入钱包转入/转出记录


            $userWalletRecordsArr = array(
                'user_id'=>$user_id,
                'recharge_no'=>$recharge_no,
                'pay_type'=>$pay_type,
                'value'=>$amount,
                'type'=>$type,
                'pay_status'=>$pay_status,
                'user_bank_id'=>$userBankId,
                'device_type'=>$deviceType,
                'add_time'=>$now_time,
                'modify_time'=>$now_time,
                'enable_interest'=>$enable_interest,
                'remark'=>$remark
            );
            $walletRecordId = $userWalletRecordsObj->add($userWalletRecordsArr);
            if($walletRecordId){
                $this->ajaxReturn(array("status"=>1,'link'=>C('ADMIN_ROOT')."/statistics/userWalletAddRecords/user_id/".$user_id."/key/".$key));

            }else{
                $this->ajaxReturn(array("status"=>0));
            }

        }else{
            $user_id = I("get.user_id",0,'int');//用户id
            $key  = I("get.key",'','strip_tags');//查询内容
            $this->assign("user_id",$user_id);
            $this->assign("key",$key);
            $this->display();
        }

    }
    /**
     * 录入用户跟踪记录
     */
    public function user_track(){
        $userTrackTypeObj = M('UserTrackType');//用户跟踪一级类型列表
        //用户id
        $user_id = I("get.uid",0,'int');
        $userTrackTypeList = $userTrackTypeObj->where(array('parent_id'=>0))->select();
        $this->assign("trackTypeList",$userTrackTypeList);
        $this->assign('user_id',$user_id);
        $this->display();
    }
    /**
     *
     *获取用户跟踪类型列表
     */
    public function catch_user_track(){
        $userTrackTypeObj = M('UserTrackType');//用户跟踪一级类型列表
        if(!IS_AJAX || !IS_POST){
            exit;
        }
        $type_id = I('post.type_id',0,'int');
        if(!$type_id){
            exit;
        }
        $userTrackTypeList = $userTrackTypeObj->where(array('parent_id'=>$type_id))->select();
        if($userTrackTypeList){
            $this->ajaxReturn($userTrackTypeList);
        }

    }
    /**
     * 录入用户跟踪内容
     */
    public function insert_user_track(){
        $userTrackContentObj = M('UserTrackContent');
        if(IS_POST){
            $user_track_content = I('post.user_track_content','','strip_tags');//跟踪内容
            $user_id = I("post.user_id",'','int');//用户id
            $index_type = I("post.index_type",'','int');//类型
            if(!$user_track_content || !$user_id || !$index_type){
                $this->ajaxReturn(array('status'=>0,'info'=>'参数有误'));
            }
            $time = date('Y-m-d H:i:s', time()).'.'.getMillisecond();
            $data=array(
                'type_id'=>$index_type,
                'track_user'=>$user_id,
                'content'=>$user_track_content,
                'add_time'=>$time,
                'add_user_id'=>$_SESSION[ADMIN_SESSION]['uid'],
                'modify_time'=>$time,
                'modify_user_id'=>$_SESSION[ADMIN_SESSION]['uid']
            );

            $insert_id = $userTrackContentObj->add($data);
            if($insert_id){
                $url = C('ADMIN_ROOT').'/statistics/user_track/uid/'.$user_id;
                $this->ajaxReturn(array('status'=>1,'link'=>$url));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'录入失败'));
            }
        }
    }
    /**
     *获取用户详细咨询问题
     */
    public function get_track_user_list(){
        //用户id
        $user_id = I("get.uid",0,'int');
        if(empty($user_id)){
            $this->error("用户参数不对,请联系系统管理员");
        }
        $sql = "SELECT m.`id`,n.`title`,m.`content`,m.`add_time`,k.`username` FROM `s_user_track_content` AS m,`s_user_track_type` AS n,`s_member` AS k WHERE m.`is_delete` =0 and m.`track_user` = ".$user_id." AND m.`type_id` = n.`id` AND k.`id` = m.`add_user_id`";
        $track_user_list = M()->query($sql);
        $this->assign("user_id",$user_id);
        $this->assign("track_user_list",$track_user_list);
        $this->display();
    }
    
    /**
     *获取用户收货地址
     */
    public function get_user_address(){
        //用户id
        $user_id = I("get.uid",0,'int');
        if(empty($user_id)){
            $this->error("用户参数不对,请联系系统管理员");
        }
        $list = M('userContact')->field('user_id,contact_name,contact_phone,area_code,address_detail,postalcode,is_default,add_time,area_name')
                                ->where('user_id ='.$user_id.' and is_deleted = 0')->select();
        
        foreach ($list as $key =>$val) {
            $list[$key]['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
        }
        $this->assign("user_id",$user_id);
        $this->assign("list",$list);
        $this->display();
    }
    
    /**
     * 导出指定月份的用户.注册了，其他啥也没干的用户
     */
    public function export_reg_user(){
        
        exit('不要点了，现在不能用');
        
        $startTime = date('Y-m-01', strtotime('-1 month'));
        $endTime = date('Y-m-t 23:23:59', strtotime('-1 month'));
        $condition = "add_time >='$startTime' AND add_time <'$endTime'";
        
        //渠道  用户名字  手机号  年龄 性别  注册时间
        $list = M("User")->field('id,username,real_name,card_no,add_time,channel_id')->where($condition)->select();
        
        if(empty($list)) {
            return ;
        }
        
        $res = array();
        
        foreach ($list as $val){
            //钱包充值
            $r = M("UserWalletRecords")->where('user_id=' . $val['id'] . ' and type=1 and pay_status=2')->count();
            
            if ($r<=0) {
                
               if (M('UserDueDetail')->where('user_id=' . $val['id'])->count() <= 0) {
                   
                   if($val['channel_id']) {
                       $val['channel_name'] = M('Constant')->where('id='.$val['channel_id'])->getField('cons_value');
                   }
                   
                   $val['sex'] = '';
                   $val['age'] = '';
                   
                   if($val['card_no']) {
                       $year = substr($val['card_no'], 6, 4);
                       $nowYear = date('Y', time());
                       $val['age'] = $nowYear - $year;
                       $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
                       if($sex % 2 != 0) {
                           $val['sex'] = '男';
                       } else {
                           $val['sex'] = '女';
                       }
                   }
                   $res[] = $val;
               }
            }
        }
        
        unset($list);
        
        if($res) {
            
            $filename = date("Y-m")."注册的用户 没有交易".'-'.date('YmdHis');

            vendor('PHPExcel.PHPExcel');
            
            
            $objPHPExcel = new \PHPExcel();
            
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")
            
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle(date('Y-m', strtotime('-1 month'))."注册的用户 没有交易")
                ->setCellValue("A1", "编号")
                ->setCellValue("B1", "渠道")
                ->setCellValue("C1", "名字 ")
                ->setCellValue("D1", "手机号")
                ->setCellValue("E1", "年龄")
                ->setCellValue("F1", "性别")
                ->setCellValue("G1", "注册时间");
            
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            
            // 设置列表值
            $pos = 2;
            $n = 1;
            foreach ($res as $val) {
                 
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
            
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['channel_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['username']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['age']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['sex']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['add_time']);
            
                $pos += 1;
            
                $n++;
            }
            
            ob_end_clean();//清除缓冲区,避免乱码
            
            
            header("Content-Type: application/vnd.ms-excel");
            
            header('Content-Disposition: attachment;filename='.date('Y-m', strtotime('-1 month')).'注册的用户无交易记录.xls');
            
            header('Cache-Control: max-age=0');
            
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            
            $objWriter->save('php://output');
            
            exit;
        }
    }
    
    
    /**
     * 
     * 导出上月空白用户 没消费过的
     * create time :20160612
     */
    
    public function export_blank_user(){
        
        ini_set("memory_limit", "512M");
        ini_set("max_execution_time", 0);
        
        $startTime = date('Y-m-01', strtotime('-1 month'));
        $endTime = date('Y-m-t 23:23:59', strtotime('-1 month'));
        $condition = "add_time >='$startTime' AND add_time <'$endTime'";
        $condition .= ' and real_name_auth = 0';//没有实名认证过
        
        //渠道  用户名字  手机号  年龄 性别  注册时间
        $list = M("User")->field('id,username,real_name,card_no,add_time,channel')->where($condition)->select();
        
        if(empty($list)) {
            return ;
        }
        
        $res = array();
        
        foreach ($list as $val){
                
            $val['sex'] = '';
            $val['age'] = '';
             
            if($val['card_no']) {
                $year = substr($val['card_no'], 6, 4);
                $nowYear = date('Y', time());
                $val['age'] = $nowYear - $year;
                $sex = substr($val['card_no'], strlen($val['card_no']) - 2, 1);
                if($sex % 2 != 0) {
                    $val['sex'] = '男';
                } else {
                    $val['sex'] = '女';
                }
            }
            $res[] = $val;
        }
        
        unset($list);
        
        if (!$res) exit('没有记录');
        
        $filename = date("Y-m")."注册的用户 没有交易".'-'.date('YmdHis');
    
        vendor('PHPExcel.PHPExcel');
    
        $objPHPExcel = new \PHPExcel();
    
        $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")
    
        ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
    
        $objPHPExcel->setActiveSheetIndex(0)->setTitle(date('Y-m', strtotime('-1 month'))."注册的用户 没有交易")
        ->setCellValue("A1", "编号")
        ->setCellValue("B1", "渠道")
        ->setCellValue("C1", "名字 ")
        ->setCellValue("D1", "手机号")
        ->setCellValue("E1", "年龄")
        ->setCellValue("F1", "性别")
        ->setCellValue("G1", "注册时间");
    
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    
        // 设置列表值
        $pos = 2;
        $n = 1;
        foreach ($res as $val) {
             
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
    
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['channel']);
    
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);
    
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['username']);
    
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['age']);
    
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['sex']);
    
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['add_time']);
    
            $pos += 1;
    
            $n++;
        }
    
        ob_end_clean();//清除缓冲区,避免乱码
    
    
        header("Content-Type: application/vnd.ms-excel");
    
        header('Content-Disposition: attachment;filename='.date('Y-m', strtotime('-1 month')).'注册的用户无交易记录.xls');
    
        header('Cache-Control: max-age=0');
    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    
        $objWriter->save('php://output');
    
        exit;
    }
    
    /**
     * 支付渠道统计
     * 2016/06/03
     */
    public function statistics_payment_channel() {
        /*
         * 
         * 纯数字，宝付，
            rb 融宝
         */
        if(IS_AJAX) {
            $start_time = I("post.st",'','strip_tags');
            $end_time = I("post.et",'','strip_tags');
            $channel = I("post.channel",0,'int');
            
            $cond = 'user_id > 0 ';
            
            if(!$start_time) {
                $this->ajaxReturn(array('status'=>0,'info'=>'开始时间不能为空'));
            }
            
            $cond .= " and modify_time >= '".$start_time." 00:00:00.000000'";
                
            if(!$end_time) {
                $this->ajaxReturn(array('status'=>0,'info'=>'结束时间不能为空'));
            }
            
            $cond .= " and modify_time < '".$end_time." 23:59:59.999000'";
            
            $ret = array();
                        
            $bf_amount = M('RechargeLog')->where($cond.' and status = 2 and type = 1')->sum('amount');
            $bf_api_amount = M('RechargeLog')->where($cond.' and status = 2 and type = 5')->sum('amount');
            $rb_amount = M('RechargeLog')->where($cond.' and status = 2 and type = 4')->sum('amount');
            $ll_amount = M('RechargeLog')->where($cond.' and status = 2 and type = 6')->sum('amount');
            
            if(!$rb_amount) $rb_amount = 0;
            if(!$bf_amount) $bf_amount = 0;
            if(!$bf_api_amount) $bf_api_amount = 0;
            if(!$ll_amount) $ll_amount = 0;
            
            
           
            $wallet_bf_amount = 0;
            $wallet_rb_amount = 0;
                        
            $wallet_ll_amount = 0;
            
            $wallet_bf_api_amount = 0;
            
            //$cond .= " and type=1 and pay_status=2 and (recharge_no !='' and recharge_no NOT LIKE 'JL%')";
            
            $cond .= " and type=1 and pay_status=2 and pay_type in(1,4,5,6)";
            
            $list = M('UserWalletRecords')->field('id,value,recharge_no,pay_type')->where($cond)->select();
            if($list) {
                foreach ($list as $val) {
                    
                    //支付方式 : 0:定期投资回款; 1: 宝付sdk; 3: 现金券转入; 4: 融宝; 5: 宝付API；6 连连支付
                    /*
                    if (is_numeric($val['recharge_no'])) {
                        $wallet_bf_amount += $val['value'];
                    } else {
                        $wallet_rb_amount += $val['value'];
                    }*/
                    
                    if($val['pay_type'] == 1){
                        $wallet_bf_amount += $val['value'];
                    } else if($val['pay_type'] == 4){
                        $wallet_rb_amount += $val['value'];
                    } else if($val['pay_type'] == 5){
                        $wallet_bf_api_amount += $val['value'];
                    } else if($val['pay_type'] == 6){
                        $wallet_ll_amount += $val['value'];
                    }
                }
            }
            
            $ret[] = array(
                'type' => 1,
                'channel_name' => '宝付支付',
                'amount' => $bf_amount,
                'wallet_amount' => $wallet_bf_amount,
                'total' => $bf_amount + $wallet_bf_amount,
                'start_time' =>$start_time,
                'end_time'=>$end_time
            );
            $ret[] = array(
                'type' => 4,
                'channel_name' => '融宝支付',
                'amount' => $rb_amount,
                'wallet_amount' => $wallet_rb_amount,
                'total' => $rb_amount + $wallet_rb_amount,
                'start_time' =>$start_time,
                'end_time'=>$end_time
            );
            $ret[] = array(
                'type' => 5,
                'channel_name' => '宝付APi支付',
                'amount' => $bf_api_amount,
                'wallet_amount' => $wallet_bf_api_amount,
                'total' => $bf_api_amount + $wallet_bf_api_amount,
                'start_time' =>$start_time,
                'end_time'=>$end_time
            );
            
            $ret[] = array(
                'type' => 6,
                'channel_name' => '连连支付',
                'amount' => $ll_amount,
                'wallet_amount' => $wallet_ll_amount,
                'total' => $ll_amount + $wallet_ll_amount ,
                'start_time' =>$start_time,
                'end_time'=>$end_time
            );
            
            $this->ajaxReturn(array('status'=>1,'info'=>$ret));
        } else {
            $this->display();
        }
    }
    
    /**
     * 支付渠道明细
     * 2016/06/03
     */
    public function statistics_payment_channel_detail() {
      
        if(!IS_POST){
            $paytype = I('get.paytype', 1, 'int'); //1宝付支付,2融宝支付
            $start_time = I("get.st",'','strip_tags');
            $end_time = I("get.et",'','strip_tags');
            $objtype = I("get.o",1,'strip_tags');//1购买产品，2钱包
        
            $cond = 'user_id > 0';
            $cond .= " and modify_time >= '".$start_time." 00:00:00.000000'";
            $cond .= " and modify_time < '".$end_time." 23:59:59.999000'";
            
            $ret = array();
            
            //1: 宝付sdk;  4: 融宝; 5: 宝付API；6 连连支付
            
            if($objtype == 1){
                $cond .= 'and status = 2 and type='.$paytype;
                $totalCnt =  M("rechargeLog")->where($cond)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $show = $Page->show();
                $list = M("RechargeLog")->field('id,amount,recharge_no,card_no,add_time,project_id')->where($cond)->order("modify_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    foreach ($list as $val) {
                        $val['info'] = M('UserBank')->field('bank_name,acct_name,mobile,bank_card_no')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->find();
                        $val['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
                        $val['project_name'] = M("Project")->where(array('id'=>$val['project_id']))->getField('title');
                        $ret[] = $val;
                    }
                }
            } else{                
                $cond .= ' and type=1 and pay_status=2 and pay_type='.$paytype;
                $cond = str_replace('modify_time', 'add_time', $cond);
                $totalCnt =  M("userWalletRecords")->where($cond)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $show = $Page->show();
                $list = M("userWalletRecords")->field('id,value,recharge_no,user_bank_id,add_time')
                                    ->where($cond)->order("add_time desc")
                                    ->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    foreach ($list as $val) {
                        $val['info'] = M('UserBank')->field('bank_name,acct_name,mobile,bank_card_no')->where(array('id'=>$val['user_bank_id'],'has_pay_success'=>2))->find();
                        $val['amount'] = $val['value'];
                        $val['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
                        $ret[] = $val;
                    }
                }
            }
            
            
            if($paytype == 1){
                $title = '宝付sdk';
            } else if($paytype == 4) {
                $title = '融宝';
            } else if($paytype == 5){
                $title = '宝付API';
            } else {
                $title = '连连支付';
            }

            $title .= $objtype == 1 ?'【购买产品】用户列表':'【冲钱包】用户列表';
            $title .=  '，交易日期'.$start_time .'至'.$end_time;
            
            $params = array(
                'paytype' => $paytype,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'o' => $objtype,
                'chnName' => $title,
                'totalCnt'=>$totalCnt
            );
            
            $this->assign('params', $params);
            $this->assign('list', $ret);
            $this->assign('page',$show);
            $this->display();
        }
    }
    
    /**
     * 支付渠道明细导出
     * 2016/06/03
     */
    public function statistics_payment_channel_export() {
        if(!IS_POST){
            $paytype = I('get.paytype', 1, 'int'); //1宝付支付,2融宝支付
            $start_time = I("get.st",'','strip_tags');
            $end_time = I("get.et",'','strip_tags');
            $objtype = I("get.o",1,'strip_tags');//1 购买产品，2钱包
        
            $cond = 'user_id > 0';
            $cond .= " and add_time >= '".$start_time." 00:00:00.000000'";
            $cond .= " and add_time < '".$end_time." 23:59:59.999000'";
        
            $ret = array();
            
            if($objtype == 1){
                $cond .= 'and status = 2 and type='.$paytype;
                $totalCnt =  M("rechargeLog")->where($cond)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $show = $Page->show();
                $list = M("RechargeLog")->field('id,amount,recharge_no,card_no,add_time,project_id')->where($cond)->order("modify_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    foreach ($list as $val) {
                        $val['info'] = M('UserBank')->field('bank_name,acct_name,mobile,bank_card_no')->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->find();
                        $val['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
                        $val['project_name'] = M("Project")->where(array('id'=>$val['project_id']))->getField('title');
                        $ret[] = $val;
                    }
                }
            } else{
                $cond .= ' and type=1 and pay_status=2 and pay_type='.$paytype;
                $cond = str_replace('modify_time', 'add_time', $cond);
                $totalCnt =  M("userWalletRecords")->where($cond)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $show = $Page->show();
                $list = M("userWalletRecords")->field('id,value,recharge_no,user_bank_id,add_time')
                ->where($cond)->order("add_time desc")
                ->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    foreach ($list as $val) {
                        $val['info'] = M('UserBank')->field('bank_name,acct_name,mobile,bank_card_no')->where(array('id'=>$val['user_bank_id'],'has_pay_success'=>2))->find();
                        $val['amount'] = $val['value'];
                        $val['add_time'] = date("Y-m-d H:i:s",strtotime($val['add_time']));
                        $ret[] = $val;
                    }
                }
            }
            
            if(!$ret) {
                exit('没记录');
            }

            vendor('PHPExcel.PHPExcel');
            
            $objPHPExcel = new \PHPExcel();
            
            
            $_title = $paytype==1 ? '宝付':'融宝' ;
            $_title .= $objtype == 1 ?'【购买产品】用户列表':'【冲钱包】用户列表';
            
            $title = $_title.  '，交易日期'.$start_time .'至'.$end_time;
            
            
            $objPHPExcel->getProperties()
                    ->setCreator("票票喵票据 - ")
                    ->setLastModifiedBy("票票喵票据")
                    ->setTitle($title)
                    ->setSubject("subject")
                    ->setDescription($title)
                    ->setKeywords("keywords")
                    ->setCategory("Category");
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle($_title)
                    ->setCellValue("A1", "序号")
                    ->setCellValue("B1", "账号")
                    ->setCellValue("C1", "姓名")
                    ->setCellValue("D1", "交易时间")
                    ->setCellValue("E1", "银行卡号")
                    ->setCellValue("F1", "银行名称")
                    ->setCellValue("G1", "金额")
                    ->setCellValue("H1", "项目期数");
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            
            // 设置列表值
            $pos = 2;
            $n = 1;
            foreach ($ret as $key => $val) {
            
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['info']['mobile']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['info']['acct_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['add_time']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''.$val['info']['bank_card_no'],\PHPExcel_Cell_DataType::TYPE_STRING);
            
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['info']['bank_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['amount'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['project_name']);
            
                $pos += 1;
            }
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename='.$title.'.xls');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
            
        }
    }
    
    /*
     * 销售额(支付渠道)
     * 2016/06/07
     */
    public function sales_channel(){
        
        if(IS_AJAX) {
            $start_time = I("post.st",'','strip_tags');
            $end_time = I("post.et",'','strip_tags');
            
            $cond = 'user_id > 0 ';
        
            if(!$start_time) {
                $this->ajaxReturn(array('status'=>0,'info'=>'开始时间不能为空'));
            }
        
            $cond .= " and add_time >= '$start_time'";
        
            if(!$end_time) {
                $this->ajaxReturn(array('status'=>0,'info'=>'结束时间不能为空'));
            }
        
            $cond .= " and add_time <= '".$end_time." 23:59:59.999000'";
            
            $ret = array();
            
            //钱包相关
            $wallet_list = M('UserWalletRecords')->field('recharge_no,value,pay_type')->where($cond .'and pay_status = 2')->select();
            
            $wallet['project_name'] = '零钱包';
            $wallet['rb_amount'] = 0;
            $wallet['bf_amount'] = 0;
            $wallet['pp_amount'] = 0;
            
            $wallet['ll_amount'] = 0;//连连
            
            //$wallet['cash_amount'] = 0;//现金
            
            //支付方式 : 0:定期投资回款; 1: 宝付sdk; 3: 现金券转入; 4: 融宝; 5: 宝付API；6 连连支付
            
            if($wallet_list) {
                foreach ($wallet_list as $val) {
                    
                    if($val['pay_type'] == ''){
                        $wallet['pp_amount'] +=$val['value'];
                    } else if ($val['pay_type'] == 1 || $val['pay_type'] == 5){
                        $wallet['bf_amount'] +=$val['value'];
                    } else if($val['pay_type'] == 4 ){
                        $wallet['rb_amount'] +=$val['value'];
                    } else if($val['pay_type'] == 6){
                        $wallet['ll_amount'] +=$val['value'];
                    }// else if($val['pay_type'] == 3){
                     //   $wallet['cash_amount'] +=$val['value'];
                    //}
                }
            }
            
            $wallet['total_amount'] = $wallet['rb_amount'] + $wallet['bf_amount'] + $wallet['pp_amount'] + $wallet['ll_amount'];// + $wallet['cash_amount'];
        
            $ret[] = $wallet;
            
            $due_list = M('UserDueDetail')->field('id,project_id')->where($cond)->group('project_id')->select();
            
            foreach ($due_list as $val) {
                
                $_cond = 'status = 2 and project_id ='.$val['project_id'] .' and '.$cond;
                
                $_cond = str_replace('add_time', 'modify_time', $_cond);
                
                $val['project_name'] = M('Project')->where(array('id'=>$val['project_id'],'is_delete'=>0))->getField('title');
                
                if($val['project_name'] == null){
                    $val['project_name'] = '删除的标';
                }
                
                $val['rb_amount'] = M('RechargeLog')->where($_cond ." and type = 4")->sum('amount');
                $val['bf_amount'] = M('RechargeLog')->where($_cond ." and type in(1,5)")->sum('amount');
                $val['pp_amount'] = M('RechargeLog')->where($_cond ." and type = 3")->sum('amount');
                $val['ll_amount'] = M('RechargeLog')->where($_cond ." and type = 6")->sum('amount');
                
                
                if(!$val['rb_amount']) $val['rb_amount'] = 0;
                if(!$val['bf_amount']) $val['bf_amount'] = 0;
                if(!$val['pp_amount']) $val['pp_amount'] = 0;
                if(!$val['ll_amount']) $val['ll_amount'] = 0;
                
                $val['total_amount'] = $val['rb_amount'] + $val['bf_amount'] + $val['pp_amount'] + $val['ll_amount'];
                $ret[] = $val;
                
            }
            
            $this->ajaxReturn(array('status'=>1,'info'=>$ret));
            
        } else {
            $this->display();
        }
    }
    
    /*
     * 存量资金
     */
    public function statistics_scalar_money(){
        
        //渠道列表
        $channelList = S('channelList');
        
        if(!$channelList){
            $channelPid = M('Constant')->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
            $channelList = M('Constant')->where(array('parent_id'=>$channelPid))->select();
            S('channelList', $channelList,1800);
        }
        
        $this->assign('channel_list', $channelList);
        
        $channel = I("get.channel",0,'int');
        
        $datetime = I('get.dt', date('Y-m-d', time()), 'strip_tags');
        $cacheData = S('statistics_scalar_money1'.$datetime.'-'.$channel);
        
        if(!$cacheData){
            $date = get_the_month($datetime);
            $maxDay = date('d', strtotime($date[1])); // 某个最大号数
            
            $_y = date("Y",strtotime($datetime));
            $_m = date("m",strtotime($datetime));
            
            $categories = "";
            $totleMoney = ""; 
            
            $_stime = strtotime($date[0]);
            $_etime = strtotime($date[1]);
            
            $days = 0;
            $totalMoney = 0;
            
            
            for($i = 1; $i <= $maxDay; $i++){
               $_d =  $_y.'-'.$_m.'-'.$i;
               $_money =  M('StatisticsNetprofit')->where("stat_date = '$_d' and channel_id=".$channel)->getField('money');
               
               if(!$_money) {
                   $_money = 0;
               } else {
                   $totalMoney += $_money;
                   $days ++;
               }
               
               $categories .= ",'" . $i. "日'";
               $totleMoney .= ",".$_money;
            }
            
            $avgMoney = $totalMoney/$days;
            
            if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
            
            if($totleMoney) $totleMoney = substr($totleMoney, 1);
            
            $cacheData = array(
                'categories' => $categories,
                'totleMoney' => $totleMoney,
                'avgMoney'=> number_format($avgMoney, 2, '.', ''),
            );
            
            S('statistics_scalar_money1'.$datetime.'-'.$channel, $cacheData,1800);
        }
        
        $this->assign('channel', $channel);
        $this->assign('categories', $cacheData['categories']);
        $this->assign('totlemoney', $cacheData['totleMoney']);
        $this->assign('avgMoney', $cacheData['avgMoney']);
        $this->assign('dt', date("Y-m",strtotime($datetime)));
        $this->display();
        
    }
    
    
    /*
     * 平台总销售额
     */
    public function statistics_platform_sales(){
    
        $datetime = I('get.dt', date('Y-m-d', time()), 'strip_tags');
        $cacheData = S('statistics_platform_sales'.$datetime);
    
        if(!$cacheData){
            $date = get_the_month($datetime);
            $maxDay = date('d', strtotime($date[1])); // 某个最大号数
    
            $_y = date("Y",strtotime($datetime));
            $_m = date("m",strtotime($datetime));
    
            $categories = "";
            $totleMoney = "";
    
            $_stime = strtotime($date[0]);
            $_etime = strtotime($date[1]);
    
            $days = 0;
            $totalMoney = 0;
    
            for ($i = 1; $i <= $maxDay; $i ++) {
                $_d = $_y . '-' . $_m . '-' . $i;
                
                $_money = M('StatisticsPlatformSaleDaily')->where("stat_date = '$_d'")->getField('amount');
                
                if (! $_money) {
                    $_money = 0;
                } else {
                    $totalMoney += $_money;
                    $days ++;
                }
                
                $categories .= ",'" . $i . "日'";
                $totleMoney .= "," . $_money;
            }
    
            $avgMoney = $totalMoney/$days;
    
            if($categories) $categories = mb_substr($categories, 1, mb_strlen($categories) - 1, 'utf-8');
    
            if($totleMoney) $totleMoney = substr($totleMoney, 1);
    
            $cacheData = array(
                'categories' => $categories,
                'totleMoney' => $totleMoney,
                'avgMoney'=> number_format($avgMoney, 2, '.', ''),
            );
    
            S('statistics_platform_sales'.$datetime, $cacheData,1800);
        }
        $this->assign('categories', $cacheData['categories']);
        $this->assign('totlemoney', $cacheData['totleMoney']);
        $this->assign('avgMoney', $cacheData['avgMoney']);
        $this->assign('dt', date("Y-m",strtotime($datetime)));
        $this->display();
    }
    
    /**
     * 还款列表
     */
    public function repayment_list(){
        if (!IS_POST) {
            $search = urldecode(I('get.s', '', 'strip_tags'));             
            $rtime = I('get.rtime','','strip_tags');
            
            $cond[] = 'status = 5 and is_delete = 0';
            if($search) {
                $cond[] = "title like '%" . $search . "%'";
            }
            
            if($rtime) {
                $end_time = $rtime.'23:59:59';
                $cond[] = "repayment_time >= '$rtime' and repayment_time <'$end_time'";
            }
            
            $cond = implode(' and ', $cond);
            $counts = M('Project')->where($cond)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('Project')->where('id,title,repayment_time')->where($cond)->order('repayment_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            foreach ($list as $key => $val){
                $_cond = 'user_id > 0 and project_id='.$val['id'];
                
                $list[$key]['fund_due_capital'] = M('userDueDetail')->where($_cond .' and to_wallet=2 and from_wallet=2')->sum('due_capital');
                $list[$key]['fund_due_interest'] = M('userDueDetail')->where($_cond .' and to_wallet=2 and from_wallet=2')->sum('due_interest');

                $list[$key]['to_bank_due_capital'] = M('userDueDetail')->where($_cond .' and to_wallet=0 and from_wallet=0')->sum('due_capital');
                $list[$key]['to_bank_due_interest'] = M('userDueDetail')->where($_cond .' and to_wallet=0 and from_wallet=0')->sum('due_interest');

                $list[$key]['to_wallet_due_capital'] = M('userDueDetail')->where($_cond .' and (to_wallet=1 or from_wallet=1)')->sum('due_capital');
                $list[$key]['to_wallet_due_interest'] = M('userDueDetail')->where($_cond.' and (to_wallet=1 or from_wallet=1)')->sum('due_interest');
                            
                $list[$key]['rid'] = M('repaymentDetail')->where('project_id='.$val['id'])->getField('id');
            } 
            
            $this->assign('params', array('key'=>$search,'rtime'=>$rtime));
            $this->assign('list',$list);
            $this->assign('show',$show);
            $this->display();
        } else {
            $key = I('post.key', '', 'strip_tags');
            $rtime = I('post.rtime', '', 'strip_tags');
            
            $quest = '';
            
            if($key) $quest .= '/s/'.urlencode($key);
            if($rtime) $quest .= '/rtime/'.$rtime;
            
            redirect(C('ADMIN_ROOT') . '/statistics/repayment_list'.$quest);
        }
    }
    
    
    /**
     * 融资方待还款列表
     */
    public function wait_repayment_list(){
        if (!IS_POST) {
            $fid = urldecode(I('fid', '', 'strip_tags'));
            $rtime = I('rtime','','strip_tags');
            
            
            $this->assign('financing',M('financing')->field('id,name')->select());
            
            $cond[] = 'status = 3 and is_delete = 0';
            
            if($fid>0){
               $cond[] = 'fid ='.$fid;
            }
            if($rtime) {
                $end_time = $rtime.' 23:59:59';
                $cond[] = "end_time >= '$rtime' and end_time <'$end_time'";
            }
    
            $cond = implode(' and ', $cond);
            $counts = M('Project')->where($cond)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = M('Project')->field('id,title,repayment_time,fid,end_time')->where($cond)->order('end_time asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            
            $total_due_capital = 0;
            $total_due_interest = 0;
            $total_due_amount = 0;
            
            foreach ($list as $key => $val){
                
                $_cond = 'user_id > 0 and project_id='.$val['id'];
    
                $list[$key]['due_capital'] = M('userDueDetail')->where($_cond)->sum('due_capital');
                $list[$key]['due_interest'] = M('userDueDetail')->where($_cond)->sum('due_interest');
                $list[$key]['due_amount'] = M('userDueDetail')->where($_cond)->sum('due_amount');
                $list[$key]['f_name'] = M('Financing')->where('id='.$val['fid'])->getField('name');
                $list[$key]['rid'] = M('repaymentDetail')->where('project_id='.$val['id'])->getField('id');
                
                $total_due_capital += $list[$key]['due_capital'];
                $total_due_interest += $list[$key]['due_interest'];
                $total_due_amount += $list[$key]['due_amount'];
            }
            $this->assign('params', array('fid'=>$fid,'rtime'=>$rtime,'total_due_capital'=>$total_due_capital,'total_due_interest'=>$total_due_interest,'total_due_amount'=>$total_due_amount));
            $this->assign('list',$list);
            $this->assign('show',$show);
            $this->display();
            
        } else {
            $fid = I('post.financing', '', 'strip_tags');
            $rtime = I('post.rtime', '', 'strip_tags');
            $quest = '';
    
            if($fid) $quest .= '/fid/'.urlencode($fid);
            if($rtime) $quest .= '/rtime/'.$rtime;
            redirect(C('ADMIN_ROOT') . '/statistics/wait_repayment_list'.$quest);
        }
    }
    
    
    /**
     * 导出 还款列表 to excel
     */
    public function repayment_list_export_to_excel(){
        
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        
        $search = urldecode(I('get.s', '', 'strip_tags')); 
        $cond[] = 'status = 5 and is_delete = 0';
        if($search) {
            $cond[] = "title like '%" . $search . "%'";
        }
        $cond = implode(' and ', $cond);
        $counts = M('Project')->where($cond)->count();
        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = M('Project')->where('id,title,repayment_time')->where($cond)->order('id desc')->select();
        foreach ($list as $key => $val){
            $_cond = 'user_id > 0 and project_id='.$val['id'];

            $list[$key]['fund_due_capital'] = M('userDueDetail')->where($_cond .' and to_wallet=2 and from_wallet=2')->sum('due_capital');
            $list[$key]['fund_due_interest'] = M('userDueDetail')->where($_cond .' and to_wallet=2 and from_wallet=2')->sum('due_interest');
            $list[$key]['to_bank_due_capital'] = M('userDueDetail')->where($_cond .' and from_wallet = 0')->sum('due_capital');
            $list[$key]['to_bank_due_interest'] = M('userDueDetail')->where($_cond .' and from_wallet = 0')->sum('due_interest');
            $list[$key]['to_wallet_due_capital'] = M('userDueDetail')->where($_cond .' and from_wallet = 1')->sum('due_capital');
            $list[$key]['to_wallet_due_interest'] = M('userDueDetail')->where($_cond .' and from_wallet = 1')->sum('due_interest');
            $list[$key]['repayment_time'] = date("Y-m-d",strtotime($val['repayment_time']));
        } 
        
        if(!$list) {
            exit('没有数据');
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
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('还款记录列表')
                    ->setCellValue("A1", "期数")
                    ->setCellValue("B1", "账户本金")
                    ->setCellValue("C1", "账户利息")
                    ->setCellValue("D1", "银行卡本金")
                    ->setCellValue("E1", "银行卡利息")
                    ->setCellValue("F1", "钱包本金")
                    ->setCellValue("G1", "钱包利息")
                    ->setCellValue("H1", "还款日期");
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['title']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['fund_due_capital']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['fund_due_interest']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['to_bank_due_capital']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['to_bank_due_interest']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['to_wallet_due_capital']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['to_wallet_due_interest']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['repayment_time']);
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="还款记录列表( - '.date('Y-m-d').').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 导出还款的明细
     */
    public function repayment_project_export_to_excel(){
        
        
        ini_set("memory_limit", "1000M");
        ini_set("max_execution_time", 0);
        
        $id = I('id',0,'int');
        if(!$id)exit('非法访问');
        
        $list = M('userDueDetail')->field('user_id,due_amount,due_capital,due_interest,add_time,from_wallet,card_no')
                                    ->where('user_id > 0 and project_id='.$id)->select();
        foreach ($list as $key=>$val){
            $user = M('User')->field('username,real_name')->where('id='.$val['user_id'])->find();
            $list[$key]['real_name'] = $user['real_name'];
            $list[$key]['user_name'] = $user['username'];
            $list[$key]['add_time'] = date('Y-m-d H:i:s',strtotime($val['add_time']));
            
            if($val['from_wallet'] == 1){
                $list[$key]['buy_type'] = '钱包';
                $list[$key]['bank_name'] = '';
            } else {
                $list[$key]['buy_type'] = '银行卡';
                $list[$key]['bank_name'] = M('userBank')->where("user_id=".$val['user_id']." and bank_card_no='".$val['card_no']."'")->getField('bank_name');
            }
        }
        
        if(!$list) {
            exit('没有数据');
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
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('买购记录')
                    ->setCellValue("A1", "用户名")
                    ->setCellValue("B1", "姓名")
                    ->setCellValue("C1", "购买时间")
                    ->setCellValue("D1", "购买本金")
                    ->setCellValue("E1", "支付利息")
                    ->setCellValue("F1", "购买方式")
                    ->setCellValue("G1", "银行卡号")
                    ->setCellValue("H1", "银行名称");

        
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['user_name']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['real_name']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['add_time']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['due_capital']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['due_interest']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['buy_type']);
            
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['card_no']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['bank_name']);
            $pos += 1;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="购买明细( - '.date('Y-m-d').').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
    * 存量统计列表
    * @date: 2017-2-6 下午2:12:10
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function inventory_list(){
    
        $stat_date = I('get.dt', '','strip_tags');
        $channel = I('get.channel', '', 'strip_tags');
        $channel = urldecode($channel);

        $cond = null;
        if ($stat_date) {
            $cond = "stat_date='$stat_date'";  
        }

        if ($cond && $channel) {
            $cond .= " and s_constant.cons_value like '%$channel%'";
        } elseif(!$cond && $channel) {
            $cond .= "s_constant.cons_value like '%$channel%'";
        }

        $counts = M('statisticsNetprofit')
                ->join('s_constant ON s_constant.id = s_statistics_netprofit.channel_id', 'LEFT')
                ->where($cond)->count();

        $Page = new \Think\Page($counts, $this->pageSize);
        $show = $Page->show();
        $list = M('statisticsNetprofit')
                ->field('s_statistics_netprofit.id, s_statistics_netprofit.money,s_statistics_netprofit.add_time, s_statistics_netprofit.channel_id, s_statistics_netprofit.stat_date')

                ->join('s_constant ON s_constant.id = s_statistics_netprofit.channel_id', 'LEFT')
                ->where($cond)->order('s_statistics_netprofit.id desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)->select();
    
        foreach($list as $key => $val){
            $list[$key]['channel_name'] = M('constant')->where('id='.$val['channel_id'])->getField('cons_value');
            if($list[$key]['channel_name'] == '') {
                $list[$key]['channel_name'] = '所有渠道累加';
            }
        }
        $this->assign('list', $list);
        $this->assign('show', $show);
        $this->assign('dt', $stat_date);
        $this->assign('channel', $channel);
        $this->display();
    }
    
    /**
    * 自定义渠道存量查询
    * @date: 2017-2-9 下午4:10:15
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function custom_channel_query(){        
        if(!IS_AJAX){
            $list = M('Constant')->field('id,cons_value')->where(array('parent_id'=>4))->select();
            $this->assign('list', $list);
            $this->assign('end_time',date('Y-m-d'));
            $this->display();
        } else {
            
            $start_time = I('post.st', '', 'strip_tags');
            $end_time = I('post.et', '', 'strip_tags');
            $channel = I('post.channel', '', 'strip_tags');
            $input_channel = I('post.input_channel', '', 'strip_tags');

            // 新增输入渠道名模糊查询
            $input_channel_ids = array();
            if (!empty($input_channel)) {
                $input_channel_ids = M('Constant')->where("cons_value like '%$input_channel%'")->getField('id', true);
            }

            $channel_list =  explode(',',$channel);
            
            // 处理数据
            foreach ($input_channel_ids as $value) {
                if (!in_array($value, $channel_list)) {
                    $channel_list[] = $value;
                }
            }

            if(!$channel_list) {
                $this->ajaxReturn(array('status'=>1,'info'=>'请选择渠道'));
            }
            
            $amt = 0;
            $users = '';
            foreach ($channel_list as $val) {
                if ($val == null || $val == '') {
                    continue;
                }
                
                $user_sql = "select id from s_user where channel_id = $val and real_name_auth=1 and add_time>='$start_time' and add_time<'$end_time'";
                $channel_name = M('Constant')->where('id='.$val)->getField('cons_key');
                $user_channel_sql = "select user_id as id from s_user_channel where add_time>='$start_time' and add_time<'$end_time' and channel like '$channel_name%'";
                $query_sql = $user_sql .' union ' .$user_channel_sql;
                $user_list = M()->query($query_sql);
                foreach ($user_list as $v){
                    $users .= ','.$v['id'];
                }
            }
            $users = ltrim($users,',');
            if(!$users){
                $this->ajaxReturn(array('status'=>1,'info'=>'没有查找数据'));
            }
            $amt = M('userDueDetail')->where("status=1 and user_id in($users)")->sum('due_capital');
            
            !$amt && $amt = 0;
            
            $this->ajaxReturn(array('status'=>1,'info'=>$amt));
        }
    }
    
    /**
     * 导出账户
     */
    public function exporttoexcel(){
        vendor('PHPExcel.PHPExcel');
        $id = I('get.id', 0, 'int'); // 项目ID
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

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
        $num = preg_replace('/\D/s', '', $detail['title']);
        $time = strtotime($repayDetail['repayment_time']);

        $file_name =date('md').'ppm'.$num.'账户'.date('md',$time).'到期';
        $list = $userDueDetailObj->where("project_id=".$id." and repay_id=".$repay_id." and user_id>0 and to_wallet=2 and from_wallet=2")->order('add_time desc')->select();


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
            ->setCellValue("B1", "*金额")
            ->setCellValue("C1", "*本金")
            ->setCellValue("D1", "*利息")
            ->setCellValue("E1", "*红包");

        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(24);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(24);
        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            $totle_interest += $val['due_interest'];

            $bankInfo = $userBankObj->field('acct_name,bank_code,bank_name')->where("bank_card_no='".$val['card_no']."' and bank_name<>'' and has_pay_success=2")->find();

            if($bankInfo['bank_name'] == '邮政储蓄') {
                $bankInfo['bank_name'] = '中国邮政储蓄银行';
            }

            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $bankInfo['acct_name']); // 收款方开户姓名
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,($val['due_capital']+$val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,($val['due_capital']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,($val['due_interest']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,($val['red_amount']),\PHPExcel_Cell_DataType::TYPE_NUMERIC); // 金额

            $pos += 1;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /*
     *  渠道统计 导出excel
     */
    public function channel_statistics_excel(){
        vendor('PHPExcel.PHPExcel');
        if(!IS_POST){
         //   $page = I('get.p', 1, 'int');
            $chn = I('get.chn', 0, 'int');
            $start_time = I('get.st');
            $end_time = I('get.et');
            $cons_type = I('get.cons_type',1,'int');
            $count = 10;
            $doSearch = I('get.dosearch', 0, 'int'); // 是否执行搜索操作

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');

            if(!$channelList){
                $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0,'cons_type'=>$cons_type))->getField('id');
                $channelList = $constantObj->where(array('parent_id'=>$channelPid))->order('id desc')->select();
                F('channel_list', $channelList);
            }

            $channelArr = array();
            foreach ($channelList as $c){
                $channelArr[$c['id']] = $c['cons_value'];
            }

            $this->assign('cons_type', $cons_type);
            $this->assign('channel_list', $channelList);


            $condWalle = array();

            if($doSearch){
                $userObj = M('User');
                $conditions = '';
                $cond[] = array();
                $rechargeLogObj = M('RechargeLog');
                $userAccount = M("UserAccount");
                if(!$chn){ // 全部渠道
                        unset($cond);
                        // $cond[] = "user_id in (select id from s_user where channel_id=".$channelList[$i]['id'].")";
                        $cond[] = "user_id>0";
                        $cond[] = "status=2";
                        if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
                        if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
                        $conditions = implode(' and ', $cond);
                        $userPayList = $rechargeLogObj->field('substr(add_time,1,10) as group_add_time,s_recharge_log.*')->where($conditions)->order('add_time')->select();

                        foreach ($userPayList as $u) {
                            $channelUserArr[] = $u['user_id'];
                        }

                        $channelUserArr = array_unique($channelUserArr);

                        $whereMap = array(
                            'id' => array('in', $channelUserArr)
                        );

                        $userChannelArr = $userObj->field('channel_id,id')->where($whereMap)->select();

                        $channelUserConsArr = array();
                        foreach ($userChannelArr as $c) {
                            $channelUserConsArr[$c['id']] = $c['channel_id'];
                        }


                        //按每个渠道每天 分组
                        $groupArr = array();

                        foreach ($userPayList as  $group){
                            $groupAddTime = $group['group_add_time'];

                            $groupUserId = $group['user_id'];

                            $chnId = $channelUserConsArr[$groupUserId];

                            if(!empty($groupAddTime) && !empty($chnId) ) {
                                $group['cons_value'] = $channelArr[$chnId];
                                $group['channel_id'] = $chnId;
                                $groupArr[$groupAddTime][$chnId][] = $group;
                            }
                        }


                        // 所有 日期加渠道 倒数 数据
                        $userPayListRow = array();
                        foreach ($groupArr as $g){
                            foreach ($g as $k=>$u){
                                $u['channel_id'] = $k;
                                $u['cons_value'] = $u[0]['cons_value'];
                                if(isset($u[0]['group_add_time'])){
                                    $u['group_add_time'] = $u[0]['group_add_time'];
                                }else{
                                    $u['group_add_time'] = '0000-00-00';
                                }
                                $userPayListRow[] = $u;
                            }
                        }

                        $counts = count($userPayListRow);
                        $Page = new \Think\Page($counts, $count);
                        $show = $Page->show();

                        $min = 0;
                        $max = $counts;
                       // if($max > $counts) $max = $counts;
                        $list = array();
                        $pos = 0;

                        for($i = $min; $i < $max; $i++){
                            unset($cond,$condWalle);
                            // $list[$pos] = $userPayListRow[$i]; // 渠道信息

                            $list[$pos]['cons_value'] = $userPayListRow[$i]['cons_value'];

                            $now_time = $userPayListRow[$i]['group_add_time'];
                            $list[$pos]['add_time'] = $now_time;
                            $chnId = $userPayListRow[$i]['channel_id'];

                            $userPayList = $userPayListRow[$i];//$rechargeLogObj->where($conditions)->order('add_time')->select();

                            // 还款

                            $condWalle[] = "user_id in (select id from s_user where channel_id=".$chnId.")";
                            $condWalle[] = "user_id>0";
                            $condWalle[] = "status_new=2"; //待收状态  已还款

                            if($start_time) $condWalle[] = "add_time>='".$now_time." 00:00:00.000000'";
                            if($end_time) $condWalle[] = "add_time<='".$now_time." 23:59:59.999000'";

                            $conditionsWalle = implode(' and ', $condWalle);

                            $userDueDetailArr = M('userDueDetail')->field('due_amount ,user_id')->where($conditionsWalle)->order('add_time')->select();

                            $totalRepaymentList = array();
                            $totalRepaymentUser = $totalRepaymentUserPaySum = 0;
                            foreach ($userDueDetailArr as $due) {
                                $dueAmount = isset($due['due_amount']) ? trim($due['due_amount']) : 0;
                                if (!in_array($due['user_id'], $totalRepaymentList)) {
                                    $totalRepaymentUser += 1;//还款总用户
                                }
                                array_push($totalRepaymentList, $due['user_id']);
                                $totalRepaymentUserPaySum += $dueAmount; //还款总金额
                            }
                            // 还款 end

                            $firstUserList = array(); // 第一次付费用户数组
                            $nextUserList = array(); // 第二次付费用户数组
                            $firstPayPersonSum = 0; // 第一次充值人数
                            $nextPayPersonSum = 0; // 续费用户充值人数
                            $firstPaySum = 0; // 第一次付费用户充值数
                            $nextPaySum = 0; // 续费用户充值数
                            $walletSum = 0; // 钱包余额
                            $userIds = ''; // 用户id组合列表

                            $secondUserList = array(); // 第二次付费用户数组
                            $secondPayPersonSum = 0; // 第二次充值人数
                            $secondPaySum = 0; // 第二次用户充值数

                            $arrayCount = array(); //所有user_id 的统计
                            $totalPayPersonSum = 0;// 所有充值人数
                            $totalPaySum = 0;// 所有用户充值数

                            unset($userPayList['channel_id'],$userPayList['cons_value'],$userPayList['group_add_time']);

                            foreach($userPayList as $key => $val){
                                $userIds .= ','.$val['user_id'];
                                //取红包金额
                                $bagAmount = M('UserRedenvelope')->where(array('recharge_no'=>$val['recharge_no'],'user_id'=>$val['user_id'],'status'=>1))->getField('amount');

                                if(!in_array($val['user_id'], $firstUserList)){ // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);
                                    if(!$rechargeLogObj->where('user_id='.$val['user_id'].' and status=2 and id<'.$val['id'])->getField('id')) { // 检查这个时间点外该用户是否有购买(没有)
                                        $firstPayPersonSum += 1;
                                        $firstPaySum += $val['amount'];
                                        if ($bagAmount > 0) {
                                            $firstPaySum += $bagAmount;
                                        }
                                    }
                                    else{
                                        array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPaySum += $val['amount'];
                                        if ($bagAmount > 0) {
                                            $nextPaySum += $bagAmount;
                                        }
                                    }
                                }else if($arrayCount[$val['user_id']]==1){
                                    array_push($firstUserList, $val['user_id']);
                                    $secondPayPersonSum += 1;
                                    $secondPaySum += $val['amount'];
                                    if ($bagAmount > 0) {
                                        $secondPaySum += $bagAmount;
                                    }
                                }else{ // 不是第一次购买用户ID
                                    if(!in_array($val['user_id'], $nextUserList)){
                                        array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPaySum += $val['amount'];
                                    }else{
                                        $nextPaySum += $val['amount'];
                                    }
                                    if ($bagAmount > 0) {
                                        $nextPaySum += $bagAmount;
                                    }
                                }

                                $arrayTotal = array_push($totalUserList, $val['user_id']);//所有 userId
                                $totalPayPersonSum += 1;
                                $totalPaySum += $val['amount'];
                                if($bagAmount > 0 ) {
                                    $totalPaySum += $bagAmount;
                                }

                                $arrayCount = array_count_values ($arrayTotal);

                            }

                            if($userIds) {
                                $userIds = substr($userIds, 1);
                                $walletSum = $userAccount->where("user_id in (".$userIds.")")->sum('wallet_totle');
                            }

                            $list[$pos]['start_time'] = $start_time ;
                            $list[$pos]['end_time'] = $end_time ;

                            $list[$pos]['first_pay_person_sum'] = $firstPayPersonSum;
                            $list[$pos]['first_pay_sum'] = $firstPaySum;

                            $list[$pos]['second_pay_person_sum'] = $secondPayPersonSum;
                            $list[$pos]['second_pay_sum'] = $secondPaySum;

                            $list[$pos]['next_pay_person_sum'] = $nextPayPersonSum;
                            $list[$pos]['next_pay_sum'] = $nextPaySum;

                            $list[$pos]['total_pay_count_sum'] = count($userPayList);//总投资数
                            $list[$pos]['total_pay_person_sum'] = $totalPayPersonSum; // 总投用户数
                            $list[$pos]['total_pay_sum'] = $totalPaySum; // 累计投资总额

                            $list[$pos]['total_payment_user'] = $totalRepaymentUser;// 还款用户数
                            $list[$pos]['total_payment_user_pay_sum'] = $totalRepaymentUserPaySum;// 还款总额

                            $list[$pos]['next_pay_person_sum'] = $nextPayPersonSum;
                            $list[$pos]['next_pay_sum'] = $nextPaySum;
                            $list[$pos]['wallet_sum'] = $walletSum;

                            $userWhere[] = 'channel_id = '.$chnId;// array('channel_id'=>$chnId,);
                            if($start_time) $userWhere[] = "add_time>='".$now_time." 00:00:00.000000'";
                            if($end_time) $userWhere[] = "add_time<='".$now_time." 23:59:59.999000'";

                            $userConditions = implode(' and ', $userWhere);

                            $list[$pos]['user_count'] = $userObj->where($userConditions)->count();
                            $list[$pos]['user_download'] = $this->download($chnId,$now_time); // 下载量

//                        $list[$pos]['user_count'] = $userObj->where(array('channel_id'=>$channelList[$i]['id']))->count();
                            $pos += 1;
                    }
                }else{ // 选择某个渠道后
                    unset($cond);
                    $list = array();
                    $channelOnes = array();
                    foreach ($channelList as $key => $val){
                        if($val['id'] == $chn){
                            $channelOnes = $val;
                        }
                    }

                    $now_time = $end_time;
                    $pos = 0;

                    $cond[] = "user_id in (select id from s_user where channel_id=" . $chn . ")";
                    $cond[] = "user_id>0";
                    $cond[] = "status=2";
                    if ($start_time) $cond[] = "add_time>='" . $start_time . " 00:00:00.000000'";
                    if ($end_time) $cond[] = "add_time<='" . $end_time . " 23:59:59.999000'";
                    $conditions = implode(' and ', $cond);
                    //$list[0] = $val;
                    $groupUserPayList = $rechargeLogObj->field('substr(add_time,1,10) as group_add_time,s_recharge_log.*')->where($conditions)->order('add_time')->select();

                    $groupUserList = array();
                    foreach ($groupUserPayList as $g){
                        $groupUserList[$g['group_add_time']][] = $g;
                    }

                    while (true) {
                        unset($userPayList);
                        $list[$pos]['cons_value'] = $channelOnes['cons_value'];
                        $list[$pos]['add_time']  = $now_time;

                        $userPayList = isset($groupUserList[$now_time])?$groupUserList[$now_time]:null;


                        // 还款
                        $condWalle[] = "user_id in (select id from s_user where channel_id=" . $chn . ")";
                        $condWalle[] = "user_id>0";
                        $condWalle[] = "status_new=2"; //待收状态  已还款

                        if ($start_time) $condWalle[] = "add_time>='" . $now_time . " 00:00:00.000000'";
                        if ($end_time) $condWalle[] = "add_time<='" . $now_time . " 23:59:59.999000'";

                        $conditionsWalle = implode(' and ', $condWalle);

                        $userDueDetailArr = M('userDueDetail')->field('due_amount ,user_id')->where($conditionsWalle)->order('add_time')->select();

                        $totalRepaymentList = array();
                        $totalRepaymentUser = $totalRepaymentUserPaySum = 0;
                        foreach ($userDueDetailArr as $due) {
                            $dueAmount = isset($due['due_amount']) ? trim($due['due_amount']) : 0;
                            if (!in_array($due['user_id'], $totalRepaymentList)) {
                                $totalRepaymentUser += 1;//还款总用户
                            }
                            array_push($totalRepaymentList, $due['user_id']);
                            $totalRepaymentUserPaySum += $dueAmount; //还款总金额
                        }
                        // 还款 end

                        $firstUserList = array(); // 第一次付费用户数组
                        $nextUserList = array(); // 续费用户数组
                        $firstPayPersonSum = 0; // 第一次充值人数
                        $nextPayPersonSum = 0; // 续费用户充值人数
                        $firstPaySum = 0; // 第一次付费用户充值数
                        $nextPaySum = 0; // 续费用户充值数
                        $walletSum = 0; // 钱包余额

                        $secondUserList = array(); // 第二次付费用户数组
                        $secondPayPersonSum = 0; // 第二次充值人数
                        $secondPaySum = 0; // 第二次用户充值数

                        $arrayCount = array(); //所有user_id 的统计
                        $totalPayPersonSum = 0;// 所有充值人数
                        $totalPaySum = 0;// 所有用户充值数

                        $userIds = '';
                        foreach ($userPayList as $key => $val) {
                            $userIds .= ',' . $val['user_id'];

                            //取红包金额
                            $bagAmount = M('UserRedenvelope')->where(array('recharge_no' => $val['recharge_no'], 'user_id' => $val['user_id'], 'status' => 1))->getField('amount');

                            if (!in_array($val['user_id'], $firstUserList)) { // 是第一次购买用户ID
                                array_push($firstUserList, $val['user_id']);
                                if (!$rechargeLogObj->where('user_id=' . $val['user_id'] . ' and status=2 and id<' . $val['id'])->getField('id')) { // 检查这个时间点外该用户是否有购买(没有)
                                    $firstPayPersonSum += 1;
                                    $firstPaySum += $val['amount'];
                                    if ($bagAmount > 0) {
                                        $firstPaySum += $bagAmount;
                                    }
                                } else { // 有买过(第3次买),是复投用户
                                    array_push($nextUserList, $val['user_id']);
                                    $nextPayPersonSum += 1;
                                    $nextPaySum += $val['amount'];
                                    if ($bagAmount > 0) {
                                        $nextPaySum += $bagAmount;
                                    }
                                }
                            } else if ($arrayCount[$val['user_id']] == 1) { // 第二次 购买用户id
                                array_push($firstUserList, $val['user_id']);
                                $secondPayPersonSum += 1;
                                $secondPaySum += $val['amount'];
                                if ($bagAmount > 0) {
                                    $secondPaySum += $bagAmount;
                                }
                            } else { // 不是第一次购买用户ID
                                if (!in_array($val['user_id'], $nextUserList)) {
                                    array_push($nextUserList, $val['user_id']);
                                    $nextPayPersonSum += 1;
                                    $nextPaySum += $val['amount'];
                                } else {
                                    $nextPaySum += $val['amount'];
                                }

                                if ($bagAmount > 0) {
                                    $nextPaySum += $bagAmount;
                                }
                            }

                            $arrayTotal = array_push($totalUserList, $val['user_id']);//所有 userId
                            $totalPayPersonSum += 1;
                            $totalPaySum += $val['amount'];
                            if ($bagAmount > 0) {
                                $totalPaySum += $bagAmount;
                            }


                            $arrayCount = array_count_values($arrayTotal);
                        }

                        if ($userIds) {
                            $userIds = substr($userIds, 1);
                            $walletSum = $userAccount->where("user_id in (" . $userIds . ")")->sum('wallet_totle');
                        }


                        $list[$pos]['start_time'] = $start_time;
                        $list[$pos]['end_time'] = $end_time;

                        $list[$pos]['first_pay_person_sum'] = $firstPayPersonSum;
                        $list[$pos]['first_pay_sum'] = $firstPaySum;

                        $list[$pos]['second_pay_person_sum'] = $secondPayPersonSum;
                        $list[$pos]['second_pay_sum'] = $secondPaySum;

                        $list[$pos]['total_payment_user'] = $totalRepaymentUser;// 还款用户数
                        $list[$pos]['total_payment_user_pay_sum'] = $totalRepaymentUserPaySum;// 还款总额

                        $list[$pos]['total_pay_count_sum'] = count($userPayList);//总投资数
                        $list[$pos]['total_pay_person_sum'] = $totalPayPersonSum; // 总投用户数
                        $list[$pos]['total_pay_sum'] = $totalPaySum; // 累计投资总额

                        $list[$pos]['next_pay_person_sum'] = $nextPayPersonSum;
                        $list[$pos]['next_pay_sum'] = $nextPaySum;

                        $list[$pos]['wallet_sum'] = $walletSum;
                        $list[$pos]['user_count'] = $userObj->where("channel_id=" . $chn . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'")->count();
                        $list[$pos]['user_download'] = $this->download($chn,$now_time); // 下载量
                        if ($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                        else break;

                        $pos ++;
                    }

                }
            }
        }else{
            $list = array();
        }

        $channelName = '全部渠道';  //

        if($chn) $channelName  = $channelArr[$chn];

        $fileName = '渠道统计-'.$channelName;
        $this->excelModel->setFileName($fileName);
        $this->excelModel->setTitles($this->channel_statistics_titles());
        $this->excelModel->setFields($this->channel_statistics_field());


        $this->excelModel->excelFile($list);
    }


    private function channel_statistics_field(){
        return array(
            'cons_value',
            'add_time',
            'user_count',
            'total_pay_count_sum',
            'first_pay_person_sum',
            'first_pay_sum',
            'second_pay_person_sum',
            'second_pay_sum',
            'total_pay_person_sum',
            'total_pay_sum',
            'total_payment_user',
            'total_payment_user_pay_sum',
            'user_download'
        );
    }
    private function channel_statistics_titles(){
        return array(
            '渠道名称',
            '日期',
            '注册用户数',
            '总投资次数',
            '一投用户数',
            '一投总额',
            '二投用户数',
            '二投总额',
            '总投用户数',
            '累计投资总额',
            '还款用户数',
            '还款总额',
            '下载用户数'
        );
    }

    /**
     * 每日统计 导出 excel
     */

    public function daily_statistics_excel(){
        vendor('PHPExcel.PHPExcel');
        $chn = $isChn = I('get.chn_id', 0, 'int'); // 渠道id
        $start_time = I('get.st', '', 'strip_tags');
        $end_time = I('get.et', '', 'strip_tags');
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        // 获取渠道列表
        $channelList = F('channel_list');
        $constantObj = M('Constant');
        if(!$channelList){
            $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
            $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
            F('channel_list', $channelList);
        }
        $this->assign('channel_list', $channelList);

        $now_time = $end_time;
        $list = array();
        $rows = array();
        $cond = array();
        $conditions = '';

        $userDueDetailObj = M('UserDueDetail');
        $activationDeviceObj = M('ActivationDevice');
        $userWalletRecordsObj = M("UserWalletRecords");
        $userObj = M("User");

        $channelArr = $channelArr = array();
        foreach ($channelList as $c){
            $channelArr[$c['id']] = $c['cons_value'];
        }

        if($start_time && $end_time){ //
            if(!$chn) {
                $list = $this->channel_all_info();
            }else {

                $consValue = isset($channelArr[$chn]) ? trim($channelArr[$chn]) : '';

                $sumNewSecondPayPersonCount = 0; // 新增二次用户
                $sumTotlePayPersonCount = 0;
                $sumTotleCount = 0; // 合计投资总次数
                $sumFirstPayPerson = 0; // 首投用户数
                $sumNextPayPerson = 0; // 复投用户数
                $sumNextPayTimes = 0; // 复投次数
                $sumFirstPay = 0; // 首投金额
                $sumNextPay = 0; // 复投金额
                $sumBackPay = 0; // 还款总数
                $sumActivation = 0; // 设备激活总数
                $sumActivationPay = 0; // 当日激活当日投总数
                $sumWalletSecondPay = 0; // 新增钱包二次投资数
                $sumThreePayPerson = 0; // 三投用户数量
                $sumFourPayPerson = 0; // 四投用户数量
                $sumFivePayPerson = 0; // 五投用户数量

                $sumTotleRegisteredUsers = 0; //注册用户数(总和)

                while (true) {
                    unset($rows);
                    unset($cond);
                    $rows['datetime'] = $now_time;
                    $cond[] = "user_id in (select id from s_user where channel_id=" . $chn . ")";
                    $cond[] = "user_id>0";
                    $cond[] = "status=2";
                    $cond[] = "add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'";
                    $conditions = implode(' and ', $cond);
                    $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id>0 and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000' ";
                    $sql .= "UNION all ";
                    $sql .= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000') as t ";
                    $sql .= "where user_id in (select id from s_user where channel_id=" . $chn . ") order by add_time";
                    $userPayList = M()->query($sql);

                    $firstUserList = array(); // 第一次付费用户数组
                    $nextUserList = array(); // 第二次付费用户数组
                    $userIdList = array(); // 用户ID数组(防止统计重复用户)
                    $firstPayPersonSum = 0; // 首投人数
                    $firstPayCount = 0; //首投次数
                    $nextPayPersonSum = 0; // 复投人数
                    $newSecondPayPersonCount = 0; // 新增二次人数
                    $nextPayTimes = 0; // 复投次数
                    $firstPaySum = 0; // 首投总额
                    $nextPaySum = 0; // 复投总额
                    $threePayPersonSum = 0; // 三投人数
                    $fourPayPersonSum = 0; // 四投人数
                    $fivePayPersonSum = 0; // 五投人数
                    $totleCount = 0; // 投资总次数
                    $totlePayPersonCount = 0; // 总投用户数
                    $totleWalletSecondPay = 0; // 新增钱包二次投资
                    $totleRegisteredUsers = 0;//注册用户数


                    $lossUserCount = 0;//流失用户数

                    //提现
                    $firstWithdrawUserList = array(); // 第一次提现户数组
                    $totalWithdrawUserList = array();// 累计提现户数组
                    //$totalWithdraUserList = array(); // 总提现用户数组
                    $firstWithdrawUserSum = 0;//首次提现用户数
                    $firstWithdrawSum = 0;//首次提现金额
                    $totalWithdrawUserSum = 0;//累计提现用户数
                    $totalWithdrawUserCount = 0;//累计提现次数
                    $totalWithdrawUserPaySum = 0;//累计提现总金额
                    $withdraw = array();

                    //还款
                    $totalRepaymentList = array();// 累计还款户数组
                    $totalRepaymentUser = 0;//还款总用户
                    $totalRepaymentUserPaySum = 0;//还款总金额

                    $backPaySum = $userDueDetailObj->where("user_id in (select id from s_user where channel_id=" . $chn . ") and user_id>0 and due_time>='" . $now_time . " 00:00:00.000000' and due_time<='" . $now_time . " 23:59:59.999000'")->sum('due_capital');
                    $activationCount = $activationDeviceObj->where("channel_id=" . $chn . " and active_time>='" . $now_time . " 00:00:00.000000' and active_time<='" . $now_time . " 23:59:59.999000'")->count(); // 设备激活数
                    $activationPayCountArr = $userDueDetailObj->query("select count(id) as count from s_user_due_detail where user_id in (select id from s_user where device_serial_id in (select device_serial_id from s_activation_device where channel_id=" . $chn . " and active_time>='" . $now_time . " 00:00:00.000000' and active_time<='" . $now_time . " 23:59:59.999000')) and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'");

                    $activationPayCount = $activationPayCountArr[0]['count']; // 当日激活当日投数量
                    foreach ($userPayList as $key => $val) {
                        $totleCount += 1;
                        if($totleCount<=6) { // 记录最多 6 次

                            if ($val['frm'] == 1) {
                                $firstPayCount += 1;
                                if (!in_array($val['user_id'], $firstUserList)) { // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);

                                    if ($firstPayCount<=5) { //首投最多5次

                                        $totlePayPersonCount += 1;
                                        if (!$userDueDetailObj->where('user_id=' . $val['user_id'] . ' and id<' . $val['id'])->getField('id')) {
                                            $firstPayPersonSum += 1;
                                            $firstPaySum += $val['due_capital'];
                                        } else {
                                            if (!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                            $nextPayPersonSum += 1;
                                            $nextPayTimes += 1;
                                            $nextPaySum += $val['due_capital'];
                                        }
                                    }

                                }
                            } else { // 不是第一次购买用户ID
                                    if (!in_array($val['user_id'], $nextUserList)) {
                                        if (!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    } else {
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                            }

                        }

                        if (!in_array($val['user_id'], $userIdList)) { //  用户ID数组(防止统计重复用户)
                            array_push($userIdList, $val['user_id']);
                            // 统计产品和钱包的新增二次用户
                            if ($userDueDetailObj->where("user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000'")->count() == 1) {
                                if ($val['frm'] == 1) {
                                    $newSecondPayPersonCount += 1;
                                } else {
                                    $totleWalletSecondPay += 1;
                                }
                            }
                            // 计算3投以上(包含3投)用户数量
                            $sql2 = "select * from (select id,user_id,add_time,due_capital as v ,1 as frm from s_user_due_detail where user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000'";
                            $sql2 .= " UNION all ";
                            $sql2 .= "select id,user_id,add_time,value as v,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000') as t order by add_time asc";
                            $beforTimes = count($userDueDetailLossArr = M()->query($sql2)); // 今天之前下过几笔订单(产品+钱包)
                            $sql3 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000'";
                            $sql3 .= " UNION all ";
                            $sql3 .= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000') as t order by add_time asc";
                            $nowTimes = count(M()->query($sql3)); // 今天下过的订单数量(产品+钱包)
                            switch ($beforTimes) {
                                case 2: // 3投用户
                                    $threePayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 4) $fourPayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                    break;
                                case 3: // 4投用户
                                    $fourPayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                    break;
                                case 4: // 5投用户
                                    $fivePayPersonSum += 1;
                                    $totalV = 0;
                                    foreach ($userDueDetailLossArr as $u) {
                                        $totalV += $u['v'];
                                    }

                                    if ($totalV < 100) {
                                        $lossUserCount += 1;
                                    }
                                    break;

                            }

                            // 提现统计

                            $sql_wallet = "select id,user_id,add_time,value from s_user_wallet_records where type=2 and status=1 and user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000' order by add_time asc";
                            $withdrawArr = M()->query($sql_wallet); // 今天下过的订单数量(提现统计)

                            foreach ($withdrawArr as $w) {
                                $withdrawValue = abs($w['value']); // 提现金额
                                if (!in_array($w['user_id'], $firstWithdrawUserList)) { // 是第一次购买用户ID
                                    array_push($firstWithdrawUserList, $w['user_id']);
                                    $firstWithdrawUserSum += 1;//首次提现用户数
                                    $firstWithdrawSum += $withdrawValue;//首次提现金额
                                }

                                if (!in_array($w['user_id'], $totalWithdrawUserList)) {
                                    $totalWithdrawUserSum += 1;//累计提现用户数
                                }
                                array_push($totalWithdrawUserList, $w['user_id']);

                                $totalWithdrawUserCount += 1;//累计提现次数
                                $totalWithdrawUserPaySum += $withdrawValue; //累计提现总金额
                            }

                            //还款
                            $_cond = 'user_id > 0 and user_id = ' . $val['user_id'];

                            $_cond .= " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'";

                            $userDueDetailArr = M('userDueDetail')->field('due_amount ,user_id ')->where($_cond . ' and status_new=2')->select();

                            foreach ($userDueDetailArr as $due) {
                                $dueAmount = isset($due['due_amount']) ? trim($due['due_amount']) : 0;
                                if (!in_array($due['user_id'], $totalRepaymentList)) {
                                    $totalRepaymentUser += 1;//还款总用户
                                }
                                array_push($totalRepaymentList, $due['user_id']);
                                $totalRepaymentUserPaySum += $dueAmount; //还款总金额
                            }

                        }

                        // dump(M('userDueDetail')->_sql());

                    }

                    //当日注册用户数
                    $totleRegisteredUsers = $userObj->where("channel_id=" . $chn . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'")->count();

                    //当日沉默用户
                    $channelIdWhere = " and channel_id=" . $chn;
                    $_userIdCond = 'and id  not in (select user_id from s_user_due_detail where add_time>=\'' . $now_time . ' 00:00:00.000000\' and add_time<=\'' . $now_time . ' 23:59:59.999000\'' . ' group by user_id )';

                    $where_userIdCond = "id>0 " . $_userIdCond . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000' " . $channelIdWhere;

                    $silentUserCout = $userObj->where($where_userIdCond)->count();

                    $rows['cons_value'] = $consValue;
                    $rows['new_second_pay_person_count'] = $newSecondPayPersonCount;
                    $rows['first_pay_count'] = $firstPayCount;//首投次数
                    $rows['totle_pay_person_count'] = $totlePayPersonCount;
                    $rows['totle_count'] = $totleCount;
                    $rows['totle_pay_sum'] = $firstPaySum + $nextPaySum;  // 累计投资总额 //$item['first_pay_sum'] + $item['next_pay_sum']
                    $rows['first_pay_person_sum'] = $firstPayPersonSum;
                    $rows['first_pay_sum'] = $firstPaySum;
                    $rows['next_pay_person_sum'] = $nextPayPersonSum;
                    $rows['next_pay_sum'] = $nextPaySum;
                    $rows['next_pay_times'] = $nextPayTimes;
                    $rows['back_pay_sum'] = $backPaySum;
                    $rows['activation_count'] = $activationCount;
                    $rows['activation_pay_count'] = $activationPayCount;
                    $rows['totle_wallet_second_pay'] = $totleWalletSecondPay;
                    $rows['three_pay_person_sum'] = $threePayPersonSum;
                    $rows['four_pay_person_sum'] = $fourPayPersonSum;
                    $rows['five_pay_person_sum'] = $fivePayPersonSum;
                    $rows['registered_users'] = $totleRegisteredUsers;
                    $rows['loss_users'] = $lossUserCount;//流失用户数
                    $rows['silent_users'] = $silentUserCout;//沉默用户

                    //提现
                    $rows['first_withdraw_user_sum'] = $firstWithdrawUserSum;//首次提现用户数
                    $rows['first_withdraw_sum'] = $firstWithdrawSum;//首次提现金额
                    $rows['total_withdraw_user_sum'] = $totalWithdrawUserSum;//累计提现用户数
                    $rows['total_withdraw_user_count'] = $totalWithdrawUserCount;//累计提现次数
                    $rows['total_withdraw_pay_sum'] = $totalWithdrawUserPaySum;//累计提现总金额

                    //还款
                    $totalRepaymentList = array();
                    $rows['total_repayment_user'] = $totalRepaymentUser;//还款总用户
                    $rows['total_repayment_pay_sum'] = $totalRepaymentUserPaySum;//还款总金额

                    // 计入合计
                    $sumTotleRegisteredUsers += $totleRegisteredUsers;
                    $sumNewSecondPayPersonCount += $newSecondPayPersonCount;
                    $sumTotlePayPersonCount += $totlePayPersonCount;
                    $sumTotleCount += $totleCount;
                    $sumFirstPayPerson += $firstPayPersonSum;
                    $sumNextPayPerson += $nextPayPersonSum;
                    $sumFirstPay += $firstPaySum;
                    $sumNextPay += $nextPaySum;
                    $sumNextPayTimes += $nextPayTimes;
                    $sumBackPay += $backPaySum;
                    $sumActivation += $activationCount;
                    $sumActivationPay += $activationPayCount;
                    $sumWalletSecondPay += $totleWalletSecondPay;
                    $rows['three_pay_person_sum'] = $threePayPersonSum;
                    $rows['four_pay_person_sum'] = $fourPayPersonSum;
                    $rows['five_pay_person_sum'] = $fivePayPersonSum;
                    if(!empty($userPayList)) {
                        array_push($list, $rows);
                    }
                    if ($now_time != $start_time) $now_time = date('Y-m-d', strtotime("$now_time -1 day"));
                    else break;
                }
            }

        }

        $channelName = '全部渠道';  //

        if($isChn) $channelName  = $channelArr[$isChn];

        $fileName = '每日统计-'.$channelName;
        $this->excelModel->setFileName($fileName);
        $this->excelModel->setTitles($this->daily_statistics_titles());
        $this->excelModel->setFields($this->daily_statistics_field());


        $this->excelModel->excelFile($list);
    }

    private function channel_all_info(){
        $chn = $isChn = I('get.chn_id', 0, 'int'); // 渠道id
        $start_time = I('get.st', '', 'strip_tags');
        $end_time = I('get.et', '', 'strip_tags');
        $repay_id = I('get.rid', 0, 'int'); // 还本付息表条目ID

        // 获取渠道列表
        $channelList = F('channel_list');
        $constantObj = M('Constant');
        if(!$channelList){
            $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
            $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
            F('channel_list', $channelList);
        }
        $this->assign('channel_list', $channelList);

        $now_time = $end_time;
        $list = array();
        $rows = array();
        $cond = array();
        $conditions = '';

        $userDueDetailObj = M('UserDueDetail');
        $activationDeviceObj = M('ActivationDevice');
        $userWalletRecordsObj = M("UserWalletRecords");
        $userObj = M("User");

        $channelArr  = array();
        foreach ($channelList as $c){
            $channelArr[$c['id']] = $c['cons_value'];
        }

        unset($rows);
        unset($cond);

        $chnCondWhere = '';
        $sql = "select * from (select id,user_id,due_capital,add_time,substr(add_time,1,10) as group_add_time,1 as frm from s_user_due_detail where user_id>0 and add_time>='" . $start_time . " 00:00:00.000000' and add_time<='" . $end_time . " 23:59:59.999000' ";
        $sql .= "UNION all ";
        $sql .= "select id,user_id,0,add_time,substr(add_time,1,10) as group_add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and add_time>='" . $start_time . " 00:00:00.000000' and add_time<='" . $end_time . " 23:59:59.999000') as t ";
        $sql .= " " . $chnCondWhere . " order by add_time";

        $userPayList = M()->query($sql);

        if(!$userPayList) return array();

        //获取user_id 的 Channel_id; 全部渠道
        if(!$chn) {
            foreach ($userPayList as $u) {
                $channelUserArr[] = $u['user_id'];
            }

            $channelUserArr = array_unique($channelUserArr);

            $whereMap = array(
                'id' => array('in', $channelUserArr)
            );

            $userChannelArr = $userObj->field('channel_id,id')->where($whereMap)->select();

            $channelUserConsArr = array();
            foreach ($userChannelArr as $c) {
                $channelUserConsArr[$c['id']] = $c['channel_id'];
            }
        }
        //end

        //按每个渠道每天 分组
        $groupArr = array();

        foreach ($userPayList as  $group){
            $groupAddTime = $group['group_add_time'];

            $groupUserId = $group['user_id'];

            $chnId = $channelUserConsArr[$groupUserId];

            if(!empty($groupAddTime) && !empty($chnId) ) {
                $group['cons_value'] = $channelArr[$chnId];
                $groupArr[$groupAddTime][$chnId][] = $group;
            }
        }


        $chnWhereNew = '';
        $chnIdCondWhere = ' 1 ';
        if($chn) {
            $chnWhereNew = " and user_id in (select id from s_user where channel_id=" . $chn .")";
            $chnIdCondWhere = "channel_id=" . $chn ;
        }



        $backPaySum = $userDueDetailObj->where(  " user_id>0 ".$chnWhereNew." and due_time>='" . $now_time . " 00:00:00.000000' and due_time<='" . $now_time . " 23:59:59.999000'")->sum('due_capital');
        $activationCount = $activationDeviceObj->where( $chnIdCondWhere. " and active_time>='" . $now_time . " 00:00:00.000000' and active_time<='" . $now_time . " 23:59:59.999000'")->count(); // 设备激活数
        $activationPayCountArr = $userDueDetailObj->query("select count(id) as count from s_user_due_detail where user_id in (select id from s_user where device_serial_id in (select device_serial_id from s_activation_device where channel_id=" . $chn . " and active_time>='" . $now_time . " 00:00:00.000000' and active_time<='" . $now_time . " 23:59:59.999000')) and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'");

        $activationPayCount = $activationPayCountArr[0]['count']; // 当日激活当日投数量

        foreach ($groupArr as $date_time =>$gArr) {

                foreach ($gArr as  $chnId => $userPayRows) {

                    $consValue = isset($channelArr[$chnId]) ? trim($channelArr[$chnId]) : '';

                    $firstUserList = array(); // 第一次付费用户数组
                    $nextUserList = array(); // 第二次付费用户数组
                    $userIdList = array(); // 用户ID数组(防止统计重复用户)
                    $firstPayPersonSum = 0; // 首投人数
                    $firstPayCount = 0;//首投次数
                    $nextPayPersonSum = 0; // 复投人数
                    $newSecondPayPersonCount = 0; // 新增二次人数
                    $nextPayTimes = 0; // 复投次数
                    $firstPaySum = 0; // 首投总额
                    $nextPaySum = 0; // 复投总额
                    $threePayPersonSum = 0; // 三投人数
                    $fourPayPersonSum = 0; // 四投人数
                    $fivePayPersonSum = 0; // 五投人数
                    $totleCount = 0; // 投资总次数
                    $totlePayPersonCount = 0; // 总投用户数
                    $totleWalletSecondPay = 0; // 新增钱包二次投资
                    $totleRegisteredUsers = 0;//注册用户数


                    $lossUserCount = 0;//流失用户数

                    //提现
                    $firstWithdrawUserList = array(); // 第一次提现户数组
                    $totalWithdrawUserList = array();// 累计提现户数组
                    //$totalWithdraUserList = array(); // 总提现用户数组
                    $firstWithdrawUserSum = 0;//首次提现用户数
                    $firstWithdrawSum = 0;//首次提现金额
                    $totalWithdrawUserSum = 0;//累计提现用户数
                    $totalWithdrawUserCount = 0;//累计提现次数
                    $totalWithdrawUserPaySum = 0;//累计提现总金额
                    $withdraw = array();

                    //还款
                    $totalRepaymentList = array();// 累计还款户数组
                    $totalRepaymentUser = 0;//还款总用户
                    $totalRepaymentUserPaySum = 0;//还款总金额

                    unset($rows);

                    $rows['datetime'] = $now_time = $date_time;

                    $channelDateUserArr = array();

                    foreach ($userPayRows as $key => $val) {
                        $varUserId = $val['user_id'];
                        $totleCount += 1;

                        if($totleCount<=6){ // 记录 最多 6次记录
                            if ($val['frm'] == 1) {

                                $firstPayCount +=1;
                                if (!in_array($val['user_id'], $firstUserList)) { // 是第一次购买用户ID
                                    array_push($firstUserList, $val['user_id']);

                                    if ($firstPayCount<=5) { //首投最多5次

                                        $totlePayPersonCount += 1;
                                        if (!$userDueDetailObj->where('user_id=' . $val['user_id'] . ' and id<' . $val['id'])->getField('id')) {
                                            $firstPayPersonSum += 1;
                                            $firstPaySum += $val['due_capital'];
                                        } else {
                                            if (!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                            $nextPayPersonSum += 1;
                                            $nextPayTimes += 1;
                                            $nextPaySum += $val['due_capital'];
                                        }

                                    }

                                } else { // 不是第一次购买用户ID
                                    if (!in_array($val['user_id'], $nextUserList)) {
                                        if (!in_array($val['user_id'], $nextUserList)) array_push($nextUserList, $val['user_id']);
                                        $nextPayPersonSum += 1;
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    } else {
                                        $nextPayTimes += 1;
                                        $nextPaySum += $val['due_capital'];
                                    }
                                }
                            }

                        }
                        if (!in_array($val['user_id'], $userIdList)) { //  用户ID数组(防止统计重复用户)
                            array_push($userIdList, $val['user_id']);
                            // 统计产品和钱包的新增二次用户
                            if ($userDueDetailObj->where("user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000'")->count() == 1) {
                                if ($val['frm'] == 1) {
                                    $newSecondPayPersonCount += 1;
                                } else {
                                    $totleWalletSecondPay += 1;
                                }
                            }
                            // 计算3投以上(包含3投)用户数量
                            $sql2 = "select * from (select id,user_id,add_time,due_capital as v ,1 as frm from s_user_due_detail where user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000'";
                            $sql2 .= " UNION all ";
                            $sql2 .= "select id,user_id,add_time,value as v,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=" . $val['user_id'] . " and add_time<'" . $now_time . " 00:00:00.000000') as t order by add_time asc";
                            $beforTimes = count($userDueDetailLossArr = M()->query($sql2)); // 今天之前下过几笔订单(产品+钱包)
                            $sql3 = "select * from (select id,user_id,add_time,1 as frm from s_user_due_detail where user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000'";
                            $sql3 .= " UNION all ";
                            $sql3 .= "select id,user_id,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 and user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000') as t order by add_time asc";
                            $nowTimes = count(M()->query($sql3)); // 今天下过的订单数量(产品+钱包)
                            switch ($beforTimes) {
                                case 2: // 3投用户
                                    $threePayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 4) $fourPayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                    break;
                                case 3: // 4投用户
                                    $fourPayPersonSum += 1;
                                    if ($beforTimes + $nowTimes >= 5) $fivePayPersonSum += 1;
                                    break;
                                case 4: // 5投用户
                                    $fivePayPersonSum += 1;
                                    $totalV = 0;
                                    foreach ($userDueDetailLossArr as $u) {
                                        $totalV += $u['v'];
                                    }

                                    if ($totalV < 100) {
                                        $lossUserCount += 1;
                                    }
                                    break;

                            }

                            // 提现统计

                            $sql_wallet = "select id,user_id,add_time,value from s_user_wallet_records where type=2 and status=1 and user_id=" . $val['user_id'] . " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000' order by add_time asc";
                            $withdrawArr = M()->query($sql_wallet); // 今天下过的订单数量(提现统计)

                            foreach ($withdrawArr as $w) {
                                $withdrawValue = abs($w['value']); // 提现金额
                                if (!in_array($w['user_id'], $firstWithdrawUserList)) { // 是第一次购买用户ID
                                    array_push($firstWithdrawUserList, $w['user_id']);
                                    $firstWithdrawUserSum += 1;//首次提现用户数
                                    $firstWithdrawSum += $withdrawValue;//首次提现金额
                                }

                                if (!in_array($w['user_id'], $totalWithdrawUserList)) {
                                    $totalWithdrawUserSum += 1;//累计提现用户数
                                }
                                array_push($totalWithdrawUserList, $w['user_id']);

                                $totalWithdrawUserCount += 1;//累计提现次数
                                $totalWithdrawUserPaySum += $withdrawValue; //累计提现总金额
                            }

                            //还款
                            $_cond = 'user_id > 0 and user_id = ' . $val['user_id'];

                            $_cond .= " and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'";

                            $userDueDetailArr = M('userDueDetail')->field('due_amount ,user_id ')->where($_cond . ' and status_new=2')->select();

                            foreach ($userDueDetailArr as $due) {
                                $dueAmount = isset($due['due_amount']) ? trim($due['due_amount']) : 0;
                                if (!in_array($due['user_id'], $totalRepaymentList)) {
                                    $totalRepaymentUser += 1;//还款总用户
                                }
                                array_push($totalRepaymentList, $due['user_id']);
                                $totalRepaymentUserPaySum += $dueAmount; //还款总金额
                            }


                        }

                        //user_id 属于 渠道
                        if (!empty($channelUserConsArr[$varUserId])) {
                            $channelDateUserArr[] = $channelUserConsArr[$varUserId];
                        }


                    }

                    if (!$channelDateUserArr) continue;


                    $channelIdWhere = ' and channel_id = '.$chnId;
                    //当日注册用户数
                    $totleRegisteredUsers = $userObj->where("add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000'" . $channelIdWhere)->count();
                    //当日沉默用户
                    $_userIdCond = 'and id  not in (select user_id from s_user_due_detail where add_time>=\'' . $now_time . ' 00:00:00.000000\' and add_time<=\'' . $now_time . ' 23:59:59.999000\''  . ' group by user_id )';

                    $where_userIdCond =  "id>0 ".$_userIdCond." and add_time>='" . $now_time . " 00:00:00.000000' and add_time<='" . $now_time . " 23:59:59.999000' " . $channelIdWhere ;

                    $silentUserCout =  $userObj->where($where_userIdCond)->count();

                    $rows['cons_value'] = $consValue;
                    $rows['new_second_pay_person_count'] = $newSecondPayPersonCount;
                    $rows['first_pay_count'] = $firstPayCount;//首投次数
                    $rows['totle_pay_person_count'] = $totlePayPersonCount;
                    $rows['totle_count'] = $totleCount;
                    $rows['totle_pay_sum'] = $firstPaySum + $nextPaySum;  // 累计投资总额 //$item['first_pay_sum'] + $item['next_pay_sum']
                    $rows['first_pay_person_sum'] = $firstPayPersonSum;
                    $rows['first_pay_sum'] = $firstPaySum;
                    $rows['next_pay_person_sum'] = $nextPayPersonSum;
                    $rows['next_pay_sum'] = $nextPaySum;
                    $rows['next_pay_times'] = $nextPayTimes;
                    $rows['back_pay_sum'] = $backPaySum;
                    $rows['activation_count'] = $activationCount;
                    $rows['activation_pay_count'] = $activationPayCount;
                    $rows['totle_wallet_second_pay'] = $totleWalletSecondPay;
                    $rows['three_pay_person_sum'] = $threePayPersonSum;
                    $rows['four_pay_person_sum'] = $fourPayPersonSum;
                    $rows['five_pay_person_sum'] = $fivePayPersonSum;
                    $rows['registered_users'] = $totleRegisteredUsers;
                    $rows['loss_users'] = $lossUserCount;//流失用户数
                    $rows['silent_users'] = $silentUserCout;//沉默用户

                    //提现
                    $rows['first_withdraw_user_sum'] = $firstWithdrawUserSum;//首次提现用户数
                    $rows['first_withdraw_sum'] = $firstWithdrawSum;//首次提现金额
                    $rows['total_withdraw_user_sum'] = $totalWithdrawUserSum;//累计提现用户数
                    $rows['total_withdraw_user_count'] = $totalWithdrawUserCount;//累计提现次数
                    $rows['total_withdraw_pay_sum'] = $totalWithdrawUserPaySum;//累计提现总金额

                    //还款
                    $totalRepaymentList = array();
                    $rows['total_repayment_user'] = $totalRepaymentUser;//还款总用户
                    $rows['total_repayment_pay_sum'] = $totalRepaymentUserPaySum;//还款总金额

                    $rows['three_pay_person_sum'] = $threePayPersonSum;
                    $rows['four_pay_person_sum'] = $fourPayPersonSum;
                    $rows['five_pay_person_sum'] = $fivePayPersonSum;
                    array_push($list, $rows);

                }

        }

        return $list;
    }



    private function daily_statistics_field(){
        return array(
            'cons_value',
            'datetime',
            'registered_users',
            'first_pay_person_sum',
            'first_pay_count',
            'first_pay_sum',
            'next_pay_sum',
            'next_pay_times',
            'next_pay_sum',
            'totle_pay_person_count',
            'totle_count',
            'totle_pay_sum',
            'first_withdraw_user_sum',
            'first_withdraw_sum',
            'total_withdraw_user_sum',
            'total_withdraw_user_count',
            'total_withdraw_pay_sum',
            'total_repayment_user',
            'total_repayment_pay_sum',
            'loss_users',
            'silent_users'
        );
    }
    private function daily_statistics_titles(){
        return array(
            '渠道',
            '日期',
            '注册用户数',
            '首投用户数',
            '首投次数',
            '首投总额',
            '复投用户数',
            '复投次数',
            '复投总额',
            '总投用户数',
            '总投资次数',
            '累计投资总额',
            '首次提现用户数',
            '首次提现金额',
            '累计提现用户数',
            '累计提现次数',
            '累计提现总金额',
            '还款用户数',
            '还款总额',
            '流失用户数',
            '沉默用户数'
        );
    }


    /*
     * 充值用户统计(新) excel
     */
    public function recharge_new_excel(){
        vendor('PHPExcel.PHPExcel');
        if(!IS_POST) {
            $page = I('get.p', 1, 'int'); // 页码
            $chn = I('get.chn', 0, 'int'); // 渠道ID
            $start_time = I('get.st', '');
            $end_time = I('get.et', '');

            // 获取渠道列表
            $channelList = F('channel_list');
            $constantObj = M('Constant');
            if (!$channelList) {
                $channelPid = $constantObj->where(array('cons_key' => 'channel', 'parent_id' => 0))->getField('id');
                $channelList = $constantObj->field('id,cons_key,cons_value')->where(array('parent_id' => $channelPid))->select();
                F('channel_list', $channelList);
            }

            $this->assign('channel_list', $channelList);

            foreach ($channelList as $ch){
                $channelArr[$ch['id']] = $ch['cons_value'];
            }

            // 用户列表
            $userObj = M('User');
            $userAccount = M("UserAccount");
            $rechargeLogObj = M('RechargeLog');
            $userDueDetailObj = M("UserDueDetail");

            $cond[] = "user_id>0";
            if ($start_time) $cond[] = "add_time>='" . $start_time . " 00:00:00.000000'";
            if ($end_time) $cond[] = "add_time<='" . $end_time . " 23:59:59.999000'";
            $conditions = implode(' and ', $cond);
            $ucondition = "id in (select distinct(user_id) from s_user_due_detail where " . $conditions . ")  ";
            if ($chn) $ucondition .= " and channel_id=" . $chn;


            $counts = $userObj->where($ucondition)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();

            $list = $userObj->field('id,username,real_name,channel,channel_id,add_time,status,card_no')
                ->where($ucondition)->order('add_time desc')->select();//->limit($Page->firstRow . ',' . $Page->listRows)->

            $condPay = $uId = array();

            foreach ($list as $l){
                $uId[] = $l['id'];
            }

            if($uId) $condPay[] = " and  user_id in ( ".implode(' , ', $uId).")";

            if ($start_time) $condPay[] = "add_time>='" . $start_time . " 00:00:00.000000'";
            if ($end_time) $condPay[] = "add_time<='" . $end_time . " 23:59:59.999000'";

            $conditionsPay = implode(' and ', $condPay);

            $sql = "select * from (select id,user_id,due_capital,add_time,1 as frm from s_user_due_detail where user_id >0  "  . $conditionsPay;
            // $sql .= " UNION all ";
            // $sql .= "select id,user_id,0,add_time,2 as frm from s_user_wallet_records where type=1 and pay_status=2 " . $conditionsPay
            $sql .=  "   ) as t  order by add_time";

            $userPayList = M()->query($sql);
            $totalUserList = array();
            $firstPaySum = 0; // 首投金额
            $secondPaySum = 0;// 二投金额

            $userPayArr = array();


            foreach ($userPayList as $val) {
                $userId = $val['user_id'];
                if(!$totalUserList[$userId]){
                    $firstTime = $val['add_time'];
                    $firstPaySum = $val['due_capital'];
                    $userPayArr[$val['user_id']]['first_time'] = $firstTime;
                    $userPayArr[$val['user_id']]['first_pay_sum'] = $firstPaySum;
                }else if(count($totalUserList[$userId])==1){
                    $secondTime = $val['add_time'];
                    $secondPaySum = $val['due_capital'];
                    $userPayArr[$val['user_id']]['second_time'] = $secondTime;
                    $userPayArr[$val['user_id']]['second_pay_sum'] = $secondPaySum;
                }

                $totalUserList[$userId][] = $userId;
            }

            foreach ($list as $i=>$l){
                $list[$i]['add_time'] = M('user_account')->where( array('user_id'=>$l['id']) )->getField('add_time');
                $list[$i]['first_time'] = isset($userPayArr[$l['id']]['first_time'])?substr(trim($userPayArr[$l['id']]['first_time']),0,19):'';
                $list[$i]['first_pay_sum'] = isset($userPayArr[$l['id']]['first_pay_sum'])?intval($userPayArr[$l['id']]['first_pay_sum']):'';
                $list[$i]['second_time'] = isset($userPayArr[$l['id']]['second_time'])?substr(trim($userPayArr[$l['id']]['second_time']),0,19):'';
                $list[$i]['second_pay_sum'] = isset($userPayArr[$l['id']]['second_pay_sum'])?intval($userPayArr[$l['id']]['second_pay_sum']):'';
                $list[$i]['add_time'] = substr($l['add_time'],0,19);
            }

            $channelName = '全部渠道';  //

            if($chn) $channelName  = $channelArr[$chn];

            $fileName = '用户统计-'.$channelName;
            $this->excelModel->setFileName($fileName);
            $this->excelModel->setTitles($this->recharge_new_titles());
            $this->excelModel->setFields($this->recharge_new_field());


            $this->excelModel->excelFile($list);

        }else{
            $chn = I('get.chn', 0, 'int');
            $start_time = trim(I('get.start_time', '-'));
            $end_time = trim(I('get.end_time', '-'));
            $quest = '/chn/'.$chn;
            if($start_time) $quest .= '/st/'.$start_time;
            if($end_time) $quest .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT') . '/statistics/recharge_new'.$quest);
        }
    }

    private function recharge_new_field(){
        return array(
            'id',
            'real_name',
            'username',
            'add_time',
            'first_time',
            'first_pay_sum',
            'second_time',
            'second_pay_sum'
        );
    }
    private function recharge_new_titles(){
        return array(
            '用户id',
            '用户名',
            '手机号',
            '注册时间',
            '一投日期',
            '一投金额(元)',
            '二投日期',
            '二投金额(元)'
        );
    }


    /*
     * 导入  channel_file_excel
     */

    public function channel_file_excel(){

        // 获取渠道列表
        $channelList = F('channel_list');
        $constantObj = M('Constant');
        if(!$channelList){
            $channelPid = $constantObj->where(array('cons_key'=>'channel','parent_id'=>0))->getField('id');
            $channelList = $constantObj->where(array('parent_id'=>$channelPid))->select();
            F('channel_list', $channelList);
        }

        $channelArray = array();
        foreach ($channelList as $c){
            $channelArray[$c['cons_value']] = $c['id'];
        }

        if ($_FILES) {
            $config = array(
                'exts'       =>    array('application/octet-stream'),
            );

            $config = array();
            $upload = new \Think\Upload($config);// 实例化上传类
            // 上传文件
            $info   =   $_FILES['xls']['error'];

            if(!$info) {
                $xls = $_FILES['xls']['tmp_name'];

               $xlsArr = file_get_contents($xls);
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'请选择一个excel'));
        }

        vendor('PHPExcel.PHPExcel');


        $_ReadExcel = new \PHPExcel_Reader_Excel2007();
        if(!$_ReadExcel->canRead($xls)){
            $_ReadExcel = new \PHPExcel_Reader_Excel5();
        }

        $objPHPExcel = $_ReadExcel->load($xls,'utf-8');

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数


        $num = 0;
        for($j=3;$j<=$highestRow;$j++) {
            $str = '';
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '\\';//读取单元格
            }
            $strs = explode("\\", $str);
            $add_data['channel'] = $strs[0];
            $add_data['date_time'] = date('Y-m-d',strtotime($strs[1]));
            $add_data['number'] = $strs[2];
            $data[]=$add_data;
            $num++;
        }

        $dateChannel = array();
        foreach ($data as $dt){
            $channel = trim($dt['channel']);
            $dateChannel[$channel] = $dt;
        }

        $addData = array();

        $count = count($dateChannel);

        $channelDownloadChannelIdArr = M('userChannelDownload')->field('channel_id,date_time')->group('channel_id')->select();

        $channelDownloadChannelIdNewArr = $channelDateTimeArr = array();
        foreach ($channelDownloadChannelIdArr as $downloadArr){
            $channelDownloadChannelIdNewArr[] = $downloadArr['channel_id'];
            $channelDateTimeArr[] = $downloadArr['date_time'];
        }


        if($count>50) $dateChannel = array_slice($dateChannel, 0,50);
        foreach ($dateChannel as $d){
            $channel = trim($d['channel']);
            $dateTime = trim($d['date_time']);
            $channelId = $channelArray[$channel];
            $channelDateTime = $channelArray[$dateTime];
            $d['channel_id'] = $channelId;
            if(!in_array($channelId,$channelDownloadChannelIdNewArr) && !in_array($channelDateTime,$channelDateTimeArr)){ ///
                $addData[] = $d;
            }
        }


        $obj = M('userChannelDownload')->addAll($addData);


        if($obj) {
            $this->ajaxReturn(array('status'=>1,'info'=>'添加成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'添加失败'));
        }

    }


    private function download($chn,$now_time){
        $download = M('UserChannelDownload')->field('number')->where("channel_id=" . $chn . " and date_time>='" . $now_time . " 00:00:00.000000' and date_time<='" . $now_time . " 23:59:59.999000'")->find();
        return isset($download['number'])?intval($download['number']):0;
    }

}
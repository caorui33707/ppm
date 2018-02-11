<?php
namespace Admin\Controller;

/**
 * 券包管理
 */
class InterestCouponController extends AdminController {
    
        private $pageSize = 18;
        protected $excelModel;

        protected function _initialize(){
            $this->excelModel = new \Admin\Model\ExcelModel();
        }
        /**
         * 发放券包
         */
        
        public function add(){
        
            if(!IS_POST){
                 
                $this->display('add');
        
            }else{
                                
                 
                $row['title'] = I('post.title','','strip_tags');
        
                $row['subtitle'] = I('post.subtitle','','strip_tags');
        
                $row['interest_rate'] = I('post.interest_rate', 0, 'float');//金额
        
                $row['min_invest'] = I('post.min_invest', 0, 'float');	//红包最小投资金额：
        
                $row['min_due'] = I('post.min_due', 0, 'int');	//红包最小投资期限：
        
                $row['due_time'] = I('post.due_date', 0, 'int');	//红包到期天数：
        
                $send_scope = I('post.send_scope', 0, 'int');//发放范围
                
                $row['memo'] = I('post.memo','','strip_tags');
                
                $row['source'] = trim(I('post.source', '', 'strip_tags'));

                $row['act_name']  = $act_name = trim(I('post.act_name', '', 'strip_tags')); // 活动名称
                //$row['type'] = trim(I('post.type', 0, 'int')); //红包发放渠道  个人/平台活动/微信/暗道

                $row['type']  = 4;//  只要 暗道
                
                $tag_arr = $_REQUEST['tag_id'];
        
                if (!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));
        
                if (!$row['subtitle']) $this->ajaxReturn(array('subtitle' => 0, 'info' => '请输入子标题'));

                if (!$act_name) $this->ajaxReturn(array('subtitle' => 0, 'info' => '请输入活动名称'));
        
                if (!$row['interest_rate'] || $row['interest_rate'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入券包利率'));
        
                if (!$row['min_due']  || $row['min_due'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资金额'));
        
                if (!$row['due_time'] || $row['due_time'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资期限'));
                
                
                if(!$tag_arr) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择标签'
                    ));
                }
                
                $row['apply_tag'] = implode(':',$tag_arr);
                
                /*20161019 新加*/
                //add_time 是加息券生效时间，因为有历史问题 ，字段名不方便改
                
                $row['add_time'] = trim(I('post.start_time','','strip_tags'));
                if(!$row['add_time']) {
                    $row['add_time'] = date("Y-m-d 00:00:00");
                }
        
                //红包到期天数 等于当前时间 + $due_time
                $row['expire_time'] = date("Y-m-d 23:59:59",strtotime($row['add_time'])+($row['due_time']-1) * 86400);
                $row['add_user_id'] = $row['modify_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];
                
                $row['modify_time'] = date("Y-m-d H:i:s");
                
                //记录创建时间
                $row['create_time'] = time();
                
                $row['status'] = 0;
                
                $coupon_id = M('UserInterestCoupon')->max('coupon_id');
                
                $max_id = M('EventConf')->max('id');
                
                if(!$max_id) $max_id = 1;
                
                $row['coupon_id'] = $coupon_id + $max_id;//M('UserInterestCoupon')->max('coupon_id') + 1;

                //$send_scope 1 全部用户 ，2 指定用户
                if(2 == $send_scope) {
        
                    $userIdStr = I('post.userId', '', 'strip_tags');//发放范围
        
                    if($userIdStr == "") {
                        $this->ajaxReturn(array('status' => 0, 'info' => '请添加需要指定发放的用户!'));
                    }
        
                    $userIdArr = explode("#",$userIdStr);
                    $row['scope'] = 1;
                    $res = false;
        
                    M("UserInterestCoupon")->startTrans();

        
                    foreach ($userIdArr as $val) {
        
                        $row['user_id'] = $val;
        
                        $res = M("UserInterestCoupon")->add($row);
                    }


                    if($res) {
                        M("UserInterestCoupon")->commit();
                        $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
                    }else{
                        M("UserInterestCoupon")->rollback();
                        $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
                    }
                    exit;
                } else {
                    
                    $row['scope'] = 2;
                    
                    $sql = "INSERT INTO `s_user_interest_coupon`(id,coupon_id,title,subtitle,user_id,recharge_no,interest_rate,min_due,min_invest,project_id,
                        expire_time,status,scope,add_time,add_user_id,modify_time,modify_user_id,is_delete,is_read,create_time,memo,source,apply_tag,type,act_name)
                                                
                        SELECT null, '".$row['coupon_id']."', '".$row['title']."','".$row['subtitle']."', id, 
                         
                         '', '".$row['interest_rate']."', '".$row['min_due']."', '".$row['min_invest']."', '0', '".$row['expire_time']."', '0', '".$row['scope']."', '".$row['add_time']."', '".$row['add_user_id']."', '".$row['modify_time']."', '".$row['modify_user_id']."', '0', '0',unix_timestamp(), '".$row['memo']."'
                            
                              ,'".$row['source']."','".$row['apply_tag']."', '".$row['type']."' , '".$row['act_name']."' FROM s_user";
                    
                    if( M()->execute($sql)){
                        $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
                    } else {
                        $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
                    }
                    exit;
                }
            }
        }
        
        
        public function addBatch(){
        
            if(!IS_POST){
                $this->display('add2');
            }else{
                $row['title'] = I('post.title','','strip_tags');
                $row['subtitle'] = I('post.subtitle','','strip_tags');
                $row['interest_rate'] = I('post.interest_rate', 0, 'float');//金额
                $row['min_invest'] = I('post.min_invest', 0, 'float');	//红包最小投资金额：
                $row['min_due'] = I('post.min_due', 0, 'int');	//红包最小投资期限：
                $row['due_time'] = I('post.due_date', 0, 'int');	//红包到期天数：
                $send_scope = I('post.send_scope', 0, 'int');//发放范围
                $row['memo'] = I('post.memo','','strip_tags');
        
                $tag_arr = $_REQUEST['tag_id'];
        
                if (!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));
                if (!$row['subtitle']) $this->ajaxReturn(array('subtitle' => 0, 'info' => '请输入子标题'));
                if (!$row['interest_rate'] || $row['interest_rate'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入券包利率'));
                if (!$row['min_due']  || $row['min_due'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资金额'));
                if (!$row['due_time'] || $row['due_time'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资期限'));
                if(!$tag_arr) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '请选择标签'
                    ));
                }
                $row['apply_tag'] = implode(':',$tag_arr);
        
                /*20161019 新加*/
                //add_time 是加息券生效时间，因为有历史问题 ，字段名不方便改
        
                $row['add_time'] = trim(I('post.start_time','','strip_tags'));
                if(!$row['add_time']) {
                    $row['add_time'] = date("Y-m-d 00:00:00");
                }
        
                //红包到期天数 等于当前时间 + $due_time
                $row['expire_time'] = date("Y-m-d 23:59:59",strtotime($row['add_time'])+($row['due_time']-1) * 86400);
                $row['add_user_id'] = $row['modify_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];
                $row['modify_time'] = date("Y-m-d H:i:s");
        
                //记录创建时间
                $row['create_time'] = time();
                $row['status'] = 0;
                $coupon_id = M('UserInterestCoupon')->max('coupon_id');
                $max_id = M('EventConf')->max('id');
                if(!$max_id) $max_id = 1;
                $row['coupon_id'] = $coupon_id + $max_id;//M('UserInterestCoupon')->max('coupon_id') + 1;
        
   
                $userIdStr = I('post.user_list', '', 'strip_tags');//发放范围
    
                if($userIdStr == "") {
                    $this->ajaxReturn(array('status' => 0, 'info' => '请添加需要指定发放的用户!'));
                }
    
                $userIdArr = explode("#",$userIdStr);
                $row['scope'] = 1;
                $res = false;
    
                M("UserInterestCoupon")->startTrans();
    
                foreach ($userIdArr as $val) {
                    
                    $user_id = M('User')->where("username='$val'")->getField('id');
                    if($user_id) {
                        $row['user_id'] = $user_id;
                        $res = M("userInterestCoupon")->add($row);
                        unset($row['user_id']);
                    }
                }
    
                if($res) {
                    M("UserInterestCoupon")->commit();
                    $this->ajaxReturn(array('status' => 1, 'info' => "发放成功"));
                }else{
                    M("UserInterestCoupon")->rollback();
                    $this->ajaxReturn(array('status' => 0, 'info' => "失败"));
                }
                exit;
                
            }
        }
        
        public function delete(){
            
            if(IS_AJAX) {
                
                $coupon_id = I('post.coupon_id', 0, 'int');
                
                if(!$coupon_id) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "参数错误"));
                }
                
                $cnt = M('UserInterestCoupon')->where(array('coupon_id'=>$coupon_id,'status'=>1))->count();
                
                if($cnt > 0) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "该券包已经有用户使用，无法删除"));
                }
                
                $dd['is_delete'] = 1;
                $dd['modify_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
                $dd['modify_time'] = date('Y-m-d H:i:s');
              
                if(!M('UserInterestCoupon')->where(array('coupon_id'=>$coupon_id))->save($dd)) {
                    $this->ajaxReturn(array('status' => 1, 'info' => "删除失败"));
                }
                
                $this->ajaxReturn(array('status' => 0, 'info' => "删除成功"));
                
            } else {
                $this->ajaxReturn(array('status' => 1, 'info' => "非法访问"));
            }
        }
        
    
        /**
         * 券包数据管理
         */
        public function index() {
            
            if(!IS_POST) {        
                $page = I('get.p', 1, 'int');
                $status = I("get.status",0,'int');
                $key = trim(urldecode(I("get.s",'','strip_tags')));
                $time = trim(urldecode(I("get.time",'','strip_tags')));
                
                $cond[] = 'is_delete = 0';
                if($status == 0) {
                    $cond[] = 'status >=2';
                } else {
                    $cond[] = 'status ='.$status;
                }
                if(!empty($key)) {
                    $cond[] = "title like '%$key%'";
                }
                
                if($time){
                    $cond[] = "end_time >='$time' AND end_time <= '$time :23:59:59'";
                }
                
                $cond = implode(' AND ', $cond);
                $res = array();
                
                $totalCnt =  M("Project")->where($cond)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = M("Project")->field('id,title,status,end_time,user_interest,amount,able')->where($cond)->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                
                if($list) {
                    foreach ($list as $val) {
                        if ($val['status'] == 2){
                            $val['status'] = '销售中 ';
                            $val['color'] = '#FF0000';
                        } else if ($val['status'] == 3){
                            $val['status'] = '已售完';
                            $val['color'] = '#191970';
                        } else if($val['status'] == 4) {
                            $val['status'] = '还款中';
                            $val['color'] = '#8B0000';
                        } else if($val['status'] == 5){
                            $val['status'] = '已还款';
                            $val['color'] = '#CCCCCC';
                        }
                        
                        //期结束日期
                        $val['end_time'] = date('Y-m-d',strtotime($val['end_time']));
                        
                        //项目购买金额
                        $val['project_amount'] = $val['amount'] - $val['able'];
            
                        $_list = M('UserInterestCoupon')->field('interest_rate,recharge_no,user_id,modify_time')->where("project_id=".$val['id'] ." and  recharge_no != '' and status = 1 and is_delete=0")->select();
                        
                        //使用券包人数
                        $val['use_cnt'] = count($_list);
                        
                        //券包产生的收益
                        $val['coupon_income'] = 0;
            
                        if($_list) {
            
                            foreach ($_list as $_val) {
                                
                                $_buy_time = date('Y-m-d',strtotime($_val['modify_time']));
                                
                                $order_amount = $this->getRechargeLogInfo($_val['recharge_no'], 'amount');
            
                                $val['coupon_income'] += $this->countInterest($order_amount,$_buy_time,$val['end_time'],$_val['interest_rate']);
                            }
                        }
                        $res[] = $val;
                    }
                }
                
                $params = array(
                    'status'=>$status,
                    'key'=>$key,
                    'totalCnt'=>$totalCnt,
                    'page'=>$page,
                    'time'=>$time
                );
                $sql = "SELECT p.`id` as id ,p.`title` as title,p.`status` as status FROM `s_user_due_detail` d LEFT JOIN `s_project` p  on d.`project_id` = p.`id`  where d.`interest_coupon` > 0 GROUP BY d.`project_id` ORDER BY p.id DESC";
                $this->assign("projectList",M()->query($sql));            
                $this->assign("list",$res);     
                $this->assign("params",$params);
                $this->assign('showPage',$Page->show());            
                $this->display('index');
            
            } else {
                $key = I('post.key', '', 'strip_tags');
                $status = I('post.status', 0, 'int');
                $start_time = I('post.start_time', '', 'strip_tags');
                $quest = '/status/'.$status;
                if($key) $quest .= '/s/'.urlencode($key);
                if($start_time)$quest .= '/time/'.urlencode($start_time);
                redirect(C('ADMIN_ROOT') . '/InterestCoupon/index'.$quest);
            }
        }
        
        
        
        /**
         * 券包使用明细
         * 
         */
        
        public function user_coupon_list() {

            $projectId = I("get.pid",0,'int');            
            $userName = trim(I("post.userName","","strip_tags"));            
            $page = I('get.p', 1, 'int');   // 页码            
            $res = array();
            
            if ($projectId>0) {
                $userId = '';
                $cond = 'project_id='.$projectId .' and status = 1';
                if($userName) {
                    $this->assign("userName",$userName);
                    $user = M("User")->field('id')->where("real_name like '%$userName%'")->select();
                    if($user){
                        foreach ($user as $val){
                            $userId .= $val['id'].',';
                        }
                        $userId = trim($userId,',');
                    }
                    if ($userId) {
                        $cond .= " and user_id in($userId)";
                    } else {
                        $cond .= " and user_id = -1";
                    }
                }
                
                $totalCnt =  M("UserInterestCoupon")->where($cond)->count();                        
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = M("UserInterestCoupon")->field('id,interest_rate,recharge_no,user_id,project_id,modify_time')->where($cond)->order("modify_time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    $n = 0;
                    foreach ($list as $val) {
                        
                        $rows['n'] = ++ $n;
                        
                        $rows['user_info'] = $this->getUserInfo($val['user_id']);
                        
                        $rows['order_info'] = $this->getRechargeLogInfo($val['recharge_no'],'');
                        
                        if(strpos($val['recharge_no'], 'QB') === false) {
                            $rows['bank_info'] = $this->getBankInfo($val['user_id'], $rows['order_info']['card_no']);
                        }
                        
                        $rows['interest_rate'] = $val['interest_rate'];
                        
                        $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                        
                        $_project_end_time = date('Y-m-d',strtotime($this->getProjectInfo($val['project_id'], 'end_time')));
                        
                        $rows['coupon_income'] = $this->countInterest($rows['order_info']['amount'] ,$_project_end_time,$_buy_time,$val['interest_rate']);

                        $rows['user_interest'] = $this->getProjectInfo($val['project_id'], 'user_interest');
                        
                        $res[] = $rows;
                    }
                    unset($list);
                }
            }
            
            $this->assign("title",$this->getProjectInfo($projectId,"title"));
            $this->assign('page', $page);
            $this->assign("list",$res);
            $this->assign('show', $Page->show());
            $this->display('user_coupon_list');
            
        }
        
        /**
         * 券包列表 
         * 
         */
        public function history_index() {
            if(!IS_POST) {
                $title = I("get.title", '', 'strip_tags');

                $title = urldecode($title);
                $start_time = I("get.start_time", '', 'strip_tags');//I("get.start_time", date('Y-m-d', strtotime('-3 days')), 'strip_tags');
                $end_time = I("get.end_time", '', 'strip_tags');

                $res = array();

                $condition = 'is_delete = 0 ';


                if ($title) $condition .= " AND title LIKE '$title%'";
                else  $title = '';


                if ($start_time != "") {

                    $_start_time = strtotime($start_time);

                    $condition .= " AND UNIX_TIMESTAMP(`add_time`) >= $_start_time";

                }
                if ($end_time != "") {

                    $_end_time = strtotime($end_time);

                    $condition .= " AND UNIX_TIMESTAMP(`add_time`) <= $_end_time";
                }

                $totalCnt = M()->query("select count(*) as cnt from (SELECT * FROM `s_user_interest_coupon` WHERE ( $condition ) GROUP BY coupon_id ) tmp");

                $Page = new \Think\Page($totalCnt[0]['cnt'], $this->pageSize);


                $list = M('UserInterestCoupon')->field("id,title,coupon_id,interest_rate,add_time,expire_time,min_invest,min_due,scope,memo,recharge_no,type,apply_tag,act_name,source")->where($condition)->group("coupon_id")->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();

                // apply_tag   适用标签，0: 普通   1:新手   2:爆款   6:活动  8:个人专享，适用多个表情时以":"隔开

                $applyTagArr = array(0 => '普通', 1 => '新手', 2 => '爆款', 6 => '活动', 8 => '个人专享');

                if (!empty($list)) {

                    foreach ($list as $val) {

                        $interest_rate = $val['interest_rate'];//金额

                        if (1 == $val['scope']) {
                            $val['scope'] = '个人';
                        } else if (2 == $val['scope']) {
                            $val['scope'] = '所有';
                        }

                        $val['total'] = $total = M('UserInterestCoupon')->where('coupon_id = ' . $val['coupon_id'] . ' and is_delete = 0')->count();
                        $val['use_totla'] = $use_totla = M('UserInterestCoupon')->where('coupon_id = ' . $val['coupon_id'] . " and recharge_no !='' and status = 1")->count();

                        $val['total_amount'] = $interest_rate * $total; // 发放金额

                        // 使用金额

                        $val['use_totla_amount'] = $use_totla_amount = $interest_rate * $use_totla; // 使用金额


                        //投资金额  s_investment_detail /// 用户投资记录信息表
                        $rechargeNoWhere = array(
                            'coupon_id' => $val['coupon_id'],
                            'recharge_no' => array('neq', '')
                        );
                        $rechargeNoArr = M("UserInterestCoupon")->where($rechargeNoWhere)->getField('recharge_no', true);  // dump($rechargeNoArr);

                        if ($rechargeNoArr) {

                            //$inv_succ_arr = $totla_inv_succ = M('investment_detail')->field('inv_succ,recharge_no')->where(array('recharge_no'=>array('in',$rechargeNoArr) ))->select();

                            $invest_detail_arr = M('investment_detail')->field('id,inv_succ,recharge_no')->where(array('recharge_no' => array('in', $rechargeNoArr)))->select();


                            $inv_succ_data = $invest_detail_data = $invest_detail_id_arr = array();


                            foreach ($invest_detail_arr as $i) {
                                // $inv_succ_data[$i['recharge_no']] = $i['inv_succ'];
                                $invest_detail_data[$i['id']] = $i['inv_succ'];
                                $invest_detail_id_arr[] = $i['id'];
                            }

                            if ($invest_detail_id_arr) {
                                $duration_day_arr = M('user_due_detail')->field('duration_day,invest_detail_id')->where(array('invest_detail_id' => array('in', $invest_detail_id_arr)))->select();
                            } else {
                                $duration_day_arr = array();
                            }
                            //$inv_succ*$interest_rate*$duration_day/365

                            $inv_succ_day = 0;
                            foreach ($duration_day_arr as $d) {
                                $inv_succ_amt = isset($invest_detail_data[$d['invest_detail_id']]) ? sprintf('%.2f', $invest_detail_data[$d['invest_detail_id']]) : 0;

                                $duration_day = isset($d['duration_day']) ? $d['duration_day'] : 0;

                                $inv_succ_day += $inv_succ_amt * $duration_day;
                            }

                            $val['inv_succ'] = $inv_succ = array_sum($invest_detail_data);
                        } else {
                            $val['inv_succ'] = $inv_succ = 0;
                            $inv_succ_day = 0;
                        }


                        // 使用金额  加息收益
                        $interest_rate = $val['interest_rate']; //利率
                        $interest_rate = $interest_rate / 100; // 百分比
                        $val['use_totla_amount_inc'] = $use_totla_amount_inc = ($use_totla_amount_inc_s = $inv_succ_day * $interest_rate / 365) ? sprintf('%.2f', $use_totla_amount_inc_s) : '0.00'; // 使用金额 加息收益

                        //千元投资成本
                        if ($inv_succ) {
                            $val['thousand_inv_succ'] = ($thousand_inv_succ = $use_totla_amount_inc / $inv_succ * 1000) ? sprintf('%.2f', $thousand_inv_succ) : '0.00';
                        } else {
                            $val['thousand_inv_succ'] = '0.00';
                        }

                        //适用标签
                        $apply_tag_arr = explode(':', $val['apply_tag']);

                        $apply_tag_titie_arr = array();
                        foreach ($apply_tag_arr as $a) {
                            $apply_tag_titie_arr[] = $applyTagArr[$a];
                        }
                        $val['apply_tag_titie'] = implode(',', $apply_tag_titie_arr);

                        if ($val['type'] != 4) { // 不等于暗道

                            $val['act_name'] = '';
                            $endStr = strrpos($val['source'],'_');
                            $key_name = substr($val['source'],0,$endStr);
                            if($key_name) {
                                $val['act_name'] = ($act_name = M('lottery_base')->where(array('key_name' => $key_name))->getField('name')) ? $act_name : '';
                            }

                        }

                        $res[] = $val;
                    }
                    unset($list);
                }


                $param = array("title" => $title, "start_time" => $start_time, "end_time" => $end_time);
                $this->assign("params", $param);
                $this->assign("list", $res);
                $this->assign('show', $Page->show());
                $this->display('history_index');
            }else{
                $title = I('post.title', '0', 'strip_tags');
                $start_time = trim(I('post.start_time', '-'));
                $end_time = trim(I('post.end_time', '-'));
               if(!$title) $title = 0;
                $quest = '/title/'.$title;
                //$quest = '/chn/'.$chn;
                if($start_time) $quest .= '/start_time/'.$start_time;
                if($end_time) $quest .= '/end_time/'.$end_time;
                redirect(C('ADMIN_ROOT') . '/InterestCoupon/history_index'.$quest);
            }
        }
        
        /**
        * 券包用户发放列表
        * @date: 2017-3-29 上午10:26:05
        * @author: hui.xu
        * @param: variable
        * @return:
        */
        public function couponUserList(){
            
            $couponId = I("get.couponId",0,'int');
            $userName = I("post.userName","","strip_tags");
            $this->assign('couponId', $couponId);
            $this->assign('userName', $userName);


            $page = I('get.p', 1, 'int');   // 页码
                        
            if ($couponId>0) {
                $userId = 0;
                if($userName) {
                    $this->assign("userName",$userName);
                    $userId = M("User")->where(array("real_name"=>$userName))->getField('id');
                }
                $condition = 'coupon_id='.$couponId;
                if ($userId > 0) {
                    $condition .=' and user_id='.$userId;
                }
                $totalCnt =  M("userInterestCoupon")->where($condition)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = M("userInterestCoupon")->field('id,interest_rate,recharge_no,user_id,project_id,modify_time,status')->where($condition)->order("modify_time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    $n =1;
                    foreach ($list as $key=>$val) {  
                        $list[$key]['userInfo'] =  $this->getUserInfo($val['user_id']);
                        
                        $list[$key]['buy_type'] = '';
                        //$list[$key]['recharge_no'] = '';
                        $list[$key]['coupon_income'] = '';
                        $list[$key]['amount'] = '';
                        
                        if($list[$key]['status'] == 1) {
                            if($val['recharge_no']) {
                            
                                $rechargeLogInfo = M('RechargeLog')->field('amount,type')
                                                                    ->where('user_id='.$val['user_id']." and recharge_no='".$val['recharge_no']."'")->find();
                                
                                $list[$key]['buy_type'] = $rechargeLogInfo['type'] == 3 ?'钱包':'银行卡';
                              
                                $list[$key]['amount'] = $rechargeLogInfo['amount'] ;
                                
                                $_buy_time = date('Y-m-d',strtotime($val['modify_time']) + 86400);
                    
                                $_project_end_time = date('Y-m-d',strtotime($this->getProjectInfo($val['project_id'], 'end_time')));
                    
                                $list[$key]['coupon_income'] = $this->countInterest($rechargeLogInfo['amount'] ,$_project_end_time,$_buy_time,$val['interest_rate']);
                            }
                        }
                        
                        $list[$key]['n'] = $n++;
                    }
                }
            }
            $this->assign("title",M("userInterestCoupon")->where('coupon_id='.$couponId)->getField('title'));
            $this->assign('page', $page);
            $this->assign("list",$list);
            $this->assign('show', $Page->show());
            $this->display('couponUserList');
        }


        public function couponUserList_export(){

            $couponId = I("get.couponId",0,'int');
            $userName = I("get.userName","","strip_tags");
            $title = M("userInterestCoupon")->where('coupon_id='.$couponId)->getField('title');

            if ($couponId>0) {
                $userId = 0;
                if($userName) {
                    $this->assign("userName",$userName);
                    $userId = M("User")->where(array("real_name"=>$userName))->getField('id');
                }
                $condition = 'coupon_id='.$couponId;
                if ($userId > 0) {
                    $condition .=' and user_id='.$userId;
                }
                $list = M("userInterestCoupon")->field('id,interest_rate,recharge_no,user_id,project_id,modify_time,status')->where($condition)->order("modify_time DESC")->select();
                if($list) {
                    $n =1;
                    foreach ($list as $key=>$val) {
                        $list[$key]['userInfo'] =  $this->getUserInfo($val['user_id']);

                        $list[$key]['buy_type'] = '';
                        //$list[$key]['recharge_no'] = '';
                        $list[$key]['coupon_income'] = '';
                        $list[$key]['amount'] = '';

                        if($list[$key]['status'] == 1) {
                            if($val['recharge_no']) {

                                $rechargeLogInfo = M('RechargeLog')->field('amount,type')
                                    ->where('user_id='.$val['user_id']." and recharge_no='".$val['recharge_no']."'")->find();

                                $list[$key]['buy_type'] = $rechargeLogInfo['type'] == 3 ?'钱包':'银行卡';

                                $list[$key]['amount'] = $rechargeLogInfo['amount'] ;

                                $_buy_time = date('Y-m-d',strtotime($val['modify_time']) + 86400);

                                $_project_end_time = date('Y-m-d',strtotime($this->getProjectInfo($val['project_id'], 'end_time')));

                                $list[$key]['coupon_income'] = $this->countInterest($rechargeLogInfo['amount'] ,$_project_end_time,$_buy_time,$val['interest_rate']);
                            }
                        }

                        $list[$key]['n'] = $n++;
                    }
                }

                vendor('PHPExcel.PHPExcel');

                $objPHPExcel = new \PHPExcel();

                $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")

                    ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");

                $objPHPExcel->setActiveSheetIndex(0)->setTitle($title.'用户使用名细')
                    ->setCellValue("A1", "编号")
                    ->setCellValue("B1", "用户姓名")
                    ->setCellValue("C1", "账号")
                    ->setCellValue("D1", "状态")
                    ->setCellValue("E1", "使用时间")
                    ->setCellValue("F1", "投资金额")
                    ->setCellValue("G1", "收益金额")
                    ->setCellValue("H1", "订单号")
                    ->setCellValue("I1", "付款方式");
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
                foreach ($list as $key => $val) {
                    $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['n']);
                    $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['userInfo']['real_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['userInfo']['username']);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos, $status[$val['status']]);
                    $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['modify_time']);
                    $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['amount']);// number_format($val['amount'], 2)
                    $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['coupon_income']);//number_format($val['coupon_income'], 2)
                    $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['recharge_no']);
                    $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $val['buy_type']);
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
        
        /**
         * 计算利息
         */
        private  function countInterest($amount,$start_time,$end_time,$interest) {
           return $amount * (count_days($end_time, $start_time)) * $interest /100 / 365;
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
                $data = M("User")->field('real_name,username')->where(array('id'=>$userId))->find();
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
                    if($field) {
                        return $data[$field];
                    } else{
                        return $data;
                    }
                }
            }
            return "";
        }
        
        /**

         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getProjectInfo($pId,$field) {
            $r = "";
            if($pId != "") {
                $data = M("Project")->field('status,end_time,start_time,user_interest,title')->where(array("id"=>$pId))->find();
                if($data) {
                    if("status" == $field) {
                        if($data['status'] == 1) {
                            $r = '等待审核上线';
                        } else if($data['status'] == 2) {
                            $r = '销售中';
                        } else if($data['status'] == 3) {
                            $r = '已售完';
                        } else if($data['status'] == 4) {
                            $r = '还款中';
                        } else if($data['status'] == 5) {
                            $r = '已还款';
                        }
                    } else {
                        $r = $data[$field];
                    }
                }
            }
            return $r;
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
	 * 
	 * 券包每日数据查询
	 * 
	 */
    public function day_index() {

        $starttime = I('get.start_time', '', 'strip_tags');
        $endtime = I('get.end_time', '', 'strip_tags');

        $pay_method = I("get.pay_method", 0, "int");

        $condition = "project_id >0 and status = 1 and is_delete = 0 ";

        if (! $starttime) {
            $starttime = date('Y-m-d');
        }

        $condition .= " AND `modify_time` >= '$starttime'";

        if ($endtime) {
            $_enttime = $endtime.' 23:59:59';
            $condition .= " AND `modify_time` <= '$_enttime'";
        }

        if ($pay_method == 1) {
            $condition .= " AND recharge_no LIKE 'QB%'";
        } else if ($pay_method == 2) {
            $condition .= " AND recharge_no NOT LIKE 'QB%'";
        }

        $res = array();

        $totalCnt = M("UserInterestCoupon")->where($condition)->count();

        $Page = new \Think\Page($totalCnt, $this->pageSize);

        $list = M("UserInterestCoupon")->field("user_id,interest_rate,modify_time,recharge_no,project_id")
            ->where($condition)
            ->order("modify_time desc")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        if (! empty($list)) {

            $n = 1;

            $total_coupon_income = 0;

            foreach ($list as $val) {

                $val['n'] = $n ++;

                $val['user_info'] = $this->getUserInfo($val['user_id']);

                if (strpos($val['recharge_no'], 'QB') !== false) {
                    $val['pay_method'] = "钱包";
                } else {
                    $val['pay_method'] = "银行卡";
                }

                $val['projectName'] = $this->getProjectInfo($val['project_id'], "title");

                $val['RechargeLogInfo'] = $this->getRechargeLogInfo($val['recharge_no'], '');


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

        $this->assign("params", array(
            "pay_method" => $pay_method,
            "start_time" => $starttime,
            "end_time" => $endtime,
            "totalCnt" => $totalCnt,
            'total_coupon_income' => $total_coupon_income
        ));

        $this->assign("list", $res);
        $this->assign('show', $Page->show());
        $this->display('day_index');
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
                    
                    if(strpos($val['recharge_no'], 'QB') === false) {
                        $rows['bank_info'] = $this->getBankInfo($val['user_id'], $rows['order_info']['card_no']);
                    }
                    
                    $rows['interest_rate'] = $val['interest_rate'];
                    
                    $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                    $_project_end_time = date('Y-m-d',strtotime($this->getProjectInfo($val['project_id'], 'end_time')));
                    
                    $rows['coupon_income'] = $this->countInterest(
                                            $rows['order_info']['amount'] ,$_project_end_time,$_buy_time,$val['interest_rate']);

                    $rows['user_interest'] = $this->getProjectInfo($val['project_id'], 'user_interest');
                    $rows['modify_time'] = $val['modify_time'];
                    
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
            foreach ($res as $key => $val) {                
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['user_info']['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['user_info']['username']);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos, $val['bank_info']['bank_card_no']);
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
        
            $condition = "uic.project_id >0 and uic.status = 1 and uic.is_delete = 0 ";
             
            if(!$starttime) {
                $starttime = date('Y-m-d');
            }
             
            $condition .= " AND uic.`modify_time` >= '$starttime'";
            if($endtime) {
                $_enttime = $endtime.' 23:59:59';
                $condition .= " AND uic.`modify_time` <= '$_enttime'";
            }
            if($pay_method == 1) {
                $condition .= " AND uic.recharge_no LIKE 'QB%'";
            } else if($pay_method == 2) {
                $condition .= " AND (uic.recharge_no NOT LIKE 'QB%')";
            }
            
            
            $sql = "SELECT uic.`user_id`,uic.`interest_rate`,uic.`modify_time`,uic.`recharge_no`,uic.`project_id`,user.username,`user`.real_name,pj.title,pj.end_time,log.amount FROM `s_user_interest_coupon` as uic LEFT JOIN s_user as user ON uic.user_id = `user`.id
                LEFT JOIN s_project as pj ON uic.project_id = pj.id LEFT JOIN s_recharge_log AS `log` ON uic.recharge_no = log.recharge_no  WHERE $condition ORDER BY uic.modify_time DESC";
            
            $list = M()->query($sql);
        
            if($list){
                
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
                
                $pos = 2;
                $n = 1;
                foreach ($list as $val) {
                    
                    if (strpos($val['recharge_no'], 'QB') !== false) {
                        $val['pay_method'] = "钱包";
                    } else {
                        $val['pay_method'] = "银行卡";
                    }
        
                    //收益
                    $val['coupon_income'] = $this->countInterest(
                        $val['amount'] ,
                        date('Y-m-d',strtotime($val['end_time'])),
                        date('Y-m-d',strtotime($val['modify_time'])),
                        $val['interest_rate'] );
                    
                    $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['username']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['modify_time']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['title']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,$val['interest_rate']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$val['amount']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,number_format($val['coupon_income'],2));                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['recharge_no']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("J".$pos,$val['pay_method']);
                    $pos += 1;
                }
                unset($list);
                header("Content-Type: application/vnd.ms-excel");
                header('Content-Disposition: attachment;filename="券包每日数据管理('.date("Y-m-d H:i:s").').xls"');
                header('Cache-Control: max-age=0');
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                exit('没有记录');
            }
        
        }

        /**
         * 券包列表 excel
         *
         */
        public function history_index_excel() {
            vendor('PHPExcel.PHPExcel');

            $title = I("get.title",'','strip_tags');
            $start_time = I("get.start_time",date('Y-m-d', strtotime('-3 days')),'strip_tags');
            $end_time = I("get.end_time",'','strip_tags');

            $res = array();

            $condition = 'is_delete = 0 ';


            if ($title != "") $condition .= " AND title LIKE '$title%'";


            if ($start_time != "") {

                $_start_time = strtotime($start_time);

                $condition .= " AND UNIX_TIMESTAMP(`add_time`) >= $_start_time";

            }
            if ($end_time != "") {

                $_end_time = strtotime($end_time);

                $condition .= " AND UNIX_TIMESTAMP(`add_time`) <= $_end_time";
            }

            $totalCnt = M()->query("select count(*) as cnt from (SELECT * FROM `s_user_interest_coupon` WHERE ( $condition ) GROUP BY coupon_id ) tmp");

            $Page = new \Think\Page($totalCnt[0]['cnt'], $this->pageSize);


            $list = M('UserInterestCoupon')->field("id,title,coupon_id,interest_rate,add_time,expire_time,min_invest,min_due,scope,memo,recharge_no,type,apply_tag,act_name,source")->where($condition)->group("coupon_id")->order("id desc")->select();


            // apply_tag   适用标签，0: 普通   1:新手   2:爆款   6:活动  8:个人专享，适用多个表情时以":"隔开

            $applyTagArr = array(0=>'普通',1=>'新手',2=>'爆款',6=>'活动',8=>'个人专享');

            if(!empty($list)) {

                foreach ($list as $val) {

                    $interest_rate = $val['interest_rate'];//金额

                    if (1 == $val['scope']) {
                        $val['scope'] = '个人';
                    } else if (2 == $val['scope']) {
                        $val['scope'] = '所有';
                    }

                    $val['total'] = $total = M('UserInterestCoupon')->where('coupon_id = ' . $val['coupon_id'] . ' and is_delete = 0')->count();
                    $val['use_totla'] = $use_totla = M('UserInterestCoupon')->where('coupon_id = ' . $val['coupon_id'] . " and recharge_no !='' and status = 1")->count();

                    $val['total_amount'] = $interest_rate * $total; // 发放金额

                    // 使用金额

                    $val['use_totla_amount'] = $use_totla_amount = $interest_rate * $use_totla; // 使用金额


                    //投资金额  s_investment_detail /// 用户投资记录信息表
                    $rechargeNoWhere = array(
                        'coupon_id' => $val['coupon_id'],
                        'recharge_no' => array('neq', '')
                    );
                    $rechargeNoArr = M("UserInterestCoupon")->where($rechargeNoWhere)->getField('recharge_no', true);  // dump($rechargeNoArr);

                    if ($rechargeNoArr) {

                        //$inv_succ_arr = $totla_inv_succ = M('investment_detail')->field('inv_succ,recharge_no')->where(array('recharge_no'=>array('in',$rechargeNoArr) ))->select();

                        $invest_detail_arr = M('investment_detail')->field('id,inv_succ,recharge_no')->where(array('recharge_no' => array('in', $rechargeNoArr)))->select();


                        $inv_succ_data = $invest_detail_data = $invest_detail_id_arr = array();


                        foreach ($invest_detail_arr as $i) {
                            // $inv_succ_data[$i['recharge_no']] = $i['inv_succ'];
                            $invest_detail_data[$i['id']] = $i['inv_succ'];
                            $invest_detail_id_arr[] = $i['id'];
                        }

                        if ($invest_detail_id_arr) {
                            $duration_day_arr = M('user_due_detail')->field('duration_day,invest_detail_id')->where(array('invest_detail_id' => array('in', $invest_detail_id_arr)))->select();
                        } else {
                            $duration_day_arr = array();
                        }
                        //$inv_succ*$interest_rate*$duration_day/365

                        $inv_succ_day = 0;
                        foreach ($duration_day_arr as $d) {
                            $inv_succ_amt = isset($invest_detail_data[$d['invest_detail_id']]) ? sprintf('%.2f', $invest_detail_data[$d['invest_detail_id']]) : 0;

                            $duration_day = isset($d['duration_day']) ? $d['duration_day'] : 0;

                            $inv_succ_day += $inv_succ_amt * $duration_day;
                        }

                        $val['inv_succ'] = $inv_succ = array_sum($invest_detail_data);
                    } else {
                        $val['inv_succ'] = $inv_succ = 0;
                        $inv_succ_day = 0;
                    }


                    // 使用金额  加息收益
                    $interest_rate = $val['interest_rate']; //利率
                    $interest_rate = $interest_rate / 100; // 百分比
                    $val['use_totla_amount_inc'] = $use_totla_amount_inc = ($use_totla_amount_inc_s = $inv_succ_day * $interest_rate / 365) ? sprintf('%.2f', $use_totla_amount_inc_s) : '0.00'; // 使用金额 加息收益

                    //千元投资成本
                    if ($inv_succ) {
                        $val['thousand_inv_succ'] = ($thousand_inv_succ = $use_totla_amount_inc / $inv_succ * 1000) ? sprintf('%.2f', $thousand_inv_succ) : '0.00';
                    } else {
                        $val['thousand_inv_succ'] = '0.00';
                    }

                    //适用标签
                    $apply_tag_arr = explode(':', $val['apply_tag']);

                    $apply_tag_titie_arr = array();
                    foreach ($apply_tag_arr as $a) {
                        $apply_tag_titie_arr[] = $applyTagArr[$a];
                    }
                    $val['apply_tag_titie'] = implode(',', $apply_tag_titie_arr);

                    if ( $val['type'] != 4) { // 不等于暗道

                        $val['act_name'] = '';
                        $endStr = strrpos($val['source'],'_');
                        $key_name = substr($val['source'],0,$endStr);

                        if($key_name) {
                            $val['act_name'] = ($act_name = M('lottery_base')->where(array('key_name' => $key_name))->getField('name')) ? $act_name : '';
                        }

                    }

                    $typeArr = array('普通','平台活动','平台活动','平台活动','暗道');

                    $val['type'] = $typeArr[$val['type']];

                    $res[] = $val;
                }
                unset($list);
            }

            $fileName = '券包发放记录';
            $this->excelModel->setFileName($fileName);
            $this->excelModel->setTitles($this->history_index_titles());
            $this->excelModel->setFields($this->history_index_field());


            $this->excelModel->excelFile($res);
        }


        private function history_index_field(){
            return array(
                'id',
                'title',
                'add_time',
                'interest_rate',
                'min_invest',
                'min_due',
                'expire_time',
                'apply_tag_titie',
                'type',
                'act_name',
                'total',
                'use_totla',
                'use_totla_amount_inc.f',
                'inv_succ.f',
                'thousand_inv_succ.f'
            );
        }
        private function history_index_titles(){
            return array(
                '券包ID',
                '标题',
                '生效日期',
                '券包利率',
                '最小投资金额(元)',
                '最小投资期限(天)',
                '使用截止日期',
                '适用标签',
                '发放渠道',
                '活动名称',
                '个数',
                '使用人数',
                '使用金额',
                '带动投资金额',
                '千元投资成本'
            );
        }




        /**
         * 券包核销
         */
        public function cancel_out_index()
        {
            if (!$_POST) {
                $userObj  = M('User');
                $page     = I('get.p', 1, 'int');
                $status   = I("get.status", 0, 'int');
                $userName = trim(urldecode(I("get.real_name", '', 'strip_tags')));
                $mobile   = trim(urldecode(I("get.mobile", '', 'strip_tags')));
                if (is_numeric($mobile)) {
                    $userInfo = $userObj->where(['mobile' => $mobile])->select();
                }
                if ($userName) {
                    $userInfo = $userObj->where(['real_name' => $userName])->select();
                }
                if ($userInfo) {
                    $userId = $userInfo[0]['id'];
                    if ($status == 1000) {
                        $totalCnt = M("UserInterestCoupon")->where(['user_id' => $userId])->count();
                        $Page     = new \Think\Page($totalCnt, $this->pageSize);
                        $list     = M("UserInterestCoupon")
                            ->where(['user_id' => $userId])
                            ->order("add_time DESC")
                            ->limit($Page->firstRow . ',' . $Page->listRows)
                            ->select();
                    } else {
                        $totalCnt = M("UserInterestCoupon")->where(['user_id' => $userId, 'status' => $status])->order("id DESC")->count();
                        $Page     = new \Think\Page($totalCnt, $this->pageSize);
                        $list     = M("UserInterestCoupon")
                            ->where(['user_id' => $userId, 'status' => $status])
                            ->order("add_time DESC")
                            ->limit($Page->firstRow . ',' . $Page->listRows)
                            ->select();
                    }

                    foreach ($list as $k => $v) {
                        $list[$k]['apply_tag'] = $this->transApplyTag($v['apply_tag']);
                    }
                    $params = [
                        'totalCnt' => $totalCnt,
                        'page'     => $page,
                    ];
                    $this->assign("real_name", $userInfo[0]['real_name']);
                    $this->assign("mobile", $userInfo[0]['mobile']);
                    $this->assign("status", $status);
                    $this->assign("params", $params);
                    $this->assign('showPage', $Page->show());
                    $this->assign('list', $list);
                    $this->display('cancel_out');
                } else {
                    $this->display('cancel_out');
                }
            } else {
                $real_name = I('post.real_name', '', 'strip_tags');
                $status    = I('post.status', 0, 'int');
                $mobile    = I('post.mobile', '', 'strip_tags');
                $quest     = '/status/' . $status;
                if ($real_name) $quest .= '/real_name/' . urlencode($real_name);
                if ($mobile) $quest .= '/mobile/' . urlencode($mobile);
                redirect(C('ADMIN_ROOT') . '/InterestCoupon/cancel_out_index' . $quest);
            }
        }


        /**
         * 核销处理
         */
        public function cancel_out_handle()
        {
            $id         = I('post.id', 0, 'intval');
            $rechargeNo = I('post.recharge_no', '', 'strip_tags');
            if (!$id || !$rechargeNo) {
                echo '参数不能为空，请稍后再试';
                exit();
            }
            $rechargeLog = M("rechargeLog")->where(['recharge_no'=>$rechargeNo])->where(['status' => 2])->select();
            if(!$rechargeLog){
                echo '订单号不存在';
                exit();
            }
            $userRed              = M("UserInterestCoupon");
            $userRed->status      = 1;
            $userRed->cancel_out_no = $rechargeNo;
            $userRed->modify_time = date('Y-m-d H:i:s', time());
            $userRed->where(['id' => $id])->save();
            echo '核销成功';
            exit();

        }


        /**
         * 用户红包表apply_tag翻译
         * @param $applyTag
         * @return string
         */
        private function transApplyTag($applyTag)
        {
            if (!$applyTag) {
                return '';
            }
            //'适用标签，0: 普通   1:新手   2:爆款   6:活动  8:个人专享，适用多个表情时以":"隔开',
            $tempArr   = explode(':', $applyTag);
            $resultStr = '';
            foreach ($tempArr as $v) {
                if ($v == 0) {
                    $str = '普通';
                } else if ($v == 1) {
                    $str = '新手';
                } else if ($v == 2) {
                    $str = '爆款';
                } else if ($v == 6) {
                    $str = '活动';
                } else if ($v == 8) {
                    $str = '个人专享';
                } else {
                    $str = '';
                }
                $resultStr .= ' ' . $str;
            }
            return $resultStr;
        }

}
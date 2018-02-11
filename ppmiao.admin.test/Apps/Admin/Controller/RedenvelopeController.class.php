<?php
namespace Admin\Controller;

/**
 * 红包管理
 */
class RedenvelopeController extends AdminController {
       
    
        private $pageSize = 15;

        protected $excelModel;

        protected function _initialize(){
            $this->excelModel = new \Admin\Model\ExcelModel();
        }
    
        /**
         * 红包数据管理
         */
        public function index() {    
            
            if (!IS_POST) {
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
                
                $list = M("Project")->field('id,title,status,end_time,user_interest')
                                    ->where($cond)->order("id desc")
                                    ->limit($Page->firstRow . ',' . $Page->listRows)
                                    ->select();
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
                        
                        $_list = M('UserRedenvelope')->field('amount,recharge_no,user_id,modify_time')->where("project_id=".$val['id'] ." and status = 1 and recharge_no != ''")->select();
                        
                        $val['use_cnt'] = count($_list);
                        
                        $val['redpacket_amount'] = 0;
                        $val['interest'] = 0;
                        
                        //$_end_time = date('Y-m-d',strtotime($val['end_time']));
                        
                        $val['end_time'] = date('Y-m-d',strtotime($val['end_time']));
                        
                        if($_list) {
                            
                            foreach ($_list as $_val) {
                                
                                $_buy_time = date('Y-m-d',strtotime($_val['modify_time']));
                                
                                $val['redpacket_amount'] += $_val['amount'];
                                $val['interest'] += $_val['amount'] * (count_days($val['end_time'], $_buy_time)) * $val['user_interest'] / 100 / 365;
                            }
                        }
                        $val['total_amount'] = $val['redpacket_amount'] + $val['interest'];
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
                $this->assign("params",$params);
                $this->assign("list",$res);
                $this->assign('showPage',$Page->show());
                $this->display('index');
            }else{
                $key = I('post.key', '', 'strip_tags');
                $status = I('post.status', 0, 'int');
                $start_time = I('post.start_time', '', 'strip_tags');
                $quest = '/status/'.$status;
                if($key) $quest .= '/s/'.urlencode($key);
                if($start_time)$quest .= '/time/'.urlencode($start_time);
                redirect(C('ADMIN_ROOT') . '/redenvelope/index'.$quest);
            }
            
        }
        
        
        /**
         * 项目红包使用列表
         * 
         */
        
        public function user_packet_list() {
            $projectId = I("get.pid",0,'int');            
            $userName = trim(I("post.userName","","strip_tags"));            
            $page = I('get.p', 1, 'int');   // 页码                        
            
            $res = array();            
            if ($projectId>0) { 
                
                $cond = 'project_id='.$projectId .' and status = 1';
                
                $userId = "";                
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
                
                $totalCnt =  M("UserRedenvelope")->where($cond)->count();                  
                $Page = new \Think\Page($totalCnt, $this->pageSize);                
                $list = M("UserRedenvelope")->field('id,title,amount,recharge_no,user_id,project_id,modify_time')->where($cond)->order("modify_time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                                
                if(!empty($list)) {
                    $n = 0;
                    foreach ($list as $val) {                        
                        $rows['n'] = ++ $n;                    
                        $rows['real_name'] = $this->getUserInfo($val['user_id'], "real_name");                        
                        $rows['user_name'] = $this->getUserInfo($val['user_id'], "username");                        
                        $rows['card_no'] = $this->getRechargeLogInfo($val['recharge_no'], "card_no");                        
                        $rows['amount'] = $this->getRechargeLogInfo($val['recharge_no'], "amount");                        
                        $rows['bank_name'] = $this->getBankName($val['user_id'], $rows['card_no']);                        
                        
                        $_user_interest = $this->getProjectInfo($val['project_id'], "user_interest");
                        $_end_time = $this->getProjectInfo($val['project_id'], "end_time");                        
                        
                        $_end_time = date('Y-m-d',strtotime($_end_time));
                        $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                        
                        //本金利息
                        $rows['interest'] = $this->countInterest($rows['amount'] ,$_buy_time,$_end_time,$_user_interest);                        
                        $rows['status'] = $this->getProjectInfo($val['project_id'], "status");                        
                        $rows['packet_amount'] = $val['amount'];                        
                        //红包利息
                        $rows['packet_interest'] =  $this->countInterest($rows['packet_amount'],$_buy_time,$_end_time,$_user_interest);
                        
                        //红包+利息 
                        $rows['total_amount'] = $rows['packet_amount'] + $rows['packet_interest'];                        
                        $rows['modify_time'] = date('Y-m-d H:i:s',strtotime($val['modify_time']));
                        
                        $res[] = $rows;
                        
                    }
                    unset($list);
                }
                
            }
            
            $this->assign("title",$this->getProjectInfo($projectId,"title"));
            $this->assign('page', $page);
            $this->assign("list",$res);
            $this->assign('show', $Page->show());
            $this->display('user_packet_list');
            
        }
        
        /**
         * 红包分组列表
         * 
         */
        public function packetlist() {
            if(!IS_POST) {
                $title = I("get.title","",'strip_tags');
                $start_time = I("get.start_time","",'strip_tags');
                $end_time = I("get.end_time","",'strip_tags');

                $title = urldecode($title);

                $page = I('get.p', 1, 'int');   // 页码

                $res = array();

                $condition = "1 = 1";

                if ($title) $condition .= " AND title LIKE '$title%'";
                else  $title = '';


                if ($start_time != "") {

                    $_start_time = strtotime($start_time);

                    $condition .= " AND UNIX_TIMESTAMP(`create_time`) >= $_start_time";

                }
                if ($end_time != "") {

                    $_end_time = strtotime($end_time);

                    $condition .= " AND UNIX_TIMESTAMP(`create_time`) <= $_end_time";
                }

                $totalCnt = M()->query("select count(*) as cnt from (SELECT * FROM `s_user_redenvelope` WHERE ( $condition ) GROUP BY title ) tmp");

                //echo "select count(*) as cnt from (SELECT * FROM `s_user_redenvelope` WHERE ( $condition ) GROUP BY title ) tmp";

                $Page = new \Think\Page($totalCnt[0]['cnt'], $this->pageSize);

                // apply_tag   适用标签，0: 普通   1:新手   2:爆款   6:活动  8:个人专享，适用多个表情时以":"隔开

                $applyTagArr = array(0=>'普通',1=>'新手',2=>'爆款',6=>'活动',8=>'个人专享');

                //type 发放渠道 , 0新手，1后台放发，2抽奖，3活动
                // 活动名称 关联 project_id
                $list = M("UserRedenvelope")->field("id,title,content,amount,create_time,expire_time,min_invest,min_due,scope,apply_tag,project_id,type,recharge_no,act_name,source")->where($condition)->group("title")->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();

                if(!empty($list)) {
                    $n = 0;
                    foreach ($list as $val) {
                        $amount = $val['amount'];

                        if(1 == $val['scope']) {
                            $val['scope'] = '个人';
                        } else if(2 == $val['scope']) {
                            $val['scope'] = '所有';
                        } else {
                            $val['scope'] = '活动配置';
                        }
                        $val['n'] = ++$n;

                        $val['total'] = $total = M('UserRedenvelope')->where(array('title'=>$val['title']))->count();
                        $val['use_totla'] = $use_totla =  M('UserRedenvelope')->where("title = '".$val['title'] ."' and recharge_no !='' and status = 1")->count();

                        $val['total_amount'] = $amount*$total; // 发放金额
                        $val['use_totla_amount'] = $use_totla_amount = $amount*$use_totla; // 使用金额

                        //投资金额  s_investment_detail /// 用户投资记录信息表
                        $rechargeNoWhere = array(
                            'title'=>$val['title'],
                            'recharge_no'=>array('neq','')
                        );
                        $rechargeNoArr = M("UserRedenvelope")->where($rechargeNoWhere)->getField('recharge_no',true);


                        if($rechargeNoArr) {
                            $val['inv_succ'] = $inv_succ = ($totla_inv_succ = M('investment_detail')->where(array('recharge_no' => array('in',$rechargeNoArr)))->sum('inv_succ')) ? $totla_inv_succ - $use_totla_amount : 0;
                        }else{
                            $val['inv_succ'] = $inv_succ = 0;
                        }
                        //千元投资成本
                        if($inv_succ){
                            $val['thousand_inv_succ'] = ($thousand_inv_succ = $use_totla_amount/$inv_succ*1000)?sprintf('%.2f',$thousand_inv_succ):'0.00';
                        }else{
                            $val['thousand_inv_succ'] = '0.00';
                        }

                        //适用标签
                        $apply_tag_arr = explode(':',$val['apply_tag']);


                        $apply_tag_titie_arr = array();
                        foreach ($apply_tag_arr as $a){
                            $apply_tag_titie_arr[] = $applyTagArr[$a];
                        }
                        $val['apply_tag_titie'] = implode(',',$apply_tag_titie_arr);


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



                $param = array("title"=>$title,"start_time"=>$start_time,"end_time"=>$end_time);
                $this->assign("params",$param);
                $this->assign('page', $page);
                $this->assign("list",$res);
                $this->assign('show', $Page->show());
                $this->display('packet_list');
            }else{
                $title = I('post.title', '0', 'strip_tags');
                $start_time = trim(I('post.start_time', '-'));
                $end_time = trim(I('post.end_time', '-'));
                if(!$title) $title = 0;
                $quest = '/title/'.$title;
                    //$quest = '/chn/'.$chn;
                if($start_time) $quest .= '/start_time/'.$start_time;
                if($end_time) $quest .= '/end_time/'.$end_time;
                redirect(C('ADMIN_ROOT') . '/redenvelope/packetlist'.$quest);
            }
        }
        
        
        /**
         * 计算利息
         */
        private  function countInterest($amount,$start_time,$end_time,$interest) {
           return $amount * (count_days($end_time, $start_time)) * $interest / 100 / 365;
        }
        
        
        /**
         * 取银行名字
         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getBankName($userId,$cardNo) {
            
            if($userId != "" && $cardNo != "") {
                
                $data = M("UserBank")->field('bank_name')->where(array("user_id"=>$userId,"bank_card_no"=>$cardNo))->find();
                
                if(!empty($data)) {
                    return $data['bank_name'];
                }
                
            }
            return "";
        }
        
        /**
         * 取用户的真实姓名
         * @param unknown $user
         * @param unknown $cardNo
         */
        private function getUserInfo($userId,$field) {
        
            if($userId != "") {
                $data = M("User")->field('real_name,username')->where('id='.$userId)->find();
                if(!empty($data)) {
                    if($field == "real_name") {
                        return $data['real_name'];
                    } else{
                        return $data['username'];
                    }
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
                    
                    if($field == "amount") {
                        return $data['amount'];
                    } else {
                        return $data['card_no'];
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
                
                if(!empty($data)) {
                    
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
                    } else if("end_time" == $field) {
                        
                        $r = $data['end_time'];
                        
                    } else if("start_time" == $field) {
                        
                        $r = $data['start_time'];
                        
                    } else if("user_interest" == $field) {
                        
                        $r = $data['user_interest'];
                    } else if("title" == $field) {
                        
                        $r = $data['title'];
                    }
                }
            }
            return $r;
        }
        
        
        
        /**
         * 发放红包
         */
        
        public function add(){
		
            if(!IS_POST){
				$this->display('add');         
			}else{
			    
			    $row['title'] = trim(I('post.title','','strip_tags'));		
				
				$row['content'] = trim(I('post.content','','strip_tags'));
				
				$row['amount'] = trim(I('post.amount', 0, 'float'));//金额
				
				$row['min_invest'] = trim(I('post.min_invest', 0, 'float'));	//红包最小投资金额：
				
				$row['min_due'] = trim(I('post.min_due', 0, 'int'));	//红包最小投资期限：
				
				$row['due_time'] = trim(I('post.due_date', 0, 'int'));	//红包到期天数：	

				$row['source'] = trim(I('post.source', '', 'strip_tags'));
                
				$send_scope = trim(I('post.send_scope', 0, 'int'));//发放范围

                $act_name = trim(I('post.act_name', '', 'strip_tags')); //活动名称
                $type = 4; // 现在只要暗道 //trim(I('post.type', 0, 'int')); //红包发放渠道  个人/平台活动/微信/暗道
				
				
				$tag_arr = $_REQUEST['tag_id'];
				
								
				if (!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));

                if (!$act_name) $this->ajaxReturn(array('status' => 0, 'info' => '请输入活动名称'));
				
				/*
				if(M('UserRedenvelope')->where(array('title'=>$row['title']))->count()) {
				    $this->ajaxReturn(array('status' => 0, 'info' => '红包名称` '.$row['title'].'`已经存在！'));
				}
				*/
				
				if (!$row['content']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入内容'));
				
				if (!$row['amount'] || $row['amount'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入金额'));
				
				if (!$row['min_due']  || $row['min_due'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资金额'));
				
				if (!$row['due_time'] || $row['due_time'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资期限'));
				
				
				if(!$tag_arr) {
				    $this->ajaxReturn(array(
				        'status' => 0,
				        'info' => '请选择标签'
				    ));
				}
				
				$row['apply_tag'] = implode(':',$tag_arr);
				
				$row['create_time'] = trim(I('post.create_time','','strip_tags'));
				if(!$row['create_time']) {
				    $row['create_time'] = date("Y-m-d 00:00:00");
				}
				
				//红包到期天数 等于当前时间 + $due_time					
				$row['expire_time'] = date("Y-m-d 23:59:59",strtotime($row['create_time'])+($row['due_time']-1) * 86400);	
				
				$row['add_user_id'] = $row['modify_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];
				
				$row['status'] = 0;
				
				$row['type'] = $type;
                $row['act_name'] = $act_name;
				
				$row['add_time'] = time();
				
				
				//$send_scope 1 全部用户 ，2 指定用户
				if(2 == $send_scope) {

				    $userIdStr = I('post.userId', '', 'strip_tags');//发放范围

				    if($userIdStr == "") {
				        $this->ajaxReturn(array('status' => 0, 'info' => '请添加需要指定发放的用户!'));
				    }
				    
				    $userIdArr = explode("#",$userIdStr);
				    $row['scope'] = 1;
				    $res = false;
				    
				    M("UserRedenvelope")->startTrans();
				    
				    foreach ($userIdArr as $val) {      

				        $row['user_id'] = $val;				        
				        
				        $res = M("UserRedenvelope")->add($row);		

				    }
				    				    
			        if($res) {			            
			            M("UserRedenvelope")->commit();			            
			            $this->ajaxReturn(array('status' => 1, 'info'=>C('ADMIN_ROOT').'/redenvelope/packetlist'));			            
			        }else{			            
			            M("UserRedenvelope")->rollback();			            
			            $this->ajaxReturn(array('status' => 0, 'info' => "发放失败"));
			        }    
				    exit;
				}
				else{
				    
				    $sql = "INSERT INTO `s_user_redenvelope`(id,title,content,user_id,recharge_no,amount,create_time,add_user_id,modify_time,modify_user_id,sms_msgid,min_due,min_invest,project_id,expire_time,
				        
				                status,type,scope,is_read,add_time,source,apply_tag,act_name)
				    
                            SELECT null, '".$row['title']."', '".$row['content']."',id,'', '".$row['amount']."', '".$row['create_time']."','".$row['add_user_id']."',null,'".$row['add_user_id']."', '',
             
                            '".$row['min_due']."', '".$row['min_invest']."','','".$row['expire_time'] ."',0,1,2,0,unix_timestamp(), '".$row['source']."','".$row['apply_tag']."','".$row['act_name']."' FROM s_user";
				    
				    if( M()->execute($sql)){
				        $this->ajaxReturn(array('status' => 1, 'info'=>C('ADMIN_ROOT').'/redenvelope/packetlist'));	
				    } else {
				        $this->ajaxReturn(array('status' => 0, 'info' => "发放失败"));
				    }
				    exit;
				    
				}
				
			}
		}
		
		
		
		public function addBatch(){
		
		    if(!IS_POST){
		        $this->display('add2');
		    }else{
		        $row['title'] = trim(I('post.title','','strip_tags'));		
		        $row['content'] = trim(I('post.content','','strip_tags'));		
		        $row['amount'] = trim(I('post.amount', 0, 'float'));//金额		
		        $row['min_invest'] = trim(I('post.min_invest', 0, 'float'));	//红包最小投资金额：		
		        $row['min_due'] = trim(I('post.min_due', 0, 'int'));	//红包最小投资期限：		
		        $row['due_time'] = trim(I('post.due_date', 0, 'int'));	//红包到期天数：		
		        $tag_arr = $_REQUEST['tag_id'];	
		
		        if (!$row['title']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入标题'));		
		        if (!$row['content']) $this->ajaxReturn(array('status' => 0, 'info' => '请输入内容'));		
		        if (!$row['amount'] || $row['amount'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入金额'));		
		        if (!$row['min_due']  || $row['min_due'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资金额'));		
		        if (!$row['due_time'] || $row['due_time'] <=0) $this->ajaxReturn(array('status' => 0, 'info' => '请输入红包最小投资期限'));		
		        if(!$tag_arr) {
		            $this->ajaxReturn(array(
		                'status' => 0,
		                'info' => '请选择标签'
		            ));
		        }		
		        $row['apply_tag'] = implode(':',$tag_arr);		
		        $row['create_time'] = trim(I('post.create_time','','strip_tags'));
		        if(!$row['create_time']) {
		            $row['create_time'] = date("Y-m-d 00:00:00");
		        }
		
		        //红包到期天数 等于当前时间 + $due_time
		        $row['expire_time'] = date("Y-m-d 23:59:59",strtotime($row['create_time'])+($row['due_time']-1) * 86400);		
		        $row['add_user_id'] = $row['modify_user_id'] =  $_SESSION[ADMIN_SESSION]['uid'];		
		        $row['status'] = 0;		
		        $row['type'] = 1;		
		        $row['add_time'] = time();	
		
	            $userStr = I('post.user_list', '', 'strip_tags');//发放范围
	
	            if($userStr == "") {
	                $this->ajaxReturn(array('status' => 0, 'info' => '发放用户不能为空'));
	            }
	
	            $userIdArr = explode("#",$userStr);
	            $row['scope'] = 1;
	            $res = false;
	
	            M("UserRedenvelope")->startTrans();
	
	            foreach ($userIdArr as $val) {
	                $user_id = M('User')->where("username='$val'")->getField('id');
	                if($user_id) {
	                    $row['user_id'] = $user_id;
	                    $res = M("UserRedenvelope")->add($row);
	                    unset($row['user_id']);
	                }
	            }
	            if($res) {
	                M("UserRedenvelope")->commit();
	                $this->ajaxReturn(array('status' => 1, 'info'=>C('ADMIN_ROOT').'/redenvelope/packetlist'));
	            }else{
	                M("UserRedenvelope")->rollback();
	                $this->ajaxReturn(array('status' => 0, 'info' => "发放失败"));
	            }
	            exit;
		    }
		}
		
		
		/**
		 * 根据手机号码，查用户uid
		 */
		
		public function userinfo(){
			
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
		 * 红包每日数据查询
		 * 
		 */
		public function day_packetlist(){
		    
		   $starttime = I('get.start_time', '', 'strip_tags');
		   $endtime = I('get.end_time', '', 'strip_tags');
		   
		   $pay_method = I("get.pay_method",0,"int"); 
		    
		   $condition = "status = 1";
		   
		   if(!$starttime) {
                $starttime = date('Y-m-d');
           }
		   
           $condition .= " AND `modify_time` >= '$starttime'";
		   
           if($endtime) {
               $_enttime = $endtime.' 23:59:59';
               $condition .= " AND `modify_time` <= '$_enttime'";
           }
          		   
		   if($pay_method == 1) {
		       
		       $condition .= " AND recharge_no LIKE 'QB%'";
		       
		   } else if($pay_method == 2) {

		       $condition .= " AND recharge_no NOT LIKE 'QB%'";
		   }
		   
		   $res = array();
		   
		   $totalCnt =  M("UserRedenvelope")->where($condition)->count();
		   
		   $Page = new \Think\Page($totalCnt, $this->pageSize);
		   
		   $list = M("UserRedenvelope")->field("user_id,amount,modify_time,recharge_no,project_id")->where($condition)->order("modify_time desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
		   
		   if(!empty($list)) {
		      
		       $n = 1;
		       foreach ($list as $val) {
		   
		           $userInfo = M("User")->field("username,real_name")->where("id=".$val['user_id'])->find();
		           
		           if($userInfo) {
		               $val['username'] = $userInfo['username'];
		               $val['real_name'] = $userInfo['real_name'];
		           }
		           
		           $val['n'] = $n++;
		           
		           if(strpos($val['recharge_no'], 'QB') !== false) {
		               $val['pay_method'] = "钱包";
		           } else {
		               $val['pay_method'] = "银行卡";
		           }
		           
		           $val['modify_time'] = date("Y-m-d H:i:s",strtotime($val['modify_time']));
		           $val['projectName'] = $this->getProjectInfo($val['project_id'], "title");
		           
		           $res[] = $val;
		       }
		       unset($list);
		   }
		   
		   
		   $totalAmount = M("UserRedenvelope")->where($condition)->sum('amount');
		   
		   $this->assign("params", array(
                "pay_method" => $pay_method,
                "start_time" => $starttime,
                "end_time" => $endtime,
                "totalAmount" => $totalAmount,
		        "totalCnt" =>$totalCnt
            ));
		   
		   $this->assign("list",$res);
		   $this->assign('show', $Page->show());
		   $this->display('day_packet_list');
		    
		}
		
        /**
         *
         * @param unknown $project_id            
         */
        public function exportExcel()
        {
            
            $projectId = I("get.projectId","0",'int');
            
            if(!$projectId) {
                
                $this->ajaxReturn(array('status' => 0, 'info' => "请选择要导出记录"));
            }
            
            $res = array();
                
            $list = M("UserRedenvelope")->field("id,title,amount,recharge_no,user_id,project_id,modify_time")->where(array("project_id"=>$projectId,"status"=>1))->select();
            
            if(!empty($list)) {
            
                $n = 0;
                
                foreach ($list as $val) {
                
                    $rows['n'] = ++ $n;
                    
                    $rows['real_name'] = $this->getUserInfo($val['user_id'], "real_name");
                    
                    $rows['user_name'] = $this->getUserInfo($val['user_id'], "username");
                    
                    $rows['card_no'] = $this->getRechargeLogInfo($val['recharge_no'], "card_no");
                    
                    $rows['amount'] = $this->getRechargeLogInfo($val['recharge_no'], "amount");
                    
                    $rows['bank_name'] = $this->getBankName($val['user_id'], $rows['card_no']);
                    
                    
                    $_user_interest = $this->getProjectInfo($val['project_id'], "user_interest");
                    $_end_time = $this->getProjectInfo($val['project_id'], "end_time");
                    
                    $_end_time = date('Y-m-d',strtotime($_end_time));
                    $_buy_time = date('Y-m-d',strtotime($val['modify_time']));
                    
                    
                    //本金利息
                    $rows['interest'] = $this->countInterest($rows['amount'] ,$_buy_time,$_end_time,$_user_interest);
                    
                    $rows['status'] = $this->getProjectInfo($val['project_id'], "status");
                    
                    $rows['packet_amount'] = $val['amount'];
                    
                    //红包利息
                    $rows['packet_interest'] =  $this->countInterest($rows['packet_amount'],$_buy_time,$_end_time,$_user_interest);
                    
                    //红包+利息 
                    $rows['total_amount'] = $rows['packet_amount'] + $rows['packet_interest'];
                    
                    $rows['modify_time'] = date('Y-m-d H:i:s',strtotime($val['modify_time']));
                    
                    $res[] = $rows;
                }
                
            }
        
            
            if(empty($res)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "没有记录"));
            }
            
            vendor('PHPExcel.PHPExcel');
            
            $objPHPExcel = new \PHPExcel();
            
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")
            
            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('用户使用名细')->setCellValue("A1", "编号")->setCellValue("B1", "用户姓名")
            
            ->setCellValue("C1", "账号")->setCellValue("D1", "银行卡")->setCellValue("E1", "发卡银行")
            
            ->setCellValue("F1", "投次本金(元)")->setCellValue("G1", "支付利息(元)")->setCellValue("H1", "状态")
            
            ->setCellValue("I1", "使用红包金额(元)")->setCellValue("J1", "红包利息(元)")->setCellValue("K1", "红包以及利息总额");
            
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(18);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            
            
            
            // 设置列表值
            $pos = 2;
            
            $n = 1;
            
            foreach ($res as $key => $val) {                

               
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n);
                
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
                
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['user_name']);
                
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos, $val['card_no']);
                
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['bank_name']);
                
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, number_format($val['amount'], 2));
                
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($val['interest'], 2));
                
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['status']);
                
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format($val['packet_amount'], 2));
                
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, number_format($val['packet_interest'], 2));
                
                $objPHPExcel->getActiveSheet()->setCellValue("K".$pos, number_format($val['total_amount'], 2));
                
                
                $pos += 1;
                
                $n++;
            }
            
            ob_end_clean();//清除缓冲区,避免乱码
            
            
            header("Content-Type: application/vnd.ms-excel");
            
            header('Content-Disposition: attachment;filename="红包使用明细('.date("Y-m-d").').xls"');
            
            header('Cache-Control: max-age=0');
            
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            
            $objWriter->save('php://output');
            
            exit;
             
        }
		
		
        
        /**
         *
         * 红包每日数据导出excel
         *
         */
        
        /*
        public function day_packet_ExportExcel(){
        
            $starttime = I('get.start_time', '', 'strip_tags');
            $endtime = I('get.end_time', '', 'strip_tags');
             
            $pay_method = I("get.pay_method",0,"int");
        
            $condition = "status = 1";
             
            if(!$starttime) {
                $starttime = date('Y-m-d');
            }
             
            $condition .= " AND `modify_time` >= '$starttime'";
            
            if($endtime) {
                $_enttime = $endtime.' 23:59:59';
                $condition .= " AND `modify_time` <= '$_enttime'";
            }
             
            if($pay_method == 1) {
                 
                $condition .= " AND recharge_no LIKE 'QB%'";
                 
            } else if($pay_method == 2) {
        
                $condition .= " AND recharge_no NOT LIKE 'QB%'";
            }
             
            $res = array();
            
            $list = M("UserRedenvelope")->field("user_id,amount,modify_time,recharge_no,project_id")->where($condition)->order("modify_time desc")->select();
             
            if(!empty($list)) {
                foreach ($list as $val) {
                    $val['uinfo'] = M("User")->field("username,real_name")->where("id=".$val['user_id'])->find();
                    
                    if(strpos($val['recharge_no'], 'QB') !== false) {
                        $val['pay_method'] = "钱包";
                    } else {
                        $val['pay_method'] = "银行卡";
                    }
                    $val['projectName'] = $this->getProjectInfo($val['project_id'], "title");
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
            
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('红包每日数据管理')
                ->setCellValue("A1", "序号")
                ->setCellValue("B1", "账号")
                ->setCellValue("C1", "姓名")
                ->setCellValue("D1", "红包金额")
                ->setCellValue("E1", "订单号")
                ->setCellValue("F1", "使用日期")
                ->setCellValue("G1", "付款方式")
                ->setCellValue("H1", "项目期数");
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            
            // 设置列表值
            $pos = 2;
            
            $n = 1;
            
            foreach ($res as $key => $val) {
            
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['uinfo']['username']);
            
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['uinfo']['real_name']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['amount'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''.$val['recharge_no'],\PHPExcel_Cell_DataType::TYPE_STRING);
            
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,date('Y-m-d H:i:s',strtotime($val['modify_time'])));
            
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$val['pay_method']);
            
                $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['projectName']);
            
                $pos += 1;
            }
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="红包每日数据管理('.date("Y-m-d H:i:s").').xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
            
        }
        
        */
        
        
        /*
         * 每日明细导出
         * SELECT due.`id`, due.`user_id`, due.`project_id`, due.`invest_detail_id`, due.`due_capital`
	, due.`due_interest`, due.`from_wallet`, due.`add_time`, due.`due_time`, due.`red_amount`
	, `user`.`username`, `user`.`real_name`, `user`.`card_no`, pj.title
FROM `s_user_due_detail` due LEFT JOIN s_user user ON due.user_id = `user`.id LEFT JOIN s_project pj ON due.project_id = pj.id
WHERE due.user_id > 0
	AND due.add_time > '2017-04-01 00:00:00.0000' and due.add_time <'2017-05-01'
ORDER BY due.id ASC
         * 
         * 
         * 
         */
        /**
         *
         * new 的导出红包每日数据导出excel
         *
         */
        public function day_packet_ExportExcel(){
        
            $starttime = I('get.start_time', '', 'strip_tags');
            $endtime = I('get.end_time', '', 'strip_tags');
             
            $pay_method = I("get.pay_method",0,"int");  
                  
            $condition = "red.`status` = 1";
            if(!$starttime) {
                $starttime = date('Y-m-d');
            }
            $condition .= " AND red.`modify_time` >= '$starttime'";
        
            if($endtime) {
                $_enttime = $endtime.' 23:59:59';
                $condition .= " AND red.`modify_time` <= '$_enttime'";
            }
             
            if($pay_method == 1) {
                $condition .= " AND red.`recharge_no` LIKE 'QB%'";
            } else if($pay_method == 2) {
                $condition .= " AND red.`recharge_no` NOT LIKE 'QB%'";
            }
            
            $res = array();
            $sql = "SELECT red.`amount`,red.`modify_time`,red.`recharge_no`,user.username,user.real_name,pj.title FROM ".C('DB_PREFIX')."user_redenvelope AS red
                    LEFT JOIN ".C('DB_PREFIX')."user AS user ON red.user_id = user.id LEFT JOIN ".C('DB_PREFIX')."project AS pj ON red.project_id = pj.id WHERE $condition ORDER BY red.modify_time DESC";
            
            $list = M()->query($sql);
            
            if(!empty($list)) {
                
                vendor('PHPExcel.PHPExcel');
                $objPHPExcel = new \PHPExcel();
                $objPHPExcel->getProperties()->setCreator("票票喵票据")
                            ->setLastModifiedBy("票票喵票据")->setTitle("title")->setSubject("subject")
                            ->setDescription("description")->setKeywords("keywords")->setCategory("Category");
                
                $objPHPExcel->setActiveSheetIndex(0)->setTitle('红包每日数据管理')
                            ->setCellValue("A1", "序号")
                            ->setCellValue("B1", "账号")
                            ->setCellValue("C1", "姓名")
                            ->setCellValue("D1", "红包金额")
                            ->setCellValue("E1", "订单号")
                            ->setCellValue("F1", "使用日期")
                            ->setCellValue("G1", "付款方式")
                            ->setCellValue("H1", "项目期数");
                
                $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
                
                // 设置列表值
                $pos = 2;
                $n = 1;
                
                foreach ($list as $val) {
                    if(strpos($val['recharge_no'], 'QB') !== false) {
                        $val['pay_method'] = "钱包";
                    } else {
                        $val['pay_method'] = "银行卡";
                    }                    
                    $objPHPExcel->getActiveSheet()->setCellValue("A".$pos,$n++);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['username']);                    
                    $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['amount'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,''.$val['recharge_no'],\PHPExcel_Cell_DataType::TYPE_STRING);                    
                    $objPHPExcel->getActiveSheet()->setCellValue("F".$pos,date('Y-m-d H:i:s',strtotime($val['modify_time'])));                    
                    $objPHPExcel->getActiveSheet()->setCellValue("G".$pos,$val['pay_method']);                    
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['title']);
                    $pos += 1;
                }
                unset($list);
                header("Content-Type: application/vnd.ms-excel");
                header('Content-Disposition: attachment;filename="红包每日数据管理('.date("Y-m-d H:i:s").').xls"');
                header('Cache-Control: max-age=0');
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                exit('没有数据');
            }
        }
		
        
        /**
         * 券包用户发放列表
         * @date: 2017-3-29 上午10:26:05
         * @author: hui.xu
         * @param: variable
         * @return:
         */
        public function redUserList(){
        
            $redTitle =urldecode(I("get.redTitle","",'strip_tags'));
            $userName = I("post.userName","","strip_tags");

            $page = I('get.p', 1, 'int');   // 页码
        
            if ($redTitle) {
                $userId = 0;
                if($userName) {
                    $this->assign("userName",$userName);
                    $userId = M("User")->where(array("real_name"=>$userName))->getField('id');
                }
                $condition = "title='$redTitle'";
                if ($userId > 0) {
                    $condition .=' and user_id='.$userId;
                }


                $totalCnt =  M("userRedenvelope")->where($condition)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = M("userRedenvelope")->field('id,amount,recharge_no,user_id,project_id,modify_time,status')->where($condition)->order("modify_time DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    $n =1;
                    foreach ($list as $key=>$val) {
                        $list[$key]['userInfo'] =  M("User")->field('real_name,username')->where('id='.$val['user_id'])->find();
        
                        $list[$key]['buy_type'] = '';
                        //$list[$key]['recharge_no'] = '';
                        $list[$key]['amount'] = '';
        
                        if($val['recharge_no']) {
        
                            $rechargeLogInfo = M('RechargeLog')->field('amount,type')
                                ->where('user_id='.$val['user_id']." and recharge_no='".$val['recharge_no']."'")->find();
        
                            $list[$key]['buy_type'] = $rechargeLogInfo['type'] == 3 ?'钱包':'银行卡';
        
                            $list[$key]['amount'] = $rechargeLogInfo['amount'] ;
        
                         }
        
                        $list[$key]['n'] = $n++;
                    }
                }
            }
            $this->assign("title",$redTitle);
            $this->assign('page', $page);
            $this->assign("list",$list);
            $this->assign('show', $Page->show());
            $this->display('redUserList');
        }

    ///excel 导出
    public function redUserList_export(){

        $userName = I("get.userName","","strip_tags");
        $title = $redTitle =  I("get.redTitle","","strip_tags");

        if ($redTitle) {
            $userId = 0;
            if($userName) {
                $this->assign("userName",$userName);
                $userId = M("User")->where(array("real_name"=>$userName))->getField('id');
            }
            $condition = "title='$redTitle'";
            if ($userId > 0) {
                $condition .=' and user_id='.$userId;
            }
           // $totalCnt =  M("userRedenvelope")->where($condition)->count();
          //  $Page = new \Think\Page($totalCnt, $this->pageSize);
            $list = M("userRedenvelope")->field('id,amount,recharge_no,user_id,project_id,modify_time,status')->where($condition)->order("modify_time DESC")->select(); // limit($Page->firstRow . ',' . $Page->listRows)->
            if($list) {
                $n =1;
                foreach ($list as $key=>$val) {
                    $list[$key]['userInfo'] =  M("User")->field('real_name,username')->where('id='.$val['user_id'])->find();

                    $list[$key]['buy_type'] = '';
                    //$list[$key]['recharge_no'] = '';
                    $list[$key]['amount'] = '';

                    if($val['recharge_no']) {

                        $rechargeLogInfo = M('RechargeLog')->field('amount,type')
                            ->where('user_id='.$val['user_id']." and recharge_no='".$val['recharge_no']."'")->find();

                        $list[$key]['buy_type'] = $rechargeLogInfo['type'] == 3 ?'钱包':'银行卡';

                        $list[$key]['amount'] = $rechargeLogInfo['amount'] ;

                    }

                    $list[$key]['n'] = $n++;
                }
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
            ->setCellValue("G1", "订单号")
            ->setCellValue("H1", "付款方式");
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
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['amount']); //number_format($val['amount'], 2)
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['recharge_no']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['buy_type']);
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

    /**
     * 红包分组列表 excel
     *
     */
    public function packetlist_excel() {
        vendor('PHPExcel.PHPExcel');

        $title = I("get.title","",'strip_tags');
        $start_time = I("get.start_time","",'strip_tags');
        $end_time = I("get.end_time","",'strip_tags');


        $page = I('get.p', 1, 'int');   // 页码

        $res = array();

        $condition = "1 = 1";

        if ($title != "") $condition .= " AND title LIKE '$title%'";


        if ($start_time != "") {

            $_start_time = strtotime($start_time);

            $condition .= " AND UNIX_TIMESTAMP(`create_time`) >= $_start_time";

        }
        if ($end_time != "") {

            $_end_time = strtotime($end_time);

            $condition .= " AND UNIX_TIMESTAMP(`create_time`) <= $_end_time";
        }

        $totalCnt = M()->query("select count(*) as cnt from (SELECT * FROM `s_user_redenvelope` WHERE ( $condition ) GROUP BY title ) tmp");

        //echo "select count(*) as cnt from (SELECT * FROM `s_user_redenvelope` WHERE ( $condition ) GROUP BY title ) tmp";

        $Page = new \Think\Page($totalCnt[0]['cnt'], $this->pageSize);

        // apply_tag   适用标签，0: 普通   1:新手   2:爆款   6:活动  8:个人专享，适用多个表情时以":"隔开

        $applyTagArr = array(0=>'普通',1=>'新手',2=>'爆款',6=>'活动',8=>'个人专享');

        //type 发放渠道 , 0新手，1后台放发，2抽奖，3活动
        // 活动名称 关联 project_id
        $list = M("UserRedenvelope")->field("id,title,content,amount,create_time,expire_time,min_invest,min_due,scope,apply_tag,project_id,type,recharge_no,act_name,source")->where($condition)->group("title")->order("id desc")->select();

        if(!empty($list)) {
            $n = 0;
            foreach ($list as $val) {
                $amount = $val['amount'];

                if(1 == $val['scope']) {
                    $val['scope'] = '个人';
                } else if(2 == $val['scope']) {
                    $val['scope'] = '所有';
                } else {
                    $val['scope'] = '活动配置';
                }
                $val['n'] = ++$n;

                $val['total'] = $total = M('UserRedenvelope')->where(array('title'=>$val['title']))->count();
                $val['use_totla'] = $use_totla =  M('UserRedenvelope')->where("title = '".$val['title'] ."' and recharge_no !='' and status = 1")->count();

                $val['total_amount'] = $amount*$total; // 发放金额
                $val['use_totla_amount'] = $use_totla_amount = $amount*$use_totla; // 使用金额

                //投资金额  s_investment_detail /// 用户投资记录信息表
                $rechargeNoWhere = array(
                    'title'=>$val['title'],
                    'recharge_no'=>array('neq','')
                );
                $rechargeNoArr = M("UserRedenvelope")->where($rechargeNoWhere)->getField('recharge_no',true);


                if($rechargeNoArr) {
                    $val['inv_succ'] = $inv_succ = ($totla_inv_succ = M('investment_detail')->where(array('recharge_no' => array('in',$rechargeNoArr)))->sum('inv_succ')) ? $totla_inv_succ - $use_totla_amount : 0;
                }else{
                    $val['inv_succ'] = $inv_succ = 0;
                }
                //千元投资成本
                if($inv_succ){
                    $val['thousand_inv_succ'] = ($thousand_inv_succ = $use_totla_amount/$inv_succ*1000)?sprintf('%.2f',$thousand_inv_succ):'0.00';
                }else{
                    $val['thousand_inv_succ'] = '0.00';
                }

                //适用标签
                $apply_tag_arr = explode(':',$val['apply_tag']);


                $apply_tag_titie_arr = array();
                foreach ($apply_tag_arr as $a){
                    $apply_tag_titie_arr[] = $applyTagArr[$a];
                }
                $val['apply_tag_titie'] = implode(',',$apply_tag_titie_arr);

                if ($val['type'] != 4) { // 不等于暗道

                    $val['act_name'] = '';
                    $endStr = strrpos($val['source'],'_');
                    $key_name = substr($val['source'],0,$endStr);
                    if($key_name) {
                        $val['act_name'] = ($act_name = M('lottery_base')->where(array('key_name' => $key_name))->getField('name')) ? $act_name : '';
                    }

                }

                $typeArr = array('新手','后台发放','抽奖','平台活动','暗道');

                $val['type'] = $typeArr[$val['type']];





                $res[] = $val;
            }
            unset($list);
        }

        $fileName = '红包数据管理';
        $this->excelModel->setFileName($fileName);
        $this->excelModel->setTitles($this->packetlist_titles());
        $this->excelModel->setFields($this->packetlist_field());


        $this->excelModel->excelFile($res);


    }

    private function packetlist_field(){
        return array(
            'id',
            'title',
            'create_time',
            'amount',
            'min_invest',
            'min_due',
            'expire_time',
            'apply_tag_titie',
            'type',
            'act_name',
            'total',
            'use_totla',
            'total_amount.f',
            'use_totla_amount.f',
            'inv_succ.f',
            'thousand_inv_succ.f'
        );
    }
    private function packetlist_titles(){
        return array(
            '红包ID',
            '标题',
            '生效日期',
            '金额(元)',
            '最小投资金额(元)',
            '最小投资期限(天)',
            '截止日期',
            '适用标签',
            '发放渠道',
            '活动名称',
            '发放个数',
            '使用人数',
            '发放金额',
            '使用金额',
            '带动投资金额',
            '千元投资成本'
        );
    }

    /**
     * 红包核销
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
                    $totalCnt = M("userRedenvelope")->where(['user_id' => $userId])->count();
                    $Page     = new \Think\Page($totalCnt, $this->pageSize);
                    $list     = M("userRedenvelope")
                        ->where(['user_id' => $userId])
                        ->order("create_time DESC")
                        ->limit($Page->firstRow . ',' . $Page->listRows)
                        ->select();
                } else {
                    $totalCnt = M("userRedenvelope")->where(['user_id' => $userId, 'status' => $status])->count();
                    $Page     = new \Think\Page($totalCnt, $this->pageSize);
                    $list     = M("userRedenvelope")
                        ->where(['user_id' => $userId, 'status' => $status])
                        ->order("create_time DESC")
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
            redirect(C('ADMIN_ROOT') . '/redenvelope/cancel_out_index' . $quest);
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
        $userRed              = M("userRedenvelope");
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
            } else {
                $str = '个人专享';
            }
            $resultStr .= ' ' . $str;
        }
        return $resultStr;
    }

}
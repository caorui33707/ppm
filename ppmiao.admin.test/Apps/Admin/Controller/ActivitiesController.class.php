<?php
namespace Admin\Controller;

/**
 * 活动控制器
 * @package Admin\Controller
 */
class ActivitiesController extends AdminController{

    /**
     * 新人送钱活动
     */
    public function newmoney(){
        if(!IS_POST){
            $money = 20; // 首次购买送20元
            $page = I('get.p', 1, 'int'); // 页码
            $status = I('get.status', -1, 'int');
            $start_time = I('get.st', '', 'strip_tags');
            $end_time = I('get.et', '', 'strip_tags');

            $userObj = M('User');
            $userDueDetailObj = M('UserDueDetail');
            $userBankObj = M('UserBank');
            $constantObj = M('Constant');

            if($start_time && $end_time){
                $conditions = '';
                if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
                if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
                $conditions = implode(' and ', $cond);
                $list = $userDueDetailObj->where($conditions)->group('user_id')->order('add_time')->select();

                $userInfo = null;
                foreach($list as $key => $val){
                    $userInfo = $userObj->field('real_name,mobile,card_no,newmoney,channel_id')->where(array('id'=>$val['user_id']))->find();
                    $list[$key]['real_name'] = $userInfo['real_name'];
                    $list[$key]['mobile'] = $userInfo['mobile'];
                    $list[$key]['newmoney'] = $userInfo['newmoney'];
                    $list[$key]['card_no2'] = $userInfo['card_no'];
                    $list[$key]['bankname'] = $userBankObj->where(array('bank_card_no'=>$val['card_no'],'has_pay_success'=>2))->getField('bank_name');
                    $list[$key]['channelStr'] = $constantObj->where(array('id'=>$userInfo['channel_id']))->getField('cons_value');
                }
            }

            $params = array(
                'page' => $page,
                'status' => $status,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $this->assign('params', $params);
            $this->assign('list', $list);
            $this->display();
        }else{
            $status = I('post.status', -1, 'int');
            $start_time = I('post.start_time', '', 'strip_tags');
            $end_time = I('post.end_time', '', 'strip_tags');
            $quest = '/status/'.$status;
            if($start_time) $quest .= '/st/'.$start_time;
            if($end_time) $quest .= '/et/'.$end_time;
            redirect(C('ADMIN_ROOT') . '/activities/newmoney'.$quest);
        }
    }

    /**
     * 支付新人送钱活动
     */
    public function newmoney_pay(){
        if(!IS_POST || !IS_AJAX) exit;

        $start_time = I('post.start_time', '', 'strip_tags');
        $end_time = I('post.end_time', '', 'strip_tags');

        $userObj = M('User');
        $userDueDetailObj = M('UserDueDetail');

        $conditions = '';
        if($start_time) $cond[] = "add_time>='".$start_time." 00:00:00.000000'";
        if($end_time) $cond[] = "add_time<='".$end_time." 23:59:59.999000'";
        $conditions = implode(' and ', $cond);

        $list = $userDueDetailObj->field('user_id')->where($conditions)->group('user_id')->select();
        $ids = '';
        foreach($list as $key => $val){
            $ids .= ',' . $val['user_id'];
        }
        if($ids) $ids = substr($ids, 1);
        if(!$userObj->where('id in ('.$ids.')')->save(array('newmoney'=>1))) $this->ajaxReturn(array('status'=>0,'info'=>'执行失败,请重试'));
        $this->ajaxReturn(array('status'=>1));
    }

    /**
     * 新手标购买记录
     */
    public function newman_record(){
        if(!IS_POST){
            $projectObj = M('Project');
            $list = $projectObj->field('id,title')->where(array('new_preferential'=>1))->select(); // 新手标信息

            $this->assign('list', $list);
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $projectObj = M('Project');
            $userObj = M('User');
            $userDueDetailObj = M('UserDueDetail');
            $constantObj = M('Constant');

            // 获取某个新手标下面的只购买一次的用户
            $page = I('post.p', 1, 'int');
            $pid = I('post.pid', 0, 'int');
            $datetime = $projectObj->where(array('id'=>$pid))->getField('start_time');
            $sql = "select user_id,due_capital,due_time from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$pid.") and add_time>='".$datetime."' group by user_id having count(id)=1";
            $list = $userDueDetailObj->query($sql);
            $html = '';
            $userIds = '';
            foreach($list as $key => $val){
                $userIds .= ','.$val['user_id'];
                $list[$key]['userinfo'] = $userObj->field('username,real_name,card_no,channel_id,add_time')->where(array('id'=>$val['user_id']))->find();
                $list[$key]['userinfo']['sex'] = substr($list[$key]['userinfo']['card_no'], strlen($list[$key]['userinfo']['card_no'])-2, 1)%2;
                $html .= '<tr class="row">';
                $html .= '<td align="center">'.$list[$key]['userinfo']['username'].'</td>';
                $html .= '<td align="center">'.$list[$key]['userinfo']['real_name'].'</td>';
                $html .= '<td align="center">';
                switch($list[$key]['userinfo']['sex']){
                    case 1:
                        $html .= '男';
                        break;
                    case 0:
                        $html .= '女';
                        break;
                }
                $html .= '</td>';
                $html .= '<td align="center">'.$list[$key]['userinfo']['card_no'].'</td>';
                $html .= '<td>'.$constantObj->where(array('id'=>$list[$key]['userinfo']['channel_id']))->getField('cons_value').'</td>';
                $html .= '<td align="right">'.number_format($val['due_capital'], 2).'</td>';
                $html .= '<td align="center">'.date('Y-m-d H:i',strtotime($list[$key]['userinfo']['add_time'])).'</td>';
                $html .= '<td align="center">'.date('Y-m-d H:i',strtotime($val['due_time'])).'</td>';
                $html .= '<td></td></tr>';
            }
            if($userIds) $userIds = substr($userIds, 1);
            $totle = $userDueDetailObj->where(array('project_id'=>$pid))->count();

            $this->ajaxReturn(array('status'=>1,'info'=>$html,'percent'=>number_format((($totle-count($list))/$totle)*100,2)));
        }
    }

    /**
     * 新手标购买记录百分比(只投一次用户)
     */
    public function newman_record_percent(){
        if(!IS_POST || !IS_AJAX) exit;

        $projectObj = M('Project');
        $userDueDetailObj = M('UserDueDetail');

        $moreCount = 0; // 二次以上用户数量
        $totleCount = 0; // 总用户数量

        $list = $projectObj->field('id,start_time')->where(array('new_preferential'=>1))->select();
        foreach($list as $key => $val){
            $sql1 = "select count(id) as count from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$val['id'].") and add_time>='".$val['start_time']."' group by user_id having count>1";
            $sql2 = "select count(id) as count from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$val['id'].") and add_time>='".$val['start_time']."' group by user_id";
            $userList = $userDueDetailObj->query($sql1);
            $totleList = $userDueDetailObj->query($sql2);
            $moreCount += count($userList);
            $totleCount += count($totleList);

        }
        $this->ajaxReturn(array('status'=>0,'info'=>number_format(($totleCount-$moreCount)*100/$totleCount,2).'%'));
    }

    /**
     * 爆款标购买记录
     */
    public function bk_record(){
        if(!IS_POST){
            $projectObj = M('Project');
            $list = $projectObj->field('id,title')->where(array('new_preferential'=>2))->select(); // 新手标信息

            $this->assign('list', $list);
            $this->display();
        }else{
            if(!IS_AJAX) exit;

            $projectObj = M('Project');
            $userObj = M('User');
            $userDueDetailObj = M('UserDueDetail');
            $constantObj = M('Constant');

            // 获取某个新手标下面的只购买一次的用户
            $page = I('post.p', 1, 'int');
            $pid = I('post.pid', 0, 'int');
            $datetime = $projectObj->where(array('id'=>$pid))->getField('start_time');
            $sql = "select user_id,due_capital,due_time from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$pid." and user_id>0) and add_time>='".$datetime."' group by user_id having count(id)>1";
            $list = $userDueDetailObj->query($sql);
            $html = '';
            foreach($list as $key => $val){
                $list[$key]['userinfo'] = $userObj->field('username,real_name,card_no,channel_id,add_time')->where(array('id'=>$val['user_id']))->find();
                $list[$key]['userinfo']['sex'] = substr($list[$key]['userinfo']['card_no'], strlen($list[$key]['userinfo']['card_no'])-2, 1)%2;
                $html .= '<tr class="row">';
                $html .= '<td align="center">'.$list[$key]['userinfo']['username'].'</td>';
                $html .= '<td align="center">'.$list[$key]['userinfo']['real_name'].'</td>';
                $html .= '<td align="center">';
                switch($list[$key]['userinfo']['sex']){
                    case 1:
                        $html .= '男';
                        break;
                    case 0:
                        $html .= '女';
                        break;
                }
                $html .= '</td>';
                $html .= '<td align="center">'.$list[$key]['userinfo']['card_no'].'</td>';
                $html .= '<td>'.$constantObj->where(array('id'=>$list[$key]['userinfo']['channel_id']))->getField('cons_value').'</td>';
                $html .= '<td align="right">'.number_format($val['due_capital'], 2).'</td>';
                $html .= '<td align="center">'.date('Y-m-d H:i',strtotime($list[$key]['userinfo']['add_time'])).'</td>';
                $html .= '<td align="center">'.date('Y-m-d H:i',strtotime($val['due_time'])).'</td>';
                $html .= '<td></td></tr>';
            }
            $this->ajaxReturn(array('status'=>1,'info'=>$html));
        }
    }

    /**
     * 爆款标购买记录百分比(后续购买人数)
     */
    public function bk_record_percent(){
        if(!IS_POST || !IS_AJAX) exit;

        $projectObj = M('Project');
        $userDueDetailObj = M('UserDueDetail');

        $moreCount = 0; // 二次以上用户数量
        $totleCount = 0; // 总用户数量

        $list = $projectObj->field('id,start_time')->where(array('new_preferential'=>2))->select();
        foreach($list as $key => $val){
            $sql1 = "select count(id) as count from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$val['id'].") and add_time>='".$val['start_time']."' group by user_id having count>1";
            $sql2 = "select count(id) as count from s_user_due_detail where user_id in (select user_id from s_user_due_detail where project_id=".$val['id'].") and add_time>='".$val['start_time']."' group by user_id";
            $userList = $userDueDetailObj->query($sql1);
            $totleList = $userDueDetailObj->query($sql2);
            $moreCount += count($userList);
            $totleCount += count($totleList);

        }
        $this->ajaxReturn(array('status'=>0,'info'=>number_format(($moreCount)*100/$totleCount,2).'%'));
    }

    /**
     * 赢收益,送豪礼(活动2015-07-14~2015-07-20)
     */
    public function ysyshl(){
        $userObj = M("User");
        $startTime = '2015-07-14';
        $endTime = '2015-07-20';
        $sql = "select * from (select user_id,sum(due_capital) as sum from s_user_due_detail where user_id > 0 and project_id in (select id from s_project where DATEDIFF(end_time, start_time) >= 80 and start_time>='".$startTime." 00:00:00.000000') and add_time>='".$startTime." 00:00:00.000000' and add_time<='".$endTime." 23:59:59.999000' group by user_id) as t order by sum desc";
        $list = M()->query($sql);
        $currentSum = 0;
        $result1 = array();
        $result2 = array();
        $result3 = array();
        $result4 = array();
        $result5 = array();
        $result6 = array();
        $result7 = array();

        $result1[] = array('user_id'=>0,'title'=>'iWatch');
        $result2[] = array('user_id'=>0,'title'=>'beats solo2');
        $result3[] = array('user_id'=>0,'title'=>'ipod shuffle');
        $result4[] = array('user_id'=>0,'title'=>'200元话费红包');
        $result5[] = array('user_id'=>0,'title'=>'100元话费红包');
        $result6[] = array('user_id'=>0,'title'=>'50元话费红包');
        $result7[] = array('user_id'=>0,'title'=>'木有中奖~');

        foreach($list as $key => $val){
            $list[$key]['uinfo'] = $userObj->where(array('id'=>$val['user_id']))->find();
            if($val['sum'] >= 400000){
                $result1[] = $list[$key];
            }else if($val['sum'] >= 200000){
                $result2[] = $list[$key];
            }else if($val['sum'] >= 100000){
                $result3[] = $list[$key];
            }else if($val['sum'] >= 50000){
                $result4[] = $list[$key];
            }else if($val['sum'] >= 20000){
                $result5[] = $list[$key];
            }else if($val['sum'] >= 10000){
                $result6[] = $list[$key];
            }else{
                $result7[] = $list[$key];
            }
        }
        $result1[0]['title'] .= "(中奖 ".(count($result1)-1)." 人)";
        $result2[0]['title'] .= "(中奖 ".(count($result2)-1)." 人)";
        $result3[0]['title'] .= "(中奖 ".(count($result3)-1)." 人)";
        $result4[0]['title'] .= "(中奖 ".(count($result4)-1)." 人)";
        $result5[0]['title'] .= "(中奖 ".(count($result5)-1)." 人)";
        $result6[0]['title'] .= "(中奖 ".(count($result6)-1)." 人)";
        $result7[0]['title'] .= "(中奖 ".(count($result7)-1)." 人)";
        $list = array_merge($result1, $result2, $result3, $result4, $result5, $result6, $result7);

        $this->assign('count', count($list));
        $this->assign('list', $list);
        $this->display();
    }
    /***
     * 新人送红包活动
     */
    public function red_envelope(){
        $userRedenvelopeObj = M('UserRedenvelope');//用户红包列表
        $projectObj = M('Project');//产品表
        $constantObj = M('Constant');//常量表
        $userObj = M("User");//用户表
        $rechargeLogObj = M("RechargeLog");//用户下单记录表
        if(!IS_POST){
            $count = 20;
            $page = I('get.p', 1, 'int'); // 页码
            $st = I('get.st','','strip_tags');//开始时间
            $et = I('get.et','','strip_tags');//结束时间
            $status = I('get.status','0','trim');//状态
            $query_str = "";
            if($status){
                if($status == 1){//已送出
                    if($st && $et){
                        $cond[] ="expire_time>='".$st." 00:00:00.000000' and expire_time<='".$et." 23:59:59.999000'";
                        $cond[] = "is_send = 1";
                    }else{
                        $cond[] = "is_send = 1";
                    }
                }else if($status == 2){//未送出
                    if($st && $et){
                        $cond[] ="expire_time>='".$st." 00:00:00.000000' and expire_time<='".$et." 23:59:59.999000'";
                        $cond[] = "is_send = 0";
                    }else{
                        $cond[] = "is_send = 0";
                    }
                }
            }else{
                $cond[] ="expire_time>='".$st." 00:00:00.000000' and expire_time<='".$et." 23:59:59.999000'";
            }
            if($cond){
                $query_str = implode(" and ",$cond);
            }
            $counts = $userRedenvelopeObj->where($query_str)->count();
            $Page = new \Think\Page($counts, $count);
            $userRedenvelopeList = $userRedenvelopeObj->where($query_str)->order('expire_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $show = $Page->show();
            $total_red_amount = 0;
            foreach($userRedenvelopeList as $k => $v){
                $userInfoList = $userObj->where(array('id'=>$v['user_id']))->find();
                $userRedenvelopeList[$k]['real_name']=$userInfoList['real_name'];
                $userRedenvelopeList[$k]['username']=$userInfoList['username'];
                $projectList = $projectObj->where(array('id'=>$v['project_id']))->find();
                $userRedenvelopeList[$k]['title']=$projectList['title'];
                $userRedenvelopeList[$k]['term']=$projectList['duration'];
                $userRedenvelopeList[$k]['expire_time']=$v['expire_time'];
                $userRedenvelopeList[$k]['sms_msg']=$v['sms_msgid'];
                $userRedenvelopeList[$k]['send']=($v['is_send'] ==1)? '已发送':'未发送';
                $userRedenvelopeList[$k]['amount'] = $v['amount'];
                $userRedenvelopeList[$k]['recharge_no'] = $v['recharge_no'];
                $constant_info_list = $constantObj->where(array('id'=>$userInfoList['channel_id']))->find();
                $userRedenvelopeList[$k]['cons_value'] = $constant_info_list['cons_value'];
                if(stristr($v['recharge_no'],'ST')!==false){
                    $from = '银行卡下单';
                }else if(stristr($v['recharge_no'],'QB')!==false){
                    $from = '钱包下单';
                }else{
                    $from = '未知来源';
                }
                $userRedenvelopeList[$k]['from'] = $from;
                //下单金额
                $rechargeAmount = $rechargeLogObj->where(array('recharge_no'=>$v['recharge_no']))->field('amount')->find();

                $userRedenvelopeList[$k]['rechargeAmount'] = $rechargeAmount['amount'];

            }
            //总现金券
            $total_red_amount = $userRedenvelopeObj->where($query_str)->sum('amount');
            $params=array(
                'page'=>$page,
                'start_time'=>$st,
                'end_time'=>$et,
                'total_num'=>$counts,
                'status'=>$status
            );

            $this->assign('list', $userRedenvelopeList);
            $this->assign('show', $show);
            $this->assign('params',$params);
            $this->assign('total_red_amount',$total_red_amount);
            $this->display();

        }else{
            $quest = '';
            $st = I('post.start_time','','strip_tags');//开始时间
            $et = I('post.end_time','','strip_tags');//结束时间
            $status = I('post.status','-1','trim');//状态
            if($st && $et) $quest .= '/st/'.$st.'/et/'.$et.'/status/'.$status;
            redirect(C('ADMIN_ROOT') . '/activities/red_envelope'.$quest);
        }
    }
    /***
     * 批量发送红包
     */
    public function batch_send(){
        if(!IS_AJAX || !IS_POST){
            exit;
        }
        $userRedenvelopeObj = M('UserRedenvelope');//用户红包列表
        $projectObj = M("Project");// 产品表
        $userObj    = M("User");//用户表
        $userWalletRecordsObj = M("UserWalletRecords");//用户钱包转入转出记录信息表
        $userAccountObj = M("UserAccount");//用户信息表
        $userBankObj =  M("UserBank");//用户关联银行卡

        $st = I('post.start_time','','strip_tags');//开始时间
        $et = I('post.end_time','','strip_tags');//结束时间
        if(!$st || !$et){
            $this->ajaxReturn(array('status'=>0,'info'=>'需要选择时间区间'));
        }

        $query_str = "add_time>='".$st." 00:00:00.000000' and add_time<='".$et." 23:59:59.999000' and is_send=0";
        $userRedenvelopeList = $userRedenvelopeObj->where($query_str)->select();

        if(empty($userRedenvelopeList)){
            $this->ajaxReturn(array('status'=>0,'info'=>'没有用户需要赠送红包的记录'));
        }
        foreach($userRedenvelopeList as $k=>$v){
            $projectTitle = $projectObj->where(array('id'=>$v['project_id']))->getField('title');
            $userArr   = $userObj->where(array('id'=>$v['user_id']))->find();

            //用户银行卡ID
            $userBankId  = $userBankObj->where(array('user_id'=>$v['user_id'],'has_pay_success'=>2))->getField('id');
            //查出用户是什么机型
            $deviceType = $userObj->where(array('id'=>$v['user_id']))->getField('device_type');
            //插入钱包转入/转出记录
            $now_time = date("Y-m-d H:i:s",time()).'.'.getMillisecond().'000';
            $recharge_no ='';
            if(stripos($v['recharge_no'],"QB")!==false){
                $recharge_no = str_replace("QB","XT",$v['recharge_no']);
            }else if(stripos($v['recharge_no'],"ST")!==false){
                $recharge_no = str_replace("ST","XT",$v['recharge_no']);
            }

            $userWalletRecordsArr = array(
                'user_id'=>$v['user_id'],
                'recharge_no'=>$recharge_no,
                'pay_type'=>3,
                'value'=>$v['amount'],
                'type'=>1,
                'pay_status'=>2,
                'user_bank_id'=>$userBankId,
                'device_type'=>$deviceType,
                'add_time'=>$now_time,
                'modify_time'=>$now_time,
                'remark'=>'送现金券'
            );
            $walletRecordId = $userWalletRecordsObj->add($userWalletRecordsArr);
            if($walletRecordId){
                //修改用户信息表
               $userAccountObj->where(array('user_id'=>$v['user_id']))->setInc('wallet_totle',$v['amount']);
                //修改用户银行卡的钱包可提金额
                $userBankObj->where(array('id'=>$userBankId))->setInc('wallet_money',$v['amount']);
            }
            //极光推送

                $msgid = $this->send_msg($v['amount'],$userArr['mobile']);

                if($msgid){//短信回执编码
                    $userRedenvelopeObj->where(array('id'=>$v['id']))->save(array('sms_msgid'=>$msgid,'is_send'=>1));
                }


        }
        $this->ajaxReturn(array('status'=>1,'info'=>'批量处理完成'));

    }
    //单条发送短信
    public function send_msg($amount,$mobile){
        $msg = "尊敬的客户，您参加石头理财狂欢周活动的".$amount."元现金奖励已经充值到您的钱包，请注意查收，祝您投资愉快。";

        $params = 'account='.C('SMS_INTDERFACE.account');
        $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
        $params .= '&mobile='.$mobile;
        $params .= '&msg='.$msg;
        $params .= '&needstatus=true';
        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }
        $msgid = trim($arr[1][0]);
        return $msgid;

       //pushMsg($msg,$regid);
    }
    /***
     * 新人送红包活动-excel
     */
    public function red_envelope_excel(){
        ini_set("memory_limit", "2000M");
        ini_set("max_execution_time", 0);
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('新人送红包活动用户列表')->setCellValue("A1", "姓名")->setCellValue("B1", "手机号码")->setCellValue("C1", "红包金额")->setCellValue("D1", "获取红包日期")->setCellValue("E1", "发放红包日期");
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        $pos = 2;
        $userRedenvelopeObj = M('UserRedenvelope');//用户红包列表
        $projectObj = M('Project');//产品表
        $constantObj = M('Constant');//常量表
        $userObj = M("User");//用户表
        $rechargeLogObj = M("RechargeLog");//用户下单记录表
        $userWalletRecordsObj = M("UserWalletRecords");//用户钱包转入转出记录信息表
        //开始时间
        $begin_time = I("get.st",'2015-12-25','strip_tags');
        //结束时间
        $end_time = I("get.et",date('Y-m-d',time()),'strip_tags');
        //状态
        $status = I("get.status",0,'int');//1表示已送出2表示未送出
        $query_str = "";
        $cond[] ="add_time>='".$begin_time." 00:00:00.000000' and add_time<='".$end_time." 23:59:59.999000'";
        if($status == 1){ //已送出
            $cond[] = "is_send = 1";
        }else if($status == 2){ //未送出
            $cond[] = "is_send = 0";
        }

        if($cond){
            $query_str = implode(" and ",$cond);
        }

        $userRedenvelopeList = $userRedenvelopeObj->where($query_str)->order('add_time desc')->select();
        foreach($userRedenvelopeList as $k => $v){
            $userInfoList = $userObj->where(array('id'=>$v['user_id']))->find();
            $userRedenvelopeList[$k]['real_name']=$userInfoList['real_name'];
            $userRedenvelopeList[$k]['username']=$userInfoList['username'];
            $projectList = $projectObj->where(array('id'=>$v['project_id']))->find();
            $userRedenvelopeList[$k]['title']=$projectList['title'];
            $userRedenvelopeList[$k]['add_time']=$v['add_time'];
            $userRedenvelopeList[$k]['sms_msg']=$v['sms_msgid'];
            $userRedenvelopeList[$k]['send']=($v['is_send'] ==1)? '已发送':'未发送';
            $userRedenvelopeList[$k]['amount'] = $v['amount'];
            $userRedenvelopeList[$k]['recharge_no'] = $v['recharge_no'];
            $constant_info_list = $constantObj->where(array('id'=>$userInfoList['channel_id']))->find();
            $userRedenvelopeList[$k]['cons_value'] = $constant_info_list['cons_value'];
            if(stristr($v['recharge_no'],'ST')!==false){
                $from = '银行卡下单';
            }else if(stristr($v['recharge_no'],'QB')!==false){
                $from = '钱包下单';
            }else{
                $from = '未知来源';
            }
            $userRedenvelopeList[$k]['from'] = $from;
            //下单金额
            $rechargeAmount = $rechargeLogObj->where(array('recharge_no'=>$v['recharge_no']))->field('amount')->find();

            $userRedenvelopeList[$k]['rechargeAmount'] = $rechargeAmount['amount'];
            //打款时间
            $recharge_no = str_replace("ST","XT",$v['recharge_no']);
            $send_time = $userWalletRecordsObj->field('add_time')->where(array('recharge_no'=>$recharge_no))->find();
            $userRedenvelopeList[$k]['send_time'] = $send_time['add_time'];

        }
        foreach($userRedenvelopeList as $k=>$v){
            $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
            $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, date("Y-m-d",strtotime($v['add_time'])));
            $objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, date("Y-m-d",strtotime($v['send_time'])));

            $pos++;
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="用户红包('.$begin_time.'至'.$end_time.').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    //新人投资送红包奖励-补记录
    public function write_redWallet(){
        if(!IS_AJAX || !IS_POST){
            $this->ajaxReturn(array("status"=>0,'info'=>"处理方式不对,请联系系统管理员"));
        }
        $userRedenvelopeObj = M('UserRedenvelope');//用户红包列表
        $begin_time = "2016-01-01";
        $end_time   = "2016-01-31";
        $sql = "SELECT b.`real_name`,b.`username`,b.`id`,e.`due_capital`,e.`project_id`,e.`add_time`,b.`add_time` AS reg_time,v.`title`,v.`duration`,h.`recharge_no` FROM ".C('DB_NAME').".`s_user_due_detail` AS e,".C('DB_NAME').".`s_user` AS b,".C('DB_NAME').".`s_project` AS v,".C('DB_NAME').".`s_investment_detail` AS h  WHERE e.`user_id` = b.`id` AND e.`project_id` = v.`id` AND e.`user_id`>0 AND e.`invest_detail_id` = h.`id` AND e.`add_time`>='".$begin_time." 00:00:00.000000' AND e.`add_time`<='".$end_time." 23:59:59.999000' AND e.`user_id` NOT IN(SELECT w.`user_id` FROM ".C('DB_NAME').".`s_user_due_detail` AS w WHERE w.`user_id`>0 AND w.`add_time`<='2016-01-01 00:00:00.000000' GROUP BY w.`user_id`)";
        $user_list = M()->query($sql);
        $amount = 0;
        $second_amount = 0;
        foreach($user_list as $k=>$v){
            if($v['due_capital']>=1000) {
                if ($v['due_capital'] >= 1000 && $v['duration'] < 50) {
                    $amount = 10;
                } else if ($v['due_capital'] >= 5000 && ($v['duration'] > 50 && $v['duration'] < 140)) {
                    $amount = 38;
                    $second_amount = 10;
                } else if ($v['due_capital'] >= 10000 && $v['duration'] >= 140) {
                    $amount = 88;
                    $second_amount = 10;
                }
                //查询用户是否已经送过红包
                $exist_id = $userRedenvelopeObj->where(array('user_id' => $v['id'], 'amount' => $amount))->find();
                if (!$exist_id) {
                    $userRedenvelopeArr = array(
                        'user_id' => $v['id'],
                        'recharge_no' => $v['recharge_no'],
                        'amount' => $amount,
                        'project_id' => $v['project_id'],
                        'add_user_id' => $v['id'],
                        'add_time' => $v['add_time'],
                        'modify_user_id' => $v['id'],
                        'modify_time' => $v['add_time'],
                    );
                    $userRedenvelopeId = $userRedenvelopeObj->add($userRedenvelopeArr);
                }
                //当用户投资理财金额大于5000，判断有没有获取到10元的红包
                $second_exist_id = $userRedenvelopeObj->where(array('user_id' => $v['id'], 'amount' => $second_amount))->find();
                if (!$second_exist_id) {
                    $userSecondRedenvelopeArr = array(
                        'user_id' => $v['id'],
                        'recharge_no' => $v['recharge_no'],
                        'amount' => $second_amount,
                        'project_id' => $v['project_id'],
                        'add_user_id' => $v['id'],
                        'add_time' => $v['add_time'],
                        'modify_user_id' => $v['id'],
                        'modify_time' => $v['add_time'],
                    );
                    $userSecondRedenvelopeId = $userRedenvelopeObj->add($userSecondRedenvelopeArr);
                }
            }
        }
        $this->ajaxReturn(array("status"=>1,'info'=>"处理成功"));
    }
    /**
     * 石头理财A轮融资活动
     * comment:
     * 活动口号：“庆石头理财获A轮融资，年货豪礼大回馈”
    活动时间：1.21 00:00 - 1.24 24:00，给力96小时，送礼不打烊
    开奖时间：1.25 14:00
    活动对象：全员参与
    活动规则：
    活动期间，投资带有奖励标识的“XXXXXXX”活动标，且满足以下条件即送豪礼！
    新增累计投资额≥1w送价值300元京东卡1张
    新增累计投资额≥2W送价值800元Beats耳机1副
    新增累计投资额≥5W送价值1500元京东卡1张
    新增累计投资额≥10w送价值3588 ipad mini4 64G 1台
    新增累计投资额≥15w送价值 6088 iphone6s 64G 1台
     */
    public function financing_activity(){
        if(!IS_POST){
            $count = 20;
            $page = I('get.p', 1, 'int'); // 页码
            $st = I('get.st','2016-01-21','strip_tags');//开始时间
            $et = I('get.et','2016-01-24','strip_tags');//结束时间
            $status = I('get.status',0,'int');//状态
            //$count_sql = "SELECT COUNT(w.id) AS  total FROM (SELECT m.id,m.`real_name`,m.`username`,SUM(n.`due_capital`) AS total_due_amount FROM stone.`s_user_due_detail` AS n,stone.`s_user` AS m WHERE m.`add_time`>='".$st." 00:00:00.000000' AND m.`add_time`<='".$et." 23:59:59.999000' AND n.`user_id` = m.`id` GROUP BY n.`user_id`) AS w";
            //$counts_arr = M()->query($count_sql);
            //$counts = $counts_arr[0]['total'];
            //$Page = new \Think\Page($counts, $count);
            $action_st = "2016-01-21";//活动开始时间
            $action_et = "2016-01-24";//活动结束时间
            $financing_sql = "SELECT m.id,m.`real_name`,m.`username`,SUM(n.`due_capital`) AS total_due_amount FROM ".C('DB_NAME').".`s_user_due_detail` AS n,".C('DB_NAME').".`s_user` AS m WHERE n.`add_time`>='".$st." 00:00:00.000000' AND n.`add_time`<='".$et." 23:59:59.999000' AND n.`user_id` = m.`id` AND n.`project_id` IN(SELECT m.`id` FROM ".C('DB_NAME').".`s_project` AS m WHERE m.`new_preferential` = 4 AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') GROUP BY n.`user_id`";

            $financingList = M()->query($financing_sql);
           // $show = $Page->show();
            $total_amount =0;
            $total_join = 0;
            $newfinancingList = array();
            foreach($financingList as $k=>$v){
                $due_amount = $v['total_due_amount'];
                if($status == 2){// 中奖情况
                    if($due_amount>=10000){
                        $newfinancingList[$k]['id']=$v['id'];
                        $newfinancingList[$k]['real_name']=$v['real_name'];
                        $newfinancingList[$k]['username']=$v['username'];
                        $newfinancingList[$k]['total_due_amount']=$due_amount;
                        $prizeStr = $this->get_prize_comment($due_amount);
                        $newfinancingList[$k]['prize']=$prizeStr;
                        $total_amount+=$v['total_due_amount'];
                        $total_join++;
                    }
                }else if($status == 1 || $status == 0){
                    $newfinancingList[$k]['id']=$v['id'];
                    $newfinancingList[$k]['real_name']=$v['real_name'];
                    $newfinancingList[$k]['username']=$v['username'];
                    $newfinancingList[$k]['total_due_amount']=$due_amount;
                    $prizeStr = $this->get_prize_comment($due_amount);
                    $newfinancingList[$k]['prize']=$prizeStr;
                    $total_amount+=$v['total_due_amount'];
                    $total_join++;
                }

            }

            $params=array(
                'page'=>$page,
                'start_time'=>$st,
                'status'=>$status,
                'end_time'=>$et,
                'total_num'=>$total_join,
                'total_amount'=>$total_amount,
            );

            $this->assign('list', $newfinancingList);
            //$this->assign('show', $show);
            $this->assign('params',$params);

        }else{
            $quest = '';
            $st = I('post.start_time','','strip_tags');//开始时间
            $et = I('post.end_time','','strip_tags');//结束时间
            $status = I('post.status',0,'int');//帅选条件
            if($st && $et) $quest .= '/st/'.$st.'/et/'.$et.'/status/'.$status;
            redirect(C('ADMIN_ROOT') . '/activities/financing_activity'.$quest);
        }
        $this->display();
    }
    /**
     * 查看指定用户在指定时间段内投资列表
     */
    public function look_user_due_list(){
        $userObj = M('user');//用户表
        $st = I('get.st','2016-01-01','strip_tags');//开始时间
        $et = I('get.et',date("Y-m-d",time()-24*3600),'strip_tags');//结束时间
        $action_st = "2016-01-21";//活动开始时间
        $action_et = "2016-01-24";//活动结束时间
        $uid = I('get.uid',0,'int');//用户id
        if(!$uid){
            exit("用户信息有误，请联系系统管理员");
        }
        $count = 20;
        $page = I('get.p', 1, 'int'); // 页码
        $st = I('get.st','2016-01-01','strip_tags');//开始时间
        $et = I('get.et',date("Y-m-d",time()-24*3600),'strip_tags');//结束时间

        $count_sql = "SELECT COUNT(*) AS  total FROM (SELECT n.`title`,m.`start_time`,m.`due_time`,m.`due_capital`,m.`add_time` FROM ".C('DB_NAME').".`s_user_due_detail` AS m,".C('DB_NAME').".`s_project` AS n WHERE m.`user_id` = ".$uid." AND m.`project_id` = n.`id` AND m.`project_id` IN(SELECT m.`id` FROM ".C('DB_NAME').".`s_project` AS m WHERE m.`new_preferential` = 4 AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') AND m.`add_time`>='".$st." 00:00:00.000000' AND m.`add_time`<='".$et." 23:59:59.999000') AS w";
        $counts_arr = M()->query($count_sql);
        $counts = $counts_arr[0]['total'];
        $Page = new \Think\Page($counts, $count);
        $due_list_sql = "SELECT m.`id`,n.`title`,m.`start_time`,m.`due_time`,m.`due_capital`,m.`add_time` FROM ".C('DB_NAME').".`s_user_due_detail` AS m,".C('DB_NAME').".`s_project` AS n WHERE m.`user_id` = ".$uid." AND m.`project_id` = n.`id` AND m.`project_id` IN(SELECT m.`id` FROM ".C('DB_NAME').".`s_project` AS m WHERE m.`new_preferential` = 4 AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') AND m.`add_time`>='".$st." 00:00:00.000000' AND m.`add_time`<='".$et." 23:59:59.999000' LIMIT ".$Page->firstRow.",".$Page->listRows."";
        $dueList = M()->query($due_list_sql);
        $show = $Page->show();
        $total_amount =0;
        foreach($dueList as $k=>$v){
            $dueList[$k]['id'] = $v['id'];
            $dueList[$k]['title'] = $v['title'];
            $dueList[$k]['due_capital'] = $v['due_capital'];
            $dueList[$k]['add_time'] = $v['add_time'];
            $duration_day = (strtotime(date("Y-m-d",strtotime($v['due_time'])))-strtotime(date("Y-m-d",strtotime($v['add_time'])+24*3600)))/(24*3600)+1;
            $dueList[$k]['duration'] =$duration_day;
            $total_amount+=$v['due_capital'];
        }
        //用户基本信息
        $real_name_arr = $userObj->field("real_name")->where(array('id'=>$uid))->find();

        $params=array(
            'page'=>$page,
            'start_time'=>$st,
            'end_time'=>$et,
            'total_num'=>$counts,
            'total_amount'=>$total_amount,
            'user_name'=>$real_name_arr['real_name']
        );

        $this->assign('list', $dueList);
        $this->assign('show', $show);
        $this->assign('params',$params);
        $this->assign('financing_total',$counts);


        $this->display();

    }
    /**
     * 获取相应的奖品
     */
    public function get_prize_comment($due_amount){
        //奖品 活动期间()
        $prizeArr = array(
            '0'=>'300元京东卡1张',//新增累计投资额≥1w送价值300元京东卡1张
            '1'=>'800元Beats耳机1副',//新增累计投资额≥2W送价值800元Beats耳机1副
            '2'=>'1500元京东卡1张',//新增累计投资额≥5W送价值1500元京东卡1张
            '3'=>'3588元ipad mini4 64G 1台',//新增累计投资额≥10w送价值3588 ipad mini4 64G 1台
            '4'=>'6088元iphone6s 64G 1台',//新增累计投资额≥15w送价值 6088 iphone6s 64G 1台
        );
        if($due_amount>=10000 && $due_amount<20000){
            $prizeStr = $prizeArr[0];
        }else if($due_amount>=20000 && $due_amount<50000){
            $prizeStr =$prizeArr[1];
        }else if($due_amount>=50000 && $due_amount<100000){
            $prizeStr =$prizeArr[2];
        }else if($due_amount>=100000 && $due_amount<150000){
            $prizeStr =$prizeArr[3];
        }else if($due_amount>=150000){
            $prizeStr =$prizeArr[4];
        }else{
            $prizeStr = '';
        }
        return $prizeStr;
    }
    /**
     * 导出A轮融资活动大回馈活动，用户获奖列表
     */
    public function export_excel_prize(){
        ini_set("memory_limit", "3000M");
        ini_set("max_execution_time", 0);
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('A轮获奖用户列表')->setCellValue("A1", "用户真实名称")->setCellValue("B1", "用户账号")->setCellValue("C1", "累计投资金额")->setCellValue("D1", "奖品");
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $action_st = "2016-01-21";//活动开始时间
        $action_et = "2016-01-24";//活动结束时间
        $pos = 2;

        $financing_sql = "SELECT m.id,m.`real_name`,m.`username`,SUM(n.`due_capital`) AS total_due_amount FROM ".C('DB_NAME').".`s_user_due_detail` AS n,".C('DB_NAME').".`s_user` AS m WHERE m.`add_time`>='".$action_st." 00:00:00.000000' AND m.`add_time`<='".$action_et." 23:59:59.999000' AND n.`user_id` = m.`id` AND n.`project_id` IN(SELECT m.`id` FROM ".C('DB_NAME').".`s_project` AS m WHERE m.`new_preferential` = 4 AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') GROUP BY n.`user_id`";
        $financingList = M()->query($financing_sql);

        foreach($financingList as $k => $v){
            if($v['total_due_amount']>=10000) {
                $prize_str = $this->get_prize_comment($v['total_due_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['total_due_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $prize_str);
                $pos++;
            }
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="(A轮获奖用户列表.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    /**
     * 批量发送获奖用户短信通知
     */
    public function batch_send_msg(){
        $action_st = "2016-01-21";//活动开始时间
        $action_et = "2016-01-24";//活动结束时间
        $financing_sql = "SELECT m.id,m.`real_name`,m.`username`,SUM(n.`due_capital`) AS total_due_amount FROM ".C('DB_NAME').".`s_user_due_detail` AS n,".C('DB_NAME').".`s_user` AS m WHERE m.`add_time`>='".$action_st." 00:00:00.000000' AND m.`add_time`<='".$action_et." 23:59:59.999000' AND n.`user_id` = m.`id` AND n.`project_id` IN(SELECT m.`id` FROM ".C('DB_NAME').".`s_project` AS m WHERE m.`new_preferential` = 4 AND m.`start_time`>='".$action_st." 00:00:00.000000' AND m.`start_time`<='".$action_et." 23:59:59.999000') GROUP BY n.`user_id`";
        $financingList = M()->query($financing_sql);
        foreach($financingList as $k => $v){
            if($v['total_due_amount']>=10000) {
                if($v['username']){
                    $this->send_prize_msg($v['total_due_amount'],$v['username']);
                }
            }
        }
        $this->ajaxReturn(array('status'=>1));
    }
    //单条发送短信
    public function send_prize_msg($amount,$mobile){
        $prize_list_arr = array(
            '0'=>'300元京东礼品卡（1张）',
            '1'=>'800元Beats耳机（1副）',
            '2'=>'1500元京东礼品卡（1张）',
            '3'=>'3588元ipad mini4 64G（1台）',
            '4'=>'6088元iphone6s 64G（1台）',
        );
        if($amount<10000){
            $prize_str =$prize_list_arr[0];
        }else if($amount>=10000 && $amount<20000){
            $prize_str =$prize_list_arr[1];
        }else if($amount>=20000 && $amount<50000){
            $prize_str =$prize_list_arr[2];
        }else if($amount>=50000 && $amount<100000){
            $prize_str =$prize_list_arr[3];
        }else if($amount>=100000 && $amount<150000){
            $prize_str =$prize_list_arr[4];
        }
        $msg = "尊敬的用户，恭喜您在石头理财庆A轮投资活动中，获得".$prize_str."，请于1.28前将收货地址等信息发送至kefu@stlc.cn邮箱，谢谢您的参与。";
        $params = 'account='.C('SMS_INTDERFACE.account');
        $params .= '&pswd='.C('SMS_INTDERFACE.pswd');
        $params .= '&mobile='.$mobile;
        $params .= '&msg='.$msg;
        $params .= '&needstatus=true';
        $smsData = file_get_contents('http://'.C('SMS_INTDERFACE.ip').':'.C('SMS_INTDERFACE.port').'/msg/HttpBatchSendSM?'.$params);
        $arr = explode("\n", $smsData);
        foreach($arr as $key => $val){
            $arr[$key] = explode(',', $val);
        }
        $msgid = trim($arr[1][0]);
        return $msgid;
    }

}
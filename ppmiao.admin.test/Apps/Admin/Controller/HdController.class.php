<?php
namespace Admin\Controller;

/**
 * 活动控制器
 * @package Admin\Controller
 */
class HdController extends AdminController{
    
    private $pageSize = 10;
    
    public function event20170407(){
        if(!IS_POST){
            $start_time = I('get.st', date("Y-m-d"), 'strip_tags');
        
            $userObj = M('User');
            $userDueDetailObj = M('UserDueDetail');
            $userBankObj = M('UserBank');
            
            $res = array();
            
            if($start_time){
                $end_time = date("Y-m-d",strtotime($start_time)+86400);
                $conditions = '';
                $cond[] = "add_time>='$start_time'";
                $cond[] = "add_time<'$end_time'";
                    
                $conditions = implode(' and ', $cond);
                //当天新注册的用户数
                $res['regCount'] = $userObj->where($conditions)->count();
                
                //当天所有标的投资用户数
                $res['dayInvestCount'] = $userDueDetailObj->where($conditions .' and user_id >0')->count('DISTINCT user_id');
                
                //截止到统计当日新手标累计投资额≥5万的用户数；只统计处于活动期间内的用户。
                $sql = "SELECT d.user_id,SUM(due_capital) from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE d.add_time <'$end_time' and d.user_id > 0 and p.new_preferential = 1 GROUP BY d.user_id HAVING SUM(d.due_capital )>50000";
                $list = M()->query($sql);                
                $res['investAmount5'] = count($list);

                //投资五次新手标的用户数量
                $sql = "SELECT * from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE d.add_time <='$end_time' and d.user_id > 0 and p.new_preferential = 1 GROUP BY d.user_id HAVING count(d.id)>4;";
                $list = M()->query($sql);
                $res['investCount5'] = count($list);
                
                $invest_type_1=$this->staticsPeopleAmount($start_time,$end_time, 1);
                $invest_type_2=$this->staticsPeopleAmount($start_time,$end_time, 2);
                $invest_type_3=$this->staticsPeopleAmount($start_time,$end_time, 3);
                $invest_type_4=$this->staticsPeopleAmount($start_time,$end_time, 4);
                $invest_type_5=$this->staticsPeopleAmount($start_time,$end_time, 5);
                
                $res['invest_type_people_1'] = $invest_type_1['cnt'];
                $res['invest_type_amount_1'] = $invest_type_1['total'];
                
                $res['invest_type_people_2'] = $invest_type_2['cnt'];
                $res['invest_type_amount_2'] = $invest_type_2['total'];
                
                $res['invest_type_people_3'] = $invest_type_3['cnt'];
                $res['invest_type_amount_3'] = $invest_type_3['total'];
                
                $res['invest_type_people_4'] = $invest_type_4['cnt'];
                $res['invest_type_amount_4'] = $invest_type_4['total'];
                
                $res['invest_type_people_5'] = $invest_type_5['cnt'];
                $res['invest_type_amount_5'] = $invest_type_5['total'];
                
                //当日新手标投资总额
                $res['todayInvestAmount'] = 0;
                $sql = "SELECT SUM(due_capital) as due_capital from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE d.add_time >='$start_time' and d.add_time<'$end_time' and d.user_id > 0 and p.new_preferential = 1";
                $list = M()->query($sql);
                if($list) $res['todayInvestAmount'] = $list[0]['due_capital'];
                
                $res['time'] = $start_time;
            }
        
            $params = array(
                'start_time' => $start_time,
                'ret' => $res,
            );
            F('event20170407-'.$start_time, $res);
            $this->assign('params', $params);
            $this->display();
        }else{
            $start_time = I('post.start_time', date('Y-m-d'), 'strip_tags');   
            if($start_time) $quest .= '/st/'.$start_time;
            redirect(C('ADMIN_ROOT') . '/hd/event20170407'.$quest);
        }
    }
    
    //标的期限类型 1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
    private function staticsPeopleAmount($stime,$etime,$type){
        $ret = array(
            'cnt'=>0,
            'total'=>0
        );
        if($stime && $etime && ($type>0 && $type<6)){
            $sql = "SELECT d.user_id as user_id,d.due_capital as due_capital from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE p.new_preferential=1 and p.duration_type = $type and d.add_time >='$stime' and d.add_time <'$etime' and d.user_id>0 GROUP BY d.user_id";
            $list = M()->query($sql);
            if($list) {
                $ret['cnt'] = count($list);
                $ret['total'] =0;
                foreach ($list as $val){
                    $ret['total']+=$val['due_capital'];
                }
            }
            
        }
        return $ret;
    }
    
    private function staticsUserNewProject($stime,$etime,$type,$userId){
        $amount = 0;
        if($stime && $etime && ($type>0 && $type<6) && $userId>0){
            $sql = "SELECT d.due_capital as due_capital from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE p.new_preferential=1 and p.duration_type = $type and  d.user_id = $userId and d.add_time >='$stime' and d.add_time <'$etime'";
            $list = M()->query($sql);
            if($list) {
                $amount = $list[0]['due_capital'];
            }
        }
        return $amount;
    }
    
    public function userList(){
        if(!IS_POST){
            $start_time = I('get.time', date("Y-m-d"), 'strip_tags');
            $type = I('get.type', '', 'strip_tags');
            
            $end_time = date("Y-m-d",strtotime($start_time)+86400);
            $conditions = '';
            $cond[] = "add_time>='$start_time'";
            $cond[] = "add_time<'$end_time'";
            
            $conditions = implode(' and ', $cond);
            
            $userObj = M('User');
            $userDueDetailObj = M('UserDueDetail');
            
            if($type == 1) {
                $title = "当日注册用户数列表-".$start_time;
                $totalCnt =  $userObj->where($conditions)->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = $userObj->field('id,username')->where($conditions)->order("id asc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
                if($list) {
                    $n = 1;
                    foreach ($list as $key=>$val){
                        $list[$key]['n'] = $n++;
                        $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['id'].' and '.$conditions)->sum('due_capital');
                        $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['id']);
                        $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['id']);
                        $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['id']);
                        $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['id']);
                        $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['id']);
                        
                        
                        $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                                                                +$list[$key]['invest_duration_type_2_amount']
                                                                +$list[$key]['invest_duration_type_3_amount']
                                                                +$list[$key]['invest_duration_type_4_amount']
                                                                +$list[$key]['invest_duration_type_5_amount'];
                        
                        $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                        $list[$key]['award_amount'] = 0;
                        
                        if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                            $list[$key]['award_amount'] = 20;
                        }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                            $list[$key]['award_amount'] = 30;
                        }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                            $list[$key]['award_amount'] = 40;
                        }else if($totalNewProjectAmount>=140000){
                            $list[$key]['award_amount'] = 50;
                        }
                        
                        if($list[$key]['invest_duration_type_1_amount']>0 
                            && $list[$key]['invest_duration_type_2_amount']>0
                            && $list[$key]['invest_duration_type_3_amount']>0
                            && $list[$key]['invest_duration_type_4_amount']>0
                            && $list[$key]['invest_duration_type_5_amount']>0
                            ) {
                                if($totalNewProjectAmount>=30000){
                                    $list[$key]['award_amount'] += 10;
                                }
                            }
                    }
                    
                }
            
            } else if($type == 2){//当日总投资用户数(人)
                
                $title = "当日总投资用户数列表-".$start_time;
                
                $totalCnt =  $userDueDetailObj->where("add_time>='$start_time' and add_time<'$end_time' and user_id >0 ")->group('user_id')->count();
                $Page = new \Think\Page($totalCnt, $this->pageSize);
                $list = $userDueDetailObj->field('user_id')->where("add_time>='$start_time' and add_time<'$end_time' and user_id >0 ")->group('user_id')->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                
                if($list) {
                    foreach ($list as $key => $val){
                        $n = 1;
                        foreach ($list as $key=>$val){
                            $list[$key]['n'] = $n++;
                            $list[$key]['id'] = $val['user_id'];
                            $list[$key]['username'] = $userObj->where('id='.$val['user_id'])->getField('username');
                            $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['user_id'].' and '.$conditions)->sum('due_capital');
                            $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['user_id']);
                            $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['user_id']);
                            $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['user_id']);
                            $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['user_id']);
                            $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['user_id']);
                        
                        
                            $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                                                            +$list[$key]['invest_duration_type_2_amount']
                                                            +$list[$key]['invest_duration_type_3_amount']
                                                            +$list[$key]['invest_duration_type_4_amount']
                                                            +$list[$key]['invest_duration_type_5_amount'];
                        
                            $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                            $list[$key]['award_amount'] = 0;
                        
                            if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                                $list[$key]['award_amount'] = 20;
                            }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                                $list[$key]['award_amount'] = 30;
                            }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                                $list[$key]['award_amount'] = 40;
                            }else if($totalNewProjectAmount>=140000){
                                $list[$key]['award_amount'] = 50;
                            }
                        
                            if($list[$key]['invest_duration_type_1_amount']>0
                                && $list[$key]['invest_duration_type_2_amount']>0
                                && $list[$key]['invest_duration_type_3_amount']>0
                                && $list[$key]['invest_duration_type_4_amount']>0
                                && $list[$key]['invest_duration_type_5_amount']>0
                            ) {
                                if($totalNewProjectAmount>=30000){
                                    $list[$key]['award_amount'] += 10;
                                }
                            }
                        }
                    }
                }
                
            } else if($type >= 3 && $type<=7){
                
                
                $_type = $type - 2;
                
                
                //1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
                if($_type ==1){
                    $title = "当日一周新手标投资用户列表";
                } elseif($_type==2){
                    $title = "当日1月新手标投资用户列表";
                } elseif($_type ==3){
                    $title = "当日2月新手标投资用户列表";
                }elseif($_type ==4){
                    $title = "当日3月新手标投资用户列表";
                }else{
                    $title = "当日6月新手标投资用户列表";
                }
                $title.=$start_time;

                $query = $userDueDetailObj->alias('d')
                                            ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                                            ->where("p.new_preferential=1 and p.duration_type = $_type and d.add_time >='$start_time' and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                                            ->field('d.user_id')
                                            ->order('d.id asc')
                                            ->select();
                
                $Page = new \Think\Page(count($query), $this->pageSize);
                
                $list = $userDueDetailObj->alias('d')
                                            ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                                            ->where("p.new_preferential=1 and p.duration_type = $_type and d.add_time >='$start_time' and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                                            ->field('d.user_id as user_id')
                                            ->order('d.id asc')
                                            ->limit($Page->firstRow . ',' . $Page->listRows)
                                            ->select();
                
                if($list) {
                    foreach ($list as $key => $val){
                        $n = 1;
                        foreach ($list as $key=>$val){
                            $list[$key]['n'] = $n++;
                            $list[$key]['id'] = $val['user_id'];
                            $list[$key]['username'] = $userObj->where('id='.$val['user_id'])->getField('username');
                            $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['user_id'].' and '.$conditions)->sum('due_capital');
                            $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['user_id']);
                            $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['user_id']);
                            $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['user_id']);
                            $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['user_id']);
                            $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['user_id']);
                
                
                            $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                            +$list[$key]['invest_duration_type_2_amount']
                            +$list[$key]['invest_duration_type_3_amount']
                            +$list[$key]['invest_duration_type_4_amount']
                            +$list[$key]['invest_duration_type_5_amount'];
                
                            $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                            $list[$key]['award_amount'] = 0;
                
                            if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                                $list[$key]['award_amount'] = 20;
                            }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                                $list[$key]['award_amount'] = 30;
                            }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                                $list[$key]['award_amount'] = 40;
                            }else if($totalNewProjectAmount>=140000){
                                $list[$key]['award_amount'] = 50;
                            }
                
                            if($list[$key]['invest_duration_type_1_amount']>0
                                && $list[$key]['invest_duration_type_2_amount']>0
                                && $list[$key]['invest_duration_type_3_amount']>0
                                && $list[$key]['invest_duration_type_4_amount']>0
                                && $list[$key]['invest_duration_type_5_amount']>0
                            ) {
                                if($totalNewProjectAmount>=30000){
                                    $list[$key]['award_amount'] += 10;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->assign("list",$list);
        $this->assign("type",$type);
        $this->assign("title",$title);
        $this->assign("start_time",$start_time);
        $this->assign('show', $Page->show());
        $this->display('userList');
    }
    
    public function userList2(){
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $start_time = I('get.time', date("Y-m-d"), 'strip_tags');
        
        $end_time = date("Y-m-d",strtotime($start_time)+86400);
        $conditions = '';
        $cond[] = "add_time>='$start_time'";
        $cond[] = "add_time<'$end_time'";
        
        
        $query = $userDueDetailObj->alias('d')
                ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                ->where("p.new_preferential=1 and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                ->field('d.user_id')
                ->order('d.id asc')
                ->having('SUM(d.due_capital )>50000')
                ->select();
        
        $Page = new \Think\Page(count($query), $this->pageSize);
        
        $list = $userDueDetailObj->alias('d')
                ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                ->where("p.new_preferential=1 and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                ->field('d.user_id as user_id')
                ->order('d.id asc')
                ->having('SUM(d.due_capital )>50000')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        
        if($list){
            $n=1;
            foreach ($list as $key=>$val){
                $list[$key]['n'] = $n++;
                $list[$key]['id'] = $val['user_id'];
                $list[$key]['username'] = $userObj->where('id='.$val['user_id'])->getField('username');
                $list[$key]['invest_duration_type_1_amount'] = $this->getUserInvestAmount($val['user_id'],1,1,$end_time);
                $list[$key]['invest_duration_type_2_amount'] = $this->getUserInvestAmount($val['user_id'],1,2,$end_time);
                $list[$key]['invest_duration_type_3_amount'] = $this->getUserInvestAmount($val['user_id'],1,3,$end_time);
                $list[$key]['invest_duration_type_4_amount'] = $this->getUserInvestAmount($val['user_id'],1,4,$end_time);
                $list[$key]['invest_duration_type_5_amount'] = $this->getUserInvestAmount($val['user_id'],1,5,$end_time);
                $list[$key]['invest_amount'] = $this->getUserInvestAmount($val['user_id'],0,5,$end_time);
            }
        }
        
        $this->assign("list",$list);
        $this->assign('show', $Page->show());
        $this->display('userList2');
    }
    //取新手标和非新手标的投资金额
    private function getUserInvestAmount($userId,$isNew,$type,$etime){
        $totalAmount = 0;
        if($isNew == 1){
            $sql = "SELECT sum(d.due_capital) as due_capital from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE p.new_preferential=1 and p.duration_type = $type and  d.user_id = $userId and d.add_time<'$etime'";
        } else{
            $sql = "SELECT sum(d.due_capital) as due_capital from s_user_due_detail d left JOIN s_project p on d.project_id = p.id WHERE p.new_preferential!=1 and d.user_id = $userId and d.add_time<'$etime'";
        }
        $ret = M()->query($sql);
        if($ret){
            $totalAmount = $ret[0]['due_capital'];
        }
        return $totalAmount;
    }

    public function listExport(){
        
        $start_time = I('get.time', '', 'strip_tags');
        
        $cacheData = F('event20170407-'.$start_time);
        
        if($cacheData){
            
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('新手标活动单日数据统计')
                                                ->setCellValue("A1", "日期")
                                                ->setCellValue("B1", "当日注册用户人数")
                                                ->setCellValue("C1", "当日总投资用户人数")
                                                ->setCellValue("D1", "投资额到达五万用户数")
                                                ->setCellValue("E1", "累计投资五次用户数")
                                                ->setCellValue("F1", "当日应发放奖励(元)")
                                                ->setCellValue("G1", "当日实际发放奖励(元)")
                                                ->setCellValue("H1", "当日新手标投资总额(元)")
                                                ->setCellValue("I1", "投资1周新手标人数(人)")
                                                ->setCellValue("J1", "投资1周新手标金额(元)")
                                                ->setCellValue("K1", "投资1月新手标人数(人)")
                                                ->setCellValue("L1", "投资1月新手标金额(元)")
                                                ->setCellValue("M1", "投资2月新手标人数(人)")
                                                ->setCellValue("N1", "投资2月新手标金额(元)")
                                                ->setCellValue("O1", "投资3月新手标人数(人)")
                                                ->setCellValue("P1", "投资3月新手标金额(元)")
                                                ->setCellValue("Q1", "投资6月新手标人数(人)")
                                                ->setCellValue("R1", "投资6月新手标金额(元)");
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(30);
            $pos = 2;
            $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $cacheData['time']);
            $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $cacheData['regCount']);
            $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $cacheData['dayInvestCount']);
            $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $cacheData['investAmount5']);
            $objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $cacheData['investCount5']);
            $objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, '-');
            $objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, '-');
            $objPHPExcel->getActiveSheet()->setCellValue("H" . $pos, $cacheData['todayInvestAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, $cacheData['invest_type_people_1']);
            $objPHPExcel->getActiveSheet()->setCellValue("J" . $pos, $cacheData['invest_type_amount_1']);
            $objPHPExcel->getActiveSheet()->setCellValue("K" . $pos, $cacheData['invest_type_people_2']);
            $objPHPExcel->getActiveSheet()->setCellValue("L" . $pos, $cacheData['invest_type_amount_2']);
            $objPHPExcel->getActiveSheet()->setCellValue("M" . $pos, $cacheData['invest_type_people_3']);
            $objPHPExcel->getActiveSheet()->setCellValue("N" . $pos, $cacheData['invest_type_amount_3']);
            $objPHPExcel->getActiveSheet()->setCellValue("O" . $pos, $cacheData['invest_type_people_4']);
            $objPHPExcel->getActiveSheet()->setCellValue("P" . $pos, $cacheData['invest_type_amount_4']);
            $objPHPExcel->getActiveSheet()->setCellValue("Q" . $pos, $cacheData['invest_type_people_5']);
            $objPHPExcel->getActiveSheet()->setCellValue("R" . $pos, $cacheData['invest_type_amount_5']);
            //$cacheData
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="(新手标活动单日数据统计'.$cacheData['time'].'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        } else {
            echo '12';
        } 
        
    }
    
    
    public function userListExport(){
        $start_time = I('get.start_time', date("Y-m-d"), 'strip_tags');
        $type = I('get.type', '', 'strip_tags');
        
        $end_time = date("Y-m-d",strtotime($start_time)+86400);
        $conditions = '';
        $cond[] = "add_time>='$start_time'";
        $cond[] = "add_time<'$end_time'";
        
        $conditions = implode(' and ', $cond);
        
        $userObj = M('User');
        $userDueDetailObj = M('UserDueDetail');
        //当日注册用户数
        if($type == 1) {
            $title = '当日注册用户数列表';
            $list = $userObj->field('id,username')->where($conditions)->order("id asc")->select();
            if($list) {
                $n = 1;
                foreach ($list as $key=>$val){
                    $list[$key]['n'] = $n++;
                    $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['id'].' and '.$conditions)->sum('due_capital');
                    $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['id']);
                    $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['id']);
                    $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['id']);
                    $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['id']);
                    $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['id']);
    
    
                    $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                                            +$list[$key]['invest_duration_type_2_amount']
                                            +$list[$key]['invest_duration_type_3_amount']
                                            +$list[$key]['invest_duration_type_4_amount']
                                            +$list[$key]['invest_duration_type_5_amount'];
    
                    $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                    $list[$key]['award_amount'] = 0;
    
                    if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                        $list[$key]['award_amount'] = 20;
                    }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                        $list[$key]['award_amount'] = 30;
                    }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                        $list[$key]['award_amount'] = 40;
                    }else if($totalNewProjectAmount>=140000){
                        $list[$key]['award_amount'] = 50;
                    }
    
                    if($list[$key]['invest_duration_type_1_amount']>0
                        && $list[$key]['invest_duration_type_2_amount']>0
                        && $list[$key]['invest_duration_type_3_amount']>0
                        && $list[$key]['invest_duration_type_4_amount']>0
                        && $list[$key]['invest_duration_type_5_amount']>0
                    ) {
                        if($totalNewProjectAmount>=30000){
                            $list[$key]['award_amount'] += 10;
                        }
                    }
                }
    
            }
    
        } else if($type == 2){//当日总投资用户数(人)
                $title = "当日总投资用户数列表";
                $list = $userDueDetailObj->field('user_id')->where("add_time>='$start_time' and add_time<'$end_time' and user_id >0 ")->group('user_id')->order('id asc')->select();
        
                if($list) {
                    foreach ($list as $key => $val){
                        $n = 1;
                        foreach ($list as $key=>$val){
                            $list[$key]['n'] = $n++;
                            $list[$key]['id'] = $val['user_id'];
                            $list[$key]['username'] = $userObj->where('id='.$val['user_id'])->getField('username');
                            $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['user_id'].' and '.$conditions)->sum('due_capital');
                            $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['user_id']);
                            $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['user_id']);
                            $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['user_id']);
                            $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['user_id']);
                            $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['user_id']);
        
        
                            $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                            +$list[$key]['invest_duration_type_2_amount']
                            +$list[$key]['invest_duration_type_3_amount']
                            +$list[$key]['invest_duration_type_4_amount']
                            +$list[$key]['invest_duration_type_5_amount'];
        
                            $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                            $list[$key]['award_amount'] = 0;
        
                            if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                                $list[$key]['award_amount'] = 20;
                            }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                                $list[$key]['award_amount'] = 30;
                            }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                                $list[$key]['award_amount'] = 40;
                            }else if($totalNewProjectAmount>=140000){
                                $list[$key]['award_amount'] = 50;
                            }
        
                            if($list[$key]['invest_duration_type_1_amount']>0
                                && $list[$key]['invest_duration_type_2_amount']>0
                                && $list[$key]['invest_duration_type_3_amount']>0
                                && $list[$key]['invest_duration_type_4_amount']>0
                                && $list[$key]['invest_duration_type_5_amount']>0
                            ) {
                                if($totalNewProjectAmount>=30000){
                                    $list[$key]['award_amount'] += 10;
                                }
                            }
                        }
                    }
                }
        
            } else if($type >= 3 && $type<=7){
        
                $_type = $type - 2;
                
                //1(1周新手标);2(1月标);3(2月标);4(3月标);5(6月标)
                if($_type ==1){
                    $title = "当日一周新手标投资用户列表";
                } elseif($_type==2){
                    $title = "当日1月新手标投资用户列表";
                } elseif($_type ==3){
                    $title = "当日2月新手标投资用户列表";
                }elseif($_type ==4){
                    $title = "当日3月新手标投资用户列表";
                }else{
                    $title = "当日6月新手标投资用户列表";
                }
        
                $list = $userDueDetailObj->alias('d')
                                            ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                                            ->where("p.new_preferential=1 and p.duration_type = $_type and d.add_time >='$start_time' and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                                            ->field('d.user_id as user_id')
                                            ->order('d.id asc')
                                            ->select();
        
                if($list) {
                    foreach ($list as $key => $val){
                        $n = 1;
                        foreach ($list as $key=>$val){
                            $list[$key]['n'] = $n++;
                            $list[$key]['id'] = $val['user_id'];
                            $list[$key]['username'] = $userObj->where('id='.$val['user_id'])->getField('username');
                            $list[$key]['todayInvestAmount'] = $userDueDetailObj->where('user_id='.$val['user_id'].' and '.$conditions)->sum('due_capital');
                            $list[$key]['invest_duration_type_1_amount'] = $this->staticsUserNewProject($start_time, $end_time, 1, $val['user_id']);
                            $list[$key]['invest_duration_type_2_amount'] = $this->staticsUserNewProject($start_time, $end_time, 2, $val['user_id']);
                            $list[$key]['invest_duration_type_3_amount'] = $this->staticsUserNewProject($start_time, $end_time, 3, $val['user_id']);
                            $list[$key]['invest_duration_type_4_amount'] = $this->staticsUserNewProject($start_time, $end_time, 4, $val['user_id']);
                            $list[$key]['invest_duration_type_5_amount'] = $this->staticsUserNewProject($start_time, $end_time, 5, $val['user_id']);
        
        
                            $totalNewProjectAmount = $list[$key]['invest_duration_type_1_amount']
                            +$list[$key]['invest_duration_type_2_amount']
                            +$list[$key]['invest_duration_type_3_amount']
                            +$list[$key]['invest_duration_type_4_amount']
                            +$list[$key]['invest_duration_type_5_amount'];
        
                            $list[$key]['invest_amount'] = ($list[$key]['todayInvestAmount'] - $totalNewProjectAmount);
                            $list[$key]['award_amount'] = 0;
        
                            if($totalNewProjectAmount>=30000 && $totalNewProjectAmount<50000){
                                $list[$key]['award_amount'] = 20;
                            }else if($totalNewProjectAmount>=50000 && $totalNewProjectAmount<100000){
                                $list[$key]['award_amount'] = 30;
                            }else if($totalNewProjectAmount>=100000 && $totalNewProjectAmount<1400000){
                                $list[$key]['award_amount'] = 40;
                            }else if($totalNewProjectAmount>=140000){
                                $list[$key]['award_amount'] = 50;
                            }
        
                            if($list[$key]['invest_duration_type_1_amount']>0
                                && $list[$key]['invest_duration_type_2_amount']>0
                                && $list[$key]['invest_duration_type_3_amount']>0
                                && $list[$key]['invest_duration_type_4_amount']>0
                                && $list[$key]['invest_duration_type_5_amount']>0
                            ) {
                                if($totalNewProjectAmount>=30000){
                                    $list[$key]['award_amount'] += 10;
                                }
                            }
                        }
                    }
                }
            }
            
            
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('新手标活动单日数据统计')
                                        ->setCellValue("A1", "用户Id")
                                        ->setCellValue("B1", "当日投资总额")
                                        ->setCellValue("C1", "1周新手标投资金额")
                                        ->setCellValue("D1", "1月新手标投资金额")
                                        ->setCellValue("E1", "2月新手标投资金额")
                                        ->setCellValue("F1", "3月新手标投资金额")
                                        ->setCellValue("G1", "6月新手标投资金额")
                                        ->setCellValue("H1", "其他标的投资金额")
                                        ->setCellValue("I1", "当日应发放奖励")
                                        ->setCellValue("J1", "当日实际发放奖励");
            
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
            
            $pos = 2;
            foreach ($list as $val){
                $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $val['id']);
                $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $val['todayInvestAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $val['invest_duration_type_1_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $val['invest_duration_type_2_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $val['invest_duration_type_3_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $val['invest_duration_type_4_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $val['invest_duration_type_5_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("H" . $pos, $val['invest_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, $val['award_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("J" . $pos, 0);
                $pos++;
            }
            $title.=$start_time;
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="('.$title.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    

    
    public function userList2Export(){
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        
        $start_time = I('get.start_time', date("Y-m-d"), 'strip_tags');
        
        $end_time = date("Y-m-d",strtotime($start_time)+86400);
        $conditions = '';
        $cond[] = "add_time>='$start_time'";
        $cond[] = "add_time<'$end_time'";
        
        $list = $userDueDetailObj->alias('d')
                        ->join('LEFT JOIN s_project p ON d.project_id = p.id')
                        ->where("p.new_preferential=1 and d.add_time <'$end_time' and d.user_id>0")->group('d.user_id')
                        ->field('d.user_id as user_id')
                        ->order('d.id asc')
                        ->having('SUM(d.due_capital )>50000')
                        ->select();
        
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('投资额到达五万用户列表')
                    ->setCellValue("A1", "用户Id")
                    ->setCellValue("B1", "1周新手标投资金额")
                    ->setCellValue("C1", "1月新手标投资金额")
                    ->setCellValue("D1", "2月新手标投资金额")
                    ->setCellValue("E1", "3月新手标投资金额")
                    ->setCellValue("F1", "6月新手标投资金额")
                    ->setCellValue("G1", "其他标的投资金额");
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        
        if($list){
            $pos = 2;
            foreach ($list as $val){
                $val['invest_duration_type_1_amount'] = $this->getUserInvestAmount($val['user_id'],1,1,$end_time);
                $val['invest_duration_type_2_amount'] = $this->getUserInvestAmount($val['user_id'],1,2,$end_time);
                $val['invest_duration_type_3_amount'] = $this->getUserInvestAmount($val['user_id'],1,3,$end_time);
                $val['invest_duration_type_4_amount'] = $this->getUserInvestAmount($val['user_id'],1,4,$end_time);
                $val['invest_duration_type_5_amount'] = $this->getUserInvestAmount($val['user_id'],1,5,$end_time);
                $val['invest_amount'] = $this->getUserInvestAmount($val['user_id'],0,5,$end_time);
                
                $objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $val['user_id']);
                $objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $val['invest_duration_type_1_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $val['invest_duration_type_2_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $val['invest_duration_type_3_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $val['invest_duration_type_4_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $val['invest_duration_type_5_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $val['invest_amount']);
                $pos++;
            }
        }
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="(投资额到达五万用户列表_'.$start_time.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //好友邀请列表 客服
    public function inviteList(){
        
        if(IS_AJAX){
            $type = I('post.type', '1', 'int');
            $userName = trim(I('post.userName', '', 'strip_tags'));
            $time = I('post.time', '', 'strip_tags');
            
            
            if(!$userName){
                $this->ajaxReturn(array('status'=>0,'info'=>'邀请人手机号码不能为空'));
            }
            
            $userInviteListObj = M('userInviteList');
            $userObj = M('user');
            $userDueDetailObj = M('UserDueDetail');
            
            if($type == 1){
                
                if(!$time){
                    $this->ajaxReturn(array('status'=>0,'info'=>'邀请人手机号码不能为空'));
                }
                
                $time = $time.'-01';
                $currentMonth = date("Y-m-01",strtotime($time));
                $nextMonth = date("Y-m-d",strtotime("$currentMonth +1 month"));
                
                $uid = $userObj->where('username='.$userName)->getField('id');
                
                if(!$uid) $this->ajaxReturn(array('status'=>0,'info'=>'没有查到邀请人'));
                
                $list = $userInviteListObj->field('id,invited_user_id,invited_phone,add_time')
                                          ->where('user_id='.$uid." and add_time>='$time' and add_time<'$nextMonth'")->group('invited_user_id')->select();
                
                if($list) {
                    $awardCash = 0;
                    $totalInvestAmount = 0;
                    $i = 1;
                    foreach ($list as $key=>$val) {
                        
                        $list[$key]['userName'] = $userName;
                        
                        $list[$key]['info'] = $userInviteListObj->field('first_invest_time,first_invest_amount,amount')
                                                                ->where('invited_user_id='.$val['invited_user_id'].' and type in(0,2)')
                                                                ->order('first_invest_amount desc,type desc')->find();                
                        
                        if($list[$key]['info']['amount'] > 5) {
                            $awardCash += $list[$key]['info']['amount'];
                        } else {
                            $list[$key]['info']['amount'] = 0;
                        }
                        
                        $_startTime = $list[$key]['add_time'];
                        $_endTime = date("Y-m-d H:i:s",(strtotime($list[$key]['add_time']) + 86400 * 30));
                        
                        $list[$key]['total_invest_amount'] = $userDueDetailObj->where('user_id='.$val['invited_user_id']." and add_time>='$_startTime' and add_time<='$_endTime'")->sum('due_capital');
                        $totalInvestAmount +=$list[$key]['total_invest_amount'];
                        
                        $list[$key]['i'] = $i++;
                    }
                    $this->ajaxReturn(array('status'=>1,'info'=>$list,'awardCash'=>$awardCash,'totalInvestAmount'=>$totalInvestAmount,'act'=>0));
                } else {
                    $this->ajaxReturn(array('status'=>1,'info'=>'','act'=>0));
                }
            } else {
                
                $list = $userInviteListObj->field('id,invited_user_id,invited_phone,add_time,user_id,first_invest_time,first_invest_amount,amount')
                        ->where("invited_phone='$userName' and type in(0,2)")->order('first_invest_amount desc,type desc')->limit(1)->select();
                
                if($list) {
                    $count = count($list);
                    $i = 1;
                    foreach ($list as $key=>$val) {
                        
                        $list[$key]['info']['amount'] = $val['amount'];
                    
                        $list[$key]['userName'] = $userObj->where('id='.$val['user_id'])->getField('username');
                        $list[$key]['info']['first_invest_time'] = $val['first_invest_time'];
                        $list[$key]['info']['first_invest_amount'] = $val['first_invest_amount'];
                        
                        
                        $list[$key]['total_invest_amount'] = $userDueDetailObj->where('user_id='.$val['invited_user_id'])->sum('due_capital');
                        $list[$key]['i'] = $i++;
                    }
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$list,'act'=>1));
            }
        } else {
            $this->assign('regDate', date('Y-m'));
            $this->display();
        }
    }
    
    //好友邀请列表
    public function yy_inviteList(){
    
        F('yy_inviteList',null);
        
        if(IS_AJAX){
                        
            $type = I('post.type', '1', 'int');
            $userName = trim(I('post.userName', '', 'strip_tags'));
            $time = I('post.time', '', 'strip_tags');
    
    
            if(!$userName){
                $this->ajaxReturn(array('status'=>0,'info'=>'邀请人手机号码不能为空'));
            }
    
            $userInviteListObj = M('userInviteList');
            $userObj = M('user');
            $userDueDetailObj = M('UserDueDetail');
    
            if($type == 1){
    
                if(!$time){
                    $this->ajaxReturn(array('status'=>0,'info'=>'邀请人手机号码不能为空'));
                }
    
                $time = $time.'-01';
                $currentMonth = date("Y-m-01",strtotime($time));
                $nextMonth = date("Y-m-d",strtotime("$currentMonth +1 month"));
    
                
                $uid = $userObj->where('username='.$userName)->getField('id');
                
                if(!$uid) $this->ajaxReturn(array('status'=>0,'info'=>'没有查到邀请人'));
                
    
                $list = $userInviteListObj->field('invited_user_id,invited_phone,add_time')
                                          ->where('user_id='.$uid." and add_time>='$time' and add_time<'$nextMonth'")
                                          ->group('invited_user_id')->select();
    
                if($list) {
                    $i = 1;
                    foreach ($list as $key=>$val) {    
                        $list[$key]['userName'] = $userName;    
                        $list[$key]['info'] = $userInviteListObj->field('first_invest_time,first_invest_amount')
                                            ->where('invited_user_id='.$val['invited_user_id'])
                                            ->order('first_invest_amount desc,type desc')->find();
    
                        
    
                        $_startTime = $list[$key]['add_time'];
                        $_endTime = date("Y-m-d H:i:s",(strtotime($list[$key]['add_time']) + 86400 * 30));
                        
                        //30内累计投资金额
                        $list[$key]['total_invest_amount'] = $userDueDetailObj->where('user_id='.$val['invited_user_id']." and add_time>='$_startTime' and add_time<='$_endTime'")->sum('due_capital');
                        //30天内累计投资次数
                        $list[$key]['total_invest_count'] = $userDueDetailObj->where('user_id='.$val['invited_user_id']." and add_time>='$_startTime' and add_time<='$_endTime'")->count();
                        //最近一次投资时间
                        $list[$key]['last_invest_time'] = $userDueDetailObj->where('user_id='.$val['invited_user_id'])->order('id desc')->getField('add_time');
                        
                        if($list[$key]['last_invest_time']) {
                            $list[$key]['last_invest_time'] = date('Y-m-d H:i:s',strtotime($list[$key]['last_invest_time']));
                        } else {
                            $list[$key]['last_invest_time'] = '';
                        }
                        
                        $list[$key]['i'] = $i++;
                    }
                    F('yy_inviteList',$list);
                    $this->ajaxReturn(array('status'=>1,'info'=>$list,'act'=>0));
                } else {
                    $this->ajaxReturn(array('status'=>1,'info'=>'','act'=>0));
                }
            } else {
    
                $list = $userInviteListObj->field('id,invited_user_id,invited_phone,add_time,user_id,first_invest_time,first_invest_amount,amount')
                        ->where("invited_phone='$userName' and type in(0,2)")->order('first_invest_amount desc,type desc')->limit(1)->select();
    
                if($list) {
    
                    $i = 1;
                    foreach ($list as $key=>$val) {
    
                        $list[$key]['userName'] = $userObj->where('id='.$val['user_id'])->getField('username');
                        $list[$key]['info']['first_invest_time'] = $val['first_invest_time'];
                        $list[$key]['info']['first_invest_amount'] = $val['first_invest_amount'];
                        
    
                        $list[$key]['info']['total_invest_amount'] = $userDueDetailObj->where('user_id='.$val['invited_user_id'])->sum('due_capital');
                        
                        
                        $_startTime = $list[$key]['add_time'];
                        $_endTime = date("Y-m-d H:i:s",(strtotime($list[$key]['add_time']) + 86400 * 30));
                        
                        //30内累计投资金额
                        $list[$key]['total_invest_amount'] = $userDueDetailObj->where('user_id='.$val['invited_user_id']." and add_time>='$_startTime' and add_time<='$_endTime'")->sum('due_capital');
                        //30天内累计投资次数
                        $list[$key]['total_invest_count'] = $userDueDetailObj->where('user_id='.$val['invited_user_id']." and add_time>='$_startTime' and add_time<='$_endTime'")->count();
                        //最近一次投资时间
                        $list[$key]['last_invest_time'] = $userDueDetailObj->where('user_id='.$val['invited_user_id'])->getField('add_time');
                        
                        if($list[$key]['last_invest_time']) {
                            $list[$key]['last_invest_time'] = date('Y-m-d H:i:s',strtotime($list[$key]['last_invest_time']));
                        } else {
                            $list[$key]['last_invest_time'] = '';
                        }
                        
                        $list[$key]['i'] = $i++;
                    }
                }
                F('yy_inviteList',$list);
                $this->ajaxReturn(array('status'=>1,'info'=>$list,'act'=>1));
            }
        } else {
            $this->assign('regDate', date('Y-m'));
            $this->display();
        }
    }
    
    
    public function toExport(){
        $list = F('yy_inviteList');
        //if(!$list) exit('没有记录');
        vendor('PHPExcel.PHPExcel');
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
                    ->setCreator("票票喵 -邀请好友")
                    ->setLastModifiedBy("票票喵 -邀请好友")
                    ->setTitle("title")
                    ->setSubject("subject")
                    ->setDescription("description")
                    ->setKeywords("keywords")
                    ->setCategory("Category");
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('好友邀请')
                    ->setCellValue("A1", "编号")
                    ->setCellValue("B1", "邀请人手机号码")
                    ->setCellValue("C1", "被邀请人手机号码")
                    ->setCellValue("D1", "注册日期")
                    ->setCellValue("E1", "首投日期(30天内)")
                    ->setCellValue("F1", "首投金额(30天内)")
                    ->setCellValue("G1", "累计投资金额(30天内)")
                    ->setCellValue("H1", "累计投资次数(30天内)")
                    ->setCellValue("I1", "最近一次投资日期");
        
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
        $pos = 2;
        foreach ($list as $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['i']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("B".$pos,$val['userName']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("C".$pos,$val['invited_user_id']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("D".$pos,$val['add_time']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("E".$pos,$val['info']['first_invest_time']); // 开户行名称
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("F".$pos,$val['info']['first_invest_amount']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("G".$pos,$val['total_invest_amount']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("H".$pos,$val['total_invest_count']); 
            $objPHPExcel->getActiveSheet()->setCellValueExplicit("I".$pos,$val['last_invest_time']); 
            $pos++;
        }
        ob_end_clean();
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="邀请好友 _'.date(YmdHis).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    
    public function invite_report(){
        if (!IS_POST) {
            $search = urldecode(I('get.user_name', '', 'strip_tags'));
            $date = date("Y-m-d");//I('get.date','','strip_tags');
            /*
            $cond[] = 'count_date = 5 and is_delete = 0';
            if($search) {
                $cond[] = "title like '%" . $search . "%'";
            }
        
            if($date) {
                $end_time = $date.'23:59:59';
                $cond[] = "repayment_time >= '$rtime' and repayment_time <'$end_time'";
            }*/
            
            $cond = "count_date='$date'";
        
            //$cond = implode(' and ', $cond);
            $counts = M('userInviteReport')->where($cond)->count();
            $Page = new \Think\Page($counts, /*$this->pageSize*/100);
            $show = $Page->show();
            $list = M('userInviteReport')->where($cond)->order('invite_count desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            //$this->assign('params', array('key'=>$search,'rtime'=>$rtime));
            $this->assign('list',$list);
            $this->assign('show',$show);
            $this->display();
        } else {
            $user_name = I('post.user_name', '', 'strip_tags');
            $date = I('post.date', '', 'strip_tags');        
            $quest = '';        
            if($user_name) $quest .= '/s/'.urlencode($user_name);
            if($date) $quest .= '/date/'.$date;        
            redirect(C('ADMIN_ROOT') . '/hd/invite_report'.$quest);
        }
    }


    public function invite_report_excel(){
        $search = urldecode(I('get.user_name', '', 'strip_tags'));
        $cond = '';
        $date = date("Y-m-d");//I('get.date','','strip_tags');

        $cond = "count_date='$date'";

        $counts = M('userInviteReport')->where($cond)->count();
        $list = M('userInviteReport')->where($cond)->order('invite_count desc')->select();
        //$this->assign('params', array('key'=>$search,'rtime'=>$rtime));
        $this->assign('list',$list);


        $title  = '邀请好友活动数据复盘';

        vendor('PHPExcel.PHPExcel');

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")

            ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");

        $objPHPExcel->setActiveSheetIndex(0)->setTitle($title)
            ->setCellValue("A1", "编号")
            ->setCellValue("B1", "邀请人姓名")
            ->setCellValue("C1", "邀请人手机号")
            ->setCellValue("D1", "邀请人数")
            ->setCellValue("E1", "邀请人数(已投资)")
            ->setCellValue("F1", "现金奖励发放总金额")
            ->setCellValue("G1", "5元红包发放数量")
            ->setCellValue("H1", "5元红包使用数量")
            ->setCellValue("I1", "5元红包带动投资金额")
            ->setCellValue("J1", "被邀请人总投资金额");
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

        // 设置列表值
        $pos = 2;
        $n = 1;

        $status = ["未使用","已使用","过期"];
        foreach ($list as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['id']);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['mobile']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['invite_count']);
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['invite_invest_count']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['award_cash']);//number_format($val['award_cash'], 2)
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['redbag_count']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $val['redbag_use_count']);
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $val['redbag_invest_amt']);
            $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $val['invite_total_amt']);
            $pos += 1;
            $n++;
        }
        ob_end_clean();//清除缓冲区,避免乱码
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="'.$title.'('.date("Y-m-d").').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;

    }
    
}
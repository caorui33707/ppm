<?php
namespace Admin\Controller;

/**
 * 浮动管理
 */
class DynamicRateController extends AdminController {
    
    protected $pageSize = 15;

    
    
    //产品统计
    public function project_statistics(){
        
        if(!IS_POST){
            $key = trim(I('get.key', '', 'strip_tags'));            
            $repayment_time = I('get.dt', '', 'strip_tags');
            $status = I('get.status',0,'int');
            
            $cond = 'new_preferential=9';
            
            if($status>0){
                $cond .= ' and status = '.$status;
            } else {
                $cond .= ' and status in(2,3,4,5)';
            }
            
            if($key){
                $cond .=" and title like '%$key%'";
            }
            if ($repayment_time ){                
                $st = $repayment_time.' 00:00:00';
                $et = $repayment_time.' 23:59:59';                
                $cond .=" and end_time >='$st' and end_time <='$et'";
            }
            
            $cond .= ' and is_delete = 0';       
                    
            $totalCnt =  M("Project")->where($cond)->count();
        
            $Page = new \Think\Page($totalCnt, $this->pageSize);
        
            $list = M("Project")->field('id,title,end_time,status,user_interest')
                                ->where($cond)->order("end_time DESC")
                                ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
            if(!empty($list)) {
        
                foreach ($list as $k => $val) {                  
                    
                    $invest_list = M('userDueDetail')->field('duration_day,due_capital,interest_coupon')
                                            ->where('project_id='.$val['id'].' and user_id>0')->select();
                                                                    
                    $list[$k]['coupon_income'] = 0;
                    $list[$k]['coupon_income_count'] = 0;
                    
                    foreach ($invest_list as $v){  
                        $list[$k]['due_capital'] += $v['due_capital'];                        
                        if($v['interest_coupon'] > 0 ){
                            $list[$k]['coupon_income'] += $v['due_capital'] * $v['duration_day'] * ($v['interest_coupon'])/ 100 / 365;
                            $list[$k]['coupon_income_count']++;
                        }
                    }
                }
            }
            $param = array(
                "repayment_time" => $repayment_time,
                'status' => $status,
                'key'=>$key,
            );
            $this->assign("params",$param);
            $this->assign("list",$list);
            $this->assign('show', $Page->show());
            $this->display();
        }
    }
    
    /**
     * 券包使用明细
     *
     */
    
    public function buy_detail_list() {
    
        $projectId = I("get.project_id",0,'int');
        $userName = trim(I("post.userName","","strip_tags"));
        $page = I('get.p', 1, 'int');   // 页码
        $res = array();
    
        if ($projectId>0) {
            $userId = '';
            $cond = 'project_id='.$projectId;
            if($userName) {
                $this->assign("userName",$userName);
                $user = M("User")->field('id')->where("username='$userName'")->select();
                if($user){
                    foreach ($user as $val){
                        $userId .= $val['id'].',';
                    }
                    $userId = trim($userId,',');
                }
                if ($userId) {
                    $cond .= " and user_id in($userId)";
                } 
            }
    
            $totalCnt =  M("userDueDetail")->where($cond)->count();
            $Page = new \Think\Page($totalCnt, $this->pageSize);
            $list = M("userDueDetail")->field('user_id,duration_day,due_capital,interest_coupon,add_time')
                                    ->where($cond)->order("modify_time DESC")
                                    ->limit($Page->firstRow . ',' . $Page->listRows)->select();
            if($list) {
                $n = 0;
                foreach ($list as $key => $val) {    
                    $list[$key]['n'] = ++ $n;    
                    $list[$key]['user_info'] = M('User')->field('username,real_name')->where('id='.$val['user_id'])->find();    
                    $list[$key]['income'] = $val['due_capital'] * $val['duration_day'] * ($val['interest_coupon'])/ 100 / 365;
                }
            }
            
            $projectInfo = M('Project')->field('title,user_interest')->where('id='.$projectId)->find();
        }
    
        $this->assign("projectInfo",$projectInfo);
        $this->assign('page', $page);
        $this->assign("list",$list);
        $this->assign('show', $Page->show());
        $this->display();
    }
    
    //购买明细导出
    public function buy_detail_list_export() {    
        $projectId = I("get.project_id",0,'int');
        
        if($projectId<=0){
            return;
        }
        
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
                    ->setSubject("subject")->setDescription("description")
                    ->setKeywords("keywords")->setCategory("Category");
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('购买记录')
                    ->setCellValue("A1", "编号")
                    ->setCellValue("B1", "姓名")
                    ->setCellValue("C1", "账号")
                    ->setCellValue("D1", "项目利率")
                    ->setCellValue("E1", "加息利率")
                    ->setCellValue("F1", "投资金额")
                    ->setCellValue('G1','加息收益')
                    ->setCellValue('H1','购买日历');
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        
            
        $sql = "SELECT dd.duration_day,dd.due_capital,dd.interest_coupon,dd.add_time,u.username,u.real_name,dd.user_id from s_user_due_detail as dd left JOIN s_user as u ON dd.user_id = u.id WHERE dd.project_id = $projectId and dd.user_id>0";
        $list = M()->query($sql);
        
        
        if($list) {
            $user_interest = M('Project')->where('id='.$projectId.' and is_delete=0')->getField('user_interest');
            $n = 1;
            $pos = 2;
            foreach ($list as $key => $val) {
                
                $user_info = M('User')->field('username,real_name')->where('id='.$val['user_id'])->find();
                $income = $val['due_capital'] * $val['duration_day'] * ($val['interest_coupon'])/ 100 / 365;
            
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n++);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $user_info['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $user_info['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $user_interest);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['interest_coupon']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['due_capital']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($income, 2));
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
                $pos++;
            
            }
        }        
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="购买记录('.date('Y-m-d H:i:s').').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    //每日数据
    public function daily_statistics(){        
        $start_time = urldecode(I("get.start_time",date('Y-m-d 00:00:00'),'strip_tags'));
        $end_time = urldecode(I("get.end_time",date("Y-m-d 23:59:59"),'strip_tags'));
        
        $start_time = str_replace('+',' ', $start_time);
        $end_time = str_replace('+',' ', $end_time);
        
        $sqlCount = "SELECT dd.id from s_user_due_detail as dd left join s_project as pp ON dd.project_id = pp.id left JOIN s_user as u ON dd.user_id = u.id WHERE pp.new_preferential = 9 and pp.is_delete = 0 and  dd.user_id>0 and dd.interest_coupon>0 and dd.add_time>='$start_time' and dd.add_time<='$end_time'";
                
        $countRes = M()->query($sqlCount);
        
        $Page = new \Think\Page(count($countRes), $this->pageSize);
        
        $sql = "SELECT dd.id,u.username,u.real_name,pp.title,pp.user_interest,dd.add_time,dd.interest_coupon,dd.due_capital,dd.duration_day from s_user_due_detail as dd left join s_project as pp ON dd.project_id = pp.id left JOIN s_user as u ON dd.user_id = u.id WHERE pp.new_preferential = 9 and pp.is_delete = 0 and  dd.user_id>0 and dd.interest_coupon>0 and dd.add_time>='$start_time' and dd.add_time<='$end_time' limit $Page->firstRow".','. "$Page->listRows";
        
        $list = M()->query($sql);
        
        $total_coupon_income = 0;
        if($list) {
            foreach ($list as $key =>$val) {
                $list[$key]['coupon_income'] += round(($val['due_capital'] * $val['duration_day'] * ($val['interest_coupon'])/ 100 / 365),2);
                $total_coupon_income +=$list[$key]['coupon_income'];
            }
        }
        
        $param = array(
            "start_time" => $start_time,
            "end_time" => $end_time,
            'total_cnt' => count($countRes),
            'total_coupon_income' => $total_coupon_income,
        );
        $this->assign("params",$param);
        $this->assign("list",$list);
        $this->assign('showPage',$Page->show());
        $this->display();
    }
    
    public function daily_statistics_export(){
        $start_time = urldecode(I("get.start_time",date('Y-m-d 00:00:00'),'strip_tags'));
        $end_time = urldecode(I("get.end_time",date("Y-m-d 23:59:59"),'strip_tags'));
    
        $start_time = str_replace('+',' ', $start_time);
        $end_time = str_replace('+',' ', $end_time);
    
        //echo $start_time;
        //echo $end_time;
        
        $sql = "SELECT dd.id,u.username,u.real_name,pp.title,pp.user_interest,dd.add_time,dd.interest_coupon,dd.due_capital,dd.duration_day from s_user_due_detail as dd left join s_project as pp ON dd.project_id = pp.id left JOIN s_user as u ON dd.user_id = u.id WHERE pp.new_preferential = 9 and pp.is_delete = 0 and  dd.user_id>0 and dd.interest_coupon>0 and dd.add_time>='$start_time' and dd.add_time<='$end_time'";
    //echo $sql;
        $list = M()->query($sql);
        
        if(!$list) {
            exit('没有记录');
        }
        
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("票票喵理财")->setLastModifiedBy("票票喵理财")->setTitle("title")
                ->setSubject("subject")->setDescription("description")
                ->setKeywords("keywords")->setCategory("Category");
        
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('购买记录')
                ->setCellValue("A1", "编号")
                ->setCellValue("B1", "姓名")
                ->setCellValue("C1", "账号")
                ->setCellValue("D1", "购买时间")
                ->setCellValue("E1", "项目期数")
                ->setCellValue("F1", "加息利率")
                ->setCellValue('G1','投资金额')
                ->setCellValue('H1','加息收益');        
        
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        
        $n = 1;
        $pos = 2;
        
        foreach ($list as $val) {
            $coupon_income = round(($val['due_capital'] * $val['duration_day'] * ($val['interest_coupon'])/ 100 / 365),2);
            $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $n++);
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['username']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['title']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['interest_coupon']);
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $val['due_capital']);
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, $coupon_income);
            $pos++;
        }
        
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="浮动加息每日数据('.date('Y-m-d').').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
    public function index(){
        if(!IS_POST){
            $page = I('get.p', 1, 'int'); // 页码
            $tbObj = M('monthlyIncreaseInterestConfig');
        
            $counts = $tbObj->where(array('is_delete'=>0))->count();
            $Page = new \Think\Page($counts, $this->pageSize);
            $show = $Page->show();
            $list = $tbObj->where(array('is_deleted'=>0))->order('group_id desc,id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $params = array(
                'page' => $page,
            );
            $this->assign('list', $list);
            $this->assign('show', $show);
            $this->assign('params', $params);
            $this->display();
        }else{
        
        }
    }
    
    public function add(){
        if(!IS_POST){
            $this->display();
        }else{
            $dd['status'] = I('post.status', 0, 'int');
            $dd['min_invest'] = trim(I('post.min_invest', 0, 'int'));
            $dd['rank'] = trim(I('post.rank', 1, 'int'));            
            $dd['description'] = trim($_POST['description']);
            $dd['group_id'] = trim(I('post.group_id', 0, 'int'));
            $dd['amount_value'] = trim(I('post.amount_value', '', 'strip_tags'));
            $dd['popup_txt'] = trim($_POST['popup_txt']);
            
            if(!$dd['min_invest']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入最小投资金额，金额必须 大于0'
                ));
            }    
            if(!$dd['amount_value']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入加息值，如果同组排序为1时要配置3个值'
                ));
            }    
            if(!$dd['description']) {
    
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入描述内容'
                ));
            }          
            /*
            if($dd['rank'] == 1){
                if(!$dd['popup_txt']) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '选的同组排序是1，请输入弹窗文案'
                    ));
                }
            } else {
                unset($dd['popup_txt']);
            }
            */
            $dd['add_time'] = date("Y-m-d H:i:s");       
            $dd['add_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            if(!M('monthlyIncreaseInterestConfig')->add($dd)) $this->ajaxReturn(array('status'=>0,'info'=>'添加失败,请重试'));
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/DynamicRate/index'));
        }
    }
    
    /**
     * 编辑文字广告
     */
    public function edit(){
        if(!IS_POST){
            $id = I('get.id', 0, 'int');
            $detail = M('monthlyIncreaseInterestConfig')->where(array('id'=>$id,'is_deleted'=>0))->find();
            if(!$detail){
                $this->error('记录不存在');exit;
            }
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $id = I('post.id', 0, 'int');
            $page = I('post.page', 1, 'int');
    
            $dd['status'] = I('post.status', 0, 'int');
            $dd['min_invest'] = trim(I('post.min_invest', 0, 'float'));
            $dd['rank'] = trim(I('post.rank', 1, 'int'));
            $dd['description'] = trim($_POST['description']);//trim(I('post.description'));
            $dd['group_id'] = trim(I('post.group_id', 0, 'int'));
            $dd['amount_value'] = trim(I('post.amount_value', '', 'strip_tags'));
            
            $dd['popup_txt'] = trim($_POST['popup_txt']);
            
            if(!$dd['min_invest']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入最小投资金额，金额必须 大于0'
                ));
            }
            if(!$dd['amount_value']) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入加息值，如果同组排序为1时要配置3个值'
                ));
            }
            if(!$dd['description']) {
            
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '请输入描述内容'
                ));
            }
            /*
            if($dd['rank'] == 1){
                if(!$dd['popup_txt']) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '选的同组排序为1，请输入弹窗文案'
                    ));
                }
            } else {
                unset($dd['popup_txt']);
            }*/
            
            $dd['edit_user_id'] = $_SESSION[ADMIN_SESSION]['uid'];
            $dd['mtime'] = date("Y-m-d H:i:s");
            
            if(!M('monthlyIncreaseInterestConfig')->where(array('id'=>$id))->save($dd)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'编辑失败,请重试'));
            }
            $this->ajaxReturn(array('status'=>1,'info'=>C('ADMIN_ROOT').'/DynamicRate/index/p/'.$page));
        }
    }
    
    /**
     * 删除广告(软删除)
     */
    public function delete(){
        if(!IS_POST || !IS_AJAX) exit;
        $id = I('post.id', 0, 'int');
    
        $uid = $_SESSION[ADMIN_SESSION]['uid'];
        if(!M('monthlyIncreaseInterestConfig')->where(array('id'=>$id))->save(array('is_deleted'=>1,'edit_user_id'=>$uid,'mtime'=>date("Y-m-d H:i:s")))) {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败,请重试'));
        }
        $this->ajaxReturn(array('status'=>1));
    }
}

<?php
namespace Admin\Controller;

/**
 * 存管
 * @package Admin\Controller
 */
class UserDepositController extends AdminController{
    
    private $pageSize = 15;
	 
    /**
    * 充值提现流水
    * @date: 2017-7-5 上午11:06:57
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function orderLog(){
        
        if(!IS_POST){//get
            $start_time = urldecode((I('get.start_time',date('Y-m-d 00:00:00', strtotime('-1 days')),'strip_tags')));//起始时间
            $end_time   = urldecode((I('get.end_time',date('Y-m-d 23:59:59', strtotime('-1 days')),'strip_tags')));//结束时间
            $type      = I('get.type','0','strip_tags');//类型1表示转入2表示转出
            
            $start_time = str_replace('+',' ', $start_time);            
            $end_time = str_replace('+',' ', $end_time);
            
            $page = I('get.p', 1, 'int'); // 页码
            $totle = 0;
            $params = array(
                'type'=>$type,
                'start_time'=>$start_time,
                'end_time'=>$end_time,
                'page'=>$page
            );
              
            $userWalletRecords = M('userWalletRecords');            
            	
            $conditions = "modify_time>='$start_time' and modify_time<='$end_time'";
            
            if($type>0) {
                $conditions .= " and type=$type";
            }
            
            if($type == 2){
                $conditions = str_replace('modify_time','add_time',$conditions);
            }
                        
            $counts = $userWalletRecords->where($conditions)->count();
            $Page = new \Think\Page($counts, $this->pageSize);
           
            $show = $Page->show();
            
            $list = $userWalletRecords->where($conditions)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            
            foreach ($list as $key=>$val){
                $list[$key]['user'] = M('User')->field('username,real_name,platcust')->where('id='.$val['user_id'])->find();
                $totle +=$val['value'];
                if($val['type'] == 1 || $val['type'] == 4 || $val['type'] == 5){
                    $list[$key]['status_str'] = $val['pay_status'] == 1 ? '未支付' : ($val['pay_status'] == 2 ? '已支付' : '支付失败');
                } else {
                    $list[$key]['status_str'] = $val['status'] == 1 ? '正常' : ($val['status'] == 2 ? '失败' : '等待处理');
                }
            }
            $this->assign('params',$params);
            $this->assign('list',$list);
            $this->assign('totle',$totle);
            $this->assign('show',$show);
            $this->assign('counts',$counts);
            $this->display();
        }else{
            $start_time = I('post.start_time', '', 'strip_tags'); // 起始时间
            $end_time = I('post.end_time', '', 'strip_tags'); // 结束时间         
            $type = I('post.type', '0', 'strip_tags'); // 类型1表示转入2表示转出
            $quest = '/start_time/' . urldecode($start_time) . '/end_time/' . urldecode($end_time) . '/type/' . $type;
            redirect(C('ADMIN_ROOT') . '/UserDeposit/orderLog' . $quest);
        }
        
    }
    
    public function orderLogToExport(){
        ini_set("memory_limit", "512M");
        set_time_limit(0);
        
        $start_time = urldecode((I('get.st',date('Y-m-d 00:00:00', strtotime('-1 days')),'strip_tags')));//起始时间
        $end_time   = urldecode((I('get.et',date('Y-m-d 23:59:59', strtotime('-1 days')),'strip_tags')));//结束时间
        
        $start_time = str_replace('+',' ', $start_time);
        $end_time = str_replace('+',' ', $end_time);
        
        $type = I('get.type', 0, 'strip_tags');                
        
        $userWalletRecords = M('userWalletRecords');
         
        $conditions = "uwr.modify_time>='$start_time' and uwr.modify_time<='$end_time'";
        
        if($type>0){
            $conditions .= ' and uwr.type='.$type;
        }
        
        if($type == 2){
            $conditions = str_replace('modify_time','add_time',$conditions);
        }
        
        $sql = "SELECT uwr.*, u.platcust,u.real_name,u.username FROM s_user_wallet_records as uwr LEFT JOIN s_user u ON uwr.user_id = u.id WHERE $conditions ORDER BY uwr.id desc";
        
        $list = M()->query($sql);
        
        //if($list) {

            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getProperties()->setCreator("票票喵")->setLastModifiedBy("票票喵")->setTitle("title")
                        ->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
                        
            $objPHPExcel->setActiveSheetIndex(0)->setTitle('充值提现明细')
                        ->setCellValue("A1", "编号")
                        ->setCellValue("B1", "存管账号")
                        ->setCellValue("C1", "用户账号")
                        ->setCellValue("D1", "用户姓名")
                        ->setCellValue("E1", "交易编码")
                        ->setCellValue("F1", "涉及金额")
                        ->setCellValue("G1", "交易时间")
                        ->setCellValue("H1", "完成时间")
                        ->setCellValue("I1", "状态")
                        ->setCellValue("J1", "类型");
        
            $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setName('宋体')->setSize(11);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        
            // 设置列表值
            $pos = 2;
        
            foreach($list as $val){
                
                if($val['type'] == 1 || $val['type'] == 4 || $val['type'] == 5){
                    $val['status_str'] = $val['pay_status'] == 1 ? '未支付' : ($val['pay_status'] == 2 ? '已支付' : '支付失败');
                } else {
                    $val['status_str'] = $val['status'] == 1 ? '正常' : ($val['status'] == 2 ? '失败' : '等待处理');
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['id']);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['platcust']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['real_name']);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $val['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['recharge_no']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['value']);
                $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
                $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, date("Y-m-d H:i:s",strtotime($val['modify_time'])));
                $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, $val['status_str']);
                
                
                $type = '';
                switch ($val['type']){
                    case 1:
                        $type='充值';
                        break;
                    case 2:
                        $type='提现';
                        break;
                    case 3:
                        $type='购买定期';
                        break;
                    case 4:
                        $type='回款';
                        break;
                    case 5:
                        $type='现金券';
                        break;
                }
                
                $objPHPExcel->getActiveSheet()->setCellValue("J".$pos, $type);
                
                $pos += 1;
            }
            unset($list);
            ob_end_clean();//清除缓冲区,避免乱码
            header("Content-Type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="充值提现明细('.date("Y-m-d").').xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
       // } else {
        //    $this->ajaxReturn(array('status' => 0, 'info' => "没有记录"));
        //}
    }

}
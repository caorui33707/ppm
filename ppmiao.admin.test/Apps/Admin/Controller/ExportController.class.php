<?php
namespace Admin\Controller;

/**
 * 每日销售列表批量导出Excel
 */
class ExportController extends AdminController {
	/**
     * 每日销售列表导出Excel
     */
    public function daysales_export(){		
	        
			$start_datetime = I('get.sdt', '', 'strip_tags');
			$end_datetime = I('get.edt', '', 'strip_tags');
			if(empty($start_datetime) || empty($end_datetime)){ 
				exit("导出日期区间未设置");
			}
			
            $updatecache = I('get.uc', 1, 'int'); // 更新缓存   
			$start_time = $start_datetime.' 00:00:00.0000';		
            $end_time = $end_datetime.' 23:59:59.999000';			
			$file_datetime = $start_datetime."-".$end_datetime;
			
            $projectObj = M('Project');
            $rechargeLogObj = M('RechargeLog');
            $userDueDetailObj = M("UserDueDetail");
            $userWalletRecordsObj = M("UserWalletRecords");
            $contractObj = M("Contract");
            $contractProjectObj = M("ContractProject");
    
            if($updatecache) $cacheData = null;
            if(!$cacheData){
                $list = $userDueDetailObj->field('project_id,sum(due_capital) totlecapital')->where("add_time>='".$start_time."' and add_time<='".$end_time."'")->group('project_id')->order('project_id')->select();
                $totleMoney = 0;
                $totleMoneyMore = 0;
                $totleGhostMoney = 0; // 幽灵账户购买金额
                $totleYiBaoMoney = 0; // 使用易宝购买金额
                $totleWalletMoney = 0; // 使用钱包购买金额
                $totleFee = 0; // 总手续费
                foreach($list as $key => $val){
                    $list[$key]['project'] = $projectObj->field('id,title,amount,user_interest,financing,start_time,end_time,remark')->where(array('id'=>$val['project_id']))->find();
                    $list[$key]['project']['days'] = count_days($list[$key]['project']['end_time'], $list[$key]['project']['start_time']);
                    $more_money = $userDueDetailObj->where("project_id=".$val['project_id']." and add_time<='".$end_time."'")->sum('due_capital') - $list[$key]['project']['amount'];
                    if($more_money < 0) $more_money = 0;
                    $list[$key]['money_more'] = $more_money;
                    $totleMoney += $val['totlecapital'];
                    $totleMoneyMore += $more_money;
                    $ghost_money = $userDueDetailObj->where("user_id=0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('due_capital');
                    $list[$key]['ghost_money'] = $ghost_money;
                    $totleGhostMoney += $ghost_money;
                    $yibao_money = $rechargeLogObj->where("type=2 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('amount');
                    $list[$key]['yibao_money'] = $yibao_money;
                    $totleYiBaoMoney += $yibao_money;
                    $wallet_money = $userDueDetailObj->where("from_wallet=1 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('due_capital');
                    //$wallet_money = $rechargeLogObj->where("type=3 and status=2 and user_id>0 and project_id=".$val['project_id']." and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('amount');
                    $list[$key]['wallet_money'] = $wallet_money;
                    $totleWalletMoney += $wallet_money;
                    // 计算还款手续费(连连每笔1块,盛付通1W以下1块(包括),1W以上2块)<3家银行用连连:邮政(01000000)，华夏(03040000)，兴业(03090000)>
                    //$cardList = $userDueDetailObj->field('due_capital,card_no')->where("add_time>='".$start_time."' and add_time<='".$end_time."'")->select();
                    $sql = "select a.due_capital,a.card_no,b.bank_code from s_user_due_detail a left join s_user_bank b on b.bank_card_no=a.card_no and b.has_pay_success=2 where a.user_id>0 and a.project_id=".$val['project_id']." and a.add_time>='".$start_time."' and a.add_time<='".$end_time."'";
                    $payFeeList = M()->query($sql);
                    $fee = 0;
                    $llCount = 0;
                    $stfCount = 0;
                    foreach($payFeeList as $k => $v){
                        if(in_array($v['bank_code'], array('01000000','03040000','03090000'))){
                            $llCount += 1;
                            if($v['due_capital'] <= 10000){
                                $fee += 1;
                            }else{
                                $fee += 2;
                            }
                        }else{
                            $stfCount += 1;
                            $fee += 1;
                        }
                    }
                    $list[$key]['fee_info'] = array(
                        'fee' => $fee,
                        'll_count' => $llCount,
                        'stf_count' => $stfCount,
                    );
                    $totleFee += $fee;
                    // 获取产品对应合同相关数据
                    $contractId = $contractProjectObj->where(array('project_name'=>$list[$key]['project']['title']))->getField('contract_id');
                    if($contractId){
                        $list[$key]['contract_info'] = $contractObj->where(array('id'=>$contractId))->find();
                    }
                }
                $walletArr = array(
                    'project' => array('id'=>0,'title'=>'石头钱包','financing'=>'王伟军'),
                    'money_more' => 0,
                    'ghost_money' => 0,
                    'wallet_money' => 0,
                );
                $walletArr['totlecapital'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('value');
                if($walletArr['totlecapital'] > 0){
                    $walletArr['yibao_money'] = $userWalletRecordsObj->where("type=1 and pay_status=2 and pay_type=2 and add_time>='".$start_time."' and add_time<='".$end_time."'")->sum('value');
                    $totleMoney += $walletArr['totlecapital'];
                    $totleYiBaoMoney += $walletArr['yibao_money'];
                    array_unshift($list, $walletArr);
                }
                if($datetime < date('Y-m-d', time())){
                    $rows = array(
                        'list' => $list,
                        'totle_money' => $totleMoney,
                        'totle_money_more' => $totleMoneyMore,
                        'totle_ghost_money' => $totleGhostMoney,
                        'totle_yibao_money' => $totleYiBaoMoney,
                        'totle_wallet_money' => $totleWalletMoney,
                        'totle_fee' => $totleFee,
                    );
                    F('project_daysales_'.str_replace('-', '_', $datetime), $rows);
                }
            }
			 
		//////////////////////////////
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品名称')->setCellValue("A1", "日期")->setCellValue("B1", "产品名称")->setCellValue("C1", "利率(%)")->setCellValue("D1", "还款手续费(元)")->setCellValue("E1", "合同利率(%)")->setCellValue("F1", "合同手续费(%)")->setCellValue("G1", "募集款数(元)")->setCellValue("H1", "易宝购买(元)")->setCellValue("I1", "钱包购买(元)")->setCellValue("J1", "超过部分(元)")->setCellValue("K1", "幽灵账户(元)")->setCellValue("M1", "期限")->setCellValue("N1", "融资人")->setCellValue("O1", "备注");
        $objPHPExcel->getActiveSheet()->getStyle('A1:O1')->getFont()->setName('宋体')->setSize(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);

        // 设置列表值
        $pos = 2;
        foreach ($list as $key => $val) {
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, date("Y-m-d",$val['contract_info']['add_time']));
            $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['project']['title']);
            $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['project']['user_interest']);
            $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['fee_info']['fee']));
            $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $val['contract_info']['interest']);
            $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $val['contract_info']['fee']);
            $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($val['totlecapital'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, number_format($val['yibao_money'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue("I".$pos, number_format($val['wallet_money'], 2));
			$objPHPExcel->getActiveSheet()->setCellValue("J".$pos, number_format($val['money_more'], 2));
			$objPHPExcel->getActiveSheet()->setCellValue("K".$pos, number_format($val['ghost_money'], 2));
			$objPHPExcel->getActiveSheet()->setCellValue("M".$pos, $val['project']['days']);
			$objPHPExcel->getActiveSheet()->setCellValue("N".$pos, $val['project']['financing']);
			$objPHPExcel->getActiveSheet()->setCellValue("O".$pos, $val['project']['remark']);
            $pos += 1;
        }
		/*
        $objPHPExcel->getActiveSheet()->setCellValue("A".$pos, '合计');
        $objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($totleMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($totleYiBaoMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($totleWalletMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("E".$pos, number_format($totleMoneyMore, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("F".$pos, number_format($totleGhostMoney, 2));
        $objPHPExcel->getActiveSheet()->setCellValue("G".$pos, '');
        $objPHPExcel->getActiveSheet()->setCellValue("H".$pos, '');
		*/
		
        header("Content-Type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="日销售额('.$file_datetime.').xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
		
        exit;
    }
}
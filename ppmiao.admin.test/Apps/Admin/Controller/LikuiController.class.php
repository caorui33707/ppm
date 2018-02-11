<?php
namespace Admin\Controller;

/**
 * Likui测试系统控制器
 * @package Admin\Controller
 */
class LikuiController extends AdminController{

    public function sex(){
		$obj = M('User');
		$list = $obj->field('card_no,id') -> where("add_time<='2015-11-30 23:59:59.999000'")->select();
		$man_num = 0;$woman_num=0;
		foreach($list as $k=>$v){ 
			$sex = substr($v['card_no'], strlen($v['card_no']) - 2, 1);
			if(($sex % 2) != 0){ 
				$man_num++;
			}else{ 
				$woman_num++;
			}
		}
		 echo "男:".$man_num;
		 echo "<br>";
		 echo "女:".$woman_num;
	}
	//地区统计投资金额
	public function area_due(){
		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('地区统计投资金额')->setCellValue("A1", "省份")->setCellValue("B1", "金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$pos = 2;
		$A1="北京市";//北京市
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A1."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A1."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A1);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A2="天津市";//天津市
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A2."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A2."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A2);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A3="重庆市";//重庆市
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A3."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A3."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A3);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A4="上海市";//上海市
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A4."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A4."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A4);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A5="河北省";//河北省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A5."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A5."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A5);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A6="山西省";//山西省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A6."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A6."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A6);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A7="辽宁省";//辽宁省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A7."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A7."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A7);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A8="吉林省";//吉林省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A8."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A8."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A8);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A9="黑龙江省";//黑龙江省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A9."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A9."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A9);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A10="江苏省";//江苏省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A10."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A10."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A10);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A11="浙江省";//浙江省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A11."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A11."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A11);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A12="安徽省";//安徽省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A12."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A12."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A12);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A13="福建省";//福建省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A13."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A13."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A13);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A14="江西省";//江西省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A14."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A14."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A14);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A15="山东省";//山东省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A15."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A15."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A15);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A16="河南省";//河南省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A16."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A16."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A16);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A17="湖北省";//湖北省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A17."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A17."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A17);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A18="湖南省";//湖南省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A18."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A18."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A18);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A19="广东省";//广东省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A19."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A19."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A19);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A20="海南省";//海南省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A20."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A20."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A20);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A21="四川省";//四川省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A21."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A21."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A21);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A22="贵州省";//贵州省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A22."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A22."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A22);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A23="云南省";//云南省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A23."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A23."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A23);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A24="陕西省";//陕西省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A24."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A24."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A24);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A25="甘肃省";//甘肃省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A25."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A25."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A25);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A26="青海省";//青海省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A26."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A26."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A26);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A27="台湾省";//台湾省
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A27."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A27."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A27);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A28="内蒙古自治区";//内蒙古自治区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A28."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A28."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A28);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A29="广西壮族自治区";//广西壮族自治区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A29."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A29."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A29);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A30="宁夏回族自治区";//宁夏回族自治区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A30."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A30."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A30);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A31="新疆维吾尔自治区";//新疆维吾尔自治区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A31."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A31."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A31);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A32="西藏自治区";//西藏自治区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A32."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A32."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A32);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A33="香港特别行政区";//香港特别行政区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A33."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A33."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A33);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		$A34="澳门特别行政区";//澳门特别行政区
		//投资理财金额
		$product_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A34."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$product_list_arr = M()->query($product_sql);
		$product_amount = $product_list_arr[0]['total'];
		//投资钱包金额
		$wallet_sql = "SELECT SUM(m.`value`) AS total  FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m  WHERE m.`province` ='".$A34."' AND m.`add_time`<='2015-12-31 23:59:59.999000') AND m.`add_time`<='2015-12-31 23:59:59.999000'";
		$wallet_list_arr = M()->query($wallet_sql);
		$wallet_amount = $wallet_list_arr[0]['total'];
		//总金额
		$amount = $product_amount + $wallet_amount;
		$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $A34);
		$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $amount);
		$pos += 1;
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="地区统计投资金额").xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');

	}
	public function year(){
		$y20_29 = 0;
		$y30_39 = 0;
		$y40_49 = 0;
		$gt_50 = 0;
		$obj = M('User');
		$list = $obj->field('card_no,id') -> where("add_time<='2015-11-30 23:59:59.999000'")->select();

		foreach($list as $k=>$v){
			$sex = substr($v['card_no'], strlen($v['card_no']) - 2, 1);
			$year = substr($v['card_no'], 6, 4);
			$nowYear = date('Y', time());
			$age = $nowYear - $year;
			if($age>=20 && $age<=29){
				$y20_29++;
			}else if($age>=30 && $age<=39){
				$y30_39++;
			} else if($age>=40 && $age<=49){
				$y40_49++;
			} else if($age>=50){
				$gt_50++;
			}
		}
		echo "20-29:".$y20_29;
		echo "<br>";
		echo "30-39:".$y30_39;
		echo "<br>";
		echo "40-49:".$y40_49;
		echo "<br>";
		echo ">50:".$gt_50;
		/*
		$card_no = "342829194308294338";
		$url = "http://apistore.baidu.com/microservice/icardinfo?id=".$card_no;
		$text_object = json_decode(file_get_contents($url));
		$single_card_no =  $text_object->retData->address;
		$single_card = mb_substr($single_card_no,0,3,'utf-8');
		echo $single_card;
		echo "<br>";
		$province_arr = array('北京市','天津市','重庆市','上海市','河北省','山西省','辽宁省','吉林省','黑龙江省','江苏省','浙江省','安徽省','福建省','江西省','山东省','河南省','湖北省','湖南省','广东省','海南省','四川省','贵州省','云南省','陕西省','甘肃省','青海省','台湾省','内蒙古自治区','广西壮族自治区','宁夏回族自治区','新疆维吾尔自治区','西藏自治区','香港特别行政区','澳门特别行政区');
		echo count($province_arr);
		echo "<br>";
		if(in_array($single_card,$province_arr)){
			$flg = 1;

		}else{
			$flg = 0;
		}
		echo $flg;*/


	}
	//地区处理
	public function area(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);
		$obj = M('User');
		//$list_num = $obj->field('card_no,id') -> where("real_name_auth = 1 AND add_time<='2015-09-30 23:59:59.999000'")->count();

		$A1=0;//北京市
		$A2=0;//天津市
		$A3=0;//重庆市
		$A4=0;//上海市
		$A5=0;//河北省
		$A6=0;//山西省
		$A7=0;//辽宁省
		$A8=0;//吉林省
		$A9=0;//黑龙江省
		$A10=0;//江苏省
		$A11=0;//浙江省
		$A12=0;//安徽省
		$A13=0;//福建省
		$A14=0;//江西省
		$A15=0;//山东省
		$A16=0;//河南省
		$A17=0;//湖北省
		$A18=0;//湖南省
		$A19=0;//广东省
		$A20=0;//海南省
		$A21=0;//四川省
		$A22=0;//贵州省
		$A23=0;//云南省
		$A24=0;//陕西省
		$A25=0;//甘肃省
		$A26=0;//青海省
		$A27=0;//台湾省
		$A28=0;//内蒙古自治区
		$A29=0;//广西壮族自治区
		$A30=0;//宁夏回族自治区
		$A31=0;//新疆维吾尔自治区
		$A32=0;//西藏自治区
		$A33=0;//香港特别行政区
		$A34=0;//澳门特别行政区
		$A35=0;//其他
		$A35_name = '';

		$sql = "select card_no,id from stone.`s_user` where real_name_auth = 1 AND  add_time>='2015-12-31 23:59:59.999000' and add_time<='2016-12-31 23:59:59.999000'";
		$list_tmp = M()->query($sql);
		$lost_id_str = "";
		$start_read = 0;
 		foreach ($list_tmp as $k => $v) {
			$card_no = $v['card_no'];
			$url = "http://apistore.baidu.com/microservice/icardinfo?id=" . $card_no;
			$text_object = json_decode(file_get_contents($url));
			$single_card_no = $text_object->retData->address;
			$name = mb_substr($single_card_no, 0, 3, 'utf-8');
			echo '('.($k+1).')'.$name.'<br><br>';
			if (empty($name)) {
				$lost_id_str .= ',' . $v['id'];
			}
			$alter_name = "";
			if (stristr('北京市', $name) !== false) {//1
				$A1++;
				$alter_name ="北京市";
			} else if (stristr('天津市', $name) !== false) {//2
				$A2++;
				$alter_name ="天津市";
			} else if (stristr('重庆市', $name) !== false) {//3
				$A3++;
				$alter_name ="重庆市";
			} else if (stristr('上海市', $name) !== false) {//4
				$A4++;
				$alter_name ="上海市";
			} else if (stristr('河北省', $name) !== false) {//5
				$A5++;
				$alter_name ="河北省";
			} else if (stristr('山西省', $name) !== false) {//6
				$A6++;
				$alter_name ="山西省";
			} else if (stristr('辽宁省', $name) !== false) {//7
				$A7++;
				$alter_name ="辽宁省";
			} else if (stristr('吉林省', $name) !== false) {//8
				$A8++;
				$alter_name ="吉林省";
			} else if (stristr('黑龙江省', $name) !== false) {//9
				$A9++;
				$alter_name ="黑龙江省";
			} else if (stristr('江苏省', $name) !== false) {//10
				$A10++;
				$alter_name ="江苏省";
			} else if (stristr('浙江省', $name) !== false) {//11
				$A11++;
				$alter_name ="浙江省";
			} else if (stristr('安徽省', $name) !== false) {//12
				$A12++;
				$alter_name ="安徽省";
			} else if (stristr('福建省', $name) !== false) {//13
				$A13++;
				$alter_name ="福建省";
			} else if (stristr('江西省', $name) !== false) {//14
				$A14++;
				$alter_name ="江西省";
			} else if (stristr('山东省', $name) !== false) {//15
				$A15++;
				$alter_name ="山东省";
			} else if (stristr('河南省', $name) !== false) {//16
				$A16++;
				$alter_name ="河南省";
			} else if (stristr('湖北省', $name) !== false) {//17
				$A17++;
				$alter_name ="湖北省";
			} else if (stristr('湖南省', $name) !== false) {//18
				$A18++;
				$alter_name ="湖南省";
			} else if (stristr('广东省', $name) !== false) {//19
				$A19++;
				$alter_name ="广东省";
			} else if (stristr('海南省', $name) !== false) {//20
				$A20++;
				$alter_name ="海南省";
			} else if (stristr('四川省', $name) !== false) {//21
				$A21++;
				$alter_name ="四川省";
			} else if (stristr('贵州省', $name) !== false) {//22
				$A22++;
				$alter_name ="贵州省";
			} else if (stristr('云南省', $name) !== false) {//23
				$A23++;
				$alter_name ="云南省";
			} else if (stristr('陕西省', $name) !== false) {//24
				$A24++;
				$alter_name ="陕西省";
			} else if (stristr('甘肃省', $name) !== false) {//25
				$A25++;
				$alter_name ="甘肃省";
			} else if (stristr('青海省', $name) !== false) {//26
				$A26++;
				$alter_name ="青海省";
			} else if (stristr('台湾省', $name) !== false) {//27
				$A27++;
				$alter_name ="台湾省";
			} else if (stristr('内蒙古自治区', $name) !== false) {//28
				$A28++;
				$alter_name ="内蒙古自治区";
			} else if (stristr('广西壮族自治区', $name) !== false) {//29
				$A29++;
				$alter_name ="广西壮族自治区";
			} else if (stristr('宁夏回族自治区', $name) !== false) {//30
				$A30++;
				$alter_name ="宁夏回族自治区";
			} else if (stristr('新疆维吾尔自治区', $name) !== false) {//31
				$A31++;
				$alter_name ="新疆维吾尔自治区";
			} else if (stristr('西藏自治区', $name) !== false) {//32
				$A32++;
				$alter_name ="西藏自治区";
			} else if (stristr('香港特别行政区', $name) !== false) {//33
				$A33++;
				$alter_name ="香港特别行政区";
			} else if (stristr('澳门特别行政区', $name) !== false) {//34
				$A34++;
				$alter_name ="澳门特别行政区";
			}else{ 
				$A35_name = $name;
				$A35++;
				$alter_name ="";
			}
			$start_read++;
			//修改数据表
			$sql_update = "UPDATE stone.`s_user` AS m SET m.`province` = '".$alter_name."' WHERE m.`id` = ".$v['id'];
			M()->execute($sql_update);
		}

		$lost_id_str = substr($lost_id_str, 1);
		echo "北京市".$A1."<br>";
		echo "天津市".$A2."<br>";
		echo "重庆市".$A3."<br>";
		echo "上海市".$A4."<br>";
		echo "河北省".$A5."<br>";
		echo "山西省".$A6."<br>";
		echo "辽宁省".$A7."<br>";
		echo "吉林省".$A8."<br>";
		echo "黑龙江省".$A9."<br>";
		echo "江苏省".$A10."<br>";
		echo "浙江省".$A11."<br>";
		echo "安徽省".$A12."<br>";
		echo "福建省".$A13."<br>";
		echo "江西省".$A14."<br>";
		echo "山东省".$A15."<br>";
		echo "河南省".$A16."<br>";
		echo "湖北省".$A17."<br>";
		echo "湖南省".$A18."<br>";
		echo "广东省".$A19."<br>";
		echo "海南省".$A20."<br>";
		echo "四川省".$A21."<br>";
		echo "贵州省".$A22."<br>";
		echo "云南省".$A23."<br>";
		echo "陕西省".$A24."<br>";
		echo "甘肃省".$A25."<br>";
		echo "青海省".$A26."<br>";
		echo "台湾省".$A27."<br>";
		echo "内蒙古自治区".$A28."<br>";
		echo "广西壮族自治区".$A29."<br>";
		echo "宁夏回族自治区".$A30."<br>";
		echo "新疆维吾尔自治区".$A31."<br>";
		echo "西藏自治区".$A32."<br>";
		echo "香港特别行政区".$A33."<br>";
		echo "澳门特别行政区".$A34."<br>";
		echo "总处理条数".$start_read."<br>";
		echo "其他".$card_no."<br>";
		echo "处理异常数据".$lost_id_str;
	}
	//地区统计
	public function do_area($name){

		if(stristr('北京市',$name)!==false){

		}else if(stristr('天津市',$name)!==false){

		}else if(stristr('重庆市',$name)!==false){

		}else if(stristr('上海市',$name)!==false){

		}else if(stristr('河北省',$name)!==false){

		}else if(stristr('山西省',$name)!==false){

		}else if(stristr('辽宁省',$name)!==false){

		}else if(stristr('吉林省',$name)!==false){

		}else if(stristr('黑龙江省',$name)!==false){

		}else if(stristr('江苏省',$name)!==false){

		}else if(stristr('浙江省',$name)!==false){

		}else if(stristr('安徽省',$name)!==false){

		}else if(stristr('福建省',$name)!==false){

		}else if(stristr('江西省',$name)!==false){

		}else if(stristr('山东省',$name)!==false){

		}else if(stristr('河南省',$name)!==false){

		}else if(stristr('湖北省',$name)!==false){

		}else if(stristr('湖南省',$name)!==false){

		}else if(stristr('广东省',$name)!==false){

		}else if(stristr('海南省',$name)!==false){

		}else if(stristr('四川省',$name)!==false){

		}else if(stristr('贵州省',$name)!==false){

		}else if(stristr('云南省',$name)!==false){

		}else if(stristr('陕西省',$name)!==false){

		}else if(stristr('甘肃省',$name)!==false){

		}else if(stristr('青海省',$name)!==false){

		}else if(stristr('台湾省',$name)!==false){

		}else if(stristr('内蒙古自治区',$name)!==false){

		}else if(stristr('广西壮族自治区',$name)!==false){

		}else if(stristr('宁夏回族自治区',$name)!==false){

		}else if(stristr('新疆维吾尔自治区',$name)!==false){

		}else if(stristr('西藏自治区',$name)!==false){

		}else if(stristr('香港特别行政区',$name)!==false){

		}else if(stristr('澳门特别行政区',$name)!==false){

		}
	}
	//每月投资笔数，每月投资金额,时间截止2015-9-30 23:59:59.999000号
	public function batch_invest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		$batch_list_arr=array();
		for($i=strtotime('2015-04-02');$i<=strtotime('2015-09-30');$i+=86400){
			$j = date("Y-m-d",$i);
			$tmp = $this->do_invest($j);

			$batch_list_arr[]=$tmp;
		}
		//导出excel
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('日期')->setCellValue("A1", "投资笔数")->setCellValue("B1", "投资金额(元)");
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

		// 设置列表值
		$pos = 2;
		foreach ($batch_list_arr as $key => $val) {
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['date']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['num']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($val['totle'], 2));
			$pos += 1;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="每日投资详细('.time().').xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//处理投资笔数
	public function do_invest($date){
		$user_due_db = M('UserDueDetail');
		$user_due_totle = $user_due_db->where("user_id>0 and add_time>='".$date." 00:00:00.000000' and add_time<='".$date." 23:59:59.999000'")->sum('due_capital');
		$user_due_num = $user_due_db->where("user_id>0 and add_time>='".$date." 00:00:00.000000' and add_time<='".$date." 23:59:59.999000'")->count();
		$tmp_due_arr = array('date'=>$date,'totle'=>$user_due_totle,'num'=>$user_due_num);
		return $tmp_due_arr;
	}
	//批量处理-项目期限占比（30天左右，31-60天，61-90天，大于90天）
	public function batch_duration(){
		$project_obj = M('Project');//标的表
		$user_due_obj = M('UserDueDetail');//用户投资
		//30 start
		$duration_30_id_arr = $project_obj->field('id')->where("status = 3 and duration<=30")->select();
		$duration_30_id="";
		foreach($duration_30_id_arr as $k=>$v){
			$duration_30_id.=",".$v['id'];

		}
		$duration_30_id = substr($duration_30_id,1);

		$duration_user_num_30 = $user_due_obj->where("project_id in(".$duration_30_id.") and add_time<='2015-09-30 23:59:59.999000' AND user_id>0")->group('user_id')->select();

		echo "30->".count($duration_user_num_30)."<br>";

		//30 end

		//31-60 start
		$duration_31_60_id_arr = $project_obj->field('id')->where("status = 3 and duration>=31 and duration<=60")->select();
		$duration_31_60_id="";
		foreach($duration_31_60_id_arr as $k=>$v){
			$duration_31_60_id.=",".$v['id'];

		}
		$duration_31_60_id = substr($duration_31_60_id,1);
		$duration_user_num_31_60 = $user_due_obj->where("project_id in(".$duration_31_60_id.") and add_time<='2015-09-30 23:59:59.999000' AND user_id>0")->group('user_id')->select();
		echo "31-60->".count($duration_user_num_31_60)."<br>";
		//31-60 end

		//61-90 start
		$duration_61_90_id_arr = $project_obj->field('id')->where("status = 3 and duration>=61 and duration<=90")->select();
		$duration_61_90_id="";
		foreach($duration_61_90_id_arr as $k=>$v){
			$duration_61_90_id.=",".$v['id'];

		}
		$duration_61_90_id = substr($duration_61_90_id,1);
		$duration_user_num_61_90 = $user_due_obj->where("project_id in(".$duration_61_90_id.") and add_time<='2015-09-30 23:59:59.999000' AND user_id>0")->group('user_id')->select();
		echo "60-90->".count($duration_user_num_61_90)."<br>";
		//61-90 end

		//大于90天 start
		$duration_big_90_id_arr = $project_obj->field('id')->where("status = 3 and duration>90")->select();
		$duration_big_90_id="";
		foreach($duration_big_90_id_arr as $k=>$v){
			$duration_big_90_id.=",".$v['id'];

		}
		$duration_big_90_id = substr($duration_big_90_id,1);
		$duration_user_num_big_90 =$user_due_obj->where("project_id in(".$duration_big_90_id.") and add_time<='2015-09-30 23:59:59.999000' AND user_id>0")->group('user_id')->select();
		echo "大于90->".count($duration_user_num_big_90)."<br>";
		//大于90天 end
	}
	//导出产品从2015-04-02到2015-11-02日钱包转入/转出

	public function bank_takein(){//钱包提现
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包提现')->setCellValue("A1", "日期")->setCellValue("B1", "金额");		
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$pos = 2;
		for($i=strtotime('2015-07-01');$i<=strtotime('2015-11-04');$i+=86400){
				$tmp_date = date("Y-m-d",$i);
				$take_in_sql = "SELECT sum(a.`value`) as num FROM stone.`s_user_wallet_records` AS a WHERE  a.pay_status = 2 and a.type=1 AND a.user_bank_id>0 AND a.user_due_detail_id=0 AND a.add_time>='".$tmp_date." 00:00:00.000000' AND a.add_time<='".$tmp_date." 23:59:59.999000'";
				
				$list = M()->query($take_in_sql);
				
				if($list[0]['num']){ 
					$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
					$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'],2));				
					$pos += 1;
				}
				

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="(2015-10-07-01至2015-11-04)钱包银行卡提现.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	public function due_takein(){//还本付息
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$take_in_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM stone.`s_user_wallet_records` AS a,stone.`s_user` AS b WHERE a.`user_id` = b.`id` AND a.type=1 AND a.user_bank_id=0 AND a.user_due_detail_id>0 AND a.add_time>='2015-07-01 00:00:00.000000' AND a.add_time<='2015-11-02 23:59:59.999000'";
		$list = M()->query($take_in_sql);
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包转入还本付息')->setCellValue("A1", "用户账号")->setCellValue("B1", "用户名称")
			->setCellValue("C1", "交易编号")->setCellValue("D1", "购买产品(元)")->setCellValue("E1", "交易时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

		// 设置列表值
		$pos = 2;
		foreach ($list as $key => $val) {
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));
			$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
			$pos += 1;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="钱包上线截止2015-11-02钱包转入还本付息.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
	//钱包提现
	public function batch_tixian(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包转出提现记录')->setCellValue("A1", "日期")->setCellValue("B1", "金额");
		
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

		$pos = 2;
		for($i=strtotime('2015-07-01');$i<=strtotime('2015-11-04');$i+=86400){
			$tmp_date = date("Y-m-d",$i);			
			$take_out_sql = "";
			$list = array();
			$take_out_sql = "SELECT sum(a.`value`) as num FROM stone.`s_user_wallet_records` AS a,stone.`s_user` AS b WHERE a.`user_id` = b.`id` and a.status = 1 and a.type=2 and a.user_bank_id>0 and a.user_due_detail_id=0 and a.add_time>='".$tmp_date." 00:00:00.000000' and a.add_time<='".$tmp_date." 23:59:59.999000' order by a.add_time asc";
			$list = M()->query($take_out_sql);
		    if($list[0]['num']){ 
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));
				$pos += 1;
			}	
		
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="钱包提现(2015-07-01至2015-11-04).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//钱包购买产品
	public function wallet_invest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$take_in_sql = "SELECT b.`username`,b.`real_name`,a.`recharge_no`,a.`value`,a.`add_time` FROM stone.`s_user_wallet_records` AS a,stone.`s_user` AS b WHERE a.`user_id` = b.`id` AND a.type=2 AND a.user_bank_id=0 AND a.user_due_detail_id>0 AND a.add_time>='2015-07-01 00:00:00.000000' AND a.add_time<='2015-11-02 23:59:59.999000'";
		$list = M()->query($take_in_sql);
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
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
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $val['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $val['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $val['recharge_no']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($val['value'], 2));
			$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, date("Y-m-d H:i:s",strtotime($val['add_time'])));
			$pos += 1;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="钱包上线截止2015-11-02购买产品记录.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
	//产品
	public function batch_project(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品融资')->setCellValue("A1", "日期")->setCellValue("B1", "金额")->setCellValue("C1", "融资人");
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$pos = 2;
		for($i=strtolower('2015-04-01');$i<=strtotime('2015-11-05');$i+=86400){ 
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "SELECT k.`add_time`,sum(k.`amount`) as num,k.`financing` FROM stone.`s_project` AS k WHERE k.`financing`!='王伟军' and k.`add_time`>='".$tmp_date." 00:00:00.000000' and k.`add_time`<='".$tmp_date." 23:59:59.999000' order by k.`add_time` asc";
			$list = M()->query($take_out_sql);
			if($list[0]['add_time']){ 
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, date("Y-m-d",strtotime($list[0]['add_time'])));
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));
				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $list[0]['financing']);
				$pos += 1;
			}			
		
		}	
		
		
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="产品融资其他人(2015-04-01至2015-11-05).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//钱包购买理财产品-王伟军
	public function  wallet_to_due_wang(){ 
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包购买理财-王')->setCellValue("A1", "日期")->setCellValue("B1", "金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$pos = 2;
		for($i=strtotime('2015-04-01');$i<=strtotime('2015-11-05');$i+=86400){ 
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "SELECT sum(m.`value`) as num,m.`add_time` FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 2 and m.`status` = 1 AND m.`user_bank_id`=0 AND m.`user_due_detail_id` IN(SELECT a.`id` FROM stone.`s_user_due_detail` AS a  WHERE a.`project_id` IN(SELECT k.`id` FROM stone.`s_project` AS k WHERE k.`financing`='王伟军')) and m.`add_time`>='".$tmp_date." 00:00:00.000000' and m.`add_time`<'".$tmp_date." 23:59:59.999000'";
			$list = M()->query($take_out_sql);
			if($list[0]['add_time']){ 
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));				
				$pos += 1;
			}			
		
		}	
		
		
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="钱包购买理财-王伟军(2015-04-01至2015-11-05).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;	
	}
	/**
	*统计每日还款数据
	*/
	public function tongjidue(){ 	
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品还款-其他人')->setCellValue("A1", "日期")->setCellValue("B1", "钱包金额")->setCellValue("C1", "银行卡金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$pos = 2;
		for($i=strtotime('2015-09-30');$i<=strtotime('2015-11-05');$i+=86400){ 
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "SELECT m.`id`,m.`project_id`,m.`real_time`,m.`repayment_time` FROM stone.`s_repayment_detail` AS m WHERE m.`project_id` IN(SELECT k.`id` FROM stone.`s_project` AS k WHERE k.`financing`!='王伟军') AND (m.`repayment_time`>='".$tmp_date." 00:00:00.000000' AND m.`repayment_time`<='".$tmp_date." 23:59:59.999000') OR (m.`real_time`>='".$tmp_date." 00:00:00.000000' AND m.`real_time`<='".$tmp_date." 23:59:59.999000')";
			$list = M()->query($take_out_sql);			
			$bank_total = 0;
			$wallet_total = 0;
			if($list){		
				$bank_total =0;
				$wallet_total =0;
				foreach($list as $k=>$v){ 
					$project_id = $v['project_id'];
					$repay_id  = $v['id'];
					//钱包				
					$wallet_total_arr = $this->paylist($project_id,$repay_id);					
					$wallet_total += $wallet_total_arr['debt_service_wallet_total'];
					//银行卡				
					$bank_total_arr = $this->paylisttowallet($project_id,$repay_id);					
					$bank_total += $bank_total_arr['debt_service_bank_total'];
				}			

				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($wallet_total, 2));	
				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, number_format($bank_total, 2));				
				$pos += 1;	
				unset($tmp_date);
				unset($list);
			}
					
		
		}	
		
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="产品还款-其他人(2015-04-01至2015-11-05).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;	
	
	}
	
	/**
     * 付款用户列表(去掉到期转入钱包的部分订单)
     */
    public function paylist($id,$repay_id) {  
    

        $projectObj = M('Project');
        $projectModelFundObj = M('ProjectModelFund');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');
        $fundDetailObj = M('FundDetail');		
		
        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
           return array( 
			'debt_service_wallet_total'=>0,			
			);
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
           return array( 
			'debt_service_wallet_total'=>0,			
			);
        }
        if($detail['type'] == 104 || $detail['type'] == 109 || $detail['type'] == 110) { // 基金类产品
            $isFund = true;
            $detailExt = $projectModelFundObj->where(array('project_id' => $detail['id']))->find();
            $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
            if (!$detail['advance_end_time']) $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
            else $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
            $today = date('Y-m-d', time()); // 当前时间点
            if ($today < $timeEnd) $timeEnd = $today;
            $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
            $percent = 0; // 基金类收益率
            if (count($fundList) > 1) { // 两个净值点以上
                $fundStart = $fundList[0]['val']; // 起始净值
                $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                switch ($detail['type']) {
                    case 104: // 打新股,收益超过18%分成
                        if ($fundEnd - $fundStart > 0) {
                            if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                                $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                            } else {
                                $percent = ($fundEnd - $fundStart) / $fundStart;
                            }
                        }
                        break;
                    case 109: // B类基金,杠杆0.2
                        if ($fundEnd - $fundStart > 0) {
                            $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                            $percent = ($fundEndB - $fundStart) / $fundStart;
                        }
                        break;
                    case 110: // A类基金,杠杆2.6
                        if ($fundEnd - $fundStart > 0) {
                            $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                            $percent = ($fundEndA - $fundStart) / $fundStart;
                        } else if ($fundEnd - $fundStart < 0) {
                            $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                            $percent = ($fundEndA - $fundStart) / $fundStart;
                        }
                        break;
                }
            }
        }else if($detail['type'] == 148){ // 博息宝
            $isFund = true;
            $percent = cal_fund_percent($id);
        }else{
            $isFund = false;
        }
        
        $conditions = "project_id=".$id." and repay_id=".$repay_id." and to_wallet=0 and from_wallet=0";
        
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();
        $totle_capital = 0;
        $totle_interest = 0;
        foreach ($list as $key => $val) {
            $totle_capital += $val['due_capital'];
            if(!$isFund) {
                $totle_interest += $val['due_interest'];
            } else {
                if($detail['type'] == 148){
                    $dueTime = date('Y-m-d', time());
                    if(date('Y-m-d', strtotime($val['due_time'])) < $dueTime) $dueTime = date('Y-m-d', strtotime($val['due_time']));
                    $list[$key]['due_interest'] = round($val['due_capital']*$percent*(count_days($dueTime.' 08:00:00', date('Y-m-d', strtotime($val['start_time'])).' 08:00:00')+1)/365, 2);
                    $totle_interest += $list[$key]['due_interest'];
                }else{
                    $totle_interest += $val['due_capital']*$percent;
                    $list[$key]['due_interest'] = $val['due_capital']*$percent;
                }
            }
        }
		return array( 
			'debt_service_wallet_total'=>($totle_capital+$totle_interest),			
		);

        
    }
	/**
     * 转入钱包的支付记录
     */
    public function paylisttowallet($id,$repay_id){       

        $projectObj = M('Project');
        $projectModelFundObj = M('ProjectModelFund');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');
        $fundDetailObj = M('FundDetail');

        $detail = $projectObj->where(array('id' => $id, 'is_delete' => 0))->find();
        if (!$detail) {
            return array( 
			'debt_service_bank_total'=>0,			
			);
        }
        $repayDetail = $repaymentDetailObj->where(array('id' => $repay_id, 'project_id' => $id))->find();
        if (!$repayDetail) {
            return array( 
			'debt_service_bank_total'=>0,			
		);
        }
        if($detail['type'] == 104 || $detail['type'] == 109 || $detail['type'] == 110) { // 基金类产品
            $isFund = true;
            $detailExt = $projectModelFundObj->where(array('project_id' => $detail['id']))->find();
            $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
            if (!$detail['advance_end_time']) $timeEnd = date('Y-m-d', strtotime($detail['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
            else $timeEnd = date('Y-m-d', strtotime($detail['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
            $today = date('Y-m-d', time()); // 当前时间点
            if ($today < $timeEnd) $timeEnd = $today;
            $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
            $percent = 0; // 基金类收益率
            if (count($fundList) > 1) { // 两个净值点以上
                $fundStart = $fundList[0]['val']; // 起始净值
                $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                switch ($detail['type']) {
                    case 104: // 打新股,收益超过18%分成
                        if ($fundEnd - $fundStart > 0) {
                            if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                                $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                            } else {
                                $percent = ($fundEnd - $fundStart) / $fundStart;
                            }
                        }
                        break;
                    case 109: // B类基金,杠杆0.2
                        if ($fundEnd - $fundStart > 0) {
                            $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                            $percent = ($fundEndB - $fundStart) / $fundStart;
                        }
                        break;
                    case 110: // A类基金,杠杆2.6
                        if ($fundEnd - $fundStart > 0) {
                            $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                            $percent = ($fundEndA - $fundStart) / $fundStart;
                        } else if ($fundEnd - $fundStart < 0) {
                            $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                            $percent = ($fundEndA - $fundStart) / $fundStart;
                        }
                        break;
                }
            }
        }else if($detail['type'] == 148){ // 搏息宝
            $isFund = true;
            $percent = cal_fund_percent($id);
        }else{
            $isFund = false;
        }
        
        $cond[] = "project_id=".$id;
        $cond[] = "repay_id=".$repay_id;
        $cond[] = "(to_wallet=1 or from_wallet=1)";
        $conditions = implode(' and ', $cond);        
        $list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();
        $totle_capital = 0;
        $totle_interest = 0;
        foreach ($list as $key => $val) {
            if(!$isFund) {
                $totle_interest += $val['due_interest'];
            } else {
                if($detail['type'] == 148){
                    $dueTime = date('Y-m-d', time());
                    if(date('Y-m-d', strtotime($val['due_time'])) < $dueTime) $dueTime = date('Y-m-d', strtotime($val['due_time']));
                    $list[$key]['due_interest'] = round($val['due_capital']*$percent*(count_days($dueTime.' 08:00:00', date('Y-m-d', strtotime($val['start_time'])).' 08:00:00')+1)/365, 2);
                    $totle_interest += $list[$key]['due_interest'];
                }else{
                    $totle_interest += number_format($val['due_capital']*$percent,2);
                    $list[$key]['due_interest'] = number_format($val['due_capital']*$percent,2);
                }
            }
        } 
        return array( 
			'debt_service_bank_total'=>($totle_capital+$totle_interest),			
		);
    }
	/**
	*历史累计产品还款
	*/
	public function batch_pay_back(){ 
			ini_set("memory_limit", "1000M");
			ini_set("max_execution_time", 0);
			vendor('PHPExcel.PHPExcel');
			$objPHPExcel = new \PHPExcel();
			$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
			$objPHPExcel->setActiveSheetIndex(0)->setTitle('历史产品还款')->setCellValue("A1", "日期")->setCellValue("B1", "产品名称")->setCellValue("C1", "融资人")->setCellValue("D1", "支付利息")->setCellValue("E1", "超出利息")->setCellValue("F1", "支付本金")->setCellValue("G1", "超出部分")->setCellValue("J1", "幽灵账户")->setCellValue("K1",'手续费');
			$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);	
			$force = 1;
            $projectObj = M('Project');
            $repaymentDetailObj = M('RepaymentDetail');
            $userDueDetailObj = M('UserDueDetail');
            $rechargeLogObj = M("RechargeLog");
            $projectModelFundObj = M("ProjectModelFund");
            $fundDetailObj = M("FundDetail");
            
            
            $endtime = "2015-11-30 23:59:59.999000";
            $conditions = "(repayment_time>='2015-11-01 00:00:00.000000' and repayment_time<='".$endtime."') or (real_time>='2015-11-01 00:00:00.000000' and real_time<='".$endtime."')";
            $list = $repaymentDetailObj->where($conditions)->order('status desc,repayment_time asc,real_time asc')->select(); // 项目还本付息表条目列表
            $pos = 2;
            foreach($list as $key => $val){ // 计算每期利息和本金
                $projectInfo = $projectObj->field('id,title,status,financing,duration,amount,type,contract_interest,user_interest,start_time,end_time,advance_end_time')->where(array('id'=>$val['project_id']))->find();
                $list[$key]['project_title'] = $projectInfo['title'];
                $list[$key]['project_status'] = $projectInfo['status'];
                if($projectInfo['status'] > 2){
                    $list[$key]['project_amount'] = $projectInfo['amount'];
                    $list[$key]['capital_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->sum('due_capital');
                    // 幽灵账户购买
                    $list[$key]['ghost_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id'],'user_id'=>0))->sum('due_capital');

                    if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110) { // 基金类产品
                        $detailExt = $projectModelFundObj->where(array('project_id' => $projectInfo['id']))->find();
                        $percent = 0; // 基金类收益率
                        if ($detailExt) {
                            if (!$detailExt['enter_time']) {
                                $timeStart = date('Y-m-d', strtotime($projectInfo['start_time'])); // 产品净值进入时间点
                            } else {
                                $timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
                            }
                            if (!$projectInfo['advance_end_time']) {
                                $timeEnd = date('Y-m-d', strtotime($projectInfo['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                            } else {
                                $timeEnd = date('Y-m-d', strtotime($projectInfo['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
                            }
                            $today = date('Y-m-d', time()); // 当前时间点
                            if ($today < $timeEnd) $timeEnd = $today;
                            $fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
                            if (count($fundList) > 1) { // 两个净值点以上
                                $fundStart = $fundList[0]['val']; // 起始净值
                                $fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

                                switch ($projectInfo['type']) {
                                    case 104: // 打新股,收益超过18%分成
                                        if ($fundEnd - $fundStart > 0) {
                                            if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
                                                $percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
                                            } else {
                                                $percent = ($fundEnd - $fundStart) / $fundStart;
                                            }
                                        }
                                        break;
                                    case 109: // B类基金,杠杆0.2
                                        if ($fundEnd - $fundStart > 0) {
                                            $fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
                                            $percent = ($fundEndB - $fundStart) / $fundStart;
                                        }
                                        break;
                                    case 110: // A类基金,杠杆2.6
                                        if ($fundEnd - $fundStart > 0) {
                                            $fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
                                            $percent = ($fundEndA - $fundStart) / $fundStart;
                                        } else if ($fundEnd - $fundStart < 0) {
                                            $fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
                                            $percent = ($fundEndA - $fundStart) / $fundStart;
                                        }
                                        break;
                                }
                            }
                        }
                        $due_interest_list = $userDueDetailObj->where('project_id='.$val['project_id'].' and repay_id='.$val['id'].' and user_id>0')->select();
                        $due_interest_total=0;
                        foreach($due_interest_list as $k=>$v){
                            $due_interest_total+=number_format($v['due_capital'] * $percent, 2);
                        }

                        $list[$key]['interest_totle'] = $due_interest_total;//($list[$key]['capital_totle'] - $list[$key]['ghost_totle']) * $percent;
                    }else if($projectInfo['type'] == 148){ // 搏息宝
                        $percent = cal_fund_percent($projectInfo['id']);
                        $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->sum('due_interest');
                    }else{
                        $list[$key]['interest_totle'] = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->sum('due_interest');
                    }


                    if($force == 1 && $projectInfo['type'] == 2){ // 石头1号
                        $list[$key]['counter_fee'] = $projectInfo['amount']*0.01;
                    }else{
                        $list[$key]['counter_fee'] = $projectInfo['amount']*($projectInfo['contract_interest']-$projectInfo['user_interest'])*$projectInfo['duration']/100/365;
                    }
                    // 查看是否有超出部分
                    if($projectInfo['type'] != 104 && $projectInfo['type'] != 109 && $projectInfo['type'] != 110) { // 非基金类产品
                        $moreMoney = $list[$key]['capital_totle'] - $list[$key]['project_amount'];
                        if($moreMoney > 0){
                            $lastItem = $userDueDetailObj->where(array('project_id'=>$val['project_id'],'repay_id'=>$val['id']))->order('add_time desc')->limit(1)->find();
                            $moreInterest = $moreMoney*$lastItem['duration_day']*$projectInfo['user_interest']/100/365;
                            $list[$key]['more_interest'] = $moreInterest;
                        }
                    }else if($projectInfo['type'] == 148){ // 搏息宝
                        $list[$key]['more_interest'] = 0;
                    }
					$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, date('Y-m-d', strtotime($val['repayment_time'])));
					$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $projectInfo['title']);	
					$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $projectInfo['financing']);
					$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, number_format($list[$key]['interest_totle'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, number_format($list[$key]['more_interest'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("F".$pos, number_format($projectInfo['amount'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("G".$pos, number_format($list[$key]['capital_totle']-$list[$key]['project_amount'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("J".$pos, number_format($list[$key]['ghost_totle'], 2));
					$objPHPExcel->getActiveSheet()->setCellValue("K".$pos, number_format($list[$key]['counter_fee'], 2));					
					$pos += 1;                    
                }
	
	}
			header("Content-Type: application/vnd.ms-excel");
			header('Content-Disposition: attachment;filename="历史产品还款.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
	}
	/**
	*全部产品-合同号(关联)
	*/
	public function getAllProject(){ 
			ini_set("memory_limit", "1000M");
			ini_set("max_execution_time", 0);
			vendor('PHPExcel.PHPExcel');
			$objPHPExcel = new \PHPExcel();
			$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
			$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "标名称")->setCellValue("B1", "资金额")->setCellValue("C1", "线上利率")->setCellValue("D1", "合同号");
			$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$sql = "SELECT a.`title`,a.`user_interest`,a.`amount`,b.`name` FROM stone.`s_project` AS a,stone.`s_contract` AS b,stone.`s_contract_project` AS c WHERE a.`id` = c.`project_id` AND c.`contract_id` = b.`id` ORDER BY a.id ASC";
			$list = M()->query($sql);
			$pos = 2;
			foreach($list as $k=>$v){ 
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $v['title']);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $v['amount']);	
				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $v['user_interest']);
				$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $v['name']);				
				$pos += 1;         
			
			}	
			header("Content-Type: application/vnd.ms-excel");
			header('Content-Disposition: attachment;filename="全部产品-合同号.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
	}
	/**
	 *全部产品-合同号(不关联)
	 */
	public function contract_no(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "标名称")->setCellValue("B1", "资金额")->setCellValue("C1", "线上利率")->setCellValue("D1", "合同号");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$sql = "SELECT a.`title`,a.`user_interest`,a.`amount`,a.`contract_no` FROM stone.`s_project` AS a ORDER BY a.id ASC";
		$list = M()->query($sql);
		$pos = 2;
		foreach($list as $k=>$v){
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $v['amount']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $v['user_interest']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $v['contract_no']);
			$pos += 1;

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="全部产品-合同号(不关联).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
     * 到期金额转入钱包-批量处理
     */
    public function project_export(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "标名称")->setCellValue("B1", "用户名")->setCellValue("C1", "账号")->setCellValue("D1", "本金")->setCellValue("E1","利息")->setCellValue("F1","本息")->setCellValue("G1","还款时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		//第一步查出待处理的转入钱包的支付记录
		
    
        $projectObj = M('Project');
        $projectModelFundObj = M('ProjectModelFund');
        $repaymentDetailObj = M('RepaymentDetail');
        $userDueDetailObj = M('UserDueDetail');
        $userObj = M('User');
        $userAccountObj = M('UserAccount');
        $userBankObj = M('UserBank');
        $fundDetailObj = M('FundDetail');
        $userWalletRecordsObj = M("UserWalletRecords");
        $investmentDetailObj = M("InvestmentDetail");
		
        //$sql = "SELECT h.`id`,h.`title`,w.`id` AS repay_id,h.`end_time`  FROM stone.`s_project` AS h,stone.`s_repayment_detail` AS w WHERE h.`end_time`>='2015-11-25 00:00:00.000000' AND h.`end_time`<='2015-11-25 23:59:59.999000' AND (h.`status` = 5 or h.`status` = 4)  AND w.`project_id` = h.`id` GROUP BY h.`end_time`";
		$sql = "SELECT h.`id`,h.`title`,w.`id` AS repay_id,h.`end_time`  FROM stone.`s_project` AS h,stone.`s_repayment_detail` AS w WHERE (w.real_time>='2015-10-16 00:00:00.000000' and w.real_time<='2015-10-16 23:59:59.999000' and w.status=2)  AND w.`project_id` = h.`id` GROUP BY w.`repayment_time`";
		$project_repayment_list = M()->query($sql);

		$pos = 2;
		foreach($project_repayment_list as $key => $value){
			//and (to_wallet=1 or from_wallet=1)
			$sql_tmp = "select * from stone.s_user_due_detail where project_id=".$value['id']." and repay_id=".$value['repay_id']." order by add_time desc";
        	$likui_list = M()->query($sql_tmp);


			foreach($likui_list as $n => $wwww){

				$userDueDetailInfo = $userDueDetailObj->where(array('id'=>$wwww['id']))->find();

				$projectInfo = $projectObj->where(array('id'=>$userDueDetailInfo['project_id']))->find();
				$user_info = $userObj->field('username,real_name')->where('id='.$userDueDetailInfo['user_id'])->find();
				if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110) { // 基金类产品
					$percent = 0; // 基金类收益率
					$detailExt = $projectModelFundObj->where(array('project_id' => $projectInfo['id']))->find();
					if ($detailExt) {
						if (!$detailExt['enter_time']) {
							$timeStart = date('Y-m-d', strtotime($projectInfo['start_time'])); // 产品净值进入时间点
						} else {
							$timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
						}
						if (!$projectInfo['advance_end_time']) { // 是否是提前还款
							$timeEnd = date('Y-m-d', strtotime($projectInfo['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
						} else {
							$timeEnd = date('Y-m-d', strtotime($projectInfo['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
						}
						$today = date('Y-m-d', time()); // 当前时间点
						if ($today < $timeEnd) $timeEnd = $today;
						$fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
						if (count($fundList) > 1) { // 两个净值点以上
							$fundStart = $fundList[0]['val']; // 起始净值
							$fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

							switch ($projectInfo['type']) {
								case 104: // 打新股,收益超过18%分成
									if ($fundEnd - $fundStart > 0) {
										if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
											$percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
										} else {
											$percent = ($fundEnd - $fundStart) / $fundStart;
										}
									}
									break;
								case 109: // B类基金,杠杆0.2
									if ($fundEnd - $fundStart > 0) {
										$fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
										$percent = ($fundEndB - $fundStart) / $fundStart;
									}
									break;
								case 110: // A类基金,杠杆2.6
									if ($fundEnd - $fundStart > 0) {
										$fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
										$percent = ($fundEndA - $fundStart) / $fundStart;
									} else if ($fundEnd - $fundStart < 0) {
										$fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
										$percent = ($fundEndA - $fundStart) / $fundStart;
									}
									break;
							}
						}
					}
					$userDueDetailInfo['due_interest'] = $userDueDetailInfo['due_capital'] * $percent;
					$userDueDetailInfo['due_amount'] = $userDueDetailInfo['due_capital'] + $userDueDetailInfo['due_interest'];

					$rows = array(
						'username' => $user_info['username'],
						'realname' => $user_info['real_name'],
						'capital' => $userDueDetailInfo['due_capital'],
						'amount' => $userDueDetailInfo['due_amount'],
						'title'=> $value['title'],
						'interest'=>$userDueDetailInfo['due_interest'],
						'time'=>date("Y-m-d",strtotime($value['end_time']))
					);
				}else if($projectInfo['type'] == 148) { // 搏息宝
					$percent = cal_fund_percent($projectInfo['id']);
					$userDueDetailInfo['due_interest'] = round($userDueDetailInfo['due_capital'] * (count_days(date('Y-m-d', strtotime($userDueDetailInfo['due_time'])) . ' 08:00:00', date('Y-m-d', strtotime($userDueDetailInfo['start_time'])) . ' 08:00:00')+1) * $percent / 365, 2);
					$userDueDetailInfo['due_amount'] = $userDueDetailInfo['due_capital'] + $userDueDetailInfo['due_interest'];
					$rows = array(
						'username' => $user_info['username'],
						'realname' => $user_info['real_name'],
						'capital' => $userDueDetailInfo['due_capital'],
						'amount' => $userDueDetailInfo['due_amount'],
						'title'=> $value['title'],
						'interest'=>$userDueDetailInfo['due_interest'],
						'time'=>date("Y-m-d",strtotime($value['end_time']))
					);
				}else{ // 普通产品
					$rows = array(
						'username' => $user_info['username'],
						'realname' => $user_info['real_name'],
						'capital' => $userDueDetailInfo['due_capital'],
						'amount' => $userDueDetailInfo['due_amount'],
						'title'=> $value['title'],
						'interest'=>$userDueDetailInfo['due_interest'],
						'time'=>date("Y-m-d",strtotime($value['end_time']))
					);
				}

				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $rows['title']);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $rows['realname']);
				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $rows['username']);
				$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $rows['capital']);
				$objPHPExcel->getActiveSheet()->setCellValue("E".$pos, $rows['interest']);
				$objPHPExcel->getActiveSheet()->setCellValue("F".$pos, $rows['amount']);
				$objPHPExcel->getActiveSheet()->setCellValue("G".$pos, $rows['time']);
				$pos++;


			}
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="全部产品还本付息.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
        
    }
	 /*检查导入钱包金额**/
	public function check_wallet(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "用户名")->setCellValue("B1", "账号")->setCellValue("C1", "本金")->setCellValue("D1", "本息");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		//第一步查出待处理的转入钱包的支付记录


		$projectObj = M('Project');
		$projectModelFundObj = M('ProjectModelFund');
		$repaymentDetailObj = M('RepaymentDetail');
		$userDueDetailObj = M('UserDueDetail');
		$userObj = M('User');
		$userAccountObj = M('UserAccount');
		$userBankObj = M('UserBank');
		$fundDetailObj = M('FundDetail');
		$userWalletRecordsObj = M("UserWalletRecords");
		$investmentDetailObj = M("InvestmentDetail");


		$pos = 2;

		$id = 953;	//项目ID
		$repay_id  = 952;//还本付息ID
		$cond[] = "project_id=".$id;
		$cond[] = "repay_id=".$repay_id;
		$cond[] = "(to_wallet=1 or from_wallet=1)";
		$conditions = implode(' and ', $cond);
		$list = $userDueDetailObj->where($conditions)->order('add_time desc')->select();

		foreach($list as $i => $v){

			$userDueDetailInfo = $userDueDetailObj->where(array('id'=>$v['id']))->find();

			$projectInfo = $projectObj->where(array('id'=>$userDueDetailInfo['project_id']))->find();
			$user_info = $userObj->field('username,real_name')->where('id='.$userDueDetailInfo['user_id'])->find();
			if($projectInfo['type'] == 104 || $projectInfo['type'] == 109 || $projectInfo['type'] == 110) { // 基金类产品
				$percent = 0; // 基金类收益率
				$detailExt = $projectModelFundObj->where(array('project_id' => $projectInfo['id']))->find();
				if ($detailExt) {
					if (!$detailExt['enter_time']) {
						$timeStart = date('Y-m-d', strtotime($projectInfo['start_time'])); // 产品净值进入时间点
					} else {
						$timeStart = date('Y-m-d', strtotime($detailExt['enter_time'])); // 产品净值进入时间点
					}
					if (!$projectInfo['advance_end_time']) { // 是否是提前还款
						$timeEnd = date('Y-m-d', strtotime($projectInfo['end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
					} else {
						$timeEnd = date('Y-m-d', strtotime($projectInfo['advance_end_time'])); // 产品净值结束时间点(如果当前时间在产品结束之前则取当前时间)
					}
					$today = date('Y-m-d', time()); // 当前时间点
					if ($today < $timeEnd) $timeEnd = $today;
					$fundList = $fundDetailObj->field('val,datetime')->where("fund_id=" . $detailExt['fund_id'] . " and datetime>='" . $timeStart . "' and datetime<='" . $timeEnd . "'")->order('datetime asc')->select(); // 关联基金净值列表
					if (count($fundList) > 1) { // 两个净值点以上
						$fundStart = $fundList[0]['val']; // 起始净值
						$fundEnd = $fundList[count($fundList) - 1]['val']; // 结束净值

						switch ($projectInfo['type']) {
							case 104: // 打新股,收益超过18%分成
								if ($fundEnd - $fundStart > 0) {
									if (($fundEnd - $fundStart) / $fundStart > 0.18) { // 分成
										$percent = 0.18 + (($fundEnd - $fundStart) / $fundStart - 0.18) / 2;
									} else {
										$percent = ($fundEnd - $fundStart) / $fundStart;
									}
								}
								break;
							case 109: // B类基金,杠杆0.2
								if ($fundEnd - $fundStart > 0) {
									$fundEndB = ($fundEnd - $fundStart) * 0.2 + $fundStart;
									$percent = ($fundEndB - $fundStart) / $fundStart;
								}
								break;
							case 110: // A类基金,杠杆2.6
								if ($fundEnd - $fundStart > 0) {
									$fundEndA = ($fundEnd - $fundStart) * 2.6 + $fundStart;
									$percent = ($fundEndA - $fundStart) / $fundStart;
								} else if ($fundEnd - $fundStart < 0) {
									$fundEndA = ($fundEnd - $fundStart) * 3 + $fundStart;
									$percent = ($fundEndA - $fundStart) / $fundStart;
								}
								break;
						}
					}
				}
				$userDueDetailInfo['due_interest'] = $userDueDetailInfo['due_capital'] * $percent;
				$userDueDetailInfo['due_amount'] = $userDueDetailInfo['due_capital'] + $userDueDetailInfo['due_interest'];

				$rows = array(
					'username' => $user_info['username'],
					'realname' => $user_info['real_name'],
					'capital' => $userDueDetailInfo['due_capital'],
					'amount' => $userDueDetailInfo['due_amount'],
				);
			}else if($projectInfo['type'] == 148) { // 搏息宝
				$percent = cal_fund_percent($projectInfo['id']);
				$userDueDetailInfo['due_interest'] = round($userDueDetailInfo['due_capital'] * (count_days(date('Y-m-d', strtotime($userDueDetailInfo['due_time'])) . ' 08:00:00', date('Y-m-d', strtotime($userDueDetailInfo['start_time'])) . ' 08:00:00')+1) * $percent / 365, 2);
				$userDueDetailInfo['due_amount'] = $userDueDetailInfo['due_capital'] + $userDueDetailInfo['due_interest'];
				$rows = array(
					'username' => $user_info['username'],
					'realname' => $user_info['real_name'],
					'capital' => $userDueDetailInfo['due_capital'],
					'amount' => $userDueDetailInfo['due_amount'],
				);
			}else{ // 普通产品
				$rows = array(
					'username' => $user_info['username'],
					'realname' => $user_info['real_name'],
					'capital' => $userDueDetailInfo['due_capital'],
					'amount' => $userDueDetailInfo['due_amount'],
				);
			}
			$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $rows['realname']);
			$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $rows['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $rows['capital']);
			$objPHPExcel->getActiveSheet()->setCellValue("D".$pos, $rows['amount']);
			$pos += 1;

		}


		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="富国低碳环保B类第8期.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;


	}
	/**
	 * 首投用户(期限>15，理财金额>1w)
	 */
	public function export_due_list(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "用户名")->setCellValue("B1", "手机号码")->setCellValue("C1", "投资金额")->setCellValue("D1", "到期天数");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$now_date = date("Y-m-d",time());

		 $sql = "SELECT w.`real_name`,w.`username`,q.due_capital,q.duration_day,q.due_time FROM (SELECT * FROM stone.`s_user_due_detail` AS e GROUP BY e.`user_id` HAVING COUNT(e.`user_id`)<2  AND e.`due_capital`>=10000 ORDER BY e.`user_id`) AS q,stone.`s_user` AS w WHERE q.user_id = w.id";
		//$sql = "SELECT w.`real_name`,w.`username`,q.due_capital,q.duration_day FROM (SELECT * FROM stone.`s_user_due_detail` AS e WHERE e.`duration_day`>=15 AND e.`due_capital`>=10000 GROUP BY e.`user_id` HAVING COUNT(e.`user_id`)<2 ORDER BY e.`user_id`) AS q,stone.`s_user` AS w WHERE q.user_id = w.id";
		$project_repayment_list = M()->query($sql);
		$pos = 2;
		foreach($project_repayment_list as $key => $value){

			$day_time =  (strtotime(date("Y-m-d",strtotime($value['due_time'])))-strtotime($now_date));
			if($day_time<0){
				continue;
			}else{
				$day = $day_time/(1*24*3600);
				if($day>=15) {
					$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['real_name']);
					$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
					$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $value['due_capital']);
					$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $day);

					$pos++;
				}
			}
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="历史首投用户.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}

	/**
	 * 在投用户(理财金额>1w除去钱包)
	 */
	public function export_in_due(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "用户名")->setCellValue("B1", "手机号码")->setCellValue("C1", "投资金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);


		$now_date = date("Y-m-d",time());
		 $sql = "SELECT b.id,b.`real_name`,b.`username`,w.`title`,m.`due_capital`,m.`due_time`  FROM stone.`s_user_due_detail` AS m,stone.`s_user` AS b,stone.`s_project` AS w WHERE m.`user_id`>0 AND (m.`due_time`>'2015-11-26 00:00:00.000000' OR m.`real_time`>'2015-11-26 00:00:00.000000') AND m.`from_wallet`=0 AND m.`due_capital`>=10000 AND m.`user_id` = b.`id` AND m.`project_id` = w.`id` AND m.`user_id` NOT IN(SELECT w.id FROM (SELECT * FROM stone.`s_user_due_detail` AS e GROUP BY e.`user_id` HAVING COUNT(e.`user_id`)<2  AND e.`due_capital`>=10000 ORDER BY e.`user_id`) AS q,stone.`s_user` AS w WHERE q.user_id = w.id) GROUP BY m.`user_id`";
		//$sql = "SELECT w.`real_name`,w.`username`,q.due_capital,q.duration_day,q.due_time FROM (SELECT * FROM stone.`s_user_due_detail` AS e GROUP BY e.`user_id` HAVING COUNT(e.`user_id`)<2  AND e.`due_capital`>=10000 ORDER BY e.`user_id`) AS q,stone.`s_user` AS w WHERE q.user_id = w.id";
		//$sql = "SELECT w.`real_name`,w.`username`,q.due_capital,q.duration_day FROM (SELECT * FROM stone.`s_user_due_detail` AS e WHERE e.`duration_day`>=15 AND e.`due_capital`>=10000 GROUP BY e.`user_id` HAVING COUNT(e.`user_id`)<2 ORDER BY e.`user_id`) AS q,stone.`s_user` AS w WHERE q.user_id = w.id";
		$project_repayment_list = M()->query($sql);
		$pos = 2;
		foreach($project_repayment_list as $key => $value){
			$sql_tmp = "SELECT SUM(k.`due_capital`) AS num FROM stone.`s_user_due_detail` AS k WHERE k.`user_id` = ".$value['id']." AND k.`due_time`>'2015-11-26 00:00:00.000000'";
			$due_amount = M()->query($sql_tmp);

			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $due_amount[0]['num']);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="历史在投用户.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
	/**
	 * 测试每日理财收入
	 */
	public function due_income(){

		$dt = I('post.dt', '', 'strip_tags');
		if(!isDateFormat($dt)) $this->ajaxReturn(array('status'=>0,'info'=>'日期格式不正确'));
		$nowDt = date('Y-m-d', time());
		if($dt >= $nowDt) $this->ajaxReturn(array('status'=>1,'info'=>'只能抓取历史数据'));
		$rows['dt'] = $dt;

		$projectObj = M("Project");
		$userDueDetailObj = M("UserDueDetail");
		$userWalletRecordsObj = M("UserWalletRecords");
		$statisticsDailyProfitObj = M("StatisticsDailyProfit");
		$contractObj = M("Contract");
		$projectContractObj = M("ContractProject");

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
		$rows['p_profit'] = round($incomeSum, 2)-round($expensesSum, 2);

		$existId = $statisticsDailyProfitObj->where(array('dt'=>$dt))->getField('id');
		if(!$existId){
			if(!$statisticsDailyProfitObj->add($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'添加数据失败，请重试'));
		}else{
			if(!$statisticsDailyProfitObj->where(array('id'=>$existId))->save($rows)) $this->ajaxReturn(array('status'=>0,'info'=>'更新数据失败或无更新数据，请重试'));
		}

	}
	/**
	 *每日投资
	 */
	public function export_list(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('每月投资记录')->setCellValue("A1", "日期")->setCellValue("B1", "金额");

		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

		$pos = 2;
		for($i=strtotime('2015-04-01');$i<=strtotime('2015-12-09');$i+=86400){
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "";
			$list = array();
			$take_out_sql = "SELECT sum(a.`due_capital`) as num FROM stone.`s_user_due_detail` AS a WHERE a.`user_id` >0 and a.add_time>='".$tmp_date." 00:00:00.000000' and a.add_time<='".$tmp_date." 23:59:59.999000' order by a.add_time asc";
			$list = M()->query($take_out_sql);
			if($list[0]['num']){
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));
				$pos += 1;
			}

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="每月投资记录(2015-04-01至2015-12-09).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 理论还款本金
	 *
	 */
	public function capital_list(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('还款本金')->setCellValue("A1", "日期")->setCellValue("B1", "金额");

		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

		$pos = 2;
		for($i=strtotime('2015-04-01');$i<=strtotime('2015-12-09');$i+=86400){
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "";
			$list = array();
			$take_out_sql = "SELECT sum(a.`due_capital`) as num FROM stone.`s_user_due_detail` AS a WHERE a.`user_id` >0 and a.due_time>='".$tmp_date." 00:00:00.000000' and a.due_time<='".$tmp_date." 23:59:59.999000' order by a.add_time asc";
			$list = M()->query($take_out_sql);
			if($list[0]['num']){
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));
				$pos += 1;
			}

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="每月理论还款本金记录(2015-04-01至2015-12-09).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 实际还款本金
	 *
	 */
	public function leave_list(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('月底结余')->setCellValue("A1", "日期")->setCellValue("B1", "金额");

		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

		$pos = 2;
		for($i=strtotime('2015-04-01');$i<=strtotime('2015-12-09');$i+=86400){
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "";
			$list = array();
			$take_out_sql = "SELECT sum(a.`due_capital`) as num FROM stone.`s_user_due_detail` AS a WHERE a.`user_id` >0 and a.real_time>='".$tmp_date." 00:00:00.000000' and a.real_time<='".$tmp_date." 23:59:59.999000' order by a.add_time asc";
			$list = M()->query($take_out_sql);
			if($list[0]['num']){
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, number_format($list[0]['num'], 2));
				$pos += 1;
			}

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="每月实际还款(2015-04-01至2015-12-09).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 每月钱包本金和利息
	 */
	public function capital_money(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")
			->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('钱包本息记录')->setCellValue("A1", "日期")->setCellValue("B1", "本金")->setCellValue("C1", "利息");

		$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

		$pos = 2;
		for($i=strtotime('2015-04-01');$i<=strtotime('2015-12-10');$i+=86400){
			$tmp_date = date("Y-m-d",$i);
			$take_out_sql = "";
			$list = array();
			$take_out_sql = "SELECT sum(a.`interest_capital`) as capital,sum(a.`interest`) as interest FROM stone.`s_user_wallet_interest` AS a WHERE a.`user_id` >0 and a.interest_time>='".$tmp_date." 00:00:00.000000' and a.interest_time<='".$tmp_date." 23:59:59.999000' order by a.add_time asc";
			$list = M()->query($take_out_sql);
			if($list[0]['capital']){
				$objPHPExcel->getActiveSheet()->setCellValue("A".$pos, $tmp_date);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$pos, $list[0]['capital']);
				$objPHPExcel->getActiveSheet()->setCellValue("C".$pos, $list[0]['interest']);
				$pos += 1;
			}

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="每月钱包本息记录.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 在投用户
	 */
	public function export_in_due_list(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品-合同号')->setCellValue("A1", "用户名")->setCellValue("B1", "手机号码")->setCellValue("C1", "投资金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);


		$now_date = date("Y-m-d",time());
		$user_str = "13204222488,13510270024,13512963046,13516722040,13546222676,13560216135,13685187558,13701854247,13755000267,13772053229,13815850025,13817818159,13838021541,13858029161,13899308575,13906613787,13911325586,13932113678,13941277727,13960796937,13992895922,15038294608,15045384877,15260206210,15301105521,15531860717,15593549978,15716855351,15801276834,15822031186,15968827190,17712896661,17761275120,18210153259,18234072850,18382000750,18437536916,18520879903,18605224924,18608669266,18612324644,18618382719,18643538285,18652020721,18663489668,18680339976,18691110246,18696650209,18761808265,18795993920,18810247368,18841223611,18914826066,18930168000,18953128168,13015470318,13061681592,13064716818,13168873421,13218539985,13306528623,13308197816,13311109719,13321177253,13383843088,13403123958,13423914478,13428947284,13456991853,13485177073,13488853253,13502552581,13504517001,13510886360,13512026960,13512506005,13524052398,13564015089,13575661281,13575703195,13575913522,13584053425,13586355620,13600544030,13601262725,13621796199,13634116693,13643505715,13653865061,13656167789,13661764812,13661993671,13663670617,13675037841,13696513854,13701641890,13702050723,13703677858,13715172659,13753141221,13753499765,13777003443,13777768651,13806509216,13808939507,13809308046,13809378353,13810378876,13810804393,13810805888,13811902867,13812192255,13818252083,13818882352,13819150369,13819493159,13827702385,13831782095,13832952191,13840591243,13858509981,13867485471,13871650435,13899938008,13908999204,13917319851,13918159934,13918455841,13935700316,13937502359,13937699654,13952698916,13957125484,13958206668,13961708551,13968024820,13970844411,13986345016,13992700010,13995667077,15010915490,15022612619,15034408002,15034776284,15067365887,15068380078,15096396726,15132070695,15137418892,15151833025,15221225658,15235605392,15271814471,15345060309,15356682001,15557579767,15598527551,15612179019,15618003069,15640669528,15711113889,15823282065,15834176669,15844285029,15855517576,15900820016,15906708185,15928730331,15941708922,15943082258,15945663138,15992422242,15999399049,17717769900,18101021868,18101193335,18125985769,18161175755,18258085249,18264125699,18520332255,18523570702,18554659350,18602852226,18604305685,18610323360,18611754754,18613911706,18621575767,18627032316,18631698919,18647403002,18654301616,18658173118,18673559197,18679112268,18688803524,18718863933,18753156169,18810006970,18858912913,18915655268,18942786829,18956624839,18960539002,18970127319,18982351380";
		$sql = "SELECT m.`id`,m.`real_name`,m.`username` FROM stone.`s_user` AS m WHERE m.`username` IN(".$user_str.") GROUP BY m.`id`";
		$project_repayment_list = M()->query($sql);
		$pos = 2;
		foreach($project_repayment_list as $key => $value){
			$sql_tmp = "SELECT SUM(k.`due_capital`) AS num FROM stone.`s_user_due_detail` AS k WHERE k.`user_id` = ".$value['id']." AND k.`due_time`>'".$now_date." 23:23:59.999000'";
			$due_amount = M()->query($sql_tmp);

			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $due_amount[0]['num']);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="历史在投用户.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;

	}
	//渠道注册用户统计
	public function channel_tongji(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		$time_date = I("get.date",date("Y-m-d",time()-24*3600),"trim");
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('渠道注册用户统计')->setCellValue("A1", "渠道名称")->setCellValue("B1", "手机号码")->setCellValue("C1", "用户名")->setCellValue("D1", "身份证")->setCellValue("E1", "注册时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

		$sql = "SELECT k.`cons_value`,m.`username`,m.`real_name`,m.`card_no`,m.`mobile`,m.`add_time` FROM stone.`s_user` AS m,stone.`s_constant` AS k WHERE m.`channel_id` IN(168) AND m.`add_time`>'".$time_date." 00:00:00.000000' and  m.`add_time`<'".$time_date." 23:59:59.999000' AND m.`channel_id` = k.`id`";
		$project_repayment_list = M()->query($sql);
		$pos = 2;
		foreach($project_repayment_list as $key => $value){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['cons_value']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $value['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $value['card_no']);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $value['add_time']);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="蛋蛋赚渠道注册用户统计('.$time_date.').xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//渠道推广统计
	public function channel_analysis(){
		$arr = array(
			'161'=>'爱米1',
			'162'=>'爱米2',
			'163'=>'爱米3',
			'164'=>'点击',
			'165'=>'酷划2',
		);

	}
	//产品每天的利息
	public function product_interest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品利息统计')->setCellValue("A1", "日期")->setCellValue("B1", "利息");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$pos = 2;
		for($i=strtotime('2015-11-22');$i<strtotime('2015-11-28');$i+=86400){
			$date = date("Y-m-d",$i);
			$due_interest=array();
			$sql = "SELECT sum(w.`due_interest`) as num FROM stone.`s_user_due_detail` AS w WHERE w.`project_id` = 1523 AND w.`start_time`>='".$date." 00:00:00.000000' AND w.`start_time`<='".$date." 23:59:59.999000'";
			$due_interest = M()->query($sql);
			if($due_interest){
				$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $date);
				$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $due_interest[0]['num']);
				$pos++;
			}
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="产品利息统计.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//产品每天的利息
	public function interest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品利息列表统计')->setCellValue("A1", "本金")->setCellValue("B1", "利息")->setCellValue("C1", "计息天数")->setCellValue("D1", "计息开始时间")->setCellValue("E1", "利息结束时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);

		$sql = "SELECT * FROM stone.`s_user_due_detail` AS w WHERE w.`project_id` = 1523";
		$due_interest_list = M()->query($sql);
		$pos = 2;
		foreach($due_interest_list as $key => $value){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['due_capital']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['due_interest']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $value['duration_day']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $value['start_time']);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $value['due_time']);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="产品利息列表统计.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	//用户提现
	public function user_tx(){

		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		$time_date = "2015-04-01";
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('用户提现统计')->setCellValue("A1", "手机号码")->setCellValue("B1", "真实名称")->setCellValue("C1", "提现编码")->setCellValue("D1", "提现金额")->setCellValue("E1", "提现时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

		$pos = 2;
		$sql = "SELECT * FROM stone.`s_user_wallet_records` AS m WHERE m.`type` = 2  AND m.`user_bank_id`>0 AND m.`user_due_detail_id` = 0 AND m.`add_time`>='".$time_date." 00:00:00.000000'  GROUP BY m.`user_id`";
		$wallet_tx_list = M()->query($sql);
		foreach($wallet_tx_list as $k => $v){
			$sql_tmp = "SELECT m.*,n.username,n.real_name FROM stone.`s_user_wallet_records` AS m,stone.`s_user` as n WHERE n.id = m.user_id and m.`user_id` =".$v['user_id']."  AND m.`value` =".$v['value']."  AND m.`user_bank_id`>0 AND m.`user_due_detail_id` = 0 AND m.`add_time`>='".$time_date." 00:00:00.000000'";
			$user_records_list = M()->query($sql_tmp);
			if(count($user_records_list)>1){
				foreach($user_records_list as $key => $value){
					$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['username']);
					$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['real_name']);
					$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $value['recharge_no']);
					$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $value['value']);
					$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $value['add_time']);
					$pos++;
				}
			}
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="用户提现统计.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//渠道注册统计统计
	public function channel_zc(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);

		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('渠道注册用户统计')
		->setCellValue("A1", "日期")->setCellValue("B1", "渠道名称")
		->setCellValue("C1", "激活用户")->setCellValue("D1", "投资次数")
		->setCellValue("E1", "复投次数")->setCellValue("F1","投资总额")
		->setCellValue("G1","投资用户")->setCellValue("H1","首投用户")
		->setCellValue("I1","新增二次用户")->setCellValue("J1","历史复投用户")
		->setCellValue("K1",'首投总额')->setCellValue("L1",'广告消耗')->setCellValue('M1','注册用户');
		$objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
		$time_tmp = date("Y-m-d",time()-24*3600);
		$begin_date = "2015-12-01";//开始时间
		$end_date   = $time_tmp;//结束时间
		$pos = 2;
		for($i=strtotime($begin_date);$i<=strtotime($end_date);$i+=86400){
			$temp_date = date("Y-m-d",$i);
			$channel_list_arr = array(5,9,12,17,71,167);

			foreach($channel_list_arr as $k=>$v){

				if($v) {

					//日期
					$A = $temp_date;

					//渠道名称
					$B_sql = "SELECT m.`cons_value` FROM stone.`s_constant` AS m WHERE m.`id` = " . $v;
					$B_list_arr = M()->query($B_sql);
					$B = $B_list_arr[0]['cons_value'];

					//激活用户
					$C_sql = "SELECT COUNT(m.`id`) AS total FROM stone.`s_activation_device` AS m WHERE m.`active_time`>='" . $temp_date . " 00:00:00.000000' AND m.`active_time`<='" . $temp_date . " 23:59:59.999000' AND m.`channel_id` = " . $v;
					$C_num_list = M()->query($C_sql);
					$C = $C_num_list[0]['total'];

					//投资次数
					$D_sql = "SELECT u.id FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =" . $v . "  AND m.`add_time`<='" . $temp_date . " 23:59:59.999000') AND u.`add_time`>='" . $temp_date . " 00:00:00.000000' AND u.`add_time`<='" . $temp_date . " 23:59:59.999000'";
					$D_num_list = M()->query($D_sql);
					$D = count($D_num_list);

					//投资总额
					$F_sql = "SELECT u.due_capital FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =" . $v . "  AND m.`add_time`<='" . $temp_date . " 23:59:59.999000') AND u.`add_time`>='" . $temp_date . " 00:00:00.000000' AND u.`add_time`<='" . $temp_date . " 23:59:59.999000'";
					$F_num_list = M()->query($F_sql);
					$F = 0;
					foreach ($F_num_list as $k => $w) {
						$F += $w['due_capital'];
					}

					//投资用户
					$G_sql = "SELECT u.id FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =" . $v . "  AND m.`add_time`<='" . $temp_date . " 23:59:59.999000') AND u.`add_time`>='" . $temp_date . " 00:00:00.000000' AND u.`add_time`<='" . $temp_date . " 23:59:59.999000' GROUP BY u.`user_id`";
					$G_num_list = M()->query($G_sql);
					$G = count($G_num_list);

					//首投用户
					$H_sql = "SELECT u.id FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =".$v." AND m.`add_time`<='".$temp_date." 23:59:59.999000'  AND m.`id` NOT IN(SELECT e.`user_id` FROM stone.`s_user_due_detail` AS e WHERE e.`add_time`<='".$temp_date." 00:00:00.000000' AND e.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =".$v." AND m.`add_time`<='".$temp_date." 23:59:59.999000' ) GROUP BY e.`user_id`)) AND u.`add_time`>='".$temp_date." 00:00:00.000000' AND u.`add_time`<='".$temp_date." 23:59:59.999000' GROUP BY u.`user_id`";
					$H_num_list = M()->query($H_sql);
					$H = count($H_num_list);
					//复投次数
					$E = $D-$H;

					//新增二次用户
					$I_sql_one = "SELECT m.`user_id` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$temp_date." 00:00:00.000000' AND m.`add_time`<='".$temp_date." 23:59:59.999000' AND m.`user_id` IN(SELECT w.`id` FROM stone.`s_user` AS w WHERE w.`channel_id`=".$v." AND w.`add_time`<='".$temp_date." 23:59:59.999000' AND w.`id` NOT IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$temp_date." 00:00:00.000000' AND n.`user_id` IN(SELECT w.id FROM stone.`s_user` AS w WHERE w.`channel_id`=".$v." AND w.`add_time`<='".$temp_date." 23:59:59.999000') GROUP BY n.`user_id` HAVING COUNT(n.`user_id`)>0)) GROUP BY m.`user_id` HAVING COUNT(m.`user_id`)>1";
					$I_num_list_one = M()->query($I_sql_one);
					$I_one = count($I_num_list_one);
					$I_sql = "SELECT m.`user_id` FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`add_time`>='".$temp_date." 00:00:00.000000' AND m.`add_time`<='".$temp_date." 23:59:59.999000'  AND m.`user_id`  IN(SELECT n.`user_id` FROM stone.`s_user_due_detail` AS n WHERE n.`user_id`>0 AND n.`add_time`<='".$temp_date." 00:00:00.000000' AND n.`user_id` IN(SELECT w.id FROM stone.`s_user` AS w WHERE w.`channel_id`=".$v." AND w.`add_time`<='".$temp_date." 23:59:59.999000' ) GROUP BY n.`user_id` HAVING COUNT(n.`user_id`)=1) GROUP BY m.`user_id` HAVING COUNT(m.`user_id`)>0";
					$I_num_list = M()->query($I_sql);
					$I_two = count($I_num_list);
					$I = $I_one+$I_two;
					//历史复投用户
					$J_sql = "SELECT u.`user_id` FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =" . $v . "  AND m.`add_time`<='" . $temp_date . " 23:59:59.999000') AND  u.`add_time`<='" . $temp_date . " 23:59:59.999000' GROUP BY u.`user_id` having count(u.`user_id`)>1";
					$J_num_list = M()->query($J_sql);
					$J = count($J_num_list);
					//首投总额
					$L = 0;
					$K_sql = "SELECT u.due_capital FROM stone.`s_user_due_detail` AS u WHERE u.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =".$v." AND m.`add_time`<='".$temp_date." 23:59:59.999000'  AND m.`id` NOT IN(SELECT e.`user_id` FROM stone.`s_user_due_detail` AS e WHERE e.`add_time`<='".$temp_date." 00:00:00.000000' AND e.`user_id` IN(SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`channel_id` =".$v." AND m.`add_time`<='".$temp_date." 23:59:59.999000' ) GROUP BY e.`user_id`)) AND u.`add_time`>='".$temp_date." 00:00:00.000000' AND u.`add_time`<='".$temp_date." 23:59:59.999000' GROUP BY u.`user_id`";
					$K_num_list = M()->query($K_sql);
					$K = 0;
					foreach ($K_num_list as $k => $w) {
						$K += $w['due_capital'];
						if($v == 168){//蛋蛋赚
							if($w['due_capital']>=200 && $w['due_capital'] <=499){//20
								$L+=20;
							}else if($w['due_capital']>=500 && $w['due_capital'] <=999){//30
								$L+=30;
							}else if($w['due_capital']>=1000){//50
								$L+=50;
							}
						}else if($v == 164){//点击
							if($w['due_capital']>=300 && $w['due_capital'] <=499){//50
								$L+=50;
							}else if($w['due_capital']>=500){//70
								$L+=70;
							}
						}else if($v == 167){//点入
							if($w['due_capital']>=100){//30
								$L+=30;
							}
						}
					}

					//注册用户
					$M_sql = "SELECT COUNT(m.`id`) AS total FROM stone.`s_user` AS m WHERE m.`add_time`>='" . $temp_date . " 00:00:00.000000' AND m.`add_time`<='" . $temp_date . " 23:59:59.999000' AND m.`channel_id` = " . $v;
					$M_num_list = M()->query($M_sql);
					$M = $M_num_list[0]['total'];

					//广告消耗
					if(in_array($v,array(161,162,163))){//爱米
						$L += $M*4;

					}else if($v == 165){//酷划2-激活
						$L += 2*$C;
					}else if($v == 168){//蛋蛋赚
						$L +=$M*4;
					}


					$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $A);
					$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $B);
					$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $C);
					$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $D);
					$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $E);
					$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $F);
					$objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $G);
					$objPHPExcel->getActiveSheet()->setCellValue("H" . $pos, $H);
					$objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, $I);
					$objPHPExcel->getActiveSheet()->setCellValue("J" . $pos, $J);
					$objPHPExcel->getActiveSheet()->setCellValue("K" . $pos, $K);
					$objPHPExcel->getActiveSheet()->setCellValue("L" . $pos, $L);
					$objPHPExcel->getActiveSheet()->setCellValue("M" . $pos, $M);
					$pos++;
				}
			}
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="渠道注册用户统计(日期：'.$begin_date.'至'.$end_date.').xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//平台首投钱包用户
	public function due_wallet(){
		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);

		$userWalletInterestObj = M('UserWalletInterest');
		$userWalletRecordsObj = M("UserWalletRecords");
		$userAccountRecordsObj = M('UserAccount');

		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('用户首投钱包用户列表')->setCellValue("A1", "手机号码")->setCellValue("B1", "真实名称")->setCellValue("C1", "银行卡充值")->setCellValue("D1", "还本付息")->setCellValue("E1","充值金额")->setCellValue("F1", "提现银行卡")->setCellValue("G1", "提现购买产品")->setCellValue("H1", "提现金额")->setCellValue("I1", "钱包利息")->setCellValue("J1", "钱包实际余额")->setCellValue("K1", "15以后提现")->setCellValue("L1","是否钱包用户");
		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setName('宋体')->setSize(11);
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
		
		$pos = 2;
		$wallet_tx_list =array();
		$sql = "SELECT n.`username`,n.`real_name`,n.`id` FROM stone.`s_user` AS n where n.`real_name_auth` = 1";
		$wallet_tx_list = M()->query($sql);

		foreach($wallet_tx_list as $k => $v){
			$list = array();
			$sql_list = "SELECT *  FROM stone.`s_user_wallet_records` AS m WHERE m.user_id= ".$v['id']." AND m.add_time<='2016-01-19 23:59:59.999000'";
			$list = M()->query($sql_list);
			//银行卡充值
			$bank_takein_amount = 0;
			//还本付息
			$product_takein_amount = 0;
			//充值总金额
			$takein_amount =  0;
			//提现到银行卡
			$bank_takeout_amount = 0;
			//提现到购买产品
			$product_takeout_amount = 0;
			//提现总金额
			$takeout_amount = 0;
			//15点以后提现金额
			$takeout_15_amount = 0;
			foreach($list as $q => $w){
				if($w['type'] == 1 && $w['pay_status'] == 2 && $w['user_bank_id']>0 && $w['user_due_detail_id'] == 0){//银行卡充值
					$bank_takein_amount+=$w["value"];
					$takein_amount+=$w['value'];
				}else if($w['type'] == 1 && $w['pay_status'] == 2 && $w['user_bank_id']==0 && $w['user_due_detail_id'] > 0){//还本付息
					$product_takein_amount+=$w['value'];
					$takein_amount+=$w['value'];
				}else if($w['type'] == 2 && $w['user_bank_id']>0 && $w['user_due_detail_id'] == 0 && $w['add_time']<='2016-01-19 15:00:00.000000'){//提现到银行卡
					$bank_takeout_amount+=$w['value'];
					$takeout_amount+=$w['value'];
				}else if($w['type'] == 2 && $w['user_bank_id']>0 && $w['user_due_detail_id'] == 0 && $w['add_time']>'2016-01-19 15:00:00.000000' && $w['add_time']<='2016-01-19 23:59:59.999000'){//15点以后提现金额
					$takeout_15_amount+=$w['value'];
				}else if($w['type'] == 2 && $w['user_bank_id']==0 && $w['user_due_detail_id'] > 0){//提现到购买产品
					$product_takeout_amount+=$w['value'];
					$takeout_amount+=$w['value'];
				}
			}
			//总利息
			$interest_amount = $userWalletInterestObj->where(array('user_id'=>$v['id']))->sum('interest');
			//钱包实际余额
			$wallet_amount   =  $userAccountRecordsObj->where(array('user_id'=>$v['id']))->field('wallet_totle')->find();
			//判断是否钱包用户
			if($takein_amount || $takeout_amount){
				$is_wallet_user  = "是";
			}else{
				$is_wallet_user  = "否";
			}


			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $bank_takein_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $product_takein_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $takein_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $bank_takeout_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $product_takeout_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("H" . $pos, $takeout_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("I" . $pos, $interest_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("J" . $pos, $wallet_amount['wallet_totle']);
			$objPHPExcel->getActiveSheet()->setCellValue("K" . $pos, $takeout_15_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("L" . $pos, $is_wallet_user);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//给用户循环赠送红包
	public function send_redWallet(){
		header("Content-type: text/html;charset=utf-8");
		$userBankObj =  M("UserBank");//用户关联银行卡
		$userWalletRecordsObj = M("UserWalletRecords");//用户钱包转入转出记录信息表
		$userAccountObj = M("UserAccount");//用户信息表
		$projectObj = M("Project");// 产品表
		$userObj    = M("User");//用户表
		$sql = 'SELECT m.`id` FROM stone.`s_user` AS m WHERE m.`username` IN(15707598128,17727929330,18686857538,15002501353,13919689235,18358561668)';
		$user_list = M()->query($sql);
		$amount = 10;
		foreach($user_list as $k=>$v){
			//用户银行卡ID
			$userBankId  = $userBankObj->where(array('user_id'=>$v['id'],'has_pay_success'=>2))->getField('id');
			//查出用户是什么机型
			$deviceType = $userObj->where(array('id'=>$v['id']))->getField('device_type');
			//插入钱包转入/转出记录
			$now_time = date("Y-m-d H:i:s",time()).'.'.getMillisecond().'000';


			$userWalletRecordsArr = array(
				'user_id'=>$v['id'],
				'recharge_no'=>"XT".date("YmdHis").$this->rand_num(),
				'pay_type'=>3,
				'value'=>$amount,
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
				$userAccountObj->where(array('user_id'=>$v['id']))->setInc('wallet_totle',$amount);
				//修改用户银行卡
				$userBankObj->where(array('id'=>$userBankId))->setInc('wallet_money',$amount);
			}
		}
		exit("处理完成");

	}
	public function rand_num(){
		$str = "";
		for($i=0;$i<6;$i++){
			$str.=mt_rand(0,9);
		}
		return $str;
	}
	//好友邀请列表
	public function invite_friend(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('用户邀请好友列表')->setCellValue("A1", "邀请人手机号码")->setCellValue("B1", "邀请人真实名称")->setCellValue("C1", "被邀请人手机号码")->setCellValue("D1", "被邀请人真实名称");
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

		$pos = 2;

		$sql = "SELECT n.`real_name`,n.`username`,m.`user_id` FROM stone.`s_user_invitation` AS m,stone.`s_user` AS n WHERE m.`add_time`>='2015-12-25 00:00:00.000000' AND m.`add_time`<='2016-01-03 23:59:59.999000' AND m.`user_id` = n.`id` GROUP BY m.`user_id`";
		$invite_list = M()->query($sql);
		foreach($invite_list as $k => $v){
			$sql_invited = "SELECT n.`real_name`,n.`username`,m.`invited_user_id` FROM stone.`s_user_invitation` AS m,stone.`s_user` AS n WHERE m.`add_time`>='2015-12-25 00:00:00.000000' AND m.`add_time`<='2016-01-03 23:59:59.999000' AND m.`invited_user_id` = n.`id`  AND m.`invited_success`=1 and m.user_id=".$v['user_id'];
			$invited_list = M()->query($sql_invited);
			foreach($invited_list as $u=>$b){
				$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['username']);
				$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['real_name']);
				$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $b['username']);
				$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $b['real_name']);
				$pos++;
			}
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="用户邀请列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/***
	 * 新人送红包活动
	 */
	public function red_envelope(){
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
		$query_str = "";
		$cond[] ="add_time>='2015-12-25 00:00:00.000000' and add_time<='2016-01-03 23:59:59.999000'";
		$cond[] = "is_send = 1";
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
		header('Content-Disposition: attachment;filename="用户红包(2015-12-25至2016-01-03).xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//指定标的本金和利息
	public function project(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('标的用户本息列表')->setCellValue("A1", "姓名")->setCellValue("B1", "手机号码")->setCellValue("C1", "本金")->setCellValue("D1", "利息")->setCellValue("E1", "标")->setCellValue("F1", "购买时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);

		$pos = 2;

		$sql = "SELECT n.`real_name`,n.`username`,m.`due_capital`,m.`due_interest`,m.`add_time`,k.`title` FROM stone.`s_user_due_detail` AS m,stone.`s_user` AS n,stone.`s_project` AS k WHERE m.`project_id` = 1544 AND m.`user_id` = n.`id` AND m.`project_id` = k.`id`";
		$project_list = M()->query($sql);
		foreach($project_list as $k => $v){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['due_capital']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $v['due_interest']);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $v['add_time']);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="标的用户本息列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//首投用户
	public function one_due_list(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('用户首投列表')->setCellValue("A1", "姓名")->setCellValue("B1", "手机号码")->setCellValue("C1", "投资额")->setCellValue("D1", "投资时间")->setCellValue("E1", "投资标");
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);

		$pos = 2;

		$sql = "SELECT n.`real_name`,n.`username`,m.`due_capital`,m.`add_time`,k.`title` FROM stone.`s_user_due_detail` AS m,stone.`s_user` AS n,stone.`s_project` AS k WHERE m.`user_id` NOT IN(SELECT n.`id` FROM stone.`s_user` AS n WHERE n.`add_time`<='2016-01-04 00:00:00.000000') AND  m.`user_id` = n.`id` AND m.`project_id` = k.`id` AND m.`add_time`>='2016-01-04 00:00:00.000000' AND m.`add_time`<='2016-01-05 12:30:59.999000'";
		$project_list = M()->query($sql);
		foreach($project_list as $k => $v){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['due_capital']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $v['add_time']);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="用户首投列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	//指定月份的本金+利息+利率
	public function wallet_interest(){
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('利息列表统计')->setCellValue("A1", "日期")->setCellValue("B1", "本金")->setCellValue("C1", "利息")->setCellValue("D1", "利率");
		$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

		$begin_time ="2015-12-01"; //I("get.st",'','strip_tags');//开始时间
		$end_time   ="2015-12-31"; //I("get.et",'','strip_tags');//结束时间
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
	//指定月份投资金额排序
	public function due_order(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品利息列表统计')->setCellValue("A1", "用户名")->setCellValue("B1", "手机号码")->setCellValue("C1", "投资金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$begin_time = I("get.st",'','strip_tags');//开始时间
		$end_time   = I("get.et",'','strip_tags');//结束时间
		if(empty($begin_time) || empty($end_time)){
			exit("请输入筛选日期");
		}

		$sql = "SELECT m.`user_id`,w.`real_name`,w.`username` FROM stone.`s_user_due_detail`  AS m ,stone.`s_user` AS w WHERE m.`add_time`>='".$begin_time." 00:00:00.000000' AND m.`add_time`<='".$end_time." 23:59:59.999000' AND m.`user_id` = w.`id` AND  m.`user_id` >0 GROUP BY m.`user_id`";
		$due_user_list = M()->query($sql);
		$pos = 2;
		foreach($due_user_list as $key => $value){
			// 金额
			$person_sql = "SELECT SUM(m.`due_capital`) AS num  FROM stone.`s_user_due_detail`  AS m WHERE m.`add_time`>='".$begin_time." 00:00:00.000000' AND m.`add_time`<='".$end_time." 23:59:59.999000'  AND m.`user_id` = ".$value['user_id'];
			$person_amount_arr = M()->query($person_sql);
			$person_amount = $person_amount_arr[0]['num'];
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $person_amount);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')用户投资金额排序.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/*
	 * 指定时间段内注册没有投资理财的用户列表
	 */
	public function no_due_user_list(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('时间段内注册没有投资理财的用户列表')->setCellValue("A1", "姓名")->setCellValue("B1", "手机号码")->setCellValue("C1", "注册时间");
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$begin_time = "2015-10-01";
		$end_time   = "2015-11-30";
		$pos = 2;
		$sql = "SELECT * FROM stone.`s_user` AS m WHERE m.`add_time`>='".$begin_time." 00:00:00.000000' AND m.`add_time`<='".$end_time." 23:59:59.999000' AND m.`id` NOT IN(SELECT w.`user_id` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id`>0 AND w.`add_time`<='2016-01-08 23:59:59.999000' GROUP BY w.`user_id`)";
		$project_list = M()->query($sql);
		foreach($project_list as $k => $v){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, date("Y-m-d",strtotime($v['add_time'])));
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')时间段内注册没有投资理财的用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 指定时间段内需要送红包的用户
	 */
	public function send_red_list(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('时间段内需要送红包的用户列表')->setCellValue("A1", "姓名")->setCellValue("B1", "手机号码")->setCellValue("C1", "注册时间")->setCellValue("D1", "标")->setCellValue("E1", "投资额")->setCellValue("F1", "投资时间")->setCellValue("G1", "产品期限");
		$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
		$begin_time = "2016-01-07";
		$end_time   = "2016-01-07";
		$pos = 2;
		$sql = "SELECT b.`real_name`,b.`username`,e.`due_capital`,e.`add_time`,b.`add_time` AS reg_time,v.`title`,v.`duration` FROM stone.`s_user_due_detail` AS e,stone.`s_user` AS b,stone.`s_project` AS v WHERE e.`user_id` = b.`id` AND e.`project_id` = v.`id` AND e.`user_id`>0 AND e.`add_time`>='".$begin_time." 00:00:00.000000' AND e.`add_time`<='".$end_time." 23:59:59.999000' AND e.`user_id` NOT IN(SELECT w.`user_id` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id`>0 AND w.`add_time`<='".$begin_time." 00:00:00.000000' GROUP BY w.`user_id`)";
		$project_list = M()->query($sql);
		foreach($project_list as $k => $v){
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, date("Y-m-d",strtotime($v['reg_time'])));
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $v['due_capital']);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, date("Y-m-d",strtotime($v['add_time'])));
			$objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $v['duration']);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')时间段内需要送红包的用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//新人投资送红包奖励-补记录
	public function write_redWallet(){
		header("Content-type: text/html;charset=utf-8");
		$userRedenvelopeObj = M('UserRedenvelope');//用户红包列表
		$begin_time = "2016-01-01";
		$end_time   = "2016-01-31";
		$sql = "SELECT b.`real_name`,b.`username`,b.`id`,e.`due_capital`,e.`project_id`,e.`add_time`,b.`add_time` AS reg_time,v.`title`,v.`duration`,h.`recharge_no` FROM stone.`s_user_due_detail` AS e,stone.`s_user` AS b,stone.`s_project` AS v,stone.`s_investment_detail` AS h  WHERE e.`user_id` = b.`id` AND e.`project_id` = v.`id` AND e.`user_id`>0 AND e.`invest_detail_id` = h.`id` AND e.`add_time`>='".$begin_time." 00:00:00.000000' AND e.`add_time`<='".$end_time." 23:59:59.999000' AND e.`user_id` NOT IN(SELECT w.`user_id` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id`>0 AND w.`add_time`<='2016-01-01 00:00:00.000000' GROUP BY w.`user_id`)";
		$user_list = M()->query($sql);
		$amount = 0;
		foreach($user_list as $k=>$v){
			if($v['due_capital']>=1000) {
				if ($v['due_capital'] >= 1000 && $v['duration'] < 50) {
					$amount = 10;
				} else if ($v['due_capital'] >= 5000 && ($v['duration'] > 50 && $v['duration'] < 140)) {
					$amount = 38;
				} else if ($v['due_capital'] >= 10000 && $v['duration'] >= 140) {
					$amount = 88;
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
			}
		}
		exit("处理完成");

	}
	/*
	 * 统计钱包每天投资榜单
	 */
	public function wallet_order(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('投资钱包统计')->setCellValue("A1", "日期")->setCellValue("B1", "金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);

		$begin_time = "2015-03-03";//开始时间
		$end_time   = "2015-12-31";//结束时间
		$pos = 2;
		for($i=strtotime($begin_time);$i<=strtotime($end_time);$i+=86400){
			$date = date("Y-m-d",$i);
			// 金额
			$person_sql = "SELECT SUM(m.`value`) AS num FROM stone.`s_user_wallet_records` AS  m WHERE m.`type` = 1 AND m.`pay_status` = 2  AND  m.`add_time`>='".$date." 00:00:00.000000' AND m.`add_time`<='".$date." 23:59:59.999000'";
			$person_amount_arr = M()->query($person_sql);
			$person_amount = $person_amount_arr[0]['num'];
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $date);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $person_amount);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')投资钱包统计.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/*
	 * 统计每日投资榜单
	 */
	public function due_list_order(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('每日投资统计')->setCellValue("A1", "日期")->setCellValue("B1", "金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);

		$begin_time = "2015-04-01";//开始时间
		$end_time   = "2016-01-31";//结束时间
		$pos = 2;
		for($i=strtotime($begin_time);$i<=strtotime($end_time);$i+=86400){
			$date = date("Y-m-d",$i);
			// 金额-one
			$person_one_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`add_time`>='".$date." 00:00:00.000000' AND m.`add_time`<='".$date." 23:59:59.999000'";
			$person_one_amount_arr = M()->query($person_one_sql);
			$person_one_amount = $person_one_amount_arr[0]['total'];
			//金额-two
			$person_two_sql = "SELECT SUM(m.`value`) AS num FROM stone.`s_user_wallet_records` AS  m WHERE m.`type` = 1 AND m.`pay_status` = 2  AND  m.`add_time`>='".$date." 00:00:00.000000' AND m.`add_time`<='".$date." 23:59:59.999000'";
			$person_two_amount_arr = M()->query($person_two_sql);
			$person_two_amount = $person_two_amount_arr[0]['num'];

			$total = $person_one_amount+$person_two_amount;

			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $date);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $total);
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')每日投资统计.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//投资用户金额排序
	public function due_user_order(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "1000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('产品利息列表统计')->setCellValue("A1", "用户名")->setCellValue("B1", "手机号码")->setCellValue("C1", "性别")->setCellValue("D1", "年龄")->setCellValue("E1", "省份")->setCellValue("F1", "投资金额")->setCellValue("G1", "获得利息");
		$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);

		$end_time   = "2015-12-31";//时间

		$sql = "SELECT m.`id`,m.`real_name`,m.`username`,m.`mobile`,m.`card_no` FROM stone.`s_user` AS m WHERE m.`real_name_auth` = 1 AND m.`add_time`<='".$end_time." 23:59:59.999000'";
		$due_user_list = M()->query($sql);
		$pos = 2;
		foreach($due_user_list as $key => $value){
			// 理财-金额
			$person_sql = "SELECT SUM(m.`due_capital`) AS num  FROM stone.`s_user_due_detail`  AS m WHERE  m.`add_time`<='".$end_time." 23:59:59.999000'  AND m.`user_id` = ".$value['id'];
			$person_amount_arr = M()->query($person_sql);
			$person_amount = $person_amount_arr[0]['num'];
			//钱包-金额
			$person_two_sql = "SELECT SUM(m.`value`) AS total FROM stone.`s_user_wallet_records` AS m WHERE m.`add_time`<='".$end_time." 23:59:59.99900' AND m.`type` = 1 AND m.`pay_status` = 2 AND m.`user_id` = ".$value['id'];
			$person_two_amount_arr = M()->query($person_two_sql);
			$person_two_amount = $person_two_amount_arr[0]['total'];
			//总金额
			$amount = $person_amount+$person_two_amount;
			// 计算用户年龄和性别
			$year = substr($value['card_no'], 6, 4);
			$nowYear = date('Y', time());
			$age = $nowYear - $year;
			$sex = substr($value['card_no'], strlen($value['card_no']) - 2, 1);
			if($sex % 2 != 0) $sex_name = '男';
			else $sex_name = '女';
			//省份
			$province = "";
			//利息
			$product_interest_sql = "SELECT SUM(m.`due_interest`) AS total FROM stone.`s_user_due_detail` AS m WHERE m.`add_time`<='".$end_time." 23:59:59.999000' AND m.`user_id` = ".$value['id'];
			$product_interest_arr = M()->query($product_interest_sql);
			$product_interest_amount = $product_interest_arr[0]['total'];

			$wallet_interest_sql = "SELECT SUM(m.`interest`) AS total FROM stone.`s_user_wallet_interest` AS m WHERE m.`interest_time`<='".$end_time." 23:59:59.999000' AND m.`user_id` = ".$value['id'];
			$wallet_interest_arr = M()->query($wallet_interest_sql);
			$wallet_interest_amount = $wallet_interest_arr[0]['total'];

			$amount_interest = $product_interest_amount + $wallet_interest_amount;

			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $value['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $value['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $sex_name);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $age);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $province);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $amount);
			$objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $amount_interest);
			$pos++;
		}

		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$end_time.')用户投资金额排序.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/*
 	* 统计指定时间段还本付息的标
 	*/
	public function tongji_time_due(){
		header("Content-type:text/html;charset=utf-8");
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('还本付息的标')->setCellValue("A1", "标名称")->setCellValue("B1", "还本到钱包本金")->setCellValue("C1", "还本到钱包利息")->setCellValue("D1", "还本到银行卡本金")->setCellValue("E1", "还本到银行卡利息")->setCellValue("F1", "到期日期")->setCellValue("G1", "融资人");
		$objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);

		$begin_time = "2015-11-01";//开始时间
		$end_time   = "2015-11-30";//结束时间
		$repayment_sql = "SELECT m.`project_id`,m.`id`,m.`repayment_time` FROM stone.`s_repayment_detail` AS m WHERE m.`repayment_time`>='".$begin_time." 00:00:00.000000' AND m.`repayment_time`<='".$end_time." 23:59:59.999000' AND m.`status` = 2 AND m.`status_new` = 2";

		$repayment_list_arr = M()->query($repayment_sql);
		$pos = 2;
		foreach($repayment_list_arr as $k=>$v){
			//标的名称
			$project_one_sql = "SELECT `title`,`term_type`,`financing` FROM stone.`s_project` AS m WHERE m.`id`=".$v['project_id']."";
			$project_one_amount_arr = M()->query($project_one_sql);
			$project_title = $project_one_amount_arr[0]['title'];
			$project_type  = $project_one_amount_arr[0]['term_type'];
			$project_financing = $project_one_amount_arr[0]['financing'];

			if($project_type == 1 && $project_financing == '王伟军'){
				// 还本到钱包
				$person_one_sql = "SELECT SUM(m.`due_capital`) as due_total,SUM(m.`due_interest`) as due_interest FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`project_id`=".$v['project_id']." AND m.`repay_id`= ".$v['id']." AND m.`to_wallet` =1";
				$person_one_amount_arr = M()->query($person_one_sql);
				$person_one_amount = $person_one_amount_arr[0]['due_total'];
				$person_two_amount = $person_one_amount_arr[0]['due_interest'];
				//还本到银行卡
				$person_two_sql = "SELECT SUM(m.`due_capital`) as due_total,SUM(m.`due_interest`) as due_interest FROM stone.`s_user_due_detail` AS m WHERE m.`user_id`>0 AND m.`project_id`=".$v['project_id']." AND m.`repay_id`= ".$v['id']." AND m.`to_wallet` =0";
				$person_two_amount_arr = M()->query($person_two_sql);
				$person_three_amount = $person_two_amount_arr[0]['due_total'];
				$person_four_amount = $person_two_amount_arr[0]['due_interest'];
				//日期
				$date_time = date("Y-m-d",strtotime($v['repayment_time']));

				$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $project_title);
				$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $person_one_amount);
				$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $person_two_amount);
				$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $person_three_amount);
				$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $person_four_amount);
				$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, $date_time);
				$objPHPExcel->getActiveSheet()->setCellValue("G" . $pos, $project_financing);
				$pos++;
			}

		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="('.$begin_time.'至'.$end_time.')还本付息的标.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	//平台所有用户截止时间(2016-01-17)
	public function all_user(){
		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);

		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('平台所有用户列表')->setCellValue("A1", "用户id")->setCellValue("B1", "用户名字")->setCellValue("C1", "用户账号")->setCellValue("D1", "身份证")->setCellValue("E1","投资金额")->setCellValue("F1","注册时间");;
		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);

		$date_time = date("Y-m-d",time()-24*3600);
		$pos = 2;
		$wallet_tx_list =array();
		$sql = "SELECT n.`username`,n.`real_name`,n.`id`,n.`card_no`,n.`add_time` FROM stone.`s_user` AS n where n.`real_name_auth` = 1 and n.`add_time`<='".$date_time." 23:59:59.999000'";
		$wallet_user_list = M()->query($sql);

		foreach($wallet_user_list as $k => $v){

			$sql_wallet_list = "SELECT sum(m.value) as wallet_total  FROM stone.`s_user_wallet_records` AS m WHERE m.user_id= ".$v['id']." AND m.type = 1 and m.pay_status=2 and m.user_bank_id>0 and m.user_due_detail_id=0 and m.add_time<='".$date_time." 23:59:59.999000'";
			$wallet_list = M()->query($sql_wallet_list);
			//银行卡充值
			$bank_takein_amount = $wallet_list[0]['wallet_total'];
			//购买产品
			$sql_product_list = "SELECT SUM(m.`due_capital`) AS product_total FROM stone.`s_user_due_detail` AS m WHERE  m.user_id= ".$v['id']." AND m.add_time<='".$date_time." 23:59:59.999000'";
			$product_list = M()->query($sql_product_list);
			$product_takein_amount = $product_list[0]['product_total'];


			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['id']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, strval($v['card_no'])."L");
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $product_takein_amount+$bank_takein_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, date("Y-m-d",strtotime($v['add_time'])));

			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="平台所有用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 统计T-1点入渠道统计投资用户
	 */
	public function dianru_due_user(){

		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('点入渠道统计投资用户列表')->setCellValue("A1", "用户真实名称")->setCellValue("B1", "用户账号")->setCellValue("C1", "项目名称")->setCellValue("D1", "项目周期")->setCellValue("E1","投资金额")->setCellValue("F1","投资日期");
		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);


		$date_time = date("Y-m-d",time()-24*3600);
		$chanel_id = 167;//点入渠道
		$pos = 2;

		$sql = "SELECT w.`real_name`,w.`username`,q.`title`,q.`end_time`,m.`amount`,m.`add_time` FROM stone.`s_recharge_log` AS m,stone.`s_user` AS w,stone.`s_project` AS q WHERE m.`project_id` = q.`id` AND w.`id` = m.`user_id` AND m.`status` = 2 AND m.`user_id` IN(SELECT n.`id` FROM stone.`s_user` AS n WHERE n.`channel_id` = ".$chanel_id." AND n.`add_time`<='".$date_time." 23:59:59.999000')  AND m.`add_time`<='".$date_time." 23:59:59.999000'";
		$due_user_detail_list = M()->query($sql);

		foreach($due_user_detail_list as $k => $v){
			$duration = (strtotime(date("Y-m-d",strtotime($v['end_time'])))-strtotime(date("Y-m-d",strtotime($v['add_time'])+24*3600)))/(24*3600)+1;
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $duration);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $v['amount']);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, date("Y-m-d",strtotime($v['add_time'])));
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="(截至'.$date_time.')点入渠道统计投资用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 * 统计投资理财达到指定金额范围的用户
	 */
	public function tongji_due_user(){

		ini_set("memory_limit", "3000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('统计投资用户列表')->setCellValue("A1", "用户真实名称")->setCellValue("B1", "用户账号")->setCellValue("C1", "项目名称")->setCellValue("D1", "项目周期")->setCellValue("E1","投资金额")->setCellValue("F1","投资日期");
		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);


		$date_time = date("Y-m-d",time()-24*3600);

		$pos = 2;
		$amount = 100000;
		$sql = "SELECT w.`real_name`,w.`username`,q.`title`,q.`end_time`,m.`amount`,m.`add_time` FROM stone.`s_recharge_log` AS m,stone.`s_user` AS w,stone.`s_project` AS q WHERE m.`project_id` = q.`id` AND w.`id` = m.`user_id` AND m.`status` = 2  AND m.`amount`>=".$amount." AND  m.`add_time`<='".$date_time." 23:59:59.999000' GROUP BY m.user_id";
		$due_user_detail_list = M()->query($sql);

		foreach($due_user_detail_list as $k => $v){
			$duration = (strtotime(date("Y-m-d",strtotime($v['end_time'])))-strtotime(date("Y-m-d",strtotime($v['add_time'])+24*3600)))/(24*3600)+1;
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['real_name']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $v['title']);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $duration);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $v['amount']);
			$objPHPExcel->getActiveSheet()->setCellValue("F" . $pos, date("Y-m-d",strtotime($v['add_time'])));
			$pos++;
		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="(截至'.$date_time.')统计投资用户列表.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 统计累计投资金额
	 */
	public function tongji_total_due(){

		$begin_time = "2015-04-01";//开始时间
		$end_time   = "2016-01-31";//结束时间
		$pos = 2;
		for($i=strtotime($begin_time);$i<=strtotime($end_time);$i+=86400){
			$date = date("Y-m-d",$i);
			// 金额-one
			$person_one_sql = "SELECT SUM(m.`due_capital`) AS total FROM stone.`s_user_due_detail` AS m WHERE   m.`add_time`<='".$date." 23:59:59.999000'";
			$person_one_amount_arr = M()->query($person_one_sql);
			$person_one_amount = $person_one_amount_arr[0]['total'];
			//金额-two
			$person_two_sql = "SELECT SUM(m.`value`) AS num FROM stone.`s_user_wallet_records` AS  m WHERE m.`type` = 1 AND m.`pay_status` = 2  AND  m.`add_time`<='".$date." 23:59:59.999000'";
			$person_two_amount_arr = M()->query($person_two_sql);
			$person_two_amount = $person_two_amount_arr[0]['num'];

			$total = $person_one_amount+$person_two_amount;
			if($total>=500000000){
				echo $date;exit;
			}

		}
	}

	/**
	 * csv格式
	 */
	public function tongji_export_csv(){
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="活动记录.csv"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		ob_flush();
		flush();

		//创建临时存储内存
		$fp = fopen('php://memory','w');
		fputcsv($fp,array('id','列1','列2','列3','列4'),';');
		$list = array(
			'id'=>185,
			'c1'=>'485',
			'c2'=>'485',
			'c3'=>'48',
			'c4'=>'789'

		);
		foreach($list as $item) {
			fputcsv($fp,array($item['id'],$item['c1'],$item['c2'],$item['c3'],date('Y-m-d H:i:s',$item['c4'])),';');
		}

		rewind($fp);
		$content = "";
		while(!feof($fp)){
			$content .= fread($fp,1024);
		}
		fclose($fp);
		$content = iconv('utf-8','gbk',$content);//转成gbk，否则excel打开乱码
		echo $content;
		exit;
	}
	/**
	 * 导出指定时段用户投资记录
	 */
	public function export_user_due(){
		ini_set("memory_limit", "2000M");
		ini_set("max_execution_time", 0);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator("石头理财")->setLastModifiedBy("石头理财")->setTitle("title")->setSubject("subject")->setDescription("description")->setKeywords("keywords")->setCategory("Category");
		$objPHPExcel->setActiveSheetIndex(0)->setTitle('还本付息的标')->setCellValue("A1", "用户名")->setCellValue("B1", "2015-10-12至2015-11-30投资次数")->setCellValue("C1", "2015-10-12至2015-11-30投资金额")->setCellValue("D1", "2015-12-1至2016-1-20投资次数")->setCellValue("E1", "2015-12-1至2016-1-20投资金额");
		$objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setName('宋体')->setSize(11);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);



		$sql = "SELECT m.`username`,m.`id` FROM stone.`s_user` AS m WHERE m.`username` IN(13204222488,13510270024,13512963046,13516722040,13546222676,13560216135,13685187558,13701854247,13755000267,13772053229,13815850025,13817818159,13838021541,13858029161,13899308575,13906613787,13911325586,13932113678,13941277727,13960796937,13992895922,15038294608,15045384877,15260206210,15301105521,15531860717,15593549978,15716855351,15801276834,15822031186,15968827190,17712896661,17761275120,18210153259,18234072850,18382000750,18437536916,18520879903,18605224924,18608669266,18612324644,18618382719,18643538285,18652020721,18663489668,18680339976,18691110246,18696650209,18761808265,18795993920,18810247368,18841223611,18914826066,18930168000,18953128168)";

		$test_user_list = M()->query($sql);
		$pos = 2;
		foreach($test_user_list as $k=>$v){
			$A_sql = "SELECT w.`id`,w.`due_capital` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id` =".$v['id']." AND w.`add_time`>='2015-10-12 00:00:00.000000' AND w.`add_time`<='2015-11-30 23:59:59.999000'";
			$A_list = M()->query($A_sql);
			$B_sql = "SELECT w.`id`,w.`due_capital` FROM stone.`s_user_due_detail` AS w WHERE w.`user_id` =".$v['id']." AND w.`add_time`>='2015-12-1 00:00:00.000000' AND w.`add_time`<='2016-1-20 23:59:59.999000'";
			$B_list = M()->query($B_sql);
			$A_num = 0;
			$A_amount = 0;
			$B_num = 0;
			$B_amount = 0;
			foreach($A_list as $e=>$y){
				$A_amount+=$y['due_capital'];
				$A_num++;
			}
			foreach($B_list as $u=>$p){
				$B_amount+=$p['due_capital'];
				$B_num++;
			}
			$objPHPExcel->getActiveSheet()->setCellValue("A" . $pos, $v['username']);
			$objPHPExcel->getActiveSheet()->setCellValue("B" . $pos, $A_num);
			$objPHPExcel->getActiveSheet()->setCellValue("C" . $pos, $A_amount);
			$objPHPExcel->getActiveSheet()->setCellValue("D" . $pos, $B_num);
			$objPHPExcel->getActiveSheet()->setCellValue("E" . $pos, $B_amount);
			$pos++;


		}
		header("Content-Type: application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="(新用户)投资记录.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
}
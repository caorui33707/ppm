<!DOCTYPE html>
<html>
<head>
	<title>资金存管对账</title>
</head>
<body>

<?php
	include 'FD.php';
	include 'sign.php';
	$fd  = new FD();
//	$mobile = trim($_GET['mobile']);
	//$mobile = '18612435678';

echo "/** <br>*4.8.1.	对账文件资金进出数据    liquidateAction_fundData<br>*/<br>";

	$req = array(
		'plat_no' => 'XIB-PPM-B-06626345',
		'order_no' => 'FXWLTB20170607091618',
		'partner_trans_date' => '20170607',
		'partner_trans_time' => '091618',
		'paycheck_date' => '20170607',
		'precheck_flag' => '0',
		'begin_time' => '20170606010000',
		'end_time' => '20170607100000',
	);
	$plainText =  SignUtil::params2PlainText($req);
	$sign =  SignUtil::sign($plainText);
	$req['signdata'] = $sign;

	$data  = $fd->post('liquidateAction_fundData',$req);
	if($data){
		echo $data;
	}


	echo "<br>/** <br>*4.8.2.	对账文件账户余额数据   liquidateAction_balanceInfo<br>*/<br>";

	$req = array(
		'plat_no' => 'XIB-PPM-B-06626345',
		'order_no' => 'FXWLTB20170607091618',
		'partner_trans_date' => '20170607',
		'partner_trans_time' => '091618',
		'paycheck_date' => '20170607',
		'precheck_flag' => '1',
		'begin_time' => '20170606010000',
		'end_time' => '20170607100000',
	);
	$plainText =  SignUtil::params2PlainText($req);

	$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
	$req['signdata'] = $sign;
	$data  = $fd->post('liquidateAction_balanceInfo',$req);
	if($data){
		echo $data;
	}

//
//
//echo "<br>/** <br>*4.6.2.	资金余额查询    accountAction_getAccountInfo<br>*/<br>";
//
//$req = array(
//	'plat_no' => 'XIB-PPM-B-06626345',
//	'account' => '2017060517012229610482628',
//);
//$plainText =  SignUtil::params2PlainText($req);
//
//$sign =  SignUtil::sign($plainText);
////	echo $plainText;
////	echo "check = " . SignUtil::checkSign($plainText,$sign);
//$req['signdata'] = $sign;
//$data  = $fd->post('accountAction_getAccountInfo',$req);
//if($data){
//	echo $data;
//
//}
//
//echo "<br>/** <br>*4.6.3.	还款明细查询    proRefundAction_getRefund<br>*/<br>";
//$req = array(
//    'plat_no' => 'XIB-PPM-B-06626345',
//    'platcust' => '2017060517012229610482628',
//);
//$plainText =  SignUtil::params2PlainText($req);
//
//$sign =  SignUtil::sign($plainText);
////	echo $plainText;
////	echo "check = " . SignUtil::checkSign($plainText,$sign);
//$req['signdata'] = $sign;
//$data  = $fd->post('proRefundAction_getRefund',$req);
//if($data){
//    echo $data;
//}
//
//echo "<br>/** <br>*4.6.9	充值订单状态查询    orderQueryAction_getFundOrderInfo<br>*/<br>";
//
//$req = array(
//    'plat_no' => 'XIB-PPM-B-06626345',
//    'original_serial_no' => 'FXWLTZ20170606165041',
//    'occur_balance' => '1.00',
//);
//$plainText =  SignUtil::params2PlainText($req);
//
//$sign =  SignUtil::sign($plainText);
////	echo $plainText;
////	echo "check = " . SignUtil::checkSign($plainText,$sign);
//$req['signdata'] = $sign;
//$data  = $fd->post('orderQueryAction_getFundOrderInfo',$req);
//if($data){
//    echo $data;
//}
//
//
//
//echo "<br>/** <br>*4.6.8.	订单状态查询    orderQueryAction_queryOrder <br>*/<br>";
//
//$req = array(
//    'plat_no' => 'XIB-PPM-B-06626345',
//    'order_no' => '201706061651400791807143',
//    'partner_trans_date' => '20170606',
//    'partner_trans_time' => '142354',
//    'query_order_no' => 'FXWLTZ20170606165041',
//);
//$plainText =  SignUtil::params2PlainText($req);
//
//$sign =  SignUtil::sign($plainText);
////	echo $plainText;
////	echo "check = " . SignUtil::checkSign($plainText,$sign);
//$req['signdata'] = $sign;
//$data  = $fd->post('orderQueryAction_queryOrder',$req);
//if($data){
//    echo $data;
//}
?>

</body>
</html>


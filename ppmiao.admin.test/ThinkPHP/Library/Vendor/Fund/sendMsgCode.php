<!DOCTYPE html>
<html>
<head>
	<title>资金存管DEMO</title>
</head>
<body>

<?php
	include 'FD.php';
	include 'sign.php';
	$fd  = new FD();
//	$mobile = trim($_GET['mobile']);
	//$mobile = '18612435678';

echo "/** <br>*4.6.10	平台客户编号查询    userAction_getPlatCustInfo<br>*/<br>";

	$req = array(
		'plat_no' => 'XIB-PPM-B-06626345',
		'id_type' => '1',
		'id_code' => '330381198805252916',
		'cus_type' => '1',
	);
	$plainText =  SignUtil::params2PlainText($req);
	$sign =  SignUtil::sign($plainText);

	$data  = $fd->post('userAction_getPlatCustInfo',$req);
	if($data){
		echo $data;
	}


	echo "<br>/** <br>*4.6.6.	账户余额明细查询   accountAction_getAccountN_balace<br>*/<br>";

	$req = array(
		'plat_no' => 'XIB-PPM-B-06626345',
		'account' => '2017060517012229610482628',
		'acct_type' => '12',
		'fund_type' => '01',
	);
	$plainText =  SignUtil::params2PlainText($req);

	$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
	$req['signdata'] = $sign;
	$data  = $fd->post('accountAction_getAccountN_balace',$req);
	if($data){
		echo $data;
	}



echo "<br>/** <br>*4.6.2.	资金余额查询    accountAction_getAccountInfo<br>*/<br>";

$req = array(
	'plat_no' => 'XIB-PPM-B-06626345',
	'account' => '2017060517012229610482628',
);
$plainText =  SignUtil::params2PlainText($req);

$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
$req['signdata'] = $sign;
$data  = $fd->post('accountAction_getAccountInfo',$req);
if($data){
	echo $data;

}

echo "<br>/** <br>*4.6.3.	还款明细查询    proRefundAction_getRefund<br>*/<br>";
$req = array(
    'plat_no' => 'XIB-PPM-B-06626345',
    'platcust' => '2017060517012229610482628',
);
$plainText =  SignUtil::params2PlainText($req);

$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
$req['signdata'] = $sign;
$data  = $fd->post('proRefundAction_getRefund',$req);
if($data){
    echo $data;
}

echo "<br>/** <br>*4.6.9	充值订单状态查询    orderQueryAction_getFundOrderInfo<br>*/<br>";

$req = array(
    'plat_no' => 'XIB-PPM-B-06626345',
    'original_serial_no' => 'FXWLTZ20170606165041',
    'occur_balance' => '1.00',
);
$plainText =  SignUtil::params2PlainText($req);

$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
$req['signdata'] = $sign;
$data  = $fd->post('orderQueryAction_getFundOrderInfo',$req);
if($data){
    echo $data;
}



echo "<br>/** <br>*4.6.8.	订单状态查询    orderQueryAction_queryOrder <br>*/<br>";

$req = array(
    'plat_no' => 'XIB-PPM-B-06626345',
    'order_no' => '201706061651400791807143',
    'partner_trans_date' => '20170606',
    'partner_trans_time' => '142354',
    'query_order_no' => 'FXWLTZ20170606165041',
);
$plainText =  SignUtil::params2PlainText($req);

$sign =  SignUtil::sign($plainText);
//	echo $plainText;
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
$req['signdata'] = $sign;
$data  = $fd->post('orderQueryAction_queryOrder',$req);
if($data){
    echo $data;
}
?>

</body>
</html>


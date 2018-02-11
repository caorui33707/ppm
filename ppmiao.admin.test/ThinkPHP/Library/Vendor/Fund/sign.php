<?php

	class SignUtil {
		static public $PRIVATE_KEY_PASSWD = "cHBtaWFvMjAxNw==";
		
		static public function params2PlainText($params){
			$keys = array_keys($params);
			sort($keys);
			$plainText = "";
			foreach ($keys as $key){
				$plainText = $plainText . $params[$key] . "|";
			}
			return substr($plainText,0,-1);
		}
		
		static public function sign($plainText){
			$certs = array();
			openssl_pkcs12_read(file_get_contents(APP_PATH.'../'."ThinkPHP/Library/Vendor/Fund/dep.pfx"),$certs,self::$PRIVATE_KEY_PASSWD);//  /mnt/php/cg.admin/

			if(!$certs) return;
			$sign = "";
//			echo $plainText;
			openssl_sign($plainText,$sign,$certs['pkey']);
//			echo base64_encode($sign);
			return base64_encode($sign);
		}
		
		static public function checkSign($plainText,$sign){
			$publicKeyContent = file_get_contents("/Users/cscjj2008/PhpstormProjects/ppmiaoadmin/ThinkPHP/Library/Vendor/Fund/libifsp.cer");
			// $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($publicKeyContent,64,"\n") . "-----END CERTIFICATE-----\n";
			// $pkeyid = openssl_pkey_get_public($pem);
			return openssl_verify($plainText,base64_decode($sign),$publicKeyContent);
		}
	}
	

//	$params = array(
//		'B'=>'BB',
//		'A'=>'AA',
//		'C'=>'CC'
//	);
//	print_r($params);
//	echo("<br/>");
//	$plainText =  SignUtil::params2PlainText($params);
//	echo "plainText = " . $plainText;
//	echo("<br/>");
//	$sign =  SignUtil::sign($plainText);
//	echo "sign = " . $sign;
//	echo("<br/>");
//	echo "check = " . SignUtil::checkSign($plainText,$sign);
//		echo("<br/>");
?>
<?php

// 接口类型
define('FD', 1);



class FD {
	
// CURL 请求相关参数
	public $useragent = 'DEMO';
	public $connecttimeout = 30;
	public $timeout = 30;
	public $ssl_verifypeer = FALSE;
	
// CURL 请求状态相关数据
	public $http_header = array();
	public $http_code;
	public $http_info;
	public $url;

	
	private $REQ_URL = 'http://114.55.85.42:8082/api/';
   // private $REQ_URL = 'http://ppmiao-depository.ppmiao.cn/api/';
	
	public function __construct(){

	}
	
	/**发送短信验证码 */
	public function sendMsgCode($mobile){
		$req = array(
			'mobile'  => $mobile
			);
		
		return $this->post('smsAction_sendMsgCode', $req);
	}
	
	/** 校验短信验证码 */
	public function checkMsgCode($mobile,$code){
		$req = array(
			'mobile' => $mobile,
			'code' => $code
		);
		return $this->post('smsAction_checkMsgCode',$req);
	}
	
	public function post($methodName,$data){
//		include 'sign.php';

		$data = $this->http($this->REQ_URL.$methodName,'POST',$data);
		$re = json_decode($data);
		return $this->decode($re->resText,$re->isEnc);
//		$arr = (array)json_decode($return);
//		unset($arr['signdata']);
//		return var_dump($arr);
//		$data = $arr['data'];
//		return $data;
//		$plainText =  SignUtil::params2PlainText($data);
//		$check = SignUtil::checkSign($plainText,$arr['signdata']);
//		if($check == 1){
//			return json_encode($data);
//		}else{
//			return json_encode(['check'=>0]);
//		}

	}
	
	public function get($method){
		return $this->http($this->REQ_URL.$method);
	}
		
	public function http($url,$method,$data=null){
		$this->http_info = array();
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		$method = strtoupper($method);
//		var_dump($data);
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($data))
					curl_setopt($ci, CURLOPT_POSTFIELDS, $data);
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($data))
					$url = "{$url}?{$data}";
		}
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		return $response;
	}

	private $key = '5Df8$&@S';
	private $prefix = 'TK';


	/**
	 * DES加密
	 * @param $input
	 * @param $ky
	 * @param $iv
	 * @return string
	 */
	function encode($input, $ky, $iv) {
		$key = $ky;
		$iv = $iv;  //$iv为加解密向量
		$size = 8; //填充块的大小,单位为bite    初始向量iv的位数要和进行pading的分组块大小相等!!!
		$input = $this->pkcs5_pad($input, $size);  //对明文进行字符填充
		$td = mcrypt_module_open(MCRYPT_DES, '', 'cbc', '');    //MCRYPT_DES代表用DES算法加解密;'cbc'代表使用cbc模式进行加解密.
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);    //对$input进行加密
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);   //对加密后的密文进行base64编码
		return $data;
	}

	/**
	 * 解密
	 * @param string $str 要处理的字符串
	 * @param string $enc 是否需要解密
	 * @return string
	 */
	public function decode($str,$enc="Y") {

		if($enc == "Y"){
			$strBin = base64_decode($str);
			$str =  mcrypt_decrypt(MCRYPT_DES, $this->key, $strBin, MCRYPT_MODE_CBC, $this->key);
			$str = $this->pkcs5Unpad($str);
		}
		return $str;
	}



	/*
     * 解除填充
     */

	function pkcs5Unpad($text) {
		$pad = ord($text {strlen($text) - 1});
		if ($pad > strlen($text))
			return false;

		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
			return false;

		return substr($text, 0, - 1 * $pad);
	}

	/*
     * 对明文进行给定块大小的字符填充
     */

	function pkcs5_pad($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}


	/**
	 * 获取token
	 * @param $user
	 * @return string
	 */
	function getToken($user){
		if($user){
			$sb[] = $this->prefix;
			$sb[] = date('YmdHis',strtotime($user->add_time));
			$sb[] = $user->id;
			$sb[] = $user->salt;
			$string = implode('_',$sb);
			$res = base64_encode($string);
		}else{
			$res = '';
		}
		return $res;
	}

	/**
	 * 获取加密后的token
	 * @param $user
	 * @return string
	 */
	function getDesToken($user){
		$token = $this->getToken($user);
		return $this->encode($token,$this->key,$this->key);
	}



}

?> 
<?php

/**
 * 盛付通 批付 接口 类
 * @author bakkan 852122068@qq.com
 */



/**
 * 批复详情
 * @author bakkan 852122068@qq.com
 */
class DetailContainer implements ArrayAccess, IteratorAggregate {
	
	private $details;
	
	public function __construct($details = null) {
		if (null !== $details)
			if ($details instanceof DetailContainer)
				$this->mergeWith($details);
	}
	
	public function add($detail) {
		if ($detail instanceof ApplyInfoDetail)
			$this->details[] = $detail;
		else {
			// throw...
		}
	}
	
	public function remove($detail) {
		if (false !== ($index = array_search($detail, $this->details, true))) {
			unset($this->details[$index]);
		}
	}
	
	public function mergeWith($details) {
		array_merge($this->details, $details);
	}
	
	public function toArray() {
		$returnDetails = array();
		foreach ($this->details as $detail) {
			$returnDetails[] = $detail->toArray();
		}
		return $returnDetails;
	}
	
	public function toString() {
		$returnString = '';
		foreach ($this->details as $detail) {
			$returnString .= $detail->toString();
		}
		return $returnString;
	}
	
	public function getTotalAmount() {
		$total = 0.0;
		foreach ($this->details as $detail) {
			$total += (float)$detail->getAmount();
		}
		return number_format($total, 2, '.', ''); 
	}
	
	/** (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return $this->details[$offset];
	}

	/** (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		return $this->details[$offset];	
	}

	/** (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		$this->details[$offset] = $value;
	}

	/** (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		unset($this->details[$offset]);
	}
	
	/** (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new ArrayIterator($this->details);		
	}
}

/**
 * 单个 付款 详情 
 * @author bakkan 852122068@qq.com
 */
class ApplyInfoDetail {
	private $id = '';			// 商户流水号
	private $province = '';
	private $city = '';
	private $branchName = '';
	private $bankName = '';
	private $accountType = '';
	private $bankUserName = '';
	private $bankAccount = '';
	private $amount = '';
	private $remark = '';
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $province
	 */
	public function getProvince() {
		return $this->province;
	}

	/**
	 * @return the $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @return the $branchName
	 */
	public function getBranchName() {
		return $this->branchName;
	}

	/**
	 * @return the $bankName
	 */
	public function getBankName() {
		return $this->bankName;
	}

	/**
	 * @return the $accountType
	 */
	public function getAccountType() {
		return $this->accountType;
	}

	/**
	 * @return the $bankUserName
	 */
	public function getBankUserName() {
		return $this->bankUserName;
	}

	/**
	 * @return the $bankAccount
	 */
	public function getBankAccount() {
		return $this->bankAccount;
	}

	/**
	 * @return the $amount
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return the $remark
	 */
	public function getRemark() {
		return $this->remark;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param field_type $province
	 */
	public function setProvince($province) {
		$this->province = $province;
	}

	/**
	 * @param field_type $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * @param field_type $branchName
	 */
	public function setBranchName($branchName) {
		$this->branchName = $branchName;
	}

	/**
	 * @param field_type $bankName
	 */
	public function setBankName($bankName) {
		$this->bankName = $bankName;
	}

	/**
	 * @param field_type $accountType
	 */
	public function setAccountType($accountType) {
		if ('C' == $accountType || 'B' == $accountType)
			$this->accountType = $accountType;
		else {
			// throws ...
		}
	}

	/**
	 * @param field_type $bankUserName
	 */
	public function setBankUserName($bankUserName) {
		$this->bankUserName = $bankUserName;
	}

	/**
	 * @param field_type $bankAccount
	 */
	public function setBankAccount($bankAccount) {
		$this->bankAccount = $bankAccount;
	}

	/**
	 * @param field_type $amount
	 */
	public function setAmount($amount) {
		$this->amount = $amount;
	}

	/**
	 * @param field_type $remark
	 */
	public function setRemark($remark) {
		$this->remark = $remark;
	}
	
	public function toArray() {
		return array(
				'id' 			=> $this->getId(),
				'province' 		=> $this->getProvince(),
				'city' 			=> $this->getCity(),
				'branchName'	=> $this->getBranchName(),
				'bankName' 		=> $this->getBankName(),
				'accountType' 	=> $this->getAccountType(),
				'bankUserName' 	=> $this->getBankUserName(),
				'bankAccount' 	=> $this->getBankAccount(),
				'amount' 		=> $this->getAmount(),
				'remark' 		=> $this->getRemark()
			);
	}
	
	public function toString() {
		return $this->getId() . $this->getProvince() . $this->getCity() . $this->getBranchName() . $this->getBankName() . $this->getAccountType() . 
				$this->getBankUserName() . $this->getBankAccount() . $this->getAmount() . $this->getRemark();
	}
	
}

/**
 * 批复响应接口
 * @author bakkan 852122068@qq.com
 *
 */
class DirectApplyResponse {
	
	private $batchNo;
	private $resultCode;
	private $resultMessage;
	private $signType;
	private $sign;
	private $signKey;
	
	
	/**
	 * DirectApplyResponse constructor;
	 * @param array $response
	 */
	public function __construct($response) {
		if (!is_array($response)) {
			throw new Exception('InvalidArgumentException:$resopnse', -1);
		}
		$result = $response['return'];
		foreach ($result as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * 判断签名是否有效
	 * @return boolean
	 */
	public function isValid() {
		return $this->sign() == $this->getSign();
	}
	
	/**
	 * 签名 并 返回签名串
	 * @return string
	 */
	public function sign() {
		return strtoupper(md5($this->getStringForSign() . $this->getSignKey()));
	}
	
	/**
	 * 获取待签名字符串
	 * @return string
	 */
	public function getStringForSign() {
		return 'batchNo=' . $this->getBatchNo() . 'resultCode=' . $this->getResultCode() . 'resultMessage=' . $this->getResultMessage();
	}
	
	/**
	 * @return the $signKey
	 */
	public function getSignKey() {
		return $this->signKey;
	}
	
	/**
	 * @param field_type $signKey
	 */
	public function setSignKey($signKey) {
		$this->signKey = $signKey;
	}
	
	
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $resultCode
	 */
	public function getResultCode() {
		return $this->resultCode;
	}

	/**
	 * @return the $resultMessage
	 */
	public function getResultMessage() {
		return $this->resultMessage;
	}

	/**
	 * @return the $signType
	 */
	public function getSignType() {
		return $this->signType;
	}

	/**
	 * @return the $sign
	 */
	public function getSign() {
		return $this->sign;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $resultCode
	 */
	public function setResultCode($resultCode) {
		$this->resultCode = $resultCode;
	}

	/**
	 * @param field_type $resultMessage
	 */
	public function setResultMessage($resultMessage) {
		$this->resultMessage = $resultMessage;
	}

	/**
	 * @param field_type $signType
	 */
	public function setSignType($signType) {
		$this->signType = $signType;
	}

	/**
	 * @param field_type $sign
	 */
	public function setSign($sign) {
		$this->sign = $sign;
	}
}

/**
 * 批复请求接口
 * @author bakkan
 *
 */
class DirectApplyRequest {
	
	private $signKey = '';
	
	private $batchNo = '';
	private $callbackUrl = '';
	private $totalAmount = '';		// 总付款金额
	private $customerNo = '';		// 商户号
	private $charset = 'utf-8'; 			// 字符集
	private $signType = 'MD5';			// 签名类型
	private $sign = '';				// 签名结果
	private $remark = '';			// 备注
	private $details = null;
	
	public function __construct($details = null) {
		if (null === $details)
			$this->setDetails($details);
	}
		
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $callbackUrl
	 */
	public function getCallbackUrl() {
		return $this->callbackUrl;
	}

	/**
	 * @return the $totalAmount
	 */
	public function getTotalAmount() {
		return $this->totalAmount;
	}

	/**
	 * @return the $customerNo
	 */
	public function getCustomerNo() {
		return $this->customerNo;
	}

	/**
	 * @return the $charset
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * @return the $signType
	 */
	public function getSignType() {
		return $this->signType;
	}

	/**
	 * @return the $sign
	 */
	public function getSign() {
		return $this->sign;
	}

	/**
	 * @return the $remark
	 */
	public function getRemark() {
		return $this->remark;
	}

	/**
	 * @return the $details
	 */
	public function getDetails() {
		return $this->details;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $callbackUrl
	 */
	public function setCallbackUrl($callbackUrl) {
		$this->callbackUrl = $callbackUrl;
	}

	/**
	 * @param field_type $totalAmount
	 */
	public function setTotalAmount($totalAmount) {
		$this->totalAmount = $totalAmount;
	}

	/**
	 * @param field_type $customerNo
	 */
	public function setCustomerNo($customerNo) {
		$this->customerNo = $customerNo;
	}

	/**
	 * @param field_type $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}

	/**
	 * @param field_type $signType
	 */
	public function setSignType($signType) {
		$this->signType = $signType;
	}

	/**
	 * @param field_type $sign
	 */
	public function setSign($sign) {
		$this->sign = $sign;
	}

	/**
	 * @param field_type $remark
	 */
	public function setRemark($remark) {
		$this->remark = $remark;
	}
	
	/**
	 * 设置签名密钥
	 * @param string $key
	 * @return BatchPayment
	 */
	public function setSignKey($key) {
		$this->signKey = $key;
		return $this;
	}
	
	/**
	 * 获取签名密钥
	 * @return string
	 */
	public function getSignKey() {
		return $this->signKey;
	}
	
	/**
	 * 执行签名
	 * @return string
	 */
	public function sign() {
		return strtoupper(md5($this->getStringForSign() . $this->getSignKey()));
	}
	
	/**
	 * @param field_type $details
	 */
	public function setDetails($details) {
		if ($details instanceof DetailContainer)
			$this->details = $details;
		else {
			// throws ...
		}
	}
	
	/**
	 * 获取签名的字符串
	 * @return string
	 */
	public function getStringForSign() {
		$content = $this->getCharset() . $this->getSignType() . $this->getCustomerNo() . $this->getBatchNo() . $this->getCallbackUrl() . $this->getTotalAmount() .
						$this->getDetails()->toString();
		return $content;
	}
	
	
	public function toArray() {
		return array(
				'batchNo'		=>	$this->getBatchNo(),
				'callbackUrl'	=>	$this->getCallbackUrl(),
				'totalAmount'	=>	$this->getTotalAmount(),
				'customerNo'	=>	$this->getCustomerNo(),
				'charset'		=>	$this->getCharset(),
				'signType'		=>	$this->getSignType(),
				'sign'			=>	$this->getSign(),
				'remark'		=>	$this->getRemark(),
				'details'		=>	$this->getDetails()->toArray()
			);
	}
	
}

class BatchPayment {

	private $signKey = '';
	private $request = null;
	private $applyUrl = '';
	private $isDebug = false;
	/**
	 * constructor
	 */
	public function __construct($request) {
		$this->BatchPayment($request);
	}
	
	/**
	 * constructor
	 */
	public function BatchPayment($request) {
		$this->setRequest($request);
	}
	
	public function setRequest($request) {
		if ($request instanceof DirectApplyRequest) {
			$this->request = $request;
			$request->sign();
		} else {
			// throws ... 
		}
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function setApplyUrl($url) {
		$this->applyUrl = $url;
	}
	
	public function getApplyUrl() {
		return $this->applyUrl;
	}
	
	public function getIsDebug() {
		return $this->isDebug;
	}
	
	public function setIsDebug($d) {
		$this->isDebug = (bool)$d;
	}
	
	public function apply() {
		
		require_once __DIR__ . '/libs/nusoap/nusoap.php';
		$url = $this->getApplyUrl() ? $this->getApplyUrl() : 'http://mtc.shengpay.com/services/BatchPayment/BatchPayment?wsdl';
		$soapClient = new nusoap_client($url, 'wsdl');	// 连接 web service
		
		// 设置字符集
		$soapClient->soap_defencoding = 'utf-8';
		$soapClient->decode_utf8 = false;
		$soapClient->xml_encoding = 'utf-8';
		
		if($this->getIsDebug()) {	// -- 调试
			$err = $soapClient->getError();
			if ($err) {
				echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';return ;
			}
		}
		
		$parameter = array('arg0'=>$this->getRequest()->toArray());
		$result = $soapClient->call('directApply', $parameter);	// 调用  web service
		
		
		
		
		if($this->getIsDebug()) {	// -- 调试
			if ($soapClient->fault) {
				echo '<h2>Fault</h2><pre>';
				print_r($result);
				echo '</pre>';
				return ;
			} else {
				// Check for errors
				$err = $soapClient->getError();
				if ($err) {
					// Display the error
					echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
					// Display the result
					echo '<h2>Result</h2><pre>';
					print_r($result);
					echo '</pre>';
				}
			}
			echo '<h2>Request</h2><pre>' . htmlspecialchars($soapClient->request, ENT_QUOTES) . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($soapClient->debug_str, ENT_QUOTES) . '</pre>';
		}
		
		return $result;
	}
}

/**
 * 批复回调
 * @author bakkan 852122068@qq.com
 *
 */
class CallbackResult {
	
	private $batchNo = '';
	private $statusCode = '';
	private $statusName = '';
	private $fileName = '';
	private $charset = '';
	private $signType = '';
	private $sign = '';
	private $resultCode = '';
	private $resultName = '';
	private $resultMemo = '';
	private $signKey = '';
	
	/**
	 * CallbackResult constructor
	 */
	public function __construct() {
		$this->setBatchNo($_POST['batchNo']);
		$this->setStatusCode($_POST['statusCode']);
		$this->setStatusName($_POST['statusName']);
		$this->setFileName($_POST['fileName']);
		$this->setCharset($_POST['charset']);
		$this->setSignType($_POST['signType']);
		$this->setSign($_POST['sign']);
		$this->setResultCode($_POST['resultCode']);
		$this->setResultName($_POST['resultName']);
		$this->setResultMemo($_POST['resultMemo']);
	}
	
	/**
	 * 生成结果
	 * @param array $result
	 * @return string
	 */
	public function generateResult(array $result = array('resultCode' => 'ok', 'resultMessage' => 'ok')) {
		return '<result><sign>' . $this->getSign() . '</sign><signType>' . $this->getSignType() . 
				'</signType><resultCode>' . $result['resultCode'] . '</resultCode><resultMessage>' . $result['resultMessage'] . '</resultMessage></result>';
	}
	
	/**
	 * 签名
	 * @return string
	 */
	public function sign() {
		return strtoupper(md5($this->getStringForSign() . $this->getSignKey()));
	}
	
	/**
	 * 判断签名是否有效
	 * @return boolean
	 */
	public function isValid() {
		return $this->sign() == $this->getSign();
	}
	
	/**
	 * 获取待签名的字符串
	 * @return string
	 */
	public function getStringForSign() {
		return ($this->getCharset() ? 'charset=' . $this->getCharset() : '') . 
				($this->getBatchNo() ? 'batchNo=' . $this->getBatchNo() : '') . 
				($this->getStatusCode() ? 'statusCode=' . $this->getStatusCode() : '') . 
				($this->getStatusName() ? 'statusName=' . $this->getStatusName() : '') . 
				($this->getFileName() ? 'fileName=' . $this->getFileName() : '') . 
				($this->getResultCode() ? 'resultCode=' . $this->getResultCode() : '') . 
				($this->getResultName() ? 'resultName=' . $this->getResultName() : '') . 
				($this->getResultMemo() ? 'resultMemo=' . $this->getResultMemo() : '');
	}
	
	/**
	 * 设置 密钥
	 * @param string $key
	 */
	public function setSignKey($key) {
		$this->signKey = $key;
	}
	
	/**
	 * 获取 密钥
	 * @return string
	 */
	public function getSignKey() {
		return $this->signKey;
	}

	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $statusCode
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * @return the $statusName
	 */
	public function getStatusName() {
		return $this->statusName;
	}

	/**
	 * @return the $fileName
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * @return the $charset
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * @return the $signType
	 */
	public function getSignType() {
		return $this->signType;
	}

	/**
	 * @return the $sign
	 */
	public function getSign() {
		return $this->sign;
	}

	/**
	 * @return the $resultCode
	 */
	public function getResultCode() {
		return $this->resultCode;
	}

	/**
	 * @return the $resultName
	 */
	public function getResultName() {
		return $this->resultName;
	}

	/**
	 * @return the $resultMemo
	 */
	public function getResultMemo() {
		return $this->resultMemo;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $statusCode
	 */
	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}

	/**
	 * @param field_type $statusName
	 */
	public function setStatusName($statusName) {
		$this->statusName = $statusName;
	}

	/**
	 * @param field_type $fileName
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	/**
	 * @param field_type $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}

	/**
	 * @param field_type $signType
	 */
	public function setSignType($signType) {
		$this->signType = $signType;
	}

	/**
	 * @param field_type $sign
	 */
	public function setSign($sign) {
		$this->sign = $sign;
	}

	/**
	 * @param field_type $resultCode
	 */
	public function setResultCode($resultCode) {
		$this->resultCode = $resultCode;
	}

	/**
	 * @param field_type $resultName
	 */
	public function setResultName($resultName) {
		$this->resultName = $resultName;
	}

	/**
	 * @param field_type $resultMemo
	 */
	public function setResultMemo($resultMemo) {
		$this->resultMemo = $resultMemo;
	}
}

/**
 * 批复查询
 * @author bakkan 852122068@qq.com
 *
 */
class QueryRequest {
	
	private $batchNo = '';
	private $detailId = '';
	private $customerNo = '';
	private $charset = 'utf-8';
	private $signType = 'MD5';
	private $sign = '';
	private $remark = '';
	private $signKey = '';
	private $queryUrl = '';
	public $isDebug = false;


	

	/**
	 * @return the $isDebug
	 */
	public function getIsDebug() {
		return $this->isDebug;
	}

	/**
	 * @param boolean $isDebug
	 */
	public function setIsDebug($isDebug) {
		$this->isDebug = $isDebug;
	}
	

	/**
	 * QueryRequest constructor
	 */
	public function __construct($init = null) {
		if ($init !== null && is_array($init))
			foreach ($init as $key => $value)
				$this->$key = $value;
	}
	
	/**
	 * 
	 * @return Ambigous <mixed, boolean, string, unknown>
	 */
	public function query() {
		require_once __DIR__ . '/libs/nusoap/nusoap.php';
		$url = $this->getQueryUrl() ? $this->getQueryUrl() : 'http://mtc.shengpay.com/services/BatchPayment/BatchPayment?wsdl';
		$soapClient = new nusoap_client($url, 'wsdl');	// 连接 web service

		// 设置字符集
		$soapClient->soap_defencoding = 'utf-8';
		$soapClient->decode_utf8 = false;
		$soapClient->xml_encoding = 'utf-8';
		
		
		if($this->getIsDebug()) {	// -- 调试
			$err = $soapClient->getError();
			if ($err) {
				echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';return ;
			}
		}
		
		
		$parameter = array('arg0'=>$this->toArray());
		$result = $soapClient->call('Query', $parameter);	// 调用  web service
		
		
		if($this->getIsDebug()) {	// -- 调试
			if ($soapClient->fault) {
				echo '<h2>Fault</h2><pre>';
				print_r($result);
				echo '</pre>';
				return ;
			} else {
				// Check for errors
				$err = $soapClient->getError();
				if ($err) {
					// Display the error
					echo '<h2>Error</h2><pre>' . $err . '</pre>';
				} else {
					// Display the result
					echo '<h2>Result</h2><pre>';
					print_r($result);
					echo '</pre>';
				}
			}
			echo '<h2>Request</h2><pre>' . htmlspecialchars($soapClient->request, ENT_QUOTES) . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($soapClient->debug_str, ENT_QUOTES) . '</pre>';
		}
		
		
		return $result;
	}
	
	public function toArray() {
		return array(
				'batchNo'	=>	$this->getBatchNo(),
				'detailId'	=>	$this->getDetailId(),
				'customerNo'=>	$this->getCustomerNo(),
				'charset'	=>	$this->getCharset(),
				'signType'	=>	$this->getSignType(),
				'sign'		=>	$this->getSign(),
				'remark'	=>	$this->getRemark()
			);
	}
	
	public function sign() {
		return strtoupper(md5($this->getStringForSign() . $this->getSignKey()));
	}
	
	public function getStringForSign() {
		return $this->getCharset() . $this->getSignType() . $this->getCustomerNo() . $this->getBatchNo() . $this->getDetailId();
	}
	
	/**
	 * @return the $queryUrl
	 */
	public function getQueryUrl() {
		return $this->queryUrl;
	}
	
	/**
	 * @param field_type $queryUrl
	 */
	public function setQueryUrl($queryUrl) {
		$this->queryUrl = $queryUrl;
	}
	
	/**
	 * @return the $signKey
	 */
	public function getSignKey() {
		return $this->signKey;
	}
	
	/**
	 * @param field_type $signKey
	 */
	public function setSignKey($signKey) {
		$this->signKey = $signKey;
	}
	
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $detailId
	 */
	public function getDetailId() {
		return $this->detailId;
	}

	/**
	 * @return the $customerNo
	 */
	public function getCustomerNo() {
		return $this->customerNo;
	}

	/**
	 * @return the $charset
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * @return the $signType
	 */
	public function getSignType() {
		return $this->signType;
	}

	/**
	 * @return the $sign
	 */
	public function getSign() {
		return $this->sign;
	}

	/**
	 * @return the $remark
	 */
	public function getRemark() {
		return $this->remark;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $detailId
	 */
	public function setDetailId($detailId) {
		$this->detailId = $detailId;
	}

	/**
	 * @param field_type $customerNo
	 */
	public function setCustomerNo($customerNo) {
		$this->customerNo = $customerNo;
	}

	/**
	 * @param field_type $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}

	/**
	 * @param field_type $signType
	 */
	public function setSignType($signType) {
		$this->signType = $signType;
	}

	/**
	 * @param field_type $sign
	 */
	public function setSign($sign) {
		$this->sign = $sign;
	}

	/**
	 * @param field_type $remark
	 */
	public function setRemark($remark) {
		$this->remark = $remark;
	}
}

class QueryResponse {
	
	private $batchNo = '';
	private $detailId = '';
	private $resultInfo = '';
	private $resultCode = '';
	private $resultMessage = '';
	private $signType = '';
	private $sign = '';
	private $signKey = '';
	
	/**
	 * QueryResponse constructor
	 */
	public function __construct($res) {
		$result = $res['return'];
		foreach ($result as $key => $value) {
			$this->{'set' . $key}($value);
		}
	}
	
	public function isValid() {
		return $this->sign() == $this->getSign();
	}
	
	public function sign() {
		return strtoupper(md5($this->getStringForSign() . $this->getSignKey()));
	}
	
	
	public function getStringForSign() {
		return ($this->getBatchNo() != '' ? 'batchNo=' . $this->getBatchNo() : '') . 
				($this->getDetailId() != ''  ? 'detailId=' . $this->getDetailId() : '') . 
				($this->getResultCode() != ''  ? 'resultCode=' . $this->getResultCode() : '') .
				($this->getResultMessage() != ''  ? 'resultMessage=' . $this->getResultMessage() : '');
	}
	
	/**
	 * @return the $signKey
	 */
	public function getSignKey() {
		return $this->signKey;
	}
	
	/**
	 * @param field_type $signKey
	 */
	public function setSignKey($signKey) {
		$this->signKey = $signKey;
	}
	
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $detailId
	 */
	public function getDetailId() {
		return $this->detailId;
	}

	/**
	 * @return the $resultInfo
	 */
	public function getResultInfo() {
		return $this->resultInfo;
	}

	/**
	 * @return the $resultCode
	 */
	public function getResultCode() {
		return $this->resultCode;
	}

	/**
	 * @return the $resultMessage
	 */
	public function getResultMessage() {
		return $this->resultMessage;
	}

	/**
	 * @return the $signType
	 */
	public function getSignType() {
		return $this->signType;
	}

	/**
	 * @return the $sign
	 */
	public function getSign() {
		return $this->sign;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $detailId
	 */
	public function setDetailId($detailId) {
		$this->detailId = $detailId;
	}

	/**
	 * @param field_type $resultInfo
	 */
	public function setResultInfo($resultInfo) {
		$this->resultInfo = new ResultInfo($resultInfo);
	}

	/**
	 * @param field_type $resultCode
	 */
	public function setResultCode($resultCode) {
		$this->resultCode = $resultCode;
	}

	/**
	 * @param field_type $resultMessage
	 */
	public function setResultMessage($resultMessage) {
		$this->resultMessage = $resultMessage;
	}

	/**
	 * @param field_type $signType
	 */
	public function setSignType($signType) {
		$this->signType = $signType;
	}

	/**
	 * @param field_type $sign
	 */
	public function setSign($sign) {
		$this->sign = $sign;
	}

} 

class ResultInfo {
	
	private $batchNo;
	private $customerNo;
	private $payStatus;
	private $totalCount;
	private $totalAmount;
	private $completeCount;
	private $completeAmount;
	private $batchRemark;
	private $details;
	private $isDetailsList = false;
	
	public static $statusMap = array(
			'初始状态'			=>	'00',
			'付款中'				=>	'01',
			'部分付款成功'		=>	'02',
			'全部付款成功'		=>	'03',
			'全部支付失败'		=>	'04',
			'支付结果未知'		=>	'05',
			'批次校验中'			=>	'06',
			'批次校验成功'		=>	'07',
			'批次校验失败'		=>	'08'
		);

	
	/**
	 * ResultInfo constructor
	 */
	public function __construct($init = null) {
		if (null !== $init && is_array($init))
			foreach ($init as $key => $value)
				$this->{'set' . $key}($value);
	}
	
	public static function getStatusCodeByDesc($desc) {
		return self::$statusMap[$desc];
	}
	
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $customerNo
	 */
	public function getCustomerNo() {
		return $this->customerNo;
	}

	/**
	 * @return the $payStatus
	 */
	public function getPayStatus() {
		return $this->payStatus;
	}

	/**
	 * @return the $totalCount
	 */
	public function getTotalCount() {
		return $this->totalCount;
	}

	/**
	 * @return the $totalAmount
	 */
	public function getTotalAmount() {
		return $this->totalAmount;
	}

	/**
	 * @return the $completeCount
	 */
	public function getCompleteCount() {
		return $this->completeCount;
	}

	/**
	 * @return the $completeAmount
	 */
	public function getCompleteAmount() {
		return $this->completeAmount;
	}

	/**
	 * @return the $batchRemark
	 */
	public function getBatchRemark() {
		return $this->batchRemark;
	}

	/**
	 * @return the $details
	 */
	public function getDetails() {
		return $this->details;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $customerNo
	 */
	public function setCustomerNo($customerNo) {
		$this->customerNo = $customerNo;
	}

	/**
	 * @param field_type $payStatus
	 */
	public function setPayStatus($payStatus) {
		$this->payStatus = $payStatus;
	}

	/**
	 * @param field_type $totalCount
	 */
	public function setTotalCount($totalCount) {
		$this->totalCount = $totalCount;
	}

	/**
	 * @param field_type $totalAmount
	 */
	public function setTotalAmount($totalAmount) {
		$this->totalAmount = $totalAmount;
	}

	/**
	 * @param field_type $completeCount
	 */
	public function setCompleteCount($completeCount) {
		$this->completeCount = $completeCount;
	}

	/**
	 * @param field_type $completeAmount
	 */
	public function setCompleteAmount($completeAmount) {
		$this->completeAmount = $completeAmount;
	}

	/**
	 * @param field_type $batchRemark
	 */
	public function setBatchRemark($batchRemark) {
		$this->batchRemark = $batchRemark;
	}

	/**
	 * @param field_type $details
	 */
	public function setDetails($details) {
		// $this->details = new ResultInfoDetail($details);
		foreach ($details as $key => $value)
			if (is_array($value)) {
				$this->details[$key] = new ResultInfoDetail($value);
			} else {
				$this->details[] = new ResultInfoDetail($details);
				break;
			}
	}
	
}

class ResultInfoDetail {
	private $batchNo = '';
	private $customerNo = '';
	private $id = '';
	private $province = '';
	private $city = '';
	private $branchName = '';
	private $bankName = '';
	private $accountType = '';
	private $bankUserName = '';
	private $bankAccount = '';
	private $amount = '';
	private $remark = '';
	private $orderNo = '';
	private $payStatus = '';
	private $resultRemark = '';
	public static $statusMap = array (
			'初始状态'	=>	'00',
			'付款中'		=>	'01',
			'付款成功'	=>	'03',
			'付款失败'	=>	'04',
			'未知'		=>	'05',
			'已退票'		=>	'06'
		);
	
	public function __construct($init = null) {
		if (null !== $init && is_array($init))
			foreach ($init as $key => $value)
				$this->{'set' . $key}($value);
	}
	
	public static function  getStatusCodeByDesc($desc) {
		return self::$statusMap[$desc];
	}
	
	/**
	 * @return the $batchNo
	 */
	public function getBatchNo() {
		return $this->batchNo;
	}

	/**
	 * @return the $customerNo
	 */
	public function getCustomerNo() {
		return $this->customerNo;
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $province
	 */
	public function getProvince() {
		return $this->province;
	}

	/**
	 * @return the $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @return the $branchName
	 */
	public function getBranchName() {
		return $this->branchName;
	}

	/**
	 * @return the $bankName
	 */
	public function getBankName() {
		return $this->bankName;
	}

	/**
	 * @return the $accountType
	 */
	public function getAccountType() {
		return $this->accountType;
	}

	/**
	 * @return the $bankUserName
	 */
	public function getBankUserName() {
		return $this->bankUserName;
	}

	/**
	 * @return the $bankAccount
	 */
	public function getBankAccount() {
		return $this->bankAccount;
	}

	/**
	 * @return the $amount
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return the $remark
	 */
	public function getRemark() {
		return $this->remark;
	}

	/**
	 * @return the $orderNo
	 */
	public function getOrderNo() {
		return $this->orderNo;
	}

	/**
	 * @return the $payStatus
	 */
	public function getPayStatus() {
		return $this->payStatus;
	}

	/**
	 * @return the $resultRemark
	 */
	public function getResultRemark() {
		return $this->resultRemark;
	}

	/**
	 * @param field_type $batchNo
	 */
	public function setBatchNo($batchNo) {
		$this->batchNo = $batchNo;
	}

	/**
	 * @param field_type $customerNo
	 */
	public function setCustomerNo($customerNo) {
		$this->customerNo = $customerNo;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param field_type $province
	 */
	public function setProvince($province) {
		$this->province = $province;
	}

	/**
	 * @param field_type $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * @param field_type $branchName
	 */
	public function setBranchName($branchName) {
		$this->branchName = $branchName;
	}

	/**
	 * @param field_type $bankName
	 */
	public function setBankName($bankName) {
		$this->bankName = $bankName;
	}

	/**
	 * @param field_type $accountType
	 */
	public function setAccountType($accountType) {
		$this->accountType = $accountType;
	}

	/**
	 * @param field_type $bankUserName
	 */
	public function setBankUserName($bankUserName) {
		$this->bankUserName = $bankUserName;
	}

	/**
	 * @param field_type $bankAccount
	 */
	public function setBankAccount($bankAccount) {
		$this->bankAccount = $bankAccount;
	}

	/**
	 * @param field_type $amount
	 */
	public function setAmount($amount) {
		$this->amount = $amount;
	}

	/**
	 * @param field_type $remark
	 */
	public function setRemark($remark) {
		$this->remark = $remark;
	}

	/**
	 * @param field_type $orderNo
	 */
	public function setOrderNo($orderNo) {
		$this->orderNo = $orderNo;
	}

	/**
	 * @param field_type $payStatus
	 */
	public function setPayStatus($payStatus) {
		$this->payStatus = $payStatus;
	}

	/**
	 * @param field_type $resultRemark
	 */
	public function setResultRemark($resultRemark) {
		$this->resultRemark = $resultRemark;
	}

}


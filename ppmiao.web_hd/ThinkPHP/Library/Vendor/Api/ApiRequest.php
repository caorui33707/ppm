<?php
/**
 * Created by PhpStorm.
 * User: ChenJunJie
 * Date: 18/1/15
 * Time: 下午3:55
 */


class ApiRequest
{

    private $url_domain = 'http://api.test.ppmiao.com/';
    private $key = '5Df8$&@S';
    private $prefix = 'TK';

    private $url_map = [
        'RedEnvelope' => 'user/getAllUserRedEnvelope.json',
        'UserInfo' => 'user/userInfo.json',
        'InvestingRecord' => 'user/queryInvestDetailV2.json',//获取红包
        'InvestFinishRecord' => 'user/queryInvestFinishDetailV2.json',
        'InvestFailedRecord' => 'user/getInvestFailedRecord.json',
        'getAllBindCardInfo' => 'user/getAllBindCardInfo.json',//绑定银行卡明细
        'walletDetail' => 'user/walletDetail.json',//提现充值明细
        'CashCoupons' => 'user/getAllUserInviteCashCoupons.json',//获取现金券
        'InterestCoupon' => 'user/getAllUserInterestCoupon.json',//获取加息券
        'allWaitInterestDetail' => 'user/allWaitInterestDetail.json',//待收利息
        'myWallet' => 'user/myWallet.json',//余额
        'userAccountAssets' => 'user/userAccountAssets.json',//账户信息
        'allInterestDetail' => 'user/allInterestDetail.json',//已收利息
        'dailySign' => 'ppmiao-coin/dailySign',//用户签到
        'ProjectList' => 'project/queryInProgressProjectV8.json',//产品列表
        'BindCard' => 'user/queryBindBankCard.json',//绑卡明细
        'preRecharge' => 'user/preRecharge.json',//充值界面
        'preWithdrawal' => 'user/preWithdrawal.json',//提现界面
        'tradeSmsCode' => 'user/getTradeSmsCode.json',//获取验证码，充值、提现等使用 存管
        'recharge' => 'user/recharge.json',//充值
        'withdraw' => 'user/withdrawal.json',//提现
        'tradeDetail' => 'user/tradeDetail.json',//交易明细
        'accountBalace' => 'user/getAccountBalace.json',//账户余额
        'SmsCode' => 'user/getSmsCode.json',//短信
        'UserLogin' =>'user/login.json',//登录
        'dueDetail' =>'user/dueDetail.json',//投资详情
        'cashCouponToWallet' =>'project/CashCouponToWallet.json',//使用现金券
        'projectInvestLog' =>'user/projectInvestLog.json',//使用现
        'CouponsForInvest' => 'user/getCouponsForInvest.json',//符合的红包、加息券
        'bankList' => 'message/getBankList.json',//银行列表
        'bindCard' => 'user/bindCard.json',//用户绑卡申请 存管
        'bindCardConfirm' => 'user/bindCardConfirm.json',//用户绑卡确认 存管
        'invest'=>'project/investV2.json',
        'getNewProjects' =>'activity/getNewProjects.json',//新手标签列表
    ];

    public function post($function,array $param){

        $postData = [
            'token'=>$param['token'],
            'versionName' => '0.1',
            'deviceType' => 5,
            'channel' => 'weixin',
            'registration_id' => '0',
            'device_serial_id' => '0',
        ];


        unset($param['token']);
        if(!empty($param)){
            $postData = array_merge($postData, $param);
        }


//        print_r($data);
        $url = $this->url_domain . $this->url_map[$function];
        $re = $this->curlPost($url, $postData);
        $re = json_decode($re);

        $res = $this->decode($re->resText,$re->isEnc);
        $data = json_decode($res);

//        var_dump($data);
        return $data;
    }

    
    public function httpPost($function,array $postData){
        $url = $this->url_domain . $this->url_map[$function];
        $re = $this->curlPost($url, $postData);
        $re = json_decode($re);
    
        $res = $this->decode($re->resText,$re->isEnc);
        $data = json_decode($res);
    
        //        var_dump($data);
        return $data;
    }


    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
    public function curlPost($url, $postFields)
    {

//        var_dump($postFields);
        $postFields = http_build_query($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }



    /**
     * DES加密
     * @param $input
     * @param $ky
     * @param $iv
     * @return string
     */
    function encode($input, $ky, $iv)
    {
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
    public function decode($str, $enc = "Y")
    {

        if ($enc == "Y") {
            $strBin = base64_decode($str);
            $str = mcrypt_decrypt(MCRYPT_DES, $this->key, $strBin, MCRYPT_MODE_CBC, $this->key);
            $str = $this->pkcs5Unpad($str);
        }
        return $str;
    }


    /*
     * 解除填充
     */

    function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;

        return substr($text, 0, -1 * $pad);
    }

    /*
     * 对明文进行给定块大小的字符填充
     */

    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    /**
     * 获取token
     * @param $user
     * @return string
     */
    function getToken($user)
    {
        if ($user) {
            $sb[] = $this->prefix;
            $sb[] = date('YmdHis', strtotime($user['add_time']));
            $sb[] = $user['id'];
            $sb[] = $user['salt'];
            $string = implode('_', $sb);
            $res = base64_encode($string);
        } else {
            $res = '';
        }
        return $res;
    }

}
<?php
namespace User\Controller;
use Think\Controller;

class IndexController extends BaseController {

    private $base;

    protected $api;

    public function __construct()
    {

        parent::__construct();
        vendor('Api.ApiRequest');

        $user = session(USER_ONLINE_SESSION);

        if(!$user) {
            redirect(C('WEB_ROOT').'/user/login.html');
        }


        $this->api = new \ApiRequest();
        $this->base = [
            'token'=>$user['token']
        ];


        if(!$user['couponCount']){


            $RedEnvelope = $this->api->post('RedEnvelope',$this->base);
            $CashCoupons = $this->api->post('CashCoupons',$this->base);
            $InterestCoupon = $this->api->post('InterestCoupon',$this->base);

            $info['RedEnvelopeCount'] = count($RedEnvelope->result[0]->couponList);
            $info['CashCouponsCount'] = count($CashCoupons->result[0]->couponList);
            $info['InterestCouponCount'] = count($InterestCoupon->result[0]->couponList);

            $couponCount = $info['RedEnvelopeCount']+$info['CashCouponsCount']+$info['InterestCouponCount'];




            $user['couponCount'] = '<span> '.$couponCount .'</span>';
            session(USER_ONLINE_SESSION,$user);
        }
    }







    public function index(){

        $response = $this->api->post('userAccountAssets',$this->base);

        $bank = $this->api->post('BindCard',$this->base);

        if($bank->result->bankCardNo){
            $bankInfo = $bank->result->bankName.$bank->result->supportCardType." （尾号".substr($bank->result->bankCardNo, -4)."）";
        }else{
            $bankInfo = '未绑定';
        }
        $userInfo = (array)$response->result;
        $session = session(USER_ONLINE_SESSION);
        $user = M('user')->where(array('id'=>$session['user_id']))->find();
        $user['card_no'] = hide_whole_idcard($user['card_no']);
        $user['real_name'] = $this->name_star($user['real_name']);
        $user['username'] = hide_whole_phone($user['username']);
        $this->assign('bankInfo',$bankInfo);
        $this->assign('userInfo',$userInfo);
        $this->assign('user',$user);
        $this->assign('date',getTime());
        $info['menu'] = 'index';
        $this->assign('info',$info);
        $this->display();

    }



    public function recharge(){
        $this->checkBind();
        $response = $this->api->post('preRecharge',$this->base);
        $wallet = $this->api->post('userAccountAssets',$this->base);
//        var_dump($response);
//
//        var_dump($wallet);


        //自定义数据
        $info['card_no'] = get_card_tail_number_star($response->result->bankCardNo);
        $info['mobile'] = hide_whole_phone($response->result->mobile);

        $info['menu'] = 'index';

        $this->assign('info',$info);
        $this->assign('data',$response->result);
        $this->assign('wallet',$wallet->result);
        $this->display();

    }


    public function recharge_store(){

        $msgCode = trim(I('post.msgCode', '', 'strip_tags'));
        $amount = trim(I('post.amount', '', 'strip_tags'));
        $data = $this->base;
        $data['msgCode'] = $msgCode;
        $data['amount'] = $amount;
        $response = $this->api->post('recharge',$data);

        echo json_encode($response);

    }

    public function recharge_success(){

        $this->checkBind();
        $info['menu'] = 'index';
        $info['ref'] = I('get.ref',C('WEB_ROOT').'/index.php/user/index/account');
        $this->assign('info',$info);
        $this->display();
    }


    public function bank(){

        $user_info = $this->api->post('UserInfo',$this->base);

        if($user_info->result->platcustAuth == 0){
            redirect(C('WEB_ROOT').'/index.php/user/index/bindCard');
        }
        $response = $this->api->post('preRecharge',$this->base);
        $response = $response->result;
        $info['card_no'] = get_card_tail_number_star($response->bankCardNo);

        $info['limitTimes'] = change_money($response->limitTimes);
        $info['limitDay'] = change_money($response->limitDay);
        $info['limitMonth'] = change_money($response->limitMonth);
        $info['menu'] = 'bank';
//        var_dump($info);
        $this->assign('user',$user_info);
        $this->assign('data',$response);
        $this->assign('info',$info);
        $this->display();
    }


    public function withdraw(){

        $this->checkBind();
        $response = $this->api->post('preWithdrawal',$this->base);

        //自定义数据
        $info['card_no'] = get_card_tail_number_star($response->result->bankCardNo);

        $info['menu'] = 'index';

        $this->assign('info',$info);
        $this->assign('data',$response->result);
        $this->display();

    }


    public function withdraw_store(){

        $msgCode = trim(I('post.msgCode', '', 'strip_tags'));
        $amount = trim(I('post.amount', '', 'strip_tags'));
        $data = $this->base;
        $data['msgCode'] = $msgCode;
        $data['amount'] = $amount;
        $response = $this->api->post('withdraw',$data);

//        var_dump($response);
        echo json_encode($response);

    }

    public function withdraw_success(){
        $this->checkBind();
        $info['menu'] = 'index';

        $this->assign('info',$info);
        $this->display();
    }


    public function getAllTrade($type){
        $res_data = [];
        for ($i =1;$i<=10;$i++){
            $data = $this->base;
            $data['type'] = $type;
            $data['pageNo'] = $i;
            $response = $this->api->post('tradeDetail',$data);

            if(empty($response->result)){
                break;
            }else{
                $res_data[] = $response->result;
            }


        }
        return $res_data;
    }


    public function account(){
        $info['menu'] = 'account';
        $info['type']=[
            1=>'充值',2=>'提现',3=>'购买定期',4=>'到期还款',5=>'现金奖励'
        ];

        $info['pay_status']=[
            1=>'打款中',2=>'交易成功',3=>'支付失败'
        ];
        $info['status']=[
            1=>'交易成功',2=>'交易中',3=>'等待处理'
        ];


        $in = $this->getAllTrade(1);
        $out = $this->getAllTrade(2);
        $accountBalace = $this->api->post('accountBalace',$this->base);

        $this->assign('info',$info);
        $this->assign('in',$in);
        $this->assign('out',$out);
        $this->assign('accountBalace',$accountBalace);
        $this->display();
    }


    public function dueDetail(){

        $info['menu'] = 'dueDetail';


        $response = $this->api->post('userAccountAssets',$this->base);

        $InvestingRecord = $this->api->post('InvestingRecord',$this->base);
        $InvestFinishRecord = $this->api->post('InvestFinishRecord',$this->base);
//        $InvestFailedRecord = $this->api->post('InvestFailedRecord',$this->base);
//        print_r($InvestingRecord);
//        print_r($InvestFinishRecord);


        $this->assign('info',$info);
        $this->assign('response',$response->result);
        $this->assign('InvestingRecord',$InvestingRecord->result);
        $this->assign('InvestFinishRecord',$InvestFinishRecord->result);
        $this->display();

    }

    public function dueInfo(){
        $id = 87164;
        $data = $this->base;
        $data['id'] = $id;
        $response = $this->api->post('dueDetail',$data);
        var_dump($response);
//        $this->assign('response',$response->result);
//        $this->display();


    }


    public function checkBind(){
        $user_info = $this->api->post('UserInfo',$this->base);

        if($user_info->result->realNameAuth == 0){
            redirect(C('WEB_ROOT').'/index.php/user/index/bindCard');
        }elseif ($user_info->result->realNameAuth == 1 && $user_info->result->platcustAuth == 2){
            redirect(C('WEB_ROOT').'/index.php/user/index/bank');
        }


    }

    public function ajaxCheckBind(){
        $user_info = $this->api->post('UserInfo',$this->base);
        if ($user_info->result->realNameAuth == 0){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function coupon(){


        $info['menu'] = 'coupon';
        $RedEnvelope = $this->api->post('RedEnvelope',$this->base);
        $CashCoupons = $this->api->post('CashCoupons',$this->base);
        $InterestCoupon = $this->api->post('InterestCoupon',$this->base);

        $info['RedEnvelopeCount'] = count($RedEnvelope->result[0]->couponList);
        $info['CashCouponsCount'] = count($CashCoupons->result[0]->couponList);
        $info['InterestCouponCount'] = count($InterestCoupon->result[0]->couponList);

        $couponCount = $info['RedEnvelopeCount']+$info['CashCouponsCount']+$info['InterestCouponCount'];





        $user = session(USER_ONLINE_SESSION);
        $user['couponCount'] = '<span> '.$couponCount .'</span>';
        session(USER_ONLINE_SESSION,$user);

//        var_dump($RedEnvelope);
//        var_dump($CashCoupons);
//        var_dump($InterestCoupon);


        $this->assign('info',$info);
        $this->assign('RedEnvelope',$RedEnvelope);
        $this->assign('CashCoupons',$CashCoupons);
        $this->assign('InterestCoupon',$InterestCoupon);
        $this->display();


    }

    public function bindCard()
    {

        $user_info = $this->api->post('UserInfo',$this->base);


        if($user_info->result->platcustAuth == 1){
            redirect(C('WEB_ROOT').'/index.php/user/index');
        }

        $bankList = $this->api->post('bankList',$this->base);
        $this->assign('bankList',$bankList);
        $this->display();
    }


    public function bindCardRequest(){
        $realName = trim(I('post.realName', '', 'strip_tags'));
        $bankCode = trim(I('post.bankCode', '', 'strip_tags'));
        $bankNo = trim(I('post.bankCardNo', '', 'strip_tags'));
        $cardNo = trim(I('post.cardNo', '', 'strip_tags'));
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $data = $this->base;
        $data['realName'] = $realName;
        $data['bankCode'] = $bankCode;
        $data['bankCardNo'] = $bankNo;
        $data['cardNo'] = $cardNo;
        $data['mobile'] = $mobile;
//        var_dump($data);
        $response = $this->api->post('bindCard',$data);

        echo json_encode($response);
    }

    public function bindCardConfirm(){

        $realName = trim(I('post.realName', '', 'strip_tags'));
        $bankCode = trim(I('post.bankCode', '', 'strip_tags'));
        $bankNo = trim(I('post.bankCardNo', '', 'strip_tags'));
        $cardNo = trim(I('post.cardNo', '', 'strip_tags'));
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $msgCode = trim(I('post.msgCode', '', 'strip_tags'));
        $originOrderNo = trim(I('post.originOrderNo', '', 'strip_tags'));
        $data = $this->base;
        $data['realName'] = $realName;
        $data['bankCode'] = $bankCode;
        $data['bankCardNo'] = $bankNo;
        $data['cardNo'] = $cardNo;
        $data['mobile'] = $mobile;
        $data['msgCode'] = $msgCode;
        $data['originOrderNo'] = $originOrderNo;
        $response = $this->api->post('bindCardConfirm',$data);

        echo json_encode($response);
    }

    public function bindCard_success(){
        $this->display();
    }

    public function cashCouponToWallet(){

        $id = trim(I('post.id', '', 'strip_tags'));
        $data = $this->base;
        $data['cashCouponId'] = $id;

        $cashCouponToWallet = $this->api->post('cashCouponToWallet',$data);
        echo json_encode($cashCouponToWallet);


    }


    public function getMoneyInfo(){
        $wallet = $this->api->post('allWaitInterestDetail',$this->base);


        print_r($wallet);
    }



    public function getSmsCode(){
        $code = $this->api->post('tradeSmsCode',$this->base);
        echo json_encode($code);
    }

    public function name_star($str){
        if(preg_match("/[\x{4e00}-\x{9fa5}]+/u", $str)) {
            //按照中文字符计算长度
            $len = mb_strlen($str, 'UTF-8');
            //echo '中文';
            if($len >= 3){
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $str = mb_substr($str, 0, 1, 'UTF-8') . '*' .mb_substr($str, -1);
            } elseif($len == 2) {
                //两个字符
                $str = '*' . mb_substr($str,-1);
            }
        } else {
            //按照英文字串计算长度
            $len = strlen($str);
            //echo 'English';
            if($len >= 3) {
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $str = substr($str, 0, 1) . '**';
            } elseif($len == 2) {
                //两个字符
                $str = substr($str2, 0, 1) . '*';
            }
        }
        return $str;

    }

}
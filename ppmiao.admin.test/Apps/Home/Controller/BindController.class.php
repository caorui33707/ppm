<?php
namespace Home\Controller;
use Think\Controller;

class BindController extends Controller {

    public function index(){

        $key = "account/bingbankcard";
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();

        $bank_code = I('post.bank_code');
        $bank_name = I('post.bank_name');

        $user = M('User')->where(['platcust' => I('post.platcust')])->find();

        $postdata = [
            'platcust' => I('post.platcust'),
            'type' => 1,
            'id_type' => 1,
            'id_code' => I('post.id_code'),
            'name' => I('post.name'),
            'card_no' => I('post.card_no'),
            'card_type' => 1,
            'pre_mobile' => I('post.pre_mobile'),
        ];

        $plainText =  \SignUtil::params2PlainText($postdata);
        $sign =  \SignUtil::sign($plainText);
        $postdata['signdata'] = $sign;



        $json  = $fd->post($key,$postdata);
        $data = json_decode($json);
        if($data->code == 0){

            $bank = [
                'user_id' => $user['id'],
                'bank_name' => $bank_name,
                'bank_code' => $bank_code,
                'mobile' => I('post.pre_mobile'),
                'id_no' => I('post.id_code'),
                'acct_name' => I('post.name'),
                'bank_card_no' => I('post.card_no'),
                'has_pay_success' => 1,
                'main_card' => 1,
                'add_time'=>date('Y-m-d H:i:s'),
                'modify_time'=>date('Y-m-d H:i:s'),
                'modify_user_id'=>99999999,
                'is_deleted'=>0
            ];
            $bankObj = M('UserBank')->where(['user_id'=>$user['id'],'bank_card_no'=>I('post.card_no')])->find();

            if($bankObj){

               $save = M('UserBank')->where(['id'=>$bankObj['id']])->save($bank);
            }else{
               $save = M('UserBank')->add($bank);
            }

            if($save){
                $this->merge($user['id']);
            }

        }
        header('Content-Type:application/json; charset=utf-8');
        echo $json;
    }

    public function merge($user_id){
        $bankInfo = M('UserBank')->where(['user_id'=>$user_id])->field("sum(`wait_money`) as `wait_money`,sum(`wallet_money`) as `wallet_money`,sum(`capital_money`) as `capital_money`,sum(`lock_money`) as `lock_money`")->find();

        if($bankInfo){

            M('UserBank')->startTrans();
            $merge = [
                'wait_money'=>$bankInfo['wait_money'],
                'wallet_money'=>$bankInfo['wallet_money'],
                'capital_money'=>$bankInfo['capital_money'],
                'lock_money'=>$bankInfo['lock_money'],
            ];
            $mergeRe =M('UserBank')->where(['user_id'=>$user_id,'main_card'=>1,'is_deleted'=>0])->save($merge);


            $clear = [
                'wait_money'=>0,
                'wallet_money'=>0,
                'capital_money'=>0,
                'lock_money'=>0,
            ];
            $clearRe =M('UserBank')->where(['user_id'=>$user_id,'main_card'=>0,'is_deleted'=>1])->save($clear);
//            if($mergeRe && $clearRe){
                M('UserBank')->commit();
//            }else{
//                M('UserBank')->rollback();
//            }

        }


    }


    public function unbind(){

        $key = "account/unbingbankcard";
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();



        $postdata = [
            'platcust' => trim(I('post.platcust')),
            'name' => trim(I('post.name')),
            'card_no_old' => trim(I('post.card_no_old')),
            'mobile' => trim(I('post.mobile')),
        ];


        $plainText =  \SignUtil::params2PlainText($postdata);
        $sign =  \SignUtil::sign($plainText);
        $postdata['signdata'] = $sign;



        $json  = $fd->post($key,$postdata);
        $data = json_decode($json);

        if($data->code == 0){
            $user = M('User')->where(['platcust' => trim(I('post.platcust'))])->find();
            $row = [
                'main_card'=>0,
                'is_deleted'=>1,
            ];
            M('UserBank')->where(['user_id'=>$user['id'],'main_card'=>1])->save($row);
        }


        header('Content-Type:application/json; charset=utf-8');
        echo $json;
    }



    public function bindRequest(){
        $key = "account/buildV2";
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();



        $postdata = [
            'platcust' => trim(I('post.platcust')),
            'name' => trim(I('post.name')),
            'id_code' => trim(I('post.id_code')),
            'card_no' => trim(I('post.card_no')),
            'pre_mobile' => trim(I('post.pre_mobile')),
            'open_branch' => trim(I('post.open_branch')),
        ];


        $plainText =  \SignUtil::params2PlainText($postdata);
        $sign =  \SignUtil::sign($plainText);
        $postdata['signdata'] = $sign;



        $json  = $fd->post($key,$postdata);

        header('Content-Type:application/json; charset=utf-8');
        echo $json;
    }


    public function bindConfirm(){

        $key = "account/confirmV2";
        vendor('Fund.FD');
        vendor('Fund.sign');
        $fd  = new \FD();


        $user = M('User')->where(['platcust' => trim(I('post.platcust'))])->find();

        $postdata = [
            'platcust' => trim(I('post.platcust')),
            'name' => trim(I('post.name')),
            'id_code' => trim(I('post.id_code')),
            'card_no' => trim(I('post.card_no')),
            'pre_mobile' => trim(I('post.pre_mobile')),
            'open_branch' => trim(I('post.open_branch')),
            'identifying_code' => trim(I('post.identifying_code')),
            'origin_order_no' => trim(I('post.origin_order_no')),
        ];

        $plainText =  \SignUtil::params2PlainText($postdata);
        $sign =  \SignUtil::sign($plainText);
        $postdata['signdata'] = $sign;

        $obj = M('Bank')->where(['bank_code'=>I('post.open_branch')])->find();


        $json  = $fd->post($key,$postdata);
        $data = json_decode($json);
        if($data->code == 0){

            $bank = [
                'user_id' => $user['id'],
                'bank_name' => $obj['bank_name'],
                'bank_code' => trim(I('post.open_branch')),
                'mobile' => trim(I('post.pre_mobile')),
                'id_no' => trim(I('post.id_code')),
                'acct_name' => trim(I('post.name')),
                'bank_card_no' => trim(I('post.card_no')),
                'has_pay_success' => 1,
                'main_card' => 1,
                'add_time'=>date('Y-m-d H:i:s'),
                'modify_time'=>date('Y-m-d H:i:s'),
                'modify_user_id'=>99999999,
                'is_deleted'=>0
            ];
            $bankObj = M('UserBank')->where(['user_id'=>$user['id'],'bank_card_no'=>trim(I('post.card_no'))])->find();

            if($bankObj){

                $save = M('UserBank')->where(['id'=>$bankObj['id']])->save($bank);
            }else{
                $save = M('UserBank')->add($bank);
            }

            if($save){
                $this->merge($user['id']);
            }

        }
        header('Content-Type:application/json; charset=utf-8');
        echo $json;
    }

    /**
     * 企业开户异步通知
     */
    public function succ()
    {
        $this->smsTemplate(3, '18167106183');
        $res = file_get_contents("php://input");

        //$orderNo     = I('post.order_no');
        $orderStatus = I('post.order_status');
        $errorInfo   = I('post.error_info','','strip_tags');
        $plat        = I('post.platcust');
        $financing   = M('Financing')->where(['platform_account' => $plat])->find();
        if($financing){
            //返回结果记录
            M('companyUser')->where(['id' => $financing['company_user_id']])->save(['callback_result'=>$res]);

            $companyUser = M('companyUser')->where(['id' => $financing['company_user_id']])->find();
            if ($orderStatus == 1) {
                //银行审核成功
                M('companyUser')->where(['id' => $financing['company_user_id']])->save(['status'=>3]);
                $this->smsTemplate(1, $companyUser['mobile']);
            }
            if($orderStatus == 2){
                //银行拒绝审核
                M('companyUser')->where(['id' => $financing['company_user_id']])->save(['status'=>5]);
                $this->smsTemplate(2, $companyUser['mobile']);
            }
        }
        //未通过信息保存
        if($errorInfo){
            M('companyUser')->where(['id' => $financing['company_user_id']])->save(['error_info'=>$errorInfo]);
        }

        echo '{"recode":"success"}';
    }

    public function bindUnBind()
    {
        $this->display();
    }


    /**
     * 发送短信
     * @param $type
     * @param $mobile
     */
    private function smsTemplate($type,$mobile){
        //$url = C('AUDIT_MESSAGE_URL');
        $url        = 'https://cg.ppmiao.com/';
        $sendMsgObj = new \Home\Services\CommonService();
        if($type==1){
            $successStr = '您在票票喵官网申请的企业账号已通过审核，请在官网登录查看。'. $url .'（请用电脑端打开查看）';
            $sendMsgObj->sendSms($mobile, $successStr);
        }
        if($type==2){
            $bindFailStr = '您在票票喵官网申请的企业账号绑定银行卡信息有误，请修改申请资料后再次提交。'. $url .'（请用电脑端打开查看）';
            $sendMsgObj->sendSms($mobile, $bindFailStr);
        }
        if($type==3){
            $bindFailStr = '异步回调成功了！';
            $sendMsgObj->sendSms($mobile, $bindFailStr);
        }
    }
}
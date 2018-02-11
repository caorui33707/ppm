<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 外部API接口
 * @package Home\Controller
 */
class ApiController extends BaseController{

    /**
     * 连连代付结果异步通知URL
     */
    public function llcallback(){
        $data = file_get_contents("php://input");
        //{"dt_order":"20150518154242","info_order":"测试程序代付","money_order":"0.01","no_order":"TEST20150518154243471439",
        //"oid_partner":"201503261000259502","oid_paybill":"2015051866189147","result_pay":"SUCCESS",
        //"settle_date":"20150519","sign":"9448a022a7dd6c53accfa8d40515945d","sign_type":"MD5"}

        // SUCCESS:代付成功 FAILURE:代付失败 CANCEL:代付退款
        $result = json_decode($data);
        F('llpay_result_'.time(), $data);
        $rows = array(
            'ret_code' => '0000',
            'ret_msg' => '交易成功',
        );
        echo json_encode($rows);
    }

    /**
     * 盛付通代付结果异步通知URL
     */
    public function sftcallback(){
        check_order_pay_status_by_sft('20150623001', '201506230000340');
    }

    /**
     * wap端邀请好友回调接口
     */
    public function wap_invitation(){
        if(IS_POST){
            $key = 'YcDCglmhQO7qUVeT';
            $uid = I('post.uid', 0, 'int');
            $mobil = I('post.mobil', '', 'strip_tags');
            $timestamp = I('post.t', '', 'strip_tags');
            $token = I('post.token', '', 'strip_tags');
            if($token == md5($uid.$mobil.$timestamp.$key)){
                $userObj = M("User");
                $invitInfo = $userObj->field('id,card_no')->where(array('username'=>$mobil))->find();
                $invitUid = $invitInfo['id'];
                // 确认是否是新手
                $userDueDetailObj = M("UserDueDetail");
                if($userDueDetailObj->where(array('user_id'=>$invitUid))->count() == 1){ // 仅成功购买过一次的用户才是新手
                    $userInvitationObj = M('UserInvitation');
                    // 检查被邀请者是否已被邀请(主要判断是否是同一个身份证下的不同手机号)
                    if(!$userInvitationObj->where("invited_phone='".$mobil."' or invited_cardno='".$invitInfo['card_no']."'")->getField('id')){
                        if(!$userInvitationObj->where(array('user_id'=>$uid,'invited_user_id'=>$invitUid))->getField('id')){
                            $rows = array(
                                'user_id' => $uid,
                                'invited_user_id' => $invitUid,
                                'invited_phone' => $mobil,
                                'invited_cardno' => $invitInfo['card_no'],
                                'add_time' => date('Y-m-d H:i:s', time()),
                            );
                            $userInvitationObj->add($rows);
                        }
                    }
                }
            }
        }else{
            status_403();
        }
    }

    /**
     * 接收短信回调接口
     */
    public function sms_callback(){
        $receiver = I('get.receiver', '', 'strip_tags');
        $pswd = I('get.pswd', '', 'strip_tags');
        $msgid = I('get.msgid', '', 'int');
        $reportTime = I('get.reportTime', '', 'strip_tags');
        $mobile = I('get.mobile', '', 'strip_tags');
        $status = I('get.status', '', 'strip_tags');
        $time = substr($reportTime, 0, 2);
        $time .= '-'.substr($reportTime, 2, 2);
        $time .= '-'.substr($reportTime, 4, 2);
        $time .= ' '.substr($reportTime, 6, 2);
        $time .= ':'.substr($reportTime, 8, 2);
        $reportTime = strtotime($time);
        $rows = array(
            'receiver' => $receiver,
            'pswd' => $pswd,
            'msgid' => $msgid,
            'reportTime' => $reportTime,
            'mobile' => $mobile,
            'status' => $status,
        );
        $smsStatusObj = M('SmsStatus');
        $smsStatusObj->add($rows);
    }

}
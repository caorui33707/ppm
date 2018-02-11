<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 用户登录页面
 */
class LoginController extends Controller{

    public function _empty(){
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("PublicNew:404");
    }

    public function index(){
        if(!IS_POST){
            $this->assign('source_url', $_SERVER['HTTP_REFERER']);
            $this->assign('meta_title', '用户登录');
            $this->assign('meta_keywords', '');
            $this->assign('meta_description', '');
            $this->display();
        }else{
            $phone = trim(I('post.phone', '', 'strip_tags'));
            if(strlen($phone) != 11) $this->ajaxReturn(array('status'=>0,'info'=>'手机号码格式不正确'));
            $verifycode = trim(I('post.verifycode', '', 'strip_tags'));
            $verifycode = filterSpecialChar($verifycode);
            $jump = I('post.jump', '', 'strip_tags');

            if($jump == C('WEB_ROOT').'/login/') $jump = '';
            $data = array(
                'mobile' => $phone,
                'mobile_auth_code' => $verifycode,
                'device_serial_id' => '',
                'device_type' => 4,
                'supper_user_id' => '',
                'channel' => 'pc',
                'registration_id' => '',
                'phone_system_version' => '',
            );
            $ret = post(C('API').C('interface.user_login'), $data);

            if($ret->code == 0) {
                $rows = objectToArray($ret->result);

                $rows['verify'] = md5($rows['token'].C('SESSION_KEY'));

                StorageData(ONLINE_SESSION, $rows);
                if(!$jump) $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/user/'));
                else $this->ajaxReturn(array('status'=>1,'info'=>$jump));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>($ret->errorMsg?$ret->errorMsg:'登录失败')));
            }
        }
    }

    /**
     * 登出
     */
    public function logout(){
        $jumpUrl = C('WEB_ROOT');
        if($_SERVER['HTTP_REFERER']) $jumpUrl = $_SERVER['HTTP_REFERER'];
        StorageData(ONLINE_SESSION, null);
        redirect(C('WEB_ROOT'));
        //redirect($jumpUrl);
    }

    /**
     * 发送用户登录验证码
     */
    public function sendsms(){
        if(!IS_POST || !IS_AJAX) exit;
        $SMSSendTime = $_SESSION['__SMS_SEND_TIME'];
        if(!$SMSSendTime){
            $_SESSION['__SMS_SEND_TIME'] = time(); // 保存短信发送时间
        }else{
            if(time() - $SMSSendTime < 60) $this->ajaxReturn(array('status'=>1)); // 60s内再次发送短信验证码则不做处理(直接成功操作)
            $_SESSION['__SMS_SEND_TIME'] = time(); // 保存短信发送时间
        }

        $phone = I('post.phone', '', 'strip_tags');
        $_SESSION['__SMS__'] = $phone;
        $ret = post(C('API').C('interface.login_sms'), array('mobile'=>$phone));
        if($ret->code != 0) {
            $_SESSION['__SMS_SEND_TIME'] = null;
            $_SESSION['__SMS__'] = null;
            $this->ajaxReturn(array('status'=>0,'info'=>$ret->errorMsg));
        }
        $this->ajaxReturn(array('status'=>1));
    }

}
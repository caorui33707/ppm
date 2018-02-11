<?php
namespace Home\Controller;

/**
 * 用户中心控制器
 * @package Home\Controller
 */
class UserController extends BaseController{
    
    protected $api;
    
    public function __construct(){
        
        parent::__construct();
        
        vendor('Api.ApiRequest');
        $this->api = new \ApiRequest();
    }
    
    public function login(){
        if(!IS_AJAX) {
            $user = session(USER_ONLINE_SESSION);
            if($user) {
                redirect(C('WEB_ROOT').'/product/index.html');
            } 
            $this->display('User/login/index');
        } else {
            $mobile = I('mobile','','strip_tags');
            $graph_code = I('graph_code','','strip_tags');
            $msg_code = I('msg_code',0,'int');
            $channel = I('channel', '', 'strip_tags'); 
                      
            if(!$mobile) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机号码'));
            }
            
            if(!check_mobile($mobile)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'填写的手机号码有误'));
            }
            
            $userInfo = M('User')->where('username='.$mobile)->find();
            
            if(!$userInfo) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该号码未注册，请先去注册'));
            } 
            
            
            if(!check_verify($graph_code, '')){
                $this->ajaxReturn(array('status'=>0,'info'=>'图片验证码错误','cc'=>$graph_code,));
            }
           
            if(!$msg_code) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机验证码'));
            }
           
            $postData = [
                'versionName' => '0.1',
                'device_type' => 4,
                'registration_id' => '0',
                'device_serial_id' => '0',
                'mobile'=>$mobile,
                'mobile_auth_code'=>$msg_code,
                'channel' => $channel
            ];
            
            $response = $this->api->httpPost('UserLogin', $postData);
            
            //print_r($response);
            
            if($response->code == 0) {
                $user = [
                    'user_id' => $response->result->id,
                    'token' => $response->result->token,
                    'username' => $response->result->username,                    
                    'realName' => $response->result->realName,                    
                    'realNameAuth' => $response->result->realNameAuth,
                    'platcustAuth' => $response->result->platcustAuth,
                ];
                session(USER_ONLINE_SESSION, $user);
                $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT')));
            } else {    
               $this->ajaxReturn(array('status'=>0,'info'=>$response->errorMsg));
            }
        }

    }
    
    
    
    public function reg(){
        
        $user = session(USER_ONLINE_SESSION);
        if($user) {
            redirect(C('WEB_ROOT').'/product/index.html');
        }
        if(IS_AJAX) {
            $mobile = I('mobile','','strip_tags');
            $graph_code = I('graph_code','','strip_tags');
            $msg_code = I('msg_code',0,'int');
            $channel = trim(I('channel', 'gwxz', 'strip_tags'));
            
            if(!$channel) {
                $channel = 'gwxz';
            }
            
            $jump_url = urldecode(I('jump_url', '/product/index.html', 'strip_tags'));
            if(!$mobile) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机号码'));
            }
    
            if(!check_mobile($mobile)) {
                $this->ajaxReturn(array('status'=>0,'info'=>'填写的手机号码有误'));
            }
    
            $userInfo = M('User')->where('username='.$mobile)->find();
    
            if($userInfo) {
                $this->ajaxReturn(array('status'=>0,'info'=>'该号码已经注册，请直接登录'));
            }
            if(!check_verify($graph_code, '')){
                $this->ajaxReturn(array('status'=>0,'info'=>'图形验证码错误'));
            }
            
            if(!$msg_code) {
                $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机验证码'));
            }           
           
            $postData = [
                'versionName' => '0.1',
                'device_type' => 4,
                'registration_id' => '0',
                'device_serial_id' => '0',
                'mobile'=>$mobile,
                'mobile_auth_code'=>$msg_code,
                'channel' => $channel
            ];
            
            $response = $this->api->httpPost('UserLogin', $postData);
            
            if($response->code == 0) {
                $user = [
                    'user_id' => $response->result->id,
                    'token' => $response->result->token,
                    'username' => $response->result->username,                    
                    'realName' => $response->result->realName,                    
                    'realNameAuth' => $response->result->realNameAuth,
                    'platcustAuth' => $response->result->platcustAuth,
                ];
                session(USER_ONLINE_SESSION, $user);
                $this->ajaxReturn(array('status'=>1,'info'=>C('WEB_ROOT').'/user/reg_succ.html?jump='.$jump_url));
            } else {    
               $this->ajaxReturn(array('status'=>0,'info'=>$response->errorMsg));
            }
        } else {
            $this->display('User/reg/index');
        }
    }
    
    public function reg_succ(){
        $this->assign('jump_url',I('jump'));
        $this->display('User/reg/reg_succ');
    }
    
    public function logout(){
        session(USER_ONLINE_SESSION,null);
        redirect(C('WEB_ROOT').'/user/login.html');
    }
    
    public function msgCode(){
        
        $mobile = I('mobile','','strip_tags');
        $graph_code = I('graph_code','','strip_tags');
        
        //$this->ajaxReturn(array('status'=>0,'info'=>$graph_code));
        
        if(!$mobile) {
            $this->ajaxReturn(array('status'=>0,'info'=>'请填写手机号码'));
        }
        if(!check_mobile($mobile)) {
            $this->ajaxReturn(array('status'=>0,'info'=>'填写的手机号码有误'));
        }
        
        if(!check_verify($graph_code, '')){
            $this->ajaxReturn(array('status'=>2,'info'=>'图片验证码错误'));
        }
        
        $data['mobile'] = $mobile;
        $response = $this->api->httpPost('SmsCode', $data);
        
        if($response->code == 0){
            $this->ajaxReturn(array('status'=>1,'info'=>'验证码已发送'));
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>$response->result->errorMsg));
        }
    }
}
<?php
namespace Home\Controller;
use Think\Controller;

class CommonController extends Controller{

    //获取验证码
    public function verify(){
        ob_end_clean();
        $config = array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    true, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
   
    
    public function getcode(){
        $product = '';
        $extno = '';
        $code = createSMSCode(5);  
        $msg = '您申请的手机短信的验证码为：'.$code.'，有效期1分钟.';
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        $data = [
            'account' =>  C('SMS_NAME'),
            'pswd' =>  C('SMS_PWD'),
            'mobile' =>  $mobile,
            'msg' =>  $msg,
            'needstatus' =>  'false',
            'product' => $product,
            'extno' => $extno
        ];
        $re = curlPost(C('SMS_URL'),$data);
        $arr = explode(",", $re);
        if($arr) {
            if($arr[1] == 0) {                
                S(md5($mobile),$code,60);                
                $this->ajaxReturn(array('status'=>1,'info'=>''));
            }
        }
        $this->ajaxReturn(array('status'=>0,'info'=>''));
    }
    
    public function getS(){
        $mobile = '18606502829';
        echo S(md5($mobile));
    }
    
    /**
    * 存管企业用户提现发送手机验证码
    * @date: 2017-7-12 下午5:45:54
    * @author: hui.xu
    * @param: variable
    * @return:
    */
    public function msgcode(){
        vendor('Fund.FD');
        vendor('Fund.sign');
        
        $mobile = trim(I('post.mobile', '', 'strip_tags'));
        
        $plainText =  \SignUtil::params2PlainText($req);
        
        $sign =  \SignUtil::sign($plainText);
        
        $req['signdata'] = \SignUtil::sign($plainText);
        $fd  = new \FD();
        
        return json_decode($fd->post('/project/publish',$req),true);
    }
    
}